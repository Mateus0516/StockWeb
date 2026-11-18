<?php
require_once __DIR__ . '/cliente_funcoes.php';

$editando = isset($cliente);
$destino = $editando ? 'cliente_editar.php?id=' . $id : 'cliente_novo.php';
$aviso = clienteAviso($destino);
$dados = $aviso['dados'] ?? $cliente ?? [];
$acao = $editando ? 'cliente_atualizar.php' : 'cliente_salvar.php';
$titulo = $editando ? 'Editar cliente' : 'Novo cliente';

clienteCabecalho($titulo, $aviso);
?>
        <h1 class="fw-bold mb-4"><?= clienteEscapar($titulo) ?></h1>
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-4">
                <form action="<?= clienteEscapar($acao) ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= clienteEscapar(clienteToken()) ?>">
                    <?php if ($editando): ?>
                        <input type="hidden" name="id" value="<?= (int) $id ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nome" class="form-label fw-semibold">Nome</label>
                            <input type="text" id="nome" name="nome" class="form-control" required maxlength="150"
                                   value="<?= clienteEscapar($dados['nome'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="cpf" class="form-label fw-semibold">CPF</label>
                            <input type="text" id="cpf" name="cpf" class="form-control" required maxlength="14" inputmode="numeric"
                                   aria-describedby="cpf-ajuda" value="<?= clienteEscapar($dados['cpf'] ?? '') ?>">
                            <div id="cpf-ajuda" class="form-text">Informe os 11 dígitos, com ou sem pontuação.</div>
                        </div>
                        <div class="col-md-8">
                            <label for="email" class="form-label fw-semibold">E-mail (opcional)</label>
                            <input type="email" id="email" name="email" class="form-control" maxlength="150"
                                   value="<?= clienteEscapar($dados['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="telefone" class="form-label fw-semibold">Telefone (opcional)</label>
                            <input type="tel" id="telefone" name="telefone" class="form-control" maxlength="20"
                                   value="<?= clienteEscapar($dados['telefone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                        <a href="clientes.php" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Salvar cliente</button>
                    </div>
                </form>
            </div>
        </div>
<?php clienteRodape();
