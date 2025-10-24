# Instruções de Instalação do Plugin de Banner de Perfil (Página Independente)

Este plugin cria uma página independente para gerenciar a imagem de banner do perfil do usuário, com altura máxima de 250px.

## Requisitos

- Laravel 8+
- Tabela `users` com uma coluna `profile_banner_path` (string, nullable)
- Diretório `public/uploads/profile_banners` com permissões de escrita

## Instalação

1. **Adicione a coluna ao banco de dados**

   Execute o seguinte SQL no seu banco de dados:

   ```sql
   ALTER TABLE users 
   ADD COLUMN profile_banner_path VARCHAR(255) NULL 
   AFTER profile_photo_path;
   ```

2. **Copie os arquivos do plugin**

   - Copie `ProfileBannerController.php` para `app/Http/Controllers/`
   - Copie `banner.blade.php` para `resources/views/profile/`
   - Adicione o conteúdo de `profile_banner_routes.php` ao seu arquivo `routes/web.php`

3. **Crie o diretório para uploads**

   ```
   mkdir -p public/uploads/profile_banners
   chmod 755 public/uploads/profile_banners
   ```

4. **Adicione um link para a página de banner no menu do usuário**

   Adicione o seguinte código no menu de navegação do usuário:

   ```html
   <a href="{{ route('profile.banner') }}" class="dropdown-item">
       <i class="fas fa-image mr-2"></i> Gerenciar Banner de Perfil
   </a>
   ```

## Exibição do Banner no Perfil Público

Para exibir o banner no perfil público de um usuário, adicione o seguinte código no topo da view do perfil (por exemplo, no arquivo `heading.blade.php`):

```php
<?php include(base_path('display_banner.php')); ?>
```

Ou, alternativamente, você pode adicionar este código diretamente:

```php
<?php if (isset($user) && $user->profile_banner_path && file_exists(public_path($user->profile_banner_path))): ?>
    <div class="profile-banner">
        <img src="<?php echo url($user->profile_banner_path); ?>" 
             alt="Banner do Perfil" 
             style="width: 100%; max-height: 250px; object-fit: cover; border-radius: 8px;">
    </div>
<?php endif; ?>
```

## Solução de Problemas

- Se o upload falhar, verifique as permissões da pasta `public/uploads/profile_banners`.
- Se a página não carregar, verifique se as rotas foram adicionadas corretamente ao arquivo `routes/web.php`.
- Se o banner não aparecer no perfil público, verifique se o código de exibição foi adicionado corretamente e se o usuário tem um banner salvo no banco de dados.
