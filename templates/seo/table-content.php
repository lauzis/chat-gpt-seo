<?php $missingMetaDescriptions = 0; ?>
<?php $missingMetaDescriptions_noKeywords = 0; ?>
<?php $missingMetaTitle_noKeywords = 0; ?>
<?php $totalCount = count($items); ?>

<?php foreach ($items as $item): ?>
    <?php
    $meta_description_keyword_found = false;
    $meta_title_keyword_found = false;
    ?>

    <?php $id = $item->ID; ?>


<?php include(CHAT_GPT_SEO_PLUGIN_DIR.'/templates/seo/table-content-item.php'); ?>

<?php endforeach; ?>

<!--<tr>-->
<!--    <td colspan="14">-->
<!--        <p>-->
<!--            No keyword in meta title: --><?php //= $missingMetaTitle_noKeywords ?><!-- / --><?php //= $totalCount ?>
<!--            => --><?php //= floor($missingMetaTitle_noKeywords / $totalCount * 100) ?><!--% <br/>-->
<!---->
<!--            Missing meta description: --><?php //= $missingMetaDescriptions ?><!-- / --><?php //= $totalCount ?>
<!--            => --><?php //= floor($missingMetaDescriptions / $totalCount * 100) ?><!--% <br/>-->
<!---->
<!--            No keyword in meta description: --><?php //= $missingMetaDescriptions_noKeywords ?><!-- / --><?php //= $totalCount ?>
<!--            => --><?php //= floor($missingMetaDescriptions_noKeywords / $totalCount * 100) ?><!--% <br/>-->
<!--        </p>-->
<!---->
<!--    </td>-->
<!--</tr>-->


