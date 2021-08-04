<?php

namespace rs\optimize\Core;

class UtilsView extends UtilsView_parent
{
    /**
     * @param bool $blReload
     *
     * @return mixed
     */
    public function getSmarty($blReload = false)
    {
        $oSmarty = parent::getSmarty($blReload);

        if ((bool)\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('rs-optimize_minimize_html'))
        {
            $oSmarty->load_filter('output', 'trimwhitespace');
        }

        return $oSmarty;
    }
}
