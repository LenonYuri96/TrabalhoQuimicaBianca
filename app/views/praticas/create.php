<?php
/** @var array $quimicos */
/** @var array $equipamentos */
/** @var array $vidrarias */
$quimicos = $quimicos ?? [];
$equipamentos = $equipamentos ?? [];
$vidrarias = $vidrarias ?? [];

require __DIR__ . '/../layouts/header.php';
?>

<div class="card card-custom p-4 mb-4 shadow-sm border-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-file-earmark-medical me-2 text-primary"></i>Novo Roteiro de Aula Prática
        </h4>
        <a href="index.php?route=praticas" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i> Voltar à Lista
        </a>
    </div>

    <!-- BANNER: ASSISTENTE GERADOR POR IA -->
    <div
        class="mb-4 d-flex flex-wrap justify-content-between align-items-center bg-info-subtle p-3 rounded-4 border border-info-subtle gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2 bg-info text-white rounded-circle shadow-sm">
                <i class="bi bi-stars fs-4"></i>
            </div>
            <div>
                <strong class="d-block text-dark fw-bold">Elaboração Rápida com Inteligência Artificial</strong>
                <span class="text-secondary small">A IA consulta os reagentes válidos e vidrarias com saldo positivo no
                    estoque para criar o roteiro.</span>
            </div>
        </div>
        <button type="button"
            class="btn btn-info text-white rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2"
            data-bs-toggle="modal" data-bs-target="#modalGerarPraticaIa">
            <i class="bi bi-magic"></i> Gerar Roteiro com IA
        </button>
    </div>

    <!-- FORMULÁRIO PRINCIPAL DE CADASTRO -->
    <form action="index.php?route=praticas&action=create" method="POST" id="formNovaPratica">
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <label class="form-label fw-semibold">Título do Roteiro / Prática</label>
                <input type="text" name="titulo" class="form-control form-pill" required
                    placeholder="Ex: Titulação Ácido-Base e Padronização de NaOH">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Disciplina / Matéria</label>
                <input type="text" name="materia_curso" class="form-control form-pill" required
                    placeholder="Ex: Química Analítica Quantitativa">
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Objetivo Pedagógico e Analítico</label>
                <textarea name="objetivo" rows="3" class="form-control" required
                    placeholder="Descreva a finalidade experimental da prática e o que os alunos irão aprender..."></textarea>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Procedimento Experimental Passo a Passo</label>
                <textarea name="procedimento" rows="6" class="form-control" required
                    placeholder="1. Higienizar a bureta com a solução titulante...&#10;2. Pipetar 10 mL da alíquota...&#10;3. Adicionar o indicador..."></textarea>
            </div>
        </div>

        <!-- SEÇÃO DE MATERIAIS NECESSÁRIOS -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-boxes text-info me-2"></i>Materiais e Reagentes
                    Necessários</h5>
                <small class="text-muted">Vincule os produtos do inventário que serão cobrados na preparação de
                    bancada</small>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold"
                onclick="adicionarLinhaItem()">
                <i class="bi bi-plus-lg me-1"></i> Adicionar Item
            </button>
        </div>

        <div id="container-itens" class="mb-4">
            <!-- Linhas de itens geradas dinamicamente via JS -->
        </div>

        <div class="d-flex justify-content-end gap-2 border-top pt-3">
            <a href="index.php?route=praticas" class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-check2-circle me-1"></i> Salvar Roteiro Completo
            </button>
        </div>
    </form>
</div>

<!-- ======================================================== -->
<!-- MODAL: GERADOR DE ROTEIRO COM IA                         -->
<!-- ======================================================== -->
<div class="modal fade" id="modalGerarPraticaIa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-stars text-warning me-2"></i>Criar Prática com Base no Estoque
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tema ou Assunto da Prática</label>
                    <input type="text" id="ia_tema" class="form-control"
                        placeholder="Ex: Titulação Ácido-Base, Extração de Cafeína, Cinética Química" required>

                    <!-- Sugestões de 1 clique -->
                    <div class="mt-2 d-flex flex-wrap gap-1">
                        <span class="badge bg-light text-dark border pointer" role="button"
                            onclick="preencherTema('Titulação Ácido-Base (HCl x NaOH)')">Titulação Ácido-Base</span>
                        <span class="badge bg-light text-dark border pointer" role="button"
                            onclick="preencherTema('Precipitação e Solubilidade de Sais')">Precipitação</span>
                        <span class="badge bg-light text-dark border pointer" role="button"
                            onclick="preencherTema('Determinação de Cloretos pelo Método de Mohr')">Método de
                            Mohr</span>
                        <span class="badge bg-light text-dark border pointer" role="button"
                            onclick="preencherTema('Cinética da Degradação Química')">Cinética</span>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold">Curso / Disciplina</label>
                    <input type="text" id="ia_curso" class="form-control" value="Química Geral e Analítica"
                        placeholder="Ex: Farmácia - Química Geral" required>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-3"
                    data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarGerarIa" class="btn btn-info text-white rounded-pill px-4 fw-bold"
                    onclick="executarGeradorPraticaIa()">
                    <i class="bi bi-magic me-1"></i> Criar Roteiro
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- JAVASCRIPT: GERENCIAMENTO DE ITENS E DISPARO DA IA       -->
<!-- ======================================================== -->
<script>
    const quimicos = <?= json_encode($quimicos) ?>;
    const equipamentos = <?= json_encode($equipamentos) ?>;
    const vidrarias = <?= json_encode($vidrarias) ?>;

    let indexItem = 0;

    function adicionarLinhaItem() {
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 align-items-center bg-light p-2 rounded-3 border';
        div.id = `linha_${indexItem}`;

        div.innerHTML = `
        <div class="col-md-3">
            <select name="itens[${indexItem}][tipo_item]" class="form-select form-select-sm" onchange="atualizarOpcoesItem(${indexItem}, this.value)">
                <option value="quimico">Reagente Químico</option>
                <option value="vidraria">Vidraria</option>
                <option value="equipamento">Equipamento</option>
            </select>
        </div>
        <div class="col-md-4">
            <select name="itens[${indexItem}][item_id]" id="select_item_${indexItem}" class="form-select form-select-sm" required>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" name="itens[${indexItem}][quantidade_necessaria]" class="form-control form-select-sm" placeholder="Qtd (ex: 50mL / 2 un)" required>
        </div>
        <div class="col-md-2">
            <input type="text" name="itens[${indexItem}][observacao]" class="form-control form-select-sm" placeholder="Obs (opcional)">
        </div>
        <div class="col-md-1 text-center">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" onclick="document.getElementById('linha_${indexItem}').remove()" title="Remover item">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;

        document.getElementById('container-itens').appendChild(div);
        atualizarOpcoesItem(indexItem, 'quimico');
        indexItem++;
    }

    function atualizarOpcoesItem(idx, tipo) {
        const sel = document.getElementById(`select_item_${idx}`);
        if (!sel) return;

        sel.innerHTML = '<option value="">Selecione o item...</option>';
        let lista = (tipo === 'quimico') ? quimicos : (tipo === 'vidraria' ? vidrarias : equipamentos);

        lista.forEach(i => {
            let label = i.nome + (i.capacidade_volume ? ' (' + i.capacidade_volume + ')' : '');
            sel.innerHTML += `<option value="${i.id}">${label}</option>`;
        });
    }

    function preencherTema(tema) {
        document.getElementById('ia_tema').value = tema;
    }

    function executarGeradorPraticaIa() {
        const tema = document.getElementById('ia_tema').value.trim();
        const curso = document.getElementById('ia_curso').value.trim();

        if (!tema || !curso) {
            alert('Por favor, informe o tema e a disciplina para a IA.');
            return;
        }

        const btn = document.getElementById('btnConfirmarGerarIa');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Consultando estoque real...';

        fetch('index.php?route=ai&action=gerarRoteiroPraticaIa', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tema: tema,
                curso: curso
            })
        })
            .then(response => {
                if (!response.ok) throw new Error('Falha na resposta da IA');
                return response.json();
            })
            .then(data => {
                // Preenche os campos de texto do formulário
                document.querySelector('input[name="titulo"]').value = data.titulo || '';
                document.querySelector('input[name="materia_curso"]').value = data.materia_curso || '';
                document.querySelector('textarea[name="objetivo"]').value = data.objetivo || '';
                document.querySelector('textarea[name="procedimento"]').value = data.procedimento || '';

                // Limpa a lista atual e insere os materiais reais sugeridos
                document.getElementById('container-itens').innerHTML = '';
                indexItem = 0;

                if (data.itens_sugeridos && data.itens_sugeridos.length > 0) {
                    data.itens_sugeridos.forEach(it => {
                        adicionarLinhaItem();
                        const curIdx = indexItem - 1;

                        const selectTipo = document.querySelector(
                            `#linha_${curIdx} select[name="itens[${curIdx}][tipo_item]"]`);
                        if (selectTipo) {
                            selectTipo.value = it.tipo_item;
                            atualizarOpcoesItem(curIdx, it.tipo_item);
                        }

                        const selectItem = document.getElementById(`select_item_${curIdx}`);
                        if (selectItem) {
                            selectItem.value = it.item_id;
                        }

                        const inputQtd = document.querySelector(
                            `input[name="itens[${curIdx}][quantidade_necessaria]"]`);
                        if (inputQtd) {
                            inputQtd.value = it.quantidade || '1 un';
                        }

                        const inputObs = document.querySelector(`input[name="itens[${curIdx}][observacao]"]`);
                        if (inputObs) {
                            inputObs.value = it.obs || '';
                        }
                    });
                }

                // Fecha o modal e destaca a conclusão
                bootstrap.Modal.getInstance(document.getElementById('modalGerarPraticaIa')).hide();
            })
            .catch(err => {
                alert('Não foi possível gerar a prática com IA no momento. Verifique a conexão com o servidor.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-magic me-1"></i> Criar Roteiro';
            });
    }

    // Inicia com uma linha vazia por padrão
    adicionarLinhaItem();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>