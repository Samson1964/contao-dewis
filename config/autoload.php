<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @package DeWIS
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
    'Samson',
));
 

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'Samson\DeWIS\DeWIS'				=> 'system/modules/dewis/helper/DeWIS.php',
	'Samson\DeWIS\Cache'				=> 'system/modules/dewis/helper/Cache.php',
	'Samson\DeWIS\Helper'				=> 'system/modules/dewis/helper/Helper.php',
	'Samson\DeWIS\Spieler'				=> 'system/modules/dewis/classes/Spieler.php',
	'Samson\DeWIS\Verein'				=> 'system/modules/dewis/classes/Verein.php',
	'Samson\DeWIS\Verband'				=> 'system/modules/dewis/classes/Verband.php',
	'Samson\DeWIS\Turnier'				=> 'system/modules/dewis/classes/Turnier.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'dewis_sub_spielersuche'	=> 'system/modules/dewis/templates',
	'dewis_spieler'				=> 'system/modules/dewis/templates',
	'dewis_sub_vereinsuche'		=> 'system/modules/dewis/templates',
	'dewis_verein'				=> 'system/modules/dewis/templates',
	'dewis_sub_verbandsuche'	=> 'system/modules/dewis/templates',
	'dewis_verband'				=> 'system/modules/dewis/templates',
	'dewis_sub_turniersuche'	=> 'system/modules/dewis/templates',
	'dewis_turnier'				=> 'system/modules/dewis/templates',
	'be_dewis'					=> 'system/modules/dewis/templates',
	'queries'					=> 'system/modules/dewis/templates',
));
