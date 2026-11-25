<?php
if (!defined('BPS_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * TOP Ranking General Modernizado - BASIC POINTS STATS
 */
require_once __DIR__ . '/config.php';

$q              = isset($_GET['q']) ? bps_clean_query($_GET['q']) : '';
$selectedServer = isset($selectedServer) ? $selectedServer : (isset($_GET['srv']) ? bps_clean_server_id($_GET['srv']) : '');
$limit          = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
if (!in_array($limit, [15, 25, 50, 100], true)) {
    $limit = 15;
}
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$sort = isset($_GET['sort']) ? strtolower(trim((string)$_GET['sort'])) : 'skillpoints';

$sortMapSingle = [
    'skillpoints' => 'skillpoints DESC, pug_k DESC',
    'kills'       => 'pug_k DESC, skillpoints DESC',
    'kd'          => '(pug_k / GREATEST(pug_d, 1)) DESC, pug_k DESC',
    'hs'          => '(CASE WHEN pug_k > 0 THEN (pug_hsp / pug_k) ELSE 0 END) DESC, pug_k DESC',
    'wins'        => 'pug_win DESC, skillpoints DESC',
    'level'       => 'level DESC, skillpoints DESC',
];
$sortMapGrouped = [
    'skillpoints' => 'skillpoints DESC, pug_k DESC',
    'kills'       => 'pug_k DESC, skillpoints DESC',
    'kd'          => '(SUM(pug_k) / GREATEST(SUM(pug_d), 1)) DESC, SUM(pug_k) DESC',
    'hs'          => '(CASE WHEN SUM(pug_k) > 0 THEN (SUM(pug_hsp) / SUM(pug_k)) ELSE 0 END) DESC, SUM(pug_k) DESC',
    'wins'        => 'pug_win DESC, skillpoints DESC',
    'level'       => 'level DESC, skillpoints DESC',
];
if (!array_key_exists($sort, $sortMapSingle)) {
    $sort = 'skillpoints';
}

$resultados = [];
$totalRows  = 0;
$totalPages = 1;
$offset     = ($page - 1) * $limit;

try {
    $pdo        = getDBConnection();
    $table      = $bps_tables['stats'];
    $hasSaveKey = bps_table_has_column($pdo, $table, 'save_key');
    $hasId      = bps_table_has_column($pdo, $table, 'id');
    $hasSrvCol  = bps_table_has_column($pdo, $table, 'server_id');
    $idCols     = ($hasId ? 'id, ' : '') . ($hasSaveKey ? 'save_key, ' : '') . ($hasSrvCol ? 'server_id, ' : '');

    $clauses = [];
    $params  = [];
    if ($selectedServer !== '' && $hasSrvCol) {
        $clauses[] = 'server_id = :srv';
        $params[':srv'] = $selectedServer;
    }
    if ($q !== '') {
        $clauses[] = "(nick LIKE :q1 ESCAPE '!' OR authid LIKE :q2 ESCAPE '!')";
        $like = '%' . bps_like_escape($q) . '%';
        $params[':q1'] = $like;
        $params[':q2'] = $like;
    }
    $where = !empty($clauses) ? implode(' AND ', $clauses) : '1=1';

    if ($selectedServer === '' && $hasSaveKey && $hasSrvCol) {
        $cStmt = $pdo->prepare("SELECT COUNT(DISTINCT save_key) FROM `{$table}` WHERE {$where}");
        $cStmt->execute($params);
        $totalRows  = (int)$cStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $limit));
        if ($page > $totalPages) {
            $page   = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        $sql = "SELECT save_key, MAX(authid) AS authid, MAX(nick) AS nick,
                       GROUP_CONCAT(DISTINCT server_id ORDER BY server_id SEPARATOR ', ') AS server_id,
                       SUM(skillpoints) AS skillpoints,
                       FLOOR(SUM(skillpoints) / 100) AS level,
                       SUM(pug_k) AS pug_k, SUM(pug_d) AS pug_d, SUM(pug_a) AS pug_a,
                       SUM(pug_bp) AS pug_bp, SUM(pug_bd) AS pug_bd, SUM(pug_hsp) AS pug_hsp,
                       SUM(pug_win) AS pug_win, SUM(pug_los) AS pug_los, SUM(pug_tied) AS pug_tied
                FROM `{$table}`
                WHERE {$where}
                GROUP BY save_key
                ORDER BY {$sortMapGrouped[$sort]}
                LIMIT {$limit} OFFSET {$offset}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll();
    } else {
        $cStmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE {$where}");
        $cStmt->execute($params);
        $totalRows  = (int)$cStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalRows / $limit));
        if ($page > $totalPages) {
            $page   = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        $sql = "SELECT {$idCols} authid, nick, skillpoints, level, pug_k, pug_d, pug_a,
                       pug_bp, pug_bd, pug_hsp, pug_win, pug_los, pug_tied
                FROM `{$table}`
                WHERE {$where}
                ORDER BY {$sortMapSingle[$sort]}
                LIMIT {$limit} OFFSET {$offset}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll();
    }
} catch (Exception $e) {
    echo "<div class='placeholder'>" . bps_e(bps_t('top_error')) . "</div>";
    return;
}
?>

<div class="section-header">
  <div>
    <h2><?php echo bps_e(bps_t('top_title')); ?><?php if ($selectedServer !== ''): ?> &mdash; <?php echo bps_e($selectedServer); ?><?php endif; ?></h2>
    <p><?php echo bps_e(bps_t('top_subtitle', ['n' => number_format($totalRows)])); ?></p>
  </div>
</div>

<form class="filter-bar" method="get" action="">
  <input type="hidden" name="go" value="top" />
  <?php if ($selectedServer !== ''): ?>
    <input type="hidden" name="srv" value="<?php echo bps_e($selectedServer); ?>" />
  <?php endif; ?>
  <?php if (isset($_GET['lang'])): ?>
    <input type="hidden" name="lang" value="<?php echo bps_e(bps_lang()); ?>" />
  <?php endif; ?>
  <input type="text" name="q" maxlength="64" value="<?php echo bps_e($q); ?>" placeholder="<?php echo bps_e(bps_t('top_filter_ph')); ?>" />
  <select name="sort" aria-label="Sort">
    <option value="skillpoints" <?php echo $sort === 'skillpoints' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('sort_points')); ?></option>
    <option value="kills" <?php echo $sort === 'kills' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('sort_kills')); ?></option>
    <option value="kd" <?php echo $sort === 'kd' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('sort_kd')); ?></option>
    <option value="hs" <?php echo $sort === 'hs' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('sort_hs')); ?></option>
    <option value="wins" <?php echo $sort === 'wins' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('sort_wins')); ?></option>
    <option value="level" <?php echo $sort === 'level' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('sort_level')); ?></option>
  </select>
  <select name="limit" aria-label="Limit">
    <option value="15" <?php echo $limit === 15 ? 'selected' : ''; ?>>Top 15</option>
    <option value="25" <?php echo $limit === 25 ? 'selected' : ''; ?>>Top 25</option>
    <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>Top 50</option>
    <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>Top 100</option>
  </select>
  <button type="submit" class="btn btn-primary"><?php echo bps_e(bps_t('btn_apply')); ?></button>
  <?php if ($q !== '' || $sort !== 'skillpoints' || $limit !== 15): ?>
    <a href="<?php echo bps_e(bps_url(['go' => 'top'], $selectedServer)); ?>" class="btn btn-ghost"><?php echo bps_e(bps_t('btn_reset')); ?></a>
  <?php endif; ?>
</form>

<div class="table-wrap">
  <table class="bps-table">
    <thead>
      <tr>
        <th style="width:46px;">#</th>
        <th class="left"><?php echo bps_e(bps_t('col_player_steam')); ?></th>
        <th><?php echo bps_e(bps_t('col_rank_tier')); ?></th>
        <th><?php echo bps_e(bps_t('col_level')); ?></th>
        <th><?php echo bps_e(bps_t('col_points')); ?></th>
        <th>K/D</th>
        <th><?php echo bps_e(bps_t('col_kills')); ?></th>
        <th><?php echo bps_e(bps_t('col_deaths')); ?></th>
        <th><?php echo bps_e(bps_t('col_assists')); ?></th>
        <th><?php echo bps_e(bps_t('col_hs_pct')); ?></th>
        <th><?php echo bps_e(bps_t('col_c4_pd')); ?></th>
        <th><?php echo bps_e(bps_t('col_wlt')); ?></th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($resultados)): ?>
      <tr>
        <td colspan="12" style="padding:28px; color:#5e6e87;"><?php echo bps_e(bps_t('top_empty')); ?></td>
      </tr>
    <?php else: ?>
      <?php foreach ($resultados as $index => $row):
          $rNum = $offset + $index + 1;
          $rankCell = $rNum <= 3
              ? "<span class=\"rank-badge rank-{$rNum}\">{$rNum}</span>"
              : $rNum;
          $nick   = bps_nick($row['nick'] ?? '');
          $authid = (string)($row['authid'] ?? '');
          $kills  = (int)$row['pug_k'];
          $deaths = (int)$row['pug_d'];
          $kd     = bps_kd($kills, $deaths);
          $hsPct  = bps_hs_pct($row['pug_hsp'], $kills);
          $tier   = bps_rank_tier($row['skillpoints']);
          $href   = bps_player_href($row, $selectedServer);
      ?>
      <tr>
        <td><?php echo $rankCell; ?></td>
        <td class="left">
          <div class="player-cell">
            <a class="player-link" href="<?php echo bps_e($href); ?>"><?php echo bps_e($nick); ?></a>
            <div class="steam-sub">
              <span><?php echo bps_e($authid !== '' ? $authid : '-'); ?></span>
              <?php if ($authid !== ''): ?>
                <button type="button" class="copy-mini" data-copy="<?php echo bps_e($authid); ?>"><?php echo bps_e(bps_t('btn_copy')); ?></button>
              <?php endif; ?>
              <?php if ($selectedServer === '' && !empty($row['server_id'])): ?>
                <span class="pill pill-server"><?php echo bps_e($row['server_id']); ?></span>
              <?php endif; ?>
            </div>
          </div>
        </td>
        <td><span class="tier-badge <?php echo bps_e($tier['class']); ?>"><?php echo bps_e($tier['name']); ?></span></td>
        <td><strong><?php echo (int)$row['level']; ?></strong></td>
        <td style="color:#00e5ff; font-weight:800; font-size:14px;"><?php echo number_format((int)$row['skillpoints']); ?></td>
        <td><span class="pill <?php echo (float)$kd >= 1.0 ? 'pill-good' : 'pill-bad'; ?>"><?php echo $kd; ?></span></td>
        <td style="color:#fff; font-weight:700;"><?php echo number_format($kills); ?></td>
        <td><?php echo number_format($deaths); ?></td>
        <td><?php echo number_format((int)$row['pug_a']); ?></td>
        <td>
          <div class="mini-bar-wrap">
            <span><?php echo $hsPct; ?>%</span>
            <div class="mini-bar"><span style="width:<?php echo $hsPct; ?>%;"></span></div>
          </div>
        </td>
        <td><?php echo (int)$row['pug_bp']; ?> / <?php echo (int)$row['pug_bd']; ?></td>
        <td>
          <span style="color:#22c55e; font-weight:700;"><?php echo (int)$row['pug_win']; ?></span>
          /
          <span style="color:#ef4444;"><?php echo (int)$row['pug_los']; ?></span>
          /
          <span><?php echo (int)$row['pug_tied']; ?></span>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
  <?php for ($p = 1; $p <= $totalPages; $p++):
      if ($p > 1 && $p < $totalPages && abs($p - $page) > 3) continue;
      $pUrl = bps_url(['go' => 'top', 'q' => $q, 'sort' => $sort, 'limit' => $limit, 'page' => $p], $selectedServer);
  ?>
    <a class="page-link <?php echo $p === $page ? 'active' : ''; ?>" href="<?php echo bps_e($pUrl); ?>"><?php echo $p; ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
