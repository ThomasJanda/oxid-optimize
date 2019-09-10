<?php
$sMetadataVersion = '2.0';

$aModule = array(
    'id'          => 'rs-optimize',
    'title'       => '*RS Optimize',
    'description' => 'Cache, minimize CSS, JS, DB List queries',
    'thumbnail'   => '',
    'version'     => '0.0.5',
    'author'      => '',
    'url'         => '',
    'email'       => '',
    'extend'      => array(
        \OxidEsales\Eshop\Core\UtilsView::class                     => \rs\optimize\Core\UtilsView::class,
        \OxidEsales\Eshop\Core\ViewHelper\StyleRenderer::class      => \rs\optimize\Core\ViewHelper\StyleRenderer::class,
        \OxidEsales\Eshop\Core\ViewHelper\JavaScriptRenderer::class => \rs\optimize\Core\ViewHelper\JavaScriptRenderer::class,
        \OxidEsales\Eshop\Core\Model\ListModel::class               => \rs\optimize\Model\ListModel::class,
        \OxidEsales\Eshop\Application\Model\ActionList::class       => \rs\optimize\Application\Model\ActionList::class,
        \OxidEsales\Eshop\Application\Model\ArticleList::class      => \rs\optimize\Application\Model\ArticleList::class,
        \OxidEsales\Eshop\Application\Model\CategoryList::class     => \rs\optimize\Application\Model\CategoryList::class,
        \OxidEsales\Eshop\Application\Model\CountryList::class      => \rs\optimize\Application\Model\CountryList::class,
        \OxidEsales\Eshop\Application\Model\DeliveryList::class     => \rs\optimize\Application\Model\DeliveryList::class ,
        \OxidEsales\Eshop\Application\Model\DeliverySetList::class  => \rs\optimize\Application\Model\DeliverySetList::class,
        \OxidEsales\Eshop\Application\Model\DiscountList::class     => \rs\optimize\Application\Model\DiscountList::class,
        \OxidEsales\Eshop\Application\Model\ContentList::class      => \rs\optimize\Application\Model\ContentList::class,
        \OxidEsales\Eshop\Application\Model\ManufacturerList::class => \rs\optimize\Application\Model\ManufacturerList::class,
        \OxidEsales\Eshop\Application\Model\ShopList::class         => \rs\optimize\Application\Model\ShopList::class,
        \OxidEsales\Eshop\Application\Model\PaymentList::class      => \rs\optimize\Application\Model\PaymentList::class,
        \OxidEsales\Eshop\Application\Model\NewsList::class         => \rs\optimize\Application\Model\NewsList::class,
        \OxidEsales\Eshop\Application\Model\VendorList::class       => \rs\optimize\Application\Model\VendorList::class,
    ),
    'controllers' => array(
        'rs_optimize_clear_cache' => rs\optimize\Application\Controller\Admin\rs_optimize_clear_cache::class,
    ),
    'templates'   => array(
        'rs_optimize_clear_cache.tpl'      => 'rs/optimize/views/admin/tpl/rs_optimize_clear_cache.tpl',
    ),
    'settings'    => array(
        array(
            'group' => 'rs-optimize_main_html',
            'name'  => 'rs-optimize_minimize_html',
            'type'  => 'bool',
            'value' => false,
        ),
        array(
            'group' => 'rs-optimize_main_css',
            'name'  => 'rs-optimize_active_css',
            'type'  => 'bool',
            'value' => false,
        ),
        array(
            'group' => 'rs-optimize_main_css',
            'name'  => 'rs-optimize_compile_scss',
            'type'  => 'bool',
            'value' => false,
        ),
        array(
            'group' => 'rs-optimize_main_css',
            'name'  => 'rs-optimize_min_css',
            'type'  => 'bool',
            'value' => false,
        ),
        array(
            'group' => 'rs-optimize_main_css',
            'name'  => 'rs-optimize_suffix_css',
            'type'  => 'str',
            'value' => '',
        ),

        array(
            'group' => 'rs-optimize_main_js',
            'name'  => 'rs-optimize_active_js',
            'type'  => 'bool',
            'value' => false,
        ),
        array(
            'group' => 'rs-optimize_main_js',
            'name'  => 'rs-optimize_min_js',
            'type'  => 'bool',
            'value' => false,
        ),
        array(
            'group' => 'rs-optimize_main_js',
            'name'  => 'rs-optimize_suffix_js',
            'type'  => 'str',
            'value' => '',
        ),

        array(
            'group' => 'rs-optimize_main_db',
            'name'  => 'rs-optimize_active_db',
            'type'  => 'bool',
            'value' => false,
        ),
        array(
            'group' => 'rs-optimize_main_db',
            'name'  => 'rs-optimize_suffix_db',
            'type'  => 'str',
            'value' => '',
        ),
    ),
);