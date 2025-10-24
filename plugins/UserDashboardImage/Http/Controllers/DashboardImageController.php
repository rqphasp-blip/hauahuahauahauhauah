<?php

namespace plugins\UserDashboardImage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User; // Assuming your User model is in App\Models
use Illuminate\Support\Str;

class DashboardImageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'dashboard_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('dashboard_image')) {
            // Delete old image if exists
            if ($user->dashboard_image_path && Storage::disk('public')->exists($user->dashboard_image_path)) {
                Storage::disk('public')->delete($user->dashboard_image_path);
            }

            $image = $request->file('dashboard_image');
            
            // Sanitize the original name to prevent directory traversal or other issues
            $originalName = $image->getClientOriginalName();
            $sanitizedOriginalName = preg_replace("/[^a-zA-Z0-9._-]", "", $originalName);

            // Get the extension. Try client's extension first, then guess.
            $extension = $image->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = $image->guessExtension(); // Tries to guess from MIME type
            }
            if (empty($extension)) {
                // Fallback if guessExtension also fails (e.g., for some SVG or non-standard files)
                // you might want to default to a common extension or reject the file
                $extension = 'png'; // Default to png or handle error
            }

            // Ensure the sanitized name (if used) is not empty and has an extension part
            $baseName = pathinfo($sanitizedOriginalName, PATHINFO_FILENAME);
            if (empty($baseName)) {
                $baseName = Str::random(10); // Generate a random name if original is problematic
            }

            $imageName = time() . '_' . $baseName . '.' . $extension;
            
            // Store in 'public/dashboard_images/user_id/image_name.ext'
            $path = $image->storeAs('dashboard_images/' . $user->id, $imageName, 'public');

            $userToUpdate = User::find($user->id);
            if ($userToUpdate) {
                $userToUpdate->dashboard_image_path = $path;
                $userToUpdate->save();
            }
            
            return back()->with('success', 'Imagem do dashboard atualizada com sucesso!')->with('dashboard_image_path', $path);
        }

        return back()->with('error', 'Falha no upload da imagem.');
    }
}

