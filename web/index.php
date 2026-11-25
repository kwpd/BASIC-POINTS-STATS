<?php
define('BPS_ENTRY', true);

require_once __DIR__ . '/config.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; form-action 'self'; frame-ancestors 'self';");

$nav_items = [
    'home'   => bps_t('nav_home'),
    'top'    => bps_t('nav_top'),
    'weapom' => bps_t('nav_weapons'),
    'versus' => bps_t('nav_versus'),
    'search' => bps_t('nav_search'),
];

$weaponsCatalog = bps_get_weapons_catalog();

$go = isset($_GET['go']) ? strtoupper(trim((string)$_GET['go'])) : 'HOME';
if ($go === '' && isset($_GET['q']) && trim((string)$_GET['q']) !== '') {
    $go = 'SEARCH';
}
$globalSearchQuery = isset($_GET['q']) ? bps_clean_query($_GET['q']) : '';
$selectedServer    = isset($_GET['srv']) ? bps_clean_server_id($_GET['srv']) : '';
$currentLang       = bps_lang();
$supportedLangs    = bps_supported_languages();

$serverPartitions = [];
try {
    $pdoNav = getDBConnection();
    $serverPartitions = bps_get_server_partitions($pdoNav);
} catch (Exception $e) {
    $serverPartitions = [];
}

$selectorGo = array_key_exists($go, $weaponsCatalog) ? $go : strtolower($go);
$preservedParams = ['go' => $selectorGo];
foreach (['q', 'sort', 'limit', 'mode', 'key', 'id', 'authid', 'nick'] as $pKey) {
    if (isset($_GET[$pKey]) && trim((string)$_GET[$pKey]) !== '') {
        $preservedParams[$pKey] = bps_clean_query($_GET[$pKey], 64);
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo bps_e($currentLang); ?>">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>BASIC POINTS STATS — CS 1.6</title>
<link rel="stylesheet" href="bps-modern.css" />
</head>

<body>
<div class="panel">
  <div class="panel-header">
    <div class="brand-wrap">
      <div class="brand-row">
        <a class="title" href="<?php echo bps_e(bps_url(['go' => 'home'], $selectedServer)); ?>">BASIC POINTS STATS</a>
        <span class="version-pill">v2.9</span>
      </div>
      <div class="subtitle"><?php echo bps_e(bps_t('site_subtitle')); ?></div>
    </div>

    <div class="header-actions">
      <form class="global-search-form" method="get" action="">
        <input type="hidden" name="go" value="search" />
        <?php if ($selectedServer !== ''): ?>
          <input type="hidden" name="srv" value="<?php echo bps_e($selectedServer); ?>" />
        <?php endif; ?>
        <?php if (isset($_GET['lang'])): ?>
          <input type="hidden" name="lang" value="<?php echo bps_e($currentLang); ?>" />
        <?php endif; ?>
        <input type="text" name="q" maxlength="64" value="<?php echo bps_e($globalSearchQuery); ?>" placeholder="<?php echo bps_e(bps_t('search_placeholder_hdr')); ?>" aria-label="<?php echo bps_e(bps_t('search_aria')); ?>" />
        <button type="submit"><?php echo bps_e(bps_t('btn_search')); ?></button>
      </form>
      <a class="discord-btn" href="<?php echo bps_e($bps_discord_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo bps_e(bps_t('discord_community')); ?></a>
    </div>
  </div>

  <div class="nav-bar">
    <div class="nav-links">
<?php foreach ($nav_items as $key => $label):
    $href = bps_url(['go' => $key], $selectedServer);
    $isWeaponPage = array_key_exists($go, $weaponsCatalog) && $key === 'weapom';
    $isPlayerFromSearch = ($go === 'PLAYER' && $key === 'search');
    $active = (strtolower($go) === strtolower($key) || $isWeaponPage || $isPlayerFromSearch) ? ' active' : '';
?>
      <a class="nav-tab<?php echo $active; ?>" href="<?php echo bps_e($href); ?>"><?php echo bps_e($label); ?></a>
<?php endforeach; ?>
    </div>

    <div class="nav-controls">
<?php if (!empty($serverPartitions) || $selectedServer !== ''): ?>
      <form class="server-select-form" method="get" action="">
        <?php foreach ($preservedParams as $pk => $pv): ?>
          <input type="hidden" name="<?php echo bps_e($pk); ?>" value="<?php echo bps_e($pv); ?>" />
        <?php endforeach; ?>
        <?php if (isset($_GET['lang'])): ?>
          <input type="hidden" name="lang" value="<?php echo bps_e($currentLang); ?>" />
        <?php endif; ?>
        <label for="bpsServerSelect"><?php echo bps_e(bps_t('label_server')); ?></label>
        <select id="bpsServerSelect" name="srv" aria-label="<?php echo bps_e(bps_t('label_server')); ?>">
          <option value="" <?php echo $selectedServer === '' ? 'selected' : ''; ?>><?php echo bps_e(bps_t('all_servers')); ?></option>
          <?php
          $foundSelected = ($selectedServer === '');
          foreach ($serverPartitions as $part):
              $isSel = ($selectedServer === $part['server_id']);
              if ($isSel) {
                  $foundSelected = true;
              }
          ?>
            <option value="<?php echo bps_e($part['server_id']); ?>" <?php echo $isSel ? 'selected' : ''; ?>>
              <?php echo bps_e($part['label']); ?> (<?php echo number_format($part['players']); ?>)
            </option>
          <?php endforeach; ?>
          <?php if (!$foundSelected && $selectedServer !== ''): ?>
            <option value="<?php echo bps_e($selectedServer); ?>" selected><?php echo bps_e($selectedServer); ?></option>
          <?php endif; ?>
        </select>
      </form>
<?php endif; ?>

      <form class="lang-select-form" method="get" action="">
        <?php foreach ($preservedParams as $pk => $pv): ?>
          <input type="hidden" name="<?php echo bps_e($pk); ?>" value="<?php echo bps_e($pv); ?>" />
        <?php endforeach; ?>
        <?php if ($selectedServer !== ''): ?>
          <input type="hidden" name="srv" value="<?php echo bps_e($selectedServer); ?>" />
        <?php endif; ?>
        <label for="bpsLangSelect"><?php echo bps_e(bps_t('label_lang')); ?></label>
        <select id="bpsLangSelect" name="lang" aria-label="<?php echo bps_e(bps_t('label_lang')); ?>">
          <?php foreach ($supportedLangs as $lCode => $lName): ?>
            <option value="<?php echo bps_e($lCode); ?>" <?php echo $currentLang === $lCode ? 'selected' : ''; ?>><?php echo bps_e($lName); ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>

  <div class="panel-body">
<?php
require_once __DIR__ . '/weapon_template.php';

if (array_key_exists($go, $weaponsCatalog)) {
    renderWeaponStats($go, $weaponsCatalog[$go]['name']);
} else {
    switch (strtolower($go)) {
        case 'top':
            include __DIR__ . '/top.php';
            break;
        case 'weapom':
            include __DIR__ . '/weapom.php';
            break;
        case 'versus':
            include __DIR__ . '/Versus.php';
            break;
        case 'search':
            include __DIR__ . '/search.php';
            break;
        case 'player':
            include __DIR__ . '/player.php';
            break;
        case 'home':
        default:
            include __DIR__ . '/home.php';
            break;
    }
}
?>
  </div>

  <div class="panel-footer">
    BASIC POINTS STATS &copy; <?php echo date('Y'); ?> &bull; Counter-Strike 1.6 Statistics System
  </div>
</div>

<div id="bpsToast" class="bps-toast"><?php echo bps_e(bps_t('toast_copied')); ?></div>
<script src="bps-app.js"></script>
</body>
</html>
