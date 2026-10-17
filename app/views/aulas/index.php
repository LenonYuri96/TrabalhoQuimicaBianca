<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Agenda de Aulas & Materiais do Dia</h3>
        <p class="text-muted small mb-0">Organização diária por matéria e separação automática dos materiais das
            práticas</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgendarAula">
        <i class="bi bi-calendar-plus me-1"></i> Agendar Aula / Prática
    </button>
</div>

<!-- Filtro por Data -->
<div class="card card-custom p-3 mb-4">
    <form method="GET" class="row g-2 align-items-center">
        <input type="hidden" name="route" value="aulas">
        <div class="col-auto fw-bold text-muted">Filtrar Dia:</div>
        <div class="col-auto">
            <input type="date" name="data" class="form-control"
                value="<?= htmlspecialchars($_GET['data'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        </div>
    </form>
</div>

<div class="row g-4">
    <!-- Grade de Aulas do Dia -->
    <div class="col-lg-7">
        <div class="card card-custom p-3">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Aulas Agendadas para
                <?= date('d/m/Y', strtotime($_GET['data'] ?? date('Y-m-d'))) ?>
            </h5>

            <?php if (empty($aulas)): ?>
                <div class="alert alert-info mb-0">Nenhuma aula ou prática agendada para esta data.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Horário</th>
                                <th>Matéria / Turma</th>
                                <th>Professor</th>
                                <th>Prática Vinculada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aulas as $a): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($a['horario']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($a['materia']) ?></strong>
                                        <div class="small text-muted"><?= htmlspecialchars($a['turma']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($a['professor']) ?></td>
                                    <td>
                                        <?php if (!empty($a['pratica_titulo'])): ?>
                                            <a href="index.php?route=praticas&action=view&id=<?= $a['pratica_id'] ?>"
                                                class="badge bg-success text-decoration-none">
                                                <?= htmlspecialchars($a['pratica_titulo']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Teórica / Sem roteiro</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Separação e Separação de Materiais das Práticas do Dia -->
    <div class="col-lg-5">
        <div class="card card-custom p-3 border-top border-4 border-info">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-clipboard-check text-info me-2"></i>Materiais a Separar
                na Bancada</h5>
            <p class="text-muted small">Itens exigidos pelas práticas do dia selecionado:</p>

            <h6 class="fw-bold text-danger mt-3">Reagentes Químicos</h6>
            <ul class="list-group list-group-flush mb-2">
                <?php if (empty($materiaisDoDia['quimico'])): ?>
                    <li class="list-group-item text-muted small">Nenhum reagente necessário</li>
                <?php else:
                    foreach ($materiaisDoDia['quimico'] as $q): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><strong><?= htmlspecialchars($q['item_nome']) ?></strong> <small
                                    class="text-muted">(<?= htmlspecialchars($q['materia']) ?>)</small></span>
                            <span class="badge bg-danger"><?= htmlspecialchars($q['qtd']) ?></span>
                        </li>
                    <?php endforeach; endif; ?>
            </ul>

            <h6 class="fw-bold text-info mt-3">Vidrarias</h6>
            <ul class="list-group list-group-flush mb-2">
                <?php if (empty($materiaisDoDia['vidraria'])): ?>
                    <li class="list-group-item text-muted small">Nenhuma vidraria necessária</li>
                <?php else:
                    foreach ($materiaisDoDia['vidraria'] as $v): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><strong><?= htmlspecialchars($v['item_nome']) ?></strong>
                                <?= $v['capacidade'] ? "({$v['capacidade']})" : '' ?></span>
                            <span class="badge bg-info text-white"><?= htmlspecialchars($v['qtd']) ?></span>
                        </li>
                    <?php endforeach; endif; ?>
            </ul>

            <h6 class="fw-bold text-secondary mt-3">Equipamentos / Aparelhos</h6>
            <ul class="list-group list-group-flush">
                <?php if (empty($materiaisDoDia['equipamento'])): ?>
                    <li class="list-group-item text-muted small">Nenhum equipamento fixo requisitado</li>
                <?php else:
                    foreach ($materiaisDoDia['equipamento'] as $eq): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><strong><?= htmlspecialchars($eq['item_nome']) ?></strong></span>
                            <span class="badge bg-secondary"><?= htmlspecialchars($eq['qtd']) ?></span>
                        </li>
                    <?php endforeach; endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Modal Agendar Aula -->
<div class="modal fade" id="modalAgendarAula" tabindex="-1">
    <div class="modal-dialog">
        <form action="index.php?route=aulas&action=create" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Agendar Nova Aula / Prática</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-2">
                <div class="col-6">
                    <label class="form-label">Data</label>
                    <input type="date" name="data_aula" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Horário</label>
                    <input type="text" name="horario" class="form-control" placeholder="08:00 - 10:00" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Matéria / Disciplina</label>
                    <input type="text" name="materia" class="form-control" placeholder="Ex: Físico-Química II" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Turma</label>
                    <input type="text" name="turma" class="form-control" placeholder="Eng. Química 4º Semestre"
                        required>
                </div>
                <div class="col-6">
                    <label class="form-label">Professor</label>
                    <input type="text" name="professor" class="form-control" placeholder="Nome do docente" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Roteiro de Prática Associado</label>
                    <select name="pratica_id" class="form-select">
                        <option value="">Nenhum (Apenas Aula Teórica)</option>
                        <?php foreach ($praticas as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo']) ?>
                                (<?= htmlspecialchars($p['materia_curso']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary">Agendar Aula</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>