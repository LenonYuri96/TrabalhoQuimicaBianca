<?php
/** @var array $praticas */
$praticas = $praticas ?? [];
require __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0 text-dark">Roteiros de Aulas Práticas</h3>
        <p class="text-muted small mb-0">Catálogo de experimentos, objetivos e procedimentos laboratoriais</p>
    </div>
    <a href="index.php?route=praticas&action=create" class="btn btn-primary rounded-pill px-4 fw-semibold">
        <i class="bi bi-plus-circle me-1"></i> Nova Prática
    </a>
</div>

<div class="card card-custom border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Título da Prática</th>
                        <th>Disciplina / Matéria</th>
                        <th>Objetivo Resumido</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($praticas)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                Nenhuma prática experimental cadastrada ainda.
                            </td>
                        </tr>
                    <?php else:
                        foreach ($praticas as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    <a href="index.php?route=praticas&action=view&id=<?= $p['id'] ?>"
                                        class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($p['titulo']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        <?= htmlspecialchars($p['materia_curso']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small text-truncate" style="max-width: 380px;">
                                    <?= htmlspecialchars(mb_strimwidth($p['objetivo'], 0, 110, '...')) ?>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?route=praticas&action=view&id=<?= $p['id'] ?>"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i> Ver Roteiro
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>