<?php
// Se hizo una prueba con la version XAMPP v3.3.0
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta con la función RANK()
    $sql = "SELECT ROW_NUMBER() OVER (ORDER BY pug_k DESC) AS row_num, nick, pug_k, pug_d, pug_a, pug_bp, pug_bd, pug_hsp, 
	skillpoints, pug_win, pug_los FROM basicpointsstats LIMIT 15";
    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mostrar el ranking
    echo "<table width=100% border=1 cellpadding=5 cellspacing=0 bordercolor=#B8B8B8><tr>
	<th align=left valign=top scope=col abbr=row_num>#</th>
	<th width=200 align=left valign=top scope=col abbr=NICK>Nombre</th>
	<th>Kill</th>
	<th>Dead</th>
	<th>Assist</th>
	<th>B. Plant</th>
	<th>B. Defiusa</th>
	<th>Heashot</th>
	<th>Point</th>
	<th>Win</th>
	<th>Loss</th>
	</tr>";
    foreach ($resultados as $BASIC_POINTS_STATS) {
	echo "<tr>";
        echo "<td>" . htmlspecialchars($BASIC_POINTS_STATS['row_num'] ) . "</td>";
        echo "<td>" . htmlspecialchars($BASIC_POINTS_STATS['nick']) . "</td>";
        echo "<td>" . htmlspecialchars($BASIC_POINTS_STATS['pug_k']) . "</td>";
		echo "<td>" . htmlspecialchars($BASIC_POINTS_STATS['pug_d']) . "</td>";
		echo "<td>" . htmlspecialchars($BASIC_POINTS_STATS['pug_a']) . "</td>";
        echo "<td>" . htmlspecialchars($BASIC_POINTS_STATS['pug_bp']) . "</td>";
        echo "<td>" . htmlspecialchars($BASIC_POINTS_STATS['pug_bd']) . "</td>";
		echo "<td>" . htmlspecialchars($BASIC_POINTS_STATS['pug_hsp']) . "</td>";
		echo "<td>" . htmlspecialchars($BASIC_POINTS_STATS['skillpoints']) . "</td>";
		echo "<td>" . htmlspecialchars($BASIC_POINTS_STATS['pug_win']) . "</td>";
		echo "<td>" . htmlspecialchars($BASIC_POINTS_STATS['pug_los']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "Error en la conexión: " . $e->getMessage();
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>BASIC POINTS STATS</title>
<style type="text/css">
<!--
body,td,th {
	color: #000000;
}
body {
	color: #000000;
	background-color: #393939;
}
#mytable1 {
	margin:0 auto;
	max-width:1360px;
	min-width:1000px;
	width:91%;
}
#Tabla003 {
	margin:0 auto;
	max-width:1360px;
	min-width:1000px;
	width:91%;
}
.style4 {font-family: "Times New Roman", Times, serif}

.style1 {color: #000000}
-->
</style>
</head>
</html>

