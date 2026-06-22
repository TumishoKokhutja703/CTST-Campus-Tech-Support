<?php
/**
 * nav.php — Shared top navigation bar and sidebar.
 * Include after <body> on every protected page.
 *
 * Expects $activePage to be set before inclusion:
 *   'dashboard' | 'tickets' | 'assigned' | 'reports'
 */
$activePage = $activePage ?? 'dashboard';
$staffName  = $_SESSION['staff_name'] ?? 'Staff';
$initials   = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $staffName))));
$initials   = substr($initials, 0, 2);
?>
<nav class="topnav">
    <div class="topnav-brand">
        <div class="topnav-brand-dot"></div>
        CTST
    </div>
    <div class="topnav-links">
        <a class="topnav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
        <a class="topnav-link <?= $activePage === 'tickets'   ? 'active' : '' ?>" href="tickets.php">Tickets</a>
        <a class="topnav-link <?= $activePage === 'reports'   ? 'active' : '' ?>" href="reports.php">Reports</a>
    </div>
    <div class="topnav-right">
        <a class="topnav-bell" href="#" title="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
        </a>
        <div class="avatar" title="<?= htmlspecialchars($staffName) ?>"><?= htmlspecialchars($initials) ?></div>
        <a class="topnav-signout" href="logout.php" title="Sign out">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
        </a>
    </div>
</nav>

<div class="app-body">
<aside class="sidebar">
    <div class="sidebar-section">Navigation</div>

    <a class="sidebar-item <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
        Dashboard
    </a>

    <a class="sidebar-item <?= $activePage === 'tickets' ? 'active' : '' ?>" href="tickets.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
            <path d="M9 12h6M9 16h4"/>
        </svg>
        All Tickets
    </a>

    <a class="sidebar-item <?= $activePage === 'assigned' ? 'active' : '' ?>" href="tickets.php?filter=mine">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
        </svg>
        Assigned to Me
    </a>

    <a class="sidebar-item <?= $activePage === 'reports' ? 'active' : '' ?>" href="reports.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <line x1="18" y1="20" x2="18" y2="10"/>
            <line x1="12" y1="20" x2="12" y2="4"/>
            <line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        Reports
    </a>
</aside>

<main class="main-content">
