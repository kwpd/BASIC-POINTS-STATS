<?php
define('BPS_ENTRY', true);

require_once __DIR__ . '/config.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; form-action 'self';");

$nav_items = [
    ''       => bps_t('nav_ranking'),
    'weapom' => bps_t('nav_weapons'),
    'versus' => bps_t('nav_versus_short'),
];

$weaponsCatalog = bps_get_weapons_catalog();
$go             = isset($_GET['go']) ? strtoupper(trim((string)$_GET['go'])) : '';
$selectedServer = isset($_GET['srv']) ? bps_clean_server_id($_GET['srv']) : '';
$currentLang    = bps_lang();
$supportedLangs = bps_supported_languages();
?>
<!DOCTYPE html>
<html lang="<?php echo bps_e($currentLang); ?>">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>BASIC POINTS STATS — MOTD</title>
<link rel="stylesheet" href="bps-modern.css" />
<style type="text/css">
body { background:#000; padding:12px 0; }
.panel { max-width:1000px; min-width:720px; background-color:#0f141e; border:1px solid #242e42; }
.panel-header table { width:100%; border-collapse:collapse; }
.panel-header td { vertical-align:middle; }
.panel-header .logo { text-align:right; }
.panel-header .logo img { max-width:155px; height:auto; }
</style>
</head>

<body>
<div class="panel">
  <div class="panel-header" style="display:block;">
    <table>
      <tr>
        <td>
          <div class="title">BASIC POINTS STATS</div>
          <div class="subtitle">
            <?php echo bps_e(bps_t('motd_subtitle')); ?>
            <?php if ($selectedServer !== ''): ?>
              &bull; <?php echo bps_e(bps_t('label_server')); ?>: <strong style="color:#ff9800;"><?php echo bps_e($selectedServer); ?></strong>
            <?php endif; ?>
          </div>
          <a class="discord-btn" href="<?php echo bps_e($bps_discord_url); ?>" target="_blank" rel="noopener noreferrer" style="margin-top:8px;"><?php echo bps_e(bps_t('discord_community')); ?></a>
        </td>
        <td class="logo">
          <?php if (file_exists(__DIR__ . '/images/site_logo.png')): ?>
            <img src="images/site_logo.png" alt="BASIC POINTS STATS" />
          <?php endif; ?>
        </td>
      </tr>
    </table>
  </div>

  <div class="nav-bar">
    <div class="nav-links">
<?php foreach ($nav_items as $key => $label):
    $params = ($key === '') ? [] : ['go' => $key];
    $href = bps_url($params, $selectedServer, 'top15.php');
    $isWep = ($key === 'weapom' && array_key_exists($go, $weaponsCatalog));
    $active = (strtolower($go) === strtolower($key) || $isWep) ? ' active' : '';
?>
      <a class="nav-tab<?php echo $active; ?>" href="<?php echo bps_e($href); ?>"><?php echo bps_e($label); ?></a>
<?php endforeach; ?>
    </div>

    <form class="lang-select-form" method="get" action="top15.php">
      <?php if ($go !== ''): ?>
        <input type="hidden" name="go" value="<?php echo bps_e($go); ?>" />
      <?php endif; ?>
      <?php if ($selectedServer !== ''): ?>
        <input type="hidden" name="srv" value="<?php echo bps_e($selectedServer); ?>" />
      <?php endif; ?>
      <?php foreach (['key', 'id', 'authid', 'nick', 'q'] as $pk):
          if (!empty($_GET[$pk])): ?>
        <input type="hidden" name="<?php echo bps_e($pk); ?>" value="<?php echo bps_e(bps_clean_query($_GET[$pk], 64)); ?>" />
      <?php endif; endforeach; ?>
      <select id="bpsLangSelect" name="lang" aria-label="<?php echo bps_e(bps_t('label_lang')); ?>">
        <?php foreach ($supportedLangs as $lCode => $lName): ?>
          <option value="<?php echo bps_e($lCode); ?>" <?php echo $currentLang === $lCode ? 'selected' : ''; ?>><?php echo bps_e($lName); ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <div class="panel-body">
<?php
require_once __DIR__ . '/weapon_template.php';

if ($go === '') {
    include __DIR__ . '/MOTD_top.php';
} elseif (array_key_exists($go, $weaponsCatalog)) {
    renderWeaponStats($go, $weaponsCatalog[$go]['name']);
} else {
    switch (strtolower($go)) {
        case 'weapom':
            include __DIR__ . '/weapom.php';
            break;
        case 'versus':
            include __DIR__ . '/Versus.php';
            break;
        case 'player':
            include __DIR__ . '/player.php';
            break;
        default:
            include __DIR__ . '/MOTD_top.php';
            break;
    }
}
?>
  </div>
  <div class="panel-footer">Copyright &copy; <?php echo date('Y'); ?> BASIC POINTS STATS</div>
</div>
<div id="bpsToast" class="bps-toast"><?php echo bps_e(bps_t('toast_copied')); ?></div>
<script src="bps-app.js"></script>
</body>
</html>
