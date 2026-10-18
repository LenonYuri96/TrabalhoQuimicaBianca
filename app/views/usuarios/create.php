<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="fw-bold mb-0 text-dark">
                <i class="bi bi-person-plus text-primary me-2"></i>Cadastrar Novo Instrutor
            </h3>
            <a href="index.php?route=usuarios" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
        </div>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4">
            <form action="index.php?route=usuarios&action=store" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome Completo</label>
                    <input type="text" name="nome" class="form-control form-control-lg" required placeholder="Nome do Instrutor">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">E-mail Institucional (@fiemg.com.br)</label>
                    <input type="email" name="email" class="form-control form-control-lg" required placeholder="usuario@fiemg.com.br" pattern="^[a-zA-Z0-9._%+-]+@fiemg\.com\.br$">
                    <div class="form-text">Obrigatório terminar com <strong>@fiemg.com.br</strong></div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Senha Provisória</label>
                        <input type="password" name="senha" class="form-control form-control-lg" minlength="6" required placeholder="Mínimo 6 dígitos">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-semibold">Confirmar Senha</label>
                        <input type="password" name="confirmar_senha" class="form-control form-control-lg" minlength="6" required placeholder="Repita a senha">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="index.php?route=usuarios" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Salvar Instrutor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>