<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                <i class="bi bi-gear-wide-connected fs-2 text-info"></i>
                <h4 class="mb-0 fw-bold">Cadastrar Equipamento para Manutenção</h4>
            </div>

            <form action="index.php?route=equipamentos&action=create" method="POST">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Nome do Equipamento</label>
                        <input type="text" name="nome" class="form-control form-pill" required
                            placeholder="Ex: Capela de exaustão de gases">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Código / Patrimônio</label>
                        <input type="text" name="codigo_patrimonio" class="form-control form-pill"
                            placeholder="Ex: COD:NO299A">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Frequência da Manutenção</label>
                        <select name="frequencia_meses" id="selectFrequencia" class="form-select form-pill">
                            <option value="1">A cada 1 Mês (Ex: Chuveiro)</option>
                            <option value="6" selected>A cada 6 Meses (Semestral)</option>
                            <option value="12">A cada 12 Meses (Anual)</option>
                            <option value="0">Outra / Inspeção Manual</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Última Revisão</label>
                        <input type="date" name="ultima_manutencao" id="inputUltimaManutencao"
                            class="form-control form-pill" value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Próxima Revisão (Automático)</label>
                        <input type="date" name="proxima_manutencao" id="inputProximaManutencao"
                            class="form-control form-pill bg-light">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Observações / Rotina</label>
                        <input type="text" name="observacao" class="form-control form-pill"
                            placeholder="Ex: Cuidados diários, troca de eletrodo, inspeção antes do uso...">
                    </div>

                    <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                        <a href="index.php?route=equipamentos"
                            class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-info rounded-pill px-4 fw-semibold text-white">Cadastrar
                            Equipamento</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>