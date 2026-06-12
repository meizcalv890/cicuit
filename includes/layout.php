<?php
function renderLayout(string $title, string $content, array $options = []): void {
    $role = Auth::role();
    $userName = $_SESSION['user_name'] ?? '';
    $currentPage = $options['page'] ?? '';
    $sidebar = $options['sidebar'] ?? '';
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> - <?= e(getSetting('nama_aplikasi', APP_NAME)) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?= e(Auth::dashboardUrl()) ?>" class="sidebar-brand">
                    <svg width="32" height="32" viewBox="0 0 48 48" fill="none">
                        <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="2"/>
                        <path d="M16 28c0-4 3.5-8 8-8s8 4 8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="24" cy="18" r="4" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span><?= e(getSetting('nama_aplikasi', APP_NAME)) ?></span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <?= $sidebar ?>
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="avatar"><?= e(getInitials($userName)) ?></div>
                    <div class="user-details">
                        <span class="user-name"><?= e($userName) ?></span>
                        <span class="user-role"><?= e(roleLabel($role ?? '')) ?></span>
                    </div>
                </div>
                <a href="<?= APP_URL ?>/logout.php" class="btn-logout" title="Keluar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16,17 21,12 16,7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <h1 class="page-title"><?= e($title) ?></h1>
                <div class="topbar-actions">
                    <div class="notification-bell" id="notificationBell">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 01-3.46 0"/>
                        </svg>
                        <span class="notification-count" id="notifCount" style="display:none">0</span>
                    </div>
                </div>
            </header>
            <div class="content-area">
                <?= $content ?>
            </div>
        </main>
    </div>

    <div class="toast-container" id="toastContainer"></div>
    <script>window.APP_URL = '<?= APP_URL ?>';</script>
    <script src="<?= APP_URL ?>/assets/js/app.js"></script>
    <?php if (!empty($options['scripts'])): ?>
        <?php foreach ($options['scripts'] as $script): ?>
        <script src="<?= APP_URL ?>/assets/js/<?= e($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
    <?php
}

function navItem(string $href, string $label, string $icon, string $currentPage, string $pageId): string {
    $active = $currentPage === $pageId ? ' active' : '';
    return '<a href="' . e($href) . '" class="nav-item' . $active . '">' . $icon . '<span>' . e($label) . '</span></a>';
}

function iconDashboard(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>';
}

function iconUsers(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>';
}

function iconReport(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
}

function iconCalendar(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
}

function iconShare(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>';
}

function iconBook(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>';
}

function iconJournal(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>';
}

function iconChart(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>';
}

function iconSettings(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>';
}

function iconAssign(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>';
}

function iconNote(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
}

function iconProfile(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
}

function iconLog(): string {
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>';
}
