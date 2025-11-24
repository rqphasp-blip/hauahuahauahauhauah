<?php

namespace plugins\GoogleMapsProfile\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class GoogleMapsProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        return view('googlemapsprofile::settings', [
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'feature_maps_status' => ['nullable', 'boolean'],
            'feature_maps_address' => ['nullable', 'string', 'max:255', 'required_if:feature_maps_status,1'],
            'feature_maps_coordinates' => ['nullable', 'string', 'max:255'],
            'feature_maps_zoom' => ['nullable', 'integer', 'min:1', 'max:20'],
        ], [
            'feature_maps_address.required_if' => 'Informe um endereço para exibir o mapa quando a funcionalidade estiver ativada.',
        ]);

        $user->feature_maps_status = (bool) ($validated['feature_maps_status'] ?? false);
        $user->feature_maps_address = $validated['feature_maps_address'] ?? null;
        $user->feature_maps_coordinates = $validated['feature_maps_coordinates'] ?? null;
        $user->feature_maps_zoom = $validated['feature_maps_zoom'] ?? 15;

        $user->save();

        return back()->with('success', 'Mapa atualizado com sucesso no seu perfil.');
    }
}