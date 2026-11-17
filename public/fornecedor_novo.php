<?php
require_once __DIR__ . '/fornecedor_funcoes.php';

$editando = isset($fornecedor);
$destino = $editando ? 'fornecedor_editar.php?id=' . $id : 'fornecedor_novo.php';
$aviso = fornecedorAviso($destino);
$dados = $aviso['dados'] ?? $fornecedor ?? [];
$acao = $editando ? 'fornecedor_atualizar.php' : 'fornecedor_salvar.php';
$titulo = $editando ? 'Editar fornecedor' : 'Novo fornecedor';

fornecedorCabecalho($titulo, $aviso);
?>
        <h1 class="fw-bold mb-4"><?= fornecedorEscapar($titulo) ?></h1>
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-4">
                <form action="<?= fornecedorEscapar($acao) ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= fornecedorEscapar(fornecedorToken()) ?>">
                    <?php if ($editando): ?>
                        <input type="hidden" name="id" value="<?= (int) $id ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nome" class="form-label fw-semibold">Nome</label>
                            <input type="text" id="nome" name="nome" class="form-control" required maxlength="150"
                                   value="<?= fornecedorEscapar($dados['nome'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="cnpj" class="form-label fw-semibold">CNPJ</label>
                            <input type="text" id="cnpj" name="cnpj" class="form-control" required maxlength="18"
                                   aria-describedby="cnpj-ajuda" value="<?= fornecedorEscapar($dados['cnpj'] ?? '') ?>">
                            <div id="cnpj-ajuda" class="form-text">Informe os 14 caracteres, com ou sem pontuação.</div>
                        </div>
                        <div class="col-md-8">
                            <label for="email" class="form-label fw-semibold">E-mail (opcional)</label>
                            <input type="email" id="email" name="email" class="form-control" maxlength="150"
                                   value="<?= fornecedorEscapar($dados['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="telefone" class="form-label fw-semibold">Telefone (opcional)</label>
                            <input type="tel" id="telefone" name="telefone" class="form-control" maxlength="20"
                                   value="<?= fornecedorEscapar($dados['telefone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                        <a href="fornecedores.php" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Salvar fornecedor</button>
                    </div>
                </form>
            </div>
        </div>
<?php fornecedorRodape();
