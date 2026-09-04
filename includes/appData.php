<?php
declare(strict_types=1);
/**
 * includes/appData.php
 * Fetches global ticket counts used across dashboard, nav badges, etc.
 * Requires a valid $conn (mysqli) to already be open.
 */

$counts = $conn->query("
    SELECT
        COUNT(*)                                          AS total,
        SUM(status = 'Open')                             AS open,
        SUM(status = 'In Progress')                      AS in_progress,
        SUM(status = 'Resolved' OR status = 'Closed')   AS resolved,
        SUM(priority = 'Critical' AND status = 'Open')   AS critical
    FROM tblTickets
")->fetch_assoc();

$totalTickets    = (int)($counts['total']       ?? 0);
$countOpen       = (int)($counts['open']        ?? 0);
$countInProgress = (int)($counts['in_progress'] ?? 0);
$countResolved   = (int)($counts['resolved']    ?? 0);
$countCritical   = (int)($counts['critical']    ?? 0);

// Helper functions used across multiple pages
function statusBadgeClass(string $status): string {
    return match ($status) {
        'Open'        => 'badge-open',
        'In Progress' => 'badge-progress',
        'Resolved'    => 'badge-resolved',
        'Closed'      => 'badge-resolved',
        default       => 'badge-open',
    };
}

function priorityDotClass(string $priority): string {
    return match ($priority) {
        'High', 'Critical' => 'dot-high',
        'Medium'           => 'dot-med',
        default            => 'dot-low',
    };
}

function initials(string $name): string {
    $words = array_filter(explode(' ', trim($name)));
    return strtoupper(implode('', array_map(fn($w) => $w[0], array_slice($words, 0, 2))));
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return (int)($diff / 60) . 'm ago';
    if ($diff < 86400)  return (int)($diff / 3600) . 'h ago';
    return (int)($diff / 86400) . 'd ago';
}
