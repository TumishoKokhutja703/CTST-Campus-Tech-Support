<?php
declare(strict_types=1);

/**
 * appData.php — Prototype data for CTST.
 * Replace these arrays with real MySQLi queries against ctst_db once the DB is set up.
 */

$tickets = [
    [
        'id'          => 'TKT-042',
        'description' => 'Wi-Fi drops intermittently in Block C computer lab',
        'category'    => 'Network',
        'priority'    => 'High',
        'status'      => 'In Progress',
        'assigned_to' => 'J. Mokoena',
        'assigned_initials' => 'JM',
        'submitted_by'=> 'Nomsa Bhengu',
        'date_opened' => '2025-05-19 08:42',
        'detail'      => 'Students and lecturers in the Block C computer lab (Room C-07, 40 machines) are experiencing intermittent Wi-Fi disconnections approximately every 15–20 minutes. The issue began on Monday morning after the scheduled network maintenance window. Devices reconnect automatically but the disruption is causing significant disruption to online assessments currently underway. Wired connections via LAN appear unaffected.',
        'comments'    => [
            ['initials'=>'JM','author'=>'J. Mokoena','time'=>'Today at 09:15','text'=>'Investigated the Cisco AP in C-07. DHCP lease renewal is timing out due to a misconfigured scope added during last night\'s maintenance. Rolling back the scope change now. Will monitor for 30 minutes and update.'],
            ['initials'=>'TN','author'=>'T. Nkosi','time'=>'Today at 09:51','text'=>'Scope rolled back. Monitoring in progress — no drops recorded in the last 25 minutes. Leaving ticket in progress until I can confirm stability after the 12h00 exam session.'],
        ],
    ],
    [
        'id'          => 'TKT-041',
        'description' => 'Projector not displaying HDMI signal in Room 204',
        'category'    => 'Hardware',
        'priority'    => 'Medium',
        'status'      => 'Open',
        'assigned_to' => 'S. Pillay',
        'assigned_initials' => 'SP',
        'submitted_by'=> 'Lecturer: K. Dlamini',
        'date_opened' => '2025-05-19 10:05',
        'detail'      => 'The ceiling-mounted projector in Room 204 does not display any image when connected via HDMI. VGA connection works. Affects all laptops tested. Likely a faulty HDMI port on the projector.',
        'comments'    => [],
    ],
    [
        'id'          => 'TKT-040',
        'description' => 'Student portal login failure — password reset loop',
        'category'    => 'Software',
        'priority'    => 'High',
        'status'      => 'Open',
        'assigned_to' => 'T. Nkosi',
        'assigned_initials' => 'TN',
        'submitted_by'=> 'Reception Desk',
        'date_opened' => '2025-05-18 14:30',
        'detail'      => 'Multiple students report being stuck in a password-reset loop on the student portal. After resetting, the system prompts for another reset immediately. Affects approximately 12 student accounts registered in the last 48 hours.',
        'comments'    => [],
    ],
    [
        'id'          => 'TKT-039',
        'description' => 'Printer queue stuck on 2nd floor admin office',
        'category'    => 'Hardware',
        'priority'    => 'Low',
        'status'      => 'Resolved',
        'assigned_to' => 'L. van Wyk',
        'assigned_initials' => 'LV',
        'submitted_by'=> 'Admin Office',
        'date_opened' => '2025-05-17 09:10',
        'detail'      => 'The HP LaserJet on the 2nd floor had a stuck print queue preventing any new jobs from printing. Cleared the spooler and restarted the print service.',
        'comments'    => [
            ['initials'=>'LV','author'=>'L. van Wyk','time'=>'Yesterday at 11:20','text'=>'Print spooler cleared and service restarted. Queue is flowing normally. Marking as resolved.'],
        ],
    ],
];

// Metrics derived from tickets
$countOpen       = count(array_filter($tickets, fn($t) => $t['status'] === 'Open'));
$countInProgress = count(array_filter($tickets, fn($t) => $t['status'] === 'In Progress'));
$countResolved   = count(array_filter($tickets, fn($t) => $t['status'] === 'Resolved'));
$countCritical   = count(array_filter($tickets, fn($t) => $t['priority'] === 'High'));
$totalTickets    = count($tickets);

// Priority colour map
function priorityDotClass(string $priority): string {
    return match($priority) {
        'High'   => 'dot-high',
        'Medium' => 'dot-med',
        default  => 'dot-low',
    };
}

// Status badge class map
function statusBadgeClass(string $status): string {
    return match($status) {
        'Open'        => 'badge-open',
        'In Progress' => 'badge-progress',
        'Resolved'    => 'badge-resolved',
        default       => 'badge-open',
    };
}
