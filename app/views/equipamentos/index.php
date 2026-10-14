<?php
/** @var array $equipamentos */
$equipamentos = $equipamentos ?? [];
require __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0 text-dark">Manutenção Preventiva de Equipamentos</h3>
        <p class="text-muted small mb-0">Controle de periodicidade e revisões periódicas do laboratório</p>
    </div>
    <a href="index.php?route=equipamentos&action=create" class="btn btn-cyan">
        <i class="bi bi-plus-circle me-1"></i> Novo Equipamento
    </a>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-dark">
                    <tr>
                        <th class="text-start ps-4">Equipamento</th>
                        <th>Patrimônio / Cód</th>
                        <th>Periodicidade</th>
                        <th>Última Revisão</th>
                        <th>Próxima Revisão</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($equipamentos)): ?>
                        <tr>
                            <td colspan="6" class="text-muted py-4">Nenhum equipamento cadastrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($equipamentos as $eq):
                            $hoje = date('Y-m-d');
                            $prox = $eq['proxima_manutencao'];
                            $isVencida = (!empty($prox) && $prox < $hoje);
                            ?>
                            <tr class="<?= $isVencida ? 'table-row-vencido' : '' ?>">
                                <td class="text-start ps-4 fw-semibold"><?= htmlspecialchars($eq['nome']) ?></td>
                                <td><?= htmlspecialchars($eq['codigo_patrimonio'] ?? '—') ?></td>
                                <td><?= $eq['frequencia_meses'] ? $eq['frequencia_meses'] . ' meses' : htmlspecialchars($eq['observacao'] ?? 'Manual') ?>
                                </td>
                                <td><?= !empty($eq['ultima_manutencao']) ? date('d/m/Y', strtotime($eq['ultima_manutencao'])) : 'N/A' ?>
                                </td>
                                <td><?= !empty($eq['proxima_manutencao']) ? date('d/m/Y', strtotime($eq['proxima_manutencao'])) : 'N/A' ?>
                                </td>
                                <td>
                                    <?php if ($isVencida): ?>
                                        <span class="badge bg-danger badge-status">REVISÃO ATRASADA</span>
                                    <?php elseif (!empty($prox)): ?>
                                        <span class="badge bg-success badge-status">EM DIA</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary badge-status">INSPEÇÃO MANUAL</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>