<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Adicionado para depuração, se necessário

class AppearanceSettingsController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validação (opcional, mas recomendada)
        $validatedData = $request->validate([
            'size_title' => 'nullable|integer|min:8|max:72', // Exemplo de validação
            'hide_title' => 'nullable|boolean',
        ]);

        if ($request->has('size_title')) {
            $user->size_title = $request->input('size_title');
        }

        // Se 'hide_title' for enviado e for '1', então é true. Caso contrário (não enviado ou valor diferente), é false.
        $user->hide_title = $request->input('hide_title', 0) == '1';

        try {
            $user->save();
        } catch (\Exception $e) {
            // Log::error('Erro ao salvar configurações de aparência: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao salvar as configurações de aparência.');
        }

        return redirect()->back()->with('success', 'Configurações de aparência atualizadas com sucesso!');
    }
}

