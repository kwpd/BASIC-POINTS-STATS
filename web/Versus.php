<?php
if (!defined('BPS_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Estadísticas Versus y Multikills Modernizado - BASIC POINTS STATS
 */
require_once __DIR__ . '/config.php';

$q              = isset($_GET['q']) ? bps_clean_query($_GET['q']) : '';
$selectedServer = isset($selectedServer) ? $selectedServer : (isset($_GET['srv']) ? bps_clean_server_id($_GET['srv']) : '');
$isMotd         = bps_is_motd();
$resultados     = [];
$aceLeader      = null;
$clutchLeader   = null;

try {
    $pdo        = getDBConnection();
    $table      = $bps_tables['versus'];
    $hasSaveKey = bps_table_has_column($pdo, $table, 'save_key');
    $hasId      = bps_table_has_column($pdo, $table, 'id');
    $hasSrvCol  = bps_table_has_column($pdo, $table, 'server_id');
    $idCols     = ($hasId ? 'id, ' : '') . ($hasSaveKey ? 'save_key, ' : '') . ($hasSrvCol ? 'server_id, ' : '');

    $baseClauses = [];
    $baseParams  = [];
    if ($selectedServer !== '' && $hasSrvCol) {
        $baseClauses[] = 'server_id = :srv';
        $baseParams[':srv'] = $selectedServer;
    }
    $baseWhere = !empty($baseClauses) ? implode(' AND ', $baseClauses) : '1=1';

    if ($selectedServer === '' && $hasSaveKey && $hasSrvCol) {
        $aceStmt = $pdo->query(
            "SELECT save_key, MAX(authid) AS authid, MAX(nick) AS nick,
                    SUM(pug_5k) AS pug_5k, SUM(pug_4k) AS pug_4k
             FROM `{$table}`
             GROUP BY save_key
             HAVING pug_5k > 0
             ORDER BY pug_5k DESC, pug_4k DESC
             LIMIT 1"
        );
        $aceLeader = $aceStmt->fetch() ?: null;

        $clStmt = $pdo->query(
            "SELECT save_key, MAX(authid) AS authid, MAX(nick) AS nick,
                    SUM(pug_v5) AS pug_v5, SUM(pug_v4) AS pug_v4,
                    SUM(pug_v1 + pug_v2 + pug_v3 + pug_v4 + pug_v5) AS total_clutches
             FROM `{$table}`
             GROUP BY save_key
             HAVING total_clutches > 0
             ORDER BY total_clutches DESC, pug_v5 DESC
             LIMIT 1"
        );
        $clutchLeader = $clStmt->fetch() ?: null;
    } else {
        $aceStmt = $pdo->prepare("SELECT {$idCols} authid, nick, pug_5k, pug_4k FROM `{$table}` WHERE {$baseWhere} AND pug_5k > 0 ORDER BY pug_5k DESC, pug_4k DESC LIMIT 1");
        $aceStmt->execute($baseParams);
        $aceLeader = $aceStmt->fetch() ?: null;

        $clStmt = $pdo->prepare("SELECT {$idCols} authid, nick, pug_v5, pug_v4, (pug_v1+pug_v2+pug_v3+pug_v4+pug_v5) AS total_clutches FROM `{$table}` WHERE {$baseWhere} AND (pug_v1+pug_v2+pug_v3+pug_v4+pug_v5) > 0 ORDER BY total_clutches DESC, pug_v5 DESC LIMIT 1");
        $clStmt->execute($baseParams);
        $clutchLeader = $clStmt->fetch() ?: null;
    }

    $clauses = $baseClauses;
    $params  = $baseParams;
    if ($q !== '') {
        $clauses[] = "(nick LIKE :q1 ESCAPE '!' OR authid LIKE :q2 ESCAPE '!')";
        $like = '%' . bps_like_escape($q) . '%';
        $params[':q1'] = $like;
        $params[':q2'] = $like;
    }
    if ($q === '') {
        $clauses[] = '(pug_2k + pug_3k + pug_4k + pug_5k + pug_v1 + pug_v2 + pug_v3 + pug_v4 + pug_v5) > 0';
    }
    $where = !empty($clauses) ? implode(' AND ', $clauses) : '1=1';

    if ($selectedServer === '' && $hasSaveKey && $hasSrvCol) {
        $sql = "SELECT save_key, MAX(authid) AS authid, MAX(nick) AS nick,
                       GROUP_CONCAT(DISTINCT server_id ORDER BY server_id SEPARATOR ', ') AS server_id,
                       SUM(pug_2k) AS pug_2k, SUM(pug_3k) AS pug_3k, SUM(pug_4k) AS pug_4k, SUM(pug_5k) AS pug_5k,
                       SUM(pug_v1) AS pug_v1, SUM(pug_v2) AS pug_v2, SUM(pug_v3) AS pug_v3, SUM(pug_v4) AS pug_v4, SUM(pug_v5) AS pug_v5
                FROM `{$table}`
                WHERE {$where}
                GROUP BY save_key
                ORDER BY pug_5k DESC, pug_v5 DESC, pug_4k DESC, pug_3k DESC
                LIMIT 25";
    } else {
        $sql = "SELECT {$idCols} authid, nick, pug_2k, pug_3k, pug_4k, pug_5k,
                       pug_v1, pug_v2, pug_v3, pug_v4, pug_v5
                FROM `{$table}`
                WHERE {$where}
                ORDER BY pug_5k DESC, pug_v5 DESC, pug_4k DESC, pug_3k DESC
                LIMIT 25";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll();
} catch (Exception $e) {
    echo "<div class='placeholder'>" . bps_e(bps_t('versus_error')) . "</div>";
    return;
}
?>

<div class="section-header">
  <div>
    <h2><?php echo bps_e(bps_t('versus_title')); ?><?php if ($selectedServer !== ''): ?> &mdash; <?php echo bps_e($selectedServer); ?><?php endif; ?></h2>
    <p><?php echo bps_e(bps_t('versus_subtitle')); ?></p>
  </div>
</div>

<?php if ($aceLeader || $clutchLeader): ?>
<div class="kpi-grid" style="margin-bottom:18px;">
  <?php if ($aceLeader): ?>
  <div class="kpi-card">
    <div class="num accent"><?php echo number_format((int)$aceLeader['pug_5k']); ?> ACEs</div>
    <div class="label"><?php echo bps_e(bps_t('versus_ace_leader')); ?></div>
    <div class="sub">
      <a href="<?php echo bps_e(bps_player_href($aceLeader, $selectedServer)); ?>" style="color:#fff; text-decoration:none; font-weight:700;">
        <?php echo bps_e(bps_nick($aceLeader['nick'])); ?>
      </a>
      (<?php echo bps_e($aceLeader['authid']); ?>)
    </div>
  </div>
  <?php endif; ?>

  <?php if ($clutchLeader): ?>
  <div class="kpi-card">
    <div class="num cyan"><?php echo number_format((int)$clutchLeader['total_clutches']); ?> Clutches</div>
    <div class="label"><?php echo bps_e(bps_t('versus_clutch_leader')); ?></div>
    <div class="sub">
      <a href="<?php echo bps_e(bps_player_href($clutchLeader, $selectedServer)); ?>" style="color:#fff; text-decoration:none; font-weight:700;">
        <?php echo bps_e(bps_nick($clutchLeader['nick'])); ?>
      </a>
      &bull; <?php echo bps_e(bps_t('versus_1v5_wins', ['n' => (int)$clutchLeader['pug_v5']])); ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<form class="filter-bar" method="get" action="<?php echo $isMotd ? 'top15.php' : ''; ?>">
  <input type="hidden" name="go" value="versus" />
  <?php if ($selectedServer !== ''): ?>
    <input type="hidden" name="srv" value="<?php echo bps_e($selectedServer); ?>" />
  <?php endif; ?>
  <?php if ($isMotd || isset($_GET['lang'])): ?>
    <input type="hidden" name="lang" value="<?php echo bps_e(bps_lang()); ?>" />
  <?php endif; ?>
  <input type="text" name="q" maxlength="64" value="<?php echo bps_e($q); ?>" placeholder="<?php echo bps_e(bps_t('versus_filter_ph')); ?>" />
  <button type="submit" class="btn btn-primary"><?php echo bps_e(bps_t('btn_filter')); ?></button>
  <?php if ($q !== ''): ?>
    <a href="<?php echo bps_e(bps_url(['go' => 'versus'], $selectedServer)); ?>" class="btn btn-ghost"><?php echo bps_e(bps_t('btn_clear')); ?></a>
  <?php endif; ?>
</form>

<div class="table-wrap">
  <table class="bps-table">
    <thead>
      <tr>
        <th style="width:45px;">#</th>
        <th class="left"><?php echo bps_e(bps_t('col_player_steam')); ?></th>
        <th>5K (ACE)</th>
        <th>4K</th>
        <th>3K</th>
        <th>2K</th>
        <th>1 vs 1</th>
        <th>1 vs 2</th>
        <th>1 vs 3</th>
        <th>1 vs 4</th>
        <th>1 vs 5 (PRO)</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($resultados)): ?>
      <tr>
        <td colspan="11" style="padding:28px; color:#5e6e87;"><?php echo bps_e(bps_t('versus_empty')); ?></td>
      </tr>
    <?php else: ?>
      <?php foreach ($resultados as $index => $row):
          $rNum = $index + 1;
          $rankCell = $rNum <= 3
              ? "<span class=\"rank-badge rank-{$rNum}\">{$rNum}</span>"
              : $rNum;
          $nick   = bps_nick($row['nick'] ?? '');
          $authid = (string)($row['authid'] ?? '');
          $href   = bps_player_href($row, $selectedServer);
      ?>
      <tr>
        <td><?php echo $rankCell; ?></td>
        <td class="left">
          <div class="player-cell">
            <a class="player-link" href="<?php echo bps_e($href); ?>"><?php echo bps_e($nick); ?></a>
            <span class="steam-sub">
              <?php echo bps_e($authid !== '' ? $authid : '-'); ?>
              <?php if ($selectedServer === '' && !empty($row['server_id'])): ?>
                <span class="pill pill-server"><?php echo bps_e($row['server_id']); ?></span>
              <?php endif; ?>
            </span>
          </div>
        </td>
        <td style="color:#ff9800; font-weight:800; font-size:14px;"><?php echo number_format((int)$row['pug_5k']); ?></td>
        <td style="color:#fff; font-weight:700;"><?php echo number_format((int)$row['pug_4k']); ?></td>
        <td><?php echo number_format((int)$row['pug_3k']); ?></td>
        <td><?php echo number_format((int)$row['pug_2k']); ?></td>
        <td><?php echo number_format((int)$row['pug_v1']); ?></td>
        <td><?php echo number_format((int)$row['pug_v2']); ?></td>
        <td><?php echo number_format((int)$row['pug_v3']); ?></td>
        <td><?php echo number_format((int)$row['pug_v4']); ?></td>
        <td style="color:#00e5ff; font-weight:800;"><?php echo number_format((int)$row['pug_v5']); ?></td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>
