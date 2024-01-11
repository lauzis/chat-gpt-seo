<?php

/**
 * Plugin Name: Chat GPT SEO
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
    $baseDir = $uploadDir['basedir'] . '/chat-gpt-seo';
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
    $baseDir = $uploadDir['baseurl'] . '/chat-gpt-seo';
    define('CHAT_GPT_SEO_UPLOAD_URL', untrailingslashit($baseDir));
}

if (!defined('CHAT_GPT_SEO_REPORT_URL')) {
    $url = CHAT_GPT_SEO_UPLOAD_URL . '/report';
    define('CHAT_GPT_SEO_REPORT_URL', $url);
}


require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/ChatGptSeoHelpers.php');
require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/ChatGptSeoApi.php');
require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/ChatGptSeo.php');
require(CHAT_GPT_SEO_PLUGIN_DIR . '/classes/ChatBot.php');

function CHAT_GPT_SEO_init():void
{

    $chatGptSeo = new \ChatGptSeo\ChatGptSeo();
    $chatGptSeo->init();

    if (current_user_can('editor') || current_user_can('administrator')){
        $id = $_GET['id'] ?? null;

        if (isset($_GET['chat-gpt-seo-test'])){
            switch ($_GET['chat-gpt-seo-test']){
                case 'keywords': ?>
                    <pre>
                    <?php print_r(\ChatGptSeo\ChatGptSeoHelpers::get_keywords()); ?>
                </pre>

                    <pre>
                    <?php print_r(\ChatGptSeo\ChatGptSeoHelpers::get_keywords($id)); ?>
                </pre>
                    <?php
                    break;
                case 'audit':
                    if ($id){?>
                        <pre>
                            <?php
                            $url = get_the_permalink($id);
                            \ChatGptSeo\ChatGptSeoHelpers::remove_report($url);
                        print_r(\ChatGptSeo\ChatGptSeoApi::audit_item($id));
                        ?>
                            </pre>
<?php
                    }
                    break;

            }
            die();
        }
    }

}

add_action('init', 'CHAT_GPT_SEO_init');

