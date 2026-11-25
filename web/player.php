<?php
if (!defined('BPS_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Perfil Detallado de Jugador - BASIC POINTS STATS
 */
require_once __DIR__ . '/config.php';

$keyParam       = isset($_GET['key']) ? bps_clean_query($_GET['key'], 64) : '';
$idParam        = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$authidParam    = isset($_GET['authid']) ? bps_clean_query($_GET['authid'], 40) : '';
$nickParam      = isset($_GET['nick']) ? bps_clean_query($_GET['nick'], 32) : '';
$selectedServer = isset($selectedServer) ? $selectedServer : (isset($_GET['srv']) ? bps_clean_server_id($_GET['srv']) : '');
$isMotd         = bps_is_motd();

$player = null;
$weaponsData = [];
$versusData = [];
$globalRank = 1;
$totalPlayers = 0;
$playerSrv = '';
$lastServer = '';

try {
    $pdo     = getDBConnection();
    $tStats  = $bps_tables['stats'];
    $tWep1   = $bps_tables['weapon1'];
    $tWep2   = $bps_tables['weapon2'];
    $tVersus = $bps_tables['versus'];

    $hasSaveKey = bps_table_has_column($pdo, $tStats, 'save_key');
    $hasId      = bps_table_has_column($pdo, $tStats, 'id');
    $hasSrvCol  = bps_table_has_column($pdo, $tStats, 'server_id');

    $srvClause = ($selectedServer !== '' && $hasSrvCol) ? ' AND server_id = :srv' : '';
    $srvBind   = ($selectedServer !== '' && $hasSrvCol) ? [':srv' => $selectedServer] : [];

    // 1. Localizar fila base para conocer save_key / authid
    $seedRow = null;
    if ($keyParam !== '' && $hasSaveKey) {
        $stmt = $pdo->prepare("SELECT * FROM `{$tStats}` WHERE save_key = :v{$srvClause} ORDER BY skillpoints DESC LIMIT 1");
        $stmt->execute(array_merge([':v' => $keyParam], $srvBind));
        $seedRow = $stmt->fetch();
    } elseif ($idParam > 0 && $hasId) {
        $stmt = $pdo->prepare("SELECT * FROM `{$tStats}` WHERE id = :v{$srvClause} LIMIT 1");
        $stmt->execute(array_merge([':v' => $idParam], $srvBind));
        $seedRow = $stmt->fetch();
    } elseif ($authidParam !== '') {
        $stmt = $pdo->prepare("SELECT * FROM `{$tStats}` WHERE authid = :v{$srvClause} ORDER BY skillpoints DESC LIMIT 1");
        $stmt->execute(array_merge([':v' => $authidParam], $srvBind));
        $seedRow = $stmt->fetch();
    } elseif ($nickParam !== '') {
        $stmt = $pdo->prepare("SELECT * FROM `{$tStats}` WHERE nick = :v{$srvClause} ORDER BY skillpoints DESC LIMIT 1");
        $stmt->execute(array_merge([':v' => $nickParam], $srvBind));
        $seedRow = $stmt->fetch();
    }

    if ($seedRow) {
        if ($selectedServer === '' && $hasSaveKey && $hasSrvCol && !empty($seedRow['save_key'])) {
            // Agregar estadísticas de todas las particiones del jugador
            $aggStmt = $pdo->prepare(
                "SELECT save_key, MAX(authid) AS authid, MAX(nick) AS nick,
                        GROUP_CONCAT(DISTINCT server_id ORDER BY server_id SEPARATOR ', ') AS server_id,
                        MAX(last_server) AS last_server, MAX(last_seen) AS last_seen,
                        SUM(skillpoints) AS skillpoints,
                        FLOOR(SUM(skillpoints) / 100) AS level,
                        SUM(pug_k) AS pug_k, SUM(pug_d) AS pug_d, SUM(pug_a) AS pug_a,
                        SUM(pug_bp) AS pug_bp, SUM(pug_bd) AS pug_bd, SUM(pug_adr) AS pug_adr,
                        SUM(pug_hsp) AS pug_hsp, SUM(pug_rws) AS pug_rws,
                        SUM(pug_win) AS pug_win, SUM(pug_los) AS pug_los, SUM(pug_tied) AS pug_tied,
                        SUM(pug_r_los) AS pug_r_los, SUM(pug_rp) AS pug_rp
                 FROM `{$tStats}`
                 WHERE save_key = :sk
                 GROUP BY save_key"
            );
            $aggStmt->execute([':sk' => $seedRow['save_key']]);
            $player = $aggStmt->fetch() ?: $seedRow;

            $rankStmt = $pdo->prepare(
                "SELECT COUNT(*) + 1 FROM (
                    SELECT SUM(skillpoints) AS sp FROM `{$tStats}` GROUP BY save_key HAVING sp > :sp
                 ) AS r"
            );
            $rankStmt->execute([':sp' => (int)$player['skillpoints']]);
            $globalRank = (int)$rankStmt->fetchColumn();

            $totalPlayers = (int)$pdo->query("SELECT COUNT(DISTINCT save_key) FROM `{$tStats}`")->fetchColumn();
            $playerSrv    = !empty($player['server_id']) ? (string)$player['server_id'] : '';
        } else {
            $player    = $seedRow;
            $playerSrv = ($hasSrvCol && !empty($player['server_id'])) ? (string)$player['server_id'] : '';

            if ($playerSrv !== '') {
                $rankStmt = $pdo->prepare("SELECT COUNT(*) + 1 FROM `{$tStats}` WHERE server_id = :srv AND skillpoints > :sp");
                $rankStmt->execute([':srv' => $playerSrv, ':sp' => (int)$player['skillpoints']]);
                $globalRank = (int)$rankStmt->fetchColumn();

                $totStmt = $pdo->prepare("SELECT COUNT(*) FROM `{$tStats}` WHERE server_id = :srv");
                $totStmt->execute([':srv' => $playerSrv]);
                $totalPlayers = (int)$totStmt->fetchColumn();
            } else {
                $rankStmt = $pdo->prepare("SELECT COUNT(*) + 1 FROM `{$tStats}` WHERE skillpoints > :sp");
                $rankStmt->execute([':sp' => (int)$player['skillpoints']]);
                $globalRank = (int)$rankStmt->fetchColumn();

                $totalPlayers = (int)$pdo->query("SELECT COUNT(*) FROM `{$tStats}`")->fetchColumn();
            }
        }

        $lastServer = !empty($player['last_server']) ? (string)$player['last_server'] : '';

        $lookupCol = ($hasSaveKey && !empty($player['save_key'])) ? 'save_key' : 'authid';
        $lookupVal = ($lookupCol === 'save_key') ? $player['save_key'] : $player['authid'];
        $catalog   = bps_get_weapons_catalog();

        $w1Cols = [];
        $w2Cols = [];
        foreach ($catalog as $code => $meta) {
            if ($meta['table'] === 'basicweapomstats1') {
                $w1Cols[] = "COALESCE(SUM(`{$meta['col']}`),0) AS `{$meta['col']}`";
            } else {
                $w2Cols[] = "COALESCE(SUM(`{$meta['col']}`),0) AS `{$meta['col']}`";
            }
        }

        $w1SrvClause = ($selectedServer !== '' && bps_table_has_column($pdo, $tWep1, 'server_id')) ? ' AND server_id = :srv' : '';
        $w1Bind      = ($w1SrvClause !== '') ? [':v' => $lookupVal, ':srv' => $selectedServer] : [':v' => $lookupVal];
        $w1Stmt      = $pdo->prepare("SELECT " . implode(', ', $w1Cols) . " FROM `{$tWep1}` WHERE `{$lookupCol}` = :v{$w1SrvClause}");
        $w1Stmt->execute($w1Bind);
        $w1 = $w1Stmt->fetch() ?: [];

        $w2SrvClause = ($selectedServer !== '' && bps_table_has_column($pdo, $tWep2, 'server_id')) ? ' AND server_id = :srv' : '';
        $w2Bind      = ($w2SrvClause !== '') ? [':v' => $lookupVal, ':srv' => $selectedServer] : [':v' => $lookupVal];
        $w2Stmt      = $pdo->prepare("SELECT " . implode(', ', $w2Cols) . " FROM `{$tWep2}` WHERE `{$lookupCol}` = :v{$w2SrvClause}");
        $w2Stmt->execute($w2Bind);
        $w2 = $w2Stmt->fetch() ?: [];

        $vsSrvClause = ($selectedServer !== '' && bps_table_has_column($pdo, $tVersus, 'server_id')) ? ' AND server_id = :srv' : '';
        $vsBind      = ($vsSrvClause !== '') ? [':v' => $lookupVal, ':srv' => $selectedServer] : [':v' => $lookupVal];
        $vsStmt      = $pdo->prepare(
            "SELECT COALESCE(SUM(pug_2k),0) AS pug_2k, COALESCE(SUM(pug_3k),0) AS pug_3k,
                    COALESCE(SUM(pug_4k),0) AS pug_4k, COALESCE(SUM(pug_5k),0) AS pug_5k,
                    COALESCE(SUM(pug_v1),0) AS pug_v1, COALESCE(SUM(pug_v2),0) AS pug_v2,
                    COALESCE(SUM(pug_v3),0) AS pug_v3, COALESCE(SUM(pug_v4),0) AS pug_v4,
                    COALESCE(SUM(pug_v5),0) AS pug_v5
             FROM `{$tVersus}` WHERE `{$lookupCol}` = :v{$vsSrvClause}"
        );
        $vsStmt->execute($vsBind);
        $versusData = $vsStmt->fetch() ?: [];

        foreach ($catalog as $code => $meta) {
            $src   = ($meta['table'] === 'basicweapomstats1') ? $w1 : $w2;
            $kills = isset($src[$meta['col']]) ? (int)$src[$meta['col']] : 0;
            if ($kills > 0) {
                $weaponsData[] = [
                    'code'  => $code,
                    'name'  => $meta['name'],
                    'cat'   => $meta['cat'],
                    'kills' => $kills,
                ];
            }
        }
        usort($weaponsData, function ($a, $b) {
            return $b['kills'] <=> $a['kills'];
        });
    }
} catch (Exception $e) {
    $player = null;
}

if (!$player): ?>
<div class="placeholder">
  <h3 style="color:#fff; margin-bottom:8px;"><?php echo bps_e(bps_t('player_not_found')); ?></h3>
  <p style="margin-bottom:16px;"><?php echo bps_e(bps_t('player_not_found_desc')); ?></p>
  <a href="<?php echo bps_e(bps_url(['go' => $isMotd ? '' : 'search'], $selectedServer)); ?>" class="btn btn-primary"><?php echo bps_e(bps_t($isMotd ? 'btn_back_ranking' : 'btn_go_search')); ?></a>
</div>
<?php return; endif;

$effectiveSrv = $selectedServer;

$nick     = bps_nick($player['nick'] ?? '');
$authid   = (string)($player['authid'] ?? '');
$points   = (int)($player['skillpoints'] ?? 0);
$level    = (int)($player['level'] ?? 0);
$kills    = (int)($player['pug_k'] ?? 0);
$deaths   = (int)($player['pug_d'] ?? 0);
$assists  = (int)($player['pug_a'] ?? 0);
$bp       = (int)($player['pug_bp'] ?? 0);
$bd       = (int)($player['pug_bd'] ?? 0);
$totalDmg = (int)($player['pug_adr'] ?? 0);
$hsCount  = (int)($player['pug_hsp'] ?? 0);
$rwsRaw   = (int)($player['pug_rws'] ?? 0);
$wins     = (int)($player['pug_win'] ?? 0);
$losses   = (int)($player['pug_los'] ?? 0);
$ties     = (int)($player['pug_tied'] ?? 0);
$rWon     = (int)($player['pug_rp'] ?? 0);
$rLost    = (int)($player['pug_r_los'] ?? 0);
$lastSeen = !empty($player['last_seen']) ? $player['last_seen'] : null;

$kd       = bps_kd($kills, $deaths);
$hsPct    = bps_hs_pct($hsCount, $kills);
$winRate  = bps_winrate($wins, $losses, $ties);
$tier     = bps_rank_tier($points);
$levelPct = max(0, min(100, $points % 100));
$isByName = !empty($player['save_key']) && strpos($player['save_key'], 'N:') === 0;
$isSteam  = bps_is_steam($authid);

$playedRounds = $rWon + $rLost;
$realAdr      = $playedRounds > 0 ? number_format($totalDmg / $playedRounds, 1) : number_format($totalDmg);
$avgRws       = $playedRounds > 0 ? number_format($rwsRaw / $playedRounds, 1) : number_format($rwsRaw);

$totalMatches = max(1, $wins + $losses + $ties);
$winBarPct    = round(($wins / $totalMatches) * 100, 1);
$tieBarPct    = round(($ties / $totalMatches) * 100, 1);
$lossBarPct   = max(0, 100 - $winBarPct - $tieBarPct);

$totalRounds  = max(1, $playedRounds);
$roundWinPct  = round(($rWon / $totalRounds) * 100, 1);

$totalWepKills = array_sum(array_column($weaponsData, 'kills'));
?>
<style type="text/css">
.profile-hero {
  background-color: #151d2e;
  background: linear-gradient(135deg, #192235 0%, #121825 100%);
  border: 1px solid #242e42;
  border-radius: 12px;
  padding: 22px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 18px;
  margin-bottom: 20px;
}
.profile-identity { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.rank-orb {
  width: 68px; height: 68px;
  border-radius: 16px;
  background-color: #202838;
  background: linear-gradient(135deg, rgba(255,152,0,0.22), rgba(0,229,255,0.14));
  border: 1px solid rgba(255, 152, 0, 0.45);
  display: flex; flex-direction: column; align-items: center; justify-content: center;
}
.rank-orb .pos { font-size: 20px; font-weight: 800; color: #ff9800; line-height: 1; }
.rank-orb .of { font-size: 10px; color: #8b9bb4; margin-top: 3px; }
.profile-name-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.profile-name { font-size: 26px; font-weight: 800; color: #fff; }
.profile-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 6px; font-size: 12px; color: #8b9bb4; }

.level-progress-box { min-width: 220px; flex: 0 1 280px; }
.level-top { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px; font-weight: 700; }
.xp-bar { height: 8px; background-color: #080b11; border-radius: 999px; overflow: hidden; border: 1px solid #242e42; }
.xp-bar > span { display: block; height: 100%; background-color: #ff9800; background: linear-gradient(90deg, #ff9800, #00e5ff); }

.split-bar { display: flex; height: 10px; border-radius: 999px; overflow: hidden; background-color: #080b11; margin: 10px 0 8px 0; }
.split-win { background-color: #22c55e; }
.split-tie { background-color: #ff9800; }
.split-loss { background-color: #ef4444; }

.versus-chips {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
  gap: 10px;
}
.v-chip {
  display: inline-block;
  background-color: #080b11;
  border: 1px solid #242e42;
  border-radius: 8px;
  padding: 10px;
  text-align: center;
  min-width: 78px;
}
.v-chip .val { font-size: 20px; font-weight: 800; color: #fff; }
.v-chip .lbl { font-size: 11px; color: #5e6e87; text-transform: uppercase; font-weight: 700; margin-top: 2px; }
.v-chip.highlight .val { color: #ff9800; }
.v-chip.cyan .val { color: #00e5ff; }

.wep-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
  border-bottom: 1px solid #242e42;
}
.wep-row:last-child { border-bottom: none; }
.wep-icon-wrap { width: 90px; display: flex; align-items: center; }
.wep-icon-wrap img { max-width: 68px; height: 22px; object-fit: contain; }
.wep-mid { flex: 1; }
.wep-bar-track { height: 7px; background-color: #080b11; border-radius: 999px; overflow: hidden; margin-top: 4px; }
.wep-bar-fill { height: 100%; background-color: #ff9800; background: linear-gradient(90deg, #ff9800, #00e5ff); }
</style>

<div style="margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
  <a href="<?php echo bps_e(bps_url(['go' => $isMotd ? '' : 'top'], $effectiveSrv)); ?>" class="btn btn-ghost"><?php echo bps_e(bps_t('btn_back_ranking')); ?></a>
  <?php if (!$isMotd): ?>
    <a href="<?php echo bps_e(bps_url(['go' => 'search'], $effectiveSrv)); ?>" class="btn btn-ghost"><?php echo bps_e(bps_t('btn_search_other')); ?></a>
  <?php endif; ?>
</div>

<div class="profile-hero">
  <div class="profile-identity">
    <div class="rank-orb">
      <div class="pos">#<?php echo $globalRank; ?></div>
      <div class="of"><?php echo bps_e(bps_t('player_of_total', ['n' => number_format($totalPlayers)])); ?></div>
    </div>
    <div>
      <div class="profile-name-row">
        <span class="profile-name"><?php echo bps_e($nick); ?></span>
        <span class="tier-badge <?php echo bps_e($tier['class']); ?>"><?php echo bps_e($tier['name']); ?></span>
        <?php if ($isByName): ?>
          <span class="pill pill-nosteam"><?php echo bps_e(bps_t('badge_mode_name')); ?></span>
        <?php elseif ($isSteam): ?>
          <span class="pill pill-steam"><?php echo bps_e(bps_t('badge_mode_steam')); ?></span>
        <?php endif; ?>
        <?php if ($playerSrv !== ''): ?>
          <span class="pill pill-server"><?php echo bps_e(bps_t('label_server') . ': ' . $playerSrv); ?></span>
        <?php endif; ?>
      </div>
      <div class="profile-meta">
        <span>SteamID: <code style="color:#fff;"><?php echo bps_e($authid !== '' ? $authid : 'N/A'); ?></code></span>
        <?php if ($authid !== ''): ?>
          <button type="button" class="copy-mini" data-copy="<?php echo bps_e($authid); ?>"><?php echo bps_e(bps_t('btn_copy_steam')); ?></button>
        <?php endif; ?>
        <?php if ($lastServer !== '' && $lastServer !== $playerSrv): ?>
          <span>&bull; <?php echo bps_e(bps_t('player_last_server')); ?> <strong style="color:#fff;"><?php echo bps_e($lastServer); ?></strong></span>
        <?php endif; ?>
        <?php if ($lastSeen): ?>
          <span>&bull; <?php echo bps_e(bps_t('player_last_seen')); ?> <?php echo bps_e($lastSeen); ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="level-progress-box">
    <div class="level-top">
      <span style="color:#ff9800;"><?php echo bps_e(bps_t('col_level') . ' ' . $level); ?></span>
      <span style="color:#8b9bb4;"><?php echo bps_e(bps_t('player_xp_next', ['pct' => $levelPct, 'next' => $level + 1])); ?></span>
    </div>
    <div class="xp-bar"><span style="width:<?php echo $levelPct; ?>%;"></span></div>
  </div>
</div>

<div class="kpi-grid">
  <div class="kpi-card">
    <div class="num cyan"><?php echo number_format($points); ?></div>
    <div class="label">Skill Points</div>
    <div class="sub"><?php echo bps_e(bps_t('col_level') . ' ' . $level . ' (' . $tier['name'] . ')'); ?></div>
  </div>
  <div class="kpi-card">
    <div class="num <?php echo (float)$kd >= 1.0 ? 'green' : ''; ?>"><?php echo $kd; ?></div>
    <div class="label">Ratio K/D</div>
    <div class="sub"><?php echo number_format($kills); ?> K / <?php echo number_format($deaths); ?> D / <?php echo number_format($assists); ?> A</div>
  </div>
  <div class="kpi-card">
    <div class="num accent"><?php echo $hsPct; ?>%</div>
    <div class="label">Headshots</div>
    <div class="sub"><?php echo bps_e(bps_t('kpi_hs_sub', ['n' => number_format($hsCount)])); ?></div>
  </div>
  <div class="kpi-card">
    <div class="num"><?php echo $realAdr; ?></div>
    <div class="label"><?php echo bps_e(bps_t('player_adr_label')); ?></div>
    <div class="sub"><?php echo bps_e(bps_t('player_adr_sub', ['dmg' => number_format($totalDmg), 'rws' => $avgRws])); ?></div>
  </div>
  <div class="kpi-card">
    <div class="num"><?php echo $bp; ?> / <?php echo $bd; ?></div>
    <div class="label"><?php echo bps_e(bps_t('player_c4_label')); ?></div>
    <div class="sub"><?php echo bps_e(bps_t('player_c4_sub', ['bp' => $bp, 'bd' => $bd])); ?></div>
  </div>
</div>

<div class="two-col-grid">
  <div class="card-box">
    <div class="card-box-title"><span><?php echo bps_e(bps_t('player_matches_box')); ?></span></div>

    <div style="margin-bottom:18px;">
      <div style="display:flex; justify-content:space-between; font-size:13px;">
        <span><?php echo bps_e(bps_t('player_matches_played')); ?> <strong><?php echo ($wins + $losses + $ties); ?></strong></span>
        <span style="color:#22c55e; font-weight:700;"><?php echo bps_e(bps_t('player_winrate')); ?> <?php echo $winRate; ?>%</span>
      </div>
      <div class="split-bar">
        <div class="split-win" style="width:<?php echo $winBarPct; ?>%;"></div>
        <div class="split-tie" style="width:<?php echo $tieBarPct; ?>%;"></div>
        <div class="split-loss" style="width:<?php echo $lossBarPct; ?>%;"></div>
      </div>
      <div style="display:flex; justify-content:space-between; font-size:12px; color:#8b9bb4;">
        <span style="color:#22c55e;"><?php echo bps_e(bps_t('player_wins')); ?> <?php echo $wins; ?></span>
        <span style="color:#ff9800;"><?php echo bps_e(bps_t('player_ties')); ?> <?php echo $ties; ?></span>
        <span style="color:#ef4444;"><?php echo bps_e(bps_t('player_losses')); ?> <?php echo $losses; ?></span>
      </div>
    </div>

    <div>
      <div style="display:flex; justify-content:space-between; font-size:13px;">
        <span><?php echo bps_e(bps_t('player_rounds_total')); ?> <strong><?php echo $playedRounds; ?></strong></span>
        <span style="color:#00e5ff; font-weight:700;"><?php echo bps_e(bps_t('player_effectiveness')); ?> <?php echo $roundWinPct; ?>%</span>
      </div>
      <div class="split-bar">
        <div class="split-win" style="width:<?php echo $roundWinPct; ?>%;"></div>
        <div class="split-loss" style="width:<?php echo max(0, 100 - $roundWinPct); ?>%;"></div>
      </div>
      <div style="display:flex; justify-content:space-between; font-size:12px; color:#8b9bb4;">
        <span style="color:#22c55e;"><?php echo bps_e(bps_t('player_rounds_won')); ?> <?php echo number_format($rWon); ?></span>
        <span style="color:#ef4444;"><?php echo bps_e(bps_t('player_rounds_lost')); ?> <?php echo number_format($rLost); ?></span>
      </div>
    </div>

    <div class="card-box-title" style="margin-top:22px;"><span><?php echo bps_e(bps_t('player_versus_box')); ?></span></div>
    <div class="versus-chips">
      <div class="v-chip"><div class="val"><?php echo (int)($versusData['pug_2k'] ?? 0); ?></div><div class="lbl">2K</div></div>
      <div class="v-chip"><div class="val"><?php echo (int)($versusData['pug_3k'] ?? 0); ?></div><div class="lbl">3K</div></div>
      <div class="v-chip"><div class="val"><?php echo (int)($versusData['pug_4k'] ?? 0); ?></div><div class="lbl">4K</div></div>
      <div class="v-chip highlight"><div class="val"><?php echo (int)($versusData['pug_5k'] ?? 0); ?></div><div class="lbl">5K ACE</div></div>
      <div class="v-chip"><div class="val"><?php echo (int)($versusData['pug_v1'] ?? 0); ?></div><div class="lbl">1v1</div></div>
      <div class="v-chip"><div class="val"><?php echo (int)($versusData['pug_v2'] ?? 0); ?></div><div class="lbl">1v2</div></div>
      <div class="v-chip"><div class="val"><?php echo (int)($versusData['pug_v3'] ?? 0); ?></div><div class="lbl">1v3</div></div>
      <div class="v-chip"><div class="val"><?php echo (int)($versusData['pug_v4'] ?? 0); ?></div><div class="lbl">1v4</div></div>
      <div class="v-chip cyan"><div class="val"><?php echo (int)($versusData['pug_v5'] ?? 0); ?></div><div class="lbl">1v5 PRO</div></div>
    </div>
  </div>

  <div class="card-box">
    <div class="card-box-title">
      <span><?php echo bps_e(bps_t('player_weapons_box')); ?></span>
      <span style="font-size:12px; color:#8b9bb4; text-transform:none;"><?php echo bps_e(bps_t('player_total_kills_sub', ['n' => number_format($totalWepKills)])); ?></span>
    </div>
    <?php if (empty($weaponsData)): ?>
      <div style="text-align:center; padding:30px 10px; color:#5e6e87; font-size:13px;">
        <?php echo bps_e(bps_t('player_no_weapons')); ?>
      </div>
    <?php else: ?>
      <?php $maxWep = max(1, $weaponsData[0]['kills']); ?>
      <?php foreach ($weaponsData as $w):
          $pct   = round(($w['kills'] / $maxWep) * 100, 1);
          $share = $totalWepKills > 0 ? round(($w['kills'] / $totalWepKills) * 100, 1) : 0;
          $wImg  = bps_weapon_img_src($w['code']);
      ?>
      <div class="wep-row">
        <div class="wep-icon-wrap">
          <?php if ($wImg !== ''): ?>
            <img src="<?php echo bps_e($wImg); ?>" alt="<?php echo bps_e($w['name']); ?>" />
          <?php endif; ?>
        </div>
        <div class="wep-mid">
          <div style="display:flex; justify-content:space-between; font-size:12px;">
            <a href="<?php echo bps_e(bps_url(['go' => $w['code']], $effectiveSrv)); ?>" style="color:#fff; font-weight:700; text-decoration:none;"><?php echo bps_e($w['name']); ?></a>
            <span style="color:#5e6e87;"><?php echo $share; ?>%</span>
          </div>
          <div class="wep-bar-track"><div class="wep-bar-fill" style="width:<?php echo $pct; ?>%;"></div></div>
        </div>
        <div style="width:65px; text-align:right; color:#00e5ff; font-weight:800; font-size:13px;">
          <?php echo number_format($w['kills']); ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
