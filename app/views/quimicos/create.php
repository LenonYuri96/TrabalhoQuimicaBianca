<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-flask fs-2 text-primary"></i>
                    <div>
                        <h4 class="mb-0 fw-bold">Cadastrar Novo Produto Químico</h4>
                        <p class="text-muted small mb-0">Adicione reagentes com suporte a auto-preenchimento inteligente
                        </p>
                    </div>
                </div>
            </div>

            <form action="index.php?route=quimicos&action=create" method="POST">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Nome do Reagente / Produto</label>
                            <button type="button" id="btnAutoPreencherIa"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-magic text-warning me-1"></i> Preencher com IA
                            </button>
                        </div>
                        <input type="text" name="nome" id="inputNomeQuimico" class="form-control form-pill" required
                            placeholder="Ex: Cromato de Potássio">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Quantidade</label>
                        <input type="number" step="0.01" name="quantidade" class="form-control form-pill" required
                            value="1">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Data de Validade (Em branco se N/A)</label>
                        <input type="date" name="validade" class="form-control form-pill">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Estado Físico</label>
                        <select name="estado" id="selectEstado" class="form-select form-pill" required>
                            <option value="Pó">Pó</option>
                            <option value="Liquido">Líquido</option>
                            <option value="Granulo">Grânulo</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tipo de Armazenamento</label>
                        <input type="text" name="tipo_armazenamento" id="inputArmazenamento"
                            class="form-control form-pill" placeholder="Ex: Armário de Inflamáveis...">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Periculosidade</label>
                        <input type="text" name="periculosidade" id="inputPericulosidade" class="form-control form-pill"
                            placeholder="Ex: Tóxico, Corrosivo...">
                    </div>

                    <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                        <a href="index.php?route=quimicos"
                            class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Salvar
                            Reagente</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>