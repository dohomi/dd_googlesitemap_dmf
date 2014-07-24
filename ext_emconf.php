<?php

/*********************************************************************
 * Extension configuration file for ext "dd_googlesitemap_dmf".
 *
 * Generated by ext 31-07-2013 05:13 UTC
 *
 * https://github.com/t3elmar/Ext
 *********************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Google Sitemap for plugins',
    'description' => 'Extends dd_googlesitemap that you can easy create your own sitemap.xml for you extensions. Needs only a few line of typoscript configuration - works with realurl or cooluri.',
    'category' => 'fe',
    'shy' => 0,
    'version' => '1.2.2',
    'dependencies' => '',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => 'uploads/tx_ddgooglesitemap_dmf',
    'modify_tables' => '',
    'clearcacheonload' => 0,
    'lockType' => '',
    'author' => 'Dominic Garms',
    'author_email' => 'djgarms@gmail.com',
    'author_company' => 'DMFmedia GmbH',
    'CGLcompliance' => NULL,
    'CGLcompliance_note' => NULL,
    'constraints' =>
        array(
            'depends' =>
                array(
                    'dd_googlesitemap' => '*',
                    'typo3' => '4.5.0-6.1.99',
                ),
            'conflicts' => array(),
            'suggests' =>
                array(),
        ),
    'comment' => 'bugfix for wrong mm table generation',
    'user' => 'dohomi',
);

?>