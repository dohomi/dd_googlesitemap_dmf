<?php

if (!defined('TYPO3_MODE')) {
	exit;
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['sitemap']['dmf'] = 'tx_ddgooglesitemap_dmf->main';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'DMF\\DdGooglesitemapDmf\\Command\\CrawlerCommandController';

?>