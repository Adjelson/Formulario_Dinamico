</main>

<!-- FOOTER -->
<footer class="df-footer mt-auto">
    <div class="container-xl">
        <div class="row align-items-center py-3">
            <div class="col-md-6 text-center text-md-start">
                <span class="footer-brand">
            <?php echo SITENAME; ?>
                </span>
                <span class="text-muted ms-2 small">&copy; <?php echo date('Y'); ?> Todos os direitos reservados.</span>
                   <span class="text-muted ms-2 small">&copy; <?php echo date('Y'); ?> Adjelson Info.</span>
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <span class="text-muted small d-flex align-items-center justify-content-center justify-content-md-end gap-1">
                    <i class="fa-solid fa-shield-halved me-1 text-success"></i>Plataforma segura
                </span>
            </div>
        </div>
    </div>
</footer>

<?php
// Usar Bootstrap local se existir, senão CDN
$bootstrapJs = file_exists(APPROOT . '/public/js/bootstrap.bundle.min.js')
    ? URLROOT . '/js/bootstrap.bundle.min.js'
    : 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js';
?>
<script src="<?php echo $bootstrapJs; ?>"></script>
<script src="<?php echo URLROOT; ?>/js/app.js"></script>
</body>
</html>
