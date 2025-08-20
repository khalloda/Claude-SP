<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = I18n::t('navigation.dashboard') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title"><?= I18n::t('navigation.dashboard') ?></h1>
    </div>
    <div class="card-body">
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['clients'] ?></div>
                <div class="stat-label"><?= I18n::t('navigation.clients') ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?= $stats['products'] ?></div>
                <div class="stat-label"><?= I18n::t('navigation.products') ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?= $stats['quotes'] ?></div>
                <div class="stat-label"><?= I18n::t('navigation.quotes') ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?= $stats['invoices'] ?></div>
                <div class="stat-label"><?= I18n::t('navigation.invoices') ?></div>
            </div>
        </div>
        
        <div style="text-align: center; padding: 40px;">
            <h2><?= I18n::t('app.welcome') ?></h2>
            <p style="color: #666; margin-top: 10px;">
                <?= I18n::getLocale() === 'ar' ? 
                    'مرحباً بك في نظام إدارة قطع الغيار. استخدم القائمة العلوية للتنقل بين الأقسام المختلفة.' :
                    'Welcome to the Spare Parts Management System. Use the navigation menu above to access different sections.'
                ?>
            </p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
