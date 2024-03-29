<?php

namespace rs\optimize\Core\ViewHelper;

/**
 * Class JavaScriptRenderer
 *
 * @see     \OxidEsales\EshopCommunity\Core\ViewHelper\JavaScriptRenderer
 *
 */
class JavaScriptRenderer extends JavaScriptRenderer_parent
{
    protected function formFilesOutput($includes, $widget)
    {
        /** @var \rs\optimize\Core\OptimizeDb $oOptimize */
        $oOptimize = oxNew(\rs\optimize\Core\Optimize::class);
        $includes = $oOptimize->checkScripts($includes);

        return parent::formFilesOutput($includes, $widget);
    }

    protected function formSnippetsOutput($scripts, $widgetName, $ajaxRequest)
    {
        /** @var \rs\optimize\Core\OptimizeDb $oOptimize */
        $oOptimize = oxNew(\rs\optimize\Core\Optimize::class);
        $scripts = $oOptimize->checkScriptSnippets($scripts);

        return parent::formSnippetsOutput($scripts, $widgetName, $ajaxRequest);
    }
}