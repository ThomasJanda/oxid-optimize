<?php

namespace rs\optimize\Application\Model;

/**
 * Class ListModel
 *
 * @see     \OxidEsales\Eshop\Core\Model\PaymentList
 */
class PaymentList extends PaymentList_parent
{

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @return mixed
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function selectString($sql, array $parameters = [])
    {
        /**
         * @var \OxidEsales\Eshop\Core\Config          $oConfig
         * @var \OxidEsales\Eshop\Core\Model\ListModel $this
         */
        $oOptimize = oxNew(\rs\optimize\Core\OptimizeDb::class);
        $bDoCache = $oOptimize->shouldCache();

        if ( ! $bDoCache) {
            parent::selectString($sql, $parameters);

            return;
        }


        $this->clear();

        $oSaved = clone $this->getBaseObject();

        $sCacheKey = str_replace("\\", "_", get_class($oSaved)."_".md5(
                $sql."|".
                ($this->_sCoreTable ?? "")."|".
                ($this->_sShopId ?? "")."|".
                serialize($this->_aSqlLimit)."|".
                serialize($parameters)
            ));

        if ($oResult = $oOptimize->getFileCache($sCacheKey)) {
            foreach ($oResult as $aFields) {
                $oListObject = clone $oSaved;

                $this->_assignElement($oListObject, $aFields);
                $this->add($oListObject);
            }
        } else {

            $aCache = [];

            $oDb
                = \OxidEsales\Eshop\Core\DatabaseProvider::getDb(\OxidEsales\Eshop\Core\DatabaseProvider::FETCH_MODE_ASSOC);
            if ($this->_aSqlLimit[0] || $this->_aSqlLimit[1]) {
                $rs = $oDb->selectLimit($sql, $this->_aSqlLimit[1],
                    max(0, $this->_aSqlLimit[0]), $parameters);
            } else {
                $rs = $oDb->select($sql, $parameters);
            }

            if ($rs != false && $rs->count() > 0) {
                while ( ! $rs->EOF) {
                    $aCache[] = $rs->fields;

                    $oListObject = clone $oSaved;
                    $this->_assignElement($oListObject, $rs->fields);
                    $this->add($oListObject);
                    $rs->fetchRow();
                }
            }

            $oOptimize->setFileCache($sCacheKey, $aCache);
        }
    }
}