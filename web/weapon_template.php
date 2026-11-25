<?php
if (!defined('BPS_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Plantilla genérica modernizada para mostrar estadísticas por arma - BASIC POINTS STATS
 */
require_once __DIR__ . '/config.php';

function renderWeaponStats($weaponCode, $weaponName = '', $tableName = 'basicweapomstats2') {
    global $bps_tables;
    $catalog    = bps_get_weapons_catalog();
    $weaponCode = strtoupper(trim((string)$weaponCode));

    if (!isset($catalog[$weaponCode])) {
        echo "<div class='placeholder'>" . bps_e(bps_t('weapon_invalid')) . "</div>";
        return;
    }

    $meta       = $catalog[$weaponCode];
    $weaponName = $meta['name'];
    $column     = $meta['col'];
    $tableName  = ($meta['table'] === 'basicweapomstats1') ? $bps_tables['weapon1'] : $bps_tables['weapon2'];

    $q              = isset($_GET['q']) ? bps_clean_query($_GET['q']) : '';
    $selectedServer = isset($_GET['srv']) ? bps_clean_server_id($_GET['srv']) : '';
    $isMotd         = bps_is_motd();
    $backHref       = bps_url(['go' => 'weapom'], $selectedServer);

    $resultados       = [];
    $totalServerKills = 0;

    try {
        $pdo        = getDBConnection();
        $hasSaveKey = bps_table_has_column($pdo, $tableName, 'save_key');
        $hasId      = bps_table_has_column($pdo, $tableName, 'id');
        $hasSrvCol  = bps_table_has_column($pdo, $tableName, 'server_id');
        $idCols     = ($hasId ? 'id, ' : '') . ($hasSaveKey ? 'save_key, ' : '') . ($hasSrvCol ? 'server_id, ' : '');

        $totWhere  = ($selectedServer !== '' && $hasSrvCol) ? 'server_id = :srv' : '1=1';
        $totParams = ($selectedServer !== '' && $hasSrvCol) ? [':srv' => $selectedServer] : [];
        $totStmt   = $pdo->prepare("SELECT COALESCE(SUM(`{$column}`),0) FROM `{$tableName}` WHERE {$totWhere}");
        $totStmt->execute($totParams);
        $totalServerKills = (int)$totStmt->fetchColumn();

        $clauses = ["`{$column}` > 0"];
        $params  = $totParams;
        if ($selectedServer !== '' && $hasSrvCol) {
            $clauses[] = 'server_id = :srv';
        }
        if ($q !== '') {
            $clauses[] = "(nick LIKE :q1 ESCAPE '!' OR authid LIKE :q2 ESCAPE '!')";
            $like = '%' . bps_like_escape($q) . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
        }
        $where = implode(' AND ', $clauses);

        if ($selectedServer === '' && $hasSaveKey && $hasSrvCol) {
            $sql = "SELECT save_key, MAX(authid) AS authid, MAX(nick) AS nick,
                           GROUP_CONCAT(DISTINCT server_id ORDER BY server_id SEPARATOR ', ') AS server_id,
                           SUM(`{$column}`) AS frags
                    FROM `{$tableName}`
                    WHERE {$where}
                    GROUP BY save_key
                    ORDER BY frags DESC
                    LIMIT 25";
        } else {
            $sql = "SELECT {$idCols} authid, nick, `{$column}` AS frags
                    FROM `{$tableName}`
                    WHERE {$where}
                    ORDER BY `{$column}` DESC
                    LIMIT 25";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll();
    } catch (Exception $e) {
        echo "<div class='placeholder'>" . bps_e(bps_t('weapon_error')) . "</div>";
        return;
    }

    $maxFrags = !empty($resultados) ? max(1, (int)$resultados[0]['frags']) : 1;
    $wImg     = bps_weapon_img_src($weaponCode);
?>

<div class="section-header" style="border-bottom:1px solid #242e42; padding-bottom:14px;">
  <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
    <?php if ($wImg !== ''): ?>
      <img src="<?php echo bps_e($wImg); ?>" alt="<?php echo bps_e($weaponName); ?>" style="max-height:38px;" />
    <?php endif; ?>
    <div>
      <h2>TOP 25 &mdash; <?php echo bps_e($weaponName); ?><?php if ($selectedServer !== ''): ?> (<?php echo bps_e($selectedServer); ?>)<?php endif; ?></h2>
      <p><?php echo bps_e(bps_t('weapon_total_kills')); ?> <strong style="color:#00e5ff;"><?php echo number_format($totalServerKills); ?></strong></p>
    </div>
  </div>
  <a href="<?php echo bps_e($backHref); ?>" class="btn btn-ghost"><?php echo bps_e(bps_t('btn_all_weapons')); ?></a>
</div>

<form class="filter-bar" method="get" action="<?php echo $isMotd ? 'top15.php' : ''; ?>">
  <input type="hidden" name="go" value="<?php echo bps_e($weaponCode); ?>" />
  <?php if ($selectedServer !== ''): ?>
    <input type="hidden" name="srv" value="<?php echo bps_e($selectedServer); ?>" />
  <?php endif; ?>
  <?php if ($isMotd || isset($_GET['lang'])): ?>
    <input type="hidden" name="lang" value="<?php echo bps_e(bps_lang()); ?>" />
  <?php endif; ?>
  <input type="text" name="q" maxlength="64" value="<?php echo bps_e($q); ?>" placeholder="<?php echo bps_e(bps_t('weapon_filter_ph', ['w' => $weaponName])); ?>" />
  <button type="submit" class="btn btn-primary"><?php echo bps_e(bps_t('btn_filter')); ?></button>
  <?php if ($q !== ''): ?>
    <a href="<?php echo bps_e(bps_url(['go' => $weaponCode], $selectedServer)); ?>" class="btn btn-ghost"><?php echo bps_e(bps_t('btn_clear')); ?></a>
  <?php endif; ?>
</form>

<div class="table-wrap">
  <table class="bps-table">
    <thead>
      <tr>
        <th style="width:50px;">#</th>
        <th class="left"><?php echo bps_e(bps_t('col_player_steam')); ?></th>
        <th style="width:180px;"><?php echo bps_e(bps_t('col_share')); ?></th>
        <th style="width:150px;"><?php echo bps_e(bps_t('col_weapon_kills', ['w' => $weaponName])); ?></th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($resultados)): ?>
      <tr>
        <td colspan="4" style="padding:28px; color:#5e6e87;"><?php echo bps_e(bps_t('weapon_empty')); ?></td>
      </tr>
    <?php else: ?>
      <?php foreach ($resultados as $index => $row):
          $rNum = $index + 1;
          $rankCell = $rNum <= 3
              ? "<span class=\"rank-badge rank-{$rNum}\">{$rNum}</span>"
              : $rNum;
          $nick   = bps_nick($row['nick'] ?? '');
          $authid = (string)($row['authid'] ?? '');
          $frags  = (int)$row['frags'];
          $barPct = round(($frags / $maxFrags) * 100, 1);
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
        <td>
          <div class="mini-bar" style="width:140px; margin:0 auto;">
            <span style="width:<?php echo $barPct; ?>%;"></span>
          </div>
        </td>
        <td style="color:#00e5ff; font-weight:800; font-size:14px;"><?php echo number_format($frags); ?></td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<?php
}
?>
