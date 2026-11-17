<?php
require_once __DIR__ . '/categoria_funcoes.php';

categoriaExigirPost();

$editando = $editando ?? false;
$id = null;
$destino = 'categoria_nova.php';

if ($editando) {
    $id = categoriaId($_POST['id'] ?? null);
    if ($id === false) {
        categoriaRedirecionar('categorias.php', 'danger', 'Categoria inválida.');
    }

    $destino = 'categoria_editar.php?id=' . $id;
}

$nome = $_POST['nome'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$dados = [
    'nome' => is_string($nome) ? trim($nome) : '',
    'descricao' => is_string($descricao) ? trim($descricao) : '',
];

if (!is_string($nome) || preg_match('/\A.{1,100}\z/us', $dados['nome']) !== 1) {
    categoriaRedirecionar($destino, 'danger', 'Informe um nome de 1 a 100 caracteres.', $dados);
}

if (!is_string($descricao) || strlen($dados['descricao']) > 65535 || preg_match('//u', $dados['descricao']) !== 1) {
    categoriaRedirecionar($destino, 'danger', 'Informe uma descrição válida.', $dados);
}

require_once '/var/www/stockweb/config/database.php';

$tipo = 'danger';
$mensagem = 'Não foi possível salvar a categoria. Tente novamente.';

try {
    $pdo->beginTransaction();
    $categoriaExiste = true;

    if ($editando) {
        $stmt = $pdo->prepare('SELECT id FROM categorias WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $categoriaExiste = $stmt->fetchColumn() !== false;
    }

    if (!$categoriaExiste) {
        $destino = 'categorias.php';
        $mensagem = 'Categoria não encontrada.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM categorias WHERE LOWER(TRIM(nome)) = LOWER(?) AND id <> ? LIMIT 1');
        $stmt->execute([$dados['nome'], $id ?? 0]);

        if ($stmt->fetchColumn() !== false) {
            $mensagem = 'Já existe uma categoria com esse nome.';
        } else {
            $parametros = [$dados['nome'], $dados['descricao'] !== '' ? $dados['descricao'] : null];

            if ($editando) {
                $stmt = $pdo->prepare('UPDATE categorias SET nome = ?, descricao = ? WHERE id = ?');
                $parametros[] = $id;
            } else {
                $stmt = $pdo->prepare('INSERT INTO categorias (nome, descricao) VALUES (?, ?)');
            }

            $stmt->execute($parametros);
            $pdo->commit();
            $tipo = 'success';
            $destino = 'categorias.php';
            $mensagem = $editando ? 'Categoria atualizada com sucesso.' : 'Categoria cadastrada com sucesso.';
        }
    }
} catch (PDOException $erro) {
    if ((int) ($erro->errorInfo[1] ?? 0) === 1062) {
        $mensagem = 'Já existe uma categoria com esse nome.';
    } else {
        error_log($erro->getMessage());
    }
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

categoriaRedirecionar($destino, $tipo, $mensagem, $tipo === 'danger' ? $dados : null);
