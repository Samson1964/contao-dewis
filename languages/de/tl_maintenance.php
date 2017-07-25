<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

$GLOBALS['TL_LANG']['tl_maintenance_jobs']['dewis'] = array('DeWIS-Cache leeren','Löscht den Cache der DeWIS-Abfrage. Dieser Cache kann in den Backend-Einstellungen dauerhaft deaktiviert werden.');

// Ausgabe ergänzen
$GLOBALS['TL_LANG']['tl_maintenance_jobs']['dewis'][0] .= \Samson\DeWIS\DeWIS::calcCache();
