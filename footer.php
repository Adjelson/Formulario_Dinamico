</main>
<style>
    .df-footer {
        background: #0f172a;
        /* dark moderno */
        color: #fff;
    }

    .footer-brand {
        font-weight: 700;
        font-size: 1.2rem;
    }

    .footer-title {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .footer-links li {
        margin-bottom: 6px;
    }

    .footer-links a {
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.85rem;
    }

    .footer-links a:hover {
        color: #fff;
    }

    .footer-social {
        color: #94a3b8;
        font-size: 1.1rem;
    }

    .footer-social:hover {
        color: #10b981;
        /* verde esmeralda */
    }
</style>
<!-- FOOTER -->
<footer class="df-footer mt-auto">
    <div class="container-xl py-5">

        <div class="row gy-4">

            <!-- BRAND -->
            <div class="col-lg-4">
                <h5 class="footer-brand mb-2">
                    <?php echo SITENAME; ?>
                </h5>
                <p class="text-muted small mb-3">
                    Plataforma para gestão eficiente de formulários, utilizadores e processos internos.
                </p>

                <div class="d-flex gap-3">
                    <a href="#" class="footer-social"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="footer-social"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="footer-social"><i class="fab fa-github"></i></a>
                </div>
            </div>

            <!-- LINKS -->
            <div class="col-6 col-lg-2">
                <h6 class="footer-title">Plataforma</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="#">Funcionalidades</a></li>
                    <li><a href="#">Preços</a></li>
                    <li><a href="#">Atualizações</a></li>
                </ul>
            </div>

            <!-- SUPORTE -->
            <div class="col-6 col-lg-3">
                <h6 class="footer-title">Suporte</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="#">Ajuda</a></li>
                    <li><a href="#">Documentação</a></li>
                    <li><a href="#">Contactos</a></li>
                </ul>
            </div>

            <!-- LEGAL -->
            <div class="col-lg-3">
                <h6 class="footer-title">Legal</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="#">Privacidade</a></li>
                    <li><a href="#">Termos</a></li>
                    <li><a href="#">Segurança</a></li>
                </ul>
            </div>

        </div>

        <hr class="my-4">

        <!-- BOTTOM BAR -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted gap-2">
            <div>
                &copy; <?php echo date('Y'); ?> <?php echo SITENAME; ?>. Todos os direitos reservados.
            </div>

            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-shield-halved text-success"></i>
                Plataforma segura
            </div>

            <div>
                Desenvolvido por <strong>Adjelson Info</strong>
            </div>
        </div>

    </div>
</footer>