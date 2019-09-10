[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign box=" "}]

<h1>Cache files</h1>
<div style="margin-bottom:20px; ">
    Contain all files which created during caching the lists which receive from the DB.
</div>

<div style="margin-bottom:20px; ">
    [{assign var=aList value=$oView->getFileCount()}]
    [{foreach from=$aList item=aItem name=list}]
        [{$smarty.foreach.list.iteration}]. Directory: [{$aItem.sDirectory}], File count: [{$aItem.iFilecount}]<br>
    [{foreachelse}]
        No cache files found
    [{/foreach}]
</div>

[{if $oView->hasFileCount()}]
    <div style="margin-bottom:20px; ">
        <form action="[{$oViewConf->getSelfLink()}]" method="post">
            [{$oViewConf->getHiddenSid()}]
            <input type="hidden" name="cl" value="rs_optimize_clear_cache">
            <input type="hidden" name="fnc" value="deleteCacheFiles">

            <button class="button" type="submit">Clear cache</button>
        </form>
    </div>
[{/if}]

</body>
</html>