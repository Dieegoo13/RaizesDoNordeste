<h1 align="center" style="font-weight: bold;">Raízes do Nordeste API 🌵🍽️</h1>

<div align="center">

![Badge](https://img.shields.io/badge/status-em%20desenvolvimento-yellow)
![Badge](https://img.shields.io/badge/Laravel-10-red?logo=laravel&logoColor=white)
![Badge](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)
![Badge](https://img.shields.io/badge/License-MIT-blue)

</div>

<p align="center">
 <a href="#sobre">Sobre</a> • 
 <a href="#funcionalidades">Funcionalidades</a> • 
 <a href="#tecnologias">Tecnologias</a> • 
 <a href="#como-rodar">Como Rodar</a> • 
 <a href="#endpoints">Endpoints</a> •
 <a href="#estrutura">Estrutura</a> •
 <a href="#autor">Autor</a>
</p>

<p align="center">
    <b>API REST desenvolvida com Laravel para a rede de lanchonetes Raízes do Nordeste. O sistema gerencia pedidos, usuários, estoque e fidelidade, oferecendo suporte a múltiplos canais como APP, TOTEM, BALCÃO e WEB.</b>
    <br><br>
    📄 <b>API disponível em:</b> 
    <code>http://localhost:8000/api/documentation</code>
</p>

<p align="center">
    🧪 <b>Collection para testes (Postman/Insomnia):</b><br>
    <code>raizes_postman_collection.json</code>
    <br><br>
    🔗 <a href="https://github.com/Dieegoo13/RaizesDoNordeste/blob/main/raizes_postman_collection.json" target="_blank">
        Acessar colection dos testes no GitHub
    </a>
    <br><br>
    <span>Arquivo disponível na raiz do projeto para facilitar a importação e execução dos endpoints da API.</span>
</p>

---

<h2 id="sobre">📋 Sobre o Projeto</h2>

A **Raízes do Nordeste API** é uma aplicação back-end desenvolvida para simular o funcionamento completo de uma rede de restaurantes.

O sistema foi projetado com foco em escalabilidade e boas práticas, permitindo o gerenciamento de:

- Pedidos em múltiplos canais (APP, WEB, TOTEM, BALCÃO)
- Controle de estoque por unidade
- Sistema de autenticação com **JWT**
- Programa de fidelidade
- Fluxo completo de pedidos

Este projeto foi desenvolvido com o objetivo de praticar:

- Arquitetura RESTful  
- Autenticação com **JWT**  
- Estrutura do **Laravel 10**  
- Integração com banco de dados  
- Boas práticas de back-end  
- Separação de responsabilidades  

---

<h2 id="funcionalidades">🚀 Funcionalidades Principais</h2>

- 🔐 **Autenticação com JWT**
- 👥 **Cadastro e login de usuários**
- 🍔 **Listagem de produtos por unidade**
- 🧾 **Criação e gerenciamento de pedidos**
- 📦 **Controle de estoque**
- 💳 **Simulação de pagamento (mock)**
- 🎯 **Sistema de fidelidade com pontos**
- 🏪 **Suporte a múltiplas unidades**
- 📊 **Painel administrativo (admin/gerente)**

---

<h2 id="tecnologias">💻 Tecnologias Utilizadas</h2>

<div>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/laravel/laravel-original.svg" width="30px" />
  <span>Laravel 10</span>
</div>

<div>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-original.svg" width="30px" />
  <span>PHP 8.1+</span>
</div>

<div>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/mysql/mysql-original.svg" width="30px" />
  <span>MySQL 8+</span>
</div>

<div>
  <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/composer/composer-original.svg" width="30px" />
  <span>Composer</span>
</div>

---

<h2 id="como-rodar">🚀 Como Rodar o Projeto</h2>

## Instalação

### 1. Clonar o repositório

```bash
git clone https://github.com/seu-usuario/raizes-nordeste.git
cd raizes-nordeste
```

### 2. Instalar dependências

```bash
composer install
```

### 3. Configurar variáveis de ambiente

```bash
cp .env.example .env
```

Edite o `.env` com suas credenciais do MySQL.

### 4. Gerar chaves

```bash
php artisan key:generate
php artisan jwt:secret
```

### 5. Criar banco de dados

```sql
CREATE DATABASE raizes_nordeste CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Executar migrations e seed

```bash
php artisan migrate --seed
```

Usuários criados pelo seed:

| Perfil  | E-mail                | Senha    |
|---------|-----------------------|----------|
| ADMIN   | admin@raizes.com      | password |
| GERENTE | gerente@raizes.com    | password |
| COZINHA | cozinha@raizes.com    | password |
| CLIENTE | cliente@raizes.com    | password |

### 7. Iniciar a API

```bash
php artisan serve
```

API disponível em: `http://localhost:8000/api/documentation`

---

## Documentação Swagger

Acesse após iniciar o servidor:

---

## Testes via Postman

1. Importe o arquivo `raizes_postman_collection.json` no Postman
2. Execute na ordem: **Auth → Produtos → Pedidos → Estoque → Fidelidade**
3. Os tokens JWT são capturados automaticamente pelos scripts T01, T02 e T03

### Fluxo crítico

---

## Endpoints

| Método | Rota                           | Auth    | Descrição                     |
|--------|--------------------------------|---------|-------------------------------|
| POST   | /auth/login                    | Público | Login JWT                     |
| POST   | /auth/logout                   | JWT     | Logout                        |
| POST   | /usuarios                      | Público | Cadastro de cliente           |
| GET    | /unidades                      | Público | Listar unidades               |
| GET    | /unidades/{id}                 | Público | Detalhar unidade              |
| GET    | /produtos?unidade_id=1         | Público | Cardápio por unidade          |
| GET    | /produtos/{id}                 | Público | Detalhar produto              |
| POST   | /pedidos                       | JWT     | Criar pedido (fluxo crítico)  |
| GET    | /pedidos                       | JWT/ADM | Listar pedidos com filtros    |
| GET    | /pedidos/{id}                  | JWT     | Detalhar pedido               |
| PATCH  | /pedidos/{id}/status           | JWT/ADM | Atualizar status do pedido    |
| GET    | /estoque?unidade_id=1          | ADM/GER | Consultar estoque             |
| POST   | /estoque/movimentacao          | ADM/GER | Entrada/saída de estoque      |
| GET    | /fidelidade/saldo/{id}         | JWT     | Saldo de pontos               |
| GET    | /fidelidade/historico/{id}     | JWT     | Histórico de pontos           |
| POST   | /fidelidade/resgatar           | JWT     | Resgatar pontos               |

---

## Arquitetura

---

## Estrutura do Banco

8 tabelas: `users`, `unidades`, `produtos`, `estoque`, `pedidos`, `pedido_itens`, `pagamentos`, `fidelidade_pontos`
