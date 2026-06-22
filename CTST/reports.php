<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/appData.php';

$activePage = 'reports';

// Prototype report stats (replace with real DB aggregates)
$avgResolution = '2.4';
$resolvedPct   = 94;
$weeklyRows = [
    ['day'=>'Monday',    'opened'=>8,  'resolved'=>7,  'avg'=>'1.8h', 'sla'=>100],
    ['day'=>'Tuesday',   'opened'=>12, 'resolved'=>11, 'avg'=>'2.1h', 'sla'=>92],
    ['day'=>'Wednesday', 'opened'=>9,  'resolved'=>9,  'avg'=>'2.4h', 'sla'=>100],
    ['day'=>'Thursday',  'opened'=>14, 'resolved'=>12, 'avg'=>'3.1h', 'sla'=>86],
    ['day'=>'Friday',    'opened'=>12, 'resolved'=>13, 'avg'=>'2.6h', 'sla'=>100],
];

$chartBars = [
    ['label'=>'Network',  'pct'=>45, 'class'=>'bar-network'],
    ['label'=>'Hardware', 'pct'=>30, 'class'=>'bar-hardware'],
    ['label'=>'Software', 'pct'=>25, 'class'=>'bar-software'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST | Reports</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-shell">

    <?php require __DIR__ . '/nav.php'; ?>

        <!-- Page header -->
        <div class="page-header">
            <div>
                <div class="page-title">Reports</div>
                <div class="page-sub">Performance overview</div>
            </div>
            <div class="filter-row">
                <select class="select-input">
                    <option>This Week</option>
                    <option>Last 7 Days</option>
                    <option>This Month</option>
                    <option>Last 30 Days</option>
                </select>
                <button class="btn-sm">Export PDF</button>
            </div>
        </div>

        <!-- KPI cards -->
        <div class="reports-metrics">
            <div class="report-metric-card">
                <div class="report-metric-label">Average Resolution Time</div>
                <div>
                    <span class="report-metric-value"><?= htmlspecialchars($avgResolution) ?></span>
                    <span class="report-metric-unit">hours</span>
                </div>
                <div class="report-metric-trend">&#8595; 18% faster than last week</div>
            </div>
            <div class="report-metric-card">
                <div class="report-metric-label">Resolved This Week</div>
                <div>
                    <span class="report-metric-value"><?= $resolvedPct ?></span>
                    <span class="report-metric-unit">%</span>
                </div>
                <div class="report-metric-trend">&#8593; Up from 87% last week</div>
            </div>
        </div>

        <!-- Bar chart -->
        <div class="chart-card" style="margin-bottom:20px;">
            <div class="chart-header">
                <div>
                    <div class="section-label">Tickets by Category</div>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:3px;">Based on <?= $totalTickets ?> tickets this week</div>
                </div>
                <select class="select-input">
                    <option><?= date('d M') ?> – <?= date('d M', strtotime('+6 days')) ?> <?= date('Y') ?></option>
                    <option>Previous week</option>
                </select>
            </div>

            <div class="bar-chart">
                <?php foreach ($chartBars as $bar): ?>
                <div class="bar-row">
                    <div class="bar-label"><?= htmlspecialchars($bar['label']) ?></div>
                    <div class="bar-track">
                        <div class="bar-fill <?= $bar['class'] ?>" style="width:0" data-target="<?= $bar['pct'] ?>">
                            <span><?= $bar['pct'] ?>%</span>
                        </div>
                    </div>
                    <div class="bar-pct"><?= $bar['pct'] ?>%</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Weekly breakdown table -->
        <div class="section-header">
            <div class="section-label">Weekly Breakdown</div>
        </div>
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Tickets Opened</th>
                        <th>Tickets Resolved</th>
                        <th>Avg Resolution Time</th>
                        <th>SLA Met</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weeklyRows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['day']) ?></td>
                        <td style="font-family:var(--mono);"><?= $row['opened'] ?></td>
                        <td style="font-family:var(--mono);"><?= $row['resolved'] ?></td>
                        <td style="font-family:var(--mono);"><?= htmlspecialchars($row['avg']) ?></td>
                        <td>
                            <?php if ($row['sla'] >= 95): ?>
                                <span class="badge badge-resolved"><?= $row['sla'] ?>%</span>
                            <?php elseif ($row['sla'] >= 80): ?>
                                <span class="badge badge-progress"><?= $row['sla'] ?>%</span>
                            <?php else: ?>
                                <span class="badge badge-critical"><?= $row['sla'] ?>%</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>
</div><!-- .app-body -->
</div><!-- .app-shell -->

<script>
// Animate bar chart on load
window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.querySelectorAll('.bar-fill').forEach(bar => {
            bar.style.transition = 'width 0.8s cubic-bezier(0.16,1,0.3,1)';
            bar.style.width = bar.getAttribute('data-target') + '%';
        });
    }, 100);
});
</script>
</body>
</html>
