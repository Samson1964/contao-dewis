<?php

// Anpassung der Palette
$GLOBALS['TL_DCA']['tl_member']['palettes']['default'] = str_replace
(
	'gender;',
	'gender;{dewis_legend:hide},dewisID,dewisCount,dewisCard;',
	$GLOBALS['TL_DCA']['tl_member']['palettes']['default']
);

// Hinzufügen der Feld-Konfiguration
// PKZ in DeWIS
$GLOBALS['TL_DCA']['tl_member']['fields']['dewisID'] = array
(
	'label'              => &$GLOBALS['TL_LANG']['tl_member']['dewisID'],
	'exclude'            => true,
	'inputType'          => 'text',
	'eval'               => array
	(
		'mandatory'      => false, 
		'rgxp'           => 'digit', 
		'maxlength'      => 10, 
		'tl_class'       => 'w50',
		'feEditable'     => true,
		'feViewable'     => true,
		'feGroup'        => 'dewis'
	),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

if(TL_MODE == 'BE')
{
	$GLOBALS['TL_DCA']['tl_member']['fields']['dewisID']['save_callback'] = array
	(
		(
			array('tl_member_DeWIS_BE', 'setNewPKZ')
		)
	);
}

// Zähler für PKZ-Wechsel, bei 0 kein Wechsel mehr möglich
$GLOBALS['TL_DCA']['tl_member']['fields']['dewisCount'] = array
(
	'label'              => &$GLOBALS['TL_LANG']['tl_member']['dewisCount'],
	'exclude'            => true,
	'inputType'          => 'text',
	'eval'               => array
	(
		'mandatory'      => false, 
		'rgxp'           => 'digit', 
		'maxlength'      => 1, 
		'tl_class'       => 'w50'
	),
	'sql'                => "varchar(1) NOT NULL default '2'"
);
// Abschaltung der Karteikarte
$GLOBALS['TL_DCA']['tl_member']['fields']['dewisCard'] = array
(
	'label'              => &$GLOBALS['TL_LANG']['tl_member']['dewisCard'],
	'exclude'            => true,
	'default'            => false,
	'inputType'          => 'checkbox',
	'eval'               => array
	(
		'mandatory'      => false, 
		'feEditable'     => true,
		'feViewable'     => true,
		'feGroup'        => 'dewis'
	),
	'sql'                => "char(1) NOT NULL default ''"
);

class tl_member_DeWIS_BE extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	} 

	public function setNewPKZ($varValue, DataContainer $dc)
	{
		//print_r($dc->activeRecord);
		if($dc->activeRecord->dewisID != $varValue)
		{
			// Feld geändert, deshalb alte Zuordnung auflösen und neue eintragen
			if($dc->activeRecord->dewisID)
			{
				$objSpieler = \Database::getInstance()->prepare('UPDATE tl_dwz_spi %s WHERE dewisID = ?')
													  ->set(array('contaoMemberID' => 0)) 
													  ->execute($dc->activeRecord->dewisID); 
			}
			if($varValue)
			{
				$objSpieler = \Database::getInstance()->prepare('UPDATE tl_dwz_spi %s WHERE dewisID = ?')
													  ->set(array('contaoMemberID' => $dc->activeRecord->id)) 
													  ->execute($varValue); 
			}
		}
		return $varValue;
	} 
}