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


$GLOBALS['TL_DCA']['tl_module']['fields']['dewis_searchfield'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['dewis_searchfield'],
	'inputType'     => 'checkbox',
	'eval'          => array
	(
		'tl_class'   => 'w50',
		'isBoolean'  => true,
	),
	'sql'           => "char(1) NOT NULL default ''",
);
