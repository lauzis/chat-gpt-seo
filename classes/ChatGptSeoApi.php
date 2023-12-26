<?php

namespace ChatGptSeo;

class ChatGptSeoApi
{

    private function get_api_settings():array
    {
        $test_mode = get_field('chat_gpt_seo_test_mode', 'option');
        $token = get_field('chat_gpt_seo_test_token', 'option');;
        $url = get_field('chat_gpt_seo_test_url', 'option');;
        if (!$test_mode) {
            $token = get_field('chat_gpt_seo_live_token', 'option');
            $url = get_field('chat_gpt_seo_live_url', 'option');;
        }
        return ['token' => $token, 'url' => $url];
    }


    public static function force_audit_item(\WP_REST_Request $request)
    {
        $id = $request->get_param('id');
        $url = get_the_permalink($id);
        \ChatGptSeo\ChatGptSeoHelpers::remove_report($url);
        return self::audit_item($request);
    }

    public static function audit_item(\WP_REST_Request $request)
    {

        $id = $request->get_param('id');
        $url = get_the_permalink($id);

        $result = \ChatGptSeo\ChatGptSeoHelpers::get_HTML($url);
        $report = [
            'status' => $result['status'],
            'url' => $url,
            'id' => $id,
            'timestamp' => time()
        ];


        $fromFile = false;
        $html = \ChatGptSeo\ChatGptSeoHelpers::get_html_from_file($url);
        if (!empty($html)) {
            $fromFile = true;
            $report = \ChatGptSeo\ChatGptSeoHelpers::get_report($url);
        } else {
            sleep(1);
            $fromFile = false;
            $html = $result['content'];
            $status = $result['status'];
            $reports['status'] = $status;
            \ChatGptSeo\ChatGptSeoHelpers::save_html_to_file($url, $html);
            $report = \ChatGptSeo\ChatGptSeoHelpers::audit_html($html, $report);
            $report['keywords'] = \ChatGptSeo\ChatGptSeoHelpers::$keywords;
            \ChatGptSeo\ChatGptSeoHelpers::save_report($url, $report);
        }

        $html = \ChatGptSeo\ChatGptSeoHelpers::get_raport_item_output_html($id, $fromFile);
        return [
            'html' => $html,
            'report' => $report,
            'id' => $id
        ];
    }


    public static function update_meta_description(\WP_REST_Request $request)
    {
        $id = $request->get_param('id');
        $data = $request->get_json_params();
        $meta_description = $data['meta_description'];

        update_post_meta($id, '_yoast_wpseo_metadesc', $meta_description);


        return [
            'id' => $id,
            'json' => $json,
            'meta_description' => $meta_description
        ];
    }

    public static function generate_meta_description(\WP_REST_Request $request)
    {
        $id = $request->get_param('id');
        $content = apply_filters('the_content', get_the_content(null, false, $id));
        $data = $request->get_json_params();

        $keywords = [];
        foreach($data['keywords[]'] as $keyword){
            $keywords[] = $keyword;
        }

        $content = \ChatGptSeo\ChatGptSeoHelpers::clean_html($content);

        $ChatBot = new \ChatGptSeo\ChatBot();
        // Send the message to our AI.
        $resMessage = $ChatBot->sendMessage($content, $keywords);
        //$jsonResponse = json_encode(array("responseMessage" => $resMessage));


        return [
            'id' => $id,
            'content'=>$content,
            'data'=>$data,
            'keywords' => $keywords,
            'response'=>$resMessage
        ];
    }
}
