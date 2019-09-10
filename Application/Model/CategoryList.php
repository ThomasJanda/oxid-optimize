<?php

namespace rs\optimize\Application\Model;

/**
 * Class CategoryList
 *
 * @see     \OxidEsales\Eshop\Application\Model\CategoryList
 */
class CategoryList extends CategoryList_parent
{

    /**
     * Get data from db
     *
     * @return array
     */
    protected function _loadFromDb()
    {
        $oOptimize = oxNew(\rs\optimize\Core\OptimizeDb::class);
        $bDoCache = $oOptimize->shouldCache();

        if ( ! $bDoCache) {
            return parent::_loadFromDb();
        }

        $sSql = $this->_getSelectString(false, null, 'oxparentid, oxsort, oxtitle');
        $sCacheKey = str_replace("\\", "_", get_class($this)."_".md5($sSql));


        if (($oData = $oOptimize->getFileCache($sCacheKey)) === null) {
            //cache
            $oData = parent::_loadFromDb();
            $oOptimize->setFileCache($sCacheKey, $oData);
        }

        return $oData;
    }

}