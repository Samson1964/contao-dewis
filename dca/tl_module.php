<?php
/**
 * Avatar for Contao Open Source CMS
 *
 * Copyright (C) 2013 Kirsten Roschanski
 * Copyright (C) 2013 Tristan Lins <http://bit3.de>
 *
 * @package    DeWIS
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Add palette to tl_module
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['dewis_einstellungen'] = '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},cssID,align,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['dewis_spielersuche'] = '{title_legend},name,headline,type;{config_legend},dewis_searchfield;{protected_legend:hide},protected;{expert_legend:hide},cssID,align,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['dewis_spieler'] = '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},cssID,align,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['dewis_verein'] = '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},cssID,align,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['dewis_verband'] = '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},cssID,align,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['dewis_turnier'] = '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},cssID,align,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['dewis_suche'] = '{title_legend},name,headline,type;{options_legend},dewis_searchkey,dewis_spieler,dewis_verein,dewis_verband,dewis_turnier;{protected_legend:hide},protected;{expert_legend:hide},cssID,align,space';


$GLOBALS['TL_DCA']['tl_module']['fields']['dewis_searchfield'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['dewis_searchfield'],
	'inputType'               => 'checkbox',
	'eval'                    => array
	(
		'tl_class'            => 'w50',
		'isBoolean'           => true,
	),
	'sql'                     => "char(1) NOT NULL default ''",
);

// Welcher Parameter enthält den Suchstring?
$GLOBALS['TL_DCA']['tl_module']['fields']['dewis_searchkey'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['dewis_searchkey'],
	'exclude'                 => true,
	'default'                 => 'q',
	'inputType'               => 'text',
	'eval'                    => array
	(
		'mandatory'           => false,
		'maxlength'           => 16,
		'tl_class'            => 'w50'
	),
	'sql'                     => "varchar(16) NOT NULL default 'q'"
);

// Suche in Spieler aktivieren/deaktivieren
$GLOBALS['TL_DCA']['tl_module']['fields']['dewis_spieler'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['dewis_spieler'],
	'inputType'               => 'checkbox',
	'eval'                    => array
	(
		'tl_class'            => 'w50 clr',
		'isBoolean'           => true,
	),
	'sql'                     => "char(1) NOT NULL default ''",
);

// Suche in Verein aktivieren/deaktivieren
$GLOBALS['TL_DCA']['tl_module']['fields']['dewis_verein'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['dewis_verein'],
	'inputType'               => 'checkbox',
	'eval'                    => array
	(
		'tl_class'            => 'w50',
		'isBoolean'           => true,
	),
	'sql'                     => "char(1) NOT NULL default ''",
);

// Suche in Verband aktivieren/deaktivieren
$GLOBALS['TL_DCA']['tl_module']['fields']['dewis_verband'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['dewis_verband'],
	'inputType'               => 'checkbox',
	'eval'                    => array
	(
		'tl_class'            => 'w50',
		'isBoolean'           => true,
	),
	'sql'                     => "char(1) NOT NULL default ''",
);

// Suche in Turnier aktivieren/deaktivieren
$GLOBALS['TL_DCA']['tl_module']['fields']['dewis_turnier'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['dewis_turnier'],
	'inputType'               => 'checkbox',
	'eval'                    => array
	(
		'tl_class'            => 'w50',
		'isBoolean'           => true,
	),
	'sql'                     => "char(1) NOT NULL default ''",
);
