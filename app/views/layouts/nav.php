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
                <?php if (Auth::check()): ?>
                    <li>
                        <span class="nav-link">Welcome, <?= Helpers::escape(Auth::user()['name']) ?></span>
                    </li>
                    <li><a href="/logout" class="nav-link"><?= I18n::t('auth.logout') ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
