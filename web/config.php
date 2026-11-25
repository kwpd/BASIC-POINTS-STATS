<?php
if (!defined('BPS_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/lang.php';

/**
 * Configuración de Base de Datos para BASIC POINTS STATS
 * Recomendación de seguridad: usar un usuario MySQL dedicado con solo permiso SELECT para la web
 * o definir las variables de entorno BPS_DB_HOST, BPS_DB_PORT, BPS_DB_USER, BPS_DB_PASS, BPS_DB_NAME.
 */

$host     = getenv('BPS_DB_HOST') ?: '127.0.0.1';
$port     = getenv('BPS_DB_PORT') ?: '3306';
$username = getenv('BPS_DB_USER') ?: 'bps_web';
$password = getenv('BPS_DB_PASS') ?: '';
$dbname   = getenv('BPS_DB_NAME') ?: 'basicpointsstats';

$bps_discord_url = getenv('BPS_DISCORD_URL') ?: 'https://discord.com/';

$bps_tables = [
    'stats'   => 'basicpointsstats',
    'weapon1' => 'basicweapomstats1',
    'weapon2' => 'basicweapomstats2',
    'versus'  => 'basicversusstats',
    'servers' => 'bps_servers',
];

foreach ($bps_tables as $k => $tbl) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $tbl)) {
        throw new RuntimeException('Invalid SQL table identifier in configuration.');
    }
}

/**
 * Retorna la conexión PDO singleton configurada a MySQL
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    global $host, $port, $username, $password, $dbname;

    $dbHost = $host;
    $dbPort = $port;
    if (strpos($host, ':') !== false) {
        list($dbHost, $dbPort) = explode(':', $host, 2);
    }

    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbname};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("BPS DB Connection Error: " . $e->getMessage());
        throw new RuntimeException(bps_t('db_conn_error'));
    }
}

require_once __DIR__ . '/helpers.php';
?>
