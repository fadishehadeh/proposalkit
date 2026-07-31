<?php
declare(strict_types=1);

function dashboard_index(): void
{
    // Counts + annual value by status in one pass
    $byStatus = db_all('
        SELECT pr.status,
               COUNT(DISTINCT pr.id) AS cnt,
               COALESCE(SUM(pi.monthly_salary * pr.multiplier * pi.allocation * 12), 0) AS annual_value
        FROM proposals pr
        LEFT JOIN proposal_items pi ON pi.proposal_id = pr.id
        GROUP BY pr.status
    ');

    $stats = ['draft' => 0, 'sent' => 0, 'approved' => 0, 'rejected' => 0];
    $values = ['draft' => 0.0, 'sent' => 0.0, 'approved' => 0.0, 'rejected' => 0.0];
    foreach ($byStatus as $row) {
        $stats[$row['status']]  = (int) $row['cnt'];
        $values[$row['status']] = (float) $row['annual_value'];
    }

    $total    = array_sum($stats);
    $closed   = $stats['approved'] + $stats['rejected'];
    $winRate  = $closed > 0 ? round($stats['approved'] / $closed * 100) : null;

    // Recent 8 proposals
    $recent = db_all('
        SELECT pr.*, c.name AS company_name, c.logo_path AS company_logo,
               COALESCE(SUM(pi.monthly_salary * pr.multiplier * pi.allocation * 12), 0) AS annual_value
        FROM proposals pr
        LEFT JOIN companies c ON c.id = pr.company_id
        LEFT JOIN proposal_items pi ON pi.proposal_id = pr.id
        GROUP BY pr.id
        ORDER BY pr.created_at DESC
        LIMIT 8
    ');

    // Top clients by proposal count
    $topClients = db_all('
        SELECT cl.name, COUNT(pr.id) AS cnt,
               COALESCE(SUM(pi.monthly_salary * pr.multiplier * pi.allocation * 12), 0) AS annual_value
        FROM clients cl
        JOIN proposals pr ON pr.client_id = cl.id
        LEFT JOIN proposal_items pi ON pi.proposal_id = pr.id
        GROUP BY cl.id
        ORDER BY cnt DESC
        LIMIT 5
    ');

    layout('dashboard.index', 'Dashboard', compact(
        'stats', 'values', 'total', 'winRate', 'recent', 'topClients'
    ));
}
