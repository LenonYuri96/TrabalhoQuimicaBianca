<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                <i class="bi bi-box-seam fs-2 text-info"></i>
                <h4 class="mb-0 fw-bold">Cadastrar Equipamento ou Vidraria</h4>
            </div>

            <form action="index.php?route=equipamentos&action=create" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tipo do Item</label>
                        <select name="tipo" id="selectTipo" class="form-select form-pill"
                            onchange="toggleCamposTipo(this.value)">
                            <option value="equipamento">Equipamento / Aparelho</option>
                            <option value="vidraria">Vidraria de Laboratório</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nome do Item</label>
                        <input type="text" name="nome" class="form-control form-pill" required
                            placeholder="Ex: Balão Volumétrico ou Autoclave">
                    </div>

                    <!-- Campos Vidrarias -->
                    <div id="blocoVidraria" class="row g-3 d-none">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Volume / Capacidade</label>
                            <input type="text" name="capacidade_volume" class="form-control form-pill"
                                placeholder="Ex: 250 mL, 500 mL, 1 L">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Quantidade Inicial em Estoque</label>
                            <input type="number" name="quantidade_total" class="form-control form-pill" value="10"
                                min="1">
                        </div>
                    </div>

                    <!-- Campos Equipamentos -->
                    <div id="blocoEquipamento" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Patrimônio / Cód</label>
                            <input type="text" name="codigo_patrimonio" class="form-control form-pill"
                                placeholder="Ex: COD:NO299A">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Frequência Manutenção</label>
                            <select name="frequencia_meses" class="form-select form-pill">
                                <option value="1">1 Mês</option>
                                <option value="6" selected>6 Meses (Semestral)</option>
                                <option value="12">12 Meses (Anual)</option>
                                <option value="0">Manual</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Última Revisão</label>
                            <input type="date" name="ultima_manutencao" class="form-control form-pill"
                                value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Observações / Cuidados</label>
                        <input type="text" name="observacao" class="form-control form-pill"
                            placeholder="Ex: Armazenar seco, calibragem periódica...">
                    </div>

                    <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                        <a href="index.php?route=equipamentos"
                            class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-info rounded-pill px-4 fw-semibold text-white">Salvar
                            Cadastro</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleCamposTipo(tipo) {
        if (tipo === 'vidraria') {
            document.getElementById('blocoVidraria').classList.remove('d-none');
            document.getElementById('blocoEquipamento').classList.add('d-none');
        } else {
            document.getElementById('blocoVidraria').classList.add('d-none');
            document.getElementById('blocoEquipamento').classList.remove('d-none');
        }
    }
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>