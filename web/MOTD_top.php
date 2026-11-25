<?php
if (!defined('BPS_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Vista MOTD TOP 15 - BASIC POINTS STATS
 */
require_once __DIR__ . '/config.php';

$selectedServer = isset($selectedServer) ? $selectedServer : (isset($_GET['srv']) ? bps_clean_server_id($_GET['srv']) : '');

try {
    $pdo = getDBConnection();
    $table = $bps_tables['stats'];
    $hasSaveKey = bps_table_has_column($pdo, $table, 'save_key');
    $hasSrvCol  = bps_table_has_column($pdo, $table, 'server_id');

    if ($selectedServer === '' && $hasSaveKey && $hasSrvCol) {
        $sql = "SELECT save_key, MAX(authid) AS authid, MAX(nick) AS nick,
                       SUM(skillpoints) AS skillpoints,
                       FLOOR(SUM(skillpoints) / 100) AS level,
                       SUM(pug_k) AS pug_k, SUM(pug_d) AS pug_d, SUM(pug_a) AS pug_a,
                       SUM(pug_hsp) AS pug_hsp, SUM(pug_bp) AS pug_bp, SUM(pug_bd) AS pug_bd,
                       SUM(pug_rws) AS pug_rws, SUM(pug_rp) AS pug_rp, SUM(pug_r_los) AS pug_r_los,
                       SUM(pug_win) AS pug_win, SUM(pug_los) AS pug_los
                FROM `{$table}`
                GROUP BY save_key
                ORDER BY skillpoints DESC
                LIMIT 15";
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll();
    } else {
        $where  = ($selectedServer !== '' && $hasSrvCol) ? 'WHERE server_id = :srv' : '';
        $params = ($selectedServer !== '' && $hasSrvCol) ? [':srv' => $selectedServer] : [];
        $keyCol = $hasSaveKey ? 'save_key, ' : '';

        $sql = "SELECT {$keyCol} authid, nick, skillpoints, level, pug_k, pug_d, pug_a, pug_hsp, pug_bp,
                       pug_bd, pug_rws, pug_rp, pug_r_los, pug_win, pug_los
                FROM `{$table}`
                {$where}
                ORDER BY skillpoints DESC
                LIMIT 15";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll();
    }
} catch (Exception $e) {
    echo "<div style='color:#888; text-align:center; padding:20px;'>" . bps_e(bps_t('motd_error')) . "</div>";
    return;
}
?>
<style type="text/css">
table.ranking { width:100%; border-collapse:collapse; font-size:13px; font-family:"Segoe UI", Tahoma, sans-serif; }
table.ranking thead th {
	background-color:#1c212b;
	color:#ff9900;
	text-transform:uppercase;
	font-size:10px;
	letter-spacing:.5px;
	padding:9px 6px;
	border-bottom:2px solid #ff9900;
	text-align:center;
}
table.ranking thead th img { width:20px; height:20px; vertical-align:middle; }
table.ranking td {
	padding:7px 6px;
	border-bottom:1px solid #232830;
	text-align:center;
	color:#bbb;
}
table.ranking tbody tr:nth-child(even) { background-color:#171b22; }
table.ranking tbody tr:hover { background-color:#202632; }
table.ranking td.name { text-align:left; color:#eee; font-weight:bold; }
table.ranking td.name a { color:#eee; text-decoration:none; }
table.ranking td.name a:hover { color:#ff9800; text-decoration:underline; }
table.ranking td.score { color:#06dcf8; font-weight:bold; }
table.ranking td.kills { color:#fff; }
table.ranking .badge {
	display:inline-block;
	width:20px;
	height:20px;
	line-height:20px;
	border-radius:50%;
	font-weight:bold;
	font-size:11px;
	color:#111;
}
table.ranking .badge-1 { background-color:#ffd700; }
table.ranking .badge-2 { background-color:#c0c0c0; }
table.ranking .badge-3 { background-color:#cd7f32; }
</style>

<table class="ranking">
  <thead>
    <tr>
      <th>#</th>
      <th style="text-align:left;"><?php echo bps_e(bps_t('col_name')); ?></th>
      <th><?php echo bps_e(bps_t('col_points')); ?></th>
      <th><?php echo bps_e(bps_t('col_kills')); ?></th>
      <th>K/D</th>
      <th><?php echo bps_e(bps_t('col_assists')); ?></th>
      <th><img src="imagenpanagamers/balaenlacabeza.jpg" alt="HS" title="Headshots" /></th>
      <th><img src="imagenpanagamers/C4.jpg" alt="C4" title="C4" /></th>
      <th><img src="imagenpanagamers/Defuse.jpg" alt="Defuse" title="Defuse" /></th>
      <th>RWS</th>
      <th><?php echo bps_e(bps_t('col_wl')); ?></th>
    </tr>
  </thead>
  <tbody>
<?php if (empty($resultados)): ?>
    <tr>
      <td colspan="11" style="text-align:center; padding:20px; color:#777;"><?php echo bps_e(bps_t('motd_empty')); ?></td>
    </tr>
<?php else: ?>
<?php foreach ($resultados as $index => $row):
    $rNum = $index + 1;
    $rankCell = $rNum <= 3
        ? "<span class=\"badge badge-{$rNum}\">{$rNum}</span>"
        : $rNum;
    $nick = bps_nick($row['nick'] ?? '');
    $kills = (int)$row['pug_k'];
    $deaths = (int)$row['pug_d'];
    $hsPercent = bps_hs_pct($row['pug_hsp'], $kills);
    $kd = bps_kd($kills, $deaths);
    $roundsTot = (int)($row['pug_rp'] ?? 0) + (int)($row['pug_r_los'] ?? 0);
    $rwsDisp = $roundsTot > 0 ? number_format(((int)$row['pug_rws']) / $roundsTot, 1) : (int)$row['pug_rws'];
    $pHref = bps_player_href($row, $selectedServer, 'top15.php');
?>
    <tr>
      <td><?php echo $rankCell; ?></td>
      <td class="name"><a href="<?php echo bps_e($pHref); ?>"><?php echo bps_e($nick); ?></a></td>
      <td class="score"><?php echo number_format((int)$row['skillpoints']); ?></td>
      <td class="kills"><?php echo number_format($kills); ?></td>
      <td><?php echo $kd; ?></td>
      <td><?php echo (int)$row['pug_a']; ?></td>
      <td><?php echo $hsPercent; ?>%</td>
      <td><?php echo (int)$row['pug_bp']; ?></td>
      <td><?php echo (int)$row['pug_bd']; ?></td>
      <td><?php echo $rwsDisp; ?></td>
      <td>
        <span style="color:#28a745;"><?php echo (int)$row['pug_win']; ?></span> /
        <span style="color:#dc3545;"><?php echo (int)$row['pug_los']; ?></span>
      </td>
    </tr>
<?php endforeach; ?>
<?php endif; ?>
  </tbody>
</table>
