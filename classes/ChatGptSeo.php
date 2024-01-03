<?php

namespace ChatGptSeo;


const LimeResyncThreshold = (60 * 30);
//treshold for retry, if fails sync (lime is not responsive or something)
//we will retry same object after hour.


class ChatGptSeo
{
    public function init():void
    {
        if (is_admin()) {
            $this->add_options_page();
            add_action('admin_menu', [$this, 'add_chat_gpt_seo_log_page']);
        }



        $this->setup_hooks();
        $this->setup_cron();
        $this->setup_api_routes();
    }

    public function setup_api_routes():void
    {

        add_action( 'rest_api_init', function () {
            register_rest_route( 'chat-gpt-seo/v1', '/audit-item/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => 'ChatGptSeo\ChatGptSeoApi::audit_item',
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric( $param );
                        }
                    ),
                ),
            ) );

            register_rest_route( 'chat-gpt-seo/v1', '/force-audit-item/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => 'ChatGptSeo\ChatGptSeoApi::force_audit_item',
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric( $param );
                        }
                    ),
                ),
            ) );

            register_rest_route( 'chat-gpt-seo/v1', '/update-meta-description/(?P<id>\d+)', array(
                'methods' => 'POST',
                'callback' => 'ChatGptSeo\ChatGptSeoApi::update_meta_description',
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric( $param );
                        }
                    ),
                ),
            ) );


            register_rest_route( 'chat-gpt-seo/v1', '/generate-meta-description/(?P<id>\d+)', array(
                'methods' => 'POST',
                'callback' => 'ChatGptSeo\ChatGptSeoApi::generate_meta_description',
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric( $param );
                        }
                    ),
                ),
            ) );

        } );
    }

    public function cron_add_timing($schedules):array
    {
        if (!isset($schedules["5min"])) {
            $schedules["5min"] = array(
                'interval' => 5 * 60,
                'display' => __('Once every 5 minutes'));
        }
        return $schedules;
    }

    private function setup_cron():void
    {

        if (isset($_GET['run_cron_user_group'])) {
            $this->check_user_meta_for_missing_user_group();
            die();
        }

        add_filter('cron_schedules', [$this, 'cron_add_timing'], 10, 1);

        if (!wp_next_scheduled('CHAT_GPT_SEO_user_meta_customer_user_group')) {
            wp_schedule_event(time(), '5min', 'CHAT_GPT_SEO_user_meta_customer_user_group');
        }
        add_action('CHAT_GPT_SEO_user_meta_customer_user_group', [$this, 'check_user_meta_for_missing_user_group']);
    }


    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=chat-gpt-seo-logs">'.__('Settings','chat-gpt-seo').'</a>';
        array_unshift($links, $settings_link);
        return $links;
    }


    private function setup_hooks():void
    {
        //add_action('wp_login', [$this, 'handle_user_login'], 100, 2);
        add_action("wp_ajax_get_company", [$this, 'ajax_get_company_by_registration_number']);
        add_action("wp_ajax_nopriv_get_company", [$this, 'ajax_no_priv_get_company_by_registration_number']);
        add_action("wp_ajax_update_company_customer_number", [$this, 'ajax_update_customer_number']);
        add_action("wp_ajax_nopriv_update_company_customer_number", [$this, 'ajax_update_customer_number']);
        add_filter('plugin_action_links_chat-gpt-seo', [$this,'add_settings_link']);
        add_action('admin_enqueue_scripts', [$this,'enqueue_plugin_styles']);
        add_action('admin_enqueue_scripts', [$this,'enqueue_plugin_scripts']);
    }

    public function enqueue_plugin_styles():void
    {
        wp_enqueue_style('plugin-styles', CHAT_GPT_SEO_PLUGIN_URL. '/assets/css/main.css');
    }

    public function enqueue_plugin_scripts():void
    {
        wp_enqueue_script('plugin-scripts', CHAT_GPT_SEO_PLUGIN_URL . '/assets/js/main.js', array(),  CHAT_GPT_SEO_VERSION, true);
    }

    public function add_chat_gpt_seo_log_page():void
    {
        add_submenu_page(
            'tools.php',
            __('Seo status', 'chat-gpt-seo'),
            __('Seo status', 'chat-gpt-seo'),
            'edit_posts',
            'chat-gpt-seo-logs',
            [$this, 'log_page_callback']
        );
    }

    public function check_user_meta_for_missing_user_group():void
    {
        //TODO
    }



    public function log_page_callback():void
    {

        ?>
        <div class="wrap">
            <?php include(CHAT_GPT_SEO_PLUGIN_DIR."/templates/seo.php"); ?>
        </div>
        <?php
    }




    private function add_options_page():void
    {
        if (function_exists('acf_add_options_page')) {

            $pageSettings = array(
                'page_title' => 'Chat GPT SEO Settings',
                'menu_title' => 'Chat GPT SEO Settings',
                'menu_slug' => 'chat-gpt-seo-settings',
                'capability' => 'edit_posts',
                'redirect' => false
            );

            acf_add_options_page($pageSettings);

            $pageSettings = array(
                'page_title' => 'Chat GPT SEO Check pages',
                'menu_title' => 'Chat GPT SEO Check pages',
                'menu_slug' => 'chat-gpt-seo-check pages',
                'capability' => 'edit_posts',
                'redirect' => false
            );
            acf_add_options_page($pageSettings);

            $field_group = json_decode(file_get_contents(CHAT_GPT_SEO_PLUGIN_DIR . '/acf_json/group_62f0bc7465155.json'), TRUE);
            acf_add_local_field_group($field_group);
            $field_group = json_decode(file_get_contents(CHAT_GPT_SEO_PLUGIN_DIR . '/acf_json/group_65536c2771900.json'), TRUE);
            acf_add_local_field_group($field_group);
        }
    }

    private function get_api_settings():array
    {
        $test_mode = get_field('chat_gpt_seo_test_mode', 'option');
        $token = get_field('chat_gpt_seo_test_token', 'option');


    }




    function log($text):void
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
