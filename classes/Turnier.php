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

class Turnier extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'dewis_turnier';
	protected $subTemplate = 'dewis_sub_turniersuche';
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

			$objTemplate->wildcard = '### DEWIS TURNIER ###';
			$objTemplate->title = $this->name;
			$objTemplate->id = $this->id;

			return $objTemplate->parse();
		}
		else
		{
			// FE-Modus: URL mit allen möglichen Parametern auflösen
			\Input::setGet('code', \Input::get('code')); // Turniercode
			\Input::setGet('id', \Input::get('id')); // ID des Spielers

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

		$code = \Input::get('code'); // Turniercode angefordert?
		$search = \Input::get('search'); // Turniersuche aktiv?
		$turniercode = \Input::get('code'); // Turniercode
		$id = \Input::get('id'); // Spieler-ID
		$view = \Input::get('view'); // View
		
		$mitglied = \Samson\DeWIS\Helper::getMitglied(); // Daten des aktuellen Mitgliedes laden
		$aktzeit = time();
		
		$this->Template->hl = 'h1'; // Standard-Überschriftgröße
		$this->Template->shl = 'h2'; // Standard-Überschriftgröße 2
		$this->Template->headline = 'DWZ - Turnier'; // Standard-Überschrift
		$this->Template->navigation   = \Samson\DeWIS\Helper::Navigation(); // Navigation ausgeben

		// Sperrstatus festlegen
		if(KARTEISPERRE_GAESTE) $gesperrt = $mitglied->id ? false : true;
		else $gesperrt = false;

		// DeWIS-Klasse initialisieren
		$dewis = new \Samson\DeWIS\DeWIS();

		// Verbands- und Vereinsliste holen
		$liste = \Samson\DeWIS\DeWIS::Verbandsliste('00000');

		if($search)
		{

			/*********************************************************
			 * TURNIERSUCHE
			 * ============
			 * Die DeWIS-API ermöglicht keine Turniersuche jahresübergreifend.
			 * Deshalb wird an dieser Stelle ein Array mit den Zeiträumen generiert
			 * und die Jahre einzeln abgefragt.
			*/

			// ZPS-Cookie setzen
			setcookie('dewis-verband-zps', rtrim(\Input::get('zps'),0), time()+8640000, '/');
			
			// GET-Parameter korrigieren
			$last_months = 0 + \Input::get('last_months');
			$from_year = sprintf('%04d',\Input::get('from_year'));
			$to_year = sprintf('%04d',\Input::get('to_year'));
			$from_month = sprintf('%02d',\Input::get('from_month'));
			$to_month = sprintf('%02d',\Input::get('to_month'));
			($from_year < 2011) ? $from_year = 2011 : '';
			
			// Zeitraum anpassen, wenn "Letzte x Monate" gewählt wurde
			if($last_months > 0 && $last_months < 13)
			{
				$last_months--; // Wegen aktuellem Monat 1 abziehen
				$from_year = date('Y', strtotime('-'.$last_months.' months', mktime(0,0,0,date("n",$aktzeit),1,date("Y",$aktzeit))));
				$from_month = date('m', strtotime('-'.$last_months.' months', mktime(0,0,0,date("n",$aktzeit),1,date("Y",$aktzeit))));
				$to_year = date('Y', $aktzeit);
				$to_month = date('m', $aktzeit);
			}

			// Nur zulässige Jahre berücksichtigen
			if($from_year != '0000' && $to_year != '0000')
			{
				$zeitraum = array();
				for($year = $from_year; $year <= $to_year; $year++)
				{
					$zeitraum[] = array
					(
						'von' => ($year == $from_year) ? $from_year.'-'.$from_month.'-01' : $year.'-01-01',
						'bis' => ($year == $to_year) ? $to_year.'-'.$to_month.'-'.\Samson\DeWIS\Helper::Monatstage($to_year)[ltrim($to_month,0)] : $year.'-12-31',
					);
				}

				/*********************************************************
				 * Abfrage aller Zeiträume durchführen
				*/

				$daten = array();
				foreach($zeitraum as $periode)
				{
					/*********************************************************
					 * Suchbegriff im Turniersuche-Cache?
					*/

					// ZPS-Nummer des Verbandes
					$param = array
					(
						'funktion'  => 'Turnierliste',
						'cachekey'  => strtolower(\Input::get('keyword')).'-'.\Input::get('zps').'-'.$periode['von'].'-'.$periode['bis'],
						'von'       => $periode['von'],
						'bis'       => $periode['bis'],
						'zps'       => \Input::get('zps'),
						'suche'     => strtolower(\Input::get('keyword')),
					);

					$resultArr = \Samson\DeWIS\DeWIS::autoQuery($param); // Abfrage ausführen

					if($resultArr['result']->tournaments)
					{
						foreach($resultArr['result']->tournaments as $t)
						{
							$daten[] = array
							(
								'Turniercode'	=> $t->tcode,
								'Turniername'	=> sprintf('<a href="'.ALIAS_TURNIER.'/%s.html" title="%s">%s</a>', $t->tcode, $t->tname, \Samson\DeWIS\DeWIS::Turnierkurzname($t->tname)),
								'Turnierende'	=> substr($t->finishedOn,8,2).'.'.substr($t->finishedOn,5,2).'.'.substr($t->finishedOn,0,4),
								'Auswerter'		=> \Samson\DeWIS\DeWIS::Wertungsreferent($t->assessor1, false),
							);
						}
					}
				}
			}

			/*********************************************************
			 * Ergebnisse sortieren (nach Turniercode abwärts)
			*/

			// Hilfsarray für Sortierung anlegen
			$sortArray = array();
			foreach($daten as $arr)
			{
				foreach($arr as $key=>$value)
				{
					if(!isset($sortArray[$key]))
					{
						$sortArray[$key] = array();
					}
					$sortArray[$key][] = $value;
				}
			}
			
			$orderby = 'Turniercode'; // Sortierschlüssel
			if (is_array($sortArray[$orderby])) array_multisort($sortArray[$orderby],SORT_DESC,$daten); 

			/*********************************************************
			 * Seitentitel ändern
			*/

			$title = 'Ergebnis für den Zeitraum '.$from_month.'/'.$from_year.' bis '.$to_month.'/'.$to_year;
			$objPage->pageTitle = $title;
			$this->Template->subHeadline = $title; // Unterüberschrift setzen


			/*********************************************************
			 * Templates füllen
			*/

			$this->Subtemplate = new \FrontendTemplate($this->subTemplate);
			$this->Subtemplate->daten = $daten;
			$this->Subtemplate->anzahl = count($daten);
			$this->Subtemplate->search_keyword = $keyword;
			$this->Subtemplate->search_verband = \Input::get('zps');
			$this->Subtemplate->search_from = $from_month.'/'.$from_year;
			$this->Subtemplate->search_to = $to_month.'/'.$to_year;
			$this->Template->fehler = \Samson\DeWIS\DeWIS::ZeigeFehler();
			$this->Template->searchresult = $this->Subtemplate->parse();
			$Infotemplate = new \FrontendTemplate($this->infoTemplate);
			$this->Template->infobox = $Infotemplate->parse();

		}
		elseif($turniercode && $view == 'results')
		{

			/*********************************************************
			 * Ausgabe der Ergebnisse bzw. des Scoresheets
			*/

			/*********************************************************
			 * Auswertung laden und DWZ speichern
			*/

			$playerArr = array(); // Array mit den Teilnehmern
			$resultArr = array(); // Array mit den Ergebnissen
			$result_tausw = \Samson\DeWIS\DeWIS::Turnierauswertung($turniercode); // Auswertung laden (für DWZ)

			if($result_tausw->evaluation)
			{
				foreach ($result_tausw->evaluation as $t)
				{
					$playerArr[$t->pid] = array
					(
						'Spielername'	=> $Blacklist[$t->pid] ? '***' : \Samson\DeWIS\Helper::Spielername($t, $gesperrt),
						'Spielername'	=> $Blacklist[$t->pid] ? '***' : ($gesperrt ? sprintf("%s,%s%s", $t->surname, $t->firstname, $t->title ? ',' . $t->title : '') : sprintf("<a href=\"".ALIAS_SPIELER."/%s.html\">%s</a>", $t->pid, sprintf("%s,%s%s", $t->surname, $t->firstname, $t->title ? ',' . $t->title : ''))),
						'Scoresheet'	=> $Blacklist[$t->pid] ? '' : ($gesperrt ? '' : sprintf("<a href=\"".ALIAS_TURNIER."/%s/%s.html\">SC</a>", $result_tausw->tournament->tcode, $t->pid)),
						'DWZ'			=> ($t->ratingOld) ? $t->ratingOld : '',
						'Punkte'		=> 0,
						'Partien'		=> 0,
						'Buchholz'		=> 0,
					);
				}
			}

			/*********************************************************
			 * Ergebnisse laden
			*/

			// Abfrageparameter
			$param = array
			(
				'funktion'  => 'Turnierergebnisse',
				'cachekey'  => $turniercode,
				'code'      => $turniercode
			);

			$result = \Samson\DeWIS\DeWIS::autoQuery($param); // Abfrage ausführen
			$result_tresult = $result['result'];

			/*********************************************************
			 * Turnierheader
			*/

			$objPage->pageTitle = 'Ergebnisse '.$result_tresult->tournament->tname;
			$this->Template->subHeadline = $result_tresult->tournament->tname; // Unterüberschrift setzen

			$theader = array
			(
				'Auswertung'	=> sprintf('<a href="'.ALIAS_TURNIER.'/%s.html">Turnierauswertung</a>', $result_tresult->tournament->tcode),
				'Ergebnisse'	=> sprintf('<a href="'.ALIAS_TURNIER.'/%s/Ergebnisse.html">Turnierergebnisse</a>', $result_tresult->tournament->tcode),
				'Turniercode'	=> $result_tresult->tournament->tcode,
				'Turniername'	=> $result_tresult->tournament->tname,
				'Turnierende'	=> \Samson\DeWIS\Helper::datum_mysql2php($result_tresult->tournament->finishedOn),
				'Berechnet'		=> sprintf("%s %s", \Samson\DeWIS\Helper::datum_mysql2php(substr($result_tresult->tournament->computedOn, 0, 10)), substr($result_tresult->tournament->computedOn, 11, 5)),
				'Nachberechnet'	=> $result_tresult->tournament->recomputedOn == 'NULL' || $result_tresult->tournament->recomputedOn == '' ? '&nbsp;' : sprintf("%s %s", \Samson\DeWIS\Helper::datum_mysql2php(substr($result_tresult->tournament->recomputedOn, 0, 10)), substr($result_tresult->tournament->recomputedOn, 11, 5)),
				'Auswerter1'	=> \Samson\DeWIS\DeWIS::Wertungsreferent($result_tresult->tournament->assessor1),
				'Auswerter2'	=> \Samson\DeWIS\DeWIS::Wertungsreferent($result_tresult->tournament->assessor2, false),
				'Spieler'		=> $result_tresult->tournament->cntPlayer,
				'Partien'		=> $result_tresult->tournament->cntGames,
				'Runden'		=> $result_tresult->tournament->rounds,
			);

			/*********************************************************
			 * Ergebnisse in einem Array speichern
			*/

			if ($result_tresult->rounds)
			{
				foreach($result_tresult->rounds as $r)
				{
					foreach ($r->games as $g) 
					{
						// Es kann mehrere Ergebnisse je Runde geben, deshalb das Subarray!
						if(!$playerArr[$g->idWhite]['Spielername']) $playerArr[$g->idWhite]['Spielername'] = $g->white;
						$resultArr[$g->idWhite][$r->no][] = array
						(
							'Gegner'   => $g->idBlack,
							'Ergebnis' => mb_substr($g->result,0,1),
							'Farbe'    => 'white',
							'Nummer'   => 0,
						); 
						if(!$playerArr[$g->idBlack]['Spielername']) $playerArr[$g->idBlack]['Spielername'] = $g->black;
						$resultArr[$g->idBlack][$r->no][] = array
						(
							'Gegner'   => $g->idWhite,
							'Ergebnis' => mb_substr($g->result,2,1),
							'Farbe'    => 'black',
							'Nummer'   => 0,
						); 
					}
				}
			}

			/*********************************************************
			 * Punkte addieren
			*/

			foreach($resultArr as $playerId => $roundArr)
			{
				foreach($roundArr as $runde => $dataArr)
				{
					foreach($dataArr as $erg)
					{
						switch($erg['Ergebnis'])
						{
							case '1':
							case '+':
								$playerArr[$playerId]['Punkte'] += 1;
								$playerArr[$playerId]['Partien'] += 1;
								break;
							case '½':
								$playerArr[$playerId]['Punkte'] += .5;
								$playerArr[$playerId]['Partien'] += 1;
								break;
							case '0':
							case '-':
								$playerArr[$playerId]['Partien'] += 1;
								break;
							default:
						}
					}
				}
			}

			/*********************************************************
			 * Buchholz addieren (für Sortierung)
			*/

			foreach($resultArr as $playerId => $roundArr)
			{
				foreach($roundArr as $runde => $dataArr)
				{
					foreach($dataArr as $opp)
					{
						$playerArr[$playerId]['Buchholz'] += $playerArr[$opp['Gegner']]['Punkte'];
					}
				}
			}

			/*********************************************************
			 * Sortierschlüssel hinzufügen und Array für Sortierung umbauen
			*/

			$i = 0;
			$tempArr = array();
			foreach($playerArr as $playerId => $dataArr)
			{
				$i++;
				$key = sprintf('%04d-%03d-%04d-%04d', 9999 - $playerArr[$playerId]['Punkte'] * 10, $playerArr[$playerId]['Partien'], 9999 - $playerArr[$playerId]['Buchholz'], $i);
				$tempArr[$key] = array
				(
					'Spielername'	=> $playerArr[$playerId]['Spielername'],
					'Scoresheet'	=> $playerArr[$playerId]['Scoresheet'],
					'DWZ'			=> $playerArr[$playerId]['DWZ'],
					'Punkte'		=> $playerArr[$playerId]['Punkte'],
					'Partien'		=> $playerArr[$playerId]['Partien'],
					'Buchholz'		=> $playerArr[$playerId]['Buchholz'],
					'ID'			=> $playerId,
				);
			}

			ksort($tempArr); // Nach Schlüssel alphabetisch sortieren

			// Array zurückwandeln
			$playerArr = array();
			$i = 0;
			foreach($tempArr as $key => $dataArr)
			{
				$i++;
				$playerArr[$dataArr['ID']] = array
				(
					'Nummer'		=> $i,
					'Spielername'	=> $dataArr['Spielername'],
					'Scoresheet'	=> $dataArr['Scoresheet'],
					'DWZ'			=> $dataArr['DWZ'],
					'Punkte'		=> $dataArr['Punkte'],
					'Partien'		=> $dataArr['Partien'],
					'Ergebnis'		=> sprintf("%s/%s", \Samson\DeWIS\DeWIS::Punkte($dataArr['Punkte']), $dataArr['Partien']),
					'Buchholz'		=> $dataArr['Buchholz'],
				);
			}

			/*********************************************************
			 * Ergebnisse in Array einfügen und gegnerische Nummer (nach Sortierung) ergänzen
			*/

			foreach($playerArr as $playerId => $dataArr)
			{
				// Nummern der Gegner ergänzen
				if($resultArr[$playerId])
				{
					foreach($resultArr[$playerId] as $runde => $roundArr)
					{
						for($x = 0; $x < count($roundArr); $x++)
						{
							$resultArr[$playerId][$runde][$x]['Nummer'] = $playerArr[$roundArr[$x]['Gegner']]['Nummer']; // gegnerische Nummer ergänzen
						}
					}
				}
				// Ergebnisarray einfügen
				$playerArr[$playerId]['Ergebnisse'] = $resultArr[$playerId];
			}

			/*********************************************************
			 * Ausgabe für Scoresheet modifizieren, wenn $id gesetzt
			*/

			if($id && $playerArr[$id])
			{

				// Seitentitel/Unterüberschrift
				$titel = 'Scoresheet '.strip_tags($playerArr[$id]['Spielername']).' / '.$result_tresult->tournament->tname;
				$objPage->pageTitle = $titel;
				$this->Template->subHeadline = $result_tresult->tournament->tname; // Unterüberschrift setzen
				$this->Template->spielername = strip_tags($playerArr[$id]['Spielername']);
				$this->Template->dwz = $playerArr[$id]['DWZ'];
				
				// Ergebnisarray neu zusammensetzen
				$ergArr = array();
				$sumPunkte = 0; $sumWe = 0;
				if ($playerArr[$id]['Ergebnisse'])
				{
					foreach($playerArr[$id]['Ergebnisse'] as $runde => $dataArr)
					{
						foreach($dataArr as $erg)
						{
							// Punkte addieren
							switch($erg['Ergebnis'])
							{
								case '1':
									$We = \Samson\DeWIS\DeWIS::Gewinnerwartung($playerArr[$id]['DWZ'], $playerArr[$erg['Gegner']]['DWZ']);
									if($We)
									{
										$sumPunkte += 1;
										$sumWe += $We;
									}
									break;
								case '½':
									$We = \Samson\DeWIS\DeWIS::Gewinnerwartung($playerArr[$id]['DWZ'], $playerArr[$erg['Gegner']]['DWZ']);
									if($We)
									{
										$sumPunkte += .5;
										$sumWe += $We;
									}
									break;
								case '0':
									$We = \Samson\DeWIS\DeWIS::Gewinnerwartung($playerArr[$id]['DWZ'], $playerArr[$erg['Gegner']]['DWZ']);
									if($We)
									{
										$sumWe += $We;
									}
									break;
								default:
									$We = false;
							}
							$ergArr[] = array
							(
								'Runde'		=> $runde,
								'Gegner'	=> $playerArr[$erg['Gegner']]['Spielername'],
								'Scoresheet'=> $playerArr[$erg['Gegner']]['Scoresheet'],
								'DWZ'		=> $playerArr[$erg['Gegner']]['DWZ'],
								'Farbe'	    => $erg['Farbe'],
								'Ergebnis'	=> $erg['Ergebnis'],
								'We'	    => $We,
							);
						}
					}
				}
				// Summe hinzufügen
				$ergArr[] = array
				(
					'Runde'		=> 'Σ',
					'Gegner'	=> '',
					'Scoresheet'=> '',
					'DWZ'		=> '',
					'Farbe'	    => '',
					'Ergebnis'	=> $sumPunkte,
					'We'	    => $sumWe,
				);
				
				if(!$gesperrt) 
				{
					$this->Template->daten = $ergArr;
					$this->Template->scoresheet = true;
				}
			}
			else
			{
				$this->Template->daten = $playerArr;
			}

			//\Samson\DeWIS\DeWIS::debug($playerArr);
			$this->Template->turnierheader = $theader;
			$this->Template->ergebnisse = true;
			$this->Template->ergebnisliste = true;
			$this->Template->hinweis = $gesperrt;
			$this->Template->registrierung = \Samson\DeWIS\DeWIS::Registrierungshinweis();
			$Infotemplate = new \FrontendTemplate($this->infoTemplate);
			$this->Template->infobox = $Infotemplate->parse();

		}
		elseif($turniercode && !$id)
		{

			/*********************************************************
			 * Ausgabe der Auswertung
			*/

			$result_tausw = \Samson\DeWIS\DeWIS::Turnierauswertung($turniercode);

			/*********************************************************
			 * Turnierheader
			*/

			$objPage->pageTitle = 'DWZ-Auswertung '.$result_tausw->tournament->tname;
			$this->Template->subHeadline = $result_tausw->tournament->tname; // Unterüberschrift setzen

			$theader = array
			(
				'Ergebnisse'	=> sprintf('<a href="'.ALIAS_TURNIER.'/%s/Ergebnisse.html">Turnierergebnisse</a>', $result_tausw->tournament->tcode),
				'Turniercode'	=> $result_tausw->tournament->tcode,
				'Turniername'	=> $result_tausw->tournament->tname,
				'Turnierende'	=> \Samson\DeWIS\Helper::datum_mysql2php($result_tausw->tournament->finishedOn),
				'Berechnet'		=> sprintf("%s %s", \Samson\DeWIS\Helper::datum_mysql2php(substr($result_tausw->tournament->computedOn, 0, 10)), substr($result_tausw->tournament->computedOn, 11, 5)),
				'Nachberechnet'	=> $result_tausw->tournament->recomputedOn == 'NULL' || $result_tausw->tournament->recomputedOn == '' ? '&nbsp;' : sprintf("%s %s", \Samson\DeWIS\Helper::datum_mysql2php(substr($result_tausw->tournament->recomputedOn, 0, 10)), substr($result_tausw->tournament->recomputedOn, 11, 5)),
				'Auswerter1'	=> \Samson\DeWIS\DeWIS::Wertungsreferent($result_tausw->tournament->assessor1),
				'Auswerter2'	=> \Samson\DeWIS\DeWIS::Wertungsreferent($result_tausw->tournament->assessor2, false),
				'Spieler'		=> $result_tausw->tournament->cntPlayer,
				'Partien'		=> $result_tausw->tournament->cntGames,
				'Runden'		=> $result_tausw->tournament->rounds,
			);

			/*********************************************************
			 * Auswertung
			*/

			$daten = array();
			if($result_tausw->evaluation)
			{
				$z = 0;
				foreach ($result_tausw->evaluation as $t)
				{
					// Ratingdifferenz errechnen
					$ratingdiff = $t->ratingNew - $t->ratingOld;
					if($ratingdiff > 0) $ratingdiff = "+".$ratingdiff;

					// Schlüssel für Sortierung generieren
					$z++;
					$key = \StringUtil::generateAlias($t->surname.$t->firstname).$z;

					$daten[$key] = array
					(
						'PKZ'			=> $t->pid,
						'Spielername'	=> $Blacklist[$t->pid] ? '***' : \Samson\DeWIS\Helper::Spielername($t, $gesperrt),
						'Scoresheet'	=> $Blacklist[$t->pid] ? '' : ($gesperrt ? '' : sprintf("<a href=\"".ALIAS_TURNIER."/%s/%s.html\">SC</a>", $result_tausw->tournament->tcode, $t->pid)),
						'DWZ alt'		=> \Samson\DeWIS\DeWIS::DWZ($t->ratingOld, $t->ratingOldIndex),
						'DWZ neu'		=> \Samson\DeWIS\DeWIS::DWZ($t->ratingNew, $t->ratingNewIndex),
						'MglNr'         => sprintf("%04d", $t->membership),
						'VKZ'           => sprintf("<a href=\"".ALIAS_VEREIN."/%s.html\">%s</a>", $t->vkz, sprintf("%s-%s", $t->vkz, sprintf("%04d", $t->membership))),
						'ZPS'           => sprintf("%s-%s", $t->vkz, sprintf("%04d", $t->membership)),
						'Verein'        => sprintf("<a href=\"".ALIAS_VEREIN."/%s.html\">%s</a>", $t->vkz, $t->club),
						'Geburt'        => $t->yearOfBirth,
						'Geschlecht'    => ($t->gender == 'm') ? '&nbsp;' : ($t->gender == 'f' ? 'w' : strtolower($t->gender)),
						'Elo'           => ($t->elo) ? $t->elo : '-----',
						'Titel'         => $t->fideTitle ? $t->fideTitle : '&nbsp;',
						'DWZ+-'         => ($t->ratingNew && $t->ratingOld) ? $ratingdiff : "",
						'Punkte'        => \Samson\DeWIS\DeWIS::Punkte($t->points),
						'Partien'       => $t->games,
						'Ungewertet'    => $t->unratedGames == 'NULL' || !$t->unratedGames ? '&nbsp;' : $t->unratedGames,
						'E'             => \Samson\DeWIS\DeWIS::DWZ($t->ratingNew, $t->ratingNewIndex) == '-----' ? '&nbsp;' : $t->eCoefficient,
						'Ergebnis'      => sprintf("%s/%s", \Samson\DeWIS\DeWIS::Punkte($t->points), $t->games),
						'We'            => str_replace('.', ',', $t->we),
						'Niveau'        => $t->level,
						'Leistung'      => $t->achievement ? $t->achievement : '&nbsp;',
					);
				}
				// Liste sortieren (ASC)
				ksort($daten);
			}

			$this->Template->turnierheader = $theader;
			$this->Template->daten = $daten;
			$this->Template->auswertung = true;
			$this->Template->hinweis = $gesperrt;
			$this->Template->registrierung = \Samson\DeWIS\DeWIS::Registrierungshinweis();
			$Infotemplate = new \FrontendTemplate($this->infoTemplate);
			$this->Template->infobox = $Infotemplate->parse();

		}
		else
		{

			/*********************************************************
			 * Suche nicht aktiv, deshalb Suchformular initialisieren
			*/

			// ZPS der Verbände in Cookie gespeichert?
			$zpscookie = \Input::cookie('dewis-verband-zps');
			
			// Auswahl Verbände
			// DeWIS-API erwartet immer dreistellige ZPS!
			foreach($liste['verbaende'] as $key => $value)
			{
				$kurz = rtrim($value['zps'],0);
				$kurzlaenge = strlen($kurz);
				if($zpscookie) 
				{
					// Verband vorselektieren, wenn Cookie gesetzt ist
					$selected = ($zpscookie == $kurz) ? ' selected' : '';
				}
				else
				{
					// Kein oder leeres Cookie, ZPS 0 setzen
					$selected = ($kurzlaenge) ? '' : ' selected';
				}
					
				switch($kurzlaenge)
				{
					case 0:
						$opArray = array('<option value="0" class="level_0"'.$selected.'><b>0 - Alle Verbände</b></option>');
						break;	
					case 1:
						$opArray[] = sprintf('<option value="%s00" class="level_1"'.$selected.'>%s - %s</option>', $kurz, $kurz, $value['name']);
						break;
					case 2:
						$opArray[] = sprintf('<option value="%s0" class="level_2"'.$selected.'>%s - %s</option>', $kurz, $kurz, $value['name']);
						break;
					case 3:
						$opArray[] = sprintf('<option value="%s" class="level_3"'.$selected.'>%s - %s</option>', $kurz, $kurz, $value['name']);
						break;
					default:
				}
			}
			
			$this->Template->form_verbaende = implode("\n",$opArray);

			// Auswahl Zeitraum
			$aktjahr = date("Y");
			$aktmonat = date("n");
			$monate = array
			(
				1 => "Januar",
				2 => "Februar",
				3 => "März",
				4 => "April",
				5 => "Mai",
				6 => "Juni",
				7 => "Juli",
				8 => "August",
				9 => "September",
				10 => "Oktober",
				11 => "November",
				12 => "Dezember"
			);

			// Auswahl Von-Monat/Bis-Monat
			$opArray = array();
			for($x = 1; $x <= 12; $x++)
			{
				$opArray[] = ($x == $aktmonat) ? '<option value="'.sprintf("%02d",$x).'" selected>'.$monate[$x].'</option>' : '<option value="'.sprintf("%02d",$x).'">'.$monate[$x].'</option>';
			}
			$this->Template->form_monat = implode("\n",$opArray);

			// Auswahl Von-Jahr
			$opArray = array();
			for($x = 2011; $x <= $aktjahr; $x++)
			{
				$opArray[] = ($x == $aktjahr - 1) ? '<option value="'.$x.'" selected>'.$x.'</option>' : '<option value="'.$x.'">'.$x.'</option>';
			}
			$this->Template->form_vonjahr = implode("\n",$opArray);
			
			// Auswahl Bis-Jahr
			$opArray = array();
			for($x = 2011; $x <= $aktjahr; $x++)
			{
				$opArray[] = ($x == $aktjahr) ? '<option value="'.$x.'" selected>'.$x.'</option>' : '<option value="'.$x.'">'.$x.'</option>';
			}
			$this->Template->form_bisjahr = implode("\n",$opArray);
		}

	}

}
