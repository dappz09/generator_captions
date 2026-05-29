<?php

require_once 'AiProviderInterface.php';

class OllamaProvider implements AiProviderInterface {
    private $modelName;
    private $apiUrl;

    public function __construct(string $modelName = 'llama3', string $apiUrl = 'http://localhost:11434/api/chat') {
        $this->modelName = $modelName;
        $this->apiUrl = $apiUrl;
    }

    public function generateCaption(string $productName, string $features, string $tone): string {
        $systemPrompt = "Kamu adalah World-Class Social Media Copywriter. Buatkan caption yang SANGAT KREATIF, memukau, dan out-of-the-box.\n\nJangan terjebak template kaku. Bebaskan kreativitasmu merangkai cerita (storytelling) yang menyentuh emosi, elegan, dan profesional, jauh lebih hebat dari tulisan AI biasa.\n\nInstruksi Mutlak:\n1. Dilarang keras menyertakan nama personal/orang.\n2. HANYA berikan teks caption akhirnya saja, TANPA kata pengantar.\n3. JANGAN gunakan format markdown rumit seperti (**) atau bullet (*). Gunakan enter paragraf yang indah dan emoji estetik.";
        $userPrompt = "Nama Produk/Layanan: " . $productName . "\n"
                    . "Keunggulan: " . $features . "\n"
                    . "Gaya Bahasa: " . $tone . "\n";

        $data = [
            "model" => $this->modelName,
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
            "stream" => false // Pastikan tidak stream agar kita dapat respon penuh sekaligus
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        // Menambah timeout lebih panjang karena menjalankan AI secara lokal bisa memakan waktu tergantung spesifikasi PC
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return "Error (cURL Ollama): " . $error . " - Pastikan aplikasi Ollama sedang berjalan di PC Anda.";
        }

        try {
            $result = json_decode($response, true);
            
            if (isset($result['error'])) {
                return "Ollama API Error: " . $result['error'];
            }
            
            if (isset($result['message']['content'])) {
                return $result['message']['content'];
            }
            
            return "Error: Format respons Ollama tidak sesuai ekspektasi. " . json_encode($result);
        } catch (Exception $e) {
            return "Error (JSON Parse): " . $e->getMessage();
        }
    }
}
