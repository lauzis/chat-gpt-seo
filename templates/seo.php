<h1>
    Seo audit
</h1>

<?php include(CHAT_GPT_SEO_PLUGIN_DIR.'/templates/seo/section-keywords.php'); ?>

<?php

$postSettings = [
    "Pages" => [
        'post_type' => 'page',
        'post_status' => ['publish', 'draft'],
        'numberposts' => -1,
        'suppress_filters' => false
    ],
    "Posts" => [
        'post_type' => 'post',
        'post_status' => ['publish', 'draft'],
        'numberposts' => -1,
        'suppress_filters' => false
    ]
];
?>

<h2>
    Status: <div class="chat-gpt-seo-status-wrap">
        <div class="chat-gpt-seo-status"></div>
    </div>
</h2>

<?php foreach ($postSettings as $type => $settings): ?>
    <h2><?= $type ?></h2>
    <?php $items = get_posts($settings); ?>


    <?php if ($items): ?>
        <?php include(CHAT_GPT_SEO_PLUGIN_DIR . "/templates/seo/table-header.php"); ?>
        <?php include(CHAT_GPT_SEO_PLUGIN_DIR . "/templates/seo/table-content.php"); ?>
        <?php include(CHAT_GPT_SEO_PLUGIN_DIR . "/templates/seo/table-footer.php"); ?>
    <?php else : ?>
        <pre>
            <?php print_r($items); ?>
        </pre>
    <?php endif; ?>

<?php endforeach; ?>

