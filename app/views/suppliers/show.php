<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = $supplier['name'] . ' - ' . I18n::t('navigation.suppliers') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= Helpers::escape($supplier['name']) ?></h1>
            <div>
                <a href="/suppliers/<?= $supplier['id'] ?>/edit" class="btn btn-primary"><?= I18n::t('actions.edit') ?></a>
                <a href="/suppliers" class="btn btn-secondary"><?= I18n::t('actions.back') ?></a>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Supplier Details -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong><?= I18n::t('common.type') ?>:</strong></td>
                        <td><?= Helpers::escape(ucfirst($supplier['type'])) ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= I18n::t('common.email') ?>:</strong></td>
                        <td><?= Helpers::escape($supplier['email']) ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= I18n::t('common.phone') ?>:</strong></td>
                        <td><?= Helpers::escape($supplier['phone']) ?></td>
                    </tr>
                    <tr>
                        <td><strong><?= I18n::t('common.created_at') ?>:</strong></td>
                        <td><?= Helpers::formatDate($supplier['created_at']) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <div>
                    <strong><?= I18n::t('common.address') ?>:</strong><br>
                    <?= Helpers::escape($supplier['address']) ?>
                </div>
            </div>
        </div>

        <!-- Future: Purchase Orders, Products Supplied, etc. -->
        <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 10px;">
            <h3 style="color: #667eea;">Supplier Details</h3>
            <p style="color: #666; margin-top: 1rem;">
                Additional supplier information and purchase history will be available in future phases.
            </p>
        </div>
    </div>
</div>

<style>
.row {
    display: flex;
    flex-wrap: wrap;
    margin: -0.5rem;
}

.col-md-6 {
    flex: 0 0 50%;
    padding: 0.5rem;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table td {
    padding: 0.5rem 0;
    border: none;
    vertical-align: top;
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
    }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
