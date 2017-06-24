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
  
  unionRatingList($client);
  tournament($client);
  tournamentCard($client);
  tournamentCardByZPS($client);
  tournamentPairings($client);
  searchByName($client);
  tournamentsByPeriod($client);
  bestOfFed($client);
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
    $members = $client->searchByName("me", "", 0,30);
    
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
    $tournament = $client->tournamentPairings("B216-000-FBL");
    
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
    $tcard = $client->tournamentCardForZps("C0132-88");
  
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
    $tournament = $client->tournament("B148-C12-SLG");

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
highlight_file(__FILE__);
?>
