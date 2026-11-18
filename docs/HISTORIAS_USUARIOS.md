# Histórias de Usuários — StockWeb

O StockWeb é um sistema web de gerenciamento de estoque desenvolvido para o Trabalho A3 de PHP. As histórias a seguir descrevem os fluxos disponíveis e definem critérios que podem ser verificados na interface ou nos registros gerados.

## Perfis utilizados

A autenticação armazena o perfil da conta na sessão e o Dashboard exibe esse valor. Os perfis cadastrados no modelo são `ADMIN` e `VENDEDOR`. As rotas funcionais verificam se a sessão está autenticada. Como a implementação oferece os mesmos fluxos aos dois perfis, as histórias usam **usuário autenticado** como papel comum. Para o login, o papel é **usuário com conta cadastrada**. [Autenticação](../public/autenticar.php) · [Dashboard](../public/dashboard.php)

## Autenticação

### US01 — Entrar no sistema

Como usuário com conta cadastrada, quero entrar com meu e-mail e minha senha, para acessar os módulos do StockWeb.

**Critérios de aceitação**

- Com e-mail e senha válidos, o sistema inicia a sessão, regenera seu identificador e abre o Dashboard.
- Com e-mail ou senha inválidos, o sistema retorna à tela de login sem autenticar o usuário.
- Ao abrir uma página protegida sem sessão, o usuário é encaminhado ao login.

**Arquivos PHP**

[login.php](../public/login.php), [autenticar.php](../public/autenticar.php), [dashboard.php](../public/dashboard.php)

### US02 — Encerrar sessão

Como usuário autenticado, quero sair pelo menu lateral, para encerrar meu acesso ao sistema.

**Critérios de aceitação**

- A ação Sair encerra a sessão e redireciona para o login.
- Ao solicitar novamente uma página protegida após sair, o sistema volta a exigir autenticação.

**Arquivos PHP**

[logout.php](../public/logout.php), [dashboard.php](../public/dashboard.php), [sidebar.php](../includes/sidebar.php)

## Dashboard

### US03 — Acompanhar os indicadores

Como usuário autenticado, quero ver um resumo do estoque e das vendas, para identificar produtos que precisam de atenção.

**Critérios de aceitação**

- O painel mostra as contagens de produtos ativos, clientes cadastrados e vendas concluídas.
- Produtos ativos com saldo menor ou igual ao estoque mínimo entram na contagem de estoque baixo.
- A tabela mostra até dez produtos ativos, do maior para o menor ID.

**Arquivos PHP**

[dashboard.php](../public/dashboard.php)

## Produtos

### US04 — Gerenciar produtos

Como usuário autenticado, quero cadastrar, consultar, editar e excluir produtos, para manter atualizados os itens do estoque.

**Critérios de aceitação**

- Um cadastro válido com categoria e fornecedor existentes aparece na lista de produtos ativos.
- O formulário de edição carrega o produto e as alterações válidas aparecem na listagem.
- Estoque inicial positivo gera uma entrada. Na edição, um aumento gera entrada e uma redução gera saída pela diferença. Saldo inalterado não gera movimentação.
- Confirmar a exclusão desativa o produto e o retira da lista de ativos, preservando seu registro.

**Arquivos PHP**

[produto_novo.php](../public/produto_novo.php), [produto_salvar.php](../public/produto_salvar.php), [produtos.php](../public/produtos.php), [produto_editar.php](../public/produto_editar.php), [produto_atualizar.php](../public/produto_atualizar.php), [produto_excluir.php](../public/produto_excluir.php)

## Categorias

### US05 — Gerenciar categorias

Como usuário autenticado, quero cadastrar, consultar, editar e excluir categorias, para organizar os produtos.

**Critérios de aceitação**

- É possível salvar uma categoria com nome válido e descrição opcional, consultá-la na lista e editar seus dados.
- Um nome já usado por outra categoria é rejeitado no cadastro ou na edição.
- Uma categoria sem produtos vinculados pode ser excluída. A exclusão é bloqueada se houver vínculo com qualquer produto, inclusive inativo.

**Arquivos PHP**

[categoria_nova.php](../public/categoria_nova.php), [categoria_salvar.php](../public/categoria_salvar.php), [categorias.php](../public/categorias.php), [categoria_editar.php](../public/categoria_editar.php), [categoria_atualizar.php](../public/categoria_atualizar.php), [categoria_excluir.php](../public/categoria_excluir.php)

## Fornecedores

### US06 — Gerenciar fornecedores

Como usuário autenticado, quero cadastrar, consultar, editar e excluir fornecedores, para manter a origem dos produtos atualizada.

**Critérios de aceitação**

- É possível cadastrar e editar um fornecedor com nome e CNPJ válidos e consultar os dados na listagem. E-mail e telefone são opcionais.
- O sistema normaliza o CNPJ aceito e rejeita um CNPJ já usado por outro fornecedor.
- Um fornecedor sem produtos vinculados pode ser excluído. Qualquer vínculo com produto impede a exclusão.

**Arquivos PHP**

[fornecedor_novo.php](../public/fornecedor_novo.php), [fornecedor_salvar.php](../public/fornecedor_salvar.php), [fornecedores.php](../public/fornecedores.php), [fornecedor_editar.php](../public/fornecedor_editar.php), [fornecedor_atualizar.php](../public/fornecedor_atualizar.php), [fornecedor_excluir.php](../public/fornecedor_excluir.php)

## Clientes

### US07 — Gerenciar clientes

Como usuário autenticado, quero cadastrar, consultar, editar e excluir clientes, para associar vendas aos clientes corretos.

**Critérios de aceitação**

- É possível cadastrar e editar nome e CPF e consultar os dados na listagem. E-mail e telefone são opcionais.
- O sistema exige CPF com 11 dígitos após normalizar a pontuação e rejeita CPF já usado por outro cliente.
- Um cliente sem vendas vinculadas pode ser excluído. Qualquer venda vinculada impede a exclusão.

**Arquivos PHP**

[cliente_novo.php](../public/cliente_novo.php), [cliente_salvar.php](../public/cliente_salvar.php), [clientes.php](../public/clientes.php), [cliente_editar.php](../public/cliente_editar.php), [cliente_atualizar.php](../public/cliente_atualizar.php), [cliente_excluir.php](../public/cliente_excluir.php)

## Vendas

### US08 — Registrar uma venda

Como usuário autenticado, quero concluir uma venda para um cliente com um ou mais produtos, para registrar os valores vendidos e atualizar o estoque.

**Critérios de aceitação**

- O formulário exige um cliente existente, pelo menos um produto e quantidade inteira positiva em cada item.
- O servidor recalcula os valores com os preços do banco. Produtos inativos e quantidades acumuladas acima do saldo impedem a conclusão.
- A venda concluída grava os itens e o preço unitário de cada produto, baixa o saldo uma vez por produto e registra uma saída com referência à venda.
- Uma falha na gravação reverte a venda, seus itens, as saídas e as alterações de estoque.

**Arquivos PHP**

[venda_nova.php](../public/venda_nova.php), [venda_salvar.php](../public/venda_salvar.php), [venda_registro.php](../includes/venda_registro.php), [movimentacao_registro.php](../includes/movimentacao_registro.php)

### US09 — Consultar vendas e seus itens

Como usuário autenticado, quero abrir a lista de vendas e consultar os detalhes de uma venda, para conferir o que foi registrado.

**Critérios de aceitação**

- A lista mostra número, data, cliente, status e total da venda e oferece a ação Detalhes.
- Os detalhes mostram cliente, responsável, data, status, produtos, quantidades, preços registrados, subtotais e total.
- Um identificador inexistente retorna à listagem com mensagem de venda não encontrada.

**Arquivos PHP**

[vendas.php](../public/vendas.php), [venda_detalhes.php](../public/venda_detalhes.php)

## Movimentações de estoque

### US10 — Registrar entrada

Como usuário autenticado, quero lançar uma entrada de produto ativo, para atualizar o saldo e registrar o recebimento.

**Critérios de aceitação**

- Selecionar um produto ativo no formulário exibe seu saldo atual.
- Uma entrada com quantidade inteira positiva soma essa quantidade ao saldo e registra uma movimentação ENTRADA com o usuário da sessão.
- Uma entrada que ultrapasse o limite do campo de quantidade é recusada, e uma falha na gravação reverte o saldo e o histórico.

**Arquivos PHP**

[movimentacao_nova.php](../public/movimentacao_nova.php), [movimentacao_salvar.php](../public/movimentacao_salvar.php), [movimentacao_registro.php](../includes/movimentacao_registro.php)

### US11 — Registrar saída

Como usuário autenticado, quero lançar uma saída de produto ativo, para registrar uma retirada manual de estoque.

**Critérios de aceitação**

- Uma saída com quantidade inteira positiva e saldo suficiente subtrai a quantidade e registra uma movimentação SAIDA com o usuário da sessão.
- Uma saída acima do saldo é recusada e deixa o estoque e o histórico intactos.
- A observação informada pode ser consultada no histórico após o lançamento.

**Arquivos PHP**

[movimentacao_nova.php](../public/movimentacao_nova.php), [movimentacao_salvar.php](../public/movimentacao_salvar.php), [movimentacao_registro.php](../includes/movimentacao_registro.php)

### US12 — Consultar o histórico de movimentações

Como usuário autenticado, quero consultar as entradas e saídas registradas, para rastrear alterações no estoque.

**Critérios de aceitação**

- A lista mostra data e horário, produto, tipo, quantidade, responsável e observação.
- Os filtros por produto, tipo e período podem ser combinados e retornam somente os registros correspondentes.
- Uma data inválida ou um intervalo invertido apresenta mensagem de erro.

**Arquivos PHP**

[movimentacoes.php](../public/movimentacoes.php)

## Relatórios

### US13 — Analisar produtos e estoque

Como usuário autenticado, quero filtrar o estoque atual por categoria, fornecedor, status e situação do saldo, para localizar produtos que precisam de reposição.

**Critérios de aceitação**

- Os filtros podem ser combinados e cada linha apresenta produto, categoria, fornecedor, saldo, mínimo e situação.
- O indicador de estoque baixo conta produtos ativos com saldo menor ou igual ao mínimo.
- Uma consulta sem registros mostra uma mensagem de resultado vazio.

**Arquivos PHP**

[relatorios.php](../public/relatorios.php), [relatorios_consultas.php](../includes/relatorios_consultas.php), [relatorios_visual.php](../includes/relatorios_visual.php)

### US14 — Analisar vendas

Como usuário autenticado, quero combinar filtros de período, cliente, produto e status, para conferir vendas e faturamento no recorte escolhido.

**Critérios de aceitação**

- O relatório exibe somente vendas que correspondem aos filtros combinados e conta cada venda uma vez.
- Com filtro por produto, o valor da linha soma apenas os subtotais históricos dos itens desse produto.
- O faturamento, as unidades vendidas e o ticket médio consideram apenas as vendas concluídas no resultado.

**Arquivos PHP**

[relatorios.php](../public/relatorios.php), [relatorios_consultas.php](../includes/relatorios_consultas.php), [relatorios_visual.php](../includes/relatorios_visual.php)

### US15 — Analisar movimentações

Como usuário autenticado, quero combinar filtros de período, produto, tipo e responsável, para acompanhar as quantidades movimentadas.

**Critérios de aceitação**

- As linhas correspondem à combinação dos filtros válidos.
- O relatório mostra separadamente o número de movimentações registradas, as unidades de entrada e as unidades de saída.

**Arquivos PHP**

[relatorios.php](../public/relatorios.php), [relatorios_consultas.php](../includes/relatorios_consultas.php), [relatorios_visual.php](../includes/relatorios_visual.php)

### US16 — Consultar o resumo geral

Como usuário autenticado, quero ver os indicadores atuais e históricos em uma mesma tela, para acompanhar estoque, vendas e movimentações.

**Critérios de aceitação**

- Produtos ativos, estoque baixo e clientes cadastrados representam a situação atual.
- Alterar o período afeta os indicadores de vendas e movimentações do resumo e mantém os indicadores atuais.
- Valores financeiros, unidades vendidas e quantidades movimentadas são apresentados como métricas separadas.

**Arquivos PHP**

[relatorios.php](../public/relatorios.php), [relatorios_consultas.php](../includes/relatorios_consultas.php), [relatorios_visual.php](../includes/relatorios_visual.php)

### US17 — Imprimir e salvar relatórios em PDF

Como usuário autenticado, quero abrir a versão de impressão com os filtros aplicados, para salvar ou compartilhar a listagem filtrada.

**Critérios de aceitação**

- Em qualquer aba, Exportar PDF abre uma versão de impressão com o recorte aplicado, os resultados, os totais e a data de geração.
- O botão Imprimir / Salvar como PDF abre o diálogo do navegador, que permite salvar a listagem em PDF. O layout usa A4 em paisagem.
- Acima de 500 registros, a página orienta a restringir os filtros antes de imprimir.
- A versão de impressão exige sessão autenticada.

**Arquivos PHP**

[relatorios.php](../public/relatorios.php), [relatorio_imprimir.php](../public/relatorio_imprimir.php), [relatorios_consultas.php](../includes/relatorios_consultas.php), [relatorios_visual.php](../includes/relatorios_visual.php)

## Matriz de rastreabilidade

| História | Módulo | Arquivos PHP |
| --- | --- | --- |
| [US01](#us01--entrar-no-sistema) | Autenticação | [login.php](../public/login.php), [autenticar.php](../public/autenticar.php), [dashboard.php](../public/dashboard.php) |
| [US02](#us02--encerrar-sessão) | Autenticação | [logout.php](../public/logout.php), [dashboard.php](../public/dashboard.php), [sidebar.php](../includes/sidebar.php) |
| [US03](#us03--acompanhar-os-indicadores) | Dashboard | [dashboard.php](../public/dashboard.php) |
| [US04](#us04--gerenciar-produtos) | Produtos | [produto_novo.php](../public/produto_novo.php), [produto_salvar.php](../public/produto_salvar.php), [produtos.php](../public/produtos.php), [produto_editar.php](../public/produto_editar.php), [produto_atualizar.php](../public/produto_atualizar.php), [produto_excluir.php](../public/produto_excluir.php) |
| [US05](#us05--gerenciar-categorias) | Categorias | [categoria_nova.php](../public/categoria_nova.php), [categoria_salvar.php](../public/categoria_salvar.php), [categorias.php](../public/categorias.php), [categoria_editar.php](../public/categoria_editar.php), [categoria_atualizar.php](../public/categoria_atualizar.php), [categoria_excluir.php](../public/categoria_excluir.php) |
| [US06](#us06--gerenciar-fornecedores) | Fornecedores | [fornecedor_novo.php](../public/fornecedor_novo.php), [fornecedor_salvar.php](../public/fornecedor_salvar.php), [fornecedores.php](../public/fornecedores.php), [fornecedor_editar.php](../public/fornecedor_editar.php), [fornecedor_atualizar.php](../public/fornecedor_atualizar.php), [fornecedor_excluir.php](../public/fornecedor_excluir.php) |
| [US07](#us07--gerenciar-clientes) | Clientes | [cliente_novo.php](../public/cliente_novo.php), [cliente_salvar.php](../public/cliente_salvar.php), [clientes.php](../public/clientes.php), [cliente_editar.php](../public/cliente_editar.php), [cliente_atualizar.php](../public/cliente_atualizar.php), [cliente_excluir.php](../public/cliente_excluir.php) |
| [US08](#us08--registrar-uma-venda) | Vendas | [venda_nova.php](../public/venda_nova.php), [venda_salvar.php](../public/venda_salvar.php), [venda_registro.php](../includes/venda_registro.php), [movimentacao_registro.php](../includes/movimentacao_registro.php) |
| [US09](#us09--consultar-vendas-e-seus-itens) | Vendas | [vendas.php](../public/vendas.php), [venda_detalhes.php](../public/venda_detalhes.php) |
| [US10](#us10--registrar-entrada) | Movimentações de estoque | [movimentacao_nova.php](../public/movimentacao_nova.php), [movimentacao_salvar.php](../public/movimentacao_salvar.php), [movimentacao_registro.php](../includes/movimentacao_registro.php) |
| [US11](#us11--registrar-saída) | Movimentações de estoque | [movimentacao_nova.php](../public/movimentacao_nova.php), [movimentacao_salvar.php](../public/movimentacao_salvar.php), [movimentacao_registro.php](../includes/movimentacao_registro.php) |
| [US12](#us12--consultar-o-histórico-de-movimentações) | Movimentações de estoque | [movimentacoes.php](../public/movimentacoes.php) |
| [US13](#us13--analisar-produtos-e-estoque) | Relatórios | [relatorios.php](../public/relatorios.php), [relatorios_consultas.php](../includes/relatorios_consultas.php), [relatorios_visual.php](../includes/relatorios_visual.php) |
| [US14](#us14--analisar-vendas) | Relatórios | [relatorios.php](../public/relatorios.php), [relatorios_consultas.php](../includes/relatorios_consultas.php), [relatorios_visual.php](../includes/relatorios_visual.php) |
| [US15](#us15--analisar-movimentações) | Relatórios | [relatorios.php](../public/relatorios.php), [relatorios_consultas.php](../includes/relatorios_consultas.php), [relatorios_visual.php](../includes/relatorios_visual.php) |
| [US16](#us16--consultar-o-resumo-geral) | Relatórios | [relatorios.php](../public/relatorios.php), [relatorios_consultas.php](../includes/relatorios_consultas.php), [relatorios_visual.php](../includes/relatorios_visual.php) |
| [US17](#us17--imprimir-e-salvar-relatórios-em-pdf) | Relatórios | [relatorios.php](../public/relatorios.php), [relatorio_imprimir.php](../public/relatorio_imprimir.php), [relatorios_consultas.php](../includes/relatorios_consultas.php), [relatorios_visual.php](../includes/relatorios_visual.php) |
