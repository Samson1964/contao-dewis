<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package News
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Table tl_dwz_ver
 */
$GLOBALS['TL_DCA']['tl_dwz_ver'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'switchToEdit'                => true, 
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id'                  => 'primary',
				'zpsver'              => 'index',
			)
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('zpsver'),
			'flag'                    => 3,
			'panelLayout'             => 'sort,filter;search,limit'
		),
		'label' => array
		(
			'fields'                  => array('zpsver', 'status', 'name'),
			'format'                  => '%s %s %s',
			'label_callback'          => array('tl_dwz_ver', 'listVereine')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_dwz_ver']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_dwz_ver']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				//'button_callback'     => array('tl_dwz_ver', 'copyArchive')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_dwz_ver']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
				//'button_callback'     => array('tl_dwz_ver', 'deleteArchive')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_dwz_ver']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'                => array('image'), 
		'default'                     => '{name_legend},zpsver,verband,name,abkuerzung,status,statusdatum;{adresse_legend},empfaenger,plz,ort,strasse,telefon,email,adressdatum;{image_legend},image;{info_legend},info,homepage;{source_legend},elobase;{publish_legend},published'
	),

	// Subpalettes
	'subpalettes' => array
	(
		'image'                       => 'singleSRC,image_caption,image_size'
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		), 
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'zpsver' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['zpsver'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => true, 
				'maxlength'           => 5, 
				'tl_class'            => 'w50',
				'unique'              => true
			),
			'sql'                     => "varchar(5) NOT NULL default ''"
		),
		'verband' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['verband'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>3, 'tl_class'=>'w50'),
			'sql'                     => "varchar(3) NOT NULL default ''"
		),
		'status' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['status'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'default'                 => 'A',
			'inputType'               => 'select',
			'options'                 => array
			(
				'A'                   => 'Aktiv',
				'L'                   => 'Abgemeldet',
			),
			'eval'                    => array
			(
				'mandatory'           => false, 
				'maxlength'           => 1, 
				'tl_class'            => 'w50',
				'includeBlankOption'  => true,
			),
			'sql'                     => "varchar(1) NOT NULL default ''"
		),
		'statusdatum' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['statusdatum'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 10,
				'tl_class'            => 'w50',
				'rgxp'                => 'alnum'
			),
			'load_callback'           => array
			(
				array('tl_dwz_ver', 'getDate')
			),
			'save_callback' => array
			(
				array('tl_dwz_ver', 'putDate')
			),
			'sql'                     => "int(8) unsigned NOT NULL default '0'"
		),
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => false, 
				'maxlength'           => 40, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(40) NOT NULL default ''"
		),
		'abkuerzung' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['abkuerzung'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => false, 
				'maxlength'           => 4, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(4) NOT NULL default ''"
		),
		'empfaenger' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['empfaenger'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 40, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(40) NOT NULL default ''"
		),  
		'telefon' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['telefon'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 20, 
				'tl_class'            => 'w50',
				'rgxp'                => 'phone'
			),
			'sql'                     => "varchar(20) NOT NULL default ''"
		),  
		'plz' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['plz'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 10, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(10) NOT NULL default ''"
		),  
		'ort' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['ort'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 40, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(40) NOT NULL default ''"
		),  
		'strasse' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['strasse'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 40, 
				'tl_class'            => 'w50'
			),
			'sql'                     => "varchar(40) NOT NULL default ''"
		),  
		'email' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['email'],
			'exclude'                 => true,
			'search'                  => false,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 40, 
				'tl_class'            => 'w50',
				'rgxp'                => 'email'
			),
			'sql'                     => "varchar(40) NOT NULL default ''"
		),  
		'adressdatum' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['adressdatum'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'maxlength'           => 10,
				'tl_class'            => 'w50',
				'rgxp'                => 'alnum'
			),
			'load_callback'           => array
			(
				array('tl_dwz_ver', 'getDate')
			),
			'save_callback' => array
			(
				array('tl_dwz_ver', 'putDate')
			),
			'sql'                     => "int(8) unsigned NOT NULL default '0'"
		),
		'image' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['image'],
			'inputType'               => 'checkbox',
			'filter'                  => true,
			'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr'),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'singleSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['singleSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array
			(
				'filesOnly'           => true, 
				'fieldType'           => 'radio',
				'mandatory'           => true,
				'tl_class'            => 'clr'
			),
			'sql'                     => "binary(16) NULL",
		),  
		'image_size' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['image_size'],
			'exclude'                 => true,
			'inputType'               => 'imageSize',
			'options'                 => $GLOBALS['TL_CROP'],
			'reference'               => &$GLOBALS['TL_LANG']['MSC'],
			'eval'                    => array('rgxp'=>'digit', 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
		),
		'image_caption' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['image_caption'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'allowHtml'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'info' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['info'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array('rte'=>'tinyMCE'),
			'explanation'             => 'insertTags', 
			'sql'                     => "text NULL"
		),
		'homepage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['homepage'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => false, 
				'rgxp'                => 'url', 
				'decodeEntities'      => true, 
				'maxlength'           => 255, 
				'tl_class'            => 'long clr'
			),
			'sql'                     => "varchar(255) NOT NULL default ''"
		), 
		'elobase' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['elobase'],
			'inputType'               => 'checkbox',
			'filter'                  => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_dwz_ver']['published'],
			'inputType'               => 'checkbox',
			'filter'                  => true,
			'eval'                    => array('tl_class' => 'w50','isBoolean' => true),
			'sql'                     => "char(1) NOT NULL default ''"
		),
	)
);


/**
 * Class tl_dwz_ver
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    News
 */
class tl_dwz_ver extends Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	/**
	 * Datensätze auflisten
	 * @param array
	 * @return string
	 */
	public function listVereine($arrRow)
	{ 
		$temp = '<b>' . $arrRow['zpsver'] . '</b> ';
		($arrRow['status'] == 'A') ? $temp .= '(A) ' : $temp .= '(L) ';
		$temp .= $arrRow['name'];
		return $temp;
	}

	/**
	 * Datumswert aus Datenbank umwandeln
	 * @param mixed
	 * @return mixed
	 */
	public function getDate($varValue)
	{
		$laenge = strlen($varValue);
		$temp = '';
		switch($laenge)
		{
			case 8: // JJJJMMTT
				$temp = substr($varValue,6,2).'.'.substr($varValue,4,2).'.'.substr($varValue,0,4);
				break;
			case 6: // JJJJMM
				$temp = substr($varValue,4,2).'.'.substr($varValue,0,4);
				break;
			case 4: // JJJJ
				$temp = $varValue;
				break;
			default: // anderer Wert
				$temp = '';
		}

		return $temp;
	}

	/**
	 * Datumswert für Datenbank umwandeln
	 * @param mixed
	 * @return mixed
	 */
	public function putDate($varValue)
	{
		$laenge = strlen(trim($varValue));
		$temp = '';
		switch($laenge)
		{
			case 10: // TT.MM.JJJJ
				$temp = substr($varValue,6,4).substr($varValue,3,2).substr($varValue,0,2);
				break;
			case 7: // MM.JJJJ
				$temp = substr($varValue,3,4).substr($varValue,0,2);
				break;
			case 4: // JJJJ
				$temp = $varValue;
				break;
			default: // anderer Wert
				$temp = 0;
		}

		return $temp;
	}

}
