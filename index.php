<?php 

    session_start();
    require_once 'config.php';

    $errors = [];
    $short_url = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $long_url = trim($_POST['url'] ?? '');

        if (empty($long_url)) {
            $errors[] = "Please enter a URL!";
        } else {
            if (!filter_var($long_url, FILTER_VALIDATE_URL)) {
                $errors[] = "Please, enter a valid URL (for example: https://google.com).";
            } else {
                if (!preg_match('#^https?://#i', $long_url)) {
                    $long_url = 'https://' . $long_url;
                    
                    if (!filter_var($long_url, FILTER_VALIDATE_URL)) {
                        $errors[] = "URL format is invalid!";
                    }
                }
            }
        }

        if (empty($errors)) {
        // 1. Bu URL oldin saqlanganmi? Tekshirish kerak
        $stmt = $pdo->prepare("SELECT short_url FROM urls WHERE long_url = ? LIMIT 1");
        $stmt->execute([$long_url]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Oldin bor — o‘sha short code ni qaytar
            $short_url = $existing['short_url'];
        } else {
            // Yangi short code yarat
            do {
                // 7 belgili URL-safe kod
                $short_url = substr(bin2hex(random_bytes(4)), 0, 7);

                $stmt = $pdo->prepare("SELECT 1 FROM urls WHERE short_url = ?");
                $stmt->execute([$short_url]);
            } while ($stmt->fetchColumn());

            // DB ga saqlash
            $stmt = $pdo->prepare("INSERT INTO urls (long_url, short_url, created_date) VALUES (?, ?, NOW())");
            $stmt->execute([$long_url, $short_url]);
        }

        // Domenni dinamik olish
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $domain = $_SERVER['HTTP_HOST'];
        $short_url = $protocol . $domain . '/' . $short_url;

        // Faqat bir marta ko‘rsatish uchun — session ga saqlaymiz
        $_SESSION['short_url_once'] = $short_url;
        } else {
            $_SESSION['errors'] = $errors;
        }
}

// Xato va natijani faqat bir marta chiqarish
if (!empty($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}

if (!empty($_SESSION['short_url_once'])) {
    $success_message = $_SESSION['short_url_once'];
    unset($_SESSION['short_url_once']); // Refreshda yo‘qoladi!
}

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>Short URL</title>
    </head>

    <body>
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="header-title mt-5">Short URL</h1>
                <p class="by-text">Made by <a href="https://x.com/husanswe">Husan SWE</a></p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="card main-card border-0">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4 text-dark fw-bold">
                                Paste the URL to be shortened
                            </h2>

                            <!-- FORM -->
                            <form action="" method="POST" class="row g-3">
                                <div class="col-12 col-md-8">
                                    <input 
                                        type="url" 
                                        name="url" 
                                        class="form-control form-control-lg" 
                                        placeholder="Enter the link here" 
                                        required
                                        autocomplete="off">
                                </div>
                                <div class="col-12 col-md-4">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 btn-shorten">
                                        Shorten
                                    </button>
                                </div>
                            </form>

                            <!-- Result -->
                            <div id="result" class="mt-4 text-center">
                                <?php if (!empty($_SESSION['errors'])): ?>
                                    <div class="alert alert-danger mt-4">
                                        <?php foreach ($_SESSION['errors'] as $error): ?>
                                            <div class="mt-4">
                                            <?php if (isset($_SESSION['error'])): ?>
                                                <div class="alert alert-danger alert-dismissible fade show">
                                                    <?= htmlspecialchars($_SESSION['error']) ?>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                                </div>
                                                <?php unset($_SESSION['error']); ?>
                                            <?php endif; ?>

                                            <?php if (isset($_SESSION['success'])): ?>
                                                <div class="alert alert-success alert-dismissible fade show text-center">
                                                    <h4 class="alert-heading">Qisqa havola tayyor!</h4>
                                                    <p class="mb-3">
                                                        <big>
                                                            <a href="<?= htmlspecialchars($_SESSION['success']) ?>" target="_blank">
                                                                <?= htmlspecialchars($_SESSION['success']) ?>
                                                            </a>
                                                        </big>
                                                    </p>
                                                    <button class="btn btn-outline-success btn-sm" onclick="copyToClipboard('<?= htmlspecialchars($_SESSION['success']) ?>')">
                                                        Nusxa olish
                                                    </button>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                                </div>
                                                <?php unset($_SESSION['success']); ?>  <!-- Bir marta ko‘rsatib o‘chiramiz -->
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php unset($_SESSION['errors']); ?>
                                <?php endif; ?>

                                <?php if (!empty($_SESSION['short_url'])): ?>
                                <div class="alert alert-success mt-4 text-center">
                                    <h4>Short link is ready!</h4>
                                    <big><a href="<?= htmlspecialchars($_SESSION['short_url']) ?>" target="_blank">
                                        <?= htmlspecialchars($_SESSION['short_url']) ?>
                                    </a></big>
                                    <button class="btn btn-outline-success btn-sm ms-3" onclick="copyToClipboard('<?= htmlspecialchars($_SESSION['short_url']) ?>')">
                                        Copy
                                    </button>
                                </div>
                                <?php unset($_SESSION['short_url']); unset($_SESSION['success']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    alert("Copied!");
                });
            }
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>