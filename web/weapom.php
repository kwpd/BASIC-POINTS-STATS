<?php
if (!defined('BPS_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Selector de Armas Modernizado - BASIC POINTS STATS
 */
require_once __DIR__ . '/config.php';

$selectedServer = isset($selectedServer) ? $selectedServer : (isset($_GET['srv']) ? bps_clean_server_id($_GET['srv']) : '');

$catalog = bps_get_weapons_catalog();
$weaponsGrouped = [];
foreach ($catalog as $code => $meta) {
    $weaponsGrouped[$meta['cat']][] = [
        'code' => $code,
        'name' => $meta['name'],
    ];
}

$weaponTotals = [];
$dbError = false;
try {
    $pdo = getDBConnection();
    $sums1 = [];
    $sums2 = [];
    foreach ($catalog as $code => $meta) {
        if ($meta['table'] === 'basicweapomstats1') {
            $sums1[] = "COALESCE(SUM(`{$meta['col']}`),0) AS `{$code}`";
        } else {
            $sums2[] = "COALESCE(SUM(`{$meta['col']}`),0) AS `{$code}`";
        }
    }
    $w1HasSrv = bps_table_has_column($pdo, $bps_tables['weapon1'], 'server_id');
    $w2HasSrv = bps_table_has_column($pdo, $bps_tables['weapon2'], 'server_id');

    $s1 = $pdo->prepare("SELECT " . implode(', ', $sums1) . " FROM `{$bps_tables['weapon1']}` WHERE " . (($selectedServer !== '' && $w1HasSrv) ? 'server_id = :srv' : '1=1'));
    $s1->execute(($selectedServer !== '' && $w1HasSrv) ? [':srv' => $selectedServer] : []);
    $r1 = $s1->fetch() ?: [];

    $s2 = $pdo->prepare("SELECT " . implode(', ', $sums2) . " FROM `{$bps_tables['weapon2']}` WHERE " . (($selectedServer !== '' && $w2HasSrv) ? 'server_id = :srv' : '1=1'));
    $s2->execute(($selectedServer !== '' && $w2HasSrv) ? [':srv' => $selectedServer] : []);
    $r2 = $s2->fetch() ?: [];

    $weaponTotals = array_merge($r1, $r2);
} catch (Exception $e) {
    $weaponTotals = [];
    $dbError = true;
}
?>
<style type="text/css">
.weapon-category-title {
  color: #ff9800;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  margin: 22px 0 10px 0;
  padding-bottom: 6px;
  border-bottom: 1px solid #242e42;
}
.weapon-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}
.weapon-card {
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 162px;
  flex: 1 1 155px;
  max-width: 215px;
  background-color: #151c2a;
  border: 1px solid #242e42;
  border-radius: 10px;
  padding: 14px 10px;
  margin: 0 6px 8px 0;
  text-align: center;
  text-decoration: none;
  color: #ccc;
  vertical-align: top;
  transition: all 0.2s ease;
}
.weapon-card:hover {
  border-color: #ff9800;
  background-color: #1c2436;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 152, 0, 0.14);
}
.weapon-card img {
  max-width: 110px;
  height: 36px;
  object-fit: contain;
}
.weapon-card .w-name { display: block; font-size: 13px; font-weight: 700; color: #fff; }
.weapon-card:hover .w-name { color: #ff9800; }
.weapon-card .w-kills { display: block; font-size: 11px; color: #00e5ff; font-weight: 700; }
</style>

<?php if ($dbError): ?>
<div class="placeholder" style="margin-bottom:18px;"><?php echo bps_e(bps_t('db_conn_error')); ?></div>
<?php endif; ?>

<div class="section-header">
  <div>
    <h2><?php echo bps_e(bps_t('weapons_title')); ?><?php if ($selectedServer !== ''): ?> &mdash; <?php echo bps_e($selectedServer); ?><?php endif; ?></h2>
    <p><?php echo bps_e(bps_t('weapons_subtitle')); ?></p>
  </div>
  <input type="text" id="weaponQuickFilter" placeholder="<?php echo bps_e(bps_t('weapons_quick_filter')); ?>"
         style="background-color:#080b11; border:1px solid #242e42; color:#fff; padding:8px 12px; border-radius:6px; font-size:13px; min-width:230px; outline:none;" />
</div>

<div id="weaponCatalogRoot">
<?php foreach ($weaponsGrouped as $categoryName => $weapons): ?>
  <div class="weapon-category-block">
    <div class="weapon-category-title"><?php echo bps_e($categoryName); ?></div>
    <div class="weapon-grid">
    <?php foreach ($weapons as $w):
        $totalW = isset($weaponTotals[$w['code']]) ? (int)$weaponTotals[$w['code']] : 0;
        $wHref  = bps_url(['go' => $w['code']], $selectedServer);
        $wImg   = bps_weapon_img_src($w['code']);
    ?>
      <a href="<?php echo bps_e($wHref); ?>" class="weapon-card" data-wname="<?php echo bps_e(strtolower($w['name'] . ' ' . $w['code'])); ?>">
        <?php if ($wImg !== ''): ?>
          <img src="<?php echo bps_e($wImg); ?>" alt="<?php echo bps_e($w['name']); ?>" />
        <?php endif; ?>
        <span class="w-name"><?php echo bps_e($w['name']); ?></span>
        <span class="w-kills"><?php echo bps_e(bps_t('weapons_kills_count', ['n' => number_format($totalW)])); ?></span>
      </a>
    <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>
