<?php

namespace App\Libraries;

class GeminiClient
{
    protected $apiKey;
    protected $client;

    public function __construct()
    {
        $this->apiKey = env('gemini.apiKey');
        $this->client = \Config\Services::curlrequest();
    }

    /**
     * Generate content from Gemini API using gemini-1.5-flash model
     */
    public function generate($prompt, $systemInstruction = null)
    {
        if (empty($this->apiKey)) {
            // Return a fallback/mock response if key is not configured
            return "[MOCK AI] API Key Gemini tidak terpasang. Ini adalah respon simulasi dari asisten PO Bus.";
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ];

            if ($systemInstruction) {
                $payload['systemInstruction'] = [
                    'parts' => [
                        ['text' => $systemInstruction]
                    ]
                ];
            }

            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $payload,
                'http_errors' => false // don't throw exception on 4xx/5xx to handle errors gracefully
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);

            if ($statusCode === 200 && isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                return $body['candidates'][0]['content']['parts'][0]['text'];
            }

            // Error log or fallback
            log_message('error', 'Gemini API Error: ' . json_encode($body));
            return "[AI Error] Gagal memproses permintaan Anda. Kode Status: {$statusCode}";

        } catch (\Exception $e) {
            log_message('error', 'Gemini client exception: ' . $e->getMessage());
            return "[AI Exception] Terjadi kendala koneksi ke server AI.";
        }
    }

    /**
     * Generate structured JSON content from Gemini
     */
    public function generateJson($prompt, $systemInstruction = null)
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ];

            if ($systemInstruction) {
                $payload['systemInstruction'] = [
                    'parts' => [
                        ['text' => $systemInstruction]
                    ]
                ];
            }

            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $payload,
                'http_errors' => false
            ]);

            $body = json_decode($response->getBody(), true);
            if ($response->getStatusCode() === 200 && isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                return json_decode($body['candidates'][0]['content']['parts'][0]['text'], true);
            }

            return null;
        } catch (\Exception $e) {
            log_message('error', 'Gemini JSON exception: ' . $e->getMessage());
            return null;
        }
    }
}
