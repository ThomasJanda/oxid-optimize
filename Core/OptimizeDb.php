<?php

namespace rs\optimize\Core;

class OptimizeDb extends \OxidEsales\Eshop\Core\Base
{

    /**
     * @param string $sObjectInListName
     * @return boolean
     */
    public function canObjectCache($sObjectInListName)
    {
        //echo $sObjectInListName."<br>";
        $bDoCache1 = false;
        $bDoCache2 = false;
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();

        if((bool) $oConfig->getConfigParam('rs-optimize_display_names_in_shop_db'))
        {
            echo "<br>".$sObjectInListName."<br>";
        }

        $sNotCachable = (string) $oConfig->getConfigParam('rs-optimize_not_cachable_db');
        $aNotCachable = explode("|",$sNotCachable);
        $aNotCachable = array_filter($aNotCachable);
        $aNotCachable = array_map('trim',$aNotCachable);
        $aNotCachable = array_map('strtolower',$aNotCachable);
        if(empty($aNotCachable))
        {
            $aNotCachable=[
                'oxorder',
                'oxorderarticles',
                'oxuser',
                'oxuserbasketitem',
                'oxuserbasket',
                'oxuserpayment',
                'oxrecommlist',
                'oxreview'
            ];
        }

        if(!in_array(strtolower($sObjectInListName),$aNotCachable))
           $bDoCache1=true;

        if($bDoCache1)
        {
            if((bool) $oConfig->getConfigParam('rs-optimize_only_cachable_ox_db'))
            {
                if(substr(strtolower($sObjectInListName),0,2)==="ox")
                {
                    $bDoCache2=true;
                }
            }
            else
                $bDoCache2=true;
        }


        $bDoCache = ($bDoCache1 && $bDoCache2) === true;
        if((bool) $oConfig->getConfigParam('rs-optimize_display_names_in_shop_db'))
        {
            if($bDoCache)
                echo "CACHE<br>";
            else
                echo "NO CACHE<br>";
        }

        return $bDoCache;
    }


    /**
     * @return bool
     */
    public function shouldCache()
    {
        $bDoCache = true;
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();
        if ($oConfig->isAdmin()) {
            $bDoCache = false;
        }

        if ( ! (bool)$oConfig->getConfigParam('rs-optimize_active_db')) {
            $bDoCache = false;
        }
        return $bDoCache;
    }

    /**
     * @return string
     */
    protected function _getUniqueIdentifier()
    {
        return md5(__CLASS__);
    }

    /**
     * @return string
     */
    public function getCacheDirectory()
    {
        /**
         * @var \OxidEsales\Eshop\Core\Config $oConfig
         */
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();

        $sPath = $oConfig->getConfigParam('sCompileDir')."rs-optimize";
        @mkdir($sPath);
        return $sPath;
    }

    /**
     * @return string
     */
    protected function _getPath()
    {
        /**
         * @var \OxidEsales\Eshop\Core\Config $oConfig
         */
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();

        $sSuffix = $oConfig->getConfigParam('rs-optimize_suffix_db');
        if($sSuffix == "now")
            $sSuffix = date('Y-m-d');
        elseif($sSuffix=="")
            $sSuffix = $this->_getUniqueIdentifier();

        //create root path
        $sPath = $this->getCacheDirectory();

        //delete all folders/files which is not $sSuffix
        $aList=[];
        if($handle=opendir($sPath))
        {
            while ($sFilename = readdir ($handle)) {

                if($sFilename!=$sSuffix && $sFilename!="." && $sFilename!="..")
                {
                    $aList[]=$sPath."/".$sFilename;
                }
            }
            closedir($handle);
        }
        if(count($aList)>0)
        {
            foreach($aList as $sFilePath)
            {
                if(!is_dir($sFilePath))
                {
                    @unlink($sFilePath);
                }
                else
                {
                    //directory
                    $aList2 = glob($sFilePath."/*");
                    foreach($aList2 as $sFilePath2)
                    {
                        @unlink($sFilePath2);
                    }
                    @rmdir($sFilePath);
                }
            }
        }


        //create sub path
        $sPath .= "/".$sSuffix;
        @mkdir($sPath);

        return $sPath."/";
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getFileCache($key)
    {
        $sContent = null;
        $sPath = $this->_getPath().$key.".txt";
        if (file_exists($sPath)) {
            try {
                $sContent = unserialize(file_get_contents($sPath));
            } catch (\Exception $e) {
                $sContent = null;
            }
        }

        return $sContent;
    }

    /**
     * @param string $key
     * @param string $content
     */
    public function setFileCache($key, $content)
    {
        $sPath = $this->_getPath().$key.".txt";
        file_put_contents($sPath, serialize($content));
    }
}
