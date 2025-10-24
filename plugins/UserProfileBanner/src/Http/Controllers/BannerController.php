<?php

namespace plugins\UserProfileBanner\Http\Controllers;

use App\Http\Controllers\Controller; // Assuming your app's base controller
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User; // Assuming your User model is in App\Models

class BannerController extends Controller
{
    public function upload(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return back()->with("error", "Usuário não autenticado.");
        }

        $config = config("user_profile_banner");

        $validator = Validator::make($request->all(), [
            "profile_banner" => [
                "required",
                "image",
                "mimes:" . implode(",", array_map(fn($mime) => explode("/", $mime)[1], $config["allowed_mime_types"])),
                "max:" . $config["max_file_size"],
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($request->hasFile("profile_banner")) {
            $file = $request->file("profile_banner");
            $disk = $config["storage_disk"];
            $directory = $config["banner_directory"];

            // Remove old banner if exists
            if ($user->profile_banner_path && Storage::disk($disk)->exists($user->profile_banner_path)) {
                Storage::disk($disk)->delete($user->profile_banner_path);
            }

            // Store new banner
            // Use a unique name, e.g., user_id_timestamp.extension
            $fileName = $user->id . "_" . time() . "." . $file->getClientOriginalExtension();
            $path = $file->storeAs($directory, $fileName, $disk);

            // Update user model
            // Ensure your User model has `profile_banner_path` in $fillable or use save()
            $userToUpdate = User::find($user->id);
            if ($userToUpdate) {
                $userToUpdate->profile_banner_path = $path;
                $userToUpdate->save();
                return back()->with("success", "Banner do perfil atualizado com sucesso!");
            } else {
                 if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }
                return back()->with("error", "Não foi possível encontrar o usuário para atualizar o banner.");
            }
        }

        return back()->with("error", "Nenhum arquivo de banner enviado.");
    }

    public function remove(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return back()->with("error", "Usuário não autenticado.");
        }
        
        $userToUpdate = User::find($user->id);
        if (!$userToUpdate) {
            return back()->with("error", "Usuário não encontrado.");
        }

        if ($userToUpdate->profile_banner_path) {
            $disk = config("user_profile_banner.storage_disk", "public");
            if (Storage::disk($disk)->exists($userToUpdate->profile_banner_path)) {
                Storage::disk($disk)->delete($userToUpdate->profile_banner_path);
            }
            $userToUpdate->profile_banner_path = null;
            $userToUpdate->save();
            return back()->with("success", "Banner do perfil removido com sucesso!");
        }

        return back()->with("info", "Nenhum banner de perfil para remover.");
    }
}

