<?php
use App\Core\I18n;
use App\Core\Auth;
use App\Core\Helpers;

// Get current URL without language parameter
$currentUrl = $_SERVER['REQUEST_URI'];
$urlParts = parse_url($currentUrl);
$basePath = $urlParts['path'] ?? '/dashboard';

// Build language switch URLs
$enUrl = $basePath . '?lang=en';
$arUrl = $basePath . '?lang=ar';
?>
<nav class="navbar">
    <div class="container">
        <div class="navbar-content">
            <a href="/dashboard" class="navbar-brand">
                <?= I18n::t('app.name') ?>
            </a>
            
            <ul class="navbar-nav">
                <li><a href="/dashboard" class="nav-link"><?= I18n::t('navigation.dashboard') ?></a></li>
                
                <!-- Masters Menu -->
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">Masters</a>
                    <div class="dropdown-menu">
                        <a href="/clients" class="dropdown-item"><?= I18n::t('navigation.clients') ?></a>
                        <a href="/suppliers" class="dropdown-item"><?= I18n::t('navigation.suppliers') ?></a>
                        <a href="/warehouses" class="dropdown-item"><?= I18n::t('navigation.warehouses') ?></a>
                        <a href="/products" class="dropdown-item"><?= I18n::t('navigation.products') ?></a>
                        <a href="/dropdowns" class="dropdown-item">Dropdowns</a>
                    </div>
                </li>
                
                <!-- Sales Flow Menu -->
                <li class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle">Sales</a>
                    <div class="dropdown-menu">
                        <a href="/quotes" class="dropdown-item"><?= I18n::t('navigation.quotes') ?></a>
                        <a href="/salesorders" class="dropdown-item"><?= I18n::t('navigation.sales_orders') ?></a>
                        <a href="/invoices" class="dropdown-item"><?= I18n::t('navigation.invoices') ?></a>
                        <a href="/payments" class="dropdown-item"><?= I18n::t('navigation.payments') ?></a>
                    </div>
                </li>
                
                <!-- Language Switcher -->
                <li class="lang-switcher">
                    <a href="<?= $enUrl ?>" class="lang-link <?= I18n::getLocale() === 'en' ? 'active' : '' ?>">EN</a>
                    <a href="<?= $arUrl ?>" class="lang-link <?= I18n::getLocale() === 'ar' ? 'active' : '' ?>">AR</a>
                </li>
                
                <!-- User Menu -->
                <!-- Enhanced User Menu Dropdown -->
<?php if (Auth::check()): ?>
    <li class="dropdown">
        <a href="#" class="nav-link dropdown-toggle">
            <i class="fas fa-user me-2"></i><?= Helpers::escape(Auth::user()['name']) ?>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <h6 class="dropdown-header">Welcome back!</h6>
            
            <!-- Admin-only Currencies Menu -->
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <li><a class="dropdown-item" href="/currencies"><i class="fas fa-coins me-2"></i>Manage Currencies</a></li>
                <li><hr class="dropdown-divider"></li>
            <?php endif; ?>
            
            <!-- User Preferences -->
            <li><a class="dropdown-item" href="/profile"><i class="fas fa-user-cog me-2"></i>Profile Settings</a></li>
            <li><a class="dropdown-item" href="#" onclick="showCurrencySelector()"><i class="fas fa-money-bill-wave me-2"></i>Change Currency</a></li>
            <li><hr class="dropdown-divider"></li>
            
            <!-- Logout -->
            <li><a class="dropdown-item text-danger" href="/logout"><i class="fas fa-sign-out-alt me-2"></i><?= I18n::t('auth.logout') ?></a></li>
        </div>
    </li>
<?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
