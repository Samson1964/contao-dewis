<?php

session_start();

$t0 = gett_microtime();         # Startzeit des Skripts

// URL-Parameter entschärfen
foreach($_GET as $name=>$value)
{
	$value = str_replace("<","",$value);
	$value = str_replace("%3C","",$value);
	$value = str_replace("%3E","",$value);
	$_GET[$name] = str_replace(">","",$value);
}

$GLOBALS["debug"] = false;

// Konfiguration
$GLOBALS["dewis"]["gebjahr_anzeigen"] = false;

// Cache aktiv = Array mit den Stunden von 0 bis 23 Uhr
// 1 = Cache benutzen, 0 = Cache nicht benutzen
// Bis 3 Uhr läuft DEWIS täglich im Wartungsmodus
$cachedauer = 60 * 60 * 24;
$cachezeiten = array(0 => 1,
					 1 => 1,
					 2 => 1,
					 3 => 1,
					 4 => 0,
					 5 => 0,
					 6 => 0,
					 7 => 0,
					 8 => 1,
					 9 => 1,
					 10=> 1,
					 11=> 1,
					 12=> 1,
					 13=> 1,
					 14=> 1,
					 15=> 1,
					 16=> 1,
					 17=> 1,
					 18=> 1,
					 19=> 1,
					 20=> 1,
					 21=> 1,
					 22=> 1,
					 23=> 1
					);

// Cache-Klasse einbinden und Cache-Konfiguraton mitgeben
require_once($_SERVER["DOCUMENT_ROOT"]."/php/class.cache.php");
$GLOBALS["dewis"]["cache"] = new DSBCache($cachedauer,$cachezeiten,$_SERVER["DOCUMENT_ROOT"]."/templates/dsb/php/dewis/cache");

function kopf($titel, $javascript = '') {
	# --------------------------------------------------------
	# Genereller HTML-Kopf
	# --------------------------------------------------------
	global $dwz_db;

	$titel2 = $titel;
	if($titel) $titel = " :: $titel";

$ausgabe = <<<EOD
<!-- DeWIS Start -->
$javascript
<div id="dewis">
<div class="align-left">
<p><a href="spieler.html">Spieler</a> | <a href="verein.html">Verein</a> | <a href="verband.html">Verband</a> | <a href="turnier.html">Turnier</a>
<h2>$titel2</h2>

EOD;
	return $ausgabe;
}

function AlteDatenbank($id)
{
# --------------------------------------------------------
# Sucht in der alten Datenbank nach dem Spieler mit der ID
# --------------------------------------------------------

	// Mit MySQL-Server verbinden
	mysql_connect("mysql4.deutscher-schachbund.de","db107305_1","dwzdb1708");
	mysql_select_db("db107305_1");
	$sql = "SELECT pkz_alt FROM pkz WHERE pkz_neu = '$id'";
	$ergebnis = mysql_query($sql);
	if($row = mysql_fetch_object($ergebnis))
	{
		// Alte PKZ gefunden, dann nach einer ZPS suchen
		$pkz = $row->pkz_alt;
		$sql = "SELECT zpsver,szpsmgl,sstatus FROM dwz_spi WHERE pkz = '$pkz'";
		$ergebnis = mysql_query($sql);
		while($row = mysql_fetch_object($ergebnis))
		{
			// mind. eine ZPS gefunden
			return array("zps" => $row->zpsver."-".$row->szpsmgl,"status" => $row->sstatus);
		}
	}
	return false;
}

function fuss() {
# --------------------------------------------------------
# Letzte Ausgabe auf jeder Seite
# --------------------------------------------------------
  return "</div>\n</div><!-- DeWIS Stop -->\n";
}

function gett_microtime() {
# --------------------------------------------------------
# Aktuelle Zeit exakt ermittlen
# --------------------------------------------------------
  $mtime = explode(' ', microtime());
  return ($mtime[1] + $mtime[0]);
}

function show_microtime($start) {
# --------------------------------------------------------
# Benötigte Zeit ab start zurückgeben
# --------------------------------------------------------
  return str_replace('.', ',', sprintf("%.4f",gett_microtime() - $start));
}

function ANSI_gross($eingabe) {
# --------------------------------------------------------
# Wandelt eingabe in Großbuchstaben um, ersetzt dabei
# Sonderzeichen in A-Z (z.B. é in E)
# Ausgabe entspricht dann Feldname + '_g'
# --------------------------------------------------------

	$mapping = array
		(32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32,
		 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32,
		 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47,
		 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63,
		 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79,
		 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95,
		 96, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79,
		 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90,123,124,125,126,127,
		 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32,
		 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32,
		 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32,
		 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32, 32,
		 65, 65, 65, 65,196, 65, 32, 67, 69, 69, 69, 69, 73, 73, 73, 73,
		 32, 78, 79, 79, 79, 79,214, 32, 32, 85, 85, 85,220, 89, 32,223,
		 65, 65, 65, 65,196,197,198, 67, 69, 69, 69, 69, 73, 73, 73, 73,
		 32, 78, 79, 79, 79, 79,214, 32,216, 85, 85, 85,220, 89, 32, 89);
	$ausgabe = '';
	for ($i=0; $i<strlen($eingabe); $i++)
		$ausgabe .= chr($mapping[ord(substr($eingabe, $i, 1))]);

	$umlaute = array('Ä'=>'AE', 'Æ'=>'AE', 'Å'=>'AU', 'Ö'=>'OE', 'Ø'=>'OE', 'Ü'=>'UE', 'ß'=>'SS');
	$ausgabe = strtr($ausgabe, $umlaute);

	return $ausgabe;
}

function convert($text) {
	return utf8_decode($text);
}

function datum_mysql2php($datum) {
	return $datum ? substr($datum, 8, 2) . '.' . substr($datum, 5, 2) . '.' . substr($datum, 0, 4) : '';
}

function DWZ($rating, $ratingIndex) {
	return ($ratingIndex == 0 and $ratingIndex == 0) ? '-----' : sprintf("%s -%s", str_replace(' ', '&nbsp;&nbsp;', sprintf("%4d", $rating)), str_replace(' ', '&nbsp;&nbsp;', sprintf("%3d", $ratingIndex)));
}

function Punkte($points) {
	return ($points == 0.5) ? '½' : str_replace('.5', '½', $points * 1);
}

function zeiten($zeit_abfrage, $zeit_ausgabe) {
	return sprintf("<p>Abfrage in %s sec<br/>Ausgabe in %s sec</p>\n\n", $zeit_abfrage, $zeit_ausgabe);
}

function verband_struktur($zps) {
	global $verbaende, $vereine;
	if ($zps == '00000')
		return '';
	include_once('org.inc.php');
	$ausgabe =  "<p><strong>Verbandszugehörigkeit</strong></p>\n";
	if (isset($GLOBALS["dewis"]["vereine"][$zps]))
		$zps = $GLOBALS["dewis"]["vereine"][$zps]['Uebergeordnet'];
	$kopf = '';
	$schluss = '';
	do {
		$alt = $zps;
		$kopf = sprintf("<ul><li><a href=\"verband.html?zps=%s\">%s</a>", rtrim($GLOBALS["dewis"]["verbaende"][$zps]['ZPS'], 0), $GLOBALS["dewis"]["verbaende"][$zps]['Name']) . $kopf;
		$schluss .= '</li></ul>';
		$zps = $GLOBALS["dewis"]["verbaende"][$zps]['Uebergeordnet'];
	} while ($GLOBALS["dewis"]["verbaende"][$alt]['Uebergeordnet'] != $GLOBALS["dewis"]["verbaende"][$alt]['ZPS']);
	return sprintf("%s%s%s\n\n", $ausgabe, $kopf, $schluss);
}

function bearbeiter($pkz) {

	list($ausgabe,$zeit_abfrage,$zeit_ausgabe,$result,$message) = soap_abfrage('ratingOfficer',array($pkz),'auswerter',array('felder' => $felder,'key' => ''));

	return "<p><strong>Zuständiger DWZ-Referent:</strong> $ausgabe</p>\n";

}

function hinweis() {
	$ausgabe =  "<p><strong>Hinweis zur Aktiv-Spielberechtigung</strong></p>\n";
	$ausgabe .= "<p>Die Darstellung der Aktiv-Spielberechtigung auf diesen Seiten ist bei Bedarf möglich, aber zur Zeit nicht realisiert. Maßgebend für den Nachweis der aktuellen Vereinszugehörigkeit und damit der Aktiv-Spielberechtigung im engeren Sinne ist die Auskunft über die DV-Referenten der Landesverbände.</p>\n\n";
	return $ausgabe;
}

function is_utf8($str) {
	$strlen = strlen($str);
	for($i=0; $i<$strlen; $i++) {
		$ord = ord($str[$i]);
		if($ord < 0x80) continue; // 0bbbbbbb
		elseif(($ord&0xE0)===0xC0 && $ord>0xC1) $n = 1; // 110bbbbb (exkl C0-C1)
		elseif(($ord&0xF0)===0xE0) $n = 2; // 1110bbbb
		elseif(($ord&0xF8)===0xF0 && $ord<0xF5) $n = 3; // 11110bbb (exkl F5-FF)
		else return false; // ungültiges UTF-8-Zeichen
		for($c=0; $c<$n; $c++) // $n Folgebytes? // 10bbbbbb
			if(++$i===$strlen || (ord($str[$i])&0xC0)!==0x80)
				return false; // ungültiges UTF-8-Zeichen
	}
	return true; // kein ungültiges UTF-8-Zeichen gefunden
}

/**
*Diese Funktion sortiert ein Objektarray nach einer bestimmten Eigenschaft
*
*@param  array $array           Es wird ein zu sortierender Array erwartet
*@param string $eigenschaft     Es wird der Name der Eigenschaft erwartet nach der sortiert werden soll.
*@param string $sortierrichtung N für Normal und R für Reverse
*/

function ObjektarraySort($array, $eigenschaft, $sortierrichtung='N')
{

	if($array)
	{
		foreach($array as $index=>$value){
			$array_eigenschaft[$index] = strtolower($value->$eigenschaft);
		}

		if($sortierrichtung == 'N'){
			asort($array_eigenschaft);
		}elseif($sortierrichtung == 'R'){
			arsort($array_eigenschaft);
		}

		foreach($array_eigenschaft as $index=>$value){
			$sortiertes_array[$index] = $array[$index];
		}
		return $sortiertes_array;
	}
	else return $array;

}

?>