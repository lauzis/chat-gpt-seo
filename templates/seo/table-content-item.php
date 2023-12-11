<?php
if (empty($id)){
    ?>
    <tr>
        <td colspan="13">
            Missing ID
        </td>
    </tr>
    <?php
}
$url = get_the_permalink($id);
$report = \ChatGptSeo\ChatGptSeoHelpers::get_report($url);
$report_url = \ChatGptSeo\ChatGptSeoHelpers::get_report_url($url);
$report_html_url = \ChatGptSeo\ChatGptSeoHelpers::get_report_html_url($url);
$penalty = $report ? 0 : "not analysed yet";


if ($report && !$report['meta_description']) {
    $penalty++;
    $penalty++;
    $penalty++;
}
if ($report && $report['h1_count'] == 0) {
    $penalty++;
    $penalty++;
    $penalty++;
}
if ($report && $report['h1_found_keyword'] == 0) {
    $penalty++;
}
if ($report && !$report['meta_description_keyword_found']) {
    $penalty++;
}
if ($report && !$report['first_paragraph_found_keywords']) {
    $penalty++;
}

if ($report && !$report['meta_title_keyword_found']) {
    $penalty++;
} ?>

<tr data-id="<?= $id ?>" id="seo-summary-<?= $id ?>" class="<?= $report ? 'chat-gpt-seo-report-done' : 'chat-gpt-seo-check-post' ?>">
    <?php include(CHAT_GPT_SEO_PLUGIN_DIR."/templates/seo/table-content-item-first-row.php"); ?>
</tr>
<tr id="seo-more-details-<?= $id; ?>" class="seo-more-details">
    <?php include(CHAT_GPT_SEO_PLUGIN_DIR."/templates/seo/table-content-item-second-row.php"); ?>
</tr>

