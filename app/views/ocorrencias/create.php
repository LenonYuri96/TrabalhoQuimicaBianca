<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom p-4">

            <!-- Cabeçalho -->
            <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                <i class="bi bi-shield-exclamation fs-2 text-warning"></i>
                <div>
                    <h4 class="mb-0 fw-bold">Registrar Nova Ocorrência</h4>
                    <p class="text-muted small mb-0">Livro de registro de quebras, falhas mecânicas e perdas com suporte
                        de IA</p>
                </div>
            </div>

            <!-- Formulário -->
            <form action="index.php?route=ocorrencias&action=create" method="POST" id="formOcorrencia">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nome de quem está relatando</label>
                        <input type="text" name="relator_nome" class="form-control form-pill" required
                            placeholder="Ex: Ellen Carneiro">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Onde foi o ocorrido (Local)</label>
                        <input type="text" name="local_ocorrido" class="form-control form-pill" required
                            placeholder="Ex: Bancada 2, Capela de Exaustão">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Data / Hora</label>
                        <input type="datetime-local" name="data_hora" class="form-control form-pill" required
                            value="<?= date('Y-m-d\TH:i') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tipo de Item</label>
                        <select name="categoria" id="categoriaOcorrencia" class="form-select form-pill" required>
                            <option value="Vidraria">Vidraria</option>
                            <option value="Equipamento">Equipamento</option>
                            <option value="Produto">Produto / Reagente</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Nome da Vidraria / Equipamento / Produto</label>
                        <input type="text" name="item_nome" id="itemNomeOcorrencia" class="form-control form-pill"
                            required placeholder="Ex: Balão Volumétrico 250mL / Agitador Magnético / Fenolftaleína">
                    </div>

                    <!-- Descrição + Botão da IA -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Descrição do fato / Observação</label>
                            <button type="button" id="btnAnalisarIa"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-stars text-warning me-1"></i> Analisar Risco com IA
                            </button>
                        </div>
                        <textarea name="descricao" id="descricaoOcorrencia" class="form-control rounded-4 p-3" rows="4"
                            required
                            placeholder="Descreva os detalhes do ocorrido (ex: motor do agitador travou com cheiro de queimado)..."></textarea>
                    </div>

                    <!-- Caixa de Parecer Técnico da IA (aparece via JavaScript) -->
                    <div class="col-12 d-none" id="painelIaResultado">
                        <div class="card border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-4 p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-robot fs-4 text-primary"></i>
                                <h6 class="fw-bold mb-0 text-primary">Parecer Técnico Preliminar da IA</h6>
                                <span class="badge bg-danger ms-auto rounded-pill" id="iaBadgeGravidade">Gravidade:
                                    Média</span>
                            </div>
                            <div class="small text-dark">
                                <p class="mb-1"><strong><i class="bi bi-search me-1"></i> Diagnóstico de
                                        Causa-Raiz:</strong> <span id="iaCausa"></span></p>
                                <p class="mb-0"><strong><i class="bi bi-check2-circle me-1 text-success"></i> Ação
                                        Corretiva Recomendada:</strong> <span id="iaAcao"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                        <a href="index.php?route=ocorrencias"
                            class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold text-dark">
                            <i class="bi bi-check2-circle me-1"></i> Registrar Ocorrência
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>