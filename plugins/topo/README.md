# Instruções de Instalação do Plugin de Banner de Perfil (Versão Direta)

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

2. **Copie o arquivo do plugin**

   Copie o arquivo `banner_plugin_direto.php` para qualquer diretório do seu projeto Laravel.

3. **Inclua o plugin na view**

   Adicione o seguinte código na view onde deseja exibir o formulário de upload:

   ```php
   <?php include(base_path('caminho/para/banner_plugin_direto.php')); ?>
   ```

   Substitua `caminho/para/` pelo caminho real onde você colocou o arquivo.

4. **Configure o storage público**

   Certifique-se de que o storage público está configurado e acessível:

   ```bash
   php artisan storage:link
   ```

## Exibição do Banner no Perfil Público

Para exibir o banner no perfil público de um usuário, adicione o seguinte código no topo da view do perfil:

```php
<?php if ($user->profile_banner_path): ?>
    <div class="profile-banner">
        <img src="<?php echo Storage::url($user->profile_banner_path); ?>" 
             alt="Banner do Perfil" 
             style="width: 100%; max-height: 250px; object-fit: cover;">
    </div>
<?php endif; ?>
```

## Solução de Problemas

- Se as imagens não aparecerem, verifique se o link simbólico do storage está configurado corretamente.
- Se o upload falhar, verifique as permissões da pasta `storage/app/public/profile_banners`.
- Este plugin não depende de namespaces, ServiceProviders ou includes Blade complexos, o que elimina a maioria dos problemas comuns de integração.
