<?php
namespace DMF\DdGooglesitemapDmf\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Class CrawlerCommandController
 * @package DMF\Intranet\Command
 */
class Sitemap extends \DmitryDulepov\DdGooglesitemap\Generator\TtNewsSitemapGenerator
{

	/**
	 * Creates an instance of this class
	 *
	 * @return    void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Generates extension site map.
	 *
	 * @return    void
	 */
	protected function generateSitemapContent()
	{
		$selector = trim(GeneralUtility::_GP('selector'));
		$typoscriptSelector = $selector . '.';
		$currentSetup = $GLOBALS['TSFE']->tmpl->setup['plugin.']['dd_googlesitemap_dmf.'][$typoscriptSelector];


		$pidList = ($currentSetup['pidList']) ? GeneralUtility::intExplode(',', $currentSetup['pidList']) : $this->pidList;


		$catList = (GeneralUtility::_GP('catList')) ? GeneralUtility::intExplode(',', GeneralUtility::_GP('catList')) : GeneralUtility::intExplode(',', $currentSetup['catList']);
		$catMMList = (GeneralUtility::_GP('catMMList')) ? GeneralUtility::intExplode(',', GeneralUtility::_GP('catMMList')) : GeneralUtility::intExplode(',', $currentSetup['catMMList']);
		$currentSetup['singlePid'] = (GeneralUtility::_GP('singlePid')) ? (int)GeneralUtility::_GP('singlePid') : (int)$currentSetup['singlePid'];

		$currentSetup['languageUid'] = '';
		if (!$currentSetup['disableLanguageCheck']) {
			if (is_int($GLOBALS['TSFE']->sys_language_uid)) {
				// set language through TSFE checkup
				$currentSetup['languageUid'] = (int)$GLOBALS['TSFE']->sys_language_uid;
			}
			if (GeneralUtility::_GP('L')) {
				// overwrites if L param is set
				$currentSetup['languageUid'] = (int)GeneralUtility::_GP('L');
			}
		}

		if (count($pidList) > 0 && isset($selector) && isset($currentSetup)) {
			$table = $currentSetup['sqlMainTable'];
			$mmTable = $currentSetup['sqlMMTable'];
			$catColumn = $currentSetup['sqlCatColumn'];

			$sqlCondition = ($catColumn && count($catList) > 0 && $catList[0] > 0) ? ' AND ' . $catColumn . ' IN (' . implode(',', $catList) . ')' : '';

			$sqlMMCondition = $sqlMMTable = '';
			if ($mmTable != '' && count($catMMList) > 0 && $catMMList[0] > 0) {
				$sqlMMTable = ',' . $mmTable;
				$sqlMMCondition = ' AND ' . $table . '.uid = ' . $mmTable . '.uid_local AND ' . $mmTable . '.uid_foreign IN (' . implode(',', $catMMList) . ')';
			}

			$newsSelect = (GeneralUtility::_GP('type') == 'news') ? ',' . $currentSetup['sqlTitle'] . ',' . $currentSetup['sqlKeywords'] : '';

			$languageWhere = (is_int($currentSetup['languageUid'])) ? ' AND ' . $table . '.sys_language_uid=' . $currentSetup['languageUid'] : '';
			if ($table=='tx_news_domain_model_news') {
				$noInternalURLwhere = (' AND (' . $table . '.internalurl=FALSE OR ' . $table . ' .internalurl IS NULL)');
				$noExternalURLwhere = (' AND (' . $table . '.externalurl=FALSE  OR ' . $table . ' .externalurl IS NULL)');
			} else {
				$noInternalURLwhere = '';
				$noExternalURLwhere = '';
			}
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid,' . $currentSetup['sqlLastUpdated'] . $newsSelect,
				$table . $sqlMMTable,
				'pid IN (' . implode(',', $pidList) . ')' . $sqlCondition . $sqlMMCondition . $this->cObj->enableFields($table) . $languageWhere . $noInternalURLwhere . $noExternalURLwhere,
				'uid',
				$currentSetup['sqlOrder'] ? $currentSetup['sqlOrder'] : ''
			);

			$rowCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

			while (false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
				if ($url = $this->getVariousItemUrl($row['uid'], $currentSetup)) {
					$frequency = ($currentSetup['frequency']) ? $currentSetup['frequency'] : $this->getChangeFrequency($row[$currentSetup['sqlLastUpdated']]);
					echo $this->renderer->renderEntry(
						$url,
						$row[$currentSetup['sqlTitle']],
						$row[$currentSetup['sqlLastUpdated']],
						$frequency,
						$row[$currentSetup['sqlKeywords']]
					);
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);

			if ($rowCount === 0) {
				echo '<!-- It appears that there are no extension entries. If your ' .
					'storage sysfolder is outside of the rootline, you may ' .
					'want to use the dd_googlesitemap.skipRootlineCheck=1 TS ' .
					'setup option. Beware: it is insecure and may cause certain ' .
					'undesired effects! Better move your pid sysfolder ' .
					'inside the rootline! -->';
			} elseif (!$rowCount) {
				echo '<!-- There is an sql error. please check all corresponding sql fields in your typoscript setup. -->';
			}
		} else {
			echo 'There is something wrong with the config. Please check your selector and pidList elements. You may ' .
				'want to use the dd_googlesitemap.skipRootlineCheck=1 TS ' .
				'setup option if your storage sysfolder is outside the rootline. Beware: it is insecure and may cause certain ' .
				'undesired effects! Better move your pid sysfolder ' .
				'inside the rootline! -->';
		}
	}

	/**
	 * Creates a link to the news item
	 *
	 * @param    int $newsId    News item uid
	 *
	 * @return    string
	 */
	protected function getVariousItemUrl($showUid, $currentSetup)
	{
		$languageParam = (is_int($currentSetup['languageUid'])) ? '&L=' . $currentSetup['languageUid'] : '';

		$conf = array(
			'parameter'        => $currentSetup['singlePid'],
			'additionalParams' => '&' . $currentSetup['linkParams'] . '=' . $showUid . $languageParam,
			'returnLast'       => 'url',
			'useCacheHash'     => true,
		);
		$link = htmlspecialchars($this->cObj->typoLink('', $conf));

		return GeneralUtility::locationHeaderUrl($link);
	}


	/**
	 * @param $lastChange
	 *
	 * @return string
	 */
	protected function getChangeFrequency($lastChange)
	{
		$timeValues[] = $lastChange;
		$timeValues[] = time();
		sort($timeValues, SORT_NUMERIC);
		$sum = 0;
		for ($i = count($timeValues) - 1; $i > 0; $i--) {
			$sum += ($timeValues[$i] - $timeValues[$i - 1]);
		}
		$average = ($sum / (count($timeValues) - 1));

		return ($average >= 180 * 24 * 60 * 60 ? 'yearly' :
			($average <= 24 * 60 * 60 ? 'daily' :
				($average <= 60 * 60 ? 'hourly' :
					($average <= 14 * 24 * 60 * 60 ? 'weekly' : 'monthly'))));
	}
}
