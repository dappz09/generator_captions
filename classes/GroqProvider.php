<?php

require_once 'AiProviderInterface.php';

class GroqProvider implements AiProviderInterface {
    private $apiKey;

    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }

    public function generateCaption(string $productName, string $features, string $tone): string {
        $url = "https://api.groq.com/openai/v1/chat/completions";

        $systemPrompt = "Kamu adalah World-Class Social Media Copywriter. Buatkan caption yang SANGAT KREATIF, memukau, dan out-of-the-box.\n\nJangan terjebak template kaku. Bebaskan kreativitasmu merangkai cerita (storytelling) yang menyentuh emosi, elegan, dan profesional, jauh lebih hebat dari tulisan AI biasa.\n\nInstruksi Mutlak:\n1. Dilarang keras menyertakan nama personal/orang.\n2. HANYA berikan teks caption akhirnya saja, TANPA kata pengantar.\n3. JANGAN gunakan format markdown rumit seperti (**) atau bullet (*). Gunakan enter paragraf yang indah dan emoji estetik.\n4. WAJIB sertakan tepat 5 hashtag (#) rekomendasi terbaik yang sangat relevan dan berpotensi viral di akhir caption.";
        $userPrompt = "Nama Produk/Layanan: " . $productName . "\n"
                    . "Keunggulan: " . $features . "\n"
                    . "Gaya Bahasa: " . $tone . "\n";

        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [
                [
                    "role" => "system",
                    "content" => $systemPrompt
                ],
                [
                    "role" => "user",
                    "content" => $userPrompt
                ]
            ],
            "temperature" => 0.7,
            "max_tokens" => 800
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Error koneksi ke API Groq: " . $error);
        }

        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            $msg = is_array($result['error']) && isset($result['error']['message']) ? $result['error']['message'] : json_encode($result['error']);
            throw new Exception("Groq API Error: " . $msg);
        }
        
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }
        
        throw new Exception("Format respons API tidak dikenali.");
    }
}
