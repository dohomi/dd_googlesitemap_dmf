<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007-2008 Dmitry Dulepov <dmitry@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 * This class implements any extension sitemap like tx_news
 *
 * The following URL parameters are expected:
 * - sitemap=dmf
 * - singlePid=<uid of the "single view" commerce product>
 * - pidList=pid where products are stored
 * http://example.com/?eID=dd_googlesitemap&sitemap=dmf&singlePid=100&pidList=115,116
 *
 * If you need to show products on different single view pages, make several sitemaps
 * (it is possible with Google).
 *
 * @author        Dmitry Dulepov <dmitry@typo3.org>
 * @author        Dominic Garms <djgarms@gmail.com>
 * @author		  Maximilian Grimm <grimm@grimmcreative.com>
 * @package       TYPO3
 * @subpackage    tx_ddgooglesitemap_dmf
 */
class tx_ddgooglesitemap_dmf extends DmitryDulepov\DdGooglesitemap\Generator\TtNewsSitemapGenerator {

	/**
	 * Creates an instance of this class
	 *
	 * @return    void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Generates extension site map.
	 *
	 * @return    void
	 */
	protected function generateSitemapContent() {

		$selector = trim(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('selector'));
		$typoscriptSelector = $selector . '.';
		$currentSetup = $GLOBALS['TSFE']->tmpl->setup['plugin.']['dd_googlesitemap_dmf.'][$typoscriptSelector];


		$pidList = ($currentSetup['pidList']) ? \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $currentSetup['pidList']) : $this->pidList;


		$catList = (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('catList')) ? \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('catList')) : \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $currentSetup['catList']);
		$catMMList = (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('catMMList')) ? \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('catMMList')) : \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $currentSetup['catMMList']);
		$currentSetup['singlePid'] = (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('singlePid')) ? intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('singlePid')) : intval($currentSetup['singlePid']);

		$currentSetup['languageUid'] = '';
		if (!$currentSetup['disableLanguageCheck']) {
			if (is_int($GLOBALS['TSFE']->sys_language_uid)) {
				// set language through TSFE checkup
				$currentSetup['languageUid'] = intval($GLOBALS['TSFE']->sys_language_uid);
			}
			if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('L')) {
				// overwrites if L param is set
				$currentSetup['languageUid'] = intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('L'));
			}
		}

		if (count($pidList) > 0 && isset($selector) && isset($currentSetup)) {
			$table = $currentSetup['sqlMainTable'];
			$mmTable = $currentSetup['sqlMMTable'];
			$catColumn = $currentSetup['sqlCatColumn'];

			$sqlCondition = (empty($currentSetup['sqlWhere']) ? '' : ' AND ' . $currentSetup['sqlWhere']) .
				(($catColumn && count($catList) > 0 && $catList[0] > 0) ? ' AND ' . $catColumn . ' IN (' . implode(',', $catList) . ')' : '');

			$sqlMMCondition = $sqlMMTable = '';
			if ($mmTable != '' && count($catMMList) > 0 && $catMMList[0] > 0) {
				$sqlMMTable = ',' . $mmTable;
				$sqlMMCondition = ' AND ' . $table . '.uid = ' . $mmTable . '.uid_local AND ' . $mmTable . '.uid_foreign IN (' . implode(',', $catMMList) . ')';
			}

			$newsSelect = (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type') == 'news') ? ',' . $currentSetup['sqlTitle'] . ',' . $currentSetup['sqlKeywords'] : '';

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

			while (FALSE !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
				if ($url = $this->getVariousItemUrl($row['uid'], $currentSetup)) {
					$frequency = ($currentSetup['frequency']) ? $currentSetup['frequency'] : $this->getChangeFrequency($row[$currentSetup['sqlLastUpdated']]);
					echo $this->renderer->renderEntry(
						$url,
						$row[$currentSetup['sqlTitle']],
						$row[$currentSetup['sqlLastUpdated']],
						$frequency,
						$row[$currentSetup['sqlKeywords']]);
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
	protected function getVariousItemUrl($showUid, $currentSetup) {
		$languageParam = (is_int($currentSetup['languageUid'])) ? '&L=' . $currentSetup['languageUid'] : '';

		$conf = array(
			'parameter'        => $currentSetup['singlePid'],
			'additionalParams' => '&' . $currentSetup['linkParams'] . '=' . $showUid . $languageParam . $currentSetup['additionalParams'],
			'returnLast'       => 'url',
			'useCacheHash'     => TRUE,
		);
		$link = htmlspecialchars($this->cObj->typoLink('', $conf));

		return \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($link);
	}


	/**
	 * @param $lastChange
	 *
	 * @return string
	 */
	protected function getChangeFrequency($lastChange) {

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

/** @noinspection PhpUndefinedVariableInspection */
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap_dmf/class.tx_googlesitemap_dmf.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dd_googlesitemap_dmf/class.tx_googlesitemap_dmf.php']);
}

?>
