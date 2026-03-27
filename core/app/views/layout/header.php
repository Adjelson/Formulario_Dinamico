<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITENAME; ?></title>

    <!-- Bootstrap 5 (local) -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/bootstrap.min.css">
    <!-- Font Awesome 6 (local) -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/style.css">

    <script>
        window.URLROOT  = '<?php echo URLROOT; ?>';
        window.BASE_URL = '<?php echo URLROOT; ?>';
    </script>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark df-navbar sticky-top">
    <div class="container-xl">
        <a class="navbar-brand d-flex align-items-center gap-2" >
            <span class="brand-name"><?php echo SITENAME; ?></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a href="<?php echo URLROOT; ?>/admin/dashboard" class="nav-link nav-icon-link">
                                <i class="fa-solid fa-gauge-high"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo URLROOT; ?>/admin/forms" class="nav-link nav-icon-link">
                                <i class="fa-solid fa-file-alt"></i> Formulários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo URLROOT; ?>/admin/users" class="nav-link nav-icon-link">
                                <i class="fa-solid fa-users"></i> Utilizadores
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="<?php echo URLROOT; ?>/home" class="nav-link nav-icon-link">
                                <i class="fa-solid fa-list-check"></i> Formulários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo URLROOT; ?>/my/history" class="nav-link nav-icon-link">
                                <i class="fa-solid fa-clock-rotate-left"></i> Histórico
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item ms-lg-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                                <div class="user-avatar-sm">
                                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                                </div>
                                <span class="d-none d-lg-inline"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                                <li><span class="dropdown-item-text text-muted small">
                                    <i class="fa-solid fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>
                                </span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo URLROOT; ?>/logout">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i>Terminar Sessão
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/login" class="nav-link nav-icon-link">
                            <i class="fa-solid fa-right-to-bracket"></i> Entrar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo URLROOT; ?>/register" class="btn btn-primary btn-sm ms-2 px-3">
                            <i class="fa-solid fa-user-plus me-1"></i> Registar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="df-main">
