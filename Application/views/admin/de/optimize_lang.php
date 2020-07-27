<?php
$sLangName = "Deutsch";

$aLang = array(
    'charset' => 'UTF-8',

    'SHOP_MODULE_GROUP_rs-optimize_main_html' => 'Settings HTML',
    'SHOP_MODULE_rs-optimize_minimize_html' => 'Minimize HTML?',

    'SHOP_MODULE_GROUP_rs-optimize_main_css' => 'Settings CSS',
    'SHOP_MODULE_rs-optimize_active_css' => 'Active?',
    'SHOP_MODULE_rs-optimize_min_css_admin' => 'Compile also admin area?',
    'SHOP_MODULE_rs-optimize_compile_scss' => 'Compile SCSS?',
    'SHOP_MODULE_rs-optimize_min_css_image' => 'Embed images into CSS file',
    'SHOP_MODULE_rs-optimize_min_css' => 'Minimize?',
    'SHOP_MODULE_rs-optimize_suffix_css' => 'Suffix ("now" will replace with the current timestamp)',
    'SHOP_MODULE_rs-optimize_group_css' => 'If you like to combine CSS files use follwing schema #GROUP_NAME#|#ABSOLUTE_PATH_FROM_SHOP_ROOT#. All files with the same group name will combine to one file and safe in the out folder. The file will add to the top of the list of css files. For each view of the shop, the file will recreate.',

    'SHOP_MODULE_GROUP_rs-optimize_main_js' => 'Settings JS',
    'SHOP_MODULE_rs-optimize_active_js' => 'Active?',
    'SHOP_MODULE_rs-optimize_min_js' => 'Minimize?',
    'SHOP_MODULE_rs-optimize_suffix_js' => 'Suffix ("now" will replace with the current timestamp)',

    'SHOP_MODULE_GROUP_rs-optimize_main_db' => 'Settings DB',
    'SHOP_MODULE_rs-optimize_active_db' => 'Active? (All lists will cache in the tmp folder and will never refresh)',
    'SHOP_MODULE_rs-optimize_not_cachable_db' => 'Following classes not cachable. Separate with | (Default: oxreview|oxorder|oxorderarticles|oxuser|oxuserbasketitem|oxuserbasket|oxuserpayment|oxrecommlist)',
    'SHOP_MODULE_rs-optimize_only_cachable_ox_db' => 'Only cache object which starts with "ox". Means only the oxid classes.',
    'SHOP_MODULE_rs-optimize_display_names_in_shop_db' => 'Display object name in shop frontend (Debug mode)',
    'SHOP_MODULE_rs-optimize_suffix_db' => 'Suffix ("now" will replace with the current date, create every day a new folder. Tmp-Folder have to clear from time to time.)',

    'navi_rs_company' => 'Reisacher Software',
    'navi_rs_optimize' => 'Optimize',
    'navi_rs_optimize_clear_cache' => 'Clear cache',
);