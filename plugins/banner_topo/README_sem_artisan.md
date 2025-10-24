# Instruções de Instalação do Plugin de Banner de Perfil (Versão Sem Artisan)

Este plugin simples permite adicionar uma imagem de banner ao perfil de cada usuário, com altura máxima de 250px, sem depender de comandos artisan.

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

2. **Crie o diretório para uploads**

   Crie o diretório `uploads/profile_banners` dentro da pasta `public` do seu projeto:

   ```
   mkdir -p public/uploads/profile_banners
   chmod 755 public/uploads/profile_banners
   ```

3. **Copie o arquivo do plugin**

   Copie o arquivo `banner_plugin_direto_sem_artisan.php` para qualquer diretório do seu projeto Laravel.

4. **Inclua o plugin na view**

   Adicione o seguinte código na view onde deseja exibir o formulário de upload:

   ```php
   <?php include(base_path('caminho/para/banner_plugin_direto_sem_artisan.php')); ?>
   ```

   Substitua `caminho/para/` pelo caminho real onde você colocou o arquivo.

## Exibição do Banner no Perfil Público

Para exibir o banner no perfil público de um usuário, adicione o seguinte código no topo da view do perfil:

```php
<?php if ($user->profile_banner_path): ?>
    <div class="profile-banner">
        <img src="<?php echo url($user->profile_banner_path); ?>" 
             alt="Banner do Perfil" 
             style="width: 100%; max-height: 250px; object-fit: cover;">
    </div>
<?php endif; ?>
```

## Solução de Problemas

- Se o upload falhar, verifique as permissões da pasta `public/uploads/profile_banners`.
- Este plugin não depende de namespaces, ServiceProviders, includes Blade complexos ou comandos artisan, o que elimina a maioria dos problemas comuns de integração.
- Os arquivos são salvos diretamente na pasta pública, sem usar o sistema de storage do Laravel.
