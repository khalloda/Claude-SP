<?php
use App\Core\I18n;
use App\Core\Helpers;

$isEdit = isset($warehouse);
$title = ($isEdit ? I18n::t('actions.edit') : I18n::t('actions.create')) . ' ' . I18n::t('navigation.warehouses') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <h1 class="card-title">
            <?= $isEdit ? I18n::t('actions.edit') : I18n::t('actions.create') ?> <?= I18n::t('navigation.warehouses') ?>
        </h1>
    </div>
    
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/warehouses/' . $warehouse['id'] : '/warehouses' ?>">
            <?= Helpers::csrfField() ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="form-label"><?= I18n::t('common.name') ?> *</label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            class="form-control" 
                            value="<?= $isEdit ? Helpers::escape($warehouse['name']) : Helpers::old('name') ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="form-label"><?= I18n::t('common.address') ?></label>
                        <textarea 
                            name="address" 
                            id="address" 
                            class="form-control" 
                            rows="3"
                        ><?= $isEdit ? Helpers::escape($warehouse['address']) : Helpers::old('address') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="capacity" class="form-label">Capacity (m²)</label>
                        <input 
                            type="number" 
                            name="capacity" 
                            id="capacity" 
                            class="form-control" 
                            step="0.01"
                            min="0"
                            value="<?= $isEdit ? $warehouse['capacity'] : Helpers::old('capacity') ?>"
                        >
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="responsible_name" class="form-label">Responsible Person *</label>
                        <input 
                            type="text" 
                            name="responsible_name" 
                            id="responsible_name" 
                            class="form-control" 
                            value="<?= $isEdit ? Helpers::escape($warehouse['responsible_name']) : Helpers::old('responsible_name') ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="responsible_email" class="form-label">Responsible Email</label>
                        <input 
                            type="email" 
                            name="responsible_email" 
                            id="responsible_email" 
                            class="form-control" 
                            value="<?= $isEdit ? Helpers::escape($warehouse['responsible_email']) : Helpers::old('responsible_email') ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="responsible_phone" class="form-label">Responsible Phone</label>
                        <input 
                            type="text" 
                            name="responsible_phone" 
                            id="responsible_phone" 
                            class="form-control" 
                            value="<?= $isEdit ? Helpers::escape($warehouse['responsible_phone']) : Helpers::old('responsible_phone') ?>"
                        >
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? I18n::t('actions.update') : I18n::t('actions.create') ?>
                </button>
                <a href="/warehouses" class="btn btn-secondary"><?= I18n::t('actions.cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
