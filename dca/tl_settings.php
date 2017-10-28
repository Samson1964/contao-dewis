<?php

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['__selector__'][] = 'dewis_cache';
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{dewis_legend:hide},dewis_cache';
$GLOBALS['TL_DCA']['tl_settings']['subpalettes']['dewis_cache'] = 'dewis_cache_default,dewis_cache_verband,dewis_cache_referent';

/**
 * fields
 */

// Cache ein- oder ausschalten
$GLOBALS['TL_DCA']['tl_settings']['fields']['dewis_cache'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['dewis_cache'],
	'inputType'               => 'checkbox',
	'eval'                    => array
	(
		'submitOnChange'      => true
	)
);

// Anzahl Stunden, die der Standardcache gültig ist
$GLOBALS['TL_DCA']['tl_settings']['fields']['dewis_cache_default'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['dewis_cache_default'],
	'inputType'               => 'text',
	'eval'                    => array
	(
		'tl_class'            => 'w50',
		'rgxp'                =>'natural'
	)
);

// Anzahl Stunden, die der Verbandcache gültig ist
$GLOBALS['TL_DCA']['tl_settings']['fields']['dewis_cache_verband'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['dewis_cache_verband'],
	'inputType'               => 'text',
	'eval'                    => array
	(
		'tl_class'            => 'w50 clr',
		'rgxp'                =>'natural'
	)
);

// Anzahl Stunden, die der Referentcache gültig ist
$GLOBALS['TL_DCA']['tl_settings']['fields']['dewis_cache_referent'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['dewis_cache_referent'],
	'inputType'               => 'text',
	'eval'                    => array
	(
		'tl_class'            => 'w50',
		'rgxp'                =>'natural'
	)
);
