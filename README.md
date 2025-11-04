# 📚 Sistema de Gestão de Livros

Sistema de gerenciamento de livros desenvolvido com **Laravel 12** e **Livewire 3**, seguindo os princípios de **Domain-Driven Design (DDD)** e **CQRS (Command Query Responsibility Segregation)**.

## 📋 Índice

- [Tecnologias](#tecnologias)
- [Dependências](#dependências)
- [Arquitetura](#arquitetura)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Como Executar](#como-executar)
- [Testes](#testes)
- [Comandos Disponíveis](#comandos-disponíveis)

## 🚀 Tecnologias

- **PHP 8.3**
- **Laravel 12**
- **Livewire 3**
- **MySQL**
- **Docker & Docker Compose**
- **Bootstrap 5**
- **Pest PHP** (Testes Unitários)

## 📦 Dependências

### Principais Dependências

- `laravel/framework`: ^12.0
- `livewire/livewire`: ^3.0
- `barryvdh/laravel-dompdf`: Para geração de relatórios em PDF
- `pestphp/pest`: Framework de testes
- `mockery/mockery`: Para mocks em testes

### Extensões PHP Necessárias

- `pdo_mysql`
- `mbstring`
- `xml`
- `gd`
- `zip`
- `intl`

## 🏗️ Arquitetura

O projeto segue os princípios de **Domain-Driven Design (DDD)** e **CQRS**, organizando o código em camadas bem definidas:

### Camadas da Arquitetura

```
┌─────────────────────────────────────┐
│      Presentation Layer             │
│  (Livewire Components, Views)       │
└─────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│      Application Layer              │
│  (Use Cases: Commands & Queries)    │
└─────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│      Domain Layer                   │
│  (Entities, Value Objects)          │
└─────────────────────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│      Infrastructure Layer           │
│  (Repositories, Eloquent Models)    │
└─────────────────────────────────────┘
```

### Princípios Aplicados

1. **Domain-Driven Design (DDD)**
   - Separação clara entre domínio e infraestrutura
   - Value Objects (VOs) para garantir integridade dos dados
   - Entities para representar entidades de negócio
   - Regras de negócio encapsuladas no domínio

2. **CQRS (Command Query Responsibility Segregation)**
   - Commands: operações que modificam estado (Create, Update, Delete)
   - Queries: operações de leitura (Find, List)
   - Separação clara entre leitura e escrita

3. **Repository Pattern**
   - Interfaces no Application Layer
   - Implementações no Infrastructure Layer
   - Desacoplamento entre domínio e persistência

## 📁 Estrutura do Projeto

```
app/
├── Application/                    # Camada de Aplicação
│   ├── Repository/                 # Interfaces de Repositórios
│   │   ├── AssuntoRepositoryInterface.php
│   │   ├── AutorRepositoryInterface.php
│   │   └── LivroRepositoryInterface.php
│   └── Usecases/                   # Use Cases
│       ├── Commands/               # Comandos (mutação de estado)
│       │   ├── CreateAssuntoCommand.php
│       │   ├── UpdateAssuntoCommand.php
│       │   ├── DeleteAssuntoCommand.php
│       │   ├── CreateAutorCommand.php
│       │   ├── UpdateAutorCommand.php
│       │   ├── DeleteAutorCommand.php
│       │   ├── CreateLivroCommand.php
│       │   ├── UpdateLivroCommand.php
│       │   └── DeleteLivroCommand.php
│       └── Queries/                # Queries (leitura)
│           ├── FindAssuntoByIdQuery.php
│           ├── ListAssuntosQuery.php
│           ├── FindAutorByIdQuery.php
│           ├── ListAutoresQuery.php
│           ├── FindLivroByIdQuery.php
│           └── ListLivrosQuery.php
│
├── Domain/                         # Camada de Domínio
│   ├── Entity/                     # Entidades de Domínio
│   │   ├── Assunto.php
│   │   ├── Autor.php
│   │   └── Livro.php
│   └── VOs/                        # Value Objects
│       ├── DescricaoAssunto.php
│       ├── NomeAutor.php
│       ├── TituloLivro.php
│       ├── NomeEditora.php
│       ├── NumeroEdicao.php
│       ├── AnoPublicacao.php
│       └── ValorLivro.php
│
├── Infrastructure/                  # Camada de Infraestrutura
│   └── Repository/                 # Implementações dos Repositórios
│       ├── AssuntoRepository.php
│       ├── AutorRepository.php
│       └── LivroRepository.php
│
├── Livewire/                       # Componentes Livewire
│   ├── HomePage.php
│   ├── BooksPage.php
│   ├── AuthorsPage.php
│   └── SubjectsPage.php
│
├── Http/                           # Controllers e Middlewares
│   ├── Controllers/
│   │   ├── AssuntoController.php
│   │   ├── AutorController.php
│   │   ├── LivroController.php
│   │   └── ReportController.php
│   └── Resources/
│       └── LivroResource.php
│
└── Models/                         # Eloquent Models (Infraestrutura)
    ├── AssuntoModel.php
    ├── AutorModel.php
    └── LivroModel.php

database/
├── migrations/                     # Migrações do Banco de Dados
└── seeders/                        # Seeders
    └── BibliotecaSeeder.php

resources/
├── views/                          # Views Blade
│   ├── livewire/                   # Views dos Componentes Livewire
│   └── components/                 # Componentes Blade
└── sass/                           # Estilos SCSS
    └── app.scss

tests/
└── Unit/                           # Testes Unitários
    ├── Domain/
    │   ├── Entity/
    │   └── VOs/
    └── Application/
        └── Usecases/
```

## 🚀 Como Executar

### Pré-requisitos

- Docker e Docker Compose instalados
- Make (opcional, mas recomendado)

### Clone repository

**Clone o repositório** (se aplicável)
```bash
git clone <repository-url>
cd livros
```

### Primeira Execução

Na primeira vez que executar o projeto, você pode precisar:

1. **Configurar o arquivo `.env`** (se necessário)
   ```bash
   cp .env.example .env
   ```

2. **Gerar chave da aplicação** (se necessário)
   ```bash
   make artisan cmd="key:generate"
   ```

### Desenvolvimento
**Execute o projeto**
```bash
make up
```
   
   Este comando irá:
   - Subir os containers (PHP, MySQL, Nginx)
   - Aguardar os containers ficarem prontos
   - Executar as migrations automaticamente
   - Executar os seeds automaticamente
   - Deixar o projeto pronto para uso

**Acesse o projeto**
   - Frontend: http://localhost

### Produção

```bash
make up-prod
```

Este comando faz o mesmo que `make up`, mas usando o target de produção.


## 🧪 Testes

### Executar Todos os Testes Unitários

```bash
make test
```

Este comando executa todos os testes unitários usando Pest.

### Estrutura dos Testes

Os testes estão organizados seguindo a mesma estrutura da aplicação:

```
tests/Unit/
├── Domain/
│   ├── Entity/          # Testes das Entidades
│   └── VOs/             # Testes dos Value Objects
└── Application/
    └── Usecases/        # Testes dos Use Cases
        ├── Commands/
        └── Queries/
```

### Cobertura de Testes

- ✅ **Value Objects (VOs)**: 100% coberto
- ✅ **Entities**: 100% coberto
- ✅ **Use Cases (Commands)**: 100% coberto
- ✅ **Use Cases (Queries)**: 100% coberto

**Total: 97 testes passando (176 asserções)**

## 📝 Comandos Disponíveis

### Docker e Containers

```bash
make up              # Sobe containers (dev), executa migrations e seeds
make up-prod         # Sobe containers (prod), executa migrations e seeds
make down            # Para containers
make restart         # Reinicia containers
make logs-app        # Ver logs do PHP
make logs-nginx      # Ver logs do Nginx
make logs-db         # Ver logs do MySQL
make bash            # Acessa shell do container PHP
```

### Laravel Artisan

```bash
make artisan cmd="<comando>"    # Executa comando artisan
make migrate                     # Executa migrations
make seed                       # Executa seeds
make seed-biblioteca           # Executa seeder específico
make fresh                      # Recria banco e executa seeds
make tinker                    # Abre Tinker
```

### Testes

```bash
make test                      # Executa testes unitários
```

### Composer

```bash
make composer cmd="<comando>"  # Executa comando composer
```

### Limpeza

```bash
make prune                     # Remove containers, volumes e cache Docker
```

## 🎯 Funcionalidades

### Gerenciamento de Assuntos
- Criar, editar e excluir assuntos
- Validação de duplicidade (mesma descrição)
- Impedir exclusão quando vinculado a livros

### Gerenciamento de Autores
- Criar, editar e excluir autores
- Validação de duplicidade (mesmo nome)
- Impedir exclusão quando vinculado a livros

### Gerenciamento de Livros
- Criar, editar e excluir livros
- Relacionamento muitos-para-muitos com autores
- Relacionamento muitos-para-muitos com assuntos
- Validação de campos obrigatórios
- Validação de preço (deve ser maior que zero)
- Geração de relatórios em PDF (livros por autor)

### Regras de Negócio

1. **Assuntos**
   - Não pode cadastrar assunto duplicado (mesma descrição)
   - Não pode excluir assunto vinculado a livros

2. **Autores**
   - Não pode cadastrar autor duplicado (mesmo nome)
   - Não pode excluir autor vinculado a livros

3. **Livros**
   - Deve ter pelo menos um autor
   - Deve ter pelo menos um assunto
   - Não pode ter autores duplicados no mesmo livro
   - Não pode ter assuntos duplicados no mesmo livro
   - Valor deve ser maior que zero

## 🔒 Segurança

- Validação de dados no backend
- CSRF protection habilitado
- Sanitização de inputs através de Value Objects
- Transações de banco de dados para operações críticas

## 📊 Banco de Dados

### Estrutura

- **assuntos**: ID, descrição
- **autores**: ID, nome
- **livros**: ID, título, editora, edição, ano de publicação, valor
- **livro_autor**: Tabela pivot (relacionamento muitos-para-muitos)
- **livro_assunto**: Tabela pivot (relacionamento muitos-para-muitos)

### Tamanhos e Tipos

- Todos os campos seguem os tamanhos definidos nas migrations
- Valores são armazenados em centavos (inteiro) para precisão
- Anos são armazenados como strings de 4 caracteres

## 🎨 Frontend

- **Bootstrap 5** para estilização
- **Livewire 3** para componentes reativos
- **Inputmask** para máscaras de entrada
- Validação em tempo real
- Mensagens de feedback (toasts)
- Modais para criação/edição
- Relatórios em PDF
