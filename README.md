# StockWeb

O StockWeb é um sistema web de gerenciamento de estoque. Ele reúne cadastros, vendas, movimentações e relatórios para acompanhar o saldo de produtos e consultar o histórico das operações.

O projeto foi desenvolvido para o Trabalho A3 de PHP. O objetivo acadêmico é demonstrar uma aplicação web completa com persistência relacional, autenticação, operações CRUD e transações que mantêm as vendas e o estoque consistentes.

## Tecnologias

| Tecnologia | Uso no projeto |
| --- | --- |
| PHP 8.3 | Aplicação, validações, sessões e regras de negócio. O Dockerfile usa `php:8.3-apache`. |
| MySQL 8.0 | Banco relacional com tabelas InnoDB, chaves estrangeiras e restrições. |
| PDO e `pdo_mysql` | Conexão e consultas preparadas. O driver é instalado pelo Dockerfile. |
| Bootstrap 5.3.3 | Formulários, tabelas responsivas, componentes e menu Offcanvas. |
| JavaScript, HTML e CSS | Interface, cálculo prévio dos valores da venda e estilos para tela e impressão. |
| Apache HTTP Server 2.4 | Servidor web do container PHP. |
| Docker | Execução isolada dos serviços PHP/Apache e MySQL. |

Ambiente local conferido com PHP 8.3.36, MySQL 8.0.46, Apache 2.4.69 e Docker CLI 29.5.2. Esses números identificam o ambiente verificado. As tags `php:8.3-apache` e `mysql:8.0` podem receber novas versões de correção.

## Funcionalidades

| Módulo | O que permite fazer |
| --- | --- |
| Autenticação | Entrar com e-mail e senha e encerrar a sessão. |
| Dashboard | Consultar produtos ativos, clientes cadastrados, vendas concluídas, alertas de estoque baixo e produtos recentes. |
| Produtos | Cadastrar, consultar, editar e desativar produtos, incluindo preço, saldo, estoque mínimo, categoria e fornecedor. |
| Categorias | Cadastrar, consultar, editar e excluir categorias sem produtos vinculados. |
| Fornecedores | Cadastrar, consultar, editar e excluir fornecedores sem produtos vinculados. |
| Clientes | Cadastrar, consultar, editar e excluir clientes sem vendas vinculadas. |
| Vendas | Selecionar cliente e produtos, informar quantidades, concluir a venda e consultar seus detalhes. |
| Movimentações | Registrar entradas e saídas manuais e consultar o histórico por produto, tipo e período. |
| Relatórios | Consultar estoque, vendas, movimentações e resumo geral com filtros avançados e opção de impressão em PDF pelo navegador. |

## Estrutura do projeto

| Caminho | Responsabilidade |
| --- | --- |
| [Dockerfile](Dockerfile) | Imagem PHP 8.3 com Apache e `pdo_mysql`. |
| [config/database.php](config/database.php) | Lê as variáveis de ambiente e configura a conexão PDO. |
| [database/schema.sql](database/schema.sql) | Cria as oito tabelas, seus relacionamentos e restrições. |
| [database/seed.sql](database/seed.sql) | Carrega dados fictícios para demonstração local. |
| [includes/](includes/) | Layout, sidebar, consultas de relatórios e rotinas compartilhadas de vendas e estoque. |
| [public/](public/) | Raiz pública do Apache. Contém as rotas, telas e formulários. |
| [public/assets/](public/assets/) | JavaScript da venda e estilos da sidebar e da impressão dos relatórios. |

## Pré-requisitos

- Git e Docker com o serviço em execução, como Docker Desktop no macOS ou Windows ou Docker Engine no Linux.
- Navegador moderno e acesso à internet para baixar as imagens Docker e carregar o Bootstrap e o Bootstrap Icons via CDN.
- Terminal Bash ou Zsh. No Windows, os comandos abaixo podem ser usados no WSL com a integração do Docker habilitada.
- Portas locais **8081** e **3308** livres. O cliente MySQL usado no tutorial já existe no container. PHP, Apache e MySQL no computador anfitrião são opcionais.

## Instalação local

O repositório fornece um Dockerfile para o PHP. A rede, o volume e os dois containers são criados com a CLI do Docker. O arquivo `.env` é local e está no `.gitignore`. Os caminhos absolutos usados pelo PHP exigem que a raiz do projeto seja montada em `/var/www/stockweb` e que `public/` seja montado em `/var/www/html`.

Use os passos a seguir em uma instalação nova. Se já houver containers com esses nomes ou um banco StockWeb com dados, use a instalação existente ou separe os nomes, as portas e o volume antes de prosseguir.

### 1. Clonar o repositório

```bash
git clone https://github.com/Mateus0516/StockWeb.git
cd StockWeb
```

Antes de continuar, confirme que a revisão obtida inclui `database/schema.sql` e `database/seed.sql`. Se algum deles faltar, obtenha a revisão da entrega que contém os dois arquivos.

### 2. Configurar o ambiente

Crie um arquivo `.env` na raiz do clone com este conteúdo. Substitua o valor de `DB_PASSWORD` por uma senha local própria, usando um valor compatível com atribuição no Bash ou Zsh, sem espaços ou caracteres especiais do shell.

```dotenv
DB_HOST=stockweb-mysql
DB_NAME=stockweb_db
DB_USER=stockweb_app
DB_PASSWORD=SUBSTITUA_POR_UMA_SENHA_LOCAL_FORTE
```

`DB_HOST` é o nome do container MySQL na rede Docker. O PHP usa a porta interna **3306**. A porta publicada **3308** serve para conexões opcionais a partir do computador anfitrião.

### 3. Criar a rede, o volume e o container MySQL

Na raiz do projeto, carregue as variáveis do arquivo local que você acabou de criar:

```bash
. ./.env
docker network create stockweb-network
docker volume create stockweb-mysql-data
export MYSQL_PASSWORD="$DB_PASSWORD"

docker run -d --name stockweb-mysql \
  --network stockweb-network \
  -p 127.0.0.1:3308:3306 \
  --mount type=volume,source=stockweb-mysql-data,target=/var/lib/mysql \
  -e MYSQL_DATABASE="$DB_NAME" \
  -e MYSQL_USER="$DB_USER" \
  -e MYSQL_PASSWORD \
  -e MYSQL_RANDOM_ROOT_PASSWORD=yes \
  mysql:8.0 --character-set-server=utf8mb4 --collation-server=utf8mb4_0900_ai_ci

unset MYSQL_PASSWORD
```

No primeiro início com um volume vazio, a imagem do MySQL cria `stockweb_db` e o usuário da aplicação a partir das variáveis acima. A senha administrativa é gerada separadamente pela imagem e o PHP utiliza somente o usuário da aplicação.

Confirme a inicialização com o usuário da aplicação. Se o servidor ainda estiver iniciando, aguarde alguns segundos e repita a consulta até ela retornar `stockweb_db`.

```bash
docker exec stockweb-mysql sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" mysql --protocol=TCP -h 127.0.0.1 -u "$MYSQL_USER" "$MYSQL_DATABASE" --batch --skip-column-names -e "SELECT DATABASE()"'
```

### 4. Importar a estrutura e a carga inicial

Execute na raiz do projeto, com o banco recém-criado e vazio. O primeiro comando cria as tabelas e o segundo insere os dados fictícios. Continue para a carga somente após a estrutura ser importada com sucesso.

```bash
docker exec -i stockweb-mysql sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" mysql --default-character-set=utf8mb4 -u "$MYSQL_USER" "$MYSQL_DATABASE"' < database/schema.sql

docker exec -i stockweb-mysql sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" mysql --default-character-set=utf8mb4 -u "$MYSQL_USER" "$MYSQL_DATABASE"' < database/seed.sql
```

A carga deve ser executada uma vez para o banco vazio. Ela cria o administrador fictício, três categorias, três fornecedores, três clientes e seis produtos. Vendas e movimentações começam vazias.

### 5. Iniciar a aplicação

```bash
docker build -t stockweb-php .

docker run -d --name stockweb-php \
  --network stockweb-network \
  --env-file .env \
  -p 127.0.0.1:8081:80 \
  --mount "type=bind,source=$PWD,target=/var/www/stockweb,readonly" \
  --mount "type=bind,source=$PWD/public,target=/var/www/html,readonly" \
  stockweb-php
```

Acesse [http://localhost:8081](http://localhost:8081) e entre com a conta de demonstração abaixo. Se já houver uma sessão autenticada, a página inicial abre o Dashboard. Em usos posteriores, parta da instalação existente com `docker start stockweb-mysql` e depois `docker start stockweb-php`. O volume guarda os dados entre execuções.

## Acesso de demonstração

| Campo | Valor |
| --- | --- |
| E-mail | `admin@stockweb.example` |
| Senha | `StockWeb-A3-Demo!2026` |
| Perfil cadastrado | `ADMIN` |

Essas credenciais fictícias são públicas e exclusivas para demonstração local. Em produção, use credenciais próprias e mantenha a conta de demonstração fora do banco.

## Banco de dados

| Tabela | Dados e relacionamentos |
| --- | --- |
| `usuarios` | Nome, e-mail único, hash de senha e perfil. Um usuário pode registrar várias vendas e movimentações. |
| `categorias` | Nome único e descrição opcional. Uma categoria pode reunir vários produtos. |
| `fornecedores` | Nome, CNPJ único e contatos opcionais. Um fornecedor pode estar vinculado a vários produtos. |
| `clientes` | Nome, CPF único e contatos opcionais. Um cliente pode ter várias vendas. |
| `produtos` | Preço, quantidade atual, estoque mínimo e situação. Cada produto pertence a uma categoria e a um fornecedor. |
| `vendas` | Cliente, usuário responsável, data, valor total e status. Uma venda concluída pelo sistema contém um ou mais itens. |
| `itens_venda` | Venda, produto, quantidade e preço unitário registrado na venda. O MySQL gera o subtotal a partir da quantidade e do preço. |
| `movimentacoes` | Produto, usuário responsável, tipo, quantidade, data e observação. As saídas de vendas identificam a venda na observação. |

A estrutura de referência está em [schema.sql](database/schema.sql). Os dados fictícios estão em [seed.sql](database/seed.sql).

## Regras de negócio

- **Saldo e estoque mínimo.** O saldo atual está em `produtos.quantidade`. O Dashboard sinaliza produtos ativos cujo saldo é menor ou igual ao mínimo.
- **Entradas e saídas.** Entradas somam ao saldo e saídas subtraem. A movimentação manual exige produto ativo e quantidade inteira positiva. Saídas acima do saldo e entradas que ultrapassam o limite da coluna são rejeitadas.
- **Vendas.** O servidor usa os preços do banco e verifica se os produtos estão ativos e têm saldo suficiente. A conclusão grava venda, itens, baixa de estoque e saída no histórico na mesma transação. Cada quantidade vendida é descontada uma vez.
- **Histórico.** Inclui movimentações manuais, saídas de vendas, entradas de estoque inicial pelo cadastro e diferenças de quantidade na edição de produtos. Registros anteriores à integração não são preenchidos retroativamente.
- **Exclusão.** Produtos são desativados. Categorias e fornecedores com produtos vinculados, inclusive inativos, e clientes com vendas vinculadas têm a exclusão bloqueada.
- **Validação e concorrência.** O PHP valida os dados enviados. As operações de estoque usam transações PDO e bloqueios de linha quando leem o saldo para atualizá-lo. Se uma gravação associada falhar, a transação é revertida.

## Relatórios e PDF

| Relatório | Indicadores e filtros |
| --- | --- |
| Produtos e estoque | Saldo, estoque mínimo e situação atual. Filtros por categoria, fornecedor, status e faixa de saldo. |
| Vendas | Quantidade de vendas, unidades, faturamento e ticket médio. Filtros por período, cliente, produto e status. Os indicadores financeiros usam vendas concluídas. O filtro por produto usa os subtotais históricos dos itens correspondentes. |
| Movimentações registradas | Quantidade de registros e totais de entradas e saídas. Filtros por período, produto, tipo e responsável. |
| Resumo geral | Indicadores atuais de produtos, estoque e clientes, além dos totais históricos de vendas e movimentações no período selecionado. |

Para salvar uma listagem, abra **Relatórios**, selecione a aba e aplique os filtros. Clique em **Exportar PDF**, depois em **Imprimir / Salvar como PDF** e escolha **Salvar como PDF** no diálogo do navegador. A versão de impressão usa A4 em paisagem e inclui filtros, resultados, totais e data de geração. Se houver mais de 500 registros, restrinja a consulta antes de imprimir. A geração do arquivo ocorre no navegador.

## Segurança

- O login verifica o hash com `password_verify` e regenera o ID da sessão. As páginas protegidas exigem sessão PHP, e o logout encerra a sessão.
- O banco é acessado por PDO com consultas preparadas e parâmetros para os valores recebidos.
- Formulários que gravam dados usam POST e token CSRF. Os relatórios usam GET e consultas somente leitura.
- O PHP valida campos, quantidades, vínculos e filtros. As telas escapam dados ao renderizar HTML.
- As credenciais do ambiente ficam no `.env` local, ignorado pelo Git. A senha pública do seed é exclusiva para demonstração.

## Documentação acadêmica

- [Modelo Entidade-Relacionamento (MER)](docs/MER.md)
- [Dicionário de dados](docs/DICIONARIO_DADOS.md)
- [Histórias de usuários](docs/HISTORIAS_USUARIOS.md)
- [Protótipo visual](docs/PROTOTIPO.md)
