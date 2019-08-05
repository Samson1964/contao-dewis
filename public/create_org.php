<?php

// Melde alle Fehler außer E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);

include('config.inc.php');

function org($result) {
	function sub_org($a, &$liste) {
		$c = (is_array($a->children) && count($a->children) > 0) ? 1 : 0;
		$p = (isset($a->p) && isset($liste[$a->p]['ZPS'])) ? $liste[$a->p]['ZPS'] : $a->vkz;
		$n = $a->club;
		$liste[$a->id] = array(
			'ZPS'           => $a->vkz, # sprintf("%-05s", $a->vkz),
			'Name'          => str_replace("'", "\'", $n),
			'Name_G'        => str_replace("'", "\'", ANSI_gross($n)),
			'Uebergeordnet' => $p,
			'Kinder'        => $c
		);
		if ($c) {
			foreach ($a->children as $b) {
				sub_org($b, $liste);
			}
		}
	}
	sub_org($result, $liste);

	$GLOBALS["dewis"]["verbaende"] = array();
	$GLOBALS["dewis"]["vereine"] = array();
	reset($liste);
	foreach ($liste as $l) {
		if ($l['Kinder'] or $l['Uebergeordnet'] == '00000') {
			$l['Kinder'] = array();
			$GLOBALS["dewis"]["verbaende"]['Z' . $l['ZPS']] = $l;
			if ($l['ZPS'] != '00000')
				$GLOBALS["dewis"]["verbaende"]['Z' . $l['Uebergeordnet']]['Kinder'][] = $l['ZPS'];
		}
		else {
			unset($l['Kinder']);
			$GLOBALS["dewis"]["vereine"]['Z' . $l['ZPS']] = $l;
		}
	}
	return array($GLOBALS["dewis"]["verbaende"], $GLOBALS["dewis"]["vereine"]);
}

try {
	$client = new SOAPClient(
		NULL,
		array(
			'location'	=> 'https://dwz.svw.info/services/soap/index.php',
			'uri'		=> 'https://soap',
			'style'		=> SOAP_RPC,
			'use'		=> SOAP_ENCODED
		)
	);

	$soap_funktion = 'organizations';
	$soap_parameter = array('00000');
	$result = $client->__soapCall($soap_funktion, $soap_parameter);
	if ($GLOBALS['debug']) {
		print "<pre>\n";
		print "soap_funktion = $soap_funktion\n\n";
		print "soap_parameter = ";
		print_r($soap_parameter);
		print "\nresult = ";
		print_r($result);
		print "</pre>\n";
	}
	list($GLOBALS["dewis"]["verbaende"], $GLOBALS["dewis"]["vereine"]) = org($result);

	// Verband K hinzufügen und DSB modifizieren
	$GLOBALS["dewis"]["verbaende"]["ZK0000"] = array('ZPS' => 'K0000', 'Name' => 'Ausländer', 'Name_G' => 'AUSLAENDER', 'Uebergeordnet' => '00000', 'Kinder' => array());
	if(!is_array($GLOBALS["dewis"]["verbaende"]["Z00000"]["Kinder"])) $GLOBALS["dewis"]["verbaende"]["Z00000"]["Kinder"] = array();
	$GLOBALS["dewis"]["verbaende"]["Z00000"]["Kinder"] = array_merge($GLOBALS["dewis"]["verbaende"]["Z00000"]["Kinder"],array("K0000"));

	$fh = fopen('org.inc.php', 'wb');
	fwrite($fh, "<?php\n\n");

	ksort($GLOBALS["dewis"]["verbaende"]);
	fwrite($fh, "\$GLOBALS[\"dewis\"][\"verbaende\"] = array(\n");
	$maske = "\t'%s' => array('ZPS' => '%s', 'Name' => '%s', 'Name_G' => '%s', 'Uebergeordnet' => '%s', 'Kinder' => array(%s)),\n";
	foreach($GLOBALS["dewis"]["verbaende"] as $k => $v) {
		asort($v['Kinder']);
		fwrite($fh, sprintf($maske, $v['ZPS'], $v['ZPS'], $v['Name'], $v['Name_G'], $v['Uebergeordnet'], $v['Kinder'] ? sprintf("'%s'", implode("', '", $v['Kinder'])) : ''));
	}
	fwrite($fh, ");\n\n");

	ksort($GLOBALS["dewis"]["vereine"]);
	fwrite($fh, "\$GLOBALS[\"dewis\"][\"vereine\"] = array(\n");
	$maske = "\t'%s' => array('ZPS' => '%s', 'Name' => '%s', 'Name_G' => '%s', 'Uebergeordnet' => '%s'),\n";
	foreach($GLOBALS["dewis"]["vereine"] as $k => $v) {
		fwrite($fh, sprintf($maske, $v['ZPS'], $v['ZPS'], $v['Name'], $v['Name_G'], $v['Uebergeordnet']));
	}
	fwrite($fh, ");\n\n");

	fwrite($fh, "?>");
	echo "Fertig!";

} catch (SOAPFault $f) {
		print "Soap-Fehler: {$f->faultstring}";
}

?>