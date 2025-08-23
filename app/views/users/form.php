<?php
// Create this file: app/views/users/form.php

use App\Core\I18n;
use App\Core\Helpers;

$isEdit = isset($user);
$title = ($isEdit ? 'Edit User' : 'Add New User') . ' - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title"><?= $isEdit ? 'Edit User' : 'Add New User' ?></h1>
            <a href="/users" class="btn btn-secondary">Back to Users</a>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/users/' . $user['id'] . '/update' : '/users/store' ?>">
            <?= Helpers::csrfField() ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               class="form-control" 
                               value="<?= Helpers::escape($user['name'] ?? Helpers::old('name')) ?>"
                               placeholder="Enter full name"
                               required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               value="<?= Helpers::escape($user['email'] ?? Helpers::old('email')) ?>"
                               placeholder="Enter email address"
                               required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password <?= $isEdit ? '(leave blank to keep current)' : '*' ?></label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Enter password"
                               minlength="6"
                               <?= !$isEdit ? 'required' : '' ?>>
                        <small class="form-text text-muted">Minimum 6 characters</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="locale">Language Preference</label>
                        <select id="locale" name="locale" class="form-control" required>
                            <option value="en" <?= ($user['locale'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                            <option value="ar" <?= ($user['locale'] ?? 'en') === 'ar' ? 'selected' : '' ?>>العربية</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="role_id">User Role</label>
                        <select id="role_id" name="role_id" class="form-control">
                            <option value="">No specific role</option>
                            <?php if (!empty($roles)): ?>
                                <?php 
                                $currentRoleId = null;
                                if ($isEdit && !empty($userRoles)) {
                                    $currentRoleId = $userRoles[0]['id'] ?? null;
                                }
                                ?>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" 
                                            <?= $currentRoleId == $role['id'] ? 'selected' : '' ?>>
                                        <?= Helpers::escape($role['name']) ?> 
                                        <?php if (!empty($role['description'])): ?>
                                            - <?= Helpers::escape($role['description']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="form-text text-muted">Assign a role to grant specific permissions</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Account Status</label>
                        <div class="form-control-static">
                            <?php if ($isEdit): ?>
                                <div class="status-info">
                                    <strong>User ID:</strong> #<?= $user['id'] ?><br>
                                    <strong>Created:</strong> <?= Helpers::formatDate($user['created_at']) ?><br>
                                    <strong>Status:</strong> <span class="badge badge-success">Active</span>
                                </div>
                            <?php else: ?>
                                <div class="text-muted">
                                    New user account will be active immediately after creation.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($isEdit && !empty($userRoles)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5>Current Roles:</h5>
                        <?php foreach ($userRoles as $role): ?>
                            <span class="badge badge-primary me-1"><?= Helpers::escape($role['name']) ?></span>
                        <?php endforeach; ?>
                        <p class="mb-0 mt-2"><small>Select a new role above to change the user's permissions.</small></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Update User' : 'Create User' ?>
                </button>
                <a href="/users" class="btn btn-secondary">Cancel</a>
                
                <?php if ($isEdit && $user['id'] != \App\Core\Auth::id()): ?>
                    <button type="button" class="btn btn-danger float-end" onclick="confirmDelete()">
                        Delete User
                    </button>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($isEdit && $user['id'] != \App\Core\Auth::id()): ?>
            <!-- Hidden Delete Form -->
            <form id="delete-form" method="POST" action="/users/<?= $user['id'] ?>/delete" style="display: none;">
                <?= Helpers::csrfField() ?>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.form-group {
    margin-bottom: 1.5rem;
}

.form-control-static {
    min-height: 38px;
    padding: 6px 12px;
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    border-radius: 4px;
    display: flex;
    align-items: center;
}

.status-info {
    font-size: 0.9rem;
    line-height: 1.4;
}

.form-actions {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

.alert-info {
    border-left: 4px solid #17a2b8;
}

.badge {
    font-size: 0.75rem;
    margin-right: 0.25rem;
}

.badge-primary { background-color: #007bff; }
.badge-success { background-color: #28a745; }

@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .float-end {
        float: none !important;
        margin-top: 1rem;
        width: 100%;
    }
}
</style>

<script>
function confirmDelete() {
    const userName = '<?= Helpers::escape($user['name'] ?? '') ?>';
    
    if (confirm(`Are you sure you want to delete the user "${userName}"?\n\nThis action cannot be undone and will remove all user data and permissions.`)) {
        document.getElementById('delete-form').submit();
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    
    if (!name) {
        alert('Full name is required');
        e.preventDefault();
        return;
    }
    
    if (!email) {
        alert('Email address is required');
        e.preventDefault();
        return;
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        e.preventDefault();
        return;
    }
    
    // Password validation
    if (!isEdit && !password) {
        alert('Password is required for new users');
        e.preventDefault();
        return;
    }
    
    if (password && password.length < 6) {
        alert('Password must be at least 6 characters long');
        e.preventDefault();
        return;
    }
});

// Dynamic role info
document.getElementById('role_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const roleInfo = document.getElementById('role-info');
    
    if (selectedOption.value && selectedOption.text.includes('-')) {
        const description = selectedOption.text.split(' - ')[1];
        if (!roleInfo) {
            const info = document.createElement('small');
            info.id = 'role-info';
            info.className = 'form-text text-info';
            this.parentNode.appendChild(info);
        }
        document.getElementById('role-info').textContent = description;
    } else if (roleInfo) {
        roleInfo.remove();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
