<?php
/** @var array $quimicos */
$quimicos = $quimicos ?? [];
require __DIR__ . '/../layouts/header.php';
?>

<div class="row align-items-center justify-content-between mb-4 g-3">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold mb-0 text-dark">Inventário de Químicos</h3>
        <p class="text-muted small mb-0">Controle com consulta inteligente de segurança, EPIs e descarte</p>
    </div>
    <div class="col-12 col-md-6 d-flex gap-2 justify-content-md-end">
        <div class="input-group input-group-sm w-auto flex-grow-1 flex-md-grow-0">
            <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i
                    class="bi bi-search text-muted"></i></span>
            <input type="text" id="filtroTabela" class="form-control border-start-0 rounded-end-pill"
                placeholder="Filtrar reagente...">
        </div>
        <a href="index.php?route=quimicos&action=create" class="btn btn-cyan text-nowrap">
            <i class="bi bi-plus-circle me-1"></i> Novo Reagente
        </a>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" id="tabelaQuimicos">
                <thead class="table-success">
                    <tr>
                        <th class="text-start ps-4">Nome</th>
                        <th>Qtd</th>
                        <th>Validade</th>
                        <th>Armazenamento</th>
                        <th>Periculosidade</th>
                        <th>Estado</th>
                        <th>Validade Status</th>
                        <th>Guia IA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quimicos)): ?>
                        <tr>
                            <td colspan="8" class="text-muted py-4">Nenhum produto cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quimicos as $q):
                            $statusInfo = Quimico::calcularStatus($q['validade']);
                            ?>
                            <tr class="<?= $statusInfo['status'] === 'VENCIDO' ? 'table-row-vencido' : '' ?>">
                                <td class="text-start ps-4 fw-semibold"><?= htmlspecialchars($q['nome']) ?></td>
                                <td><?= $q['quantidade'] ?></td>
                                <td><?= $q['validade'] ? date('d/m/Y', strtotime($q['validade'])) : '<span class="text-muted">N/A</span>' ?>
                                </td>
                                <td><?= htmlspecialchars($q['tipo_armazenamento'] ?: 'NÃO INFORMADO') ?></td>
                                <td><?= htmlspecialchars($q['periculosidade'] ?: 'NÃO INFORMADO') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $q['estado'] ?></span></td>
                                <td>
                                    <span class="badge <?= $statusInfo['classe'] ?> badge-status">
                                        <?= $statusInfo['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-dark rounded-pill px-3 btn-consultar-quimico-ia"
                                        data-nome="<?= htmlspecialchars($q['nome']) ?>">
                                        <i class="bi bi-stars text-warning me-1"></i> FISPQ / Descarte
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL DE CONSULTA DA IA (FISPQ / DESCARTE) -->
<div class="modal fade" id="modalQuimicoIa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalQuimicoNome">Guia de Segurança com IA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border-0 d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-shield-lock fs-4"></i>
                    <div>
                        <strong class="d-block">Classe de Perigo Principal:</strong>
                        <span id="iaPerigoTexto">Carregando...</span>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold text-dark"><i class="bi bi-exclamation-octagon text-danger me-1"></i>
                        Incompatibilidades Químicas Perigosas:</h6>
                    <p class="text-muted small ps-3 mb-0" id="iaIncompTexto"></p>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold text-dark"><i class="bi bi-recycle text-success me-1"></i> Protocolo de Descarte
                        Ecológico:</h6>
                    <p class="text-muted small ps-3 mb-0" id="iaDescarteTexto"></p>
                </div>

                <div>
                    <h6 class="fw-bold text-dark"><i class="bi bi-person-badge text-primary me-1"></i> EPIs Obrigatórios
                        Recomendados:</h6>
                    <div id="iaEpisBadges" class="d-flex flex-wrap gap-2 ps-3 mt-1"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>