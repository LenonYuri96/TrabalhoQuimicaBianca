<?php
/** @var array $ocorrencias */
$ocorrencias = $ocorrencias ?? [];
require __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0 text-dark">Livro de Registro de Ocorrências</h3>
        <p class="text-muted small mb-0">Controle de perdas, quebras de vidrarias e falhas operacionais</p>
    </div>
    <a href="index.php?route=ocorrencias&action=create" class="btn btn-cyan">
        <i class="bi bi-plus-circle me-1"></i> Nova Ocorrência
    </a>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Data / Hora</th>
                        <th>Relator</th>
                        <th>Local</th>
                        <th>Tipo</th>
                        <th>Item Envolvido</th>
                        <th>Descrição do Fato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ocorrencias)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhuma ocorrência registrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ocorrencias as $oc): ?>
                            <tr>
                                <td class="ps-4 text-nowrap"><?= date('d/m/Y H:i', strtotime($oc['data_hora'])) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($oc['relator_nome']) ?></td>
                                <td><span
                                        class="badge bg-light text-dark border"><?= htmlspecialchars($oc['local_ocorrido']) ?></span>
                                </td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($oc['categoria']) ?></span></td>
                                <td class="fw-semibold text-primary"><?= htmlspecialchars($oc['item_nome']) ?></td>
                                <td><small><?= nl2br(htmlspecialchars($oc['descricao'])) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>