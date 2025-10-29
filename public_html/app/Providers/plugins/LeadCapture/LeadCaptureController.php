<?php

namespace plugins\LeadCapture\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use plugins\LeadCapture\Models\Lead;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadCaptureController extends Controller
{
    private const STATUS_OPTIONS = ['novo', 'contatado', 'convertido', 'arquivado'];

    public function showForm(): View
    {
        return view('LeadCapture::form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string'],
        ]);

        Lead::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'novo',
            'notes' => null,
        ]);

        return redirect()->route('leadcapture.form')->with('success', __('Lead enviado com sucesso.'));
    }

    public function index(Request $request): View
    {
        $query = Lead::query();

        [$search, $status, $dateFrom, $dateTo] = $this->applyFilters($query, $request);

        $leads = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Lead::count(),
            'novos' => Lead::where('status', 'novo')->count(),
            'contatados' => Lead::where('status', 'contatado')->count(),
            'convertidos' => Lead::where('status', 'convertido')->count(),
        ];

        return view('LeadCapture::index', compact('leads', 'stats', 'search', 'status', 'dateFrom', 'dateTo'));
    }

    public function show(Lead $lead): View
    {
        return view('LeadCapture::show', compact('lead'));
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUS_OPTIONS)],
            'notes' => ['nullable', 'string'],
        ]);

        $lead->fill([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);
        $lead->save();

        return redirect()
            ->route('leadcapture.show', $lead)
            ->with('success', __('Lead atualizado com sucesso.'));
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()
            ->route('leadcapture.index')
            ->with('success', __('Lead excluído com sucesso.'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Lead::query();
        $this->applyFilters($query, $request);

        $fileName = 'leads_' . now()->format('Y_m_d_His') . '.csv';

        return Response::streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Nome', 'E-mail', 'Telefone', 'Status', 'Notas', 'Criado em']);

            $query->orderByDesc('created_at')->chunk(200, function ($leads) use ($handle) {
                foreach ($leads as $lead) {
                    fputcsv($handle, [
                        $lead->id,
                        $lead->name,
                        $lead->email,
                        $lead->phone,
                        $lead->status,
                        $lead->notes,
                        optional($lead->created_at)->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  Builder  $query
     * @return array{0:?string,1:?string,2:?string,3:?string}
     */
    private function applyFilters(Builder $query, Request $request): array
    {
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $status = $request->input('status');
        if (is_string($status) && in_array($status, self::STATUS_OPTIONS, true)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        $dateFrom = $request->input('date_from');
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        } else {
            $dateFrom = null;
        }

        $dateTo = $request->input('date_to');
        if ($dateTo) {
            $query->whereDate('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
        } else {
            $dateTo = null;
        }

        return [$search !== '' ? $search : null, $status, $dateFrom, $dateTo];
    }
}