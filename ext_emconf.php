<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "dd_googlesitemap_dmf".
 *
 * Auto generated 13-06-2013 04:01
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Google Sitemap for plugins',
	'description' => 'Extends dd_googlesitemap that you can easy create your own sitemap.xml for you extensions. Needs only a few line of typoscript configuration - works with realurl or cooluri.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '0.0.4',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Dominic Garms',
	'author_email' => 'djgarms@gmail.com',
	'author_company' => 'DMFmedia GmbH',
	'CGLcompliance' => NULL,
	'CGLcompliance_note' => NULL,
	'constraints' => 
	array (
		'depends' => 
		array (
			'dd_googlesitemap' => '*',
			'typo3' => '4.5.0-6.1.99',
		),
		'conflicts' => '',
		'suggests' => 
		array (
		),
	),
);

?>