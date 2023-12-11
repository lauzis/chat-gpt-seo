<?php
/**
 * Plugin Name: Chat GPT SEO
 * Plugin URI: https://www.awave.com/
 * Description: Checks authentification gainst lime
 * Version: 1.0.0
 * Author: Awave AB
 * Author URI: https://www.awave.com/
 * License: (c) 2020 Awave AB - All right reserved.
 * Modifying, copying, distributing or selling
 * this software is prohibited without written permission.
 */


if (!defined('CHAT_GPT_SEO_PLUGIN_DIR')) {
    define('CHAT_GPT_SEO_PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
}
if (!defined('CHAT_GPT_SEO_UPLOAD_DIR')) {

    $uploadDir = wp_get_upload_dir();
    $baseDir = $uploadDir['basedir'] . '/chat-gpt-seo';
    if (!is_dir($baseDir) && !file_exists($baseDir)) {
        mkdir($baseDir, 0777);
    }
    define('CHAT_GPT_SEO_UPLOAD_DIR', untrailingslashit($baseDir));
}

if (!defined('CHAT_GPT_SEO_UPLOAD_URL')) {

    $uploadDir = wp_get_upload_dir();
    $baseDir = $uploadDir['baseurl'] . '/chat-gpt-seo';
    define('CHAT_GPT_SEO_UPLOAD_URL', untrailingslashit($baseDir));
}




require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/ChatGptSeoHelpers.php');
require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/ChatGptSeoApi.php');
require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/ChatGptSeo.php');
require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/ChatBot.php');


add_action('init', 'CHAT_GPT_SEO_init');
function CHAT_GPT_SEO_init()
{

    $chatGptSeo = new \ChatGptSeo\ChatGptSeo();
    $chatGptSeo->init();

}



