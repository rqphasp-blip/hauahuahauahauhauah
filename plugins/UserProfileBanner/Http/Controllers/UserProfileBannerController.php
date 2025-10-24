<?php

namespace plugins\UserProfileBanner\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image; // Assuming Intervention Image is or will be installed

class UserProfileBannerController extends Controller
{
    public function __construct()
    {
        // Middleware to ensure user is authenticated
        $this->middleware("auth");
    }

    /**
     * Show the form for uploading a new profile banner.
     */
    public function create()
    {
        return view("userprofilebanner::upload_form");
    }

    /**
     * Store a newly uploaded profile banner.
     */
    public function store(Request $request)
    {
        $request->validate([
            "banner_image" => "required|image|mimes:jpeg,png,jpg,gif,webp|max:2048", // Max 2MB
        ]);

        $user = Auth::user();

        // Delete old banner if exists
        if ($user->profile_banner_path && Storage::disk("public")->exists($user->profile_banner_path)) {
            Storage::disk("public")->delete($user->profile_banner_path);
        }

        $image = $request->file("banner_image");
        $filename = uniqid("banner_", true) . "." . $image->getClientOriginalExtension();
        
        // Store in public/storage/profile_banners
        // Ensure you have run `php artisan storage:link`
        $path = $image->storeAs("profile_banners", $filename, "public");

        $user->profile_banner_path = $path;
        $user->save();

        return back()->with("success", "Banner do perfil atualizado com sucesso!");
    }

    /**
     * Remove the profile banner.
     */
    public function destroy()
    {
        $user = Auth::user();

        if ($user->profile_banner_path) {
            if (Storage::disk("public")->exists($user->profile_banner_path)) {
                Storage::disk("public")->delete($user->profile_banner_path);
            }
            $user->profile_banner_path = null;
            $user->save();
            return back()->with("success", "Banner do perfil removido com sucesso!");
        }

        return back()->with("error", "Nenhum banner para remover.");
    }
}

