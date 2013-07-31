<?php
namespace DMF\DdGooglesitemapDmf\Command;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Class CrawlerCommandController
 * @package DMF\Intranet\Command
 */
class CrawlerCommandController extends CommandController {


	/**
	 * This function crawls generated Sitemap.xml from dd_googlesitemap and re-crawles
	 * the whole website links found in it.
	 *
	 * @param bool $clearAllCaches deletes all typo3temp files and clearAllCaches call
	 * @param bool $clearStaticfilecache deletes all files from typo3temp/tx_staticfilecache
	 * @param string $url dd_googlesitemap call like http://www.domain.com/?eID=dd_googlesitemap
	 * @param string $domain full domain address like http://www.domain.com
	 * @param bool $enableWgetCrawl ALPHA / BETA
	 */
	public function crawlXmlCommand($clearAllCaches = FALSE, $clearStaticfilecache = FALSE, $url = '', $domain = '', $enableWgetCrawl = FALSE) {

		/** @var ConfigurationManager $configurationManager */
		$configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
		$settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);


		if (is_dir(PATH_site . 'typo3temp/tx_staticfilecache') && $clearAllCaches === FALSE && $clearStaticfilecache !== FALSE) {
			$clearTypo3Temp = 'rm -rf ' . PATH_site . 'typo3temp/tx_staticfilecache/*';
			exec("$clearTypo3Temp");
		}

		// clear all caches
		if ($clearAllCaches !== FALSE) {
			// clears all cache tables
			$this->clearAllCaches();

			// remove all temp files
			$clearTypo3Temp = 'rm -rf ' . PATH_site . 'typo3temp/*';
			exec("$clearTypo3Temp");
		}

		if ($enableWgetCrawl === FALSE) {
			return;
		}

		if ($url) {
			$xmlUrls[] = $url;
		} else {
			$xmlUrls = $settings['plugin.']['dd_googlesitemap_dmf.']['crawler.'];
		}
		if (!is_array($xmlUrls) || $domain === '') {
			return;
		}

		$pathToLogFile = PATH_site . 'uploads/tx_ddgooglesitemap_dmf/';
		if (!is_dir($pathToLogFile)) {
			// create folder if not exist
			$makeFolder = 'mkdir ' . $pathToLogFile;
			exec("$makeFolder");
		}

		// remove wgetLog.txt
		$removeWgetFile = 'rm ' . $pathToLogFile . 'wgetLog.txt';
		exec("$removeWgetFile");

		foreach ($xmlUrls as $httpUrl) {
			$output = 'wget -q "' . $httpUrl . '" --output-document - | ';
			$output .= 'egrep -o "' . $domain . '[^<]+" | ';
			$output .= 'wget --delete-after -w 1 -o ' . $pathToLogFile . 'wgetXml.log -i -';
			exec("$output");
		}
	}


	/**
	 * Clear all table caches
	 *
	 * @throws \RuntimeException
	 */
	protected function clearAllCaches() {
		// Clear all caching framework caches
		$GLOBALS['typo3CacheManager']->flushCaches();
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('cms')) {
			$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('cache_treelist');
		}
		// Clearing additional cache tables:
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearAllCache_additionalTables'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearAllCache_additionalTables'] as $tableName) {
				if (!preg_match('/[^[:alnum:]_]/', $tableName) && substr($tableName, -5) === 'cache') {
					$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery($tableName);
				} else {
					throw new \RuntimeException('TYPO3 Fatal Error: Trying to flush table "' . $tableName . '" with "Clear All Cache"', 1270853922);
				}
			}
		}
	}

	/**
	 * Get URL from http source
	 *
	 * @param $url
	 *
	 * @return mixed
	 */
	protected function get_url_contents($url) {
		$crl = curl_init();
		$timeout = 5;
		curl_setopt($crl, CURLOPT_URL, $url);
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
		$ret = curl_exec($crl);
		curl_close($crl);

		return $ret;
	}
}

?>