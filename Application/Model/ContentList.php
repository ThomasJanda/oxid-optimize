<?php

namespace rs\optimize\Application\Model;

/**
 * Class ContentList
 *
 * @see     \OxidEsales\Eshop\Application\Model\ContentList
 */
class ContentList extends ContentList_parent
{

    protected function _loadFromDb($iType)
    {
        $oOptimize = oxNew(\rs\optimize\Core\OptimizeDb::class);
        $bDoCache = $oOptimize->shouldCache();

        if ( ! $bDoCache) {
            return parent::_loadFromDb($iType);
        }

        $sSql = $this->_getSQLByType($iType);
        $sCacheKey = str_replace("\\", "_", get_class($this)."_".md5($sSql));


        if (($oData = $oOptimize->getFileCache($sCacheKey)) === null) {
            //cache
            $oData = parent::_loadFromDb($iType);
            $oOptimize->setFileCache($sCacheKey, $oData);
        }

        return $oData;
    }

}