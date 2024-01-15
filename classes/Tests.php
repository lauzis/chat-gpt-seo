<?php

namespace SeoAudit;

use function SeoAudit\get_field;
use function SeoAudit\get_the_permalink;

class Tests
{
    public static function tests(){
        if (current_user_can('editor') || current_user_can('administrator')) {
            $id = $_GET['id'] ?? null;

            if (isset($_GET['chat-gpt-seo-test'])) {
                switch ($_GET['chat-gpt-seo-test']) {
                    case 'keywords': ?>
                        <pre>
                    <?php print_r(\SeoAudit\Helpers::get_keywords()); ?>
                </pre>

                        <pre>
                    <?php print_r(\SeoAudit\Helpers::get_keywords($id)); ?>
                </pre>
                        <?php
                        break;
                    case 'audit':
                        if ($id) {
                            ?>
                            <pre>
                            <?php
                            $url = get_the_permalink($id);
                            \SeoAudit\Helpers::remove_report($url);
                            print_r(\SeoAudit\SeoAuditApi::audit_item($id));
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
}
