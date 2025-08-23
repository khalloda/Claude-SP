<?php
// Create this file: app/views/users/show.php

use App\Core\I18n;
use App\Core\Helpers;
use App\Core\Auth;

$title = $user['name'] . ' - User Details - ' . I18n::t('app.name');
$showNav = true;

ob_start();
?>

<div class="card">
    <div class="card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="card-title">
                <?= Helpers::escape($user['name']) ?>
                <?php if ($user['id'] == Auth::id()): ?>
                    <span class="badge badge-info ms-2">You</span>
                <?php endif; ?>
            </h1>
            <div>
                <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-primary">Edit User</a>
                <a href="/users" class="btn btn-secondary">Back to Users</a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- User Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h3>Basic Information</h3>
                <table class="table table-borderless">
                    <tr>
                        <td><strong>User ID:</strong></td>
                        <td>#<?= $user['id'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Full Name:</strong></td>
                        <td><?= Helpers::escape($user['name']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email Address:</strong></td>
                        <td>
                            <a href="mailto:<?= Helpers::escape($user['email']) ?>">
                                <?= Helpers::escape($user['email']) ?>
                            </a>
                        </td>
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
                    <tr>
                        <td><strong>Account Status:</strong></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <h3>Roles & Permissions</h3>
                <?php if (!empty($userRoles)): ?>
                    <div class="role-section">
                        <?php foreach ($userRoles as $role): ?>
                            <div class="role-card">
                                <?php 
                                $badgeClass = match($role['name']) {
                                    'admin' => 'badge-danger',
                                    'manager' => 'badge-warning',
                                    default => 'badge-info'
                                };
                                ?>
                                <div class="role-header">
                                    <span class="badge <?= $badgeClass ?> role-badge">
                                        <?= Helpers::escape($role['name']) ?>
                                    </span>
                                </div>
                                <?php if (!empty($role['description'])): ?>
                                    <div class="role-description">
                                        <?= Helpers::escape($role['description']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This user has no specific roles assigned. They have basic user permissions only.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity Statistics -->
        <div class="row mb-4">
            <div class="col-12">
                <h3>Account Activity</h3>
                <div class="row">
                    <div class="col-md-4">
                        <div class="activity-stat">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-label">Member Since</div>
                                <div class="stat-value"><?= date('M Y', strtotime($user['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="activity-stat">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-label">Last Login</div>
                                <div class="stat-value"><?= $userStats['last_login'] ?? 'Never' ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="activity-stat">
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-label">Login Count</div>
                                <div class="stat-value"><?= $userStats['login_count'] ?? 0 ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <h3>Actions</h3>
                <div class="action-buttons">
                    <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit User Details
                    </a>
                    
                    <?php if ($user['id'] != Auth::id()): ?>
                        <button type="button" class="btn btn-warning" onclick="resetPassword()">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </button>
                        
                        <form method="POST" action="/users/<?= $user['id'] ?>/delete" style="display: inline;">
                            <?= Helpers::csrfField() ?>
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Are you sure you want to delete this user?\n\nUser: <?= Helpers::escape($user['name']) ?>\nEmail: <?= Helpers::escape($user['email']) ?>\n\nThis action cannot be undone!')">
                                <i class="fas fa-trash me-2"></i>Delete User
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            You cannot delete your own account. Ask another administrator to manage your account if needed.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.role-section {
    margin-top: 1rem;
}

.role-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.role-header {
    margin-bottom: 0.5rem;
}

.role-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.role-description {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.4;
}

.activity-stat {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.activity-stat:hover {
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 2rem;
    color: #667eea;
    margin-bottom: 1rem;
}

.stat-label {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.badge {
    font-size: 0.8rem;
    padding: 0.35rem 0.65rem;
}

.badge-primary { background-color: #007bff; }
.badge-success { background-color: #28a745; }
.badge-info { background-color: #17a2b8; }
.badge-warning { background-color: #ffc107; color: #000; }
.badge-danger { background-color: #dc3545; }

.alert {
    border-radius: 8px;
    border-left: 4px solid;
}

.alert-info {
    border-left-color: #17a2b8;
}

.alert-warning {
    border-left-color: #ffc107;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .activity-stat {
        margin-bottom: 1rem;
    }
}

/* RTL Support */
[dir="rtl"] .role-badge {
    margin-left: 0.5rem;
    margin-right: 0;
}

[dir="rtl"] .stat-icon {
    margin-left: 0.5rem;
    margin-right: 0;
}
</style>

<script>
function resetPassword() {
    const userName = '<?= Helpers::escape($user['name']) ?>';
    const userEmail = '<?= Helpers::escape($user['email']) ?>';
    
    if (confirm(`Reset password for ${userName}?\n\nA new temporary password will need to be generated and shared with the user securely.`)) {
        // For now, redirect to edit page where password can be changed
        // In the future, this could generate a temporary password
        window.location.href = '/users/<?= $user['id'] ?>/edit';
    }
}

// Add some interactivity
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to activity stats
    const stats = document.querySelectorAll('.activity-stat');
    stats.forEach(stat => {
        stat.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
        });
        
        stat.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Add click to copy email
    const emailLink = document.querySelector('a[href^="mailto:"]');
    if (emailLink) {
        emailLink.addEventListener('click', function(e) {
            e.preventDefault();
            const email = this.textContent.trim();
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(email).then(() => {
                    // Brief visual feedback
                    const original = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check text-success"></i> Email copied!';
                    setTimeout(() => {
                        this.innerHTML = original;
                    }, 2000);
                });
            } else {
                // Fallback - open email client
                window.location.href = this.href;
            }
        });
        
        emailLink.title = 'Click to copy email address';
        emailLink.style.cursor = 'pointer';
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
