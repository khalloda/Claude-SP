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