<?php
// Arquivo: create_google_reviews_table.php
// Este arquivo contém a definição da tabela google_reviews para inserção manual

class CreateGoogleReviewsTable
{
    /**
     * Retorna o SQL para criação da tabela google_reviews
     *
     * @return string
     */
    public static function getSQL()
    {
        return "
        CREATE TABLE `google_reviews` (
          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `place_id` varchar(255) NOT NULL,
          `name` varchar(255) NOT NULL,
          `rating` decimal(3,1) NOT NULL DEFAULT 0.0,
          `reviews_count` int(11) NOT NULL DEFAULT 0,
          `last_updated` timestamp NULL DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `google_reviews_place_id_unique` (`place_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
    }
}
