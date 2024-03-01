<?php
/**
 * Plugin Name: SEO Audit
 * Plugin URI: https://www.awave.com/
 * Description: SEO analysis for pages and posts. Generate meta description using chat-gpt.
 * Version: 1.0.11
 * Author: Awave AB
 * Author URI: https://www.awave.com/
 * License: (c) 2020 Awave AB - All right reserved.
 * Modifying, copying, distributing or selling
 * this software is prohibited without written permission.
 */

if (!defined('CHAT_GPT_SEO_VERSION')) {
    define('CHAT_GPT_SEO_VERSION', '1.0.12');
}

if (!defined('CHAT_GPT_SEO_PLUGIN_DIR')) {
    define('CHAT_GPT_SEO_PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
}

if (!defined('CHAT_GPT_SEO_PLUGIN_URL')) {
    define('CHAT_GPT_SEO_PLUGIN_URL', untrailingslashit(plugin_dir_url(__FILE__)));
}

if (!defined('CHAT_GPT_SEO_PLUGIN_FILE')) {
    define('CHAT_GPT_SEO_PLUGIN_FILE', plugin_basename(__FILE__));
}

if (!defined('CHAT_GPT_SEO_UPLOAD_DIR')) {
    $uploadDir = wp_get_upload_dir();
    $baseDir = $uploadDir['basedir'] . '/seo-audit';
    if (!is_dir($baseDir) && !file_exists($baseDir)) {
        mkdir($baseDir, 0777);
    }
    define('CHAT_GPT_SEO_UPLOAD_DIR', untrailingslashit($baseDir));
}

if (!defined('CHAT_GPT_SEO_REPORT_DIR')) {
    $dir = CHAT_GPT_SEO_UPLOAD_DIR . '/report';
    if (!is_dir($dir) && !file_exists($dir)) {
        mkdir($dir, 0777);
    }
    define('CHAT_GPT_SEO_REPORT_DIR', $dir);
}

if (!defined('CHAT_GPT_SEO_UPLOAD_URL')) {
    $uploadDir = wp_get_upload_dir();
    $baseDir = $uploadDir['baseurl'] . '/seo-audit';
    define('CHAT_GPT_SEO_UPLOAD_URL', untrailingslashit($baseDir));
}

if (!defined('CHAT_GPT_SEO_REPORT_URL')) {
    $url = CHAT_GPT_SEO_UPLOAD_URL . '/report';
    define('CHAT_GPT_SEO_REPORT_URL', $url);
}

require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/Helpers.php');
require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/Audit.php');
require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/Init.php');
require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/RestRoutes.php');
require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/Tests.php');
require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/ChatGptApi.php');

function seo_audit_init(): void
{
    $chatGptSeo = new \SeoAudit\Init();
    $chatGptSeo->init();
    \SeoAudit\Tests::tests();
}

add_action('init', 'seo_audit_init');

