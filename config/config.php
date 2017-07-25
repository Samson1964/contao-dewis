<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   DeWIS
 * @author    Frank Hoppe
 * @license   GNU/LGPL
 * @copyright Frank Hoppe 2014
 */

//define(CACHE_AKTIV, false); // Cachestatus
//define(CACHE_TIME, 36000); // Cachezeit (36000 = 10h)
//define(CACHE_TIME_FAKTOR_VERBAND, 20); // CACHE_TIME * Faktor = gesamte Cachelebenszeit
//define(CACHE_TIME_FAKTOR_REFERENT, 5); // CACHE_TIME * Faktor = gesamte Cachelebenszeit
define(CACHE_DIR, TL_ROOT . '/system/cache/dewis/'); // Cacheverzeichnis festlegen

define(KARTEISPERRE_GAESTE, true); // Anzeige von Karteikarten für nichtangemeldete Besucher gesperrt
define(PASSIVE_AUSBLENDEN, false); // Anzeige passiver Spieler
define(GEBURTSJAHR_AUSBLENDEN, true); // Anzeige des Geburtsjahres
define(GESCHLECHT_AUSBLENDEN, true); // Anzeige des Geschlechtes

define(ALIAS_SPIELER, 'spieler'); // Spielerseite
define(ALIAS_VEREIN, 'verein'); // Vereineseite
define(ALIAS_VERBAND, 'verband'); // Verbändeseite
define(ALIAS_TURNIER, 'turnier'); // Turniereseite

/**
 * Backend-Module
 */

$GLOBALS['BE_MOD']['dewis'] = array
(
	'dwz-spieler'    => array
	(
		'tables'         => array
		(
			'tl_dwz_spi', 
			'tl_dwz_spiver',
			'tl_dwz_kar',
			'tl_dwz_inf',
			'tl_dwz_fid',
		),
		'icon'           => 'system/modules/dewis/assets/images/icon_spieler.png',
	),
	'dwz-vereine'    => array
	(
		'tables'         => array
		(
			'tl_dwz_ver', 
		),
		'icon'           => 'system/modules/dewis/assets/images/icon_vereine.png',
	),
	'dwz-turniere'    => array
	(
		'tables'         => array
		(
			'tl_dwz_tur', 
		),
		'icon'           => 'system/modules/dewis/assets/images/icon_turniere.png',
	),
	'dwz-bearbeiter'    => array
	(
		'tables'         => array
		(
			'tl_dwz_bea', 
		),
		'icon'           => 'system/modules/dewis/assets/images/icon_bearbeiter.png',
	),
);

/**
 * Frontend-Module
 */

$GLOBALS['FE_MOD']['dewis'] = array
(
	'dewis_spieler'         => 'Samson\DeWIS\Spieler',
	'dewis_verein'          => 'Samson\DeWIS\Verein',
	'dewis_verband'         => 'Samson\DeWIS\Verband',
	'dewis_turnier'         => 'Samson\DeWIS\Turnier',
	'dewis_suche'           => 'Samson\DeWIS\Suche',
);

// http://de.contaowiki.org/Strukturierte_URLs
$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = array('DeWIS\DeWIS', 'getParamsFromUrl');

if (TL_MODE == 'BE') 
{
	//echo "<pre>";
	//print_r(get_defined_constants());
	//echo "</pre>";
}

/**
 * Purge jobs / Reinigungsarbeiten
 */
$GLOBALS['TL_PURGE']['custom']['dewis'] = array
(
	'callback' => array('DeWIS\DeWIS', 'purgeCache')
);

/**
 * -------------------------------------------------------------------------
 * Voreinstellungen Contao-BE System -> Einstellungen
 * -------------------------------------------------------------------------
 */

$GLOBALS['TL_CONFIG']['dewis_cache'] = 1;
$GLOBALS['TL_CONFIG']['dewis_cache_default'] = 4;
$GLOBALS['TL_CONFIG']['dewis_cache_referent'] = 24;
$GLOBALS['TL_CONFIG']['dewis_cache_verband'] = 48;
