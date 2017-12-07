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

use Causal\Cloudflare\Services\ClearCacheService;
use Causal\Cloudflare\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class TCEmain
{
    /**
     * @var ClearCacheService
     */
    protected $clearCacheService = null;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->clearCacheService = GeneralUtility::makeInstance(
            ClearCacheService::class,
            ConfigurationUtility::getExtensionConfiguration(),
            $GLOBALS['BE_USER']
        );
    }

    /**
     * Hooks into the "clear all caches" call.
     *
     * @param array $params
     * @param DataHandler $pObj
     * @return void
     */
    public function clear_cacheCmd(array $params, DataHandler $pObj)
    {
        if (ConfigurationUtility::isFlyMode()) {
            $this->clearCacheService->clearCache($params);
        } else {
            /**
             * @TODO implement enqueue
             */
        }
    }

    /**
     * Answers to AJAX call invoked when clearing only Cloudflare cache.
     *
     * @return void
     */
    public function clearCache()
    {
        if (!isset($GLOBALS['BE_USER'])) {
            return;
        }
        if ($GLOBALS['BE_USER']->isAdmin()
            || $GLOBALS['BE_USER']->getTSConfigVal('options.clearCache.all')
            || $GLOBALS['BE_USER']->getTSConfigVal('options.clearCache.cloudflare')
        ) {
            $this->clearCacheService->clearCloudflareCache();
        }
    }
}
