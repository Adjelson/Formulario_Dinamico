<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(SITENAME); ?></title>
    <link rel="stylesheet" href="<?php echo e(URLROOT); ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo e(URLROOT); ?>/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(URLROOT); ?>/css/style.css?v=<?php echo e(APPVERSION); ?>">
    <script>window.URLROOT=<?php echo json_encode(URLROOT); ?>;</script>
</head>
<body>
<a class="skip-link" href="#main-content">Saltar para o conteúdo principal</a>
<nav class="navbar navbar-expand-lg navbar-dark df-navbar sticky-top" aria-label="Navegação principal">
    <div class="container-xl">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo e(URLROOT); ?>">
            <span class="brand-icon" aria-hidden="true"><i class="fa-solid fa-wpforms"></i></span>
            <span class="brand-name"><?php echo e(SITENAME); ?></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Abrir menu de navegação">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <li class="nav-item"><a href="<?php echo e(URLROOT); ?>/admin/dashboard" class="nav-link nav-icon-link"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard</a></li>
                        <li class="nav-item"><a href="<?php echo e(URLROOT); ?>/admin/forms" class="nav-link nav-icon-link"><i class="fa-solid fa-file-alt" aria-hidden="true"></i> Formulários</a></li>
                        <li class="nav-item"><a href="<?php echo e(URLROOT); ?>/admin/users" class="nav-link nav-icon-link"><i class="fa-solid fa-users" aria-hidden="true"></i> Utilizadores</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a href="<?php echo e(URLROOT); ?>/home" class="nav-link nav-icon-link"><i class="fa-solid fa-list-check" aria-hidden="true"></i> Formulários</a></li>
                        <li class="nav-item"><a href="<?php echo e(URLROOT); ?>/my/history" class="nav-link nav-icon-link"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i> Histórico</a></li>
                    <?php endif; ?>
                    <li class="nav-item ms-lg-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="user-avatar-sm" aria-hidden="true"><?php echo e(user_initial((string) ($_SESSION['user_name'] ?? 'U'))); ?></span>
                                <span class="d-none d-lg-inline"><?php echo e($_SESSION['user_name'] ?? 'Utilizador'); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                                <li><span class="dropdown-item-text text-muted small"><?php echo e($_SESSION['user_email'] ?? ''); ?></span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?php echo e(URLROOT); ?>/logout" method="post" class="m-0">
<?php echo csrf_field(); ?>
<button class="dropdown-item text-danger" type="submit"><i class="fa-solid fa-right-from-bracket me-2" aria-hidden="true"></i>Terminar sessão</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a href="<?php echo e(URLROOT); ?>/login" class="nav-link nav-icon-link"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Entrar</a></li>
                    <li class="nav-item"><a href="<?php echo e(URLROOT); ?>/register" class="btn btn-primary btn-sm ms-2 px-3"><i class="fa-solid fa-user-plus" aria-hidden="true"></i> Registar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div id="status-region" class="container-xl mt-3" aria-live="polite" aria-atomic="true">
    <?php foreach (($data['flash'] ?? []) as $flash): ?>
        <?php $type = in_array($flash['type'] ?? '', ['success','danger','warning','info'], true) ? $flash['type'] : 'info'; ?>
        <div class="alert alert-<?php echo e($type); ?> alert-dismissible fade show" role="status">
            <?php echo e($flash['message'] ?? ''); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar mensagem"></button>
        </div>
    <?php endforeach; ?>
</div>

<main class="df-main" id="main-content" tabindex="-1">
