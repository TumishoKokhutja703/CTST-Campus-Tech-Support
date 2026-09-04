<?php
/**
 * nav.php — Shared navigation bar + sidebar.
 * $activePage must be set before including this file.
 */
$activePage  = $activePage  ?? 'dashboard';
$staffName   = $_SESSION['staff_name'] ?? 'Staff';
$staffRole   = $_SESSION['staff_role'] ?? 'technician';
$initials    = strtoupper(implode('', array_map(fn($w) => $w[0],
               array_slice(array_filter(explode(' ', $staffName)), 0, 2))));
?>
<nav class="topnav">
    <div class="topnav-brand">
        <div class="topnav-brand-dot">C</div>
        CTST
    </div>
    <div class="topnav-links">
        <a class="topnav-link <?= $activePage==='dashboard'    ? 'active':'' ?>" href="dashboard.php">Dashboard</a>
        <a class="topnav-link <?= $activePage==='tickets'      ? 'active':'' ?>" href="tickets.php">Tickets</a>
        <a class="topnav-link <?= $activePage==='reports'      ? 'active':'' ?>" href="reports.php">Reports</a>
        <?php if (in_array($staffRole, ['admin','senior'])): ?>
        <a class="topnav-link <?= $activePage==='manage_staff' ? 'active':'' ?>" href="manage_staff.php">Manage Staff</a>
        <?php endif; ?>
    </div>
    <div class="topnav-right">
        <!-- Theme picker injected here by theme.js -->
        <a class="topnav-bell" href="#" title="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </a>
        <div class="avatar" title="<?= htmlspecialchars($staffName) ?>"><?= htmlspecialchars($initials) ?></div>
        <a class="topnav-signout" href="logout.php" title="Sign out">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
    </div>
</nav>

<div class="app-body">
<aside class="sidebar">
    <div class="sidebar-section">Menu</div>
    <a class="sidebar-item <?= $activePage==='dashboard'    ? 'active':'' ?>" href="dashboard.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <span>Dashboard</span>
    </a>
    <a class="sidebar-item <?= $activePage==='tickets'      ? 'active':'' ?>" href="tickets.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
        <span>All Tickets</span>
    </a>
    <a class="sidebar-item <?= $activePage==='assigned'     ? 'active':'' ?>" href="tickets.php?filter=mine">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Assigned to Me</span>
    </a>
    <a class="sidebar-item <?= $activePage==='reports'      ? 'active':'' ?>" href="reports.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        <span>Reports</span>
    </a>
    <?php if (in_array($staffRole, ['admin','senior'])): ?>
    <a class="sidebar-item <?= $activePage==='manage_staff' ? 'active':'' ?>" href="manage_staff.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Manage Staff</span>
    </a>
    <?php endif; ?>
</aside>

<main class="main-content">
