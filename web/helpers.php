<?php
if (!defined('BPS_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}

/**
 * Funciones auxiliares para BASIC POINTS STATS Web
 */

if (!function_exists('bps_e')) {
    function bps_e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('bps_is_motd')) {
    function bps_is_motd() {
        return (isset($_SERVER['PHP_SELF']) && basename((string)$_SERVER['PHP_SELF']) === 'top15.php');
    }
}

if (!function_exists('bps_clean_query')) {
    function bps_clean_query($value, $maxLen = 64) {
        $value = trim((string)$value);
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maxLen, 'UTF-8');
        }
        return substr($value, 0, $maxLen);
    }
}

if (!function_exists('bps_like_escape')) {
    function bps_like_escape($value) {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], (string)$value);
    }
}

if (!function_exists('bps_nick')) {
    function bps_nick($nick, $maxLen = 12) {
        $nick = trim((string)$nick);
        if ($nick === '') {
            return bps_t('unnamed');
        }
        if (function_exists('mb_substr')) {
            return mb_substr($nick, 0, $maxLen, 'UTF-8');
        }
        return substr($nick, 0, $maxLen);
    }
}

if (!function_exists('bps_is_steam')) {
    function bps_is_steam($authid) {
        $authid = strtoupper(trim((string)$authid));
        if ($authid === '' || in_array($authid, ['STEAM_ID_LAN', 'VALVE_ID_LAN', 'STEAM_ID_PENDING', 'VALVE_ID_PENDING', 'BOT', 'HLTV'], true)) {
            return false;
        }
        return (strpos($authid, 'STEAM_') === 0 || strpos($authid, 'VALVE_') === 0);
    }
}

if (!function_exists('bps_kd')) {
    function bps_kd($kills, $deaths) {
        $k = (int)$kills;
        $d = (int)$deaths;
        if ($d <= 0) {
            return number_format((float)$k, 2);
        }
        return number_format($k / $d, 2);
    }
}

if (!function_exists('bps_hs_pct')) {
    function bps_hs_pct($hs, $kills) {
        $k = (int)$kills;
        $h = (int)$hs;
        if ($k <= 0) {
            return 0.0;
        }
        return min(100.0, round(($h / $k) * 100, 1));
    }
}

if (!function_exists('bps_winrate')) {
    function bps_winrate($win, $los, $tied = 0) {
        $total = (int)$win + (int)$los + (int)$tied;
        if ($total <= 0) {
            return 0.0;
        }
        return round(((int)$win / $total) * 100, 1);
    }
}

if (!function_exists('bps_rank_tier')) {
    function bps_rank_tier($points) {
        $p = (int)$points;
        if ($p >= 5000) return ['name' => bps_t('tier_global'),  'class' => 'tier-global'];
        if ($p >= 3000) return ['name' => bps_t('tier_supreme'), 'class' => 'tier-supreme'];
        if ($p >= 1800) return ['name' => bps_t('tier_eagle'),   'class' => 'tier-eagle'];
        if ($p >= 1000) return ['name' => bps_t('tier_master'),  'class' => 'tier-master'];
        if ($p >= 600)  return ['name' => bps_t('tier_gold'),    'class' => 'tier-gold'];
        if ($p >= 300)  return ['name' => bps_t('tier_silver'),  'class' => 'tier-silver'];
        if ($p >= 100)  return ['name' => bps_t('tier_bronze'),  'class' => 'tier-bronze'];
        return ['name' => bps_t('tier_recruit'), 'class' => 'tier-recruit'];
    }
}

if (!function_exists('bps_schema_snapshot')) {
    /**
     * Carga en una sola consulta toda la estructura de tablas/columnas de la BD actual.
     */
    function bps_schema_snapshot(PDO $pdo) {
        static $schema = null;
        if ($schema !== null) {
            return $schema;
        }
        $schema = ['tables' => [], 'columns' => []];
        try {
            $stmt = $pdo->query(
                "SELECT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE()"
            );
            foreach ($stmt->fetchAll() as $row) {
                $t = (string)$row['TABLE_NAME'];
                $c = (string)$row['COLUMN_NAME'];
                $schema['tables'][$t] = true;
                $schema['columns'][$t . '.' . $c] = true;
            }
        } catch (Exception $e) {
            $schema = ['tables' => [], 'columns' => []];
        }
        return $schema;
    }
}

if (!function_exists('bps_table_has_column')) {
    function bps_table_has_column(PDO $pdo, $table, $column) {
        $schema = bps_schema_snapshot($pdo);
        return !empty($schema['columns'][$table . '.' . $column]);
    }
}

if (!function_exists('bps_table_exists')) {
    function bps_table_exists(PDO $pdo, $table) {
        $schema = bps_schema_snapshot($pdo);
        return !empty($schema['tables'][$table]);
    }
}

if (!function_exists('bps_clean_server_id')) {
    function bps_clean_server_id($srv) {
        $srv = preg_replace('/[^a-zA-Z0-9_-]/', '', trim((string)$srv));
        return substr($srv, 0, 16);
    }
}

if (!function_exists('bps_format_save_mode')) {
    function bps_format_save_mode($mode) {
        switch ((int)$mode) {
            case 0:  return bps_t('mode_disabled');
            case 2:  return bps_t('mode_name_12');
            default: return bps_t('mode_steamid');
        }
    }
}

if (!function_exists('bps_url')) {
    function bps_url(array $params = [], $selectedServer = '', $base = null) {
        if ($base === null) {
            $base = bps_is_motd() ? 'top15.php' : '';
        }
        if ($selectedServer !== '' && !array_key_exists('srv', $params)) {
            $params['srv'] = $selectedServer;
        }
        // En MOTD o si se pidió idioma explícito en URL, conservar lang
        if (!array_key_exists('lang', $params) && (bps_is_motd() || isset($_GET['lang']))) {
            $params['lang'] = bps_lang();
        }
        foreach ($params as $k => $v) {
            if ($v === '' || $v === null) {
                unset($params[$k]);
            }
        }
        $qs = http_build_query($params);
        if ($qs === '') {
            return $base !== '' ? $base : '?';
        }
        return $base . '?' . $qs;
    }
}

if (!function_exists('bps_get_registered_servers')) {
    function bps_get_registered_servers(PDO $pdo) {
        global $bps_tables;
        $srvTable = $bps_tables['servers'] ?? 'bps_servers';
        if (!bps_table_exists($pdo, $srvTable)) {
            return [];
        }
        try {
            $stmt = $pdo->query(
                "SELECT server_id, server_name, save_mode, multi_mode, last_heartbeat,
                        TIMESTAMPDIFF(SECOND, last_heartbeat, CURRENT_TIMESTAMP) AS seconds_ago
                 FROM `{$srvTable}`
                 ORDER BY last_heartbeat DESC, server_id ASC"
            );
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('bps_get_server_partitions')) {
    function bps_get_server_partitions(PDO $pdo) {
        global $bps_tables;
        $statsTable = $bps_tables['stats'];
        if (!bps_table_has_column($pdo, $statsTable, 'server_id')) {
            return [];
        }

        $registered = [];
        foreach (bps_get_registered_servers($pdo) as $srv) {
            $registered[$srv['server_id']] = $srv;
        }

        $partitions = [];
        try {
            $rows = $pdo->query("SELECT server_id, COUNT(*) AS players FROM `{$statsTable}` GROUP BY server_id ORDER BY (server_id = 'global') DESC, players DESC, server_id ASC")->fetchAll();
            foreach ($rows as $r) {
                $sid = (string)$r['server_id'];
                if ($sid === '') {
                    continue;
                }
                if ($sid === 'global') {
                    $label = bps_t('global_shared') . ' (global)';
                } elseif (isset($registered[$sid]) && trim((string)$registered[$sid]['server_name']) !== '') {
                    $label = $registered[$sid]['server_name'] . ' (' . $sid . ')';
                } else {
                    $label = $sid;
                }
                $partitions[$sid] = [
                    'server_id' => $sid,
                    'label'     => $label,
                    'players'   => (int)$r['players'],
                ];
            }
        } catch (Exception $e) {
            // Ignorar si no hay filas aún
        }

        foreach ($registered as $sid => $srv) {
            if ((int)$srv['multi_mode'] === 1 && !isset($partitions[$sid])) {
                $label = trim((string)$srv['server_name']) !== '' ? ($srv['server_name'] . ' (' . $sid . ')') : $sid;
                $partitions[$sid] = [
                    'server_id' => $sid,
                    'label'     => $label,
                    'players'   => 0,
                ];
            }
        }

        return array_values($partitions);
    }
}

if (!function_exists('bps_server_label')) {
    function bps_server_label($serverId, array $registeredById = []) {
        $sid = (string)$serverId;
        if ($sid === '' || $sid === 'global') {
            return bps_t('global_short');
        }
        if (isset($registeredById[$sid]) && trim((string)$registeredById[$sid]['server_name']) !== '') {
            return $registeredById[$sid]['server_name'];
        }
        return $sid;
    }
}

if (!function_exists('bps_player_href')) {
    function bps_player_href(array $row, $selectedServer = '', $base = null) {
        $params = ['go' => 'player'];
        if (!empty($row['save_key'])) {
            $params['key'] = $row['save_key'];
        } elseif (!empty($row['id'])) {
            $params['id'] = $row['id'];
        } elseif (!empty($row['authid'])) {
            $params['authid'] = $row['authid'];
        } else {
            $params['nick'] = (string)($row['nick'] ?? '');
        }
        $srv = '';
        if ($selectedServer !== '') {
            $srv = (string)$selectedServer;
        } elseif (empty($row['save_key']) && !empty($row['server_id']) && strpos((string)$row['server_id'], ',') === false) {
            $srv = (string)$row['server_id'];
        }
        return bps_url($params, $srv, $base);
    }
}

if (!function_exists('bps_get_weapons_catalog')) {
    function bps_get_weapons_catalog() {
        $cPistols = bps_t('wcat_pistols');
        $cSpecial = bps_t('wcat_special');
        $cSmg     = bps_t('wcat_smg');
        $cRifles  = bps_t('wcat_rifles');
        $cSnipers = bps_t('wcat_snipers');

        return [
            // Pistolas (basicweapomstats1)
            'GLOCK18'   => ['name' => 'Glock 18',              'cat' => $cPistols, 'table' => 'basicweapomstats1', 'col' => 'pug_CSW_GLOCK18'],
            'USP'       => ['name' => 'USP Tactical',          'cat' => $cPistols, 'table' => 'basicweapomstats1', 'col' => 'pug_CSW_USP'],
            'P228'      => ['name' => 'P228',                  'cat' => $cPistols, 'table' => 'basicweapomstats1', 'col' => 'pug_CSW_P228'],
            'DEAGLE'    => ['name' => 'Desert Eagle',          'cat' => $cPistols, 'table' => 'basicweapomstats1', 'col' => 'pug_CSW_DEAGLE'],
            'ELITE'     => ['name' => 'Dual Elites',           'cat' => $cPistols, 'table' => 'basicweapomstats1', 'col' => 'pug_CSW_ELITE'],
            'FIVESEVEN' => ['name' => 'Five-SeveN',            'cat' => $cPistols, 'table' => 'basicweapomstats1', 'col' => 'pug_CSW_FIVESEVEN'],
            // Especiales (basicweapomstats1)
            'KNIFE'     => ['name' => bps_t('wname_knife'),     'cat' => $cSpecial, 'table' => 'basicweapomstats1', 'col' => 'pug_CSW_KNIFE'],
            'HEGRENADE' => ['name' => bps_t('wname_hegrenade'), 'cat' => $cSpecial, 'table' => 'basicweapomstats1', 'col' => 'pug_CSW_HEGRENADE'],
            'C4'        => ['name' => bps_t('wname_c4'),        'cat' => $cSpecial, 'table' => 'basicweapomstats1', 'col' => 'pug_CSW_C4'],
            // Escopetas y SMGs (basicweapomstats2)
            'M3'        => ['name' => 'M3 Super 90',           'cat' => $cSmg,     'table' => 'basicweapomstats2', 'col' => 'pug_CSW_M3'],
            'XM1014'    => ['name' => 'XM1014',                'cat' => $cSmg,     'table' => 'basicweapomstats2', 'col' => 'pug_CSW_XM1014'],
            'TMP'       => ['name' => 'TMP',                   'cat' => $cSmg,     'table' => 'basicweapomstats2', 'col' => 'pug_CSW_TMP'],
            'MAC10'     => ['name' => 'MAC-10',                'cat' => $cSmg,     'table' => 'basicweapomstats2', 'col' => 'pug_CSW_MAC10'],
            'MP5NAVY'   => ['name' => 'MP5 Navy',              'cat' => $cSmg,     'table' => 'basicweapomstats2', 'col' => 'pug_CSW_MP5NAVY'],
            'UMP45'     => ['name' => 'UMP-45',                'cat' => $cSmg,     'table' => 'basicweapomstats2', 'col' => 'pug_CSW_UMP45'],
            'P90'       => ['name' => 'FN P90',                'cat' => $cSmg,     'table' => 'basicweapomstats2', 'col' => 'pug_CSW_P90'],
            // Rifles de Asalto (basicweapomstats2)
            'GALIL'     => ['name' => 'Galil AR',              'cat' => $cRifles,  'table' => 'basicweapomstats2', 'col' => 'pug_CSW_GALIL'],
            'FAMAS'     => ['name' => 'FAMAS',                 'cat' => $cRifles,  'table' => 'basicweapomstats2', 'col' => 'pug_CSW_FAMAS'],
            'AK47'      => ['name' => 'AK-47',                 'cat' => $cRifles,  'table' => 'basicweapomstats2', 'col' => 'pug_CSW_AK47'],
            'M4A1'      => ['name' => 'M4A1',                  'cat' => $cRifles,  'table' => 'basicweapomstats2', 'col' => 'pug_CSW_M4A1'],
            'SG552'     => ['name' => 'SG 552',                'cat' => $cRifles,  'table' => 'basicweapomstats2', 'col' => 'pug_CSW_SG552'],
            'AUG'       => ['name' => 'AUG',                   'cat' => $cRifles,  'table' => 'basicweapomstats2', 'col' => 'pug_CSW_AUG'],
            // Snipers y Ametralladoras (basicweapomstats2)
            'SCOUT'     => ['name' => 'Scout',                 'cat' => $cSnipers, 'table' => 'basicweapomstats2', 'col' => 'pug_CSW_SCOUT'],
            'AWP'       => ['name' => 'AWP',                   'cat' => $cSnipers, 'table' => 'basicweapomstats2', 'col' => 'pug_CSW_AWP'],
            'G3SG1'     => ['name' => 'G3SG1',                 'cat' => $cSnipers, 'table' => 'basicweapomstats2', 'col' => 'pug_CSW_G3SG1'],
            'SG550'     => ['name' => 'SG 550',                'cat' => $cSnipers, 'table' => 'basicweapomstats2', 'col' => 'pug_CSW_SG550'],
            'M249'      => ['name' => 'M249',                  'cat' => $cSnipers, 'table' => 'basicweapomstats2', 'col' => 'pug_CSW_M249'],
        ];
    }
}

if (!function_exists('bps_weapon_img_src')) {
    function bps_weapon_img_src($code) {
        $code = strtoupper(preg_replace('/[^A-Z0-9_]/', '', (string)$code));
        if ($code === '') {
            return '';
        }
        if (file_exists(__DIR__ . "/weapom/imagen/{$code}.gif")) {
            return "weapom/imagen/{$code}.gif";
        }
        if ($code === 'C4' && file_exists(__DIR__ . '/imagenpanagamers/C4.jpg')) {
            return 'imagenpanagamers/C4.jpg';
        }
        return '';
    }
}
?>
