<?php
if (empty($id)) {
    ?>
    <tr>
        <td colspan="13">
            Missing ID
        </td>
    </tr>
    <?php
}
$url = get_the_permalink($id);
$report = false;

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

<td colspan="18">
    <div class="seo-more-info-container">
        <p>
            <strong>Post title </strong>:<?= get_the_title($id); ?> <br/>
            <strong>Title (title tag in header)</strong>: <?= $report['meta_title_text']; ?> <br/>
            <strong>H1</strong>: <?= $report['h1_text']; ?> <br/>
            <strong>First paragraph</strong>: <?= $report['first_paragraph']; ?> <br/>
            <strong>Meta description</strong>: <?= get_post_meta($id, '_yoast_wpseo_metadesc', true) ?> <br/>
            <strong>Keywords found</strong>:

        <ul class="found-keywords">
            <?php foreach ($report['keywords'] as $keyword): ?>
                <li>
                    <?php if ($keyword['count'] > 0): ?>
                        <?= $keyword['keyword']; ?> (<?= $keyword['count']; ?>) found in:
                        <ul class="found-in-places">
                            <?php foreach ($keyword['place_where_found'] as $place): ?>
                                <li>
                                    <span class="found-in">
                                        <?= ucfirst(str_replace("_", " ", $place)); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($keyword['variations'] as $kv): ?>
                        <?php if ($kv['count'] > 0): ?>
                        <li>
                            <?= $kv['keyword']; ?> (<?= $kv['count']; ?>) found in:
                            <ul class="found-in-places">
                                <?php foreach ($kv['place_where_found'] as $place): ?>
                                    <li>
                                            <span class="found-in">
                                            <?= ucfirst(str_replace("_", " ", $place)); ?>
                                            </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <li>
                            <?php endif; ?>
                            <?php endforeach; ?>
                    </ul>
                </li>

            <?php endforeach; ?>
        </ul>

        <br/>
        </p>
        <br/>
        <div class="chat-gpt-seo-form">

            <form id="seo-description-form-<?= $id ?>" class="seo-description-form">

                <h3>Edit and update meta description </h3>

                <textarea
                        name="meta_description"
                        id="seo-description-<?= $id; ?>"
                ><?= get_post_meta($id, '_yoast_wpseo_metadesc', true) ?></textarea>
                <div class="keyword-list">
                    <ul>
                        <?php foreach ($report['keywords'] as $keyword): ?>

                            <li>
                                <label>
                                    <input type="checkbox"
                                           name="keywords[]"
                                           value="<?= $keyword['keyword']; ?>"
                                    /><?= $keyword['keyword']; ?>
                                    (<?= $keyword['count']; ?>)
                                </label>
                                <ul>
                                    <?php foreach ($keyword['variations'] as $kv): ?>
                                        <li>
                                            <label>
                                                <input
                                                        name="keywords[]"
                                                        type="checkbox"
                                                        value="<?= $kv['keyword']; ?>"
                                                /> <?= $kv['keyword']; ?>
                                                (<?= $kv['count']; ?>)
                                            </label>
                                        </li>
                                    <?php endforeach; ?>

                                </ul>
                            </li>


                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="button-row">
                    <a onclick="updateMetaDescription(<?= $id; ?>)">Update meta description</a>
                    <div>
                        <a id="generate-button-<?= $id; ?>" onclick="generateMetaDescription(<?= $id; ?>)">Generate meta
                            description</a>

                    </div>
                </div>
                <div class="button-row">
                    <div>
                        &nbsp;
                    </div>
                    <label>
                        <input type="checkbox" name="force-keyword" value="1"/> Force use keyword
                    </label>
                </div>

            </form>

        </div>

        <div class="button-row">
            <a class="secondary" target="_blank" href="<?= $report_url; ?>">Full report</a> <br/>
            <a class="secondary" target="_blank" href="<?= $report_url; ?>">Scraped HTML</a>
        </div>

    </div>

</td>

