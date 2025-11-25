<?php

namespace App\Http\Controllers;

use App\Models\VerificationBadge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class VerificationBadgeController extends Controller
{
    public function index()
    {
        $badges = VerificationBadge::orderBy('name')->get();

        return view('panel.verification-badges', [
            'badges' => $badges,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'alt_text' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:3072'],
        ]);

        $uploadPath = public_path('assets/linkstack/images/verification-badges');

        if (! File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true, true);
            File::put($uploadPath.'/.gitignore', "*\n!.gitignore");
        }

        $filename = Str::uuid()->toString().'.'.$request->file('icon')->extension();
        $request->file('icon')->move($uploadPath, $filename);

        VerificationBadge::create([
            'name' => $validated['name'],
            'alt_text' => $validated['alt_text'],
            'icon_path' => 'assets/linkstack/images/verification-badges/'.$filename,
        ]);

        return redirect()->route('verification-badges.index')
            ->with('success', __('messages.Verification badge created'));
    }

    public function destroy(VerificationBadge $verificationBadge)
    {
        if ($verificationBadge->icon_path && File::exists(public_path($verificationBadge->icon_path))) {
            File::delete(public_path($verificationBadge->icon_path));
        }

        $verificationBadge->delete();

        return redirect()->route('verification-badges.index')
            ->with('success', __('messages.Verification badge deleted'));
    }
}