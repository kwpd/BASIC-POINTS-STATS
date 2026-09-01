<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>BASIC POINTS STATS</title>
<link rel="stylesheet" href="BPS/mbcsmbmcp.css" type="text/css" />
<style type="text/css">
<!--
body,td,th {
	color: #000000;
}
body {
	color: #000000;
	background-color: #393939;
	background: url("BSP-1.4.1.jpg") repeat-x top center #767a7f fixed;
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
#Tabla003 tr td p {
	color: #FF0000;
	font-weight: bold;
	font-size: 36px;
}
.COPI {
	color: #FF0000;
}
.COPI strong {
	font-size: 18px;
	font-family: Georgia, "Times New Roman", Times, serif;
	color: #0F0;
}
-->
</style></head>

<body>
<table border="0" align="center" cellpadding="0" cellspacing="0" id="Tabla003">
  <tr>
    <td width="76%" align="left" valign="top"><p>BASIC POINST STATS</p></td>
    <td width="24%" align="right" valign="top">&nbsp;</td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="0" id="mytable1">
  <tr>
    <td width="20" height="20" align="center" background="images/corners_1.png"></td>
    <td align="center" background="images/bg_1.gif">&nbsp;</td>
    <td width="20" height="20" align="center" background="images/corners_2.png">&nbsp;</td>
  </tr>
  <tr>
    <td align="center" background="images/bg_2.gif">&nbsp;</td>
    <td align="center" bgcolor="#FFFFFF"><table width="100%" border="0" cellpadding="5" cellspacing="0" id="mytable2">
      <tr>
        <td width="125" align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="40" align="center" valign="top">
<!-- No Copiar Este menu... Copyright © 2007-2013 E-Sports Panama -->
<ul id="mbmcpebul_table" class="mbmcpebul_menulist css_menu" style="width: 125px; height: 362px;">
  <li class="topitem spaced_li first_button"><div class="buttonbg gradient_button gradient35"><div class="icon_1"><a href="?go=home">Home</a></div>
  </div></li>
  <li class="topitem spaced_li"><div class="buttonbg gradient_button gradient35"><div class="arrow"><div class="icon_2"><a>Rankings</a></div></div></div>
  <ul>
  <li class="first_item"><a href="?go=top" title="">TOP Ranking</a></li>
  <li><a href="?go=weapom" title="">Stats Weapom</a></li>
  <li><a href="?go=versus" title="">Stats Versus</a></li>
  <li class="last_item"><a href="?go=Ranking/gungame" title="">Stats GunGame</a></li>
  </ul></li>
  <li class="topitem spaced_li"><div class="buttonbg gradient_button gradient35"><div class="arrow"><div class="icon_3"><a>Server</a></div></div></div>
  <ul>
  <li class="first_item"><a href="?go=home" title="">S. PUG/SCRIM</a></li>
  <li><a href="?go=home" title="">S. HLTV</a></li>
  <li class="last_item"><a href="?go=home" title="">D. REPLAY</a></li>
  </ul></li>
  <li class="topitem spaced_li"><div class="buttonbg gradient_button gradient38" style="height: 38px;"><div class="icon_4"><a href="?go=home" class="button_4">Changelog</a></div>
  </div></li>
  <li class="topitem spaced_li"><div class="buttonbg gradient_button gradient35"><div class="icon_5"><a href="?go=home">Download</a></div>
  </div></li>
  <li class="topitem spaced_li"><div class="buttonbg gradient_button gradient35"><div class="icon_6"><a href="?go=home">Admin</a></div>
  </div></li>
  <li class="topitem spaced_li"><div class="buttonbg gradient_button gradient35"><div class="icon_7"><a href="?go=home">Banned</a></div>
  </div></li>
  <li class="topitem spaced_li"><div class="buttonbg gradient_button gradient35"><div class="icon_8"><a href="?go=home">Equipo</a></div>
  </div></li>
  <li class="topitem spaced_li"><div class="buttonbg gradient_button gradient35"><div class="icon_9"><a href="?go=home">Forum</a></div>
  </div></li>
  <li class="topitem last_button"><div class="buttonbg gradient_button gradient35"><div class="icon_10"><a href="?go=home">Support</a></div>
  </div></li>
</ul>
<!-- No Copiar Este menu... Copyright © 2007-2013 E-Sports Panama -->
<script type="text/javascript" src="BPS/mbjsmbmcp.js"></script>
			</td>
          </tr>
        </table>
		<p>&nbsp;</p>
          </td>
        <td align="left" valign="top">
<?php $allowed_pages = ['home', 'top', 'weapom', 'versus', 'gungame', 
'AK47', 'AUG', 'AWP', 'C4', 'DEAGLE', 'ELITE', 'FAMAS', 'FIVESEVEN', 
'G3SG1', 'GALIL', 'GLOCK18', 'HEGRENADE', 'KNIFE', 'M3', 'M4A1', 'M249', 
'MAC10', 'MP5NAVY', 'P90', 'P228', 'SCOUT', 'SG550', 'SG552', 'TMP', 'UMP45', 'USP', 'XM1014'];
$go = isset($_GET['go']) ? $_GET['go'] : 'home';

if (in_array($go, $allowed_pages)) {
    include($go . '.php');
} else {
    include('home.php');
}
?>
</td>
      </tr>
    </table>        </td>
    <td align="center" background="images/bg_3.gif">&nbsp;</td>
  </tr>
  <tr>
    <td width="20" height="20" align="center" background="images/corners_3.png">&nbsp;</td>
    <td align="center" background="images/bg_4.gif">&nbsp;</td>
    <td width="20" height="20" align="center" background="images/corners_4.png">&nbsp;</td>
  </tr>
</table>
<p align="center" class="COPI"><strong>Copyright &copy; BASIC POINST STATS</strong></p>
<p align="center">&nbsp;</p>
</body>
</body>
</body>
</html>