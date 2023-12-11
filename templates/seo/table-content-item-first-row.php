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
}

$time = date("Y-m-d H:i:s");
if ($report){
    $time = date("Y-m-d H:i:s", $report['timestamp']);
}



?>

    <td>
        <?= get_the_title($id); ?>
    </td>

    <td><?php \ChatGptSeo\ChatGptSeoHelpers::get_audit_status($report && $report['meta_title_keyword_found']); ?></td>
    <td><?php \ChatGptSeo\ChatGptSeoHelpers::get_audit_status($report && $report['meta_description']); ?></td>
    <td><?php \ChatGptSeo\ChatGptSeoHelpers::get_audit_status($report && $report['meta_description_keyword_found']); ?></td>
    <td><?php \ChatGptSeo\ChatGptSeoHelpers::get_audit_status($report && $report['h1_count'] > 0); ?></td>
    <td><?php \ChatGptSeo\ChatGptSeoHelpers::get_audit_status($report && $report['h1_found_keyword'] > 0); ?></td>
    <td><?php \ChatGptSeo\ChatGptSeoHelpers::get_audit_status($report && $report['first_paragraph_found_keywords']); ?></td>
    <td><?php \ChatGptSeo\ChatGptSeoHelpers::get_audit_status($report && $report['content_has_keywords']); ?></td>

    <td><?php \ChatGptSeo\ChatGptSeoHelpers::get_audit_status($report && isset($report['img_alt_missing']) ? floor(count($report['img_alt_missing']) / $report['img_count'] * 100) : false); ?></td>


    <td><span id="penalty-<?= $id ?>"><?= $penalty ?></span></td>
    <td><span ><?= $time ?></span></td>

    <td>
        <a onclick="expandReport(<?= $id; ?>)">More info</a>
    </td>
    <td>
        <a onclick="reAudit(<?= $id; ?>)">ReAudit</a>
    </td>
    <td>
        <a href="<?= get_the_permalink($id); ?>" target="_blank">View page</a>
    </td>
    <td>
       --> <?php edit_post_link(__('Edit page'), "", "", $id); ?><--
    </td>



