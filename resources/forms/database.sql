-- Script para criação do banco de dados e tabela de participantes da promoção

CREATE DATABASE IF NOT EXISTS promocao_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE promocao_db;

-- Tabela para armazenar os participantes da promoção
CREATE TABLE IF NOT EXISTS participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    whatsapp VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    instagram VARCHAR(255) NOT NULL UNIQUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_cadastro VARCHAR(45),
    user_agent TEXT,
    INDEX idx_cpf (cpf),
    INDEX idx_instagram (instagram),
    INDEX idx_data_cadastro (data_cadastro)
);

-- Inserir alguns dados de exemplo para teste
INSERT INTO participantes (nome, cpf, whatsapp, email, instagram) VALUES 
('João Silva', '123.456.789-00', '(11) 99999-9999', 'joao@email.com', '@joaosilva'),
('Maria Santos', '987.654.321-00', '(11) 88888-8888', 'maria@email.com', '@mariasantos');

