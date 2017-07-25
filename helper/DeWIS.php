<?php

namespace Samson\DeWIS;
 
class DeWIS
{

	/**
	 * Current object instance
	 * @var object
	 */
	protected static $instance = null;

	var $Fragmente;

	/**
	 * Klasse initialisieren
	 */
	public function __construct()
	{
		$this->answertime = false; // Antwortzeit des Servers
		$this->Fragmente = '';
	}


	/**
	 * Return the current object instance (Singleton)
	 * @return BannerCheckHelper
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new \Samson\DeWIS\DeWIS();
		}
	
		return self::$instance;
	}


	/*********************************************************
	 * autoQuery
	 * =========
	 * Vollautomatisierte Abfrage von DeWIS inkl. Cachenutzung
	 * 
	 * @param       Array mit den Parametern
	 * $param = array
	 * (
	 * 	"funktion" => "Spielerliste", // DeWIS-Funktion/Cachename
	 * 	"cachekey" => "Cacheschlüssel", // Name des Datensatzes im Cache
	 * 	"vorname"  => $vorname, // definierbar anhand DeWIS-Funktion
	 * );
	 * @return      Array mit den Rückgabewerten
	*/
	public function autoQuery($params)
	{

		if($GLOBALS['TL_CONFIG']['dewis_cache'])
		{
			// Cache initialisieren
			$cache = new \Samson\DeWIS\Cache(array('name' => $params['funktion'], 'path' => CACHE_DIR, 'extension' => '.cache'));
			$cache->eraseExpired(); // Cache aufräumen, abgelaufene Schlüssel löschen
			// Cache laden
			if($cache->isCached($params['cachekey']))
			{
				$result = $cache->retrieve($params['cachekey']);
			}
			// Cachezeiten modifizieren
			switch($params['funktion'])
			{
				case 'Verbaende':
					$cachetime = 3600 * $GLOBALS['TL_CONFIG']['dewis_cache_verband'];
					break;
				case 'Wertungsreferent':
					$cachetime = 3600 * $GLOBALS['TL_CONFIG']['dewis_cache_referent'];
					break;
				default:
					$cachetime = 3600;
			}
		}

		// DeWIS-Abfrage, wenn Cache leer
		if(!$result)
		{
			// Abfrage DeWIS
			$tstart = microtime(true);
			$result = self::Abfrage($params);
			$tende = microtime(true) - $tstart;
			$querytime = sprintf("%1.3f", $tende);
			$cachemode = false;
			// im Cache speichern
			if($GLOBALS['TL_CONFIG']['dewis_cache']) $cache->store($params['cachekey'], $result, $cachetime);
			$GLOBALS['DeWIS-Cache']['dewis-queries']++;
			$GLOBALS['DeWIS-Cache']['dewis-queriestimes'] += $querytime;
			//echo $params['funktion'];
			
			// DeWIS-Daten in Contao-Datenbank aktualisieren
			self::AktualisiereDWZTabellen($result, $params);

		}
		else
		{
			// Cache-Modus
			$querytime = false;
			$cachemode = true;
			$GLOBALS['DeWIS-Cache']['cache-queries']++;
		}

		return array
		(
			'result'		=> $result,
			'querytime'		=> $querytime,
			'cachemode'		=> $cachemode
		);
	}


	/*
	* Status der Geburtsjahranzeige setzen
	*
	* @param boolean $status         1 = anzeigen, 0 = nicht anzeigen
	*/
	public function Abfrage($parameter)
	{
		try
		{
			$time_start = microtime(true);
			$client = new \SOAPClient(
				NULL,
				array(
					'location'           => 'https://dwz.svw.info/services/soap/index.php',
					'uri'                => 'https://soap',
					'style'              => SOAP_RPC,
					'use'                => SOAP_ENCODED,
					'connection_timeout' => 15
				)
			);

			switch($parameter["funktion"])
			{
				case "Spielerliste": // Spielerliste einer Suche
					// vorname = Vorname des Spielers, default = leer
					// nachname = Nachname des Spielers
					// limit = Anzahl der Ergebnisse
					$tstart = microtime(true);
					$this->answer = $client->searchByName($parameter["nachname"],$parameter["vorname"],0,$parameter["limit"]);
					$this->answertime = microtime(true) - $tstart;
					break;
				case "Karteikarte": // Karteikarte nach ID
					// id = ID des Spielers
					$tstart = microtime(true);
					$this->answer = $client->tournamentCardForId($parameter["id"]);
					$this->answertime = microtime(true) - $tstart;
					break;
				case "KarteikarteZPS": // Karteikarte nach ZPS
					// zps = Mitgliedsnummer des Spielers
					$tstart = microtime(true);
					$this->answer = $client->tournamentCardForZps($parameter["zps"]);
					$this->answertime = microtime(true) - $tstart;
					break;
				case "Wertungsreferent": // Adresse zur ID eines Wertungsreferenten
					$tstart = microtime(true);
					$this->answer = $client->ratingOfficer($parameter["id"]);
					$this->answertime = microtime(true) - $tstart;
					break;
				case "Vereinsliste": // Spielerliste eines Vereins
					// zps = fünfstellig
					$tstart = microtime(true);
					$this->answer = $client->unionRatingList($parameter["zps"]);
					$this->answertime = microtime(true) - $tstart;
					break;
				case "Verbandsliste": // Bestenliste eines Verbands
					// zps = ein- bis fünfstellig
					// limit = Anzahl der Plätze (<=1000)
					// alter_von = Alter von (>=0)
					// alter_bis = Alter bis (>=0 && <=140)
					// geschlecht ('m', 'f', '')
					$tstart = microtime(true);
					$this->answer = $client->bestOfFederation($parameter["zps"],$parameter["limit"],$parameter["alter_von"],$parameter["alter_bis"],$parameter["geschlecht"]);
					$this->answertime = microtime(true) - $tstart;
					break;
				case "Turnierliste": // Turnierliste
					// von = Datum von als Unixzeit
					// bis = Datum bis als Unixzeit
					// zps = ein- bis dreistellig
					// suche = Suchbegriff (Turniername)
					// von = Datum im Format JJJJ-MM-TT
					// bis = Datum im Format JJJJ-MM-TT
					// von/bis muß im gleichen Jahr liegen!
					$tstart = microtime(true);
					$this->answer = $client->tournamentsByPeriod($parameter["von"],$parameter["bis"],$parameter["zps"],true,"",$parameter["suche"]);
					$this->answertime = microtime(true) - $tstart;
					break;
				case "Turnierauswertung": // Auswertung eines Turniers
					// code = Turniercode, z.B. B148-C12-SLG
					$tstart = microtime(true);
					$this->answer = $client->tournament($parameter["code"]);
					$this->answertime = microtime(true) - $tstart;
					break;
				case "Turnierergebnisse": // Ergebnisse eines Turniers
					// code = Turniercode, z.B. B148-C12-SLG
					$tstart = microtime(true);
					$this->answer = $client->tournamentPairings($parameter["code"]);
					$this->answertime = microtime(true) - $tstart;
					break;
				case "Verbaende": // Verbände einer ZPS-Struktur laden
					// zps = fünfstellig
					$tstart = microtime(true);
					$this->answer = $client->organizations($parameter["zps"]);
					$this->answertime = microtime(true) - $tstart;
					break;
				default:
			}

/*
			echo "<pre>";
			print_r($this->answer);
			echo "</pre>";
*/

			// Abfrage erfolgreich
			return $this->answer;

		}

		catch(\SOAPFault $f)
		{
			$time_request = (microtime(true)-$time_start);
			if(ini_get('default_socket_timeout') < $time_request) 
			{
				// Timeout Fehler!
				$this->error = "Die DeWIS-Datenbank unter svw.info ist nicht erreichbar.";
			}
			else 
			{
				switch($f->faultstring)
				{
					case "that is not a valid federation id":
						$this->error = "Ungültiger Verbandscode [1]";
						break;
					case "that federation does not exists":
						$this->error = "Ungültiger Verbandscode [2]";
						break;
					case "that is not a valid union id":
						break;
					case "that union does not exists":
						$this->error = "Ungültiger Vereinscode";
						$this->errorcode = 410; // Gone (Die angeforderte Ressource wird nicht länger bereitgestellt und wurde dauerhaft entfernt.) - Vorschlag Mossakowski
						$this->log('ZPS-Vereinscode '.$parameter["zps"].' ist ungültig ('.$f->faultstring.')', 'DeWIS-Abfrage', TL_ERROR);
						// Abbruch nicht möglich, da auch gültige Anfragen kommen: http://www.schachbund.de/verein/A0800.html
						//header('HTTP/1.1 410 Gone');
						//die('ZPS-Vereinscode '.$parameter["zps"].' ist ungueltig ('.$f->faultstring.')');
						break;
					case "Could not connect to host":
						$this->error = "Die DeWIS-Datenbank unter svw.info ist nicht erreichbar.";
						break;
					case "that is not a member":
						$this->error = "Der Spieler ist kein Mitglied des DSB.";
						break;
					case "that is not a valid surname":
						$this->error = "Ungültiger Nachname";
						break;
					case "that is not a valid tournament":
						$this->error = "Ungültiges Turnier";
						break;
					case "tournament level not valid":
						$this->error = "tournament level not valid";
						break;
					case "surname too short":
						$this->error = "Nachname zu kurz";
						break;
					default:
						$this->error = $f->faultstring;
				}
			}
		}

		// Fehler bei der Abfrage
		return FALSE;
	}

	/*
	* Wandelt Unixzeit in JJJJ-MM-TT um
	*/
	public function SOAP_Datum($zeit)
	{
		return date("Y-m-d",$zeit);
	}

	/*
	* Gibt die Antwortzeit des Servers zurück
	*/
	public function Antwortzeit()
	{
		return $this->answertime;
	}

	/*
	* Fehler der SOAP-Abfrage zurückgeben
	*/
	public function ZeigeFehler()
	{
		return $this->error;
	}

	/*
	* Fehlercode der SOAP-Abfrage zurückgeben
	*/
	public function ZeigeFehlercode()
	{
		return $this->errorcode;
	}

	/*
	* Status der Geburtsjahranzeige setzen
	*
	* @param boolean $status         1 = anzeigen, 0 = nicht anzeigen
	*/
	public function ZeigeGeburtsjahr($status)
	{
		if($status) $this->viewyear = TRUE;
		else $this->viewyear = FALSE;
	}

	public function DWZ($rating, $ratingIndex) 
	{
		return ($rating == 0 && $ratingIndex == 0) ? '' : sprintf("%s -%s", str_replace(' ', '&nbsp;&nbsp;', sprintf("%4d", $rating)), str_replace(' ', '&nbsp;&nbsp;', sprintf("%3d", $ratingIndex)));
	}

	public function Punkte($points) 
	{
		return ($points == 0.5) ? '½' : str_replace('.5', '½', $points * 1);
	}

	public function Kalenderwoche($string)
	{
		return $string ? substr($string, 2, 2) . '/' . (substr($string, 0, 1) > '9' ? '20' . (ord(substr($string, 0, 1)) - 65) : '19' . substr($string, 0, 1)) . substr($string, 1, 1) : '&nbsp;';
	}
	
	public function AlteDatenbank($id)
	{
		# --------------------------------------------------------
		# Sucht in der alten Datenbank nach dem Spieler mit der ID
		# --------------------------------------------------------
	
		// Mit MySQL-Server verbinden
		$status = @mysql_connect("mysql4.deutscher-schachbund.de","db107305_1","dwzdb1708");
		if($status)
		{
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
		}
		return false;
	}

	public function Verbandsliste($zps) 
	{

		// Abfrageparameter einstellen
		$param = array
		(
			'funktion' => 'Verbaende',
			'cachekey' => $zps,
			'zps'      => $zps
		);

		$resultArr = \Samson\DeWIS\DeWIS::autoQuery($param); // Abfrage ausführen
		
		// Verbände und Vereine ordnen
		list($verbaende, $vereine) = \Samson\DeWIS\DeWIS::org($resultArr['result']);
		
		return array('verbaende' => $verbaende, 'vereine' => $vereine);
	}

	protected function org($result) {
		
		\Samson\DeWIS\DeWIS::sub_org($result, $liste);
	
		$verbaende = array();
		$vereine = array();
		reset($liste);
		foreach ($liste as $l) {
			if ($l['childs'] or $l['parent'] == '00000') {
				$l['childs'] = array();
				$verbaende['' . $l['zps']] = $l;
				if ($l['ZPS'] != '00000')
					$verbaende['' . $l['parent']]['childs'][] = $l['zps'];
			}
			else {
				unset($l['childs']);
				$vereine['' . $l['zps']] = $l;
			}
		}

		// Verband K hinzufügen und DSB modifizieren
		$verbaende['K0000'] = array('zps' => 'K0000', 'name' => 'Ausländer', 'order' => 'auslaender', 'parent' => '00000', 'childs' => array());
		$verbaende['00000']['childs'] = array_merge($verbaende['00000']["childs"],array('00000'));

		return array($verbaende, $vereine);
	}

	protected function sub_org($a, &$liste) {
		$c = (is_array($a->children) && count($a->children) > 0) ? 1 : 0;
		$p = (isset($a->p) && isset($liste[$a->p]['zps'])) ? $liste[$a->p]['zps'] : $a->vkz;
		$n = $a->club;
		$liste[$a->id] = array(
			'zps'           => $a->vkz, # sprintf("%-05s", $a->vkz),
			'name'          => str_replace("'", "\'", $n),
			'order'         => str_replace("'", "\'", \StringUtil::generateAlias($n)),
			'parent'        => $p,
			'childs'        => $c
		);
		if ($c) {
			foreach ($a->children as $b) {
				\Samson\DeWIS\DeWIS::sub_org($b, $liste);
			}
		}
	}

	/**
	 * Hook-Funktion: 
	 * Wertet das URL-Parameter-Array aus und modifiziert es, wenn das Array für DeWIS bestimmt ist
	 *
	 * @return array
	 */
	public function getParamsFromUrl($arrFragments)
	{
		//echo "<!--";
		//print_r($arrFragments);
		$args = count($arrFragments); // Anzahl Argumente

		if($args == 1)
		{
			// In $args[0] steht das Seitenalias, jetzt prüfen auf URL-Parameter und ggfs. auf neue URL weiterleiten
			switch($arrFragments[0])
			{

				case ALIAS_SPIELER:
					if(\Input::get('zps'))
					{
						header('Location:'.ALIAS_SPIELER.'/'.\Input::get('zps').'.html');
					}
					elseif(\Input::get('pkz'))
					{
						header('Location:'.ALIAS_SPIELER.'/'.\Input::get('pkz').'.html');
					}
					break;

				case ALIAS_VEREIN:
					if(\Input::get('zps'))
					{
						header('Location:'.ALIAS_VEREIN.'/'.\Input::get('zps').'.html');
					}
					break;

				default:
			}
		}
		elseif($args > 1)
		{
			// In $args[0] steht das Seitenalias, ab $args[1] die Parameter
			switch($arrFragments[0])
			{

				case ALIAS_SPIELER:
					if($arrFragments[1] == 'auto_item') $arrFragments[1] = 'id';
					// ZPS-Angabe ggfs. anpassen (4-stellige Mitgliedsnummer!)
					$zps = explode('-', $arrFragments[2]);
					$arrFragments[2] = count($zps) == 2 ? $zps[0].'-'.substr('0000'.$zps[1], -4) : $arrFragments[2];
					break;

				case ALIAS_VEREIN:
					if($arrFragments[1] == 'auto_item') $arrFragments[1] = 'zps';
					break;

				case ALIAS_VERBAND:
					if($arrFragments[1] == 'auto_item') $arrFragments[1] = 'zps';
					break;

				case ALIAS_TURNIER:
					if($arrFragments[1] == 'auto_item') 
					{
						$arrFragments[1] = 'code';
					}
					else
					{
						$newArray = array($arrFragments[0]);
						// 1. Wert ist offensichtlich ein Turniercode
						$newArray[1] = 'code';
						$newArray[2] = $arrFragments[1];
						if($arrFragments[2] == 'Ergebnisse')
						{
							// Ein weiterer Wert wartet: Ergebnisse des Turniers anzeigen
							$newArray[3] = 'view';
							$newArray[4] = 'results';
						}
						elseif($arrFragments[2])
						{
							// Ein weiterer Wert wartet: ID des Spielers
							$newArray[3] = 'id';
							$newArray[4] = $arrFragments[2];
							$newArray[5] = 'view';
							$newArray[6] = 'results';
						}
						$arrFragments = $newArray;
					}
					break;

				default:
			}
		}
		
		//echo "<br>";
		//print_r($arrFragments);
		//echo "-->";

		return $arrFragments;
	}


	/**
	 * PurgeJob-Funktion: 
	 * Berechnet die Cache-Größe
	 */
	public function calcCache()
	{
		$speicher = array
		(
			'Verbaende',
			'Wertungsreferent',
			'Spielerliste',
			'Karteikarte',
			'KarteikarteZPS',
			'Vereinsliste',
			'Verbandsliste',
			'Turnierliste',
			'Turnierauswertung',
			'Turnierergebnisse'
		);

		$string = '</label>';
		foreach($speicher as $item)
		{
			$cache = new \Samson\DeWIS\Cache(array('name' => $item, 'path' => CACHE_DIR, 'extension' => '.cache'));
			$anzahl = count($cache->retrieveAll()); // Anzahl der Cache-Einträge
			$text = ($anzahl == 1) ? 'Eintrag' : 'Einträge';
			$string .= '<br><span style="font-weight:normal"><span style="color:black">'.$item.':</span> '.$anzahl.' '.$text.'</span>';
		}
		$string .= '<label>';
		
		//log_message(count($daten),'dewis-cache.log');
		return $string;
	}

	/**
	 * PurgeJob-Funktion: 
	 * Stellt im BE unter Systemwartung die Cache-Löschung zur Verfügung
	 */
	public function purgeCache()
	{
		$speicher = array
		(
			'Verbaende',
			'Wertungsreferent',
			'Spielerliste',
			'Karteikarte',
			'KarteikarteZPS',
			'Vereinsliste',
			'Verbandsliste',
			'Turnierliste',
			'Turnierauswertung',
			'Turnierergebnisse'
		);

		foreach($speicher as $item)
		{
			$cache = new \Samson\DeWIS\Cache(array('name' => $item, 'path' => CACHE_DIR, 'extension' => '.cache'));
			$cache->eraseAll(); // Cache löschen
		}

		log_message('Cache deleted','dewis-cache.log');
		return;
	}



	/**
	 * Hilfsfunktion: 
	 * Formatierte Ausgabe einer Variable
	 *
	 * @return array
	 */
	public function debug($value)
	{
		echo '<pre>';
		print_r($value);
		echo '</pre>';
	}

	/**
	 * Hilfsfunktion: 
	 * Kürzt den Vereinsnamen auf 34 Zeichen, entfernt vorher unnötige Zeichenfolgen
	 *
	 * @return string
	 */
	public function Vereinskurzname($value)
	{
		$ersetzen = array
		(
			' e.V.' => '',
		);
		$value = str_ireplace(array_keys($ersetzen),array_values($ersetzen),$value); 
		return (strlen($value) > 30) ? substr($value,0,30).' [...]' : $value;
	}


	/**
	 * Hilfsfunktion: 
	 * Kürzt den Turniernamen auf 50 Zeichen
	 *
	 * @return string
	 */
	public function Turnierkurzname($value)
	{
		return (strlen($value) > 38) ? substr($value,0,38).' [...]' : $value;
	}


	/**
	 * Liefert zu einer ID die kompletten Daten oder den Namen des Wertungsreferenten
	 *
	 * @return string
	 */
	public function Wertungsreferent($id, $address = true) 
	{

		// Abfrageparameter einstellen
		$param = array
		(
			'funktion' => 'Wertungsreferent',
			'cachekey' => $id,
			'id'       => $id
		);

		$resultArr = \Samson\DeWIS\DeWIS::autoQuery($param); // Abfrage ausführen

		if($address)
		{
			// Name und Adresse ausgeben
			$strasse = ($resultArr['result']->street && $resultArr['result']->street != '-') ? $resultArr['result']->street : '';
			$ort = ($resultArr['result']->zip && $resultArr['result']->city) ? $resultArr['result']->zip .' '. $resultArr['result']->city : '';
			$adresse = ($strasse && $ort) ? '<br>'.$strasse.', '.$ort : '';
			
			$email = ($resultArr['result']->email && $resultArr['result']->email != '-') ? $resultArr['result']->email : '';
			
			return $resultArr['result'] ? $resultArr['result']->firstname." ".$resultArr['result']->surname.$adresse.($email ? '<br>{{email::'.$email.'}}' : '') : '';
		}
		else
		{
			// Name ausgeben
			return $resultArr['result'] ? $resultArr['result']->firstname." ".$resultArr['result']->surname : '('.$id.')';
		}

	}


	/**
	 * Liefert eine Turnierauswertung
	 *
	 * @return string
	 */
	public function Turnierauswertung($code)
	{

		// Abfrageparameter
		$param = array
		(
			'funktion'  => 'Turnierauswertung',
			'cachekey'  => $code,
			'code'      => $code
		);
		
		$resultArr = \Samson\DeWIS\DeWIS::autoQuery($param); // Abfrage ausführen
		
		return $resultArr['result'];
	}


	/**
	 * Liefert die Gewinnerwartung
	 *
	 * @return float
	 */
	public function Gewinnerwartung ($dwz, $gegnerdwz)
	{
		if ($dwz == 0 || $gegnerdwz == 0) return false;
		return (sprintf ("%5.3f", 1/(1+pow(10,($gegnerdwz-$dwz)/400))));
	}

	/**
	 * Schätzt die Turnierleistung, wenn zu wenig Partien 
	 *
	 */
	public function LeistungSchaetzen($niveau = 0, $punkte, $partien, $dwz, $pd = '') 
	{
		
		if($pd == '')
			$pd = 0.5 * $partien;
		
		if($partien) $ppp = $punkte / $partien;
		if($niveau == 0 OR $niveau == '') 
		{
			if($partien && (($punkte - $pd) / $partien > 0.01))
				$leistung = $dwz + 100;
			elseif($partien && ((abs($punkte - $pd)) / $partien <= 0.01))
				$leistung = $dwz;
			else
				$leistung = $dwz - 100;
			return $leistung;
		}
		if(($partien != 5 AND $partien != 6) && ($ppp == 1 OR $ppp == 0)) 
		{
			$diff = 677 / (5 - $partien);
			$leistung = round(($dwz + ($dwz - ($diff + $niveau)) / (6 - $partien)), 0);
		}
		elseif(round($ppp, 0) == 0.5) 
		{
			$leistung = $niveau;
		}
		elseif ($punkte != 0)
		{
			$leistung = round(-400 * log10($partien / $punkte - 1) + $niveau, 0);
		}
		
		return $leistung;
	}

	/**
	 * Liefert die Blacklist zurück 
	 *
	 */
	public function Blacklist() 
	{
		// Gesperrte ID's einlesen
		//$result = \Database::getInstance()->prepare("SELECT dewis_id FROM tl_dewis_blacklist WHERE published = '1'")
		//								  ->execute();
		$result = \Database::getInstance()->prepare("SELECT dewisID FROM tl_dwz_spi WHERE blocked = '1'")
										  ->execute();
		
		$blacklist = array();
		// Übernehmen
		if($result->numRows)
		{
			while($result->next())
			{
				// Frage: Was ist schneller? Dieser Indexzugriff oder später in_array?
				$blacklist[$result->dewis_id] = true;
			}
		}

		return $blacklist;
	}		

	/**
	 * Liefert den Hinweistext zwecks Anmeldung zurück 
	 *
	 */
	public function Registrierungshinweis() 
	{
		return '<div class="hinweis noprint">Aus datenschutzrechtlichen Gründen können Spielerdetails seit dem <a href="http://www.schachbund.de/news/aenderungen-beim-zugriff-auf-die-dwz.html">3. Juni 2016</a> nur noch von registrierten Nutzern angesehen werden. <a href="http://www.schachbund.de/registrierung.html">Hier geht es zur kostenlosen Registrierung</a>.</div>';
	}



	/**
	 * Aktualisiert die Tabellen tl_dwz_xxx mit den Daten aus DeWIS
	 *
	 * @param object $result           Abfrageergebnis DeWIS
	 * @param array $parameter         Abfrageparameter die an DeWIS geliefert wurden
	 *
	 */
	public function AktualisiereDWZTabellen($result, $parameter)
	{
		switch($parameter["funktion"])
		{
			case "Spielerliste": // Spielerliste einer Suche
				if($result->members)
				{
					foreach($result->members as $m)
					{
						// Spieler in lokaler Datenbank suchen, nach dem Kriterium pid
						$objPlayer = \Database::getInstance()->prepare("SELECT * FROM tl_dwz_spi WHERE dewisID = ?")
															 ->execute($m->pid);
						if($objPlayer->numRows)
						{
							while($objPlayer->next())
							{
								// Spieler aktualisieren mit den Daten aus DeWIS
								$set = array
								(
									'tstamp'     => time(),
									'nachname'   => $m->surname,
									'vorname'    => $m->firstname,
									'titel'      => ($m->title) ? $m->title : '',
									'geschlecht' => strtoupper($m->gender),
									'geburtstag' => ($m->yearOfBirth > $objPlayer->geburtstag) ? $m->yearOfBirth : $objPlayer->geburtstag,
									'zpsmgl'     => $m->membership,
									'zpsver'     => $m->vkz,
									'status'     => $m->state ? $m->state : 'A',
									'dwz'        => $m->rating,
									'dwzindex'   => $m->ratingIndex,
									'dwzwoche'   => self::Kalenderwoche($m->tcode),
									'fideID'     => $m->idfide,
									'fideNation' => $m->nationfide,
									'fideElo'    => $m->elo,
									'fideTitel'  => $m->fideTitle,
								);
								$objUpdate = \Database::getInstance()->prepare("UPDATE tl_dwz_spi %s WHERE id=?")
																	 ->set($set)
																	 ->execute($objPlayer->id);
								//$arr = (array)$objPlayer;
								//print_r($arr);
							}
						}
						else
						{
							// Spieler in lokaler Datenbank nicht gefunden, deshalb neu anlegen
							$set = array
							(
								'tstamp'     => time(),
								'dewisID'    => $m->pid,
								'nachname'   => $m->surname,
								'vorname'    => $m->firstname,
								'titel'      => ($m->title) ? $m->title : '',
								'geschlecht' => strtoupper($m->gender),
								'geburtstag' => $m->yearOfBirth,
								'zpsmgl'     => $m->membership,
								'zpsver'     => $m->vkz,
								'status'     => $m->state ? $m->state : 'A',
								'dwz'        => $m->rating ? $m->rating : 0,
								'dwzindex'   => $m->ratingIndex ? $m->ratingIndex : 0,
								'dwzwoche'   => self::Kalenderwoche($m->tcode),
								'fideID'     => $m->idfide ? $m->idfide : 0,
								'fideNation' => $m->nationfide,
								'fideElo'    => $m->elo ? $m->elo : 0,
								'fideTitel'  => $m->fideTitle ? $m->fideTitle : '',
								'published'  => 1,
							);
							$objInsert = \Database::getInstance()->prepare("INSERT INTO tl_dwz_spi %s")
																 ->set($set)
																 ->execute();
							//print_r($m);
						}
					}
				}
				break;

			case "Vereinsliste": // Vereinsliste
				if($result->members)
				{
					foreach($result->members as $m)
					{
						// Spieler in lokaler Datenbank suchen, nach dem Kriterium pid
						$objPlayer = \Database::getInstance()->prepare("SELECT * FROM tl_dwz_spi WHERE dewisID = ?")
															 ->execute($m->pid);
						if($objPlayer->numRows)
						{
							while($objPlayer->next())
							{
								// Spieler aktualisieren mit den Daten aus DeWIS
								$set = array
								(
									'tstamp'     => time(),
									'nachname'   => $m->surname,
									'vorname'    => $m->firstname,
									'titel'      => ($m->title) ? $m->title : '',
									'geschlecht' => strtoupper($m->gender),
									'geburtstag' => ($m->yearOfBirth > $objPlayer->geburtstag) ? $m->yearOfBirth : $objPlayer->geburtstag,
									'zpsmgl'     => $m->membership,
									'zpsver'     => $result->union->vkz,
									'status'     => $m->state ? $m->state : 'A',
									'dwz'        => $m->rating,
									'dwzindex'   => $m->ratingIndex,
									'dwzwoche'   => self::Kalenderwoche($m->tcode),
									'fideID'     => $m->idfide,
									'fideElo'    => $m->elo,
									'fideTitel'  => $m->fideTitle,
								);
								$objUpdate = \Database::getInstance()->prepare("UPDATE tl_dwz_spi %s WHERE id=?")
																	 ->set($set)
																	 ->execute($objPlayer->id);
								//$arr = (array)$objPlayer;
								//print_r($arr);
							}
						}
						else
						{
							// Spieler in lokaler Datenbank nicht gefunden, deshalb neu anlegen
							$set = array
							(
								'tstamp'     => time(),
								'dewisID'    => $m->pid,
								'nachname'   => $m->surname,
								'vorname'    => $m->firstname,
								'titel'      => ($m->title) ? $m->title : '',
								'geschlecht' => strtoupper($m->gender),
								'geburtstag' => $m->yearOfBirth,
								'zpsmgl'     => $m->membership,
								'zpsver'     => $result->union->vkz,
								'status'     => $m->state ? $m->state : 'A',
								'dwz'        => $m->rating ? $m->rating : 0,
								'dwzindex'   => $m->ratingIndex ? $m->ratingIndex : 0,
								'dwzwoche'   => self::Kalenderwoche($m->tcode),
								'fideID'     => $m->idfide ? $m->idfide : 0,
								'fideElo'    => $m->elo ? $m->elo : 0,
								'fideTitel'  => $m->fideTitle ? $m->fideTitle : '',
								'published'  => 1,
							);
							$objInsert = \Database::getInstance()->prepare("INSERT INTO tl_dwz_spi %s")
																 ->set($set)
																 ->execute();
							//print_r($m);
						}
					}
				}
				break;

			default:
		}
	}

}
