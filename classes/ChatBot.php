<?php

namespace ChatGptSeo;
/**
 * ChatBot Class
 */
class ChatBot
{
    /**
     * @var string The authorization token for the API
     */
    private $authorization;

    /**
     * @var string The endpoint URL for the OpenAI.com API
     */
    private $endpoint;

    /**
     * ChatBot constructor.
     */
    public function __construct()
    {

        $test_mode = get_field('chat_gpt_seo_test_mode', 'option');
        $token = get_field('chat_gpt_seo_test_token', 'option');
        if (!$test_mode){
            $token = get_field('chat_gpt_seo_live_token', 'option');
        }


        // This token is not real, in case you were thinking what I'm thinking...
        $this->authorization = $token;
        $this->endpoint = 'https://api.openai.com/v1/chat/completions';
    }

    /**
     * Send a message to the API and return the response.
     *
     * @param string $content The message to send
     * @return string The response message
     * @throws Exception If there is an error in sending the message via cURL
     */
    public function sendMessage(string $content, array $keywords, bool $forceKeywords = false): string
    {
        // Prepare data for sending

        $consideration = '';
        if (count($keywords) > 0) {
            $consideration = 'Take in consideration these keywords:' . implode(", ", $keywords) . '.';
            if ($forceKeywords) {
                $consideration = 'Summary have to include these keywords:' . implode(", ", $keywords) . '.';
            }

        }

        $data = [
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are SEO content copywriter. Generate summary for given text  for SEO purposes.
                     ' . $consideration . '
                     The summary should description of the article, not as a call to action. 
                     The summary must be under 200 characters!!!
                     '
                ],
                [
                    'role' => 'user',
                    'content' => $content
                ],
            ],
            'model' => 'gpt-3.5-turbo'
        ];

        // Set headers for the API request
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->authorization,
        ];

        // Send the request to the API using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        // Check for errors in the API response
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Error sending the message: ' . $error);
        }

        curl_close($ch);

        // Parse the API response
        $arrResult = json_decode($response, true);
        $resultMessage = $arrResult["choices"][0]["message"]["content"];

        // Return the response message
        return $resultMessage;
    }
}
