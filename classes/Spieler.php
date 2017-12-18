<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @package   DeWIS
 * @file      Spieler.php
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2016
 *
 * Version 1.0 - 08.06.2016 - Frank Hoppe
 * --------------------------------------
 * DeWIS-Abfrage:
 * Ausgabe Spielersuche / Ausgabe Spielerkarteikarte mit Diagramm
 *
 */

namespace Samson\DeWIS;

class Spieler extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'dewis_spieler';
	protected $subTemplate = 'dewis_sub_spielersuche';
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

			$objTemplate->wildcard = '### DEWIS SPIELER ###';
			$objTemplate->title = $this->name;
			$objTemplate->id = $this->id;

			return $objTemplate->parse();
		}
		else
		{
			// FE-Modus: URL mit allen möglichen Parametern auflösen
			\Input::setGet('id', \Input::get('id')); // ID
			\Input::setGet('search', \Input::get('search')); // Suchbegriff

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
		$Blacklist = \Samson\DeWIS\DeWIS::Blacklist();
		
		// Spielerkartei angefordert?
		$id = \Input::get('id'); 
		// Spielersuche aktiv?
		$search = \Input::get('search'); 

		$mitglied = \Samson\DeWIS\Helper::getMitglied(); // Daten des aktuellen Mitgliedes laden
		
		$this->Template->hl = 'h1'; // Standard-Überschriftgröße
		$this->Template->shl = 'h2'; // Standard-Überschriftgröße 2
		$this->Template->headline = 'DWZ - Spieler'; // Standard-Überschrift
		$this->Template->navigation   = \Samson\DeWIS\Helper::Navigation(); // Navigation ausgeben

		// Sperrstatus festlegen
		if(KARTEISPERRE_GAESTE) $gesperrt = $mitglied->id ? false : true;
		else $gesperrt = false;

		if($search)
		{
			$check_search = \Samson\DeWIS\Helper::checkSearchstringPlayer($search); // Suchbegriff analysieren

			$this->Template->subHeadline = 'Suche nach '.$search; // Unterüberschrift setzen

			// Abfrageparameter einstellen
			if ($check_search['typ'] == 'name')
			{
				// Spielersuche
				$param = array
				(
					'funktion' => 'Spielerliste',
					'cachekey' => $search,
					'vorname'  => $check_search['vorname'],
					'nachname' => $check_search['nachname'],
					'limit'    => 500
				);
			}
			if ($check_search['typ'] == 'pkz')
			{
				// Spielersuche
				$param = array
				(
					'funktion' => 'Spielerliste',
					'cachekey' => $search,
					'vorname'  => $check_search['vorname'],
					'nachname' => $check_search['nachname'],
					'limit'    => 500
				);
			}
			if ($check_search['typ'] == 'zps')
			{
				// Spielersuche
				$param = array
				(
					'funktion' => 'Spielerliste',
					'cachekey' => $search,
					'vorname'  => $check_search['vorname'],
					'nachname' => $check_search['nachname'],
					'limit'    => 500
				);
			}

			$resultArr = \Samson\DeWIS\DeWIS::autoQuery($param); // Abfrage ausführen
			
			// Daten konvertieren für Ausgabe
			$daten = array();
			if($resultArr['result']->members)
			{
				foreach($resultArr['result']->members as $m)
				{
					
					if($Blacklist[$m->pid] || (PASSIVE_AUSBLENDEN && $m->state == 'P'))
					{
						// Blacklist und Passive überspringen
					}
					else
					{
						$daten[] = array
						(
							'PKZ'         => $m->pid,
							'Verein'      => sprintf("<a href=\"".ALIAS_VEREIN."/%s.html\">%s</a>", $m->vkz, $m->club),
							'Spielername' => \Samson\DeWIS\Helper::Spielername($m, $gesperrt),
							'KW'          => ($gesperrt) ? '&nbsp;' : \Samson\DeWIS\DeWIS::Kalenderwoche($m->tcode),
							'DWZ'         => (!$m->rating && $m->tcode) ? 'Restp.' : \Samson\DeWIS\DeWIS::DWZ($m->rating, $m->ratingIndex),
							'Elo'         => ($m->elo) ? $m->elo : '-----'
						);
					}
				}
			}

			// Leerzeichen in Suche, deshalb Abfrage wiederholen
			if ($strLeer[1])
			{
				// Spielersuche
				$param = array
				(
					'funktion' => 'Spielerliste',
					'cachekey' => $search.'_leer',
					'vorname'  => $check_search['vorname2'],
					'nachname' => $check_search['nachname2'],
					'limit'    => 500
				);
				$resultArr = \Samson\DeWIS\DeWIS::autoQuery($param); // Abfrage ausführen
				if($resultArr['result']->members)
				{
					foreach($resultArr['result']->members as $m)
					{
						
						if($Blacklist[$m->pid] || (PASSIVE_AUSBLENDEN && $m->state == 'P'))
						{
							// Blacklist und Passive überspringen
						}
						else
						{
							$daten[] = array
							(
								'PKZ'         => $m->pid,
								'Verein'      => sprintf("<a href=\"".ALIAS_VEREIN."/%s.html\">%s</a>", $m->vkz, $m->club),
								'Spielername' => \Samson\DeWIS\Helper::Spielername($m, $gesperrt),
								'KW'          => ($gesperrt) ? '&nbsp;' : \Samson\DeWIS\DeWIS::Kalenderwoche($m->tcode),
								'DWZ'         => (!$m->rating && $m->tcode) ? 'Restp.' : \Samson\DeWIS\DeWIS::DWZ($m->rating, $m->ratingIndex),
								'Elo'         => ($m->elo) ? $m->elo : '-----'
							);
						}
					}
				}
			}

			// Untertemplate initialisieren und füllen
			$this->Subtemplate = new \FrontendTemplate($this->subTemplate);
			$this->Subtemplate->DSBMitglied = $mitglied->dewisID; // Zugewiesene DeWIS-ID
			$this->Subtemplate->daten = $daten;
			$this->Subtemplate->anzahl = count($daten);
			$this->Subtemplate->maxinfo = ($param['limit'] <= count($daten)) ? 'Ausgabelimit von ' . $param['limit'] . ' Spielern erreicht' : '';
			$this->Subtemplate->guestinfo = $gesperrt ? '' : 'Spieler-Karteikarten sind nur für angemeldete Besucher sichtbar!';
			$Infotemplate = new \FrontendTemplate($this->infoTemplate);
			$this->Template->infobox = $Infotemplate->parse();
			$this->Template->searchresult = $this->Subtemplate->parse();
			$this->Template->hinweis = $gesperrt;
			$this->Template->registrierung = \Samson\DeWIS\DeWIS::Registrierungshinweis();
			$this->Template->fehler = \Samson\DeWIS\DeWIS::ZeigeFehler();
			$this->Template->searchform = true;

		}

		
		// Kartei anfordern, wenn ID numerisch
		if($id && !$Blacklist[$id])
		{
			// Prüfung $id, ob numerisch (ID) oder String (ZPS)
			if(is_numeric($id))
			{
				// Eine PKZ wurde übergeben, Abfrageparameter einstellen
				$param = array
				(
					'funktion' => 'Karteikarte',
					'cachekey' => $id,
					'id'       => $id
				);
			}
			elseif(strlen($id) == 10 && substr($id,5,1) == '-')
			{
				// Eine ZPS wurde übergeben, Abfrageparameter einstellen
				$param = array
				(
					'funktion' => 'KarteikarteZPS',
					'cachekey' => $id,
					'zps'      => $id
				);
			}

			$resultArr = \Samson\DeWIS\DeWIS::autoQuery($param); // Abfrage ausführen

			if(!$resultArr['result']) \Samson\DeWIS\Helper::get404(); // ID nicht gefunden
			
			// Seitentitel ändern
			$objPage->pageTitle = 'DWZ-Karteikarte '.$resultArr['result']->member->firstname.' '.$resultArr['result']->member->surname;
			$this->Template->subHeadline = 'DWZ-Karteikarte '.$resultArr['result']->member->firstname.' '.$resultArr['result']->member->surname; // Unterüberschrift setzen

			// Sichtbarkeit der Karteikarte festlegen
			$this->Template->sichtbar = $gesperrt ? false : true;
			$this->Template->sperre = $gesperrt;
			
			/*********************************************************
			 * Ausgabe Kopfdaten
			*/

			$this->Template->spielername  = sprintf("%s,%s%s", $resultArr['result']->member->surname, $resultArr['result']->member->firstname, $resultArr['result']->member->title ? ',' . $resultArr['result']->member->title : '');
			$this->Template->geburtsjahr  = GEBURTSJAHR_AUSBLENDEN ? '****' : $resultArr['result']->member->yearOfBirth;
			$this->Template->geschlecht   = GESCHLECHT_AUSBLENDEN ? '*' : ($resultArr['result']->member->gender == 'm' ? 'M' : ($resultArr['result']->member->gender == 'f' ? 'W' : strtoupper($resultArr['result']->member->gender)));
			$this->Template->dewis_id     = $resultArr['result']->member->pid;
			$this->Template->dwz          = $resultArr['result']->member->rating." - ".$resultArr['result']->member->ratingIndex;
			$this->Template->fide_id      = ($resultArr['result']->member->idfide) ? sprintf('<a href="http://ratings.fide.com/card.phtml?event=%s" target="_blank">%s</a>',$resultArr['result']->member->idfide,$resultArr['result']->member->idfide) : '-';
			$this->Template->elo          = ($resultArr['result']->member->elo) ? $resultArr['result']->member->elo : '-';
			$this->Template->fide_titel   = ($resultArr['result']->member->fideTitle) ? $resultArr['result']->member->fideTitle : '-';
			$this->Template->fide_nation  = ($resultArr['result']->member->fideNation) ? ($resultArr['result']->member->gender == 'f' ? sprintf('<a href="https://ratings.fide.com/topfed.phtml?tops=1&ina=1&country=%s" target="_blank">%s</a>',$resultArr['result']->member->fideNation, $resultArr['result']->member->fideNation) : sprintf('<a href="https://ratings.fide.com/topfed.phtml?tops=0&ina=1&country=%s" target="_blank">%s</a>',$resultArr['result']->member->fideNation, $resultArr['result']->member->fideNation)) : '-';

			// Alte Datenbank abfragen
			if(!\Samson\DeWIS\DeWIS::Karteisperre($id) && $altdb = \Samson\DeWIS\DeWIS::AlteDatenbank($id))
			{
				$this->Template->historie = ($altdb["status"] == "L") ? 'Vorhanden, aber zuletzt abgemeldet' : sprintf("<a href=\"http://altdwz.schachbund.de/db/spieler.html?zps=%s\" target=\"_blank\">Alte Karteikarte</a>",$altdb["zps"]);
			}
			else $this->Template->historie = 'Ohne alte Karteikarte';

			/*********************************************************
			 * Ausgabe Vereinsdaten
	 		*/

			$referent = '';
			$zps = '';
			$sortiert = array();
			if($resultArr['result']->memberships)
			{
				foreach($resultArr['result']->memberships as $m)
				{
					$status                    = $m->state ? $m->state : 'A';
					$zps_nr                    = sprintf("%s-%04d", $m->vkz, $m->membership);
					$verein                    = sprintf("<a href=\"".ALIAS_VEREIN."/%s.html\">%s</a>", $m->vkz, $m->club);
					$sortiert[$status.$zps_nr] = array
					(
						'name'   => $verein,
						'zps'    => $zps_nr,
						'status' => $status
					);
					if($referent == '')
					{
						$referent = $m->assessor;
						$zps      = $m->vkz;
					}
					if($m->assessor == '')
					{
						$referent = $m->assessor;
						$zps      = $m->vkz;
					}
				}
			}
			ksort($sortiert);
			$this->Template->vereine      = $sortiert;

			/*********************************************************
			 * Ausgabe zuständiger Wertungsreferent
			*/

			$this->Template->referent = \Samson\DeWIS\DeWIS::Wertungsreferent($referent);

			/*********************************************************
			 * Ausgabe der Karteikarte (DeWIS)
			*/
			$temp = array(); 
			$chart = array();
			$chartlabel = array(); 
			$chartdwz = array(); 
			$chartleistung = array(); 
			$i = 0;
			if($resultArr['result']->tournaments)
			{
				foreach($resultArr['result']->tournaments as $t)
				{
					$i++;
					$dwz_alt = \Samson\DeWIS\DeWIS::DWZ($t->ratingOld, $t->ratingOldIndex);
					$dwz_neu = \Samson\DeWIS\DeWIS::DWZ($t->ratingNew, $t->ratingNewIndex);
					$chartlabel[] = $t->ratingNewIndex;
					$chartdwz[] = $t->ratingNew;
					$chartleistung[] = $t->achievement ? $t->achievement : false;

					$chart[] = array
					(
						'Label'		=> $t->ratingNewIndex,
						'DWZ'		=> $t->ratingNew,
						'Niveau'	=> $t->level,
						'Leistung'	=> $t->achievement ? $t->achievement : false,
						'Punkte'	=> $t->points,
						'Partien'	=> $t->games,
						'We'		=> $t->we,
					);
					
					$temp[] = array
					(
						'nummer'     => ($i == count($resultArr['result']->tournaments) && $dwz_neu != '&nbsp;') ? 'AKT' : $i,
						'jahr'       => (substr($t->tcode, 0, 1) > '9' ? '20' . (ord(substr($t->tcode, 0, 1)) - 65) : '19' . substr($t->tcode, 0, 1)) . substr($t->tcode, 1, 1),
						'turnier'    => sprintf("<a href=\"".ALIAS_TURNIER."/%s/%s.html\" title=\"%s\">%s</a>", $t->tcode, $resultArr['result']->member->pid, $t->tname, \Samson\DeWIS\DeWIS::Turnierkurzname($t->tname)),
						'punkte'     => \Samson\DeWIS\DeWIS::Punkte($t->points),
						'partien'    => $t->games,
						'we'         => $dwz_neu == '&nbsp;' ? '&nbsp;' : str_replace('.', ',', $t->we),
						'e'          => $t->eCoefficient,
						'gegner'     => $t->level ? $t->level : '',
						'leistung'   => $t->achievement ? $t->achievement : '&nbsp;',
						'dwz-neu'    => $dwz_neu,
						'ungewertet' => $t->unratedGames
					);
				}
			}
			$this->Template->kartei = $temp;

			/*********************************************************
			 * Ausgabe Diagramm
			*/

			$this->Template->chartlabel = implode(',',\Samson\DeWIS\Helper::ArrayExtract($chart, 'Label'));
			$this->Template->chartdwz = implode(',',\Samson\DeWIS\Helper::Mittelwerte(\Samson\DeWIS\Helper::ArrayExtract($chart, 'DWZ')));

			// Leistungskurve weicher machen
			for($x = 0; $x < count($chart); $x++)
			{
				if($chart[$x]['Leistung'] == 0)
				{
					$chart[$x]['Leistung'] = \Samson\DeWIS\DeWIS::LeistungSchaetzen($chart[$x]['Niveau'], $chart[$x]['Punkte'], $chart[$x]['Partien'], $chart[$x]['DWZ']); 
				}
			}
			$this->Template->chartleistung = implode(',',\Samson\DeWIS\Helper::Mittelwerte(\Samson\DeWIS\Helper::ArrayExtract($chart, 'Leistung')));

			/*********************************************************
			 * Ausgabe Ranglistenplazierungen und Verbandszugehörigkeiten
			*/

			$temp = array(); $temp2 = array(); $x = 0; $y = 0;
			if($resultArr['result']->ranking[1])
			{
				foreach ($resultArr['result']->ranking[1] as $r)
				{
					$temp[$x]['name']     = $r->organizationType == 'o6' ? sprintf("<a href=\"".ALIAS_VEREIN."/%s.html\">%s</a>", $r->vkz, $r->organization) : sprintf("<a href=\"".ALIAS_VERBAND."/%s.html\">%s</a>", $r->vkz, $r->organization);
					$temp[$x]['typ']      = $r->organizationType;
					$temp[$x]['platz']    = $r->rank;
					$temp[$x]['referent'] = $r->assessor;
					if($r->organizationType != 'o6')
					{
						$temp2[$y]['typ']  = $r->organizationType;
						$temp2[$y]['name'] = $temp[$x]['name'];
						$y++;
					}
					$x++;
				}
			}
			$this->Template->rangliste    = $temp;
			$this->Template->verbaende    = $temp2;

			/*********************************************************
			 * Ausgabe Metadaten
			*/

			$Infotemplate = new \FrontendTemplate($this->infoTemplate);
			$this->Template->infobox = $Infotemplate->parse();

		}
		else
		{
			$this->Template->searchform = true;
		}

	}

}
