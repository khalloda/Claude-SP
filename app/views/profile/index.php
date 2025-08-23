<?php
// Create this file: app/views/profile/index.php

use App\Core\I18n;
use App\Core\Helpers;

$title = 'My Profile - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title">My Profile</h1>
            <div>
                <a href="/profile/edit" class="btn btn-primary">Edit Profile</a>
                <a href="/profile/change-password" class="btn btn-secondary">Change Password</a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="profile-info">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td><?= Helpers::escape($user['name']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td><?= Helpers::escape($user['email']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Language:</strong></td>
                            <td>
                                <?php if ($user['locale'] === 'en'): ?>
                                    <span class="badge badge-primary">English</span>
                                <?php else: ?>
                                    <span class="badge badge-success">العربية</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Account Created:</strong></td>
                            <td><?= Helpers::formatDate($user['created_at']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="profile-stats">
                        <h3>Account Information</h3>
                        <div class="stat-item">
                            <div class="stat-label">User ID</div>
                            <div class="stat-value">#<?= $user['id'] ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Account Status</div>
                            <div class="stat-value">
                                <span class="badge badge-success">Active</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Role</div>
                            <div class="stat-value">
                                <?php 
                                $role = $_SESSION['role'] ?? 'user';
                                $roleClass = $role === 'admin' ? 'badge-danger' : 'badge-info';
                                ?>
                                <span class="badge <?= $roleClass ?>"><?= ucfirst($role) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-actions mt-4">
            <h3>Quick Actions</h3>
            <div class="action-grid">
                <a href="/profile/edit" class="action-card">
                    <i class="fas fa-edit"></i>
                    <h4>Edit Profile</h4>
                    <p>Update your personal information</p>
                </a>
                
                <a href="/profile/change-password" class="action-card">
                    <i class="fas fa-lock"></i>
                    <h4>Change Password</h4>
                    <p>Update your account security</p>
                </a>
                
                <a href="#" onclick="showCurrencySelector()" class="action-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <h4>Change Currency</h4>
                    <p>Switch display currency</p>
                </a>
                
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="/currencies" class="action-card admin-only">
                    <i class="fas fa-coins"></i>
                    <h4>Manage Currencies</h4>
                    <p>System currency administration</p>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.profile-info .table td {
    padding: 0.75rem;
    border-bottom: 1px solid #dee2e6;
}

.profile-stats {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
}

.profile-stats h3 {
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.stat-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.stat-label {
    font-weight: 500;
    opacity: 0.9;
}

.stat-value {
    font-weight: 600;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.action-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
    cursor: pointer;
}

.action-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.1);
    text-decoration: none;
    color: #333;
}

.action-card.admin-only {
    border-color: #28a745;
}

.action-card.admin-only:hover {
    border-color: #20c997;
    box-shadow: 0 4px 20px rgba(40, 167, 69, 0.2);
}

.action-card i {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: #667eea;
}

.action-card.admin-only i {
    color: #28a745;
}

.action-card h4 {
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.action-card p {
    margin: 0;
    font-size: 0.9rem;
    color: #666;
}

.badge {
    padding: 0.35rem 0.65rem;
    font-size: 0.75rem;
    border-radius: 0.375rem;
}

.badge-primary { background-color: #007bff; }
.badge-success { background-color: #28a745; }
.badge-info { background-color: #17a2b8; }
.badge-danger { background-color: #dc3545; }

@media (max-width: 768px) {
    .action-grid {
        grid-template-columns: 1fr;
    }
    
    .profile-stats {
        margin-top: 1rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
