<?php

namespace SeoAudit;

class RestRoutes
{
    public static function force_audit_item_request(\WP_REST_Request $request):array
    {
        $id = $request->get_param('id');
        $url = get_the_permalink($id);
        //WP Fastest Cache clean before reaudit
        if(function_exists('wpfc_clear_post_cache_by_id')){
            wpfc_clear_post_cache_by_id($id);
        }

        \SeoAudit\Helpers::remove_report($url);
        return self::audit_item_request($request);
    }

    public static function audit_item_request(\WP_REST_Request $request):array{
        $id = $request->get_param('id');
        return \SeoAudit\Audit::audit_item($id);
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
        $url = get_the_permalink($id);
        $force_keyword = (bool) $request->get_param('force-keyword');
        $content = apply_filters('the_content', get_the_content(null, false, $id));
        $report = \SeoAudit\Helpers::get_report($url);
        $wpml_lnag_info = apply_filters( 'wpml_post_language_details', NULL, $id );
        $lang = $report['lang'] ?? "";
        if ($wpml_lnag_info && $wpml_lnag_info['locale']){
            $lang = $wpml_lnag_info['locale'];
        }
        $data = $request->get_json_params();

        $keywords = [];
        if (is_array($data['keywords[]'])){
            foreach($data['keywords[]'] as $keyword){
                $keywords[] = $keyword;
            }
        } else {
            $keywords[] = $data['keywords[]'];
        }


        $content = \SeoAudit\Helpers::clean_html($content);

        $ChatBot = new \SeoAudit\ChatGptApi();
        // Send the message to our AI.
        $resMessage = $ChatBot->generate_meta_description($content, $keywords,  $force_keyword, $lang);
        if ($resMessage){
            return [
                'id' => $id,
                'content'=>$content,
                'data'=>$data,
                'keywords' => $keywords,
                'response'=>$resMessage['message'],
                'status'=>'ok',
                'debug'=>$resMessage['debug']
            ];
        }
        //$jsonResponse = json_encode(array("responseMessage" => $resMessage));
        return [
            'id' => $id,
            'content'=>$content,
            'data'=>$data,
            'keywords' => $keywords,
            'response'=>"Pleace check if you have valid ChatGpt token",
            'status'=>'failed',
            'debug'=>false
        ];
    }

    public static function clear_audit_data() {
        $files = scandir(CHAT_GPT_SEO_REPORT_DIR);
        foreach($files as $file){
            if ($file!=='.' && $file!=='..'){
                unlink(CHAT_GPT_SEO_REPORT_DIR."/".$file);
            }
        }
    }
}
