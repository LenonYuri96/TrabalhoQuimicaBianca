<?php
/** @var array $stats */
/** @var array $ultimasOcorrencias */
/** @var array $revisoesCriticas */
/** @var array $aulasHoje */

$stats = $stats ?? [
    'total_quimicos' => 0,
    'quimicos_vencidos' => 0,
    'quimicos_validos' => 0,
    'total_equipamentos' => 0,
    'manutencoes_atrasadas' => 0,
    'total_ocorrencias' => 0,
    'total_vidrarias' => 0,
    'vidrarias_quebradas' => 0,
    'total_praticas' => 0
];
$ultimasOcorrencias = $ultimasOcorrencias ?? [];
$revisoesCriticas = $revisoesCriticas ?? [];
$aulasHoje = $aulasHoje ?? [];

require __DIR__ . '/../layouts/header.php';
?>

<!-- CABEÇALHO DO DASHBOARD & AUDITORIA IA -->
<div class="row align-items-center mb-4 g-3">
    <div class="col-12 col-md-7">
        <h2 class="fw-bold text-dark mb-1">Painel Operacional do Laboratório</h2>
        <p class="text-muted mb-0">Controle analítico de reagentes, preventivas, vidrarias e inteligência preditiva</p>
    </div>
    <div class="col-12 col-md-5 text-md-end d-flex gap-2 justify-content-md-end">
        <button id="btnAuditoriaIa"
            class="btn btn-dark rounded-pill px-4 shadow-sm fw-semibold d-inline-flex align-items-center gap-2">
            <i class="bi bi-robot text-warning fs-5"></i>
            <span id="btnAuditoriaTexto">Auditoria do Laboratório (IA)</span>
        </button>
    </div>
</div>

<!-- BARRA DE ATALHOS RÁPIDOS -->
<div class="card card-custom border-0 p-3 mb-4 bg-white shadow-sm">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="text-muted small fw-bold text-uppercase"><i
                class="bi bi-lightning-charge text-warning me-1"></i>Ações Rápidas:</span>
        <div class="d-flex flex-wrap gap-2">
            <a href="index.php?route=aulas" class="btn btn-sm btn-outline-primary rounded-pill">
                <i class="bi bi-calendar-plus me-1"></i> Agendar Aula
            </a>
            <a href="index.php?route=praticas&action=create" class="btn btn-sm btn-outline-info rounded-pill">
                <i class="bi bi-journal-plus me-1"></i> Nova Prática
            </a>
            <a href="index.php?route=quimicos&action=create" class="btn btn-sm btn-outline-success rounded-pill">
                <i class="bi bi-plus-circle me-1"></i> Novo Reagente
            </a>
            <a href="index.php?route=equipamentos&aba=vidrarias" class="btn btn-sm btn-outline-danger rounded-pill">
                <i class="bi bi-exclamation-octagon me-1"></i> Baixa em Vidraria
            </a>
            <a href="index.php?route=ocorrencias&action=create"
                class="btn btn-sm btn-outline-warning text-dark rounded-pill">
                <i class="bi bi-shield-plus me-1"></i> Relatar Ocorrência
            </a>
        </div>
    </div>
</div>

<!-- PAINEL DA AUDITORIA IA (Inicia oculto e surge com animação) -->
<div class="card card-custom border-2 border-primary mb-4 p-4 d-none shadow-lg position-relative" id="boxAuditoriaIa">
    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 bg-primary text-white rounded-circle shadow-sm">
                <i class="bi bi-cpu fs-3"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0 text-dark">Parecer do Auditor Inteligente</h5>
                <small class="text-muted">Conformidade operacional gerada em tempo real com base no acervo e
                    ocorrências</small>
            </div>
        </div>
        <div class="text-end">
            <div class="small text-muted mb-1">Índice de Saúde Operacional</div>
            <span class="badge rounded-pill px-3 py-2 fs-6 shadow-sm" id="badgeScoreIa">Calculando...</span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="alert alert-light border border-primary-subtle p-3 mb-0" id="iaDiagnosticoTexto">
                <!-- Diagnóstico via JS -->
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="p-3 bg-danger-subtle rounded-3 h-100">
                <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Riscos
                    Críticos Iminentes</h6>
                <ul class="list-group list-group-flush small bg-transparent" id="iaListaRiscos"></ul>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="p-3 bg-success-subtle rounded-3 h-100">
                <h6 class="fw-bold text-success mb-2"><i class="bi bi-check2-circle me-1"></i> Plano de Ação Recomendado
                </h6>
                <ul class="list-group list-group-flush small bg-transparent" id="iaListaAcoes"></ul>
            </div>
        </div>
    </div>
</div>

<!-- CARDS COM INDICADORES (KPIS) -->
<div class="row g-3 mb-4">
    <!-- Reagentes -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-start border-primary border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Reagentes em Estoque</span>
                    <h3 class="fw-bold my-1 text-dark" id="statTotalQuimicos"><?= $stats['total_quimicos'] ?></h3>
                    <small class="text-success"><i class="bi bi-check-circle me-1"></i><?= $stats['quimicos_validos'] ?>
                        regulares</small>
                </div>
                <div class="kpi-icon bg-primary-subtle text-primary">
                    <i class="bi bi-droplet-half fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Vencidos -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-start border-danger border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Reagentes Vencidos</span>
                    <h3 class="fw-bold my-1 text-danger" id="statQuimicosVencidos"><?= $stats['quimicos_vencidos'] ?>
                    </h3>
                    <small class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>Isolar p/
                        descarte</small>
                </div>
                <div class="kpi-icon bg-danger-subtle text-danger">
                    <i class="bi bi-calendar-x fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Preventivas / Aparelhos -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-start border-warning border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Aparelhos & Preventivas</span>
                    <h3 class="fw-bold my-1 text-dark"><?= $stats['total_equipamentos'] ?></h3>
                    <small class="<?= $stats['manutencoes_atrasadas'] > 0 ? 'text-danger fw-bold' : 'text-success' ?>">
                        <i class="bi bi-tools me-1"></i>
                        <span id="statManutAtrasadas"><?= $stats['manutencoes_atrasadas'] ?></span> revisão atrasada
                    </small>
                </div>
                <div class="kpi-icon bg-warning-subtle text-warning">
                    <i class="bi bi-wrench-adjustable fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Ocorrências / Livro Negro -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-start border-info border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Ocorrências / Avarias</span>
                    <h3 class="fw-bold my-1 text-dark" id="statTotalOcorrencias"><?= $stats['total_ocorrencias'] ?></h3>
                    <small class="text-info"><i class="bi bi-clipboard2-pulse me-1"></i>Livro de registros</small>
                </div>
                <div class="kpi-icon bg-info-subtle text-info">
                    <i class="bi bi-card-checklist fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABELAS RESUMO DO DASHBOARD -->
<div class="row g-4">
    <!-- Coluna 1: Próximas Preventivas de Equipamentos -->
    <div class="col-12 col-lg-6">
        <div class="card card-custom h-100 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-calendar-event me-2 text-primary"></i>Manutenção Preventiva de Aparelhos
                </h5>
                <a href="index.php?route=equipamentos" class="btn btn-sm btn-outline-primary rounded-pill">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start ps-3">Equipamento</th>
                                <th>Próx. Data</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($revisoesCriticas)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Nenhum equipamento cadastrado.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($revisoesCriticas as $eq):
                                    $hoje = date('Y-m-d');
                                    $prox = $eq['proxima_manutencao'];
                                    $isAtrasada = (!empty($prox) && $prox < $hoje);
                                ?>
                            <tr class="<?= $isAtrasada ? 'table-row-vencido' : '' ?>">
                                <td class="text-start ps-3 fw-semibold"><?= htmlspecialchars($eq['nome']) ?></td>
                                <td><?= !empty($prox) ? date('d/m/Y', strtotime($prox)) : 'Manual' ?></td>
                                <td>
                                    <?php if ($isAtrasada): ?>
                                    <span class="badge bg-danger rounded-pill px-2">Atrasada</span>
                                    <?php elseif (!empty($prox)): ?>
                                    <span class="badge bg-success rounded-pill px-2">Em dia</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill px-2">Manual</span>
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
    </div>

    <!-- Coluna 2: Últimas Ocorrências Registradas -->
    <div class="col-12 col-lg-6">
        <div class="card card-custom h-100 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-exclamation me-2 text-warning"></i>Últimas Ocorrências
                </h5>
                <a href="index.php?route=ocorrencias" class="btn btn-sm btn-outline-warning rounded-pill">Ver livro</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Item / Incidente</th>
                                <th>Categoria</th>
                                <th>Relator</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ultimasOcorrencias)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Nenhuma ocorrência registrada.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($ultimasOcorrencias as $oc): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($oc['item_nome']) ?></div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 220px;">
                                        <?= htmlspecialchars($oc['descricao']) ?>
                                    </small>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-light text-dark border"><?= htmlspecialchars($oc['categoria']) ?></span>
                                </td>
                                <td>
                                    <small class="text-secondary"><?= htmlspecialchars($oc['relator_nome']) ?></small>
                                </td>
                                <td>
                                    <small
                                        class="text-muted"><?= date('d/m H:i', strtotime($oc['data_hora'])) ?></small>
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
</div>

<!-- ======================================================== -->
<!-- SCRIPT: DISPARO E PROCESSAMENTO DA AUDITORIA IA          -->
<!-- ======================================================== -->
<script>
document.getElementById('btnAuditoriaIa').addEventListener('click', function() {
    const btn = this;
    const btnTexto = document.getElementById('btnAuditoriaTexto');
    const boxIa = document.getElementById('boxAuditoriaIa');
    const badgeScore = document.getElementById('badgeScoreIa');
    const diagnosticoTexto = document.getElementById('iaDiagnosticoTexto');
    const listaRiscos = document.getElementById('iaListaRiscos');
    const listaAcoes = document.getElementById('iaListaAcoes');

    // Extrai valores atuais dos KPIs para enviar à IA
    const vencidos = parseInt(document.getElementById('statQuimicosVencidos').innerText) || 0;
    const manutAtrasadas = parseInt(document.getElementById('statManutAtrasadas').innerText) || 0;
    const totalOcorrencias = parseInt(document.getElementById('statTotalOcorrencias').innerText) || 0;

    // Estado visual de carregamento
    btn.disabled = true;
    btnTexto.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Auditando laboratório...';

    fetch('index.php?route=ai&action=auditoriaGeral', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                quimicos_vencidos: vencidos,
                manutencoes_atrasadas: manutAtrasadas,
                total_ocorrencias: totalOcorrencias
            })
        })
        .then(r => {
            if (!r.ok) throw new Error('Falha na resposta da auditoria');
            return r.json();
        })
        .then(data => {
            // Exibe o painel com transição
            boxIa.classList.remove('d-none');
            boxIa.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Score e Badges
            const score = data.score !== undefined ? data.score : 70;
            badgeScore.innerText = `Saúde: ${score}/100 (${data.status_geral || 'Alerta'})`;

            if (score >= 80) {
                badgeScore.className = 'badge rounded-pill px-3 py-2 fs-6 bg-success text-white shadow-sm';
            } else if (score >= 50) {
                badgeScore.className = 'badge rounded-pill px-3 py-2 fs-6 bg-warning text-dark shadow-sm';
            } else {
                badgeScore.className = 'badge rounded-pill px-3 py-2 fs-6 bg-danger text-white shadow-sm';
            }

            // Diagnóstico
            diagnosticoTexto.innerHTML =
                `<strong>Diagnóstico do Auditor:</strong> ${data.diagnostico || 'Sem observações críticas adicionais.'}`;

            // Riscos
            listaRiscos.innerHTML = '';
            if (data.riscos_principais && data.riscos_principais.length > 0) {
                data.riscos_principais.forEach(r => {
                    listaRiscos.innerHTML +=
                        `<li class="list-group-item bg-transparent text-danger px-0 py-1"><i class="bi bi-dot fs-5"></i>${r}</li>`;
                });
            }

            // Ações
            listaAcoes.innerHTML = '';
            if (data.plano_acao && data.plano_acao.length > 0) {
                data.plano_acao.forEach(a => {
                    listaAcoes.innerHTML +=
                        `<li class="list-group-item bg-transparent text-success px-0 py-1"><i class="bi bi-arrow-right-short fs-5"></i>${a}</li>`;
                });
            }
        })
        .catch(err => {
            alert(
                'Não foi possível concluir a auditoria inteligente. Tente novamente em alguns instantes.'
                );
        })
        .finally(() => {
            btn.disabled = false;
            btnTexto.innerHTML = 'Auditoria do Laboratório (IA)';
        });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>