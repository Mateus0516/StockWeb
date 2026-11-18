<?php
require_once __DIR__ . '/cliente_funcoes.php';

clienteExigirPost();

$editando = $editando ?? false;
$destino = 'cliente_novo.php';
if ($editando) {
    $id = clienteId($_POST['id'] ?? null);
    if ($id === false) {
        clienteRedirecionar('clientes.php', 'danger', 'Cliente inválido.');
    }

    $destino = 'cliente_editar.php?id=' . $id;
}

$dados = [];
$camposInvalidos = false;
foreach (['nome', 'cpf', 'email', 'telefone'] as $campo) {
    $valor = $_POST[$campo] ?? '';
    $dados[$campo] = is_string($valor) ? $valor : '';
    $camposInvalidos = $camposInvalidos || !is_string($valor);
}

if ($camposInvalidos) {
    clienteRedirecionar($destino, 'danger', 'Confira os dados enviados no formulário.', $dados);
}

$nome = trim($dados['nome']);
$cpf = trim($dados['cpf']);
$email = trim($dados['email']);
$telefone = trim($dados['telefone']);

if (preg_match('/\A.{1,150}\z/us', $nome) !== 1) {
    clienteRedirecionar($destino, 'danger', 'Informe um nome de 1 a 150 caracteres.', $dados);
}

if (preg_match('/\A[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2}\z/', $cpf) === 1) {
    $cpf = str_replace(['.', '-'], '', $cpf);
}

if (preg_match('/\A[0-9]{11}\z/', $cpf) !== 1) {
    clienteRedirecionar($destino, 'danger', 'Informe um CPF com 11 dígitos.', $dados);
}

if ($email !== '' && (preg_match('/\A.{1,150}\z/u', $email) !== 1 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
    clienteRedirecionar($destino, 'danger', 'Informe um e-mail válido de até 150 caracteres.', $dados);
}

if (preg_match('/\A.{0,20}\z/u', $telefone) !== 1) {
    clienteRedirecionar($destino, 'danger', 'Informe um telefone de até 20 caracteres.', $dados);
}

require_once '/var/www/stockweb/config/database.php';

try {
    $stmt = $pdo->prepare('SELECT id FROM clientes WHERE cpf = ? AND id <> ? LIMIT 1');
    $stmt->execute([$cpf, $id ?? 0]);
    if ($stmt->fetchColumn() !== false) {
        clienteRedirecionar($destino, 'danger', 'Já existe um cliente com esse CPF.', $dados);
    }

    $parametros = [$nome, $cpf, $email !== '' ? $email : null, $telefone !== '' ? $telefone : null];

    if ($editando) {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT id FROM clientes WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);

        if ($stmt->fetchColumn() === false) {
            $pdo->rollBack();
            clienteRedirecionar('clientes.php', 'danger', 'Cliente não encontrado.');
        }

        $stmt = $pdo->prepare('UPDATE clientes SET nome = ?, cpf = ?, email = ?, telefone = ? WHERE id = ?');
        $parametros[] = $id;
    } else {
        $stmt = $pdo->prepare('INSERT INTO clientes (nome, cpf, email, telefone) VALUES (?, ?, ?, ?)');
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
        clienteRedirecionar($destino, 'danger', 'Já existe um cliente com esse CPF.', $dados);
    }

    error_log($erro->getMessage());
    clienteRedirecionar($destino, 'danger', 'Não foi possível salvar o cliente. Tente novamente.', $dados);
}

$mensagem = $editando ? 'Cliente atualizado com sucesso.' : 'Cliente cadastrado com sucesso.';
clienteRedirecionar('clientes.php', 'success', $mensagem);
