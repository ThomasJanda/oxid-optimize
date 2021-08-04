<?php

namespace rs\optimize\Core;

class Optimize extends \OxidEsales\Eshop\Core\Base
{

    protected function _data_uri($file, $mime)
    {
        $contents = file_get_contents($file);
        $base64   = base64_encode($contents);
        return ('data:' . $mime . ';base64,' . $base64);
    }

    /**
     * @return string
     */
    protected function _getUniqueIdentifier($bViewClass=false)
    {
        $sName = "_".md5(__CLASS__)."_RSOPTIMIZED";
        if($bViewClass)
        {
            $ids = \OxidEsales\Eshop\Core\Registry::getConfig()->getActiveViewsIds();
            $sName = "_".reset($ids).$sName;
        }

        return $sName;
    }

    protected function _deleteOldFiles($pattern)
    {
        $aDelList = glob($pattern);
        if(is_array($aDelList))
        {
            foreach($aDelList as $sDelPath)
                @unlink($sDelPath);
        }
    }

    /**
     * @param $sUrl
     *
     * @return string
     */
    protected function _convertToAbsolutePath($sUrl)
    {
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();

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
        $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();

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
            $oConfig = \OxidEsales\Eshop\Core\Registry::getConfig();
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
     * TODO: import external files lower 5 kb into the source
     *
     * @param $sPathSource
     *
     * @return false|string
     */
    protected function _compileCss($sPathSource, $isGroup)
    {

        $aPathInfo = pathinfo($sPathSource);
        $sSource = file_get_contents($sPathSource);

        //compile scss
        if ($aPathInfo['extension'] == "scss") {
            if ((bool)\OxidEsales\Eshop\Core\Registry::getConfig()
                ->getConfigParam('rs-optimize_compile_scss')
            ) {
                $sSource = $this->_checkStyleScss($sSource, $aPathInfo['dirname']);
            }
        }

        if((bool)\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('rs-optimize_min_css_image'))
        {
            $reg = "/(?:url\()(?!['\"]?(?:data|http))['\"]?([^'\"\)\s>]+)/";
            $sSource = preg_replace_callback($reg,function($match) use ($aPathInfo) {
                $path = rtrim($aPathInfo['dirname'],"/")."/".$match[1];
                if(file_exists($path))
                {
                    $e = strtolower(substr($path,strlen($path)-4));
                    if($e===".png" || $e===".gif" || $e===".jpg")
                    {
                        //if file exists and file size < 10 kb
                        if(file_exists($path) && filesize($path) < (10 * 1024))
                        {
                            $data = $this->_data_uri($path,mime_content_type($path));
                            return str_replace($match[1], $data, $match[0]);
                        }
                    }
                }
                return $match[0];
            }, $sSource);
        }


        //minimize
        if((bool)\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('rs-optimize_min_css')
        ) {
            $sSource = $this->_checkStyleMinimize($sSource,
                $aPathInfo['dirname']);
        }

        return $sSource;
    }

    /**
     * @param $Suffix
     *
     * @return array
     */
    protected function _getGroups($Suffix)
    {
        //group logic
        $Groups = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('rs-optimize_group_css');
        if(!is_array($Groups))
            $Groups = [];

        $tmpGroups = [];
        foreach($Groups as $Group)
        {
            if(strpos($Group,"|")!==false) {
                $tmp = explode("|", $Group);

                $GroupName = trim($tmp[0]);
                $File = trim($tmp[1]);

                if($GroupName!="" && $File!="")
                {
                    if(!isset($tmpGroups[$GroupName]))
                    {
                        $tmpGroups[$GroupName]['settings']['group'] = true;
                        $tmpGroups[$GroupName]['settings']['path'] = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sShopDir')."out/".$GroupName.$Suffix;
                        $tmpGroups[$GroupName]['settings']['url'] = $this->_convertToUrl($tmpGroups[$GroupName]['settings']['path']);
                    }
                    $tmpGroups[$GroupName]['files'][]=$File;
                }
            }
        }
        $Groups=$tmpGroups;

        return $Groups;
    }


    /**
     * @param string[] $aStyle
     *
     * @return string[]
     */
    public function checkStyle($aStyle)
    {
        if (
                ((bool) \OxidEsales\Eshop\Core\Registry::getConfig()->isAdmin() && !(bool)\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('rs-optimize_min_css_admin'))
                ||
                ! (bool)\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('rs-optimize_active_css')
        ) {
            return $aStyle;
        }

        $sSuffix = trim(\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('rs-optimize_suffix_css'));
        if ($sSuffix == "now") {
            $sSuffix = time();
        }
        $sSuffixFile  = $this->_getUniqueIdentifier().".css";
        $sSuffixGroup = $this->_getUniqueIdentifier(true).".css";

        //rewrite start from here
        $sStyleFinish=[];
        $aGroups = $this->_getGroups($sSuffix.$sSuffixGroup);
        $iGroupIndex=0;

        //extend original array with more information
        foreach($aStyle as $sUrlSource)
        {
            if(!$this->_checkIfSameDomain($sUrlSource))
            {
                $sStyleFinish[]=$sUrlSource;
            }
            else
            {
                //convert all path informations
                $sFileSuffix = "";
                if (strpos($sUrlSource, "?") !== false) {
                    $aTmp = explode("?", $sUrlSource);
                    $sUrlSource = $aTmp[0];
                    $sFileSuffix = $aTmp[1];
                }

                $sPathSource = $this->_convertToAbsolutePath($sUrlSource);
                $sPathTarget = $sPathSource.".".md5($sPathSource).".".$sSuffix.$sSuffixFile;
                $sUrlTarget = $this->_convertToUrl($sPathTarget);
                $sPathShopRoot = substr($sPathSource,strlen(\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sShopDir')));

                //test if it is in a group
                $bFound = false;
                foreach($aGroups as $sGroupName => $aGroup)
                {
                    if(is_array($aGroup['files']) && in_array($sPathShopRoot, $aGroup['files']))
                    {
                        $item=[];
                        $item['pathSource']=$sPathSource;
                        $item['pathTarget']=$sPathTarget;
                        $item['url']=$sUrlTarget;
                        $item['suffix'] = $sFileSuffix;
                        $aGroups[$sGroupName]['compile'][]=$item;
                        $bFound=true;
                        break;
                    }
                }
                if(!$bFound)
                {
                    $item=[];
                    $item['pathSource']=$sPathSource;
                    $item['pathTarget']=$sPathTarget;
                    $item['url']=$sUrlTarget;
                    $item['suffix'] = $sFileSuffix;
                    $aGroups[$iGroupIndex]['compile'][]=$item;
                    $aGroups[$iGroupIndex]['settings']['group']=false;
                    $aGroups[$iGroupIndex]['settings']['path']=$sPathTarget;
                    $aGroups[$iGroupIndex]['settings']['url']=$sUrlTarget;
                    $iGroupIndex++;
                }
            }
        }

        //compile everything
        foreach($aGroups as $sGroupName => $aGroup) {
            if (!file_exists($aGroup['settings']['path'])) {
                $sSource=[];
                $isGroup = $aGroup['settings']['group'];
                foreach($aGroup['compile'] as $item)
                {
                    if(file_exists($item['pathSource']))
                    {
                        $sSource[]="/**** ".basename($item['pathSource'])." ****/";
                        $sSource[]=$this->_compileCss($item['pathSource'], $isGroup);
                    }
                }
                $tmp = rtrim(dirname($aGroup['settings']['path']),"/")."/";
                if($isGroup)
                    $tmp.=$sGroupName."*".$sSuffixGroup;
                else
                    $tmp.=$sGroupName."*".$sSuffixFile;

                $this->_deleteOldFiles($tmp);

                file_put_contents($aGroup['settings']['path'], implode("\n",$sSource));
            }
        }

        //output everything
        foreach($aGroups as $aGroup) {
            if (file_exists($aGroup['settings']['path'])) {
                //compiled before
                $suffix=[];
                foreach($aGroup['compile'] as $item)
                {
                    if($item['suffix']!="")
                        $suffix[]=$item['suffix'];
                }
                $link = $aGroup['settings']['url'];
                if(count($suffix)>0)
                {
                    $link.="?".implode("&",$suffix);
                }
                $sStyleFinish[]=$link;
            }
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
        $scss = new \ScssPhp\ScssPhp\Compiler();
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
            /** @var array $aUrlSource */
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

        if ( ! (bool)\OxidEsales\Eshop\Core\Registry::getConfig()
            ->getConfigParam('rs-optimize_active_js')
        ) {
            return $sUrlSource;
        }

        if(!$this->_checkIfSameDomain($sUrlSource))
            return $sUrlSource;

        $sSuffix = trim(\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('rs-optimize_suffix_js'));
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
            $pattern = $sPathSource.".*".$this->_getUniqueIdentifier().".js";
            $this->_deleteOldFiles($pattern);

            //minimize
            if ((bool)\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('rs-optimize_min_js')) {
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
        if ( ! (bool)\OxidEsales\Eshop\Core\Registry::getConfig()
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
