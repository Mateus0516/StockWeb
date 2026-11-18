# Dicionário de Dados — StockWeb

O banco de dados do StockWeb reúne cadastros, informações de estoque, vendas e movimentações. Este dicionário descreve as oito tabelas definidas em [database/schema.sql](../database/schema.sql) e complementa o [Modelo Entidade-Relacionamento](MER.md).

As tabelas usam InnoDB, conjunto de caracteres `utf8mb4` e collation `utf8mb4_0900_ai_ci`. A coluna **Valor padrão** reproduz os valores explícitos do DDL. **Sem DEFAULT** indica a ausência da cláusula `DEFAULT`, inclusive em campos que permitem `NULL`. Os valores produzidos por `AUTO_INCREMENT` ou por expressão são descritos separadamente.

## `usuarios`

Cadastro e dados de autenticação dos usuários.

| Campo | Tipo SQL | Permite NULL? | Chave (PK/FK/UNIQUE) | Valor padrão | Descrição |
| --- | --- | --- | --- | --- | --- |
| `id` | `int` | Não | PK | Sem DEFAULT | Identificador do usuário. |
| `nome` | `varchar(150)` | Não | — | Sem DEFAULT | Nome do usuário. |
| `email` | `varchar(150)` | Não | UNIQUE | Sem DEFAULT | E-mail do usuário. |
| `senha_hash` | `varchar(255)` | Não | — | Sem DEFAULT | Hash da senha. |
| `perfil` | `varchar(20)` | Não | — | `'VENDEDOR'` | Perfil do usuário. |

- **Chave primária e índice PRIMARY.** `id` com `AUTO_INCREMENT`.
- **Índice único** `email` em `email`.
- **Restrição** `chk_usuario_perfil` com ``CHECK ((`perfil` in (_utf8mb4'ADMIN',_utf8mb4'VENDEDOR')))``.

## `categorias`

Classificação dos produtos.

| Campo | Tipo SQL | Permite NULL? | Chave (PK/FK/UNIQUE) | Valor padrão | Descrição |
| --- | --- | --- | --- | --- | --- |
| `id` | `int` | Não | PK | Sem DEFAULT | Identificador da categoria. |
| `nome` | `varchar(100)` | Não | UNIQUE | Sem DEFAULT | Nome da categoria. |
| `descricao` | `text` | Sim | — | Sem DEFAULT | Descrição da categoria. |

- **Chave primária e índice PRIMARY.** `id` com `AUTO_INCREMENT`.
- **Índice único** `nome` em `nome`.

## `fornecedores`

Cadastro dos fornecedores de produtos.

| Campo | Tipo SQL | Permite NULL? | Chave (PK/FK/UNIQUE) | Valor padrão | Descrição |
| --- | --- | --- | --- | --- | --- |
| `id` | `int` | Não | PK | Sem DEFAULT | Identificador do fornecedor. |
| `nome` | `varchar(150)` | Não | — | Sem DEFAULT | Nome do fornecedor. |
| `cnpj` | `varchar(14)` | Não | UNIQUE | Sem DEFAULT | CNPJ do fornecedor. |
| `email` | `varchar(150)` | Sim | — | `NULL` | E-mail de contato. |
| `telefone` | `varchar(20)` | Sim | — | `NULL` | Telefone de contato. |

- **Chave primária e índice PRIMARY.** `id` com `AUTO_INCREMENT`.
- **Índice único** `cnpj` em `cnpj`.

## `clientes`

Cadastro dos clientes associados às vendas.

| Campo | Tipo SQL | Permite NULL? | Chave (PK/FK/UNIQUE) | Valor padrão | Descrição |
| --- | --- | --- | --- | --- | --- |
| `id` | `int` | Não | PK | Sem DEFAULT | Identificador do cliente. |
| `nome` | `varchar(150)` | Não | — | Sem DEFAULT | Nome do cliente. |
| `cpf` | `varchar(11)` | Não | UNIQUE | Sem DEFAULT | CPF do cliente. |
| `email` | `varchar(150)` | Sim | — | `NULL` | E-mail de contato. |
| `telefone` | `varchar(20)` | Sim | — | `NULL` | Telefone de contato. |

- **Chave primária e índice PRIMARY.** `id` com `AUTO_INCREMENT`.
- **Índice único** `cpf` em `cpf`.

## `produtos`

Cadastro de produtos, preços e saldos de estoque.

| Campo | Tipo SQL | Permite NULL? | Chave (PK/FK/UNIQUE) | Valor padrão | Descrição |
| --- | --- | --- | --- | --- | --- |
| `id` | `int` | Não | PK | Sem DEFAULT | Identificador do produto. |
| `nome` | `varchar(150)` | Não | — | Sem DEFAULT | Nome do produto. |
| `descricao` | `text` | Sim | — | Sem DEFAULT | Descrição do produto. |
| `preco` | `decimal(10,2)` | Não | — | Sem DEFAULT | Preço do produto. |
| `quantidade` | `int` | Não | — | `'0'` | Quantidade atual em estoque. |
| `estoque_minimo` | `int` | Não | — | `'5'` | Quantidade mínima de referência. |
| `categoria_id` | `int` | Não | FK | Sem DEFAULT | Categoria do produto. |
| `fornecedor_id` | `int` | Não | FK | Sem DEFAULT | Fornecedor do produto. |
| `ativo` | `tinyint(1)` | Não | — | `'1'` | Indicador de situação ativa. |

- **Chave primária e índice PRIMARY.** `id` com `AUTO_INCREMENT`.
- **Índice não único** `fk_produto_categoria` em `categoria_id`.
- **Índice não único** `fk_produto_fornecedor` em `fornecedor_id`.
- **Chave estrangeira** `fk_produto_categoria` de `categoria_id` para `categorias.id`.
- **Chave estrangeira** `fk_produto_fornecedor` de `fornecedor_id` para `fornecedores.id`.
- **Restrição** `chk_produto_preco` com ``CHECK ((`preco` >= 0))``.
- **Restrição** `chk_produto_quantidade` com ``CHECK ((`quantidade` >= 0))``.

## `vendas`

Dados gerais das vendas.

| Campo | Tipo SQL | Permite NULL? | Chave (PK/FK/UNIQUE) | Valor padrão | Descrição |
| --- | --- | --- | --- | --- | --- |
| `id` | `int` | Não | PK | Sem DEFAULT | Identificador da venda. |
| `cliente_id` | `int` | Não | FK | Sem DEFAULT | Cliente associado à venda. |
| `usuario_id` | `int` | Não | FK | Sem DEFAULT | Usuário responsável pelo registro. |
| `data_venda` | `datetime` | Não | — | `CURRENT_TIMESTAMP` | Data e horário da venda. |
| `valor_total` | `decimal(10,2)` | Não | — | `'0.00'` | Valor total da venda. |
| `status` | `varchar(20)` | Não | — | `'PENDENTE'` | Situação da venda. |

- **Chave primária e índice PRIMARY.** `id` com `AUTO_INCREMENT`.
- **Índice não único** `fk_venda_cliente` em `cliente_id`.
- **Índice não único** `fk_venda_usuario` em `usuario_id`.
- **Chave estrangeira** `fk_venda_cliente` de `cliente_id` para `clientes.id`.
- **Chave estrangeira** `fk_venda_usuario` de `usuario_id` para `usuarios.id`.
- **Restrição** `chk_venda_status` com ``CHECK ((`status` in (_utf8mb4'PENDENTE',_utf8mb4'CONCLUIDA',_utf8mb4'CANCELADA')))``.
- **Restrição** `chk_venda_valor` com ``CHECK ((`valor_total` >= 0))``.

## `itens_venda`

Produtos associados às vendas, com quantidade e preço unitário por item.

| Campo | Tipo SQL | Permite NULL? | Chave (PK/FK/UNIQUE) | Valor padrão | Descrição |
| --- | --- | --- | --- | --- | --- |
| `id` | `int` | Não | PK | Sem DEFAULT | Identificador do item. |
| `venda_id` | `int` | Não | FK | Sem DEFAULT | Venda à qual o item pertence. |
| `produto_id` | `int` | Não | FK | Sem DEFAULT | Produto do item. |
| `quantidade` | `int` | Não | — | Sem DEFAULT | Quantidade do produto no item. |
| `preco_unitario` | `decimal(10,2)` | Não | — | Sem DEFAULT | Preço unitário registrado no item. |
| `subtotal` | `decimal(10,2)` | Sim | — | Sem DEFAULT | Subtotal calculado a partir da quantidade e do preço unitário. |

- **Chave primária e índice PRIMARY.** `id` com `AUTO_INCREMENT`.
- **Índice não único** `fk_item_venda` em `venda_id`.
- **Índice não único** `fk_item_produto` em `produto_id`.
- **Chave estrangeira** `fk_item_produto` de `produto_id` para `produtos.id`.
- **Chave estrangeira** `fk_item_venda` de `venda_id` para `vendas.id`.
- **Restrição** `chk_item_preco` com ``CHECK ((`preco_unitario` >= 0))``.
- **Restrição** `chk_item_quantidade` com ``CHECK ((`quantidade` > 0))``.
- **Coluna gerada** `subtotal` com `GENERATED ALWAYS AS ((quantidade * preco_unitario)) STORED`.

## `movimentacoes`

Histórico de entradas e saídas de produtos no estoque.

| Campo | Tipo SQL | Permite NULL? | Chave (PK/FK/UNIQUE) | Valor padrão | Descrição |
| --- | --- | --- | --- | --- | --- |
| `id` | `int` | Não | PK | Sem DEFAULT | Identificador da movimentação. |
| `produto_id` | `int` | Não | FK | Sem DEFAULT | Produto movimentado. |
| `usuario_id` | `int` | Não | FK | Sem DEFAULT | Usuário responsável pelo registro. |
| `tipo` | `varchar(20)` | Não | — | Sem DEFAULT | Tipo de movimentação. |
| `quantidade` | `int` | Não | — | Sem DEFAULT | Quantidade movimentada. |
| `data_movimentacao` | `datetime` | Não | — | `CURRENT_TIMESTAMP` | Data e horário da movimentação. |
| `observacao` | `text` | Sim | — | Sem DEFAULT | Observação sobre a movimentação. |

- **Chave primária e índice PRIMARY.** `id` com `AUTO_INCREMENT`.
- **Índice não único** `fk_movimentacao_produto` em `produto_id`.
- **Índice não único** `fk_movimentacao_usuario` em `usuario_id`.
- **Chave estrangeira** `fk_movimentacao_produto` de `produto_id` para `produtos.id`.
- **Chave estrangeira** `fk_movimentacao_usuario` de `usuario_id` para `usuarios.id`.
- **Restrição** `chk_movimentacao_quantidade` com ``CHECK ((`quantidade` > 0))``.
- **Restrição** `chk_movimentacao_tipo` com ``CHECK ((`tipo` in (_utf8mb4'ENTRADA',_utf8mb4'SAIDA')))``.

## Relacionamentos

| Chave estrangeira | Referência | Cardinalidade |
| --- | --- | --- |
| `produtos.categoria_id` | `categorias.id` | Um registro pai para zero ou muitos dependentes |
| `produtos.fornecedor_id` | `fornecedores.id` | Um registro pai para zero ou muitos dependentes |
| `vendas.cliente_id` | `clientes.id` | Um registro pai para zero ou muitos dependentes |
| `vendas.usuario_id` | `usuarios.id` | Um registro pai para zero ou muitos dependentes |
| `itens_venda.produto_id` | `produtos.id` | Um registro pai para zero ou muitos dependentes |
| `itens_venda.venda_id` | `vendas.id` | Um registro pai para zero ou muitos dependentes |
| `movimentacoes.produto_id` | `produtos.id` | Um registro pai para zero ou muitos dependentes |
| `movimentacoes.usuario_id` | `usuarios.id` | Um registro pai para zero ou muitos dependentes |

Cada registro dependente referencia exatamente um registro pai por chave estrangeira. `itens_venda` associa `vendas` a `produtos`. O esquema permite que uma venda exista sem itens. `movimentacoes` referencia `produtos` e `usuarios`.

## Regras de integridade

- As chaves primárias identificam cada linha de forma única. Os índices `UNIQUE` impedem a duplicação de `usuarios.email`, `categorias.nome`, `fornecedores.cnpj` e `clientes.cpf`.
- As oito chaves estrangeiras são obrigatórias e apontam para registros existentes. O DDL omite ações explícitas de `ON DELETE` e `ON UPDATE`. No InnoDB, a ação padrão impede a exclusão do registro pai ou a alteração da chave referenciada enquanto houver dependentes.
- Os `CHECK` limitam perfis, status de vendas e tipos de movimentação aos valores declarados. O saldo de produtos e os valores monetários indicados nas restrições devem ser não negativos. A quantidade de itens de venda e de movimentações deve ser positiva.
- O subtotal do item é calculado pelo banco e armazenado como coluna gerada.
