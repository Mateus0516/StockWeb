<?php
require_once __DIR__ . '/fornecedor_funcoes.php';

fornecedorExigirPost();

$editando = $editando ?? false;
$destino = 'fornecedor_novo.php';
if ($editando) {
    $id = fornecedorId($_POST['id'] ?? null);
    if ($id === false) {
        fornecedorRedirecionar('fornecedores.php', 'danger', 'Fornecedor inválido.');
    }

    $destino = 'fornecedor_editar.php?id=' . $id;
}

$dados = [];
$camposInvalidos = false;
foreach (['nome', 'cnpj', 'email', 'telefone'] as $campo) {
    $valor = $_POST[$campo] ?? '';
    $dados[$campo] = is_string($valor) ? $valor : '';
    $camposInvalidos = $camposInvalidos || !is_string($valor);
}

if ($camposInvalidos) {
    fornecedorRedirecionar($destino, 'danger', 'Confira os dados enviados no formulário.', $dados);
}

$nome = trim($dados['nome']);
$cnpj = strtoupper(trim($dados['cnpj']));
$email = trim($dados['email']);
$telefone = trim($dados['telefone']);

if (preg_match('/\A.{1,150}\z/us', $nome) !== 1) {
    fornecedorRedirecionar($destino, 'danger', 'Informe um nome de 1 a 150 caracteres.', $dados);
}

if (preg_match('~\A[A-Z0-9]{2}\.[A-Z0-9]{3}\.[A-Z0-9]{3}/[A-Z0-9]{4}-[0-9]{2}\z~', $cnpj) === 1) {
    $cnpj = str_replace(['.', '/', '-'], '', $cnpj);
}

if (preg_match('/\A[A-Z0-9]{12}[0-9]{2}\z/', $cnpj) !== 1) {
    fornecedorRedirecionar($destino, 'danger', 'Informe um CNPJ com 14 caracteres e os dois últimos numéricos.', $dados);
}

if ($email !== '' && (preg_match('/\A.{1,150}\z/u', $email) !== 1 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
    fornecedorRedirecionar($destino, 'danger', 'Informe um e-mail válido de até 150 caracteres.', $dados);
}

if (preg_match('/\A.{0,20}\z/u', $telefone) !== 1) {
    fornecedorRedirecionar($destino, 'danger', 'Informe um telefone de até 20 caracteres.', $dados);
}

require_once '/var/www/stockweb/config/database.php';

try {
    $stmt = $pdo->prepare('SELECT id FROM fornecedores WHERE cnpj = ? AND id <> ? LIMIT 1');
    $stmt->execute([$cnpj, $id ?? 0]);
    if ($stmt->fetchColumn() !== false) {
        fornecedorRedirecionar($destino, 'danger', 'Já existe um fornecedor com esse CNPJ.', $dados);
    }

    $parametros = [$nome, $cnpj, $email !== '' ? $email : null, $telefone !== '' ? $telefone : null];

    if ($editando) {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT id FROM fornecedores WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);

        if ($stmt->fetchColumn() === false) {
            $pdo->rollBack();
            fornecedorRedirecionar('fornecedores.php', 'danger', 'Fornecedor não encontrado.');
        }

        $stmt = $pdo->prepare('UPDATE fornecedores SET nome = ?, cnpj = ?, email = ?, telefone = ? WHERE id = ?');
        $parametros[] = $id;
    } else {
        $stmt = $pdo->prepare('INSERT INTO fornecedores (nome, cnpj, email, telefone) VALUES (?, ?, ?, ?)');
    }

    $stmt->execute($parametros);
    if ($editando) {
        $pdo->commit();
    }
} catch (PDOException $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ((int) ($erro->errorInfo[1] ?? 0) === 1062) {
        fornecedorRedirecionar($destino, 'danger', 'Já existe um fornecedor com esse CNPJ.', $dados);
    }

    error_log($erro->getMessage());
    fornecedorRedirecionar($destino, 'danger', 'Não foi possível salvar o fornecedor. Tente novamente.', $dados);
}

$mensagem = $editando ? 'Fornecedor atualizado com sucesso.' : 'Fornecedor cadastrado com sucesso.';
fornecedorRedirecionar('fornecedores.php', 'success', $mensagem);
