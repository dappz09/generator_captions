<?php

require_once 'AiProviderInterface.php';

class GeminiProvider implements AiProviderInterface {
    private $apiKey;

    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }

    public function generateCaption(string $productName, string $features, string $tone): string {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $this->apiKey;

        $prompt = "Kamu adalah World-Class Social Media Copywriter yang ahli merangkai kata. Tugasmu adalah membuat caption yang SANGAT KREATIF, out-of-the-box, memukau, dan membuat audiens merinding kagum.\n\n"
                . "Jangan gunakan struktur template kaku. Bebaskan kreativitasmu! Buatlah narasi yang mengalir indah seperti bercerita (storytelling), menyentuh emosi, memicu rasa penasaran, dan terlihat sangat elegan serta profesional. Kualitas tulisanmu harus jauh melebihi standar AI pada umumnya.\n\n"
                . "Detail Konteks:\n"
                . "Nama Produk/Layanan: " . $productName . "\n"
                . "Keunggulan: " . $features . "\n"
                . "Gaya Bahasa: " . $tone . "\n\n"
                . "Instruksi Mutlak:\n"
                . "1. Dilarang keras menyertakan nama personal atau orang dalam brandingnya.\n"
                . "2. HANYA berikan teks caption akhirnya saja, TANPA kata pengantar seperti 'Ini dia captionnya' atau penjelasan ekstra.\n"
                . "3. JANGAN gunakan format markdown seperti bintang (**) atau list bullet (*). Gunakan enter antar paragraf yang indah, dan taburkan emoji secara brilian agar terlihat estetik.\n"
                . "4. WAJIB sertakan tepat 5 hashtag (#) rekomendasi terbaik yang sangat relevan dan berpotensi viral di bagian paling bawah caption.\n";

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 800
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Error koneksi ke API Gemini: " . $error);
        }

        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            throw new Exception("API Error: " . $result['error']['message']);
        }
        
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }
        
        throw new Exception("Format respons API tidak dikenali.");
    }
}
