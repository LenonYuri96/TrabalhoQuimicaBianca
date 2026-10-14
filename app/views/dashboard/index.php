<?php
/** @var array $stats */
/** @var array $ultimasOcorrencias */
/** @var array $revisoesCriticas */
require __DIR__ . '/../layouts/header.php';
?>

<div class="row align-items-center mb-4 g-3">
    <div class="col-12 col-md-7">
        <h2 class="fw-bold text-dark mb-1">Painel Geral do Laboratório</h2>
        <p class="text-muted mb-0">Monitoramento analítico integrado com auditoria de inteligência artificial</p>
    </div>
    <div class="col-12 col-md-5 text-md-end d-flex gap-2 justify-content-md-end">
        <button id="btnAuditoriaIa" class="btn btn-dark rounded-pill px-4 shadow-sm fw-semibold">
            <i class="bi bi-robot text-warning me-1"></i> Gerar Auditoria IA
        </button>
    </div>
</div>

<!-- PAINEL DA AUDITORIA IA (Inicia oculto e surge com animação) -->
<div class="card card-custom border-2 border-primary mb-4 p-4 d-none shadow" id="boxAuditoriaIa">
    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 bg-primary text-white rounded-circle">
                <i class="bi bi-cpu fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-dark">Parecer do Auditor Inteligente</h5>
                <small class="text-muted">Diagnóstico de conformidade baseado nos dados atuais do laboratório</small>
            </div>
        </div>
        <div class="text-end">
            <span class="badge rounded-pill px-3 py-2 fs-6" id="badgeScoreIa">Score: --</span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="alert alert-light border mb-0" id="iaDiagnosticoTexto"></div>
        </div>
        <div class="col-12 col-md-6">
            <h6 class="fw-bold text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Riscos Críticos
                Identificados</h6>
            <ul class="list-group list-group-flush small" id="iaListaRiscos"></ul>
        </div>
        <div class="col-12 col-md-6">
            <h6 class="fw-bold text-success"><i class="bi bi-check2-circle me-1"></i> Plano de Ação Recomendado</h6>
            <ul class="list-group list-group-flush small" id="iaListaAcoes"></ul>
        </div>
    </div>
</div>

<!-- Cards com Indicadores (KPIs) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-start border-primary border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Reagentes Cadastrados</span>
                    <h3 class="fw-bold my-1 text-dark" id="statTotalQuimicos"><?= $stats['total_quimicos'] ?></h3>
                    <small class="text-success"><i class="bi bi-check-circle"></i> <?= $stats['quimicos_validos'] ?> em
                        validade</small>
                </div>
                <div class="kpi-icon bg-primary-subtle text-primary">
                    <i class="bi bi-droplet-half fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-start border-danger border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Reagentes Vencidos</span>
                    <h3 class="fw-bold my-1 text-danger" id="statQuimicosVencidos"><?= $stats['quimicos_vencidos'] ?>
                    </h3>
                    <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Requer
                        descarte/faseamento</small>
                </div>
                <div class="kpi-icon bg-danger-subtle text-danger">
                    <i class="bi bi-calendar-x fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-start border-warning border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Equipamentos</span>
                    <h3 class="fw-bold my-1 text-dark"><?= $stats['total_equipamentos'] ?></h3>
                    <small class="<?= $stats['manutencoes_atrasadas'] > 0 ? 'text-danger' : 'text-success' ?>">
                        <i class="bi bi-tools"></i> <span
                            id="statManutAtrasadas"><?= $stats['manutencoes_atrasadas'] ?></span> pendências
                    </small>
                </div>
                <div class="kpi-icon bg-warning-subtle text-warning">
                    <i class="bi bi-wrench-adjustable fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-start border-info border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Ocorrências</span>
                    <h3 class="fw-bold my-1 text-dark" id="statTotalOcorrencias"><?= $stats['total_ocorrencias'] ?></h3>
                    <small class="text-info"><i class="bi bi-clipboard2-pulse"></i> Registros operacionais</small>
                </div>
                <div class="kpi-icon bg-info-subtle text-info">
                    <i class="bi bi-card-checklist fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabelas Resumo do Dashboard -->
<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-event me-2 text-primary"></i>Próximas
                    Preventivas</h5>
                <a href="index.php?route=equipamentos" class="btn btn-sm btn-outline-primary rounded-pill">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Equipamento</th>
                                <th>Próx. Data</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($revisoesCriticas)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Nenhum equipamento cadastrado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($revisoesCriticas as $eq):
                                    $isAtrasada = ($eq['proxima_manutencao'] && $eq['proxima_manutencao'] < date('Y-m-d'));
                                    ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($eq['nome']) ?></td>
                                        <td><?= !empty($eq['proxima_manutencao']) ? date('d/m/Y', strtotime($eq['proxima_manutencao'])) : 'N/A' ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $isAtrasada ? 'bg-danger' : 'bg-success' ?> rounded-pill">
                                                <?= $isAtrasada ? 'Atrasada' : 'Em dia' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-exclamation me-2 text-warning"></i>Últimas
                    Ocorrências</h5>
                <a href="index.php?route=ocorrencias" class="btn btn-sm btn-outline-warning rounded-pill">Ver livro</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item / Fato</th>
                                <th>Relator</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ultimasOcorrencias)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Nenhuma ocorrência registrada.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ultimasOcorrencias as $oc): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($oc['item_nome']) ?></div>
                                            <small
                                                class="text-muted"><?= htmlspecialchars(substr($oc['descricao'], 0, 45)) ?>...</small>
                                        </td>
                                        <td><span
                                                class="badge bg-light text-dark border"><?= htmlspecialchars($oc['relator_nome']) ?></span>
                                        </td>
                                        <td><small><?= date('d/m H:i', strtotime($oc['data_hora'])) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>