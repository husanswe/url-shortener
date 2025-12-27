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
            $_SESSION['short_url'] = $long_url;
            $_SESSION['success'] = true;
        } else {
            $_SESSION['errors'] = $errors;
        }
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

                            <!-- Muvaffaqiyatli natija chiqsa shu yerda ko‘rsatiladi (keyin JS yoki PHP echo bilan) -->
                            <div id="result" class="mt-4 text-center">
                                <?php if (!empty($_SESSION['errors'])): ?>
                                    <div class="alert alert-danger mt-4">
                                        <?php foreach ($_SESSION['errors'] as $error): ?>
                                            <p><?= htmlspecialchars($error) ?></p>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>