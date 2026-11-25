<?php
if (!defined('BPS_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Página Principal / Dashboard Modernizado - BASIC POINTS STATS
 */
require_once __DIR__ . '/config.php';

$selectedServer = isset($selectedServer) ? $selectedServer : (isset($_GET['srv']) ? bps_clean_server_id($_GET['srv']) : '');

$totalPlayers = 0;
$totalKills = 0;
$totalHeadshots = 0;
$totalBombPlants = 0;
$totalBombDefuses = 0;
$totalAces = 0;
$totalClutches = 0;
$topPlayers = [];
$topWeapons = [];
$mvpPoints = null;
$mvpKills = null;
$mvpAce = null;
$registeredServers = [];
$registeredById = [];
$hasStatsServerCol = false;
$dbError = false;

try {
    $pdo = getDBConnection();
    $tStats  = $bps_tables['stats'];
    $tWep1   = $bps_tables['weapon1'];
    $tWep2   = $bps_tables['weapon2'];
    $tVersus = $bps_tables['versus'];

    $hasSaveKey        = bps_table_has_column($pdo, $tStats, 'save_key');
    $hasId             = bps_table_has_column($pdo, $tStats, 'id');
    $hasStatsServerCol = bps_table_has_column($pdo, $tStats, 'server_id');
    $hasLastServer     = bps_table_has_column($pdo, $tStats, 'last_server');
    $idCols = ($hasId ? 'id, ' : '') . ($hasSaveKey ? 'save_key, ' : '') . ($hasStatsServerCol ? 'server_id, ' : '') . ($hasLastServer ? 'last_server, ' : '');

    $registeredServers = bps_get_registered_servers($pdo);
    foreach ($registeredServers as $srv) {
        $registeredById[$srv['server_id']] = $srv;
    }

    $statsWhere  = '1=1';
    $statsParams = [];
    if ($selectedServer !== '' && $hasStatsServerCol) {
        $statsWhere = 'server_id = :srv';
        $statsParams[':srv'] = $selectedServer;
    }

    $countPlayersExpr = ($selectedServer === '' && $hasSaveKey) ? 'COUNT(DISTINCT save_key)' : 'COUNT(*)';
    $stmt = $pdo->prepare(
        "SELECT {$countPlayersExpr} AS tp,
                COALESCE(SUM(pug_k),0) AS tk,
                COALESCE(SUM(pug_hsp),0) AS ths,
                COALESCE(SUM(pug_bp),0) AS tbp,
                COALESCE(SUM(pug_bd),0) AS tbd
         FROM `{$tStats}` WHERE {$statsWhere}"
    );
    $stmt->execute($statsParams);
    if ($row = $stmt->fetch()) {
        $totalPlayers     = (int)$row['tp'];
        $totalKills       = (int)$row['tk'];
        $totalHeadshots   = (int)$row['ths'];
        $totalBombPlants  = (int)$row['tbp'];
        $totalBombDefuses = (int)$row['tbd'];
    }

    $vHasKey = bps_table_has_column($pdo, $tVersus, 'save_key');
    $vHasId  = bps_table_has_column($pdo, $tVersus, 'id');
    $vHasSrv = bps_table_has_column($pdo, $tVersus, 'server_id');
    $vIdCols = ($vHasId ? 'id, ' : '') . ($vHasKey ? 'save_key, ' : '') . ($vHasSrv ? 'server_id, ' : '');
    $vWhere  = ($selectedServer !== '' && $vHasSrv) ? 'server_id = :srv' : '1=1';
    $vParams = ($selectedServer !== '' && $vHasSrv) ? [':srv' => $selectedServer] : [];

    $vStmt = $pdo->prepare("SELECT COALESCE(SUM(pug_5k),0) AS aces, COALESCE(SUM(pug_v1+pug_v2+pug_v3+pug_v4+pug_v5),0) AS clutches FROM `{$tVersus}` WHERE {$vWhere}");
    $vStmt->execute($vParams);
    if ($vRow = $vStmt->fetch()) {
        $totalAces     = (int)$vRow['aces'];
        $totalClutches = (int)$vRow['clutches'];
    }

    if ($selectedServer === '' && $hasSaveKey && $hasStatsServerCol) {
        $tpStmt = $pdo->query(
            "SELECT save_key, MAX(authid) AS authid, MAX(nick) AS nick,
                    GROUP_CONCAT(DISTINCT server_id ORDER BY server_id SEPARATOR ', ') AS server_id,
                    SUM(skillpoints) AS skillpoints,
                    FLOOR(SUM(skillpoints) / 100) AS level,
                    SUM(pug_k) AS pug_k, SUM(pug_d) AS pug_d, SUM(pug_hsp) AS pug_hsp
             FROM `{$tStats}`
             GROUP BY save_key
             ORDER BY skillpoints DESC, pug_k DESC
             LIMIT 5"
        );
        $topPlayers = $tpStmt->fetchAll();
        $mvpPoints  = $topPlayers[0] ?? null;

        $mkStmt = $pdo->query(
            "SELECT save_key, MAX(authid) AS authid, MAX(nick) AS nick,
                    SUM(pug_k) AS pug_k, SUM(pug_d) AS pug_d
             FROM `{$tStats}`
             GROUP BY save_key
             ORDER BY pug_k DESC
             LIMIT 1"
        );
        $mvpKills = $mkStmt->fetch() ?: null;
    } else {
        $tpStmt = $pdo->prepare(
            "SELECT {$idCols} authid, nick, skillpoints, level, pug_k, pug_d, pug_hsp
             FROM `{$tStats}` WHERE {$statsWhere} ORDER BY skillpoints DESC LIMIT 5"
        );
        $tpStmt->execute($statsParams);
        $topPlayers = $tpStmt->fetchAll();
        $mvpPoints  = $topPlayers[0] ?? null;

        $mkStmt = $pdo->prepare("SELECT {$idCols} authid, nick, pug_k, pug_d FROM `{$tStats}` WHERE {$statsWhere} ORDER BY pug_k DESC LIMIT 1");
        $mkStmt->execute($statsParams);
        $mvpKills = $mkStmt->fetch() ?: null;
    }

    if ($selectedServer === '' && $vHasKey && $vHasSrv) {
        $maStmt = $pdo->query(
            "SELECT save_key, MAX(authid) AS authid, MAX(nick) AS nick,
                    SUM(pug_5k) AS pug_5k, SUM(pug_4k) AS pug_4k, SUM(pug_v5) AS pug_v5
             FROM `{$tVersus}`
             GROUP BY save_key
             ORDER BY pug_5k DESC, pug_4k DESC
             LIMIT 1"
        );
        $mvpAce = $maStmt->fetch() ?: null;
    } else {
        $maStmt = $pdo->prepare("SELECT {$vIdCols} authid, nick, pug_5k, pug_v5 FROM `{$tVersus}` WHERE {$vWhere} ORDER BY pug_5k DESC, pug_4k DESC LIMIT 1");
        $maStmt->execute($vParams);
        $mvpAce = $maStmt->fetch() ?: null;
    }

    $catalog = bps_get_weapons_catalog();
    $sums1 = [];
    $sums2 = [];
    foreach ($catalog as $code => $meta) {
        if ($meta['table'] === 'basicweapomstats1') {
            $sums1[] = "COALESCE(SUM(`{$meta['col']}`),0) AS `{$code}`";
        } else {
            $sums2[] = "COALESCE(SUM(`{$meta['col']}`),0) AS `{$code}`";
        }
    }

    $w1HasSrv = bps_table_has_column($pdo, $tWep1, 'server_id');
    $w2HasSrv = bps_table_has_column($pdo, $tWep2, 'server_id');

    $w1Stmt = $pdo->prepare("SELECT " . implode(', ', $sums1) . " FROM `{$tWep1}` WHERE " . (($selectedServer !== '' && $w1HasSrv) ? 'server_id = :srv' : '1=1'));
    $w1Stmt->execute(($selectedServer !== '' && $w1HasSrv) ? [':srv' => $selectedServer] : []);
    $wTotals1 = $w1Stmt->fetch() ?: [];

    $w2Stmt = $pdo->prepare("SELECT " . implode(', ', $sums2) . " FROM `{$tWep2}` WHERE " . (($selectedServer !== '' && $w2HasSrv) ? 'server_id = :srv' : '1=1'));
    $w2Stmt->execute(($selectedServer !== '' && $w2HasSrv) ? [':srv' => $selectedServer] : []);
    $wTotals2 = $w2Stmt->fetch() ?: [];

    $mergedTotals = array_merge($wTotals1, $wTotals2);

    foreach ($catalog as $code => $meta) {
        $cnt = isset($mergedTotals[$code]) ? (int)$mergedTotals[$code] : 0;
        if ($cnt > 0) {
            $topWeapons[] = ['code' => $code, 'name' => $meta['name'], 'kills' => $cnt];
        }
    }
    usort($topWeapons, function ($a, $b) { return $b['kills'] <=> $a['kills']; });
    $topWeapons = array_slice($topWeapons, 0, 5);
} catch (Exception $e) {
    $dbError = true;
}

$globalHsPct = bps_hs_pct($totalHeadshots, $totalKills);
?>
<style type="text/css">
.hero-banner {
  background-color: #161e2e;
  background: linear-gradient(135deg, rgba(255,152,0,0.12) 0%, rgba(0,229,255,0.06) 100%);
  border: 1px solid rgba(255, 152, 0, 0.45);
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 22px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 18px;
}
.hero-banner h2 { color: #fff; font-size: 22px; font-weight: 800; margin-bottom: 6px; }
.hero-banner h2 span { color: #ff9800; }
.hero-banner p { color: #8b9bb4; font-size: 13.5px; max-width: 620px; }

.hero-search {
  display: flex;
  gap: 8px;
  flex: 1;
  min-width: 260px;
  max-width: 440px;
}
.hero-search input {
  flex: 1;
  background-color: #080b11;
  border: 1px solid #242e42;
  color: #fff;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
  outline: none;
}
.hero-search input:focus { border-color: #ff9800; }

.mvp-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 14px;
  margin-bottom: 22px;
}
.mvp-card {
  background-color: #151c2a;
  border: 1px solid #242e42;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.mvp-tag { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; color: #ff9800; margin-bottom: 4px; }
.mvp-name { font-size: 16px; font-weight: 800; color: #fff; text-decoration: none; }
.mvp-name:hover { color: #ff9800; }
.mvp-stat { font-size: 20px; font-weight: 800; color: #00e5ff; text-align: right; }
.mvp-stat small { display: block; font-size: 11px; color: #5e6e87; font-weight: 600; }

.top-wep-item {
  display: flex; align-items: center; gap: 12px; padding: 9px 0; border-bottom: 1px solid #242e42;
}
.top-wep-item:last-child { border-bottom: none; }
.top-wep-item img { width: 64px; height: 22px; object-fit: contain; }
.top-wep-bar { flex: 1; }
.top-wep-bar-bg { height: 6px; background-color: #080b11; border-radius: 999px; overflow: hidden; margin-top: 5px; }
.top-wep-bar-bg > span { display: block; height: 100%; background-color: #ff9800; background: linear-gradient(90deg, #ff9800, #00e5ff); }
</style>

<?php if ($dbError): ?>
<div class="placeholder" style="margin-bottom:18px;"><?php echo bps_e(bps_t('db_conn_error')); ?></div>
<?php endif; ?>

<div class="hero-banner">
  <div>
    <h2><?php echo bps_e(bps_t('home_hero_title')); ?> <span>Counter-Strike 1.6</span><?php if ($selectedServer !== ''): ?> &mdash; <span style="color:#00e5ff;"><?php echo bps_e(bps_server_label($selectedServer, $registeredById)); ?></span><?php endif; ?></h2>
    <p><?php echo bps_e(bps_t('home_hero_desc')); ?></p>
  </div>
  <form class="hero-search" method="get" action="">
    <input type="hidden" name="go" value="search" />
    <?php if ($selectedServer !== ''): ?>
      <input type="hidden" name="srv" value="<?php echo bps_e($selectedServer); ?>" />
    <?php endif; ?>
    <?php if (isset($_GET['lang'])): ?>
      <input type="hidden" name="lang" value="<?php echo bps_e(bps_lang()); ?>" />
    <?php endif; ?>
    <input type="text" name="q" maxlength="64" placeholder="<?php echo bps_e(bps_t('home_search_ph')); ?>" />
    <button type="submit" class="btn btn-primary"><?php echo bps_e(bps_t('btn_search')); ?></button>
  </form>
</div>

<div class="kpi-grid">
  <div class="kpi-card">
    <div class="num accent"><?php echo number_format($totalPlayers); ?></div>
    <div class="label"><?php echo bps_e(bps_t('kpi_players')); ?></div>
    <div class="sub"><?php echo bps_e(bps_t('kpi_players_sub')); ?></div>
  </div>
  <div class="kpi-card">
    <div class="num"><?php echo number_format($totalKills); ?></div>
    <div class="label"><?php echo bps_e(bps_t('kpi_kills')); ?></div>
    <div class="sub"><?php echo $selectedServer !== '' ? bps_e($selectedServer) : bps_e(bps_t('all_servers')); ?></div>
  </div>
  <div class="kpi-card">
    <div class="num cyan"><?php echo $globalHsPct; ?>%</div>
    <div class="label"><?php echo bps_e(bps_t('kpi_hs')); ?></div>
    <div class="sub"><?php echo bps_e(bps_t('kpi_hs_sub', ['n' => number_format($totalHeadshots)])); ?></div>
  </div>
  <div class="kpi-card">
    <div class="num green"><?php echo number_format($totalBombPlants); ?></div>
    <div class="label"><?php echo bps_e(bps_t('kpi_bombs')); ?></div>
    <div class="sub"><?php echo bps_e(bps_t('kpi_bombs_sub', ['n' => number_format($totalBombDefuses)])); ?></div>
  </div>
  <div class="kpi-card">
    <div class="num"><?php echo number_format($totalAces); ?> / <?php echo number_format($totalClutches); ?></div>
    <div class="label"><?php echo bps_e(bps_t('kpi_aces_clutches')); ?></div>
    <div class="sub"><?php echo bps_e(bps_t('kpi_aces_sub')); ?></div>
  </div>
</div>

<?php if ($mvpPoints || $mvpKills || $mvpAce): ?>
<div class="mvp-grid">
  <?php if ($mvpPoints): ?>
  <div class="mvp-card">
    <div>
      <div class="mvp-tag"><?php echo bps_e(bps_t('mvp_points')); ?></div>
      <a class="mvp-name" href="<?php echo bps_e(bps_player_href($mvpPoints, $selectedServer)); ?>"><?php echo bps_e(bps_nick($mvpPoints['nick'])); ?></a>
      <div style="font-size:11px; color:#5e6e87; margin-top:2px;"><?php echo bps_e($mvpPoints['authid']); ?></div>
    </div>
    <div class="mvp-stat"><?php echo number_format((int)$mvpPoints['skillpoints']); ?><small><?php echo bps_e(bps_t('mvp_points_unit')); ?></small></div>
  </div>
  <?php endif; ?>

  <?php if ($mvpKills): ?>
  <div class="mvp-card">
    <div>
      <div class="mvp-tag"><?php echo bps_e(bps_t('mvp_kills')); ?></div>
      <a class="mvp-name" href="<?php echo bps_e(bps_player_href($mvpKills, $selectedServer)); ?>"><?php echo bps_e(bps_nick($mvpKills['nick'])); ?></a>
      <div style="font-size:11px; color:#5e6e87; margin-top:2px;">K/D: <?php echo bps_kd($mvpKills['pug_k'], $mvpKills['pug_d']); ?></div>
    </div>
    <div class="mvp-stat" style="color:#22c55e;"><?php echo number_format((int)$mvpKills['pug_k']); ?><small><?php echo bps_e(bps_t('mvp_kills_unit')); ?></small></div>
  </div>
  <?php endif; ?>

  <?php if ($mvpAce && ((int)$mvpAce['pug_5k'] > 0 || (int)$mvpAce['pug_v5'] > 0)): ?>
  <div class="mvp-card">
    <div>
      <div class="mvp-tag"><?php echo bps_e(bps_t('mvp_ace')); ?></div>
      <a class="mvp-name" href="<?php echo bps_e(bps_player_href($mvpAce, $selectedServer)); ?>"><?php echo bps_e(bps_nick($mvpAce['nick'])); ?></a>
      <div style="font-size:11px; color:#5e6e87; margin-top:2px;">1v5 PRO: <?php echo (int)$mvpAce['pug_v5']; ?></div>
    </div>
    <div class="mvp-stat" style="color:#ff9800;"><?php echo number_format((int)$mvpAce['pug_5k']); ?><small>ACEs (5K)</small></div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="two-col-grid">
  <div class="card-box">
    <div class="card-box-title">
      <span><?php echo bps_e(bps_t('home_top5_title')); ?></span>
      <a href="<?php echo bps_e(bps_url(['go' => 'top'], $selectedServer)); ?>"><?php echo bps_e(bps_t('home_view_full_table')); ?></a>
    </div>
    <table class="bps-table">
      <thead>
        <tr>
          <th style="width:36px;">#</th>
          <th class="left"><?php echo bps_e(bps_t('col_player')); ?></th>
          <th><?php echo bps_e(bps_t('col_points')); ?></th>
          <th>K/D</th>
          <th><?php echo bps_e(bps_t('col_hs_pct')); ?></th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($topPlayers)): ?>
        <tr><td colspan="5" style="padding:24px; color:#5e6e87;"><?php echo bps_e(bps_t('home_no_players')); ?></td></tr>
      <?php else: ?>
        <?php foreach ($topPlayers as $i => $p):
            $pos = $i + 1;
            $badge = $pos <= 3 ? "<span class='rank-badge rank-{$pos}'>{$pos}</span>" : $pos;
        ?>
        <tr>
          <td><?php echo $badge; ?></td>
          <td class="left">
            <div class="player-cell">
              <a class="player-link" href="<?php echo bps_e(bps_player_href($p, $selectedServer)); ?>"><?php echo bps_e(bps_nick($p['nick'])); ?></a>
              <span class="steam-sub">
                <?php echo bps_e($p['authid']); ?>
                <?php if ($selectedServer === '' && !empty($p['server_id'])): ?>
                  <span class="pill pill-server"><?php echo bps_e($p['server_id']); ?></span>
                <?php endif; ?>
              </span>
            </div>
          </td>
          <td style="color:#00e5ff; font-weight:800;"><?php echo number_format((int)$p['skillpoints']); ?></td>
          <td><?php echo bps_kd($p['pug_k'], $p['pug_d']); ?></td>
          <td><?php echo bps_hs_pct($p['pug_hsp'], $p['pug_k']); ?>%</td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card-box">
    <div class="card-box-title">
      <span><?php echo bps_e(bps_t('home_deadliest_weapons')); ?></span>
      <a href="<?php echo bps_e(bps_url(['go' => 'weapom'], $selectedServer)); ?>"><?php echo bps_e(bps_t('home_view_all_weapons')); ?></a>
    </div>
    <?php if (empty($topWeapons)): ?>
      <div style="text-align:center; padding:30px 10px; color:#5e6e87; font-size:13px;"><?php echo bps_e(bps_t('home_no_weapon_kills')); ?></div>
    <?php else: ?>
      <?php $maxW = max(1, $topWeapons[0]['kills']); ?>
      <?php foreach ($topWeapons as $w):
          $wPct = round(($w['kills'] / $maxW) * 100, 1);
          $wImg = bps_weapon_img_src($w['code']);
      ?>
      <div class="top-wep-item">
        <?php if ($wImg !== ''): ?>
          <img src="<?php echo bps_e($wImg); ?>" alt="<?php echo bps_e($w['name']); ?>" />
        <?php endif; ?>
        <div class="top-wep-bar">
          <div style="display:flex; justify-content:space-between; font-size:13px;">
            <a href="<?php echo bps_e(bps_url(['go' => $w['code']], $selectedServer)); ?>" style="color:#fff; font-weight:700; text-decoration:none;"><?php echo bps_e($w['name']); ?></a>
            <strong style="color:#00e5ff;"><?php echo number_format($w['kills']); ?></strong>
          </div>
          <div class="top-wep-bar-bg"><span style="width:<?php echo $wPct; ?>%;"></span></div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($registeredServers)): ?>
<div class="card-box" style="margin-top:20px;">
  <div class="card-box-title">
    <span><?php echo bps_e(bps_t('home_servers_title')); ?></span>
    <span style="font-size:12px; color:#8b9bb4; text-transform:none;"><?php echo bps_e(bps_t('home_servers_count', ['n' => count($registeredServers)])); ?></span>
  </div>
  <div class="server-grid" style="margin-top:0;">
  <?php foreach ($registeredServers as $srv):
      $sid = (string)$srv['server_id'];
      $sName = trim((string)$srv['server_name']) !== '' ? $srv['server_name'] : $sid;
      $isMultiDedicated = ((int)$srv['multi_mode'] === 1);
      $targetPartition = $isMultiDedicated ? $sid : 'global';
      $isActiveSrv = ($selectedServer === $targetPartition);
      $secsAgo = isset($srv['seconds_ago']) && $srv['seconds_ago'] !== null ? (int)$srv['seconds_ago'] : null;
      $isRecent = ($secsAgo !== null && $secsAgo >= -120 && $secsAgo <= 1800);
  ?>
    <div class="server-card<?php echo $isActiveSrv ? ' active-srv' : ''; ?>">
      <div class="server-card-head">
        <span class="server-card-name"><?php echo bps_e($sName); ?></span>
        <?php if ($isRecent): ?>
          <span class="pill pill-online"><?php echo bps_e(bps_t('srv_online')); ?></span>
        <?php else: ?>
          <span class="pill pill-nosteam"><?php echo bps_e(bps_t('srv_registered')); ?></span>
        <?php endif; ?>
      </div>
      <div class="server-card-meta">
        <span class="pill pill-server">ID: <?php echo bps_e($sid); ?></span>
        <span class="pill pill-cyan"><?php echo bps_e($isMultiDedicated ? bps_t('per_server') : bps_t('global_shared')); ?></span>
        <span class="pill pill-steam"><?php echo bps_e(bps_t('srv_save_prefix') . ' ' . bps_format_save_mode($srv['save_mode'])); ?></span>
      </div>
      <div class="server-card-foot">
        <span><?php echo bps_e(bps_t('srv_last_pulse')); ?> <?php echo bps_e($srv['last_heartbeat'] ?: '-'); ?></span>
        <a href="<?php echo bps_e(bps_url(['go' => 'top'], $targetPartition)); ?>" style="color:#ff9800; font-weight:700; text-decoration:none;"><?php echo bps_e(bps_t('srv_view_ranking')); ?></a>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
