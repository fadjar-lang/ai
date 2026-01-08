<?php
$responses = [];
$defaultModels = ['google/gemini-2.0-flash-001', 'meta-llama/llama-3.1-8b-instruct'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['value'])) {
    $models = !empty($_POST['models']) ? array_filter(array_map('trim', $_POST['models'])) : $defaultModels;
    $value = $_POST['value'];
    $url = "https://openrouter.ai/api/v1/chat/completions";

    foreach ($models as $modelId) {
        $payload = json_encode([
            "model" => $modelId,
            "messages" => [
                ["role" => "system", "content" => "Kamu adalah pakar laptop profesional. Bandingkan laptop dalam format Tabel Spesifikasi (CPU, GPU, RAM, Layar, Baterai), Kelebihan/Kekurangan, dan Kesimpulan Akhir."],
                ["role" => "user", "content" => "Bandingkan: " . $value]
            ]
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer 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", // <--- GANTI INI
                "HTTP-Referer: http://localhost",
                "X-Title: Laptop AI Compare",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $json = json_decode($response, true);
        $responses[$modelId] = $json["choices"][0]["message"]["content"] ?? "Error: " . ($json['error']['message'] ?? 'Gagal mengambil data');
        curl_close($ch);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laptop AI Comparator | TechExpert</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --accent-color: #6610f2;
            --bg-body: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: #1e293b;
        }

        /* Header Styling */
        .hero-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 60px 0;
            margin-bottom: -50px;
            color: white;
            border-radius: 0 0 50px 50px;
        }

        /* Card & Container Styling */
        .main-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .ai-response-card {
            border: none;
            border-radius: 16px;
            transition: transform 0.3s ease;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .ai-response-card:hover {
            transform: translateY(-5px);
        }

        /* Table Styling */
        .ai-content table {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            margin: 1.5rem 0;
        }

        .ai-content th {
            background-color: #f1f5f9;
            font-weight: 600;
            padding: 12px;
        }

        .ai-content td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Loading Overlay */
        #loadingOverlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.8);
            z-index: 9999;
            backdrop-filter: blur(5px);
            color: white;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .model-badge {
            font-size: 0.75rem;
            background: #e2e8f0;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
            color: #475569;
        }

        .btn-compare {
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border: none;
            font-weight: 600;
            padding: 12px;
            transition: opacity 0.3s;
        }

        .btn-compare:hover {
            opacity: 0.9;
            color: white;
        }
    </style>
</head>
<body>

<div id="loadingOverlay">
    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
    <h5>Menganalisis Spesifikasi...</h5>
    <p class="text-light">Meminta pendapat dari beberapa model AI</p>
</div>

<div class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-2"><i class="bi bi-cpu-fill"></i> HPAI Compare</h1>
        <p class="lead opacity-75">Bandingkan hp impian Anda dengan analisis kecerdasan buatan.</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="main-card p-4 p-md-5">
                <form method="POST" onsubmit="showLoading()">
                    <div class="row g-4">
                        <div class="col-md-5">
                            <label class="form-label fw-bold"><i class="bi bi-robot me-2"></i>Pilih Mesin AI</label>
                            <div id="modelsContainer">
                                <?php foreach (($models ?? $defaultModels) as $m): ?>
                                <div class="input-group mb-2 model-row">
                                    <input type="text" class="form-control form-control-sm" name="models[]" value="<?= htmlspecialchars($m) ?>">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeModel(this)"><i class="bi bi-x"></i></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-sm text-primary p-0 fw-600" onclick="addModel()">
                                <i class="bi bi-plus-circle"></i> Tambah Model Lain
                            </button>
                        </div>
                        
                        <div class="col-md-7">
                            <label class="form-label fw-bold"><i class="bi bi-search me-2"></i>Hp yang ingin Dibandingkan</label>
                            <textarea class="form-control mb-3" name="value" rows="4" placeholder="Contoh: Samsung Galaxy S25 ultra vs Iphone 17 pro" required><?= $_POST['value'] ?? '' ?></textarea>
                            <button class="btn btn-primary btn-compare w-100 shadow-sm shadow" type="submit">
                                Mulai Perbandingan <i class="bi bi-arrow-right-short"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (!empty($responses)): ?>
    <div class="row mt-5">
        <div class="col-12 mb-4">
            <h3 class="fw-bold"><i class="bi bi-journal-text me-2"></i>Hasil Analisis</h3>
        </div>
        <?php foreach ($responses as $modelId => $msg): ?>
        <div class="col-lg-6 mb-4">
            <div class="card ai-response-card h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <span class="model-badge">
                        <i class="bi bi-stars me-1 text-primary"></i> 
                        <?= strtoupper(explode('/', $modelId)[1] ?? $modelId) ?>
                    </span>
                    <button class="btn btn-sm btn-light rounded-circle" onclick="copyText('content-<?= md5($modelId) ?>')">
                        <i class="bi bi-copy"></i>
                    </button>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="ai-content" id="content-<?= md5($modelId) ?>">
                        <?= nl2br(htmlspecialchars($msg)) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<footer class="text-center py-4 text-secondary">
    <small>© 2024 TechExpert AI • Powered by OpenRouter</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    // Inisialisasi perenderan Markdown agar tabel & format bagus
    document.querySelectorAll('.ai-content').forEach(container => {
        const rawText = container.innerText;
        container.innerHTML = marked.parse(rawText);
    });

    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function addModel() {
        const container = document.getElementById('modelsContainer');
        const div = document.createElement('div');
        div.className = 'input-group mb-2 model-row';
        div.innerHTML = `
            <input type="text" class="form-control form-control-sm" name="models[]" placeholder="Model OpenRouter">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeModel(this)"><i class="bi bi-x"></i></button>
        `;
        container.appendChild(div);
    }

    function removeModel(btn) {
        if (document.querySelectorAll('.model-row').length > 1) {
            btn.closest('.model-row').remove();
        }
    }

    function copyText(id) {
        const text = document.getElementById(id).innerText;
        navigator.clipboard.writeText(text);
        alert('Teks berhasil disalin!');
    }
</script>
</body>
</html>