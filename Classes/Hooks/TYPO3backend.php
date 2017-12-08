<?php
namespace Causal\Cloudflare\Hooks;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Hook for clearing cache on Cloudflare.
 *
 * @category    Hooks
 * @package     TYPO3
 * @subpackage  tx_cloudflare
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class TYPO3backend implements \TYPO3\CMS\Backend\Toolbar\ClearCacheActionsHookInterface
{
    /**
     * Path to language file
     */
    const LL_PATH = 'EXT:cloudflare/Resources/Private/Language/locallang.xlf';

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->getLanguageService()->includeLLFile(self::LL_PATH);
    }

    /**
     * Adds cache menu item.
     *
     * @param array $cacheActions
     * @param array $optionValues
     * @return void
     */
    public function manipulateCacheActions(&$cacheActions, &$optionValues)
    {
        $backendUser = $this->getBackendUser();
        if ($backendUser->isAdmin()
            || $backendUser->getTSConfigVal('options.clearCache.all')
            || $backendUser->getTSConfigVal('options.clearCache.cloudflare')
        ) {
            if (version_compare(TYPO3_version, '8.7', '<')) {
                if (version_compare(TYPO3_version, '7.4.99', '<=')) {
                    $icon = '<span class="t3-icon t3-icon-actions t3-icon-actions-system t3-icon-system-cache-clear-impact-low"></span>';
                } else {
                    /** @var \TYPO3\CMS\Core\Imaging\IconFactory $iconFactory */
                    $iconFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Imaging\\IconFactory');
                    $icon = $iconFactory->getIcon('actions-system-cache-clear-impact-low', \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL);
                }

                // Add new cache menu item
                $title = $this->getLanguageService()->getLL('clear_cache');
                $clearCloudflare = [
                    'id' => 'cloudflare',
                    'title' => $title,
                    'href' => $GLOBALS['BACK_PATH'] . BackendUtility::getAjaxUrl('TxCloudflare::purge'),
                    'icon' => $icon,
                ];
            } else {
                $clearCloudflare = [
                    'id' => 'cloudflare',
                    'title' => 'LLL:' . self::LL_PATH . ':clear_cache',
                    'description' => 'LLL:' . self::LL_PATH . ':clear_cache_description',
                    'href' => $GLOBALS['BACK_PATH'] . BackendUtility::getAjaxUrl('TxCloudflare::purge'),
                    'iconIdentifier' => 'actions-system-cache-clear-impact-low',
                ];
            }
            $clearAll = array_shift($cacheActions);

            if ($clearAll !== null) {
                $cacheActions = array_merge([$clearAll, $clearCloudflare], $cacheActions);
            } else {
                $cacheActions[] = $clearCloudflare;
            }
            $optionValues[] = 'cloudflare';
        }
    }

    /**
     * Returns the current Backend user.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns the LanguageService.
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

}
