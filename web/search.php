<?php
if (!defined('BPS_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Buscador de Jugadores por SteamID o Nombre - BASIC POINTS STATS
 */
require_once __DIR__ . '/config.php';

$q              = isset($_GET['q']) ? bps_clean_query($_GET['q']) : '';
$selectedServer = isset($selectedServer) ? $selectedServer : (isset($_GET['srv']) ? bps_clean_server_id($_GET['srv']) : '');
$mode           = isset($_GET['mode']) ? strtolower(trim((string)$_GET['mode'])) : 'all';
if (!in_array($mode, ['all', 'nick', 'authid'], true)) {
    $mode = 'all';
}
$sort = isset($_GET['sort']) ? strtolower(trim((string)$_GET['sort'])) : 'skillpoints';
$allowedSorts = [
    'skillpoints' => 'skillpoints DESC',
    'kills'       => 'pug_k DESC',
    'level'       => 'level DESC, skillpoints DESC',
    'wins'        => 'pug_win DESC',
];
if (!array_key_exists($sort, $allowedSorts)) {
    $sort = 'skillpoints';
}

$resultados = [];
$totalResultados = 0;
$errorMsg = '';

try {
    $pdo        = getDBConnection();
    $table      = $bps_tables['stats'];
    $hasSaveKey = bps_table_has_column($pdo, $table, 'save_key');
    $hasId      = bps_table_has_column($pdo, $table, 'id');
    $hasSrvCol  = bps_table_has_column($pdo, $table, 'server_id');

    $cols = ($hasId ? 'id, ' : '') . ($hasSaveKey ? 'save_key, ' : '') . ($hasSrvCol ? 'server_id, ' : '')
          . 'authid, nick, skillpoints, level, pug_k, pug_d, pug_a, pug_hsp, pug_win, pug_los, pug_tied';

    $clauses = [];
    $params  = [];
    if ($selectedServer !== '' && $hasSrvCol) {
        $clauses[] = 'server_id = :srv';
        $params[':srv'] = $selectedServer;
    }

    if ($q !== '') {
        $like = '%' . bps_like_escape($q) . '%';
        if ($mode === 'nick') {
            $clauses[] = "nick LIKE :q1 ESCAPE '!'";
            $params[':q1'] = $like;
        } elseif ($mode === 'authid') {
            $clauses[] = "authid LIKE :q1 ESCAPE '!'";
            $params[':q1'] = $like;
        } else {
            $clauses[] = "(nick LIKE :q1 ESCAPE '!' OR authid LIKE :q2 ESCAPE '!')";
            $params[':q1'] = $like;
            $params[':q2'] = $like;
        }
    }

    $where = !empty($clauses) ? implode(' AND ', $clauses) : '1=1';
    $rowLimit = ($q !== '') ? 50 : 15;

    if ($selectedServer === '' && $hasSaveKey && $hasSrvCol) {
        if ($q !== '') {
            $countStmt = $pdo->prepare("SELECT COUNT(DISTINCT save_key) FROM `{$table}` WHERE {$where}");
            $countStmt->execute($params);
            $totalResultados = (int)$countStmt->fetchColumn();
        }

        $sql = "SELECT save_key, MAX(authid) AS authid, MAX(nick) AS nick,
                       GROUP_CONCAT(DISTINCT server_id ORDER BY server_id SEPARATOR ', ') AS server_id,
                       SUM(skillpoints) AS skillpoints,
                       FLOOR(SUM(skillpoints) / 100) AS level,
                       SUM(pug_k) AS pug_k, SUM(pug_d) AS pug_d, SUM(pug_a) AS pug_a,
                       SUM(pug_hsp) AS pug_hsp, SUM(pug_win) AS pug_win,
                       SUM(pug_los) AS pug_los, SUM(pug_tied) AS pug_tied
                FROM `{$table}`
                WHERE {$where}
                GROUP BY save_key
                ORDER BY {$allowedSorts[$sort]}
                LIMIT {$rowLimit}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll();
        if ($q === '') {
            $totalResultados = count($resultados);
        }
    } else {
        if ($q !== '') {
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE {$where}");
            $countStmt->execute($params);
            $totalResultados = (int)$countStmt->fetchColumn();
        }
        $sql = "SELECT {$cols} FROM `{$table}` WHERE {$where} ORDER BY {$allowedSorts[$sort]} LIMIT {$rowLimit}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll();
        if ($q === '') {
            $totalResultados = count($resultados);
        }
    }
} catch (Exception $e) {
    $errorMsg = bps_t('search_error');
}
?>

<div class="section-header">
  <div>
    <h2><?php echo bps_e(bps_t('search_title')); ?><?php if ($selectedServer !== ''): ?> &mdash; <?php echo bps_e($selectedServer); ?><?php endif; ?></h2>
    <p><?php echo bps_e(bps_t('search_subtitle')); ?></p>
  </div>
</div>

<form class="filter-bar" method="get" action="">
  <input type="hidden" name="go" value="search" />
  <?php if ($selectedServer !== ''): ?>
    <input type="hidden" name="srv" value="<?php echo bps_e($selectedServer); ?>" />
  <?php endif; ?>
  <?php if (isset($_GET['lang'])): ?>
    <input type="hidden" name="lang" value="<?php echo bps_e(bps_lang()); ?>" />
  <?php endif; ?>
  <input type="text" name="q" maxlength="64" value="<?php echo bps_e($q); ?>" placeholder="<?php echo bps_e(bps_t('search_input_ph')); ?>" autofocus />
  <select name="mode" aria-label="Mode">
    <option value="all" <?php echo $mode === 'all' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('search_mode_all')); ?></option>
    <option value="nick" <?php echo $mode === 'nick' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('search_mode_nick')); ?></option>
    <option value="authid" <?php echo $mode === 'authid' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('search_mode_authid')); ?></option>
  </select>
  <select name="sort" aria-label="Sort">
    <option value="skillpoints" <?php echo $sort === 'skillpoints' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('sort_points')); ?></option>
    <option value="kills" <?php echo $sort === 'kills' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('sort_kills')); ?></option>
    <option value="level" <?php echo $sort === 'level' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('sort_level')); ?></option>
    <option value="wins" <?php echo $sort === 'wins' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('sort_wins')); ?></option>
  </select>
  <button type="submit" class="btn btn-primary"><?php echo bps_e(bps_t('btn_search')); ?></button>
  <?php if ($q !== ''): ?>
    <a href="<?php echo bps_e(bps_url(['go' => 'search'], $selectedServer)); ?>" class="btn btn-ghost"><?php echo bps_e(bps_t('btn_clear')); ?></a>
  <?php endif; ?>
</form>

<?php if ($errorMsg !== ''): ?>
  <div class="placeholder"><?php echo bps_e($errorMsg); ?></div>
<?php else: ?>
  <div style="margin-bottom:12px; font-size:13px; color:#8b9bb4;">
    <?php if ($q !== ''): ?>
      <?php echo bps_e(bps_t('search_showing_query', ['shown' => count($resultados), 'total' => number_format($totalResultados)])); ?>
      <span style="color:#ff9800; font-weight:700;">"<?php echo bps_e($q); ?>"</span>
    <?php else: ?>
      <?php echo bps_e(bps_t('search_showing_top')); ?>
    <?php endif; ?>
  </div>

  <div class="table-wrap">
    <table class="bps-table">
      <thead>
        <tr>
          <th style="width:45px;">#</th>
          <th class="left"><?php echo bps_e(bps_t('col_player_steam')); ?></th>
          <th><?php echo bps_e(bps_t('col_id_type')); ?></th>
          <th><?php echo bps_e(bps_t('col_rank_tier')); ?></th>
          <th><?php echo bps_e(bps_t('col_level')); ?></th>
          <th><?php echo bps_e(bps_t('col_points')); ?></th>
          <th>K/D</th>
          <th><?php echo bps_e(bps_t('col_kills')); ?></th>
          <th><?php echo bps_e(bps_t('col_deaths')); ?></th>
          <th><?php echo bps_e(bps_t('col_hs_pct')); ?></th>
          <th><?php echo bps_e(bps_t('col_wl')); ?></th>
          <th style="width:110px;"><?php echo bps_e(bps_t('col_profile')); ?></th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($resultados)): ?>
        <tr>
          <td colspan="12" style="padding:30px; color:#5e6e87;">
            <?php echo bps_e(bps_t('search_empty')); ?> <strong><?php echo bps_e($q); ?></strong>.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($resultados as $idx => $row):
            $nick     = bps_nick($row['nick'] ?? '');
            $authid   = (string)($row['authid'] ?? '');
            $isSteam  = bps_is_steam($authid);
            $tier     = bps_rank_tier($row['skillpoints'] ?? 0);
            $kd       = bps_kd($row['pug_k'] ?? 0, $row['pug_d'] ?? 0);
            $kdNum    = (float)$kd;
            $hsPct    = bps_hs_pct($row['pug_hsp'] ?? 0, $row['pug_k'] ?? 0);
            $href     = bps_player_href($row, $selectedServer);
            $isByName = !empty($row['save_key']) && strpos($row['save_key'], 'N:') === 0;
        ?>
        <tr>
          <td><?php echo $idx + 1; ?></td>
          <td class="left">
            <div class="player-cell">
              <a class="player-link" href="<?php echo bps_e($href); ?>"><?php echo bps_e($nick); ?></a>
              <div class="steam-sub">
                <span><?php echo bps_e($authid !== '' ? $authid : bps_t('no_steamid')); ?></span>
                <?php if ($authid !== ''): ?>
                  <button type="button" class="copy-mini" data-copy="<?php echo bps_e($authid); ?>"><?php echo bps_e(bps_t('btn_copy')); ?></button>
                <?php endif; ?>
                <?php if ($selectedServer === '' && !empty($row['server_id'])): ?>
                  <span class="pill pill-server"><?php echo bps_e($row['server_id']); ?></span>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td>
            <?php if ($isByName): ?>
              <span class="pill pill-nosteam"><?php echo bps_e(bps_t('mode_name')); ?></span>
            <?php elseif ($isSteam): ?>
              <span class="pill pill-steam"><?php echo bps_e(bps_t('mode_steamid')); ?></span>
            <?php else: ?>
              <span class="pill pill-nosteam"><?php echo bps_e(bps_t('mode_lan_other')); ?></span>
            <?php endif; ?>
          </td>
          <td><span class="tier-badge <?php echo bps_e($tier['class']); ?>"><?php echo bps_e($tier['name']); ?></span></td>
          <td><strong><?php echo (int)($row['level'] ?? 0); ?></strong></td>
          <td style="color:#00e5ff; font-weight:800;"><?php echo number_format((int)($row['skillpoints'] ?? 0)); ?></td>
          <td><span class="pill <?php echo $kdNum >= 1.0 ? 'pill-good' : 'pill-bad'; ?>"><?php echo $kd; ?></span></td>
          <td style="color:#fff; font-weight:700;"><?php echo number_format((int)($row['pug_k'] ?? 0)); ?></td>
          <td><?php echo number_format((int)($row['pug_d'] ?? 0)); ?></td>
          <td><?php echo $hsPct; ?>%</td>
          <td>
            <span style="color:#22c55e; font-weight:700;"><?php echo (int)($row['pug_win'] ?? 0); ?></span>
            /
            <span style="color:#ef4444;"><?php echo (int)($row['pug_los'] ?? 0); ?></span>
          </td>
          <td><a class="btn btn-ghost" href="<?php echo bps_e($href); ?>" style="padding:5px 10px;"><?php echo bps_e(bps_t('btn_view_profile')); ?></a></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
