<?php
namespace rs\optimize\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Request;

class rs_optimize_clear_cache extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController
{
    public function render()
    {
        parent::render();
        return "rs_optimize_clear_cache.tpl";
    }

    protected $_aFileCount = false;
    public function getFileCount()
    {
        if($this->_aFileCount===false)
        {
            $aList = [];

            /** @var \rs\optimize\Core\OptimizeDb $oOptimize */
            $oOptimize = oxNew(\rs\optimize\Core\OptimizeDb::class);
            $sPath = $oOptimize->getCacheDirectory();

            if($handle=opendir($sPath))
            {
                while ($sFilename = readdir ($handle)) {

                    if($sFilename!="." && $sFilename!="..")
                    {
                        $aItem=[];
                        $aItem['sPath']=$sPath;
                        $aItem['sDirectory']=$sFilename;
                        $aItem['sFullpath']=$sPath."/".$sFilename;

                        $fi = new \FilesystemIterator($aItem['sFullpath'], \FilesystemIterator::SKIP_DOTS);
                        $aItem['iFilecount'] = iterator_count($fi);

                        $aList[]=$aItem;
                    }
                }
                closedir($handle);
            }

            $this->_aFileCount=$aList;
        }

        return $this->_aFileCount;
    }
    public function hasFileCount()
    {
        $aList = $this->getFileCount();
        if(count($aList)>0)
            return true;
        return false;
    }

    public function deleteCacheFiles()
    {

        $aList = $this->getFileCount();
        foreach($aList as $aItem)
        {
            $files = glob($aItem['sFullpath'].'/*'); // get all file names
            foreach($files as $file){ // iterate files
                if(is_file($file))
                    unlink($file); // delete file
            }
        }

    }
}
