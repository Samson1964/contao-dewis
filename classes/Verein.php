<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   DeWIS
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2014
 */

namespace Samson\DeWIS;

class Verein extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'dewis_verein';
	protected $subTemplate = 'dewis_sub_vereinsuche';
	protected $infoTemplate = 'queries';
	
	var $startzeit; // Startzeit des Skriptes
	var $dewis;
	
	var $Helper;

	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_dewis');

			$objTemplate->wildcard = '### DEWIS VEREIN ###';
			$objTemplate->title = $this->name;
			$objTemplate->id = $this->id;

			return $objTemplate->parse();
		}
		else
		{
			// FE-Modus: URL mit allen möglichen Parametern auflösen
			\Input::setGet('zps', \Input::get('zps')); // ZPS-Nummer des Vereins
			\Input::setGet('search', \Input::get('search')); // Suchbegriff
			\Input::setGet('order', \Input::get('order')); // Sortierung

			// Startzeit setzen
			$this->startzeit = microtime(true);
			$this->Helper = \Samson\DeWIS\Helper::getInstance(); // Hilfsfunktionen bereitstellen
		}

		return parent::generate(); // Weitermachen mit dem Modul
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
	
		global $objPage;
		
		// Blacklist laden
		$Blacklist = \Samson\DeWIS\DeWIS::blacklist();

		// Vereinsliste angefordert?
		$zps = \Input::get('zps'); 
		// Vereinssuche aktiv?
		$search = \Input::get('search'); 
		// Sortierung festlegen
		$order = \Input::get('order'); 
		$order = ($order == 'alpha') ? 'alpha' : 'rang';
		
		$mitglied = \Samson\DeWIS\Helper::getMitglied(); // Daten des aktuellen Mitgliedes laden
		
		$this->Template->hl = 'h1'; // Standard-Überschriftgröße
		$this->Template->shl = 'h2'; // Standard-Überschriftgröße 2
		$this->Template->headline = 'DWZ - Verein'; // Standard-Überschrift
		$this->Template->navigation   = \Samson\DeWIS\Helper::Navigation(); // Navigation ausgeben

		// Sperrstatus festlegen
		if(KARTEISPERRE_GAESTE) $gesperrt = $mitglied->id ? false : true;
		else $gesperrt = false;

		if($search)
		{

			$search = \StringUtil::generateAlias($search); // Suche modifizieren

			/*********************************************************
			 * Verbands- und Vereinsliste holen
			*/
			
			$liste = \Samson\DeWIS\DeWIS::Verbandsliste('00000');
			

			/*********************************************************
			 * Suchbegriff im Vereinssuche-Cache?
			*/

			$result_vn = array();
			if($GLOBALS['TL_CONFIG']['dewis_cache'])
			{
				$cache_vn = new \Samson\DeWIS\Cache(array('name' => 'vereinssuche', 'path' => CACHE_DIR, 'extension' => '.cache'));
				$cache_vn->eraseExpired(); // Cache aufräumen, abgelaufene Schlüssel löschen

				// Cache laden
				if($cache_vn->isCached($search))
				{
					$result_vn = $cache_vn->retrieve($search);
				}
			}


			/*********************************************************
			 * Suchbegriff im Verbandssuche-Cache?
			*/

			$result_vb = array();
			if($GLOBALS['TL_CONFIG']['dewis_cache'])
			{
				$cache_vb = new \Samson\DeWIS\Cache(array('name' => 'verbandssuche', 'path' => CACHE_DIR, 'extension' => '.cache'));
				$cache_vb->eraseExpired(); // Cache aufräumen, abgelaufene Schlüssel löschen

				// Cache laden
				if($cache_vb->isCached($search))
				{
					$result_vb = $cache_vb->retrieve($search);
				}
			}


			/*********************************************************
			 * Verbandsliste durchsuchen, Treffer in Array speichern und im Cache lagern
			*/

			if(!$result_vb)
			{
				// Nichts im Cache, Daten deshalb neu übernehmen
				foreach($liste['verbaende'] as $key => $value)
				{
					$pos = strpos($value['order'], $search);
					if($pos !== false)
					{
						$result_vb[] = array
						(
							'zps'		=> $value['zps'],
							'name'		=> sprintf('<a href="'.ALIAS_VERBAND.'/%s.html">%s</a>', $value['zps'], $value['name']),
						);
					}
				}
				// im Cache speichern
				if($GLOBALS['TL_CONFIG']['dewis_cache']) $cache_vb->store($search, $result_vb, $GLOBALS['TL_CONFIG']['dewis_cache_verband'] * 3600);
			}


			/*********************************************************
			 * Vereinsliste durchsuchen, Treffer in Array speichern und im Cache lagern
			*/

			if(!$result_vn)
			{
				// Nichts im Cache, Daten deshalb neu übernehmen
				foreach($liste['vereine'] as $key => $value)
				{
					$pos = strpos($value['order'], $search);
					if($pos !== false)
					{
						$result_vn[] = array
						(
							'zps'		=> $value['zps'],
							'name'		=> sprintf('<a href="'.ALIAS_VEREIN.'/%s.html">%s</a>', $value['zps'], $value['name']),
						);
					}
				}
				// im Cache speichern
				if($GLOBALS['TL_CONFIG']['dewis_cache']) $cache_vn->store($search, $result_vn, $GLOBALS['TL_CONFIG']['dewis_cache_verband'] * 3600);
			}


			/*********************************************************
			 * Seitentitel ändern
			*/

			$objPage->pageTitle = 'Suche nach '.$search;
			$this->Template->subHeadline = 'Suche nach '.$search; // Unterüberschrift setzen


			/*********************************************************
			 * Direkt zum Verein springen, wenn nur 1 Treffer
			*/
			if(count($result_vb) == 0 && count($result_vn) == 1)
			{
				header('Location:'.ALIAS_VEREIN.'/'.$result_vn[0]['zps'].'.html');
			}

			/*********************************************************
			 * Templates füllen
			*/

			$this->Subtemplate = new \FrontendTemplate($this->subTemplate);
			$this->Subtemplate->Sperre = $gesperrt; // Sperre?
			$this->Subtemplate->DSBMitglied = $mitglied->dewisID; // Zugewiesene DeWIS-ID
			$this->Subtemplate->daten_vb = $result_vb;
			$this->Subtemplate->anzahl_vb = count($result_vb);
			$this->Subtemplate->daten_vn = $result_vn;
			$this->Subtemplate->anzahl_vn = count($result_vn);
			$this->Template->searchresult = $this->Subtemplate->parse();
			$Infotemplate = new \FrontendTemplate($this->infoTemplate);
			$this->Template->infobox = $Infotemplate->parse();

		}

		
		// Vereinsliste anfordern
		if($zps)
		{

			// Abfrageparameter einstellen
			$param = array
			(
				'funktion' => 'Vereinsliste',
				'cachekey' => $zps,
				'zps'      => $zps
			);

			$resultArr = \Samson\DeWIS\DeWIS::autoQuery($param); // Abfrage ausführen

			// Sichtbarkeit der Vereinsliste festlegen
			$this->Template->sichtbar = true;
			

			/*********************************************************
			 * Kein Suchergebnis für $zps -> in Verbandsliste suchen
			*/

			if(!$resultArr['result'])
			{
				$liste = \Samson\DeWIS\DeWIS::Verbandsliste('00000'); // Vereins- und Verbandsliste laden
				$vzps = rtrim($zps,0); // In ZPS Nullen hinten entfernen = ZPS des Verbandes

				// Vereinsliste für $zps laden
				foreach($liste['vereine'] as $key => $value)
				{
					if($vzps && $vzps == substr($value['parent'], 0, strlen($vzps)))
					{
						$result[] = array
						(
							'zps'		=> $value['zps'],
							'name'		=> sprintf('<a href="'.ALIAS_VEREIN.'/%s.html">%s</a>', $value['zps'], $value['name']),
						);
					}
				}

				$this->Template->fehler = \Samson\DeWIS\DeWIS::ZeigeFehler();
				if(!$result && !$this->Template->fehler) \Samson\DeWIS\Helper::get404(); // VZPS nicht gefunden
				
				// Titel-Ausgabe modifizieren
				$ausgabetitel = $liste['verbaende'][$zps]['name'] ? $liste['verbaende'][$zps]['name'] : 'ZPS-Raum '.$zps;
				$objPage->pageTitle = 'Suche nach Vereinen in '.$ausgabetitel;
				$this->Template->subHeadline = 'Suche nach Vereinen in '.$ausgabetitel; // Unterüberschrift setzen
	
				// Templates füllen
				$this->Subtemplate = new \FrontendTemplate($this->subTemplate);
				$this->Subtemplate->daten_vn = $result;
				$this->Subtemplate->anzahl_vn = count($result);
				$this->Template->searchresult = $this->Subtemplate->parse();
			}
			
			/*********************************************************
			 * Ausgabe Kopfdaten
			*/

			$this->Template->listenlink   = ($order == 'alpha') ? sprintf("<a href=\"".ALIAS_VEREIN."/%s.html?order=rang\">Rangliste</a>", $resultArr['result']->union->vkz, $resultArr['result']->union->name) : sprintf("<a href=\"".ALIAS_VEREIN."/%s.html?order=alpha\">Alphaliste</a>", $resultArr['result']->union->vkz, $resultArr['result']->union->name);
			$this->Template->vereinsname  = $resultArr['result']->union->name;
			$referent = $resultArr['result']->ratingOfficer; // Wertungsreferent zuweisen


			/*********************************************************
			 * Ausgabe der Vereinsliste
			*/

			$daten = array();
			$z = 0;
			if($resultArr['result']->members)
			{
				// Seitentitel ändern
				$objPage->pageTitle = ($order == 'alpha') ? 'DWZ-Vereinsliste '.$resultArr['result']->union->name : 'DWZ-Rangliste '.$resultArr['result']->union->name;
				$this->Template->subHeadline = ($order == 'alpha') ? 'DWZ-Vereinsliste '.$resultArr['result']->union->name : 'DWZ-Rangliste '.$resultArr['result']->union->name; // Unterüberschrift setzen

				foreach($resultArr['result']->members as $m)
				{
					
					if(PASSIVE_AUSBLENDEN && $m->state == 'P')
					{
						// Passive überspringen
					}
					else
					{
						// Schlüssel für Sortierung generieren
						$z++;
						$key = ($order == 'alpha') ? \StringUtil::generateAlias($m->surname.$m->firstname.$z) : sprintf('%05d-%04d-%s-%03d', 10000 - $m->rating, 1000 - $m->ratingIndex, ($m->tcode) ? $m->tcode : 'Z', $z);
						// Daten zuweisen
						if(!$Blacklist[$m->pid])
						{ 
							$daten[$key] = array
							(
								'PKZ'         => $m->pid,
								'Mglnr'       => sprintf("%04d", $m->membership),
								'Status'      => $m->state,
								'Spielername' => \Samson\DeWIS\Helper::Spielername($m, $gesperrt),
								'Geschlecht'  => ($m->gender == 'm') ? '&nbsp;' : ($m->gender == 'f' ? 'w' : strtolower($m->gender)),
								'KW'          => ($gesperrt) ? '&nbsp;' : \Samson\DeWIS\DeWIS::Kalenderwoche($m->tcode),
								'DWZ'         => (!$m->rating && $m->tcode) ? 'Restp.' : \Samson\DeWIS\DeWIS::DWZ($m->rating, $m->ratingIndex),
								'Elo'         => ($m->elo) ? $m->elo : '-----',
								'FIDE-Titel'  => $m->fideTitle
							);
						}
					}
				}
				// Liste sortieren (ASC)
				ksort($daten);
				// Platzierung hinzufügen
				if($order == 'rang')
				{
					$this->Template->rangliste = true;
					$z = 1;
					foreach($daten as $key => $value)
					{
						$daten[$key]['Platz'] = $z;
						$z++;
					}
				}
			}
			$this->Template->daten = $daten;
			$this->Template->tablesorter = ($order == 'rang') ? array(4, 5, 7) : array(3, 4, 6); // Spaltenposition für Tablesorter-Parser

			/*********************************************************
			 * Ausgabe Verbandszugehörigkeiten
			*/

			$result = \Samson\DeWIS\DeWIS::Verbandsliste('00000');

			$temp = array();
			$y = 0;
			if(isset($result['vereine'][$zps])) $suchzps = $result['vereine'][$zps]['parent'];
			if($suchzps)
			{
				do
				{
					$temp[$y]['typ']  = '';
					$temp[$y]['name'] = sprintf('<a href="'.ALIAS_VERBAND.'/%s.html">%s</a>', $result['verbaende'][$suchzps]['zps'], $result['verbaende'][$suchzps]['name']);
					$alt = $suchzps;
					$suchzps = $result['verbaende'][$suchzps]['parent'];
					$y++;
				}
				while($suchzps != $alt); // Wenn parent-ZPS ungleich aktueller ZPS, dann läuft die Schleife weiter
			}
			$temp  = array_reverse($temp); // Array umdrehen, damit DSB als erstes kommt
			// Ebene hinzufügen
			for($x = 0; $x < count($temp); $x++)
			{
				$temp[$x]['typ'] = 'level_'.$x;
			}
	
			$this->Template->verbaende    = $temp;


			/*********************************************************
			 * Ausgabe zuständiger Wertungsreferent
			*/

			$this->Template->referent = \Samson\DeWIS\DeWIS::Wertungsreferent($referent);


			/*********************************************************
			 * Ausgabe Metadaten
			*/

			$Infotemplate = new \FrontendTemplate($this->infoTemplate);
			$this->Template->infobox = $Infotemplate->parse();
			$this->Template->hinweis = $gesperrt;
			$this->Template->registrierung = \Samson\DeWIS\DeWIS::Registrierungshinweis();

		}

		
	}

}
