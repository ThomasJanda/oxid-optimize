<?php

namespace rs\optimize\Core;

class Optimize extends \OxidEsales\Eshop\Core\Base
{

    /**
     * @return string
     */
    protected function _getUniqueIdentifier()
    {
        return "_".md5(__CLASS__)."_RSOPTIMIZED";
    }

    /**
     * @param $sUrl
     *
     * @return string
     */
    protected function _convertToAbsolutePath($sUrl)
    {
        $oConfig = $this->getConfig();

        $sTmp = $sUrl;
        $sTmp = str_replace($oConfig->getShopUrl(), "", $sTmp);
        $sTmp = str_replace($oConfig->getSslShopUrl(), "", $sTmp);

        return $oConfig->getConfigParam('sShopDir').$sTmp;
    }

    /**
     * @param $sPath
     *
     * @return string
     */
    protected function _convertToUrl($sPath)
    {
        $oConfig = $this->getConfig();

        $sTmp = $sPath;
        $sTmp = str_replace($oConfig->getConfigParam('sShopDir'), "", $sTmp);

        if ($oConfig->isSsl()) {
            return $oConfig->getSslShopUrl().$sTmp;
        } else {
            return $oConfig->getShopUrl().$sTmp;
        }
    }

    /**
     * check if script from the same domain
     *
     * @param type $sUrl
     * @return boolean
     */
    protected function _checkIfSameDomain($sUrl)
    {
        $bSame=true;
        $sUrl = strtolower($sUrl);
        if(substr($sUrl,0,5)==="http:" || substr($sUrl,0,6)==="https:")
        {
            $oConfig = $this->getConfig();
            $sUrlLocal1 = strtolower(rtrim($oConfig->getShopUrl()??"","/"));
            $sUrlLocal2 = strtolower(rtrim($oConfig->getSslShopUrl()??"","/"));
            if(
                ($sUrlLocal1!=="" && substr($sUrl,0,strlen($sUrlLocal1))!==$sUrlLocal1)
                &&
                ($sUrlLocal2!=="" && substr($sUrl,0,strlen($sUrlLocal2))!==$sUrlLocal2)
                )
            {
                $bSame=false;
            }
        }
        return $bSame;
    }


#region "CSS"

    /**
     * @param string[] $aStyle
     *
     * @return string[]
     */
    public function checkStyle($aStyle)
    {
        if ( ! (bool)$this->getConfig()
            ->getConfigParam('rs-optimize_active_css')
        ) {
            return $aStyle;
        }

        $sSuffix = trim($this->getConfig()->getConfigParam('rs-optimize_suffix_css'));
        if ($sSuffix == "now") {
            $sSuffix = time();
        }
        $sSuffix.= $this->_getUniqueIdentifier().".css";

        $sStyleFinish = [];
        foreach ($aStyle as $sUrlSource) {
            
            if(!$this->_checkIfSameDomain($sUrlSource))
            {
                $sUrlTarget=$sUrlSource;
            }
            else
            {
                $aFileSuffix = "";
                if (strpos($sUrlSource, "?") !== false) {
                    $aTmp = explode("?", $sUrlSource);
                    $sUrlSource = $aTmp[0];
                    $aFileSuffix = $aTmp[1];

                }
                $sPathSource = $this->_convertToAbsolutePath($sUrlSource);
                $sPathTarget = $sPathSource.".".md5($sPathSource).".".$sSuffix;
                $sUrlTarget = $this->_convertToUrl($sPathTarget);

                if ($aFileSuffix != "") {
                    $sUrlTarget .= "?".$aFileSuffix;
                }

                if ( ! file_exists($sPathTarget)) {
                    $aPathInfo = pathinfo($sPathSource);
                    $sSource = file_get_contents($sPathSource);

                    //try find old files and delete
                    $aDelList = glob($sPathSource.".*".$this->_getUniqueIdentifier().".css");
                    if(is_array($aDelList))
                    {
                        foreach($aDelList as $sDelPath)
                            @unlink($sDelPath);
                    }

                    //compile scss
                    if ($aPathInfo['extension'] == "scss") {
                        if ((bool)$this->getConfig()
                            ->getConfigParam('rs-optimize_compile_scss')
                        ) {
                            $sSource = $this->_checkStyleScss($sSource, $aPathInfo['dirname']);
                        }
                    }

                    //minimize
                    if ((bool)$this->getConfig()
                        ->getConfigParam('rs-optimize_min_css')
                    ) {
                        $sSource = $this->_checkStyleMinimize($sSource,
                            $aPathInfo['dirname']);
                    }

                    //save
                    file_put_contents($sPathTarget, $sSource);
                }
            }

            $sStyleFinish[] = $sUrlTarget;
        }

        return $sStyleFinish;
    }


    /**
     * http://leafo.github.io/scssphp/
     *
     * @param string $sSource
     * @param string $sIncludePath
     *
     * @return string
     */
    protected function _checkStyleScss($sSource, $sIncludePath)
    {
        $scss = new \Leafo\ScssPhp\Compiler();
        $scss->setImportPaths($sIncludePath);

        return $scss->compile($sSource);
    }

    /**
     * https://github.com/matthiasmullie/minify
     *
     * @param string $sSource
     * @param string $sIncludePath
     *
     * @return string
     */
    protected function _checkStyleMinimize($sSource, $sIncludePath)
    {
        $minifier = new  \MatthiasMullie\Minify\CSS();
        $minifier->add($sSource);

        // or just output the content
        return $minifier->minify();
    }

#endregion

#region "JS"

    /**
     * @param string[] $aScript
     *
     * @return string[]
     */
    public function checkScripts($aScript)
    {
        $sStyleFinish = [];
        foreach ($aScript as $prio => $aUrlSource) {
            foreach ($aUrlSource as $sUrlSource) {
                $sUrlTarget = $this->checkScriptFile($sUrlSource);
                $sStyleFinish[$prio][] = $sUrlTarget;
            }
        }

        return $sStyleFinish;
    }

    /**
     * @param string $sUrlSource
     *
     * @return string
     */
    public function checkScriptFile($sUrlSource)
    {

        if ( ! (bool)$this->getConfig()
            ->getConfigParam('rs-optimize_active_js')
        ) {
            return $sUrlSource;
        }

        if(!$this->_checkIfSameDomain($sUrlSource))
            return $sUrlSource;

        $sSuffix = trim($this->getConfig()->getConfigParam('rs-optimize_suffix_js'));
        if ($sSuffix == "now") {
            $sSuffix = time();
        }
        $sSuffix.=$this->_getUniqueIdentifier().".js";


        $aFileSuffix = "";
        if (strpos($sUrlSource, "?") !== false) {
            $aTmp = explode("?", $sUrlSource);
            $sUrlSource = $aTmp[0];
            $aFileSuffix = $aTmp[1];

        }

        $sPathSource = $this->_convertToAbsolutePath($sUrlSource);
        $sPathTarget = $sPathSource.".".md5($sPathSource).".".$sSuffix;
        $sUrlTarget = $this->_convertToUrl($sPathTarget);

        if ($aFileSuffix != "") {
            $sUrlTarget .= "?".$aFileSuffix;
        }

        if ( ! file_exists($sPathTarget)) {
            $aPathInfo = pathinfo($sPathSource);
            $sSource = file_get_contents($sPathSource);

            //try find old files and delete
            $aDelList = glob($sPathSource.".*".$this->_getUniqueIdentifier().".js");
            if(is_array($aDelList))
            {
                foreach($aDelList as $sDelPath)
                    @unlink($sDelPath);
            }

            //minimize
            if ((bool)$this->getConfig()->getConfigParam('rs-optimize_min_js')) {
                $sSource = $this->_checkJsMinimize($sSource,
                    $aPathInfo['dirname']);
            }

            //save
            file_put_contents($sPathTarget, $sSource);
        }

        //echo '<hr>';

        return $sUrlTarget;
    }

    /**
     * @param string $sSource
     * @param string $sIncludePath
     *
     * @return string
     */
    public function _checkJsMinimize($sSource, $sIncludePath)
    {
        $minifier = new  \MatthiasMullie\Minify\JS();
        $minifier->add($sSource);

        // or just output the content
        return $minifier->minify();
    }

    /**
     * @param $aSource
     *
     * @return string[]
     */
    public function checkScriptSnippets($aSource)
    {
        if ( ! (bool)$this->getConfig()
            ->getConfigParam('rs-optimize_active_js')
        ) {
            return $aSource;
        }
        
        $aNew = [];
        foreach ($aSource as $sKey => $sSource) {
            if ($sSource != "") {
                $aNew[$sKey] = $this->_checkJsMinimize($sSource, "");
            }
        }

        return $aNew;
    }

#endregion
}