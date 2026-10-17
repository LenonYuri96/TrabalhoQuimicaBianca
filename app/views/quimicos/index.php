<?php
/**
 * ============================================================================
 * LABMANAGER - INVENTÁRIO DE QUÍMICOS & REAGENTES
 * ----------------------------------------------------------------------------
 * Tela principal de gestão do acervo químico do laboratório.
 * Recursos integrados:
 *   - Filtro dinâmico de tabela
 *   - Consulta FISPQ/FDS via IA (AiController::consultarQuimico)
 *   - Práticas vinculadas ao reagente (AJAX)
 *   - Mapa de Incompatibilidades de Armazenamento (IA)
 *   - Calculadora de Diluição C1.V1 = C2.V2
 *   - Gerador de QR Code para etiqueta de bancada
 *   - Exportação CSV e impressão
 * ============================================================================
 *
 * @var array $quimicos
 */
$quimicos = $quimicos ?? [];
require __DIR__ . '/../layouts/header.php';

// ---- Estatísticas rápidas para os KPIs do topo -----------------------------
$hoje = date('Y-m-d');
$totalQuimicos   = count($quimicos);
$totalVencidos   = 0;
$totalVencendo   = 0;
$totalCriticos   = 0;
$em30Dias        = date('Y-m-d', strtotime('+30 days'));

foreach ($quimicos as $q) {
    $val = $q['validade'] ?? null;
    if (!empty($val)) {
        if ($val < $hoje) {
            $totalVencidos++;
        } elseif ($val <= $em30Dias) {
            $totalVencendo++;
        }
    }
    if ((float) ($q['quantidade'] ?? 0) <= 1.0) {
        $totalCriticos++;
    }
}
?>

<!-- ======================================================== -->
<!-- CABEÇALHO DA PÁGINA                                      -->
<!-- ======================================================== -->
<div class="row align-items-center justify-content-between mb-4 g-3">
    <div class="col-12 col-lg-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="index.php?route=dashboard">Início</a></li>
                <li class="breadcrumb-item active" aria-current="page">Químicos</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-droplet-half text-lab-cyan"></i>
            Inventário de Químicos & Reagentes
        </h3>
        <p class="text-muted small mb-0">
            Rastreabilidade, Ficha de Segurança (FISPQ) e práticas associadas
        </p>
    </div>

    <div class="col-12 col-lg-7">
        <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
            <!-- Filtro -->
            <div class="input-group input-group-sm flex-grow-1 flex-lg-grow-0" style="max-width: 260px;">
                <span class="input-group-text bg-white border-end-0 rounded-start-pill">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="filtroTabela" class="form-control border-start-0 rounded-end-pill"
                    placeholder="Filtrar reagente..." aria-label="Filtrar reagente">
            </div>

            <!-- Mapa de Incompatibilidades -->
            <button type="button" class="btn btn-outline-warning rounded-pill fw-semibold"
                onclick="abrirMatrizIncompatibilidade()" title="Auditar armazenamento e riscos cruzados">
                <i class="bi bi-shield-shaded me-1"></i>
                <span class="d-none d-sm-inline">Mapa de Incompatibilidades</span>
                <span class="d-sm-none">Incompatib.</span>
            </button>

            <!-- Calculadora -->
            <button type="button" class="btn btn-outline-info rounded-pill fw-semibold" data-bs-toggle="modal"
                data-bs-target="#modalCalculadoraDiluicao" title="Calcular diluição C1.V1 = C2.V2">
                <i class="bi bi-calculator me-1"></i>
                <span class="d-none d-sm-inline">Calculadora C1.V1 = C2.V2</span>
                <span class="d-sm-none">Calc.</span>
            </button>

            <!-- Novo Reagente -->
            <a href="index.php?route=quimicos&action=create" class="btn btn-cyan fw-semibold rounded-pill px-3">
                <i class="bi bi-plus-circle me-1"></i> Novo Reagente
            </a>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- KPIs RÁPIDOS                                             -->
<!-- ======================================================== -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <div>
                <div class="kpi-metric-number text-dark"><?= $totalQuimicos ?></div>
                <div class="kpi-metric-label">Total Cadastrado</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card kpi-danger">
            <div class="kpi-icon">
                <i class="bi bi-x-octagon"></i>
            </div>
            <div>
                <div class="kpi-metric-number text-danger"><?= $totalVencidos ?></div>
                <div class="kpi-metric-label">Vencidos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card kpi-warning">
            <div class="kpi-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="kpi-metric-number text-warning"><?= $totalVencendo ?></div>
                <div class="kpi-metric-label">Vencem em 30d</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#e0f2fe;color:#075985;">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div>
                <div class="kpi-metric-number text-info"><?= $totalCriticos ?></div>
                <div class="kpi-metric-label">Estoque Crítico</div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- TABELA PRINCIPAL                                         -->
<!-- ======================================================== -->
<div class="card card-custom shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" id="tabelaQuimicos">
                <thead class="table-dark">
                    <tr>
                        <th class="text-start ps-4">Nome do Reagente</th>
                        <th>Qtd</th>
                        <th>Validade</th>
                        <th class="d-none d-md-table-cell">Armazenamento</th>
                        <th class="d-none d-lg-table-cell">Periculosidade</th>
                        <th class="d-none d-lg-table-cell">Estado</th>
                        <th>Status</th>
                        <th style="width: 1%;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quimicos)): ?>
                    <tr>
                        <td colspan="8" class="py-0">
                            <div class="empty-state">
                                <i class="bi bi-inboxes"></i>
                                <h5>Nenhum reagente cadastrado</h5>
                                <p>Comece cadastrando o primeiro produto químico do laboratório.</p>
                                <a href="index.php?route=quimicos&action=create" class="btn btn-cyan">
                                    <i class="bi bi-plus-circle me-1"></i> Cadastrar Reagente
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($quimicos as $q):
                            $isVencido = (!empty($q['validade']) && $q['validade'] < $hoje);
                            $isVencendo = (!$isVencido && !empty($q['validade']) && $q['validade'] <= $em30Dias);
                            $isCritico  = ((float) ($q['quantidade'] ?? 0) <= 1.0);

                            // Status visual
                            if ($isVencido) {
                                $statusLabel = 'VENCIDO';
                                $statusClass = 'status-critico';
                            } elseif ($isVencendo) {
                                $statusLabel = 'A VENCER';
                                $statusClass = 'status-alerta';
                            } elseif ($isCritico) {
                                $statusLabel = 'CRÍTICO';
                                $statusClass = 'status-alerta';
                            } else {
                                $statusLabel = 'EM DIA';
                                $statusClass = 'status-ok';
                            }

                            $rowClass = $isVencido ? 'table-row-vencido' : ($isVencendo ? 'table-row-alerta' : '');
                            ?>
                    <tr class="<?= $rowClass ?>" data-id="<?= (int) $q['id'] ?>">
                        <td class="text-start ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-lab" aria-hidden="true">
                                    <?= strtoupper(mb_substr($q['nome'], 0, 2)) ?>
                                </div>
                                <div class="lh-sm">
                                    <div class="fw-bold text-dark nome-item">
                                        <?= htmlspecialchars($q['nome']) ?>
                                    </div>
                                    <small class="text-muted d-lg-none">
                                        <?= htmlspecialchars($q['estado'] ?: '—') ?>
                                    </small>
                                </div>
                            </div>
                        </td>

                        <td class="fw-semibold">
                            <?php
                                    $qtd = (float) ($q['quantidade'] ?? 0);
                                    echo htmlspecialchars(rtrim(rtrim(number_format($qtd, 2, ',', ''), '0'), ','));
                                    ?>
                        </td>

                        <td>
                            <?php if (!empty($q['validade'])): ?>
                            <span class="d-block small">
                                <?= date('d/m/Y', strtotime($q['validade'])) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>

                        <td class="d-none d-md-table-cell">
                            <span class="small text-secondary">
                                <?= htmlspecialchars($q['tipo_armazenamento'] ?: 'Não informado') ?>
                            </span>
                        </td>

                        <td class="d-none d-lg-table-cell">
                            <span class="small text-secondary">
                                <?= htmlspecialchars($q['periculosidade'] ?: 'Não informado') ?>
                            </span>
                        </td>

                        <td class="d-none d-lg-table-cell">
                            <span class="badge bg-light text-dark border">
                                <?= htmlspecialchars($q['estado'] ?: '—') ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge-status <?= $statusClass ?>">
                                <?= $statusLabel ?>
                            </span>
                        </td>

                        <td>
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false" aria-label="Ações para <?= htmlspecialchars($q['nome']) ?>">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <button class="dropdown-item" type="button"
                                            onclick="consultarFispq(<?= (int) $q['id'] ?>, '<?= htmlspecialchars(addslashes($q['nome'])) ?>')">
                                            <i class="bi bi-shield-shaded text-info me-2"></i>
                                            Consultar FISPQ / IA
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" type="button"
                                            onclick="verPraticasDoQuimico(<?= (int) $q['id'] ?>, '<?= htmlspecialchars(addslashes($q['nome'])) ?>')">
                                            <i class="bi bi-diagram-3 text-primary me-2"></i>
                                            Práticas Vinculadas
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" type="button"
                                            onclick="gerarQrCode(<?= (int) $q['id'] ?>, '<?= htmlspecialchars(addslashes($q['nome'])) ?>')">
                                            <i class="bi bi-qr-code text-dark me-2"></i>
                                            QR Code da Etiqueta
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" type="button"
                                            onclick="calcularPara(<?= (int) $q['id'] ?>, '<?= htmlspecialchars(addslashes($q['nome'])) ?>')">
                                            <i class="bi bi-calculator text-success me-2"></i>
                                            Calcular Diluição
                                        </button>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="index.php?route=quimicos&action=edit&id=<?= (int) $q['id'] ?>">
                                            <i class="bi bi-pencil text-secondary me-2"></i>
                                            Editar Cadastro
                                        </a>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger" type="button"
                                            onclick="excluirQuimico(<?= (int) $q['id'] ?>, '<?= htmlspecialchars(addslashes($q['nome'])) ?>')">
                                            <i class="bi bi-trash me-2"></i>
                                            Excluir Reagente
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Rodapé da tabela -->
    <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
        <small class="text-muted">
            Exibindo <strong id="contadorVisiveis"><?= $totalQuimicos ?></strong>
            de <strong><?= $totalQuimicos ?></strong> reagentes
        </small>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-ghost btn-sm" onclick="exportarCsv()">
                <i class="bi bi-download me-1"></i> Exportar CSV
            </button>
            <button type="button" class="btn btn-ghost btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 1: FISPQ / FDS (IA)                                -->
<!-- ======================================================== -->
<div class="modal fade" id="modalFispq" tabindex="-1" aria-labelledby="fispqTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header modal-header-lab">
                <h5 class="modal-title fw-bold" id="fispqTitulo">
                    <i class="bi bi-shield-check me-2"></i>
                    Ficha de Segurança (FISPQ/FDS) —
                    <span id="fispq_nome" class="text-info"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4" id="fispq_corpo">
                <!-- Conteúdo injetado via JS -->
            </div>
            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4"
                    data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 2: PRÁTICAS VINCULADAS                             -->
<!-- ======================================================== -->
<div class="modal fade" id="modalPraticasProduto" tabindex="-1" aria-labelledby="praticasTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header"
                style="background:var(--lab-navy);color:#fff;border-bottom:2px solid var(--lab-cyan);">
                <h5 class="modal-title fw-bold" id="praticasTitulo">
                    <i class="bi bi-diagram-3 me-2"></i>
                    Práticas que Usam: <span id="praticas_modal_nome" class="text-info"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4" id="praticas_corpo">
                <!-- Conteúdo AJAX -->
            </div>
            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4"
                    data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 3: MAPA DE INCOMPATIBILIDADES (IA)                 -->
<!-- ======================================================== -->
<div class="modal fade" id="modalIncompatibilidade" tabindex="-1" aria-labelledby="incompTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#78350f,#f59e0b);color:#fff;">
                <h5 class="modal-title fw-bold" id="incompTitulo">
                    <i class="bi bi-shield-shaded me-2"></i>
                    Mapa de Incompatibilidades de Armazenamento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4" id="incompatibilidade_corpo">
                <!-- Conteúdo AJAX -->
            </div>
            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4"
                    data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 4: CALCULADORA DE DILUIÇÃO / MASSA                 -->
<!-- ======================================================== -->
<div class="modal fade" id="modalCalculadoraDiluicao" tabindex="-1" aria-labelledby="calcTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header modal-header-lab">
                <h5 class="modal-title fw-bold" id="calcTitulo">
                    <i class="bi bi-calculator me-2"></i>
                    Calculadora de Preparo de Soluções
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Abas: Diluição / Massa -->
                <ul class="nav nav-pills mb-3 justify-content-center" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-diluicao" data-bs-toggle="pill"
                            data-bs-target="#pane-diluicao" type="button" role="tab">
                            <i class="bi bi-droplet me-1"></i> Diluição
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-massa" data-bs-toggle="pill" data-bs-target="#pane-massa"
                            type="button" role="tab">
                            <i class="bi bi-basket me-1"></i> Massa Sólido
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- ABA DILUIÇÃO -->
                    <div class="tab-pane fade show active" id="pane-diluicao" role="tabpanel">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Substância</label>
                                <input type="text" id="calc_nome" class="form-control form-pill"
                                    value="Ácido Clorídrico (HCl)">
                            </div>
                            <div class="col-4">
                                <label class="form-label">C1 (Inicial)</label>
                                <input type="number" step="0.01" id="calc_c1" class="form-control form-pill"
                                    value="12.0" placeholder="12">
                            </div>
                            <div class="col-4">
                                <label class="form-label">C2 (Desejada)</label>
                                <input type="number" step="0.01" id="calc_c2" class="form-control form-pill" value="0.1"
                                    placeholder="0.1">
                            </div>
                            <div class="col-4">
                                <label class="form-label">V2 (mL)</label>
                                <input type="number" id="calc_v2" class="form-control form-pill" value="500"
                                    placeholder="500">
                            </div>
                        </div>
                    </div>

                    <!-- ABA MASSA -->
                    <div class="tab-pane fade" id="pane-massa" role="tabpanel">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Substância</label>
                                <input type="text" id="calc_massa_nome" class="form-control form-pill"
                                    value="Hidróxido de Sódio (NaOH)">
                            </div>
                            <div class="col-4">
                                <label class="form-label">M (mol/L)</label>
                                <input type="number" step="0.001" id="calc_molaridade" class="form-control form-pill"
                                    value="0.1">
                            </div>
                            <div class="col-4">
                                <label class="form-label">MM (g/mol)</label>
                                <input type="number" step="0.01" id="calc_massa_molar" class="form-control form-pill"
                                    value="40.0">
                            </div>
                            <div class="col-4">
                                <label class="form-label">V (mL)</label>
                                <input type="number" id="calc_volume_massa" class="form-control form-pill" value="1000">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Área de resultado -->
                <div id="resultadoCalculo" class="mt-3 p-3 bg-light rounded-3 d-none"></div>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-ghost btn-sm" onclick="limparCalculadora()">Limpar</button>
                <button type="button" class="btn btn-cyan" onclick="executarCalculo()">
                    <i class="bi bi-lightning-charge me-1"></i> Calcular
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 5: QR CODE DA ETIQUETA                             -->
<!-- ======================================================== -->
<div class="modal fade" id="modalQrCode" tabindex="-1" aria-labelledby="qrTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow text-center p-3">
            <h6 class="fw-bold mb-2" id="qrTitulo">
                <i class="bi bi-qr-code me-1"></i> Etiqueta de Bancada
            </h6>
            <div id="areaQrCode" class="my-3 d-flex justify-content-center"></div>
            <p class="text-muted small mb-3" id="qrNomeProduto"></p>
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-dark btn-sm rounded-pill" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- JAVASCRIPT: LÓGICA COMPLETA DA TELA                      -->
<!-- ======================================================== -->
<script>
(function() {
    'use strict';

    // ========================================================================
    // 1. FILTRO DINÂMICO DE TABELA
    // ========================================================================
    const inputFiltro = document.getElementById('filtroTabela');
    const contador = document.getElementById('contadorVisiveis');

    if (inputFiltro) {
        inputFiltro.addEventListener('input', function() {
            const termo = this.value.toLowerCase().trim();
            const linhas = document.querySelectorAll('#tabelaQuimicos tbody tr[data-id]');
            let visiveis = 0;

            linhas.forEach(linha => {
                const nome = (linha.querySelector('.nome-item') || {}).textContent || '';
                const match = nome.toLowerCase().includes(termo);
                linha.style.display = match ? '' : 'none';
                if (match) visiveis++;
            });
            if (contador) contador.textContent = visiveis;
        });
    }

    // ========================================================================
    // 2. UTILITÁRIOS
    // ========================================================================
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showLoading(el, mensagem) {
        el.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-lab mx-auto mb-3"></div>
                <p class="text-muted fw-semibold small mb-0">${mensagem}</p>
            </div>`;
    }

    function showError(el, msg) {
        el.innerHTML = `
            <div class="alert alert-danger mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-x-circle-fill fs-5"></i>
                <span>${msg}</span>
            </div>`;
    }

    // ========================================================================
    // 3. CONSULTA FISPQ / FDS
    // ========================================================================
    window.consultarFispq = function(id, nomeQuimico) {
        const modalEl = document.getElementById('modalFispq');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        const corpo = document.getElementById('fispq_corpo');

        document.getElementById('fispq_nome').innerText = nomeQuimico;
        showLoading(corpo,
            `Consultando FISPQ e protocolos de segurança para <strong>${escapeHtml(nomeQuimico)}</strong>...`
        );
        modal.show();

        fetch('index.php?route=ai&action=consultarQuimico', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nome: nomeQuimico
                })
            })
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.erro) throw new Error(data.erro);

                const epis = Array.isArray(data.epis_obrigatorios) && data.epis_obrigatorios.length ?
                    data.epis_obrigatorios.map(epi =>
                        `<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1 me-1 mb-1">
                        <i class="bi bi-check-circle me-1"></i>${escapeHtml(epi)}
                    </span>`).join('') :
                    '<span class="text-muted small">EPIs básicos de laboratório (Jaleco, Óculos, Luvas)</span>';

                corpo.innerHTML = `
                <div class="alert alert-warning border-0 d-flex align-items-start gap-3 mb-4">
                    <i class="bi bi-shield-exclamation fs-2 text-warning"></i>
                    <div>
                        <strong class="d-block text-dark mb-1">Classe de Perigo Principal</strong>
                        <span class="text-dark small">${escapeHtml(data.periculosidade_resumo || 'Não classificado')}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="bi bi-person-badge text-primary me-2"></i>EPIs Obrigatórios
                    </h6>
                    <div class="d-flex flex-wrap">${epis}</div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-danger mb-2">
                        <i class="bi bi-exclamation-octagon me-2"></i>Incompatibilidades Químicas
                    </h6>
                    <div class="procedimento-box" style="border-left-color: var(--lab-danger);">
                        ${escapeHtml(data.incompatibilidades || 'Sem incompatibilidades severas conhecidas.')}
                    </div>
                </div>

                <div>
                    <h6 class="fw-bold text-success mb-2">
                        <i class="bi bi-recycle me-2"></i>Protocolo de Descarte & Neutralização
                    </h6>
                    <div class="procedimento-box" style="border-left-color: var(--lab-mint);">
                        ${escapeHtml(data.protocolo_descarte || 'Seguir protocolo municipal para descarte químico.')}
                    </div>
                </div>`;
            })
            .catch(err => {
                console.error('[FISPQ]', err);
                showError(corpo, 'Não foi possível obter os dados técnicos. Tente novamente em instantes.');
            });
    };

    // ========================================================================
    // 4. PRÁTICAS VINCULADAS
    // ========================================================================
    window.verPraticasDoQuimico = function(id, nome) {
        const modalEl = document.getElementById('modalPraticasProduto');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        const corpo = document.getElementById('praticas_corpo');

        document.getElementById('praticas_modal_nome').innerText = nome;
        showLoading(corpo, 'Buscando práticas vinculadas...');
        modal.show();

        fetch(`index.php?route=praticas&action=produtosVinculados&tipo=quimico&id=${id}`)
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(praticas => {
                if (!Array.isArray(praticas) || praticas.length === 0) {
                    corpo.innerHTML = `
                    <div class="empty-state py-4">
                        <i class="bi bi-info-circle"></i>
                        <h5>Nenhuma prática vinculada</h5>
                        <p>Este reagente ainda não está associado a nenhum roteiro de aula prática.</p>
                        <a href="index.php?route=praticas&action=create" class="btn btn-outline-cyan btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Criar Prática
                        </a>
                    </div>`;
                    return;
                }

                let html = '<div class="list-group list-group-flush">';
                praticas.forEach(p => {
                    html += `
                    <a href="index.php?route=praticas&action=view&id=${p.id}"
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-3">
                        <div>
                            <div class="fw-bold text-dark">${escapeHtml(p.titulo)}</div>
                            <small class="text-muted">
                                <i class="bi bi-book me-1"></i>${escapeHtml(p.materia_curso || 'Disciplina não informada')}
                            </small>
                        </div>
                        <span class="badge bg-info rounded-pill px-3 py-2">
                            ${escapeHtml(p.quantidade_necessaria || 'Uso padrão')}
                        </span>
                    </a>`;
                });
                html += '</div>';
                corpo.innerHTML = html;
            })
            .catch(err => {
                console.error('[Práticas]', err);
                showError(corpo, 'Erro ao carregar as práticas vinculadas.');
            });
    };

    // ========================================================================
    // 5. MAPA DE INCOMPATIBILIDADES
    // ========================================================================
    window.abrirMatrizIncompatibilidade = function() {
        const modalEl = document.getElementById('modalIncompatibilidade');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        const corpo = document.getElementById('incompatibilidade_corpo');

        showLoading(corpo, 'Auditando armários e cruzando incompatibilidades...');
        modal.show();

        fetch('index.php?route=ai&action=verificarIncompatibilidadesArmazenamento')
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                const alertas = data.alertas_incompatibilidade || [];
                const totalLocais = data.total_locais_auditados || 0;
                const totalReagentes = data.total_reagentes_analisados || 0;

                // Mapeia cores por gravidade
                const corGravidade = {
                    'Crítica': {
                        bg: '#fef2f2',
                        border: '#ef4444',
                        text: '#991b1b',
                        icon: 'bi-exclamation-octagon-fill',
                        badge: 'status-critico'
                    },
                    'Alta': {
                        bg: '#fffbeb',
                        border: '#f59e0b',
                        text: '#92400e',
                        icon: 'bi-exclamation-triangle-fill',
                        badge: 'status-alerta'
                    },
                    'Média': {
                        bg: '#f0f9ff',
                        border: '#0ea5e9',
                        text: '#075985',
                        icon: 'bi-info-circle-fill',
                        badge: 'status-info'
                    },
                    'Conforme': {
                        bg: '#ecfdf5',
                        border: '#10b981',
                        text: '#065f46',
                        icon: 'bi-check-circle-fill',
                        badge: 'status-ok'
                    }
                };

                let html = `
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3 text-center">
                            <div class="kpi-metric-number text-dark">${totalLocais}</div>
                            <div class="kpi-metric-label">Locais auditados</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3 text-center">
                            <div class="kpi-metric-number text-dark">${totalReagentes}</div>
                            <div class="kpi-metric-label">Reagentes analisados</div>
                        </div>
                    </div>
                </div>`;

                alertas.forEach(a => {
                    const c = corGravidade[a.gravidade] || corGravidade['Média'];
                    const itens = Array.isArray(a.itens) && a.itens.length ?
                        `<div class="mt-2"><small class="text-muted d-block mb-1"><strong>Itens no mesmo local:</strong></small>
                       <div class="d-flex flex-wrap gap-1">
                           ${a.itens.map(i => `<span class="badge bg-light text-dark border small">${escapeHtml(i)}</span>`).join('')}
                       </div></div>` :
                        '';

                    html += `
                    <div class="mb-3 p-3 rounded-3"
                         style="background:${c.bg};border-left:4px solid ${c.border};">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi ${c.icon} fs-4" style="color:${c.border};"></i>
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <strong class="text-dark">${escapeHtml(a.local)}</strong>
                                    <span class="badge-status ${c.badge}">${escapeHtml(a.gravidade)}</span>
                                </div>
                                <p class="small mb-0" style="color:${c.text};">
                                    ${escapeHtml(a.motivo)}
                                </p>
                                ${itens}
                            </div>
                        </div>
                    </div>`;
                });

                if (alertas.length === 0) {
                    html += `
                    <div class="empty-state py-4">
                        <i class="bi bi-shield-check text-success"></i>
                        <h5 class="text-success">Nenhuma incompatibilidade detectada</h5>
                        <p>Todos os reagentes estão armazenados corretamente.</p>
                    </div>`;
                }

                corpo.innerHTML = html;
            })
            .catch(err => {
                console.error('[Incompatibilidade]', err);
                showError(corpo, 'Erro ao auditar incompatibilidades de armazenamento.');
            });
    };

    // ========================================================================
    // 6. CALCULADORA DE DILUIÇÃO / MASSA
    // ========================================================================
    function getAbaAtiva() {
        const tabAtiva = document.querySelector('#modalCalculadoraDiluicao .nav-link.active');
        return tabAtiva && tabAtiva.id === 'tab-massa' ? 'massa' : 'diluicao';
    }

    window.executarCalculo = function() {
        const tipo = getAbaAtiva();
        const div = document.getElementById('resultadoCalculo');

        let body;
        if (tipo === 'diluicao') {
            body = {
                tipo: 'diluicao',
                reagente: document.getElementById('calc_nome').value,
                c1: parseFloat(document.getElementById('calc_c1').value),
                c2: parseFloat(document.getElementById('calc_c2').value),
                v2: parseFloat(document.getElementById('calc_v2').value)
            };
        } else {
            body = {
                tipo: 'massa',
                reagente: document.getElementById('calc_massa_nome').value,
                molaridade: parseFloat(document.getElementById('calc_molaridade').value),
                massa_molar: parseFloat(document.getElementById('calc_massa_molar').value),
                volume_ml: parseFloat(document.getElementById('calc_volume_massa').value)
            };
        }

        div.classList.remove('d-none');
        div.innerHTML =
            '<div class="text-center py-3"><div class="spinner-lab spinner-lab-sm mx-auto"></div></div>';

        fetch('index.php?route=ai&action=calcularDiluicaoIa', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(body)
            })
            .then(r => r.json())
            .then(data => {
                if (!data.sucesso) {
                    div.innerHTML =
                        `<div class="alert alert-danger mb-0 small">${escapeHtml(data.mensagem)}</div>`;
                    return;
                }

                if (tipo === 'diluicao') {
                    div.innerHTML = `
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <strong class="text-success">Alíquota: ${data.volume_aliquota_ml} mL</strong>
                    </div>
                    <p class="small text-muted mb-2">
                        Completar com <strong>${data.volume_solvente_ml} mL</strong> de água destilada
                        · Fator de diluição <strong>${data.fator_diluicao}</strong>
                    </p>
                    <div class="procedimento-box small">${escapeHtml(data.procedimento)}</div>`;
                } else {
                    div.innerHTML = `
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <strong class="text-success">Massa a pesar: ${data.massa_pesar_g} g</strong>
                    </div>
                    <p class="small text-muted mb-2">
                        ${escapeHtml(data.molaridade)} · MM ${escapeHtml(data.massa_molar)} · V ${data.volume_final_ml} mL
                    </p>
                    <div class="procedimento-box small">${escapeHtml(data.procedimento)}</div>`;
                }
            })
            .catch(err => {
                console.error('[Calculadora]', err);
                div.innerHTML =
                    '<div class="alert alert-danger mb-0 small">Erro ao calcular. Tente novamente.</div>';
            });
    };

    window.limparCalculadora = function() {
        const div = document.getElementById('resultadoCalculo');
        div.classList.add('d-none');
        div.innerHTML = '';
    };

    // Atalho: abre a calculadora já preenchida com o nome do reagente
    window.calcularPara = function(id, nome) {
        const modalEl = document.getElementById('modalCalculadoraDiluicao');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        document.getElementById('calc_nome').value = nome;
        document.getElementById('calc_massa_nome').value = nome;
        limparCalculadora();
        modal.show();
    };

    // ========================================================================
    // 7. QR CODE DA ETIQUETA
    // ========================================================================
    window.gerarQrCode = function(id, nome) {
        const modalEl = document.getElementById('modalQrCode');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        document.getElementById('qrNomeProduto').innerText = nome;

        // URL que abre a ficha do reagente ao escanear
        const url = window.location.origin + window.location.pathname +
            `?route=quimicos&action=view&id=${id}`;

        document.getElementById('areaQrCode').innerHTML = `
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=8&data=${encodeURIComponent(url)}"
                 alt="QR Code do reagente ${escapeHtml(nome)}"
                 class="img-fluid rounded border p-1 bg-white"
                 loading="lazy">
        `;
        modal.show();
    };

    // ========================================================================
    // 8. EXCLUIR REAGENTE
    // ========================================================================
    window.excluirQuimico = function(id, nome) {
        if (!confirm(`Excluir permanentemente o reagente "${nome}"?\n\nEsta ação não pode ser desfeita.`))
            return;
        window.location.href = `index.php?route=quimicos&action=delete&id=${id}`;
    };

    // ========================================================================
    // 9. EXPORTAR CSV
    // ========================================================================
    window.exportarCsv = function() {
        const linhas = [];
        const cabecalho = ['Nome', 'Quantidade', 'Validade', 'Armazenamento', 'Periculosidade', 'Estado',
            'Status'
        ];
        linhas.push(cabecalho.map(c => `"${c}"`).join(';'));

        document.querySelectorAll('#tabelaQuimicos tbody tr[data-id]').forEach(tr => {
            const cols = tr.querySelectorAll('td');
            if (cols.length < 7) return;
            const valores = [
                (cols[0].querySelector('.nome-item') || {}).textContent?.trim() || '',
                cols[1].textContent.trim(),
                cols[2].textContent.trim(),
                cols[3].textContent.trim(),
                cols[4].textContent.trim(),
                cols[5].textContent.trim(),
                cols[6].textContent.trim()
            ];
            linhas.push(valores.map(v => `"${String(v).replace(/"/g, '""')}"`).join(';'));
        });

        const csv = '\uFEFF' + linhas.join('\n');
        const blob = new Blob([csv], {
            type: 'text/csv;charset=utf-8;'
        });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `quimicos_labmanager_${new Date().toISOString().slice(0, 10)}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
    };

    // ========================================================================
    // 10. ATALHOS DE TECLADO
    // ========================================================================
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K → foca no filtro
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (inputFiltro) inputFiltro.focus();
        }
        // Ctrl/Cmd + Shift + I → abre incompatibilidade
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'i') {
            e.preventDefault();
            window.abrirMatrizIncompatibilidade();
        }
        // Ctrl/Cmd + Shift + C → abre calculadora
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'c') {
            e.preventDefault();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCalculadoraDiluicao')).show();
        }
    });

    // ========================================================================
    // 11. AUTOFOCO DO FILTRO AO CARREGAR (se hash corresponder)
    // ========================================================================
    if (window.location.hash === '#filtrar') {
        if (inputFiltro) inputFiltro.focus();
    }

    // Ao abrir a calculadora, limpa resultado anterior
    document.getElementById('modalCalculadoraDiluicao')
        ?.addEventListener('show.bs.modal', limparCalculadora);
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>