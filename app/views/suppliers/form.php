<?php
use App\Core\I18n;
use App\Core\Helpers;

$isEdit = isset($supplier);
$title = ($isEdit ? I18n::t('actions.edit') : I18n::t('actions.create')) . ' ' . I18n::t('navigation.suppliers') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">
            <?= $isEdit ? I18n::t('actions.edit') : I18n::t('actions.create') ?> <?= I18n::t('navigation.suppliers') ?>
        </h1>
    </div>
    
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/suppliers/' . $supplier['id'] : '/suppliers' ?>">
            <?= Helpers::csrfField() ?>
            
            <div class="form-group">
                <label for="type" class="form-label"><?= I18n::t('common.type') ?> *</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="">Select Type</option>
                    <option value="company" <?= ($isEdit && $supplier['type'] == 'company') ? 'selected' : '' ?>>Company</option>
                    <option value="individual" <?= ($isEdit && $supplier['type'] == 'individual') ? 'selected' : '' ?>>Individual</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="name" class="form-label"><?= I18n::t('common.name') ?> *</label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    class="form-control" 
                    value="<?= $isEdit ? Helpers::escape($supplier['name']) : Helpers::old('name') ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label"><?= I18n::t('common.email') ?></label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    class="form-control" 
                    value="<?= $isEdit ? Helpers::escape($supplier['email']) : Helpers::old('email') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="phone" class="form-label"><?= I18n::t('common.phone') ?></label>
                <input 
                    type="text" 
                    name="phone" 
                    id="phone" 
                    class="form-control" 
                    value="<?= $isEdit ? Helpers::escape($supplier['phone']) : Helpers::old('phone') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="address" class="form-label"><?= I18n::t('common.address') ?></label>
                <textarea 
                    name="address" 
                    id="address" 
                    class="form-control" 
                    rows="3"
                ><?= $isEdit ? Helpers::escape($supplier['address']) : Helpers::old('address') ?></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? I18n::t('actions.update') : I18n::t('actions.create') ?>
                </button>
                <a href="/suppliers" class="btn btn-secondary"><?= I18n::t('actions.cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
