<?php

if (!defined('TYPO3_MODE')) {
    exit;
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dd_googlesitemap']['sitemap']['dmf'] = 'DMF\\DdGooglesitemapDmf\\Hooks\\Sitemap->main';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'DMF\\DdGooglesitemapDmf\\Command\\CrawlerCommandController';
