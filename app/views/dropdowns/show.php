<?php
use App\Core\I18n;
use App\Core\Helpers;

$title = 'Dropdown Item - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= Helpers::escape($dropdown['value']) ?></h1>
            <div>
                <a href="/dropdowns/<?= $dropdown['id'] ?>/edit" class="btn btn-primary">Edit</a>
                <a href="/dropdowns" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <table class="table table-borderless">
            <tr>
                <td><strong>Category:</strong></td>
                <td><?= Helpers::escape($dropdown['category']) ?></td>
            </tr>
            <tr>
                <td><strong>Value:</strong></td>
                <td><?= Helpers::escape($dropdown['value']) ?></td>
            </tr>
            <tr>
                <td><strong>Parent ID:</strong></td>
                <td><?= $dropdown['parent_id'] ? $dropdown['parent_id'] : 'None' ?></td>
            </tr>
            <tr>
                <td><strong>Created:</strong></td>
                <td><?= Helpers::formatDate($dropdown['created_at']) ?></td>
            </tr>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
