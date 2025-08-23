<?php
use App\Core\I18n;
use App\Core\Helpers;
?>
<!DOCTYPE html>
<html lang="<?= I18n::getLocale() ?>" dir="<?= I18n::getDirection() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? I18n::t('app.name') ?></title>
    <link rel="stylesheet" href="<?= Helpers::asset('css/app.css') ?>">
	    <!-- Currency Configuration for JavaScript -->
    <script>
    window.currencyConfig = {
        currencies: <?php
        try {
            echo \App\Core\Helpers::getCurrencyJavaScriptConfig();
        } catch (Exception $e) {
            // Fallback if currency system not ready
            echo json_encode([
                'EGP' => [
                    'name' => 'Egyptian Pound',
                    'symbol' => 'ج.م',
                    'decimal_places' => 2,
                    'exchange_rate' => 1.000000,
                    'is_primary' => true
                ],
                'USD' => [
                    'name' => 'US Dollar', 
                    'symbol' => '$',
                    'decimal_places' => 2,
                    'exchange_rate' => 0.032258,
                    'is_primary' => false
                ]
            ], JSON_HEX_APOS | JSON_HEX_QUOT);
        }
        ?>,
        defaultCurrency: '<?php 
        try {
            echo \App\Core\Helpers::getDefaultCurrency();
        } catch (Exception $e) {
            echo 'EGP';
        }
        ?>'
    };
    </script>
    
    <!-- Multi-Currency JavaScript -->
    <script src="/assets/js/multicurrency.js"></script>
</head>
<body>
    <?php if (isset($showNav) && $showNav): ?>
        <?php include __DIR__ . '/nav.php'; ?>
    <?php endif; ?>

    <main class="<?= isset($showNav) && $showNav ? 'main-content' : '' ?>">
        <div class="<?= isset($showNav) && $showNav ? 'container' : '' ?>">
            <?php
            // Show flash messages
            if (isset($_SESSION['flash'])):
                foreach ($_SESSION['flash'] as $type => $message):
            ?>
                <div class="alert alert-<?= $type ?>">
                    <?= Helpers::escape($message) ?>
                </div>
            <?php
                endforeach;
                unset($_SESSION['flash']);
            endif;
            ?>

            <?php
            // Show validation errors
            $errors = Helpers::errors();
            if (!empty($errors)):
            ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= Helpers::escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php 
                Helpers::clearOldInputAndErrors();
            endif; 
            ?>

            <?= $content ?? '' ?>
        </div>
    </main>

    <script src="<?= Helpers::asset('js/app.js') ?>"></script>
    
    <script>
    // Debug console log
    console.log('Base layout loaded');
    console.log('Current URL:', window.location.href);
    console.log('Document ready state:', document.readyState);
    </script>
</body>
</html>
