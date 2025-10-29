<?php

namespace plugins\contatos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContatosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['publicForm', 'submitPublicLead']);
    }

    public function index()
    {
        $user = Auth::user();

        $campaigns = DB::table('lead_campaigns')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $leadTotals = DB::table('campaign_leads')
            ->select('campaign_id', DB::raw('COUNT(*) as total'))
            ->where('user_id', $user->id)
            ->groupBy('campaign_id')
            ->pluck('total', 'campaign_id');

        return view('contatos::index', [
            'user' => $user,
            'campaigns' => $campaigns,
            'leadTotals' => $leadTotals,
        ]);
    }

    public function createCampaign()
    {
        return view('contatos::campaign_create');
    }

    public function storeCampaign(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();
        $slugBase = Str::slug($request->input('name'));
        $slug = $slugBase ?: Str::random(8);
        $counter = 1;

        while (DB::table('lead_campaigns')->where('slug', $slug)->exists()) {
            $slug = ($slugBase ?: Str::random(8)) . '-' . $counter++;
        }

        $campaignId = DB::table('lead_campaigns')->insertGetId([
            'user_id' => $user->id,
            'name' => $request->input('name'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('contatos.campaigns.show', ['campaign' => $campaignId])
            ->with('success', 'Campanha criada com sucesso!');
    }

    public function showCampaign($campaignId)
    {
        $user = Auth::user();

        $campaign = DB::table('lead_campaigns')
            ->where('id', $campaignId)
            ->where('user_id', $user->id)
            ->first();

        if (!$campaign) {
            return redirect()->route('contatos.index')->with('error', 'Campanha não encontrada.');
        }

        $leads = DB::table('campaign_leads')
            ->where('campaign_id', $campaign->id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $formUrl = route('contatos.form', ['slug' => $campaign->slug]);

        return view('contatos::campaign_show', [
            'campaign' => $campaign,
            'leads' => $leads,
            'formUrl' => $formUrl,
        ]);
    }

    public function storeLead(Request $request, $campaignId)
    {
        $user = Auth::user();

        $campaign = DB::table('lead_campaigns')
            ->where('id', $campaignId)
            ->where('user_id', $user->id)
            ->first();

        if (!$campaign) {
            return redirect()->route('contatos.index')->with('error', 'Campanha não encontrada.');
        }

        $data = $this->validateLead($request);
        $this->persistLead($campaign, $data, $user->id);

        return redirect()->route('contatos.campaigns.show', ['campaign' => $campaign->id])
            ->with('success', 'Lead adicionado com sucesso!');
    }

    public function publicForm($slug)
    {
        $campaign = DB::table('lead_campaigns')->where('slug', $slug)->first();

        if (!$campaign) {
            abort(404);
        }

        return view('contatos::public_form', [
            'campaign' => $campaign,
        ]);
    }

    public function submitPublicLead(Request $request, $slug)
    {
        $campaign = DB::table('lead_campaigns')->where('slug', $slug)->first();

        if (!$campaign) {
            abort(404);
        }

        $data = $this->validateLead($request);
        $this->persistLead($campaign, $data, $campaign->user_id);

        return redirect()->route('contatos.form', ['slug' => $slug])
            ->with('success', 'Recebemos suas informações com sucesso!');
    }

    protected function validateLead(Request $request): array
    {
        return $request->validate([
            'lead_name' => ['required', 'string', 'max:191'],
            'lead_email' => ['required', 'email', 'max:191'],
            'lead_phone' => ['nullable', 'string', 'max:50'],
            'lead_message' => ['nullable', 'string', 'max:500'],
        ]);
    }

    protected function persistLead(object $campaign, array $data, int $userId): void
    {
        DB::table('campaign_leads')->insert([
            'user_id' => $userId,
            'campaign_id' => $campaign->id,
            'lead_name' => $data['lead_name'],
            'lead_email' => $data['lead_email'],
            'lead_phone' => $data['lead_phone'] ?? null,
            'lead_message' => $data['lead_message'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
