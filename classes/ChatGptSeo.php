<?php

namespace ChatGptSeo;


const LimeResyncThreshold = (60 * 30);
//treshold for retry, if fails sync (lime is not responsive or something)
//we will retry same object after hour.


class ChatGptSeo
{
    public function init(): void
    {
        if (is_admin()) {
            $this->add_options_page();
            add_action('admin_menu', [$this, 'add_chat_gpt_seo_log_page']);
        }
        $this->setup_hooks();
        $this->setup_api_routes();
    }

    public static function add_settings_link_to_plugin_list($links)
    {

        $links[] = '<a href="' . self::get_settings_page_url() . '">Settings</a>';
        return $links;
    }

    public static function get_settings_page_url()
    {
        return esc_url(get_admin_url(null, 'options-general.php?page=' . self::get_settings_page_relative_path()));
    }

    public static function get_settings_page_relative_path()
    {
        return 'chat-gpt-seo-settings';
    }



    public function setup_api_routes(): void
    {

        add_action('rest_api_init', function () {
            register_rest_route('chat-gpt-seo/v1', '/audit-item/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => 'ChatGptSeo\ChatGptSeoApi::audit_item',
                'args' => array(
                    'id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ),
                ),
            ));

            register_rest_route('chat-gpt-seo/v1', '/force-audit-item/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => 'ChatGptSeo\ChatGptSeoApi::force_audit_item',
                'args' => array(
                    'id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ),
                ),
            ));

            register_rest_route('chat-gpt-seo/v1', '/update-meta-description/(?P<id>\d+)', array(
                'methods' => 'POST',
                'callback' => 'ChatGptSeo\ChatGptSeoApi::update_meta_description',
                'args' => array(
                    'id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ),
                ),
            ));


            register_rest_route('chat-gpt-seo/v1', '/generate-meta-description/(?P<id>\d+)', array(
                'methods' => 'POST',
                'callback' => 'ChatGptSeo\ChatGptSeoApi::generate_meta_description',
                'args' => array(
                    'id' => array(
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ),
                ),
            ));

        });
    }

    public function cron_add_timing($schedules): array
    {
        if (!isset($schedules["5min"])) {
            $schedules["5min"] = array(
                'interval' => 5 * 60,
                'display' => __('Once every 5 minutes'));
        }
        return $schedules;
    }

    public function add_settings_link($links)
    {
        $settings_link = '<a href="admin.php?page=chat-gpt-seo-logs">' . __('Settings', 'chat-gpt-seo') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }


    private function setup_hooks(): void
    {
        if (isset($_GET['page']) && $_GET['page'] === 'chat-gpt-seo-audit') {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_plugin_styles']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_plugin_scripts']);
        }
    }

    public function enqueue_plugin_styles(): void
    {
        wp_enqueue_style('chat-gpt-seo-css', CHAT_GPT_SEO_PLUGIN_URL . '/assets/css/main.css');
        wp_enqueue_style('chat-gpt-seo-lib-table-styles', CHAT_GPT_SEO_PLUGIN_URL . '/assets/lib/jquery.dataTables.css');

    }

    public function enqueue_plugin_scripts(): void
    {

        wp_enqueue_script('chat-gpt-seo-lib-table-js', CHAT_GPT_SEO_PLUGIN_URL . '/assets/lib/jquery.dataTables.js.js', array('jquery'), CHAT_GPT_SEO_VERSION, true);
        wp_enqueue_script('chat-gpt-seo-js', CHAT_GPT_SEO_PLUGIN_URL . '/assets/js/main.js', array('jquery', 'chat-gpt-seo-lib-table-js'), CHAT_GPT_SEO_VERSION, true);
    }

    public function add_chat_gpt_seo_log_page(): void
    {

//        add_submenu_page(
//            'chat-gpt-seo',
//            __('SEO audit', 'chat-gpt-seo'),
//            __('SEO audit', 'chat-gpt-seo'),
//            'edit_posts',
//            'chat-gpt-seo',
//            [$this, 'seo_audit']
//        );
//
//        add_filter('plugin_action_links_' . plugin_basename(CHAT_GPT_SEO_PLUGIN_FILE), 'ChatGptSeo\ChatGptSeo::add_settings_link_to_plugin_list');


            add_menu_page(
                'SEO Audit',
                'SEO Audit',
                'manage_options',
                'chat-gpt-seo-audit',
                [$this, 'seo_audit'],//'ChatGptSeo\ChatGptSeo::seo_audit',
                'dashicons-chart-bar', // You can change the icon
                85 // Adjust the position as needed
            );

            // Add the second sub-menu item
//            add_submenu_page(
//                'chat-gpt-seo-audit',
//                'Settings',
//                'Settings',
//                'manage_options',
//                'seo_audit_settings_page',
//                'ChatGptSeo\ChatGptSeo::seo_audit_settings_page_callback'
//            );

    }


    public function seo_audit(): void
    {

        ?>
        <div class="wrap">
            <?php include(CHAT_GPT_SEO_PLUGIN_DIR . "/templates/seo.php"); ?>
        </div>
        <?php
    }


    private function add_options_page(): void
    {
        if (function_exists('acf_add_options_page')) {

            $settingsPage = array(
                'page_title' => 'Settings',
                'menu_title' => 'Settings',
                'menu_slug' => 'chat-gpt-seo-settings',
                'capability' => 'edit_posts',
                'redirect' => false,
                'parent_slug' => 'chat-gpt-seo-audit',
            );

            acf_add_options_page($settingsPage);

            $field_group = json_decode(file_get_contents(CHAT_GPT_SEO_PLUGIN_DIR . '/acf_json/group_62f0bc7465155.json'), TRUE);
            acf_add_local_field_group($field_group);
            $field_group = json_decode(file_get_contents(CHAT_GPT_SEO_PLUGIN_DIR . '/acf_json/group_65536c2771900.json'), TRUE);
            acf_add_local_field_group($field_group);
        }
    }

    private function get_api_settings(): array
    {
        $test_mode = get_field('chat_gpt_seo_test_mode', 'option');
        $token = get_field('chat_gpt_seo_test_token', 'option');
    }


    function log($text): void
    {
        $logger = new ChatGptSeoLog();
        $log_data = [
            'timestamp' => time(),
            'time' => date("Y-m-d H:i:s"),
            'chat_got_seo_object' => json_encode($text, JSON_PRETTY_PRINT),
            'log' => $text,
            'post_data' => $text
        ];
        $logger->log($log_data);
    }
}
