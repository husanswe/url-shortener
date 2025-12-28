<?php
    require_once 'config.php';  // PDO ulanish

    if (isset($_GET['code'])) {
        $short_code = trim($_GET['code']);

        // DB dan long_url ni topish
        $stmt = $pdo->prepare("SELECT long_url, clicks FROM urls WHERE short_url = ? LIMIT 1");
        $stmt->execute([$short_code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Clicks ni +1 qilish (statistika uchun)
            $stmt = $pdo->prepare("UPDATE urls SET clicks = clicks + 1 WHERE short_url = ?");
            $stmt->execute([$short_url]);

            // Redirect qilish (301 permanent uchun)
            header("Location: " . $row['long_url'], true, 301);
            exit;
        } else {
            // Topilmadi — 404 sahifa yoki index.php ga qayt
            header("HTTP/1.0 404 Not Found");
            echo "Link not found!";
            exit;
        }
    } else {
        // Code yo‘q — index.php ga qayt
        header("Location: index.php");
        exit;
    }
?>