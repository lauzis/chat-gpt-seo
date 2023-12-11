<?php

namespace ChatGptSeo;

const KEYWORD_FOUND_PHRASE = 2;
const KEYWORD_FOUND_EXACT_MATCH = 1;

class ChatGptSeoHelpers
{
    static array $keywords = [];
    static bool $initialized = false;


    static function init(): void
    {
        if (!self::$initialized) {
            self::$initialized = true;
            self::$keywords = self::get_keywords();
        }
    }

    static function get_keywords($id = 'option'): array
    {
        $full_list_of_keywords = [];
        if (have_rows('keyword_list', $id)) {
            while (have_rows('keyword_list', $id)) {
                the_row();
                $keyword = get_sub_field('keyword');
                $keyword_variations_acf = get_sub_field('keyword_variations');
                $keyword_variations = [];
                if ($keyword_variations_acf) {
                    foreach ($keyword_variations_acf as $keyword_variation) {
                        $keyword_variations[] = [
                            'keyword' => $keyword_variation['keyword_variation'],
                            'count' => 0,
                            'count_exact' => 0,
                            'count_phrase' => 0,
                            'place_where_found' => []
                        ];
                    }
                }
                $full_list_of_keywords[] = [
                    'keyword' => $keyword,
                    'variations' => $keyword_variations,
                    'count' => 0,
                    'count_exact' => 0,
                    'count_phrase' => 0,
                    'place_where_found' => []
                ];
            }
        }
        return $full_list_of_keywords;

    }

    static public function get_audit_status($status): void
    {
        if (is_bool($status)) {
            ?>
            <span class="kw-found-<?= $status ? 'yes' : 'no'; ?>">
                <?= $status ? 'yes' : 'no'; ?>
            </span>
            <?php
        } else {
            $state = "missing";
            if ($status >= 0 && $status < 1) {
                $state = "ok";
            }

            if ($status > 1 && $status < 50) {
                $state = "some-are-missing";
            }
            if ($status >= 50 && $status < 75) {
                $state = "missing";
            }

            if ($status >= 75 && $status < 99) {
                $state = "mostly-missing";
            }

            if ($status >= 99 && $status <= 100) {
                $state = "missing";
            }
            ?>
            <span class="kw-found-<?= $state ?>">
                <?= $state; ?>
            </span>
            <?php
        }


    }

    static public function audit_text($text)
    {

        $textMissing = false;
        $textHasNpKeywords = false;
        $penalty = 0;
        if (strlen($text) == 0) {
            $penalty = 2;
            $textMissing = true;
        } else {

            $result = \ChatGptSeo\ChatGptSeoHelpers::has_keywords($text, 'in_content');
            if ($result) {
                return $result;
            } else {
                $textHasNpKeywords = true;;
                $penalty = 1;
            }

        }

        if ($textMissing || $textHasNpKeywords) {
            return $penalty;
        }
    }

    static public function has_keywords($text, $placeWhereFound): bool|array
    {

        if (empty($text) && strlen(trim($text)) === 0) {
            return false;
        }
        self::init();
        $keywordList = self::$keywords;
        $keywordFound = false;
        foreach ($keywordList as $k_key => $keyword) {
            $kwFound = self::find_keyword($text, $keyword['keyword']);
            if ($kwFound) {
                $keywordList[$k_key]['count']++;
                $keywordFound = true;

                if ($kwFound === KEYWORD_FOUND_EXACT_MATCH) {
                    $keywordList[$k_key]['count_exact']++;
                }

                if ($kwFound === KEYWORD_FOUND_PHRASE) {
                    $keywordList[$k_key]['count_phrase']++;
                }

                if (!in_array($placeWhereFound, $keywordList[$k_key]['place_where_found'])) {
                    $keywordList[$k_key]['place_where_found'][] = $placeWhereFound;
                }
            }

            if (!is_array($keyword['variations']) && count($keyword['variations'])) {
                foreach ($keyword['variations'] as $kv_key => $variation) {
                    $variation_keyword = $variation['keyword'];
                    $kwFound = self::find_keyword($text, $variation_keyword);
                    if ($kwFound) {
                        $keywordFound = true;
                        $keywordList[$k_key]['variations'][$kv_key]['count']++;

                        if ($kwFound === KEYWORD_FOUND_EXACT_MATCH) {
                            $keywordList[$k_key]['variations'][$kv_key]['count_exact']++;
                        }

                        if ($kwFound === KEYWORD_FOUND_PHRASE) {
                            $keywordList[$k_key]['variations'][$kv_key]['count_phrase']++;
                        }

                        if (!in_array($placeWhereFound, $keywordList[$k_key]['variations'][$kv_key]['place_where_found'])) {
                            $keywordList[$k_key]['variations'][$kv_key]['place_where_found'][] = $placeWhereFound;
                        }
                    }
                }
            }
        }

        if ($keywordFound) {
            self::$keywords = $keywordList;
            return true;
        } else {
            return false;
        }

    }

    static public function find_keyword($text, $keyword): bool|int
    {
        $text_to_lower = mb_strtolower($text);
        $slit_words = explode(" ", $keyword);
        if (mb_substr_count($text_to_lower, $keyword) > 0) {
            return KEYWORD_FOUND_EXACT_MATCH;
        }

        $found_all = true;
        if (count($slit_words) > 0) {

            foreach ($slit_words as $kw) {
                if (!empty($kw) && mb_substr_count($text_to_lower, $kw) === 0) {
                    $found_all = false;
                }
            }
        }


        return $found_all ? KEYWORD_FOUND_PHRASE : false;
    }


    static public function get_HTML($url)
    {
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(

            CURLOPT_CUSTOMREQUEST => "GET",        //set request type post or get
            CURLOPT_POST => false,        //set to GET
            CURLOPT_USERAGENT => $user_agent, //set user agent
            CURLOPT_COOKIEFILE => "cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR => "cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING => "",       // handle all encodings
            CURLOPT_AUTOREFERER => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT => 120,      // timeout on response
            CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        $status = $header['http_code'];
        return ['content' => $content, 'status' => $status];
    }


    static public function save_html_to_file($url, $html)
    {

        file_put_contents(CHAT_GPT_SEO_UPLOAD_DIR . "/" . md5($url) . ".html", $html);
    }

    static public function get_html_from_file($url)
    {
        $file = CHAT_GPT_SEO_UPLOAD_DIR . "/" . md5($url) . ".html";
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return false;
    }

    static public function get_raport_item_output_html($id, $formFile)
    {
        $ajax = true;
        ob_start();
        include(CHAT_GPT_SEO_PLUGIN_DIR . "/templates/seo/table-content-item-first-row.php");
        $first_row_html = ob_get_contents();
        ob_end_clean();


        ob_start();
        include(CHAT_GPT_SEO_PLUGIN_DIR . "/templates/seo/table-content-item-first-row.php");
        $second_row_html = ob_get_contents();
        ob_end_clean();

        return [
            'first_row_html' => $first_row_html,
            'Second_row_html' => $second_row_html,
        ];
    }

    static public function save_report($url, $report)
    {
        file_put_contents(CHAT_GPT_SEO_UPLOAD_DIR . "/" . md5($url) . ".json", json_encode($report, JSON_PRETTY_PRINT));
    }

    static public function get_report($url)
    {
        $file = CHAT_GPT_SEO_UPLOAD_DIR . "/" . md5($url) . ".json";
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return false;
    }

    static public function remove_report($url)
    {
        $file_html = CHAT_GPT_SEO_UPLOAD_DIR . "/" . md5($url) . ".html";
        $file_json = CHAT_GPT_SEO_UPLOAD_DIR . "/" . md5($url) . ".json";
        if (file_exists($file_html)) {
            unlink($file_html);
        }
        if (file_exists($file_json)) {
            unlink($file_json);
        }
    }

    static public function get_report_url($url): bool|string
    {
        $file_url = CHAT_GPT_SEO_UPLOAD_URL . "/" . md5($url) . ".json";
        $file = CHAT_GPT_SEO_UPLOAD_DIR . "/" . md5($url) . ".json";
        if (file_exists($file)) {
            return $file_url;
        }
        return false;
    }

    static public function get_report_html_url($url): bool|string
    {
        $file_url = CHAT_GPT_SEO_UPLOAD_URL . "/" . md5($url) . ".html";
        $file = CHAT_GPT_SEO_UPLOAD_DIR . "/" . md5($url) . ".html";
        if (file_exists($file)) {
            return $file_url;
        }
        return false;
    }

    static public function get_report_json($url)
    {
        $file = CHAT_GPT_SEO_UPLOAD_DIR . "/" . md5($url) . ".json";
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return false;
    }

    static public function get_domains(): array
    {
        return [$_SERVER['HTTP_HOST']];
    }

    static public function is_outgoing_url($url): bool
    {

        if (empty($url)) {
            return false;
        }
        $domains = self::get_domains();

        $parsed_url = parse_url($url);

        $host = $parsed_url['host'];


        if (in_array($host, $domains)) {
            return false;
        }

        if (str_starts_with($url, "/") || str_starts_with($url, "#") || str_starts_with($url, "?")) {
            return false;
        }


//        if (substr($url,0,4)==="http"){
//            return true;
//        }
//
//        if (substr($url,0,2)==="//"){
//            return true;
//        }

        return true;
    }

    static public function audit_tags($tag, $doc, $report): array
    {

        $items = $doc->getElementsByTagName($tag);
        if (!$items) {
            return $report;
        }
        $report[$tag . '_count'] = count($items);
        $report[$tag . '_found_keyword'] = 0;

        foreach ($items as $item) {
            switch ($tag) {
                case 'a':
                    $url = $item->getAttribute('href');
                    if (!empty($url)) {
                        if (!isset($report[$tag . '_hrefs'])) {
                            $report[$tag . '_hrefs'] = [];
                        }
                        $report[$tag . '_hrefs'][] = $url;

                        if (!isset($report[$tag . '_hrefs_outgoing'])) {
                            $report[$tag . '_hrefs_outgoing'] = [];
                        }

                        if (self::is_outgoing_url($url)) {
                            $report[$tag . '_hrefs_outgoing'][] = $url;
                        }

                        $a_content = $item->textContent;

                        if (self::has_keywords($a_content, 'in_link')) {
                            $report[$tag . '_found_keyword']++;
                        }
                    }
                    break;

                case 'img':
                    if (isset($report[$tag . '_srcs'])) {
                        $report[$tag . '_srcs'] = [];
                    }

                    if (isset($report[$tag . '_alt_missing'])) {
                        $report[$tag . '_alt_missing'] = [];
                    }
                    $src = $item->getAttribute('src');
                    $report[$tag . '_srcs'][] = $src;
                    $alt = $item->getAttribute('alt');

                    if (empty($alt)) {
                        $report[$tag . '_alt_missing'][] = $src;
                    }
                    if (self::has_keywords($alt, 'in_image_alt_attribute')) {
                        $report[$tag . '_found_keyword']++;
                    }
                    break;

                default:
                    return $report;
                    $tag_textual_content = $item->textContent;
                    if (self::has_keywords($tag_textual_content, "in_" . $tag . "_tag")) {
                        $report[$tag . '_found_keyword']++;
                    }
                    break;
            }
        }

        return $report;
    }

    static public function audit_html($html, $report): array
    {

        if (empty($html)) {
            $report['html_empty'] = true;
            return $report;
        }
        self::init();

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        $errors = libxml_get_errors();
        $tags = ['a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'title', 'img'];

        foreach ($tags as $tag) {
            $report = self::audit_tags($tag, $doc, $report);
        }


        $h1 = $doc->getElementsByTagName('h1');
        $h1Text = "";
        if (count($h1) > 0) {
            $h1Text = $h1[0]->textContent;
        }
        $report['h1_text'] = $h1Text;

        $report['html_errors'] = $errors;
        $report = self::get_meta($doc, $report);


        //remove header
        $headers = $doc->getElementsByTagName('header');
        if ($headers && count($headers) > 0) {
            $header = $headers[0];
            $parent = $header->parentNode;
            $parent->removeChild($header);
            //$doc->removeChild($header);
        }

        //remove footer
        $footers = $doc->getElementsByTagName('footer');
        if ($footers && count($footers) > 0) {
            $footer = $footers[count($footers) - 1];
            $parent = $footer->parentNode;
            $parent->removeChild($footer);
        }

        //remove asides
        $asides = $doc->getElementsByTagName('aside');
        if ($asides && count($asides) > 0) {
            foreach ($asides as $aside) {
                $parent = $aside->parentNode;
                $parent->removeChild($aside);
            }
        }

        $paragraph = $doc->getElementsByTagName('p');
        $firstParagraphText = "";
        if (count($paragraph) > 0) {
            $firstParagraphText = $paragraph[0]->textContent;
        }

        $report['first_paragraph'] = $firstParagraphText;
        $report['first_paragraph_found_keywords'] = self::has_keywords($firstParagraphText, 'in_first_paragraph');


        $striped_html = self::clean_html($html);
        $report['content_has_keywords'] = self::has_keywords($striped_html, 'in_content');

        return $report;
    }


    static public function get_meta($dom, $report)
    {
        $meta_fields = $dom->getElementsByTagName("meta");

        $report['meta_description'] = false;
        $report['meta_image'] = false;
        $report['meta_description_keyword_found'] = false;
        $report['textualContent'] = "";

        foreach ($meta_fields as $meta_field) {

            $meta_field_name = $meta_field->getAttribute("name");
            if ($meta_field_name === "description") {
                $meta_field_content = $meta_field->getAttribute("content");
                if (!empty($meta_field_content)) {
                    $report['meta_description'] = true;
                    $report['meta_description_keyword_found'] = self::has_keywords($meta_field_content, 'in_meta_description');
                    $report['textualContent'] .= " " . $meta_field_content;
                }
            }

            $meta_field_property = $meta_field->getAttribute('property');
            if ($meta_field_property === "og:image") {
                $meta_field_content = $meta_field->getAttribute("content");
                if (!empty($meta_field_content)) {
                    $report['meta_image'] = true;
                }
            }
        }

        $titles = $dom->getElementsByTagName("title");
        if (count($titles)) {
            $report['meta_title_text'] = $titles[0]->textContent;
            $report['meta_title_keyword_found'] = self::has_keywords($report['meta_title_text'], 'in_title_tag');
        }
        return $report;
    }


    public static function clean_html($html)
    {
        $text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        return strip_tags($text);
    }
}
