<?php
require __DIR__ . '/../layouts/header.php';
$abaAtual = $_GET['aba'] ?? 'equipamentos';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-0 text-dark">Equipamentos &amp; Vidrarias do Laboratório</h3>
        <p class="text-muted small mb-0">Manutenções periódicas, calibração e rastreabilidade de estoque/avarias</p>
    </div>
    <a href="index.php?route=equipamentos&action=create" class="btn btn-cyan fw-bold">
        <i class="bi bi-plus-circle me-1"></i> Cadastrar Item / Vidraria
    </a>
</div>

<!-- ======================================================== -->
<!-- ABAS DE NAVEGAÇÃO                                        -->
<!-- ======================================================== -->
<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link <?= $abaAtual === 'equipamentos' ? 'active' : '' ?>"
            href="index.php?route=equipamentos&aba=equipamentos">
            <i class="bi bi-cpu me-1"></i> Equipamentos e Aparelhos
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $abaAtual === 'vidrarias' ? 'active' : '' ?>"
            href="index.php?route=equipamentos&aba=vidrarias">
            <i class="bi bi-eyedropper me-1"></i> Vidrarias &amp; Controle de Avarias
        </a>
    </li>
</ul>

<?php if ($abaAtual === 'vidrarias'): ?>
    <!-- ==================================================== -->
    <!-- PAINEL DE ANÁLISE DE VIDRARIAS                       -->
    <!-- ==================================================== -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card">
                <div class="kpi-icon">
                    <i class="bi bi-collection"></i>
                </div>
                <div>
                    <div class="kpi-metric-number text-dark">
                        <?= (int) ($statsVidrarias['total_vidros'] ?? 0) ?>
                    </div>
                    <div class="kpi-metric-label">Acervo Total</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card kpi-success">
                <div class="kpi-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="kpi-metric-number text-success">
                        <?= (int) ($statsVidrarias['total_disponivel'] ?? 0) ?>
                    </div>
                    <div class="kpi-metric-label">Disponíveis</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card kpi-danger">
                <div class="kpi-icon">
                    <i class="bi bi-x-octagon"></i>
                </div>
                <div>
                    <div class="kpi-metric-number text-danger">
                        <?= (int) ($statsVidrarias['total_quebrados'] ?? 0) ?>
                    </div>
                    <div class="kpi-metric-label">Quebradas</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card kpi-warning">
                <div class="kpi-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <div class="kpi-metric-number text-warning">
                        <?= (int) ($statsVidrarias['total_defeitos'] ?? 0) ?>
                    </div>
                    <div class="kpi-metric-label">Trincadas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================================================== -->
    <!-- TABELA DE VIDRARIAS                                  -->
    <!-- ==================================================== -->
    <div class="card card-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-dark text-white">
                        <tr>
                            <th class="text-start ps-4">Vidraria / Item</th>
                            <th>Capacidade</th>
                            <th>Estoque Total</th>
                            <th>Em Condições</th>
                            <th>Quebradas</th>
                            <th>Defeitos</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vidrarias)): ?>
                            <tr>
                                <td colspan="7" class="py-0">
                                    <div class="empty-state">
                                        <i class="bi bi-inboxes"></i>
                                        <h5>Nenhuma vidraria cadastrada</h5>
                                        <p>Cadastre o primeiro item de vidraria do laboratório.</p>
                                        <a href="index.php?route=equipamentos&action=create" class="btn btn-cyan">
                                            <i class="bi bi-plus-circle me-1"></i> Cadastrar Vidraria
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vidrarias as $v): ?>
                                <tr>
                                    <td class="text-start ps-4 fw-bold text-dark">
                                        <?= htmlspecialchars($v['nome']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($v['capacidade_volume'] ?? '—') ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold"><?= (int) $v['quantidade_total'] ?></td>
                                    <td>
                                        <span class="badge-status status-ok">
                                            <?= (int) $v['quantidade_disponivel'] ?> un
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status status-critico">
                                            <?= (int) $v['quantidade_quebrada'] ?> un
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status status-alerta">
                                            <?= (int) $v['quantidade_defeituosa'] ?> un
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                            onclick="abrirModalAvaria(<?= (int) $v['id'] ?>, '<?= htmlspecialchars(addslashes($v['nome'])) ?>')"
                                            title="Registrar quebra ou trinca">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Registrar Avaria
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

<?php else: ?>
    <!-- ==================================================== -->
    <!-- TABELA DE EQUIPAMENTOS PADRÃO                        -->
    <!-- ==================================================== -->
    <div class="card card-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-dark text-white">
                        <tr>
                            <th class="text-start ps-4">Equipamento</th>
                            <th>Patrimônio</th>
                            <th>Periodicidade</th>
                            <th>Última Revisão</th>
                            <th>Próxima Revisão</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($equipamentos)): ?>
                            <tr>
                                <td colspan="6" class="py-0">
                                    <div class="empty-state">
                                        <i class="bi bi-cpu"></i>
                                        <h5>Nenhum equipamento cadastrado</h5>
                                        <p>Cadastre o primeiro equipamento do laboratório.</p>
                                        <a href="index.php?route=equipamentos&action=create" class="btn btn-cyan">
                                            <i class="bi bi-plus-circle me-1"></i> Cadastrar Equipamento
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php
                            $hoje = date('Y-m-d');
                            foreach ($equipamentos as $eq):
                                $prox = $eq['proxima_manutencao'] ?? null;
                                $isVencida = (!empty($prox) && $prox < $hoje);
                                ?>
                                <tr class="<?= $isVencida ? 'table-row-vencido' : '' ?>">
                                    <td class="text-start ps-4 fw-semibold text-dark">
                                        <?= htmlspecialchars($eq['nome']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($eq['codigo_patrimonio'] ?? '—') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= !empty($eq['frequencia_meses'])
                                            ? (int) $eq['frequencia_meses'] . ' meses'
                                            : '<span class="text-muted small">Manual</span>' ?>
                                    </td>
                                    <td class="small">
                                        <?= !empty($eq['ultima_manutencao'])
                                            ? date('d/m/Y', strtotime($eq['ultima_manutencao']))
                                            : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="small">
                                        <?= !empty($eq['proxima_manutencao'])
                                            ? date('d/m/Y', strtotime($eq['proxima_manutencao']))
                                            : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td>
                                        <?php if ($isVencida): ?>
                                            <span class="badge-status status-critico">
                                                Revisão Atrasada
                                            </span>
                                        <?php elseif (!empty($prox)): ?>
                                            <span class="badge-status status-ok">
                                                Em Dia
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-status status-neutro">
                                                Inspeção Manual
                                            </span>
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
<?php endif; ?>

<!-- ======================================================== -->
<!-- MODAL: REGISTRAR QUEBRA / DEFEITO                        -->
<!-- ======================================================== -->
<div class="modal fade" id="modalAvaria" tabindex="-1" aria-labelledby="avariaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="index.php?route=equipamentos&action=registrarAvaria" method="POST"
            class="modal-content border-0 shadow">
            <div class="modal-header modal-header-lab">
                <h5 class="modal-title fw-bold" id="avariaTitulo">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Registrar Quebra ou Avaria
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="avaria_item_id">

                <div class="alert alert-warning border-0 d-flex align-items-center gap-2 py-2 mb-3">
                    <i class="bi bi-info-circle fs-5"></i>
                    <div class="small">
                        Vidraria: <strong id="avaria_item_nome"></strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="tipo_avaria">Tipo da Ocorrência</label>
                    <select name="tipo_avaria" id="tipo_avaria" class="form-select form-pill" required>
                        <option value="quebra">Quebra Completa (Inutilizado)</option>
                        <option value="defeito">Trinca / Fissura / Boca Lascada</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label" for="avaria_quantidade">Quantidade Envolvida</label>
                    <input type="number" name="quantidade" id="avaria_quantidade" class="form-control form-pill"
                        value="1" min="1" required>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Fechar
                </button>
                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                    <i class="bi bi-check-lg me-1"></i> Confirmar Baixa
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalAvaria(id, nome) {
        document.getElementById('avaria_item_id').value = id;
        document.getElementById('avaria_item_nome').innerText = nome;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAvaria')).show();
    }
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>