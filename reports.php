<?php
declare(strict_types=1);
/**
 * reports.php
 * Shows ticket statistics, category chart, status donut, weekly table
 * and exports everything to a PDF with one click.
 */

// 1. Check the user is logged in
require_once __DIR__ . '/includes/auth.php';

// 2. Connect to the database
require_once __DIR__ . '/includes/DBConn.php';
$conn = getDbConnection();

// 3. Load shared counts (fills $countOpen, $countResolved, $countInProgress, $totalTickets)
$activePage = 'reports';
require_once __DIR__ . '/includes/appData.php';

// 4. Get ticket count per category from the database
$catResult = $conn->query(
    "SELECT category, COUNT(*) AS total
     FROM tblTickets
     GROUP BY category
     ORDER BY total DESC"
);
$catRows = $catResult ? $catResult->fetch_all(MYSQLI_ASSOC) : [];

// 5. Get this week's breakdown from the database
$weekResult = $conn->query(
    "SELECT
        DAYNAME(date_opened)  AS day,
        COUNT(*)              AS opened,
        SUM(status IN ('Resolved','Closed')) AS resolved,
        ROUND(AVG(
            CASE WHEN date_resolved IS NOT NULL
                 THEN TIMESTAMPDIFF(MINUTE, date_opened, date_resolved) / 60
            END
        ), 1) AS avg_hours
     FROM tblTickets
     WHERE date_opened >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
     GROUP BY DAYNAME(date_opened), DAYOFWEEK(date_opened)
     ORDER BY DAYOFWEEK(date_opened)"
);
$weekRows = $weekResult ? $weekResult->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();

// 6. Calculate summary numbers
$resolvedPct   = $totalTickets > 0 ? round(($countResolved / $totalTickets) * 100) : 0;
$avgResolution = '—';

if (!empty($weekRows)) {
    $hrs = array_filter(array_column($weekRows, 'avg_hours'), fn($v) => $v !== null);
    if (!empty($hrs)) {
        $avgResolution = round(array_sum($hrs) / count($hrs), 1);
    }
}

// 7. Build chart bar data
$colorMap = [
    'Network'  => 'bar-network',
    'Hardware' => 'bar-hardware',
    'Software' => 'bar-software',
    'Other'    => 'bar-other',
];
$chartBars = [];
foreach ($catRows as $cat) {
    $pct         = $totalTickets > 0 ? round(($cat['total'] / $totalTickets) * 100) : 0;
    $chartBars[] = [
        'label' => $cat['category'],
        'count' => (int)$cat['total'],
        'pct'   => $pct,
        'class' => $colorMap[$cat['category']] ?? 'bar-other',
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTST | Reports</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* ── KPI cards ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px 20px 18px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-xs);
            transition: transform .2s, box-shadow .2s;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }

        /* Coloured top bar on each card */
        .kpi-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            border-radius: 14px 14px 0 0;
        }
        .kpi-card:nth-child(1)::before { background: linear-gradient(90deg, #0F2D52, #2563A8); }
        .kpi-card:nth-child(2)::before { background: linear-gradient(90deg, #15803D, #4ADE80); }
        .kpi-card:nth-child(3)::before { background: linear-gradient(90deg, #C47C00, #FCD34D); }
        .kpi-card:nth-child(4)::before { background: linear-gradient(90deg, #B91C1C, #F87171); }

        .kpi-label {
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.8px;
            color: var(--text-muted); margin-bottom: 12px;
        }
        .kpi-value {
            font-size: 36px; font-weight: 700;
            letter-spacing: -2px; line-height: 1;
            font-family: var(--mono);
        }
        .kpi-card:nth-child(1) .kpi-value { color: var(--navy-mid); }
        .kpi-card:nth-child(2) .kpi-value { color: var(--resolved); }
        .kpi-card:nth-child(3) .kpi-value { color: var(--progress); }
        .kpi-card:nth-child(4) .kpi-value { color: var(--critical); }
        .kpi-unit  { font-size: 14px; color: var(--text-secondary); margin-left: 3px; }
        .kpi-trend { font-size: 12px; color: var(--text-muted); margin-top: 10px; }

        /* ── Charts row ── */
        .charts-row {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        /* ── Bar chart ── */
        .chart-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
            box-shadow: var(--shadow-xs);
        }
        .chart-header {
            display: flex; justify-content: space-between;
            align-items: flex-start; margin-bottom: 20px;
        }
        .bar-chart { display: flex; flex-direction: column; gap: 14px; }
        .bar-row   { display: flex; align-items: center; gap: 12px; }
        .bar-label {
            width: 80px; font-size: 13px; font-weight: 500;
            color: var(--text-secondary); text-align: right; flex-shrink: 0;
        }
        .bar-track {
            flex: 1; height: 28px; background: var(--bg);
            border-radius: 7px; overflow: hidden; border: 1px solid var(--border);
        }
        /* Width starts at 0 — JavaScript animates it to data-target% */
        .bar-fill {
            height: 100%; width: 0;
            border-radius: 7px;
            display: flex; align-items: center;
            justify-content: flex-end; padding-right: 10px;
            transition: width 0.9s cubic-bezier(0.16,1,0.3,1);
        }
        .bar-fill span { font-size: 11px; font-weight: 700; color: #fff; }
        .bar-count { width: 32px; font-size: 12px; color: var(--text-muted); font-family: var(--mono); }

        .bar-network  { background: linear-gradient(90deg, #0F2D52, #1A4A7A); }
        .bar-hardware { background: linear-gradient(90deg, #1D6FBF, #3B82F6); }
        .bar-software { background: linear-gradient(90deg, #3B82F6, #60A5FA); }
        .bar-other    { background: linear-gradient(90deg, #475569, #64748B); }

        /* ── Donut chart ── */
        .donut-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 24px;
            box-shadow: var(--shadow-xs);
        }
        .donut-wrap {
            display: flex; align-items: center;
            justify-content: center; gap: 28px;
            margin-top: 20px; flex-wrap: wrap;
        }
        .donut-legend { display: flex; flex-direction: column; gap: 12px; }
        .donut-legend-item { display: flex; align-items: center; gap: 10px; font-size: 13px; }
        .donut-legend-dot  { width: 11px; height: 11px; border-radius: 3px; flex-shrink: 0; }
        .donut-legend-label { color: var(--text-secondary); font-weight: 500; }
        .donut-legend-count { font-family: var(--mono); font-weight: 700; color: var(--text-primary); margin-left: auto; padding-left: 16px; }

        /* ── Export PDF button ── */
        .btn-export {
            height: 38px; padding: 0 18px; border-radius: 9px;
            border: none; cursor: pointer; font-family: var(--font);
            font-size: 13px; font-weight: 600;
            background: linear-gradient(135deg, #0F2D52, #2563A8);
            color: #fff;
            display: inline-flex; align-items: center; gap: 7px;
            box-shadow: 0 2px 8px rgba(37,99,168,0.3);
            transition: opacity .15s, transform .1s;
        }
        .btn-export:hover { opacity: .9; transform: translateY(-1px); }
        .btn-export svg   { width: 15px; height: 15px; stroke: white; fill: none; stroke-width: 2; }

        /* ── Empty chart placeholder ── */
        .chart-empty {
            text-align: center; padding: 40px 20px;
            color: var(--text-muted); font-size: 13px; line-height: 1.7;
        }

        @media (max-width: 800px) {
            .kpi-grid   { grid-template-columns: 1fr 1fr; }
            .charts-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="app-shell">

<?php require __DIR__ . '/nav.php'; ?>

    <!-- Page header with Export button -->
    <div class="page-header">
        <div>
            <div class="page-title">Reports</div>
            <div class="page-sub">Live data — <?= date('d F Y') ?></div>
        </div>
        <button class="btn-export" onclick="exportPDF()">
            <svg viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export PDF
        </button>
    </div>

    <!-- ── KPI cards ── -->
    <div class="kpi-grid">

        <div class="kpi-card">
            <div class="kpi-label">Avg. Resolution Time</div>
            <div>
                <span class="kpi-value"><?= $avgResolution ?></span>
                <?php if ($avgResolution !== '—'): ?>
                    <span class="kpi-unit">hrs</span>
                <?php endif; ?>
            </div>
            <div class="kpi-trend">Based on resolved tickets this week</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Resolution Rate</div>
            <div>
                <span class="kpi-value"><?= $resolvedPct ?></span>
                <span class="kpi-unit">%</span>
            </div>
            <div class="kpi-trend"><?= $countResolved ?> of <?= $totalTickets ?> tickets resolved</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Currently Open</div>
            <div>
                <span class="kpi-value"><?= $countOpen ?></span>
                <span class="kpi-unit">tickets</span>
            </div>
            <div class="kpi-trend"><?= $countInProgress ?> in progress right now</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Total Resolved</div>
            <div>
                <span class="kpi-value"><?= $countResolved ?></span>
                <span class="kpi-unit">tickets</span>
            </div>
            <div class="kpi-trend">All time</div>
        </div>

    </div>

    <!-- ── Charts row ── -->
    <div class="charts-row">

        <!-- Left: bar chart — tickets by category -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="section-label">Tickets by Category</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">
                        <?= $totalTickets ?> total ticket<?= $totalTickets !== 1 ? 's' : '' ?> in system
                    </div>
                </div>
            </div>

            <?php if (empty($chartBars)): ?>
                <div class="chart-empty">
                    No ticket data yet.<br>
                    Tickets from the mobile app will appear here.
                </div>
            <?php else: ?>
                <div class="bar-chart">
                    <?php foreach ($chartBars as $bar): ?>
                    <div class="bar-row">
                        <div class="bar-label"><?= htmlspecialchars($bar['label']) ?></div>
                        <div class="bar-track">
                            <!--
                                width starts at 0.
                                JavaScript below animates it to data-target percent.
                            -->
                            <div class="bar-fill <?= $bar['class'] ?>"
                                 data-target="<?= $bar['pct'] ?>">
                                <span><?= $bar['pct'] ?>%</span>
                            </div>
                        </div>
                        <div class="bar-count"><?= $bar['count'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: donut chart — status breakdown -->
        <div class="donut-card">
            <div class="section-label">Status Breakdown</div>

            <?php if ($totalTickets === 0): ?>
                <div class="chart-empty">No tickets yet.</div>
            <?php else:
                // Calculate each segment as a fraction of the SVG circle circumference
                $C    = 99.9; // circumference of circle r=15.9
                $safe = max($totalTickets, 1);
                $rLen = round($C * ($countResolved   / $safe), 1);
                $iLen = round($C * ($countInProgress / $safe), 1);
                $oLen = round($C * ($countOpen       / $safe), 1);
            ?>
            <div class="donut-wrap">

                <!--
                    SVG donut chart.
                    Each circle is drawn as a dashed stroke.
                    stroke-dasharray="filled empty" controls the segment size.
                    stroke-dashoffset rotates each segment into position.
                -->
                <svg width="130" height="130" viewBox="0 0 42 42">
                    <!-- Grey background ring -->
                    <circle cx="21" cy="21" r="15.9"
                            fill="transparent"
                            stroke="var(--border)"
                            stroke-width="6"/>
                    <!-- Resolved — green -->
                    <circle cx="21" cy="21" r="15.9"
                            fill="transparent"
                            stroke="#15803D"
                            stroke-width="6"
                            stroke-dasharray="<?= $rLen ?> <?= $C - $rLen ?>"
                            stroke-dashoffset="25"
                            stroke-linecap="round"/>
                    <!-- In Progress — amber -->
                    <circle cx="21" cy="21" r="15.9"
                            fill="transparent"
                            stroke="#C47C00"
                            stroke-width="6"
                            stroke-dasharray="<?= $iLen ?> <?= $C - $iLen ?>"
                            stroke-dashoffset="-<?= round($rLen - 25, 1) ?>"
                            stroke-linecap="round"/>
                    <!-- Open — blue -->
                    <circle cx="21" cy="21" r="15.9"
                            fill="transparent"
                            stroke="#1D6FBF"
                            stroke-width="6"
                            stroke-dasharray="<?= $oLen ?> <?= $C - $oLen ?>"
                            stroke-dashoffset="-<?= round($rLen + $iLen - 25, 1) ?>"
                            stroke-linecap="round"/>
                    <!-- Centre total number -->
                    <text x="21" y="19.5" text-anchor="middle"
                          font-size="5.5" font-weight="700"
                          fill="var(--text-primary)"
                          font-family="monospace">
                        <?= $totalTickets ?>
                    </text>
                    <text x="21" y="24" text-anchor="middle"
                          font-size="3"
                          fill="var(--text-muted)"
                          font-family="sans-serif">
                        total
                    </text>
                </svg>

                <!-- Legend -->
                <div class="donut-legend">
                    <div class="donut-legend-item">
                        <div class="donut-legend-dot" style="background:#15803D"></div>
                        <span class="donut-legend-label">Resolved</span>
                        <span class="donut-legend-count"><?= $countResolved ?></span>
                    </div>
                    <div class="donut-legend-item">
                        <div class="donut-legend-dot" style="background:#C47C00"></div>
                        <span class="donut-legend-label">In Progress</span>
                        <span class="donut-legend-count"><?= $countInProgress ?></span>
                    </div>
                    <div class="donut-legend-item">
                        <div class="donut-legend-dot" style="background:#1D6FBF"></div>
                        <span class="donut-legend-label">Open</span>
                        <span class="donut-legend-count"><?= $countOpen ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Weekly breakdown table ── -->
    <div class="section-header">
        <div class="section-label">This Week's Breakdown</div>
        <span style="font-size:12px;color:var(--text-muted)">Last 7 days — live from database</span>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Tickets Opened</th>
                    <th>Tickets Resolved</th>
                    <th>Avg. Resolution Time</th>
                </tr>
            </thead>
            <tbody>

            <?php if (empty($weekRows)): ?>
                <!-- No data this week -->
                <tr>
                    <td colspan="4">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                                <rect x="9" y="3" width="6" height="4" rx="1"/>
                            </svg>
                            <div class="empty-state-title">No activity this week</div>
                            <div class="empty-state-sub">Data appears here as tickets are submitted.</div>
                        </div>
                    </td>
                </tr>

            <?php else:
                // Track totals for the bottom row
                $totalOpened   = 0;
                $totalResolved = 0;
                foreach ($weekRows as $r):
                    $totalOpened   += (int)$r['opened'];
                    $totalResolved += (int)$r['resolved'];
                    $avgDisplay     = $r['avg_hours'] !== null ? $r['avg_hours'] . 'h' : '—';
            ?>
                <tr>
                    <td style="font-weight:500"><?= htmlspecialchars($r['day']) ?></td>
                    <td style="font-family:var(--mono);font-weight:600"><?= (int)$r['opened'] ?></td>
                    <td style="font-family:var(--mono);font-weight:600;color:var(--resolved)"><?= (int)$r['resolved'] ?></td>
                    <td style="font-family:var(--mono)"><?= $avgDisplay ?></td>
                </tr>
            <?php endforeach; ?>

                <!-- Totals row -->
                <tr style="background:var(--bg);font-weight:700">
                    <td>Total</td>
                    <td style="font-family:var(--mono)"><?= $totalOpened ?></td>
                    <td style="font-family:var(--mono);color:var(--resolved)"><?= $totalResolved ?></td>
                    <td style="color:var(--text-muted)">—</td>
                </tr>

            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>
</div>
</div>

<!-- jsPDF — runs entirely in the browser, no server needed -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
// Animate the bar chart bars after the page loads
window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.querySelectorAll('.bar-fill').forEach(bar => {
            bar.style.width = bar.getAttribute('data-target') + '%';
        });
    }, 300);
});

// Export to PDF — uses jsPDF loaded above
function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc   = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
    const pageW = 210;
    const pageH = 297;
    const M     = 16;  // page margin

    // Colours
    const navy  = [15, 45, 82];
    const white = [255, 255, 255];
    const green = [21, 128, 61];
    const amber = [196, 124, 0];
    const muted = [148, 163, 184];
    const light = [240, 244, 248];
    let y = 0;

    // ── Header band ──────────────────────────────────────────────────────────
    doc.setFillColor(...navy);
    doc.rect(0, 0, pageW, 28, 'F');

    doc.setFont('helvetica', 'bold');
    doc.setFontSize(18);
    doc.setTextColor(...white);
    doc.text('CTST', M, 12);

    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text('Campus Tech Support Ticket System — Performance Report', M, 19);

    doc.setFontSize(9);
    doc.setTextColor(180, 200, 220);
    doc.text('IIE Rosebank College', M, 25);

    const dateStr = new Date().toLocaleDateString('en-ZA', {day:'2-digit', month:'long', year:'numeric'});
    doc.text('Generated: ' + dateStr, pageW - M, 25, { align: 'right' });

    y = 36;

    // ── KPI boxes ────────────────────────────────────────────────────────────
    const kpis = [
        { label: 'Avg. Resolution Time', value: '<?= $avgResolution ?>h', color: navy  },
        { label: 'Resolution Rate',      value: '<?= $resolvedPct ?>%',   color: green },
        { label: 'Currently Open',       value: '<?= $countOpen ?>',      color: amber },
        { label: 'Total Resolved',       value: '<?= $countResolved ?>',  color: navy  },
    ];
    const kW = (pageW - M * 2 - 12) / 4;
    kpis.forEach((kpi, i) => {
        const x = M + i * (kW + 4);
        doc.setFillColor(255, 255, 255);
        doc.roundedRect(x, y, kW, 22, 2, 2, 'F');
        doc.setDrawColor(221, 227, 236);
        doc.setLineWidth(0.3);
        doc.roundedRect(x, y, kW, 22, 2, 2, 'S');
        doc.setFillColor(...kpi.color);
        doc.roundedRect(x, y, kW, 1.5, 0.5, 0.5, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(6);
        doc.setTextColor(...muted);
        doc.text(kpi.label.toUpperCase(), x + 4, y + 7);
        doc.setFontSize(14);
        doc.setTextColor(...kpi.color);
        doc.text(kpi.value, x + 4, y + 17);
    });
    y += 30;

    // ── Category bars ─────────────────────────────────────────────────────────
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.setTextColor(...navy);
    doc.text('TICKETS BY CATEGORY', M, y);
    y += 5;

    <?php foreach ($chartBars as $bar): ?>
    (function() {
        const label  = '<?= addslashes($bar['label']) ?>';
        const pct    = <?= $bar['pct'] ?>;
        const maxW   = pageW - M * 2 - 28;
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(8);
        doc.setTextColor(74, 93, 115);
        doc.text(label, M, y + 4);
        doc.setFillColor(...light);
        doc.roundedRect(M + 22, y, maxW, 6, 1, 1, 'F');
        doc.setFillColor(...navy);
        doc.roundedRect(M + 22, y, maxW * (pct / 100), 6, 1, 1, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(8);
        doc.setTextColor(...navy);
        doc.text(pct + '%', pageW - M + 1, y + 4.5, { align: 'right' });
        y += 10;
    })();
    <?php endforeach; ?>

    y += 6;

    // ── Status boxes ──────────────────────────────────────────────────────────
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.setTextColor(...navy);
    doc.text('STATUS BREAKDOWN', M, y);
    y += 5;

    const statuses = [
        { label: 'Resolved',    count: <?= $countResolved ?>,   color: green },
        { label: 'In Progress', count: <?= $countInProgress ?>, color: amber },
        { label: 'Open',        count: <?= $countOpen ?>,       color: navy  },
    ];
    statuses.forEach((s, i) => {
        const x = M + i * 60;
        doc.setFillColor(255, 255, 255);
        doc.roundedRect(x, y, 54, 14, 2, 2, 'F');
        doc.setDrawColor(221, 227, 236);
        doc.setLineWidth(0.3);
        doc.roundedRect(x, y, 54, 14, 2, 2, 'S');
        doc.setFillColor(...s.color);
        doc.roundedRect(x, y, 54, 1.5, 0.5, 0.5, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(14);
        doc.setTextColor(...s.color);
        doc.text(String(s.count), x + 6, y + 10);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(7);
        doc.setTextColor(...muted);
        doc.text(s.label, x + 20, y + 10);
    });
    y += 22;

    // ── Weekly table ──────────────────────────────────────────────────────────
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.setTextColor(...navy);
    doc.text('WEEKLY BREAKDOWN', M, y);
    y += 4;

    const tableRows = [
        <?php foreach ($weekRows as $r):
            $avg = $r['avg_hours'] !== null ? $r['avg_hours'] . 'h' : '—';
        ?>
        ['<?= addslashes($r['day']) ?>', '<?= (int)$r['opened'] ?>', '<?= (int)$r['resolved'] ?>', '<?= $avg ?>'],
        <?php endforeach; ?>
    ];

    if (tableRows.length === 0) {
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.setTextColor(...muted);
        doc.text('No ticket activity this week.', M, y + 10);
    } else {
        doc.autoTable({
            startY: y,
            head: [['Day', 'Opened', 'Resolved', 'Avg. Time']],
            body: tableRows,
            headStyles: { fillColor: navy, textColor: white, fontSize: 8, fontStyle: 'bold' },
            bodyStyles: { fontSize: 8, textColor: [30, 41, 59] },
            alternateRowStyles: { fillColor: [248, 250, 252] },
            margin: { left: M, right: M },
            styles: { cellPadding: 3, lineColor: [221, 227, 236], lineWidth: 0.2 },
        });
    }

    // ── Footer band ───────────────────────────────────────────────────────────
    doc.setFillColor(...navy);
    doc.rect(0, pageH - 12, pageW, 12, 'F');
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(8);
    doc.setTextColor(180, 200, 220);
    doc.text('CTST — IIE Rosebank College | Confidential', M, pageH - 4.5);
    doc.text('Page 1 of 1', pageW - M, pageH - 4.5, { align: 'right' });

    // Save the file
    doc.save('CTST_Report_' + new Date().toISOString().slice(0, 10) + '.pdf');
}
</script>

<script src="assets/theme.js"></script>
</body>
</html>
