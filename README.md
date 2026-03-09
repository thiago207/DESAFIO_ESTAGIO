# 🛒 E-Commerce Simulado — 

> Projeto desenvolvido como desafio prático, simulando um sistema completo de e-commerce com back-end em PHP e banco de dados relacional.

---

## 📌 Sobre o Projeto

Este sistema simula o fluxo completo de uma loja virtual, cobrindo desde o cadastro de clientes e produtos até a finalização de pedidos com aplicação de cupons de desconto. Todo o ciclo foi desenvolvido com foco em boas práticas de organização de código, separação de responsabilidades (MVC) e integração dinâmica com o banco de dados.

---

## ✅ Funcionalidades

| Módulo | Descrição |
|---|---|
| 👤 **Clientes** | Cadastro, listagem e gerenciamento de clientes da loja |
| 🏪 **Loja** | Configuração e dados gerais da loja |
| 📦 **Produtos** | Adição, edição e listagem de produtos com preço e estoque |
| 🎟️ **Cupons** | Criação e aplicação de cupons de desconto nos pedidos |
| 🧾 **Pedidos** | Realização de pedidos com seleção de produtos e cupons, registrados no banco |

---

## 🛠️ Tecnologias Utilizadas

**Back-end**
- PHP 8
- CodeIgniter 4 (Framework MVC)

**Banco de Dados**
- MySQL
- SQL — modelagem e consultas relacionais

**Front-end**
- HTML5 & CSS3
- JavaScript
- jQuery & AJAX — comunicação assíncrona com o servidor

---

## 🗂️ Estrutura do Projeto (MVC — CodeIgniter 4)

```
/app
├── Controllers/       # Lógica de controle das rotas e requisições
├── Models/            # Comunicação com o banco de dados
├── Views/             # Interfaces HTML renderizadas pelo servidor
└── Config/
    └── Routes.php     # Definição das rotas da aplicação
/public                # Ponto de entrada da aplicação (index.php)
```

---

## ⚙️ Como Executar Localmente

### Pré-requisitos

- PHP >= 8.0
- MySQL
- Composer
- Servidor local (XAMPP, Laragon ou similar)

### Passos

```bash
# 1. Clone o repositório
git clone https://github.com/seu-usuario/nome-do-repositorio.git

# 2. Acesse a pasta do projeto
cd nome-do-repositorio

# 3. Instale as dependências do CodeIgniter
composer install

# 4. Configure o banco de dados
# Copie o arquivo de ambiente
cp env .env

# Edite o .env com suas credenciais
# database.default.hostname = localhost
# database.default.database = nome_do_banco
# database.default.username = root
# database.default.password = sua_senha
```

```bash
# 5. Importe o banco de dados
# Execute o arquivo .sql disponível na pasta /database no seu MySQL

# 6. Inicie o servidor
php spark serve
```

Acesse em: `http://localhost:8080`

---

## 🗄️ Banco de Dados

O projeto utiliza MySQL com as seguintes tabelas principais:

- `clientes` — dados dos clientes cadastrados
- `produtos` — catálogo de produtos da loja
- `cupons` — cupons de desconto disponíveis
- `pedidos` — cabeçalho dos pedidos realizados
- `pedido_itens` — itens vinculados a cada pedido

---

## 💡 Aprendizados

Durante o desenvolvimento deste projeto foram aplicados e consolidados conhecimentos em:

- Arquitetura **MVC** com CodeIgniter 4
- Criação de **rotas RESTful** e controllers organizados
- **AJAX + jQuery** para atualização dinâmica de elementos sem recarregar a página
- **SQL** para consultas, joins e integridade referencial entre tabelas
- Organização de formulários HTML com validação no back-end

---

## 👨‍💻 Autor

**Thiago Felipe Ribeiro Brito**
Estudante de Ciência da Computação — UNICAP

[![LinkedIn](https://img.shields.io/badge/LinkedIn-blue?style=flat&logo=linkedin)](https://linkedin.com/in/seu-perfil)
[![GitHub](https://img.shields.io/badge/GitHub-black?style=flat&logo=github)](https://github.com/seu-usuario)
