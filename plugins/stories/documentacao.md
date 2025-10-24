# Documentação do Plugin Stories

## Visão Geral
O plugin Stories permite que os usuários publiquem conteúdo temporário (stories) que ficam visíveis por 24 horas. Funcionalidade similar aos stories do Instagram, Facebook e outras redes sociais.

## Estrutura do Plugin
O plugin segue a estrutura padrão de plugins do sistema, organizado da seguinte forma:

```
plugins/
└── stories/
    ├── StoriesController.php
    ├── routes.php
    ├── create_user_stories_table.php
    ├── Providers/
    │   └── StoriesServiceProvider.php
    └── views/
        ├── index.blade.php
        ├── create.blade.php
        ├── show.blade.php
        └── user.blade.php
```

## Componentes Principais

### 1. Controller (StoriesController.php)
Gerencia todas as operações relacionadas aos stories:
- `index()`: Exibe os stories ativos do usuário logado
- `create()`: Exibe o formulário para criar um novo story
- `store()`: Processa o upload e armazena um novo story
- `show()`: Exibe um story específico
- `destroy()`: Remove um story
- `userStories()`: Exibe os stories ativos de um usuário específico

O controller implementa a lógica de expiração automática de 24 horas, filtrando stories com base na data de criação.

### 2. Rotas (routes.php)
Define as rotas para acessar as funcionalidades do plugin:
- GET `/stories`: Lista os stories do usuário logado
- GET `/stories/create`: Formulário para criar um novo story
- POST `/stories`: Salva um novo story
- GET `/stories/{id}`: Visualiza um story específico
- DELETE `/stories/{id}`: Remove um story
- GET `/user/{username}/stories`: Visualiza os stories de um usuário específico

Todas as rotas estão protegidas pelo middleware de autenticação.

### 3. Migration (create_user_stories_table.php)
Define a estrutura da tabela `user_stories` no banco de dados:
- `id`: Identificador único do story
- `user_id`: ID do usuário que criou o story (chave estrangeira)
- `image_path`: Caminho da imagem do story
- `caption`: Legenda opcional do story
- `created_at` e `updated_at`: Timestamps para controle de criação e expiração

### 4. Service Provider (StoriesServiceProvider.php)
Registra o plugin no sistema:
- Registra o binding do controller
- Carrega as rotas do plugin
- Registra as views do plugin
- Define os arquivos que podem ser publicados (views e migration)

### 5. Views
- `index.blade.php`: Exibe a lista de stories ativos do usuário
- `create.blade.php`: Formulário para criar um novo story
- `show.blade.php`: Exibe um story específico com detalhes
- `user.blade.php`: Exibe os stories ativos de um usuário específico

## Funcionalidades

1. **Publicação de Stories**
   - Upload de imagens para stories
   - Adição de legendas opcionais
   - Armazenamento em diretório dedicado

2. **Expiração Automática**
   - Stories ficam visíveis por apenas 24 horas
   - Filtragem automática de stories expirados

3. **Gerenciamento de Stories**
   - Visualização de stories próprios
   - Exclusão de stories
   - Visualização de stories de outros usuários

4. **Integração com o Sistema de Usuários**
   - Stories vinculados a contas de usuários
   - Proteção por autenticação
