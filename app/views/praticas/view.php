<?php
/** @var array $pratica */
/** @var array $itens */
$pratica = $pratica ?? [
    'id' => 0,
    'titulo' => '',
    'materia_curso' => '',
    'objetivo' => '',
    'procedimento' => ''
];
$itens = $itens ?? [];

require __DIR__ . '/../layouts/header.php';
?>

<!-- CABEÇALHO DA PRÁTICA E AÇÕES -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($pratica['titulo']) ?></h3>
        <span
            class="badge bg-primary fs-6 px-3 py-2 shadow-sm"><?= htmlspecialchars($pratica['materia_curso']) ?></span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- Botão de Auditoria de Viabilidade IA -->
        <button type="button"
            class="btn btn-warning rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-1"
            onclick="auditarPraticaIa(<?= (int) ($pratica['id'] ?? 0) ?>)">
            <i class="bi bi-robot fs-5"></i> Auditoria de Viabilidade IA
        </button>

        <a href="index.php?route=praticas" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- COLUNA DA ESQUERDA: OBJETIVO E PROCEDIMENTO -->
    <div class="col-lg-7">
        <!-- Objetivo -->
        <div class="card card-custom p-4 mb-4 shadow-sm">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-bullseye text-danger me-2"></i>Objetivo da Prática
            </h5>
            <p class="text-secondary mb-0" style="line-height: 1.7;">
                <?= nl2br(htmlspecialchars($pratica['objetivo'])) ?>
            </p>
        </div>

        <!-- Procedimento Experimental -->
        <div class="card card-custom p-4 shadow-sm">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-journal-text text-primary me-2"></i>Procedimento Experimental Passo a Passo
            </h5>
            <div class="procedimento-box bg-light p-3 rounded text-dark">
                <?= htmlspecialchars($pratica['procedimento']) ?>
            </div>
        </div>
    </div>

    <!-- COLUNA DA DIREITA: MATERIAIS, VIDRARIAS E REAGENTES -->
    <div class="col-lg-5">
        <div class="card card-custom p-4 shadow-sm h-100">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-boxes text-info me-2"></i>Materiais & Reagentes Utilizados
            </h5>

            <div class="list-group list-group-flush">
                <?php if (empty($itens)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                        Nenhum item vinculado a esta prática.
                    </div>
                <?php else:
                    foreach ($itens as $it):
                        $badgeClass = $it['tipo_item'] === 'quimico'
                            ? 'bg-warning text-dark'
                            : ($it['tipo_item'] === 'vidraria' ? 'bg-info text-white' : 'bg-secondary text-white');
                        ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start px-0 py-3">
                            <div>
                                <span class="badge <?= $badgeClass ?> me-1"><?= ucfirst($it['tipo_item']) ?></span>
                                <strong class="text-dark"><?= htmlspecialchars($it['item_nome']) ?></strong>
                                <?php if (!empty($it['capacidade_volume'])): ?>
                                    <span class="text-muted small">(<?= htmlspecialchars($it['capacidade_volume']) ?>)</span>
                                <?php endif; ?>
                                <?php if (!empty($it['observacao'])): ?>
                                    <div class="text-muted small mt-1"><?= htmlspecialchars($it['observacao']) ?></div>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-light text-dark border px-2 py-1">
                                <?= htmlspecialchars($it['quantidade_necessaria']) ?>
                            </span>
                        </div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: AUDITORIA DE VIABILIDADE IA                       -->
<!-- ======================================================== -->
<div class="modal fade" id="modalViabilidadeIa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-shield-check text-warning me-2"></i>Auditoria de Viabilidade da Prática (IA)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4" id="corpoViabilidadeIa">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2 text-muted fw-semibold">Consultando estoque, validades e integridade de vidrarias no
                        banco...</p>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4"
                    data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- SCRIPT: REQUISIÇÃO DA AUDITORIA DE VIABILIDADE           -->
<!-- ======================================================== -->
<script>
    function auditarPraticaIa(praticaId) {
        const modalEl = document.getElementById('modalViabilidadeIa');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        const corpoModal = document.getElementById('corpoViabilidadeIa');

        // Estado inicial de carregamento
        corpoModal.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-warning" role="status"></div>
            <p class="mt-2 text-muted fw-semibold">Cruzando itens do roteiro com o banco de dados...</p>
        </div>
    `;
        modal.show();

        fetch(`index.php?route=ai&action=analisarViabilidadePratica&id=${praticaId}`)
            .then(response => {
                if (!response.ok) throw new Error('Falha na resposta do servidor');
                return response.json();
            })
            .then(data => {
                if (data.erro) {
                    corpoModal.innerHTML = `<div class="alert alert-danger mb-0">${data.erro}</div>`;
                    return;
                }

                const badgeStatus = data.aprovado ?
                    '<span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle me-1"></i>LIBERADA PARA AULA</span>' :
                    '<span class="badge bg-danger fs-6 px-3 py-2"><i class="bi bi-x-octagon me-1"></i>BLOQUEADA / REQUER INTERVENÇÃO</span>';

                let html = `
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <span class="text-muted small fw-bold text-uppercase">Status Operacional:</span>
                    <div>${badgeStatus}</div>
                </div>

                <div class="alert alert-${data.aprovado ? 'success' : 'danger'} border-0 shadow-sm mb-3">
                    <div class="fw-bold mb-1"><i class="bi bi-robot me-1"></i> Parecer do Auditor Inteligente:</div>
                    <div>${data.parecer_ia}</div>
                </div>
            `;

                if (data.impeditivos && data.impeditivos.length > 0) {
                    html += `
                    <div class="p-3 bg-danger-subtle rounded-3 mb-3">
                        <h6 class="text-danger fw-bold mb-2">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Bloqueios Críticos Encontrados no Banco:
                        </h6>
                        <ul class="mb-0 ps-3">
                `;
                    data.impeditivos.forEach(imp => {
                        html += `<li class="small text-danger mb-1">${imp}</li>`;
                    });
                    html += `</ul></div>`;
                }

                if (data.alertas && data.alertas.length > 0) {
                    html += `
                    <div class="p-3 bg-warning-subtle rounded-3 mb-0">
                        <h6 class="text-warning-emphasis fw-bold mb-2">
                            <i class="bi bi-info-circle-fill me-1"></i> Alertas Operacionais / Atenção:
                        </h6>
                        <ul class="mb-0 ps-3">
                `;
                    data.alertas.forEach(alt => {
                        html += `<li class="small text-dark mb-1">${alt}</li>`;
                    });
                    html += `</ul></div>`;
                }

                corpoModal.innerHTML = html;
            })
            .catch(err => {
                corpoModal.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i> Não foi possível concluir a auditoria da prática no momento.
                </div>
            `;
            });
    }
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>