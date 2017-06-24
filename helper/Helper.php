<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * 
 * Modul Banner - Check Helper 
 * 
 * PHP version 5
 * @copyright  Glen Langer 2007..2015
 * @author     Glen Langer
 * @package    Banner
 * @license    LGPL
 */


/**
 * Class BannerCheckHelper
 *
 * @copyright  Glen Langer 2015
 * @author     Glen Langer
 * @package    Banner
 */

namespace Samson\DeWIS;

class Helper extends \Frontend
{
	/**
	 * Current object instance
	 * @var object
	 */
	protected static $instance = null;

	var $user;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Benutzerdaten laden
		if(FE_USER_LOGGED_IN)
		{
			// Frontenduser eingeloggt
			$this->user = \FrontendUser::getInstance();
		}
		parent::__construct();
	}


	/**
	 * Return the current object instance (Singleton)
	 * @return BannerCheckHelper
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new \Samson\DeWIS\Helper();
		}
	
		return self::$instance;
	}

	public function getMitglied()
	{
		//\Samson\DeWIS\DeWIS::debug(\FrontendUser::getInstance());
		return \FrontendUser::getInstance(); //$this->user;
	}

	/**
	 * Leitet auf die im System definierte 404-Seite weiter
	 */
	public function get404()
	{
		$objHandler = new $GLOBALS['TL_PTY']['error_404']();
		$objHandler->generate($objPage->id);
	}

	/**
	 * Gibt die ID des Contao-Mitgliedes zurück, dem eine bestimmte DeWIS-ID zugewiesen ist
	 * @param id	ID in DeWIS
	 * @return		ID des Contao-Mitgliedes
	 */
	public function Karteizuweisung($id)
	{
		$objSpieler = \Database::getInstance()->prepare('SELECT contaoMemberID FROM tl_dwz_spi WHERE dewisID = ?')
											  ->limit(1) 
											  ->execute($id); 
		return $objSpieler->contaoMemberID;
	}

	/**
	 * Gibt den Status der Karteikartensperre für eine DeWIS-ID zurück
	 * @param id	ID in DeWIS
	 * @return		Karteikarte gesperrt true/false
	 */
	public function Karteisperre($id)
	{
		$objCheckUser = \Database::getInstance()->prepare('SELECT dewisCard FROM tl_member WHERE id=?')
												->execute($id); 
		return $objCheckUser->dewisCard;
	}

	/**
	 * Gibt die Navigation zurück
	 * @param 		-
	 * @return		Array mit den Links
	 */
	public function Navigation()
	{
		return array
		(
			'<li class="first"><a href="'.ALIAS_SPIELER.'.html">Spieler</a></li>',
			'<li class=""><a href="'.ALIAS_VEREIN.'.html">Vereine</a></li>',
			'<li class=""><a href="'.ALIAS_VERBAND.'.html">Verbände</a></li>',
			'<li class="last"><a href="'.ALIAS_TURNIER.'.html">Turniere</a></li>',
		);
	}

	/**
	 * Prüft ob ein Jahr ein Schaltjahr ist und gibt entsprechend die Monatslängen zurück
	 * @param 		-
	 * @return		Array mit Anzahl Tage je Monat
	 */
	function Monatstage($jahr)
	{
		$monate = array
		(
			1 => 31,
			2 => 28,
			3 => 31,
			4 => 30,
			5 => 31,
			6 => 30,
			7 => 31,
			8 => 31,
			9 => 30,
			10 => 31,
			11 => 30,
			12 => 31
		);

		if(($jahr % 400) == 0 || (($jahr % 4) == 0 && ($jahr % 100) != 0))
		{
			// Schaltjahr
			$monate[2] = 29;
			return $monate;
		}
		else
		{
			// Kein Schaltjahr
			return $monate;
		}
	}

	public function datum_mysql2php($datum) 
	{
		return $datum ? substr($datum, 8, 2) . '.' . substr($datum, 5, 2) . '.' . substr($datum, 0, 4) : '';
	}

	/**
	 * Ersetzt in einem numerischen Array 0-Werte durch einen Mittelwert der Nachbarwerte
	 * @param 		Array
	 * @return		Array
	 */
	public function Mittelwerte($array)
	{
		
		$value = 0;
		$key = -1;
		for($x = 0; $x < count($array); $x++)
		{
			if($array[$x] > 0)
			{
				// Wert ungleich 0 gefunden, das ist der Nachfolgerwert
				// Anzahl 0-Werte davor ermitteln
				$teiler = $x - $key;
				if($teiler > 1)
				{
					// Nullwerte gefunden, ersetzen durch Mittelwerte
					// Mittelwertedifferenz ermitteln
					$mittelwert_differenz = sprintf('%d', ($value - $array[$x]) / $teiler);
					if($key == -1)
					{
						// Nullwerte am Anfang mit aktuellem Wert befüllen
						for($y = 0; $y < $x; $y++)
						{
							$array[$y] = $array[$x];
						}
					}
					else
					{
						// Nullwerte in der Arraymitte mit Differenz füllen
						for($y = $key + 1; $y < $x; $y++)
						{
							$array[$y] = $array[$y-1] - $mittelwert_differenz;
						}
					}
				}
				// Neue Vorgängerwerte setzen, aktuellen Wert benutzen
				$key = $x;
				$value = $array[$x];
			}
		}
		
		// Nullwerte am Arrayende auffüllen
		for($y = $key + 1; $y < count($array); $y++)
		{
			$array[$y] = $value;
		}

		return $array;
				
	}

	/**
	 * Setzt den Spielernamen zusammen mit einem Link zur Karteikarte
	 * @param 		Array
	 * @return		Array
	 */
	public function Spielername($t, $gesperrt)
	{
		return ($gesperrt) ? sprintf("%s,%s%s", $t->surname, $t->firstname, $t->title ? ',' . $t->title : '') : sprintf("<a href=\"".ALIAS_SPIELER."/%s.html\">%s</a>", $t->pid, sprintf("%s,%s%s", $t->surname, $t->firstname, $t->title ? ',' . $t->title : ''));
	}

	/**
	 * Schreibt Werte aus einem Array vom Array in ein neues Array
	 * @param 		Array
	 * 				Beispiel:
	 *				array(array('item'=>1,'val'=>2),array('item'=>3,'val'=>6))
	 * @param		String ($extract = 'item' oder 'val')
	 * @return		Array
	 */
	public function ArrayExtract($array, $extract)
	{
		$newArr = array();
		foreach($array as $key => $value)
		{
			$newArr[] = $value[$extract];
		}
		return $newArr;
	}

}