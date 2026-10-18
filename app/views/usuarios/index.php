<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold text-dark mb-1">
            <i class="bi bi-people-fill text-primary me-2"></i>Corpo Docente / Instrutores
        </h2>
        <p class="text-muted mb-0">Instrutores autorizados do laboratório FIEMG</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="index.php?route=usuarios&action=create" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Novo Instrutor
        </a>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Instrutor</th>
                    <th>E-mail Institucional</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th>Cadastrado em</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Nenhum instrutor cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <?= strtoupper(mb_substr($u['nome'], 0, 1)) ?>
                                    </div>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($u['nome']) ?></span>
                                </div>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($u['email']) ?></code>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark text-uppercase px-2 py-1">
                                    <?= htmlspecialchars($u['perfil']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="bi bi-check2 me-1"></i>Ativo
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?= date('d/m/Y H:i', strtotime($u['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>