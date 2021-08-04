<?php

namespace rs\optimize\Core\ViewHelper;

/**
 * Class StyleRenderer
 *
 * @see     \OxidEsales\EshopCommunity\Core\ViewHelper\StyleRenderer
 *
 */
class StyleRenderer extends StyleRenderer_parent
{
    protected function formStylesOutput($styles)
    {
        /** @var \rs\optimize\Core\OptimizeDb $oOptimize */
        $oOptimize = oxNew(\rs\optimize\Core\Optimize::class);
        $styles = $oOptimize->checkStyle($styles);

        return parent::formStylesOutput($styles);
    }
}