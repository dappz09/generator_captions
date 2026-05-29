<?php
session_start();

// 1. Memuat konfigurasi API Key
$config = require_once 'config.php';

// 2. Memuat semua kelas
require_once 'classes/AiProviderInterface.php';
require_once 'classes/GeminiProvider.php';
require_once 'classes/GroqProvider.php';
require_once 'classes/OllamaProvider.php';
require_once 'classes/CaptionGenerator.php';

$generatedCaption = '';
$error = '';
$rawCaption = ''; // Untuk dicopy

// Fungsi sederhana untuk mem-parsing Markdown ke HTML
function parseMarkdown($text) {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $text);
    $text = nl2br($text);
    return $text;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['product_name'] ?? '';
    $features = $_POST['features'] ?? '';
    $tone = $_POST['tone'] ?? 'Profesional';
    $aiModel = $_POST['ai_model'] ?? 'gemini';

    if (empty($productName) || empty($features)) {
        $error = "Nama produk dan keunggulan harus diisi.";
    } else {
        try {
            $provider = null;
            if ($aiModel === 'gemini') {
                $key = $config['gemini_api_key'] ?? '';
                if ($key === 'ISI_DENGAN_API_KEY_GEMINI_ANDA' || empty($key)) {
                    throw new Exception("API Key Gemini belum dikonfigurasi di file config.php.");
                }
                $provider = new GeminiProvider($key);
            } elseif ($aiModel === 'groq') {
                $key = $config['groq_api_key'] ?? '';
                if ($key === 'ISI_DENGAN_API_KEY_GROQ_ANDA' || empty($key)) {
                    throw new Exception("API Key Groq belum dikonfigurasi di file config.php.");
                }
                $provider = new GroqProvider($key);
            } else {
                throw new Exception("Model AI tidak valid.");
            }

            $generator = new CaptionGenerator($provider);
            $rawCaption = $generator->generate($productName, $features, $tone);
            $generatedCaption = parseMarkdown($rawCaption);
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // Simpan hasil ke session
    $_SESSION['result_caption'] = $generatedCaption;
    $_SESSION['result_raw'] = $rawCaption;
    $_SESSION['result_error'] = $error;
    $_SESSION['form_data'] = $_POST;
    
    // Redirect untuk mencegah Form Resubmission (PRG Pattern)
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Ambil hasil dari session jika ada (setelah redirect)
if (isset($_SESSION['result_caption'])) {
    $generatedCaption = $_SESSION['result_caption'];
    $rawCaption = $_SESSION['result_raw'];
    $error = $_SESSION['result_error'];
    $_POST = $_SESSION['form_data']; // Repopulate form
}

// Nilai default untuk form
$defaultProductName = "Sistemika";
$defaultFeatures = "Layanan social media marketing untuk agensi digital";

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PostMagic - AI Caption Generator</title>
    <!-- Favicon Custom (Emoji 🪶) -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🪶</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f6f8fd 0%, #f1f6fd 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
        }
        /* Spinner CSS */
        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 3px solid #fff;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-4xl grid md:grid-cols-2 gap-6 items-start">
        
        <!-- Bagian Kiri: Form Input -->
        <div class="glass-card rounded-2xl p-8 transition-all hover:shadow-xl">
            <div class="mb-6 flex items-center gap-4">
                <div class="w-14 h-14 flex items-center justify-center text-4xl bg-orange-50 rounded-xl shadow-sm border border-orange-100">
                    🪶
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Post<span class="text-orange-500">Magic</span></h1>
                    <p class="text-gray-500 mt-1 text-sm">Buat caption social media menarik dalam hitungan detik.</p>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form id="captionForm" method="POST" action="" class="space-y-5">
                <div>
                    <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk / Layanan</label>
                    <input type="text" id="product_name" name="product_name" 
                           value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : $defaultProductName; ?>" 
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                           required>
                </div>

                <div>
                    <label for="features" class="block text-sm font-medium text-gray-700 mb-1">Keunggulan / Fitur Utama</label>
                    <textarea id="features" name="features" rows="3" 
                              class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all resize-none"
                              required><?php echo isset($_POST['features']) ? htmlspecialchars($_POST['features']) : $defaultFeatures; ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="tone" class="block text-sm font-medium text-gray-700 mb-1">Gaya Bahasa</label>
                        <select id="tone" name="tone" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            <option value="Profesional" <?php echo (isset($_POST['tone']) && $_POST['tone'] == 'Profesional') ? 'selected' : ''; ?>>Profesional</option>
                            <option value="Santai & Friendly" <?php echo (isset($_POST['tone']) && $_POST['tone'] == 'Santai & Friendly') ? 'selected' : ''; ?>>Santai & Friendly</option>
                            <option value="Lucu & Menghibur" <?php echo (isset($_POST['tone']) && $_POST['tone'] == 'Lucu & Menghibur') ? 'selected' : ''; ?>>Lucu & Menghibur</option>
                            <option value="Persuasif (Hard Sell)" <?php echo (isset($_POST['tone']) && $_POST['tone'] == 'Persuasif (Hard Sell)') ? 'selected' : ''; ?>>Persuasif (Hard Sell)</option>
                        </select>
                    </div>

                    <div>
                        <label for="ai_model" class="block text-sm font-medium text-gray-700 mb-1">Model AI</label>
                        <select id="ai_model" name="ai_model" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            <option value="gemini" <?php echo (isset($_POST['ai_model']) && $_POST['ai_model'] == 'gemini') ? 'selected' : ''; ?>>Google Gemini</option>
                            <option value="groq" <?php echo (isset($_POST['ai_model']) && $_POST['ai_model'] == 'groq') ? 'selected' : ''; ?>>Groq Llama 3</option>
                        </select>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors duration-300 shadow-md shadow-blue-500/30 flex justify-center items-center gap-2 mt-4">
                    <span id="btnIcon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </span>
                    <span id="btnText">Generate Caption</span>
                </button>
            </form>
        </div>

        <!-- Bagian Kanan: Result Section -->
        <div class="glass-card rounded-2xl p-8 h-full min-h-[400px] flex flex-col transition-all hover:shadow-xl">
            <div class="mb-4 pb-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Hasil Caption</h2>
                <?php if (!empty($generatedCaption)): ?>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">Berhasil</span>
                <?php endif; ?>
            </div>
            
            <div class="flex-grow flex flex-col">
                <?php if (!empty($generatedCaption)): ?>
                    <div class="bg-gray-50 rounded-xl p-5 flex-grow border border-gray-100 text-gray-800 text-sm leading-relaxed overflow-y-auto shadow-inner">
                        <?php echo $generatedCaption; ?>
                    </div>
                    
                    <button onclick="copyCaption()" class="mt-4 text-blue-600 text-sm font-medium hover:text-blue-800 transition-colors self-start flex items-center gap-1 bg-blue-50 px-3 py-1.5 rounded-md hover:bg-blue-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <span id="copyText">Salin ke Clipboard</span>
                    </button>
                    
                    <!-- Hidden textarea for raw copy without HTML tags -->
                    <textarea id="rawCaptionText" class="hidden"><?php echo htmlspecialchars($rawCaption); ?></textarea>
                <?php else: ?>
                    <div class="flex-grow flex flex-col items-center justify-center text-center text-gray-400">
                        <svg class="w-16 h-16 mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <p>Caption Anda akan muncul di sini.<br>Silakan isi form dan klik Generate.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript Interaktif -->
    <script>
        // 1. Fitur Loading Spinner pada saat Submit
        document.getElementById('captionForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const icon = document.getElementById('btnIcon');
            const text = document.getElementById('btnText');
            
            // Cegah double-click
            btn.disabled = true;
            btn.classList.replace('bg-blue-600', 'bg-blue-400');
            btn.classList.replace('hover:bg-blue-700', 'cursor-not-allowed');
            
            // Tampilkan Spinner
            icon.innerHTML = '<div class="spinner"></div>';
            text.innerText = 'AI Sedang Memproses...';
        });

        // 2. Fitur Copy to Clipboard yang lebih bersih
        function copyCaption() {
            const textArea = document.getElementById('rawCaptionText');
            
            // Menggunakan API Clipboard modern
            navigator.clipboard.writeText(textArea.value).then(() => {
                const copyText = document.getElementById('copyText');
                const originalText = copyText.innerText;
                
                // Ubah teks menjadi 'Tersalin!' sementara
                copyText.innerText = 'Tersalin!';
                copyText.classList.add('text-green-600');
                
                setTimeout(() => {
                    copyText.innerText = originalText;
                    copyText.classList.remove('text-green-600');
                }, 2000);
            }).catch(err => {
                alert('Gagal menyalin teks: ' + err);
            });
        }
    </script>
</body>
</html>
