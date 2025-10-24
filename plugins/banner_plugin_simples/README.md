# Instruções de Instalação do Plugin de Banner de Perfil

Este plugin simples permite adicionar uma imagem de banner ao perfil de cada usuário, com altura máxima de 250px.

## Requisitos

- Laravel 8+
- Tabela `users` com uma coluna `profile_banner_path` (string, nullable)
- Storage configurado para armazenar arquivos públicos

## Instalação

1. **Adicione a coluna ao banco de dados**

   Execute o seguinte SQL no seu banco de dados:

   ```sql
   ALTER TABLE users 
   ADD COLUMN profile_banner_path VARCHAR(255) NULL 
   AFTER profile_photo_path;
   ```

   Ou use a migration incluída no pacote.

2. **Copie os arquivos do plugin**

   Copie a pasta `banner_plugin_simples` para a raiz do seu projeto Laravel.

3. **Configure as rotas**

   Adicione as seguintes rotas ao seu arquivo `routes/web.php`:

   ```php
   // Rota para processar o upload do banner
   Route::post('/perfil/banner/upload', function () {
       include_once base_path('banner_plugin_simples/includes/banner_upload_handler.php');
   })->middleware('auth')->name('perfil.banner.upload');

   // Rota para remover o banner
   Route::post('/perfil/banner/remover', function () {
       include_once base_path('banner_plugin_simples/includes/banner_remove_handler.php');
   })->middleware('auth')->name('perfil.banner.remover');
   ```

4. **Inclua o formulário de upload na página de perfil**

   Adicione o seguinte código na view onde deseja exibir o formulário de upload:

   ```php
   @include('banner_plugin_simples.includes.formulario_banner')
   ```

5. **Inclua a exibição do banner no perfil público**

   Adicione o seguinte código no topo da view do perfil público:

   ```php
   @include('banner_plugin_simples.includes.exibir_banner')
   ```

6. **Configure o storage público**

   Certifique-se de que o storage público está configurado e acessível:

   ```bash
   php artisan storage:link
   ```

## Personalização

Você pode personalizar os arquivos de include conforme necessário para se adequar ao estilo do seu site. Os arquivos estão localizados em:

- `banner_plugin_simples/includes/formulario_banner.blade.php` - Formulário de upload
- `banner_plugin_simples/includes/exibir_banner.blade.php` - Exibição do banner

## Solução de Problemas

- Se as imagens não aparecerem, verifique se o link simbólico do storage está configurado corretamente.
- Se o upload falhar, verifique as permissões da pasta `storage/app/public/profile_banners`.
- Se o formulário não aparecer, verifique se o caminho do include está correto.
