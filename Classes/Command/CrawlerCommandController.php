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
	 * @param bool $clearAllCaches
	 * @param bool $clearStaticfilecache
	 * @param string $url
	 */
	public function crawlXmlCommand($clearAllCaches = FALSE, $clearStaticfilecache = FALSE, $url = '') {

		/** @var ConfigurationManager $configurationManager */
		$configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
		$settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

		if ($url) {
			$xmlUrls[] = $url;
		} else {
			$xmlUrls = $settings['plugin.']['dd_googlesitemap_dmf.']['crawler.'];
		}
		if (!is_array($xmlUrls)) {
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

		if (is_dir(PATH_site . 'typo3temp/tx_staticfilecache') && $clearAllCaches === FALSE && $clearStaticfilecache !== FALSE) {
			$clearTypo3Temp = 'rm -rf ' . PATH_site . 'typo3temp/tx_staticfilecache/*';
			exec("$clearTypo3Temp");
		}


		// clear all caches
		if ($clearAllCaches) {
			// clears all cache tables
			$this->clearAllCaches();

			// remove all temp files
			$clearTypo3Temp = 'rm -rf ' . PATH_site . 'typo3temp/*';
			exec("$clearTypo3Temp");
		}

		foreach ($xmlUrls as $httpUrl) {

			$contentXml = $this->get_url_contents($httpUrl);

			$generatedArrayOfXml = GeneralUtility::xml2tree($contentXml);
			if ($urls = $generatedArrayOfXml['urlset'][0]['ch']['url']) {
				foreach ($urls as $url) {

					// wget all sitemap urls
					$http = $url['ch']['loc'][0]['values'][0];
					$wgetString = 'cd ' . $pathToLogFile . ' && wget --no-cache --delete-after -a ' . $pathToLogFile . 'wgetLog.txt ' . $http;
					exec("$wgetString");
				}
			}
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