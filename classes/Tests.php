<?php

namespace SeoAudit;

use SeoAudit\ChatBot;
use SeoAudit\ChatGptApi;

class Tests
{
    public static $asistantInstructions = "You are the Dad, who likes dad jokes";
    public static $assistantName = "Dad Jo";
    public static $assistantId = "dad-jo";
    public static function TestApiRequest()
    {
        $ChatBot = new ChatBot();
        $response = $ChatBot->sendMessage('DO i need threads and create assistant to make requests?', [], true); ?>
        <p><?= $response ?></p>
        <?php
        if ($response) {
            echo 'Test passed';
        } else {
            echo 'Test failed';
        }
    }

    public static function TestCreateAssistant()
    {
        $ChatBotApi = new ChatGptApi();

        $assistantName = self::$assistantName;
        $instructions = self::$asistantInstructions;
        $assistantId = self::$assistantId;

        $response = $ChatBotApi->create_assistant($assistantId, $instructions, $assistantName);
        $responseJson = \SeoAudit\Helpers::get_json($assistantId);

        ?>
        <p><?= json_encode($responseJson, JSON_PRETTY_PRINT) ?></p>
        <?php
        if ($response) {
            echo 'Test passed';
        } else {
            echo 'Test failed';
        }
    }

    public static function TestGetAssistant()
    {
        $ChatBotApi = new ChatGptApi();

        //TODO why we are getting this by instructions?
        $assistant = $ChatBotApi->get_assistant(self::$asistantInstructions);

        if ($assistant) {
            echo 'Test passed';
            ?>
            <p><?= json_encode($assistant, JSON_PRETTY_PRINT) ?></p>
            <?php
        } else {
            echo 'Test failed';
        }
    }

    public static function TestCreateThread() {
            $ChatBotApi = new ChatGptApi();
            $response = $ChatBotApi->create_thread();
            if ($response) {
                echo 'Test passed';
            } else {
                echo 'Test failed';
            }
    }


    public static function TestGetThread() {
        $ChatBotApi = new ChatGptApi();
        $response = $ChatBotApi->get_thread();
        if ($response) {
            echo 'Test passed';
        } else {
            echo 'Test failed';
        }
    }

}
