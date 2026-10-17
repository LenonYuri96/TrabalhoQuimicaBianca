</div> <!-- Fecha container principal flex-grow-1 -->
</main> <!-- Fecha tag main -->

<!-- ======================================================== -->
<!-- RODAPÉ INSTITUCIONAL EXPANDIDO (STICKY FOOTER)           -->
<!-- ======================================================== -->
<footer class="footer-lab mt-auto">
    <!-- Faixa Principal de Conteúdo -->
    <div class="footer-lab-top py-5">
        <div class="container">
            <div class="row g-4 align-items-start">

                <!-- Coluna 1: Identidade e Finalidade do Lab -->
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="footer-brand-icon">
                            <i class="bi bi-radioactive fs-4 text-warning"></i>
                        </div>
                        <div>
                            <span class="footer-brand-title d-block fw-bold text-white fs-5 lh-1">LabManager</span>
                            <small class="text-lab-cyan fw-semibold" style="font-size: 0.72rem;">
                                SISTEMA INTEGRADO DE LABORATÓRIO 4.0
                            </small>
                        </div>
                    </div>
                    <p class="footer-description text-white-50 small mb-3 lh-base">
                        Plataforma analítica para controle rigoroso de estoque químico, conformidade com a
                        <strong>ABNT NBR 14725 (FDS/FISPQ)</strong>, calibração de equipamentos, gestão de quebras em
                        vidrarias e apoio experimental.
                    </p>
                    <span class="badge bg-dark border border-secondary text-white-50 small"
                        style="font-size: 0.68rem;">v2.5 Enterprise</span>
                </div>

                <!-- Coluna 2: Módulos Rápidos -->
                <div class="col-6 col-md-3 col-lg-3">
                    <h6 class="footer-col-title text-white fw-bold text-uppercase letter-spacing-1">
                        Módulos
                    </h6>
                    <ul class="footer-links mb-0 small">
                        <li class="mb-2">
                            <a href="index.php?route=dashboard" class="footer-link">
                                <i class="bi bi-chevron-right me-1 text-lab-cyan"></i>Dashboard Geral
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="index.php?route=aulas" class="footer-link">
                                <i class="bi bi-chevron-right me-1 text-lab-cyan"></i>Aulas &amp; Bancada
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="index.php?route=praticas" class="footer-link">
                                <i class="bi bi-chevron-right me-1 text-lab-cyan"></i>Roteiros Práticos
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="index.php?route=quimicos" class="footer-link">
                                <i class="bi bi-chevron-right me-1 text-lab-cyan"></i>Químicos &amp; FDS
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="index.php?route=equipamentos" class="footer-link">
                                <i class="bi bi-chevron-right me-1 text-lab-cyan"></i>Vidrarias &amp; Avarias
                            </a>
                        </li>
                        <li>
                            <a href="index.php?route=ocorrencias" class="footer-link">
                                <i class="bi bi-chevron-right me-1 text-lab-cyan"></i>Livro de Ocorrências
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Coluna 3: Boas Práticas & Suporte -->
                <div class="col-6 col-md-3 col-lg-4">
                    <h6 class="footer-col-title text-white fw-bold text-uppercase letter-spacing-1">
                        Segurança &amp; Suporte
                    </h6>
                    <ul class="footer-links mb-0 small">
                        <li class="mb-2">
                            <span class="d-flex align-items-center gap-2 text-white-50">
                                <i class="bi bi-shield-check text-success"></i>
                                <span>ABNT NBR 14725 / GHS</span>
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="d-flex align-items-center gap-2 text-white-50">
                                <i class="bi bi-recycle text-info"></i>
                                <span>Gestão de Resíduos Químicos</span>
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="d-flex align-items-center gap-2 text-white-50">
                                <i class="bi bi-eyeglasses text-warning"></i>
                                <span>EPIs Obrigatórios em Bancada</span>
                            </span>
                        </li>
                        <li class="mb-2">
                            <span class="d-flex align-items-center gap-2 text-white-50">
                                <i class="bi bi-clipboard-check text-lab-cyan"></i>
                                <span>Controle de Calibração e Preventivas</span>
                            </span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <!-- Faixa Inferior de Copyright -->
    <div class="footer-lab-bottom py-3">
        <div class="container">
            <div class="row align-items-center gy-2">
                <div class="col-12 col-md-6 text-center text-md-start">
                    <small class="text-white-50">
                        © <?= date('Y') ?> <strong>LabManager</strong>. Todos os direitos reservados.
                        Sistema para Gestão de Ensino e Pesquisa.
                    </small>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end">
                    <ul class="list-inline mb-0 small">
                        <li class="list-inline-item">
                            <span class="text-white-50">Ambiente Seguro</span>
                        </li>
                        <li class="list-inline-item text-white-25">·</li>
                        <li class="list-inline-item">
                            <a href="#top" class="text-lab-cyan text-decoration-none"
                                onclick="window.scrollTo({top:0, behavior:'smooth'}); return false;">
                                <i class="bi bi-arrow-up-circle me-1"></i>Voltar ao topo
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- ======================================================== -->
<!-- SCRIPTS BASE                                             -->
<!-- ======================================================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>