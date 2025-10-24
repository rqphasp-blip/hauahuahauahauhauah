<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PluginManagerService;
use App\Models\Plugin;
use Illuminate\Support\Facades\Log;

class PluginController extends Controller
{
    protected $pluginManager;

    public function __construct(PluginManagerService $pluginManager)
    {
        $this->pluginManager = $pluginManager;
        // Adicionar middleware de admin aqui, se necessário
        // $this->middleware("auth");
        // $this->middleware("is_admin"); 
    }

    public function index()
    {
        // Descobrir plugins caso algum tenha sido adicionado manualmente
        // Em um cenário ideal, isso seria menos frequente ou via comando Artisan
        try {
            $this->pluginManager->discoverAndSyncPlugins();
        } catch (\Illuminate\Database\QueryException $e) {
            // Tabela plugins pode não existir durante as primeiras migrations
            Log::warning("PluginController: Could not sync plugins, table might not exist yet or other DB issue: " . $e->getMessage());
        } catch (\Exception $e) {
            Log::error("PluginController: Error discovering plugins: " . $e->getMessage());
        }
        
        $plugins = Plugin::all();
        $header = "Gerenciador de Plugins"; // Adicionando a variável $header
        return view("admin.plugins.index", compact("plugins", "header")); // Passando $header para a view
    }

    public function create()
    {
        $header = "Instalar Novo Plugin"; // Adicionando a variável $header
        return view("admin.plugins.create", compact("header")); // Passando $header para a view
    }

    public function store(Request $request)
    {
        $request->validate([
            "plugin_zip" => "required|file|mimes:zip",
        ]);

        $path = $request->file("plugin_zip")->store("temp_plugins");
        $fullPath = storage_path("app/" . $path);

        $result = $this->pluginManager->installPluginFromZip($fullPath, $request->has("overwrite"));

        if ($result["success"]) {
            return redirect()->route("admin.plugins.index")->with("success", $result["message"]);
        } else {
            return back()->with("error", $result["message"])->withInput();
        }
    }

    public function activate(string $identifier)
    {
        if ($this->pluginManager->activatePlugin($identifier)) {
            return redirect()->route("admin.plugins.index")->with("success", "Plugin '{$identifier}' ativado com sucesso.");
        } else {
            return redirect()->route("admin.plugins.index")->with("error", "Falha ao ativar o plugin '{$identifier}'.");
        }
    }

    public function deactivate(string $identifier)
    {
        if ($this->pluginManager->deactivatePlugin($identifier)) {
            return redirect()->route("admin.plugins.index")->with("success", "Plugin '{$identifier}' desativado com sucesso.");
        } else {
            return redirect()->route("admin.plugins.index")->with("error", "Falha ao desativar o plugin '{$identifier}'.");
        }
    }

    public function delete(string $identifier) // Alterado para destroy para seguir convenção, mas mantendo delete se for o nome da rota
    {
        if ($this->pluginManager->deletePlugin($identifier)) {
            return redirect()->route("admin.plugins.index")->with("success", "Plugin '{$identifier}' excluído com sucesso.");
        } else {
            return redirect()->route("admin.plugins.index")->with("error", "Falha ao excluir o plugin '{$identifier}'.");
        }
    }
}

