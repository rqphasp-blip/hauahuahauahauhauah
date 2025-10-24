<?php
/**
 * Script SQL para adicionar a coluna profile_banner_path à tabela users
 * 
 * Execute este script no seu banco de dados se não quiser usar migrations
 */

// Versão SQL para MySQL/MariaDB
$sql = "
ALTER TABLE users 
ADD COLUMN profile_banner_path VARCHAR(255) NULL 
AFTER profile_photo_path;
";

// Nota: Se você estiver usando PostgreSQL, o comando seria:
// ALTER TABLE users ADD COLUMN profile_banner_path VARCHAR(255) NULL;

// Nota: Se você estiver usando SQLite, o comando seria:
// ALTER TABLE users ADD COLUMN profile_banner_path TEXT NULL;
?>
