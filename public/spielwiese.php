<html>
<head><title>Testseite</title>
<style type="text/css">
table {border-collapse:collapse;empty-cells:show}
</style>
</head>

<?php
try
{
  $client = new SOAPClient( "https://dwz.svw.info/services/files/dewis.wsdl" );
  
  //unionRatingList($client);
  //tournament($client);
  //tournamentCard($client);
  //tournamentCardByZPS($client);
  //tournamentPairings($client);
  //searchByName($client);
  //tournamentsByPeriod($client);
  //bestOfFed($client);
  organizations($client);
}
catch (SOAPFault $f) {
  print $f->faultstring;
}



function bestOfFed($client) {
    echo '<h1>DWZ-Bestenliste</h1>';
    
    // VKZ des Bezirks / (U-)LV
    // Achtung: diese Abfrage ist noch sehr langsam
    $ratingList = $client->bestOfFederation("C0600",30);
    
    echo "<h2>".$ratingList->organization->vkz." ".$ratingList->organization->name."</h2>";
  echo "<table border='1'>";
  
  foreach ($ratingList->members as $m) {
        echo "<tr>";
        echo "<td>".$m->pid."</td>";
        echo "<td>".$m->surname."</td>";
        echo "<td>".$m->firstname."</td>";
        echo "<td>".$m->title."</td>";
        echo "<td>".$m->vkz."</td>";
        echo "<td>".$m->club."</td>";
        echo "<td>".$m->state."</td>";
        echo "<td>".$m->membership."</td>";
        echo "<td align='center'>".$m->rating."-".$m->ratingIndex."</td>";
        echo "<td>".$m->idfide."</td>";
        echo "<td>".$m->elo."</td>";
        echo "<td>".$m->fideTitle."</td>";
        echo "<td>".$m->tcode."</td>";
        echo "<td>".$m->finishedOn."</td>";
        echo "</tr>";
  }
  echo "</table>";
}

function tournamentsByPeriod($client) {
    echo '<h1>Turniere in einem Zeitraum</h1>';
    
    $result = $client->tournamentsByPeriod("2013-01-01","2013-12-31","000", true, "", "Staufer" );
    
    echo "<table border='1'>";
    foreach ($result->tournaments as $t) {
        echo "<tr>";
        echo "<td>".$t->tcode."</td>";
        echo "<td>".$t->tname."</td>";
        echo "<td>".$t->rounds."</td>";
        echo "<td>".$t->finishedOn."</td>";
        echo "<td>".$t->computedOn."</td>";
        echo "<td>".$t->recomputedOn."</td>";
        echo "<td>".$t->cntPlayer."</td>";
        echo "<td>".$t->assessor1."</td>";
        echo "<td>".$t->assessor2."</td>";
        echo "</tr>";
    }
    echo "</table>";
}


function searchByName($client) {
    echo '<h1>Suche nach Name, Vorname</h1>';
    
    // nachname, vorname, Start, Anzahl Datensaetze
    // vorname kann leer sein, ebenso Start und Anzahl
    $members = $client->searchByName("hoppe", "", 0,30);
    
  echo "<table border='1'>";
  
  foreach ($members->members as $m) {
        echo "<tr>";
        echo "<td>".$m->pid."</td>";
        echo "<td>".$m->surname."</td>";
        echo "<td>".$m->firstname."</td>";
        echo "<td>".$m->title."</td>";
        echo "<td>".$m->vkz."</td>";
        echo "<td>".$m->club."</td>";
        echo "<td>".$m->membership."</td>";
        echo "<td>".$m->state."</td>";
        echo "<td align='center'>".$m->rating."-".$m->ratingIndex."</td>";
        echo "<td>".$m->tcode."</td>";
        echo "<td>".$m->finishedOn."</td>";
        echo "</tr>";
  }
  echo "</table>";
}

function tournamentPairings($client) {
    echo '<h1>Spielpaarungen eines Turniers</h1>';
    
    // turniercode
    $tournament = $client->tournamentPairings("B917-K00-T5+");
    
    echo "<h3>".$tournament->tournament->tname." (".$tournament->tournament->tcode.") </h3>";
    echo "<dl>";
    echo "<dt>beendet am:</dt>";
    echo "<dd>".$tournament->tournament->finishedOn."</dd>";
    echo "<dt>berechnet am:</dt>";
    echo "<dd>".$tournament->tournament->computedOn."</dd>";
    echo "<dt>zuletzt berechnet am:</dt>";
    echo "<dd>".$tournament->tournament->recomputedOn."</dd>";
    echo "<dt>ID Erstauswerter:</dt>";
    echo "<dd>".$tournament->tournament->assessor1."</dd>";
    echo "<dt>ID Zweitauswerter:</dt>";
    echo "<dd>".$tournament->tournament->assessor2."</dd>";
    echo "<dt>Anzahl Spieler</dt>";
    echo "<dd>".$tournament->tournament->cntPlayer."</dd>";
    echo "<dt>Anzahl Partien</dt>";
    echo "<dd>".$tournament->tournament->cntGames."</dd>";
    echo "</dl>";
    
    if (is_array($tournament->rounds)) {
        foreach($tournament->rounds as $r) {
            echo '<h3>Runde '.$r->no.'</h3>';
            if (!empty($r->appointment)) {
                echo '<h4>Datum: '.$r->appointment.'</h4>';
            }
            
            echo '<table>';
            foreach ($r->games as $g) {
                echo '<tr>';
                
                echo '<td>'.$g->idWhite.'</td>';
                echo '<td>'.$g->white.'</td>';
                echo '<td>-</td>';
                echo '<td>'.$g->idBlack.'</td>';
                echo '<td>'.$g->black.'</td>';
                echo '<td>'.$g->result.'</td>';
                
                echo '</tr>';
            }
            echo '</table>';
        }
    }
    else {
        echo "<p>keine Paarungen gespeichert</p>";
    }
}

function tournamentPairingsByPlayer($client) {
    echo '<h1>Spielpaarungen eines Turniers</h1>';
    
    // turniercode
    $tournament = $client->tournamentPairingsByPlayer("B114-700-MMX","10106263");
    
    echo "<h3>".$tournament->tournament->tname." (".$tournament->tournament->tcode.") </h3>";
        echo "<dl>";
        echo "<dt>beendet am:</dt>";
        echo "<dd>".$tournament->tournament->finishedOn."</dd>";
        echo "<dt>berechnet am:</dt>";
        echo "<dd>".$tournament->tournament->computedOn."</dd>";
        echo "<dt>zuletzt berechnet am:</dt>";
        echo "<dd>".$tournament->tournament->recomputedOn."</dd>";
        echo "<dt>Auswerter:</dt>";
        echo "<dd>".$tournament->tournament->assessor."</dd>";
        echo "<dt>Anzahl Runden</dt>";
        echo "<dd>".$tournament->tournament->rounds."</dd>";
        echo "<dt>Anzahl Spieler</dt>";
        echo "<dd>".$tournament->tournament->cntPlayer."</dd>";
        echo "<dt>Anzahl Partien</dt>";
        echo "<dd>".$tournament->tournament->cntGames."</dd>";
        echo "</dl>";
    
        if (is_array($tournament->games)) {
            echo '<table>';
            foreach($tournament->games as $g) {
                echo '<tr>';
                
                echo '<td>'.$g->idWhite.'</td>';
                echo '<td>'.$g->white.'</td>';
                echo '<td>-</td>';
                echo '<td>'.$g->idBlack.'</td>';
                echo '<td>'.$g->black.'</td>';
                echo '<td>'.$g->result.'</td>';
                
                echo '</tr>';
            }
            echo '</table>';
        }
        else {
            echo "<p>keine Paarungen gespeichert</p>";
        }
}

function tournamentCardByZPS($client) {
    echo '<h1>Turnierkarte nach ZPS (Format: <em>VKZ</em>-<em>Mitgliedsnr.</em>)</h1>';
    
    // ZPS-Nummer: Format VKZ-Mitgliedsnr
    $tcard = $client->tournamentCardForZps("30052-1083");
  
    echo "<dl><dt>".$tcard->member->surname.", ".$tcard->member->firstname;
    if (!empty($tcard->member->title)) {
        echo ", ".$tcard->member->title;
    }
    echo "</dt>";
    echo "<dd>Geburtsjahr: ".$tcard->member->yearOfBirth."</dd>";
    echo "<dd>Geschlecht: ".$tcard->member->gender."</dd>";
    echo "<dd>ID: ".$tcard->member->pid."</dd>";
    echo "<dd>DWZ: ".$tcard->member->rating."-".$tcard->member->ratingIndex."</dd>";
    echo "<dd>FIDE-ID: ".$tcard->member->idfide."</dd>";
    echo "<dd>Elo: ".$tcard->member->elo."</dd>";
    echo "<dd>FIDE-Titel: ".$tcard->member->fideTitle."</dd>";
    echo "<dd>FIDE-Nation: ".$tcard->member->fideNation."</dd>";
    echo "</dl>";

    echo "<dl><dt>Ranglisten-Plazierungen:</dt>";
    foreach ($tcard->ranking[1] as $r){
        echo "<dd>".$r->vkz." ".$r->organization.": ".$r->rank. ($r->assessor == '' ? '' : " (Wert.-Ref: ".$r->assessor.")")."</dd>";
    }
    echo "</dl>";
    
    echo "<h4>Mitgliedschaften</h3>";
    echo "<table border='1'>";
    
    foreach ($tcard->memberships as $m) {
        echo "<tr>";
        echo "<td>".$m->vkz."</td>";
        echo "<td>".$m->club."</td>";
        echo "<td>".$m->membership."</td>";
        echo "<td>".$m->state."</td>";
        echo "<td>".$m->assessor."</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h4>Turniere</h4>";
    
    echo "<table border='1'>";
  
  foreach ($tcard->tournaments as $t) {
        echo "<tr>";
        echo "<td>".$t->tcode."</td>";
        echo "<td>".$t->tname."</td>";
        echo "<td>".$t->ratingOld."</td>";
        echo "<td>".$t->ratingOldIndex."</td>";
        echo "<td>".$t->points."</td>";
        echo "<td>".$t->games."</td>";
        echo "<td>".$t->unratedGames."</td>";
        echo "<td>".$t->we."</td>";
        echo "<td>".$t->achievement."</td>";
        echo "<td>".$t->eCoefficient."</td>";
        echo "<td>".$t->ratingNew."</td>";
        echo "<td>".$t->ratingNewIndex."</td>";
        echo "<td>".$t->level."</td>";
        echo "</tr>";
    }
    echo "</table>";
}

function tournamentCard($client) {
    echo '<h1>Turnierkarte nach ID des Mitglieds</h1>';
    
    // ID des Mitglieds
    $tcard = $client->tournamentCardForId(10199111);
    
    echo "<dl><dt>".$tcard->member->surname.", ".$tcard->member->firstname;
    if (!empty($tcard->member->title)) {
        echo ", ".$tcard->member->title;
    }
    echo "</dt>";
    echo "<dd>Geburtsjahr: ".$tcard->member->yearOfBirth."</dd>";
    echo "<dd>Geschlecht: ".$tcard->member->gender."</dd>";
    echo "<dd>ID: ".$tcard->member->pid."</dd>";
    echo "<dd>DWZ: ".$tcard->member->rating."-".$tcard->member->ratingIndex."</dd>";
    echo "<dd>FIDE-ID: ".$tcard->member->idfide."</dd>";
    echo "<dd>Elo: ".$tcard->member->elo."</dd>";
    echo "<dd>FIDE-Titel: ".$tcard->member->fideTitle."</dd>";
    echo "<dd>FIDE-Nation: ".$tcard->member->fideNation."</dd>";
    echo "</dl>";

    echo "<dl><dt>Ranglisten-Plazierungen:</dt>";
    foreach ($tcard->ranking[1] as $r){
        echo "<dd>".$r->vkz." ".$r->organization.": ".$r->rank. ($r->assessor == '' ? '' : " (Wert.-Ref: ".$r->assessor.")")."</dd>";
    }
    echo "</dl>";
    
    echo "<h4>Mitgliedschaften</h3>";
    echo "<table>";
    
    foreach ($tcard->memberships as $m) {
        echo "<tr>";
        echo "<td>".$m->vkz."</td>";
        echo "<td>".$m->club."</td>";
        echo "<td>".$m->membership."</td>";
        echo "<td>".$m->state."</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h4>Turniere</h4>";
    
    echo "<table border='1'>";
  
  foreach ($tcard->tournaments as $t) {
        echo "<tr>";
        echo "<td>".$t->tcode."</td>";
        echo "<td>".$t->tname."</td>";
        echo "<td>".$t->ratingOld."</td>";
        echo "<td>".$t->ratingOldIndex."</td>";
        echo "<td>".$t->points."</td>";
        echo "<td>".$t->games."</td>";
        echo "<td>".$t->unratedGames."</td>";
        echo "<td>".$t->we."</td>";
        echo "<td>".$t->achievement."</td>";
        echo "<td>".$t->eCoefficient."</td>";
        echo "<td>".$t->ratingNew."</td>";
        echo "<td>".$t->ratingNewIndex."</td>";
        echo "<td>".$t->level."</td>";
        echo "</tr>";
    }
    echo "</table>";
}

function tournament($client) {
    echo '<h1>Turnierauswertung</h1>';
    
    // Turniercode
    $tournament = $client->tournament("B813-830-HT1");

    echo "<h3>".$tournament->tournament->tname." (".$tournament->tournament->tcode.") </h3>";
    echo "<dl>";
    echo "<dt>beendet am:</dt>";
    echo "<dd>".$tournament->tournament->finishedOn."</dd>";
    echo "<dt>berechnet am:</dt>";
    echo "<dd>".$tournament->tournament->computedOn."</dd>";
    echo "<dt>zuletzt berechnet am:</dt>";
    echo "<dd>".$tournament->tournament->recomputedOn."</dd>";
    echo "<dt>ID Auswerter 1:</dt>";
    echo "<dd>".$tournament->tournament->assessor1."</dd>";
    echo "<dt>ID Auswerter 2:</dt>";
    echo "<dd>".$tournament->tournament->assessor2."</dd>";
    echo "<dt>Anzahl Spieler</dt>";
    echo "<dd>".$tournament->tournament->cntPlayer."</dd>";
    echo "<dt>Anzahl Partien</dt>";
    echo "<dd>".$tournament->tournament->cntGames."</dd>";
    echo "</dl>";
        
  echo "<table border='1'>";
  
  foreach ($tournament->evaluation as $m) {
        echo "<tr>";
        echo "<td>".$m->pid."</td>";
        echo "<td>".$m->surname."</td>";
        echo "<td>".$m->firstname."</td>";
        echo "<td>".$m->ratingOld."</td>";
        echo "<td>".$m->ratingOldIndex."</td>";
        echo "<td>".$m->points."</td>";
        echo "<td>".$m->games."</td>";
        echo "<td>".$m->unratedGames."</td>";
        echo "<td>".$m->we."</td>";
        echo "<td>".$m->achievement."</td>";
        echo "<td>".$m->eCoefficient."</td>";
        echo "<td>".$m->ratingNew."</td>";
        echo "<td>".$m->ratingNewIndex."</td>";
        echo "<td>".$m->level."</td>";
        echo "</tr>";
  }
  echo "</table>";
}

function unionRatingList($client) {
    echo '<h1>DWZ-Liste eines Vereins</h1>';
    
    // VKZ des Vereins
    $unionRatingList = $client->unionRatingList("C0560");
  echo "<h3>".$unionRatingList->union->name." (".$unionRatingList->union->vkz.") </h3>";
  echo "<dt>";
  echo "<dt>ID Wertungsreferent:</dt><dd>".$unionRatingList->ratingOfficer."</dd>";
  echo "</dl>";
  echo "<table border='1'>";
  
  foreach ($unionRatingList->members as $m) {
        echo "<tr>";
        echo "<td>".$m->pid."</td>";
        echo "<td>".$m->surname."</td>";
        echo "<td>".$m->firstname."</td>";
        echo "<td>".$m->title."</td>";
        echo "<td>".$m->state."</td>";
        echo "<td>".$m->membership."</td>";
        echo "<td align='center'>".$m->rating."-".$m->ratingIndex."</td>";
        echo "<td>".$m->tcode."</td>";
        echo "<td>".$m->finishedOn."</td>";
        echo "</tr>";
  }
  echo "</table>";
}

function organizations($client)
{
	echo '<h1>Liste aller Verbände und Vereine</h1>';
	
	// VKZ des Verbandes
	$result = $client->organizations("00000");
	echo "<h2>Array vor der Modifizierung</h2>";
	echo "<pre>";
	print_r($result);
	echo "</pre>";
	// Objekt modifizieren
	sub_org($result);
	//echo "<h2>Array nach der Modifizierung</h2>";
	//echo "<pre>";
	//print_r($result);
	//echo "</pre>";

	// Landesverbände durchlaufen
	for($index_lv = 0; $index_lv < count($result->children); $index_lv++)
	{
		echo $result->children[$index_lv]->club;
		echo "<br>";
		// Bezirke durchlaufen
		for($index_bezirk = 0; $index_bezirk < count($result->children[$index_lv]->children); $index_bezirk++)
		{
			echo "- ";
			echo $result->children[$index_lv]->children[$index_bezirk]->club;
			echo "<br>";
			switch($result->children[$index_lv]->children[$index_bezirk]->vkz)
			{
				case 'C01':
					// Vereine C01 definieren
					$vkz_found = true;
					$vereine = array
					(
						(object) array('id' => '2549', 'club' => 'Post-SV Ulm','vkz' =>'C0101','p' =>'180','assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2546', 'club' => 'SF Vöhringen','vkz' =>'C0104','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2545', 'club' => 'TSV Langenau','vkz' =>'C0105','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2543', 'club' => 'SF Blaustein','vkz' =>'C0107','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2541', 'club' => 'SC Obersulmetingen','vkz' =>'C0109','p' => '180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '4287', 'club' => 'TSG Ehingen 1848 e.V.','vkz' =>'C010A','p' => '180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2540', 'club' => 'TSV Berghülen','vkz' =>'C0110','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2537', 'club' => 'TSV Laichingen','vkz' =>'C0113','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2534', 'club' => 'TG Biberach','vkz' =>'C0116','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2532', 'club' => 'TSV 1880 Neu-Ulm','vkz' =>'C0118','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2526', 'club' => 'SC Laupheim 1962 e.V.','vkz' =>'C0124','p' => '180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2525', 'club' => 'SF Riedlingen','vkz' =>'C0125','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2523', 'club' => 'TSV Seissen e.V.','vkz' =>'C0127','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2519', 'club' => 'SC Weiße Dame Ulm e.V.','vkz' =>'C0131','p' => '180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2513', 'club' => 'SV Jedesheim 1921','vkz' =>'C0137','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2512', 'club' => 'TV Wiblingen','vkz' =>'C0138','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2511', 'club' => 'SV Steinhausen','vkz' =>'C0139','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2510', 'club' => 'TSV Reute e.V.','vkz' =>'C0140','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2508', 'club' => 'TSV Westerstetten','vkz' =>'C0142','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2507', 'club' => 'SV Thalfingen','vkz' =>'C0143','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2548', 'club' => 'SK Markdorf','vkz' =>'C0102','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2544', 'club' => 'SC Lindau','vkz' =>'C0106','p' =>'180','assessor' => '10033089', 'children' => array()),
						(object) array('id' => '4521', 'club' => 'TG Bad Waldsee 1848','vkz' =>'C010C','p' => '180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2539', 'club' => 'SC Tettnang','vkz' =>'C0111','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2538', 'club' => 'SC Wangen','vkz' =>'C0112','p' =>'180','assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2536', 'club' => 'SV Friedrichshafen','vkz' =>'C0114','p' => '180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2535', 'club' => 'SF Ravensburg','vkz' =>'C0115','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2533', 'club' => 'SF Wetzisreute','vkz' =>'C0117','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2530', 'club' => 'SF Mengen','vkz' =>'C0120','p' =>'180','assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2529', 'club' => 'SV Weingarten','vkz' =>'C0121','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2528', 'club' => 'SK Leutkirch','vkz' =>'C0122','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2520', 'club' => 'SC Bad Schussenried e.V.','vkz' =>'C0130', 'p' => '180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2518', 'club' => 'SC Bad Saulgau','vkz' =>'C0132','p' =>'180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2517', 'club' => 'SC Weiler im Allgäu e. V.','vkz' =>'C0133','p' => '180', 'assessor' => '10033089', 'children' => array()),
						(object) array('id' => '2509', 'club' => 'SF Ertingen','vkz' =>'C0141','p' =>'180', 'assessor' => '10033089', 'children' => array())
					);
					break;
				case 'C02':
					// Vereine C02 definieren
					$vkz_found = true;
					$vereine = array
					(
						(object) array('id' => '2493', 'club' => 'SC Möhringen 1961','vkz' =>'C0215','p' =>'179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2492', 'club' => 'SG Donautal Tuttlingen','vkz' =>'C0216','p' => '179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2486', 'club' => 'SV Rottweil','vkz' =>'C0222','p' =>'179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2482', 'club' => 'SR Spaichingen','vkz' =>'C0226','p' =>'179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2478', 'club' => 'SR Heuberg-Gosheim','vkz' =>'C0230','p' => '179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2476', 'club' => 'SV Trossingen','vkz' =>'C0232','p' =>'179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2496', 'club' => 'SK Horb','vkz' =>'C0211','p' =>'179','assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2495', 'club' => 'SC Klosterreichenbach','vkz' =>'C0212','p' => '179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2489', 'club' => 'SC Oberndorf','vkz' =>'C0219','p' =>'179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2472', 'club' => 'SF Dornstetten-Pfalzgrafenweiler e.V.','vkz' => 'C0238', 'p' => '179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2468', 'club' => 'SG Schramberg-Lauterbach','vkz' =>'C0242', 'p' => '179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2505', 'club' => 'SV Balingen','vkz' =>'C0202','p' =>'179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2504', 'club' => 'SC Bisingen-Steinhofen','vkz' =>'C0203', 'p' => '179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2502', 'club' => 'SF Geislingen 1990 e.V.','vkz' =>'C0205','p' => '179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2501', 'club' => 'SG Turm Albstadt 1902 e.V.','vkz' =>'C0206', 'p' => '179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2498', 'club' => 'SC Hechingen','vkz' =>'C0209','p' =>'179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2497', 'club' => 'SC Heinstetten','vkz' =>'C0210','p' =>'179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2490', 'club' => 'SC Nusplingen','vkz' =>'C0218','p' =>'179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2488', 'club' => 'SC Rangendingen','vkz' =>'C0220','p' =>'179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2480', 'club' => 'SV Stockenhausen-Frommern','vkz' =>'C0228', 'p' => '179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2474', 'club' => 'Sfr. Winterlingen 1966 e.V.','vkz' =>'C0235', 'p' => '179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2473', 'club' => 'SV Schömberg e.V.','vkz' =>'C0237','p' =>'179', 'assessor' => '10028978', 'children' => array()),
						(object) array('id' => '2469', 'club' => 'SG Dotternhausen','vkz' =>'C0241','p' =>'179', 'assessor' => '10028978', 'children' => array())
					);
					break;
				case 'C03':
					// Vereine C03 definieren
					$vkz_found = true;
					$vereine = array
					(
						(object) array('id' => '2467', 'club' => 'SV Altbach e.V.','vkz' =>'C0301','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2466', 'club' => 'Schachgemeinschaft Filder','vkz' =>'C0302', 'p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2465', 'club' => 'SF Deizisau','vkz' =>'C0303','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2464', 'club' => 'TSV Denkendorf','vkz' =>'C0304','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2463', 'club' => 'SV Dicker Turm Esslingen','vkz' =>'C0305', 'p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2462', 'club' => 'TSV/RSK Esslingen','vkz' =>'C0306','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2461', 'club' => 'TSG Esslingen','vkz' =>'C0307','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2460', 'club' => 'TSV Grafenberg','vkz' =>'C0308','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2458', 'club' => 'SF Nabern','vkz' =>'C0310','p' =>'178','assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2457', 'club' => 'SF 47 Neckartenzlingen','vkz' =>'C0311','p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2456', 'club' => 'SC Ostfildern 1952 e.V.','vkz' =>'C0312','p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2455', 'club' => 'SV Nürtingen 1920','vkz' =>'C0313','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2454', 'club' => 'SF Plochingen','vkz' =>'C0314','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2452', 'club' => 'SV 1947 Wendlingen','vkz' =>'C0316','p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2450', 'club' => 'SK Wernau','vkz' =>'C0318','p' =>'178','assessor' => '10114588', 'children' => array()),
						(object) array('id' => '4519', 'club' => 'Schachritter Kirchheim/Teck','vkz' =>'C030B', 'p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2446', 'club' => 'SV Ebersbach','vkz' =>'C0322','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2444', 'club' => 'SV Faurndau','vkz' =>'C0324','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2443', 'club' => 'SC Geislingen 1881','vkz' =>'C0325','p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2441', 'club' => 'SF 1876 Göppingen','vkz' =>'C0327','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2440', 'club' => 'SC Kirchheim/Teck','vkz' =>'C0328','p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2438', 'club' => 'TSG Salach','vkz' =>'C0330','p' =>'178','assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2420', 'club' => 'TSG Zell u.A.','vkz' =>'C0351','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2412', 'club' => 'Ssg Fils-Lauter e. V.','vkz' =>'C0359','p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2434', 'club' => 'SV Urach','vkz' =>'C0334','p' =>'178','assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2433', 'club' => 'SF Ammerbuch','vkz' =>'C0335','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2432', 'club' => 'SV Dettingen Erms','vkz' =>'C0336','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2430', 'club' => 'SC BW Kirchentellinsfurt','vkz' =>'C0338', 'p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2429', 'club' => 'Rochade Metzingen e.V.','vkz' =>'C0339','p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2426', 'club' => 'SF Pfullingen','vkz' =>'C0344','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2425', 'club' => 'SV Pliezhausen','vkz' =>'C0345','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2424', 'club' => 'SV Reutlingen','vkz' =>'C0346','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2422', 'club' => 'SC Steinlach','vkz' =>'C0349','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2421', 'club' => 'SV Tübingen 1870 e.V.','vkz' =>'C0350','p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2417', 'club' => 'SF Springer Rottenburg','vkz' =>'C0354','p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2416', 'club' => 'SF Lichtenstein','vkz' =>'C0355','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2415', 'club' => 'SG Schönbuch','vkz' =>'C0356','p' =>'178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2414', 'club' => 'SK Bebenhausen 1992','vkz' =>'C0357','p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2413', 'club' => 'Schwarz Weiß Münsingen','vkz' =>'C0358','p' => '178', 'assessor' => '10114588', 'children' => array()),
						(object) array('id' => '2410', 'club' => 'SG Königskinder Hohentübingen e.V.','vkz' => 'C0361', 'p' => '178', 'assessor' => '10114588', 'children' => array())
					);
					break;
				case 'C04':
					// Vereine C04 definieren
					$vkz_found = true;
					$vereine = array
					(
						(object) array('id' => '2409', 'club' => 'SV Aalen-Ellwangen e.V.','vkz' =>'C0401', 'p' => '177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2408', 'club' => 'SC Tannhausen 1986 e.V.','vkz' =>'C0402','p' => '177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2407', 'club' => 'SV Unterkochen','vkz' =>'C0403','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2405', 'club' => 'SV Crailsheim','vkz' =>'C0405','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2404', 'club' => 'SC 1875 Ellwangen','vkz' =>'C0406','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2391', 'club' => 'SV Oberkochen','vkz' =>'C0419','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2379', 'club' => 'SC Rainau','vkz' =>'C0431','p' =>'177','assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2374', 'club' => 'SC Bopfingen e.V.','vkz' =>'C0436','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2372', 'club' => 'SV Königsspringer Stödtlen','vkz' =>'C0438', 'p' => '177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2402', 'club' => 'SV Giengen','vkz' =>'C0408','p' =>'177','assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2400', 'club' => 'SK Heidenheim','vkz' =>'C0410','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2398', 'club' => 'SC Heidenheim - Schnaitheim','vkz' =>'C0412', 'p' => '177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2396', 'club' => 'RSV Heuchlingen','vkz' =>'C0414','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2395', 'club' => 'SF Königsbronn','vkz' =>'C0415','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2384', 'club' => 'SK Sontheim/Brenz e.V.','vkz' =>'C0426','p' => '177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2401', 'club' => 'SC Grunbach','vkz' =>'C0409','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2397', 'club' => 'SF Heubach','vkz' =>'C0413','p' =>'177','assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2394', 'club' => 'SC Leinzell','vkz' =>'C0416','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2392', 'club' => 'SF 90 Spraitbach e.V.','vkz' =>'C0418','p' => '177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2390', 'club' => 'SC Plüderhausen','vkz' =>'C0420','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2389', 'club' => 'Schachunion Schorndorf e.V.','vkz' =>'C0421', 'p' => '177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2388', 'club' => 'SG Schwäbisch Gmünd 1872 e.V','vkz' =>'C0422', 'p' => '177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2386', 'club' => 'SG Bettringen','vkz' =>'C0424','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2383', 'club' => 'TSF Welzheim','vkz' =>'C0427','p' =>'177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2377', 'club' => 'SF Waldstetten 1982','vkz' =>'C0433','p' => '177', 'assessor' => '10207830', 'children' => array()),
						(object) array('id' => '2371', 'club' => 'TSV Alfdorf e.V.','vkz' =>'C0439','p' =>'177', 'assessor' => '10207830', 'children' => array())
					);
					break;
				case 'C05':
					// Vereine C05 definieren
					$vkz_found = true;
					$vereine = array
					(
						(object) array('id' => '2365', 'club' => 'TSF Ditzingen','vkz' =>'C0506','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '4601', 'club' => 'Zentrumsbauer Stuttgart','vkz' =>'C050B', 'p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2350', 'club' => 'Stuttgarter SF 1879','vkz' =>'C0521','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2348', 'club' => 'DJK Stuttgart-Süd','vkz' =>'C0523','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2343', 'club' => 'SG Fasanenhof','vkz' =>'C0528','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2339', 'club' => 'SC Sillenbuch','vkz' =>'C0532','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2333', 'club' => 'SV Stuttgart-Wolfbusch 1956 e.V.','vkz' => 'C0538', 'p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2332', 'club' => 'SSV Zuffenhausen','vkz' =>'C0539','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2323', 'club' => 'SK e4 Gerlingen','vkz' =>'C0548','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2319', 'club' => 'SC Schachmatt Botnang','vkz' =>'C0552','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2317', 'club' => 'GSV Hemmingen','vkz' =>'C0554','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2313', 'club' => 'TSV Heumaden','vkz' =>'C0560','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2310', 'club' => 'SC Feuerbach e. V.','vkz' =>'C0563','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '4179', 'club' => 'TV Zazenhausen','vkz' =>'C0566','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '4231', 'club' => 'DJK Sportbund Stuttgart e.V.','vkz' =>'C0568', 'p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2370', 'club' => 'SC Affalterbach','vkz' =>'C0501','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2369', 'club' => 'SV Backnang','vkz' =>'C0502','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2364', 'club' => 'SV Fellbach','vkz' =>'C0507','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2361', 'club' => 'SK Korb 1948','vkz' =>'C0510','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2357', 'club' => 'SC Murrhardt 1948 e.V.','vkz' =>'C0514','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2356', 'club' => 'SF Oeffingen e.V.','vkz' =>'C0515','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2354', 'club' => 'SK Schmiden/Cannstatt','vkz' =>'C0517','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2353', 'club' => 'SV Schwaikheim','vkz' =>'C0518','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2341', 'club' => 'Mönchfelder SV 1967','vkz' =>'C0530','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2331', 'club' => 'SC Waiblingen 1921','vkz' =>'C0540','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2327', 'club' => 'SC Winnenden e.V.','vkz' =>'C0544','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2320', 'club' => 'SpVgg Rommelshausen','vkz' =>'C0551','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2316', 'club' => 'SF Hohenacker e.V.','vkz' =>'C0555','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2308', 'club' => 'Schach-Pinguine Sulzbach','vkz' =>'C0565', 'p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2367', 'club' => 'Spvgg Böblingen','vkz' =>'C0504','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2366', 'club' => 'SC Böblingen 1975 e.V.','vkz' =>'C0505','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2362', 'club' => 'SV Herrenberg e.V.','vkz' =>'C0509','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '4417', 'club' => 'Schach-Kids Bernhausen e.V.','vkz' =>'C050A', 'p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2359', 'club' => 'SC Leinfelden','vkz' =>'C0512','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2358', 'club' => 'SV Leonberg 1978 eV','vkz' =>'C0513','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2352', 'club' => 'VfL Sindelfingen','vkz' =>'C0519','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2351', 'club' => 'SC Stetten a.d.F.','vkz' =>'C0520','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2349', 'club' => 'TSV Schönaich','vkz' =>'C0522','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2346', 'club' => 'SC Aidlingen','vkz' =>'C0525','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2340', 'club' => 'SV Weil der Stadt','vkz' =>'C0531','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2337', 'club' => 'SV Nagold','vkz' =>'C0534','p' =>'176','assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2335', 'club' => 'SGem Vaihingen-Rohr','vkz' =>'C0536','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2326', 'club' => 'Spvgg Renningen','vkz' =>'C0545','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2324', 'club' => 'SC Magstadt','vkz' =>'C0547','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2315', 'club' => 'TSV Heimsheim e.V.','vkz' =>'C0558','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2314', 'club' => 'Vardar Sindelfingen','vkz' =>'C0559','p' => '176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '2309', 'club' => 'TSV Simmozheim','vkz' =>'C0564','p' =>'176', 'assessor' => '10033785', 'children' => array()),
						(object) array('id' => '4211', 'club' => 'SSV Turm Holzgerlingen','vkz' =>'C0567','p' => '176', 'assessor' => '10033785', 'children' => array())
					);
					break;
				case 'C06':
					// Vereine C06 definieren
					$vkz_found = true;
					$vereine = array
					(
						(object) array('id' => '2307', 'club' => 'SV Bad Friedrichshall','vkz' =>'C0601','p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2306', 'club' => 'SV Bad Rappenau','vkz' =>'C0602','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2305', 'club' => 'SC Blauer Turm Bad Wimpfen','vkz' =>'C0603', 'p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2303', 'club' => 'VfL Eberstadt','vkz' =>'C0605','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2302', 'club' => 'TG Forchtenberg','vkz' =>'C0606','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '4581', 'club' => 'Schachabteilung Post-SG Schwäbisch Hall','vkz' => 'C060A', 'p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2298', 'club' => 'TSG Heilbronn 1845 e.V.','vkz' =>'C0610','p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2297', 'club' => 'Heilbronner SV','vkz' =>'C0611','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2295', 'club' => 'SV 23 Böckingen','vkz' =>'C0613','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2291', 'club' => 'SC Künzelsau','vkz' =>'C0617','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2290', 'club' => 'SK Lauffen','vkz' =>'C0618','p' =>'175','assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2289', 'club' => 'SV Leingarten','vkz' =>'C0619','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2285', 'club' => 'SG Meimsheim-Güglingen','vkz' =>'C0623', 'p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2281', 'club' => 'TSG Öhringen','vkz' =>'C0627','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2279', 'club' => 'SK Schwäbisch Hall','vkz' =>'C0629','p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2276', 'club' => 'TSV Untergruppenbach','vkz' =>'C0632','p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2273', 'club' => 'SC Widdern','vkz' =>'C0635','p' =>'175','assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2272', 'club' => 'TSV Willsbach','vkz' =>'C0636','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2269', 'club' => 'SC Neckarsulm e.V.','vkz' =>'C0639','p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2266', 'club' => 'SV Gaildorf/Fichtenberg','vkz' =>'C0642', 'p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2259', 'club' => 'TSV Gerabronn','vkz' =>'C0649','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2258', 'club' => 'SV Rochade Neuenstadt','vkz' =>'C0650','p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2256', 'club' => 'SF HN-Biberach 1978 e.V.','vkz' =>'C0652', 'p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2250', 'club' => 'TSV Schwaigern','vkz' =>'C0658','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2247', 'club' => 'Lachender Turm Schwäbisch Hall','vkz' =>'C0661', 'p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '4243', 'club' => 'SF Schwaigern','vkz' =>'C0664','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '4267', 'club' => 'udk SV Ivanchuk Hn Vu Ter','vkz' =>'C0665','p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2304', 'club' => 'SV Besigheim','vkz' =>'C0604','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2301', 'club' => 'SV Gemmrigheim','vkz' =>'C0607','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2300', 'club' => 'SK Sachsenheim','vkz' =>'C0608','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2284', 'club' => 'SF Möglingen 1976','vkz' =>'C0624','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2283', 'club' => 'TSV Münchingen','vkz' =>'C0625','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2282', 'club' => 'SG Ludwigsburg 1919','vkz' =>'C0626','p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2278', 'club' => 'TSG Steinheim','vkz' =>'C0630','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2277', 'club' => 'SC Tamm 74','vkz' =>'C0631','p' =>'175','assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2275', 'club' => 'SVG Vaihingen/Enz','vkz' =>'C0633','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2271', 'club' => 'SC Erdmannhausen','vkz' =>'C0637','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2270', 'club' => 'SV Markgröningen','vkz' =>'C0638','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2268', 'club' => 'SV Marbach','vkz' =>'C0640','p' =>'175','assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2263', 'club' => 'SF 59 Kornwestheim','vkz' =>'C0645','p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2261', 'club' => 'SC Asperg','vkz' =>'C0647','p' =>'175','assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2260', 'club' => 'SK Bietigheim-Bissingen','vkz' =>'C0648', 'p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2254', 'club' => 'SC Ingersheim e.V.','vkz' =>'C0654','p' => '175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2252', 'club' => 'SF Freiberg','vkz' =>'C0656','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2251', 'club' => 'SV Oberstenfeld','vkz' =>'C0657','p' =>'175', 'assessor' => '10252948', 'children' => array()),
						(object) array('id' => '2249', 'club' => 'SV Mundelsheim','vkz' =>'C0659','p' =>'175', 'assessor' => '10252948', 'children' => array())
					);
					break;
				default:
					$vkz_found = false;
					break;
			}

			if($vkz_found)
			{
				// Vereine einhängen
				$result->children[$index_lv]->children[$index_bezirk]->children = $vereine;
			}

			// Kreise durchlaufen
			for($index_kreis = 0; $index_kreis < count($result->children[$index_lv]->children[$index_bezirk]->children); $index_kreis++)
			{
				echo "-- ";
				echo $result->children[$index_lv]->children[$index_bezirk]->children[$index_kreis]->club;
				echo "<br>";
			}
		}
	}

	echo "<h2>Array nach der Modifizierung</h2>";
	echo "<pre>";
	print_r($result);
	echo "</pre>";
	
}


function sub_org($result)
{
	$kindelemente = (is_array($result->children) && count($result->children) > 0) ? true : false; // Hat der Verband Kindelemente?
	$name = $result->club;
	// Kindelemente hinzufügen, wenn württembergische Bezirke kommen
	switch($result->vkz)
	{
		case 'C01':
		case 'C02':
		case 'C03':
		case 'C04':
		case 'C05':
		case 'C06':
			echo "<h3>Verband ".$result->vkz."</h3>";
			echo "<pre>";
			print_r($result);
			echo "</pre>";
			break;
		default:
			break;
	}
	if($kindelemente)
	{
		foreach ($result->children as $b)
		{
			sub_org($b);
		}
	}
}

//highlight_file(__FILE__);
?>
