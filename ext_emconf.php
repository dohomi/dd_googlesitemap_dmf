<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "dd_googlesitemap_dmf".
 *
 * Auto generated 05-10-2016 09:43
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
  'title' => 'Google Sitemap for plugins',
  'description' => 'Extends dd_googlesitemap that you can easy create your own sitemap.xml for you extensions. Needs only a few line of typoscript configuration - works with realurl or cooluri.',
  'category' => 'fe',
  'version' => '3.0.0',
  'state' => 'beta',
  'uploadfolder' => false,
  'createDirs' => 'uploads/tx_ddgooglesitemap_dmf',
  'clearcacheonload' => false,
  'author' => 'Dominic Garms',
  'author_email' => 'djgarms@gmail.com',
  'author_company' => 'DMFmedia GmbH',
  'constraints' =>
  array(
    'depends' =>
    array(
      'dd_googlesitemap' => '*',
      'typo3' => '6.2.0-8.7',
    ),
    'conflicts' =>
    array(
    ),
    'suggests' =>
    array(
    ),
  ),
  'comment' => 'bugfix for wrong mm table generation',
  'user' => 'dohomi',
);
