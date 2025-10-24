<?php
namespace App\Providers\plugins\banner;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ProfileBannerController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
     * Exibe a página de gerenciamento do banner de perfil
     */
    public function index()
    {
        $user = Auth::user();
        // Passa o usuário para a view, que pode então verificar $user->profile_banner_path
        return view("banner::index", compact("user"));
    }

    /**
     * Processa o upload do banner
     */
    public function store(Request $request)
    {
        $request->validate([
            "banner_image" => "required|image|mimes:jpeg,png,jpg,gif,webp|max:2048",
        ]);

        $user = Auth::user();

        // Diretório de upload
        $uploadDir = "uploads/profile_banners";
        $fullPathDir = public_path($uploadDir);

        // Cria o diretório se não existir
        if (!file_exists($fullPathDir)) {
            mkdir($fullPathDir, 0755, true);
        }

        // Remove o banner antigo se existir
        if ($user->profile_banner_path && file_exists(public_path($user->profile_banner_path))) {
            unlink(public_path($user->profile_banner_path));
        }

        // Salva o novo banner
        $image = $request->file("banner_image");
        $filename = uniqid("banner_", true) . "." . $image->getClientOriginalExtension();
        $path = $uploadDir . "/" . $filename;
        
        $image->move($fullPathDir, $filename);

        // Atualiza o banco de dados diretamente
        DB::table("users")
            ->where("id", $user->id)
            ->update(["profile_banner_path" => $path]);

        return redirect()->route("banner.index")->with("success", "Banner do perfil atualizado com sucesso!");
    }

    /**
     * Remove o banner do perfil
     */
    public function destroy()
    {
        $user = Auth::user();

        if ($user->profile_banner_path && file_exists(public_path($user->profile_banner_path))) {
            unlink(public_path($user->profile_banner_path));
            
            // Atualiza o banco de dados diretamente
            DB::table("users")
                ->where("id", $user->id)
                ->update(["profile_banner_path" => null]);

            return redirect()->route("banner.index")->with("success", "Banner do perfil removido com sucesso!");
        }

        return redirect()->route("banner.index")->with("error", "Nenhum banner para remover.");
    }
}
