<?php
use App\Core\I18n;
use App\Core\Helpers;
?>
<!DOCTYPE html>
<html lang="<?= I18n::getLanguage() ?>" dir="<?= I18n::getDirection() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? I18n::t('app.name') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/app.css">
    
    <?php if (I18n::getDirection() === 'rtl'): ?>
        <link rel="stylesheet" href="/assets/css/rtl.css">
    <?php endif; ?>
</head>
<body class="<?= I18n::getDirection() ?>">
    
    <?php if ($showNav ?? true): ?>
        <!-- Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="container">
                <a class="navbar-brand" href="/">
                    <strong><?= I18n::t('app.name') ?></strong>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard"><?= I18n::t('navigation.dashboard') ?></a>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="mastersDropdown" role="button" data-bs-toggle="dropdown">
                                <?= I18n::t('navigation.masters') ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/clients"><?= I18n::t('navigation.clients') ?></a></li>
                                <li><a class="dropdown-item" href="/suppliers"><?= I18n::t('navigation.suppliers') ?></a></li>
                                <li><a class="dropdown-item" href="/products"><?= I18n::t('navigation.products') ?></a></li>
                                <li><a class="dropdown-item" href="/warehouses"><?= I18n::t('navigation.warehouses') ?></a></li>
                                <li><a class="dropdown-item" href="/dropdowns"><?= I18n::t('navigation.dropdowns') ?></a></li>
                            </ul>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="salesDropdown" role="button" data-bs-toggle="dropdown">
                                <?= I18n::t('navigation.sales') ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/quotes"><?= I18n::t('navigation.quotes') ?></a></li>
                                <li><a class="dropdown-item" href="/salesorders"><?= I18n::t('navigation.sales_orders') ?></a></li>
                                <li><a class="dropdown-item" href="/invoices"><?= I18n::t('navigation.invoices') ?></a></li>
                                <li><a class="dropdown-item" href="/payments"><?= I18n::t('navigation.payments') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <!-- Currency Switcher -->
                        <?php if (Helpers::isMultiCurrencyEnabled()): ?>
                        <li class="nav-item dropdown me-3">
                            <select id="currency-switcher" class="form-select form-select-sm" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                                <?= Helpers::getCurrencyOptions(Helpers::getDefaultCurrency()) ?>
                            </select>
                        </li>
                        <?php endif; ?>
                        
                        <!-- Language Switcher -->
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown">
                                <?= strtoupper(I18n::getLanguage()) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="?lang=en">English</a></li>
                                <li><a class="dropdown-item" href="?lang=ar">العربية</a></li>
                            </ul>
                        </li>
                        
                        <!-- User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <?= I18n::t('navigation.welcome') ?>, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                                <li><a class="dropdown-item" href="/currencies"><i class="fas fa-coins me-2"></i>Manage Currencies</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/profile"><i class="fas fa-user me-2"></i><?= I18n::t('navigation.profile') ?></a></li>
                                <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt me-2"></i><?= I18n::t('navigation.logout') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $type => $messages): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show m-3" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        
        <?= $content ?? '' ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5">
        <div class="container">
            <small>&copy; 2025 <?= I18n::t('app.name') ?>. All rights reserved.</small>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Currency Configuration for JavaScript -->
    <script>
    window.currencyConfig = {
        currencies: <?= Helpers::getCurrencyJavaScriptConfig() ?>,
        defaultCurrency: '<?= Helpers::getDefaultCurrency() ?>'
    };
    </script>
    
    <!-- Multi-Currency JavaScript -->
    <script src="/assets/js/multicurrency.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="/assets/js/app.js"></script>
</body>
</html>
