# Raízes do Nordeste — Back-End API

API REST desenvolvida com **Laravel 10 + MySQL + JWT** para a rede de lanchonetes Raízes do Nordeste.

Suporta múltiplos canais (APP, TOTEM, BALCAO, PICKUP, WEB), controle de estoque por unidade, pagamento via mock e programa de fidelidade com conformidade LGPD.

---

## Tecnologias

| Tecnologia | Versão  |
|------------|---------|
| PHP        | 8.1+    |
| Laravel    | 10.x    |
| MySQL      | 8.0+    |
| JWT Auth   | 2.x     |

---

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

API disponível em: `http://localhost:8000/api`

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

git init
git add .
git commit -m "feat: setup inicial do projeto Laravel 10"

git add database/migrations/
git commit -m "feat: 8 migrations com FKs e constraints"

git add app/Models/
git commit -m "feat: 8 models Eloquent com relacionamentos"

git add app/Services/
git commit -m "feat: services PedidoService, PagamentoMock, Fidelidade e Estoque"

git add app/Http/
git commit -m "feat: controllers REST com JWT e autorização por perfil"

git add database/seeders/ public/api-docs.json resources/views/swagger.blade.php
git commit -m "feat: seeder, Swagger UI e coleção Postman"

git remote add origin https://github.com/seu-usuario/raizes-nordeste.git
git branch -M main
git push -u origin main