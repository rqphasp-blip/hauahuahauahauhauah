<?php
/**
 * Banner de Perfil - Arquivo PHP único e autossuficiente
 * 
 * Este arquivo contém todo o código necessário para implementar o banner de perfil
 * sem depender de namespaces, ServiceProviders, includes complexos ou comandos artisan.
 * 
 * Instruções:
 * 1. Coloque este arquivo em qualquer diretório do seu projeto
 * 2. Inclua-o diretamente na view com: <?php include('caminho/para/banner_plugin_direto.php'); ?>
 */

// Verifica se o usuário está autenticado
if (!auth()->check()) {
    return;
}

// Obtém o usuário atual
$user = auth()->user();

// Define o diretório de upload (público, acessível via web)
$upload_dir = public_path('uploads/profile_banners');
$upload_url = url('uploads/profile_banners');

// Verifica se é uma requisição de upload
if (isset($_POST['banner_upload_action']) && $_POST['banner_upload_action'] == 'upload') {
    $errors = [];
    $success = null;
    
    // Valida o arquivo
    if (!isset($_FILES['banner_image']) || $_FILES['banner_image']['error'] != 0) {
        $errors[] = "Selecione uma imagem válida.";
    } else {
        $file = $_FILES['banner_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        // Verifica o tipo de arquivo
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP.";
        }
        
        // Verifica o tamanho
        if ($file['size'] > $max_size) {
            $errors[] = "O arquivo é muito grande. Tamanho máximo: 2MB.";
        }
        
        // Se não houver erros, faz o upload
        if (empty($errors)) {
            // Cria o diretório se não existir
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Remove o banner antigo se existir
            if ($user->profile_banner_path && file_exists(public_path($user->profile_banner_path))) {
                unlink(public_path($user->profile_banner_path));
            }
            
            // Gera um nome único para o arquivo
            $filename = uniqid('banner_', true) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $path = 'uploads/profile_banners/' . $filename;
            
            // Move o arquivo para o diretório público
            if (move_uploaded_file($file['tmp_name'], public_path($path))) {
                // Atualiza o usuário no banco de dados
                $user->profile_banner_path = $path;
                $user->save();
                
                $success = "Banner do perfil atualizado com sucesso!";
            } else {
                $errors[] = "Erro ao fazer upload do arquivo. Verifique as permissões da pasta uploads/profile_banners.";
            }
        }
    }
}

// Verifica se é uma requisição de remoção
if (isset($_POST['banner_upload_action']) && $_POST['banner_upload_action'] == 'remove') {
    // Remove o banner antigo se existir
    if ($user->profile_banner_path && file_exists(public_path($user->profile_banner_path))) {
        unlink(public_path($user->profile_banner_path));
        $user->profile_banner_path = null;
        $user->save();
        $success = "Banner do perfil removido com sucesso!";
    } else {
        $errors = ["Nenhum banner para remover."];
    }
}

// Exibe mensagens de erro ou sucesso
if (isset($errors) && !empty($errors)) {
    echo '<div class="mb-4 font-medium text-sm text-red-600">';
    foreach ($errors as $error) {
        echo htmlspecialchars($error) . '<br>';
    }
    echo '</div>';
}

if (isset($success)) {
    echo '<div class="mb-4 font-medium text-sm text-green-600">' . htmlspecialchars($success) . '</div>';
}

// Exibe o formulário de upload
?>
<div>
    <form method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="banner_upload_action" value="upload">

        <div>
            <label for="banner_image" class="block font-medium text-sm text-gray-700">Imagem do Banner (JPG, PNG, GIF, WEBP - Máx 2MB)</label>
            <input id="banner_image" name="banner_image" type="file" class="block mt-1 w-full" required autofocus />
        </div>

        <div class="flex items-center justify-end mt-4">
            <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Enviar Banner
            </button>
        </div>
    </form>

    <?php if ($user->profile_banner_path): ?>
        <hr class="my-6">
        <h3 class="text-lg font-medium text-gray-900">Banner Atual</h3>
        <div class="mt-2">
            <img src="<?php echo e(url($user->profile_banner_path)); ?>" 
                 alt="Banner do Perfil" 
                 style="max-height: 250px; width: auto; border-radius: 8px; margin-top: 10px;">
        </div>
        <form method="POST" class="mt-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="banner_upload_action" value="remove">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                Remover Banner
            </button>
        </form>
    <?php endif; ?>
</div>
