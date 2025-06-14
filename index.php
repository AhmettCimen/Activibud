<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT COUNT(*) as count FROM activities";
$stmt = $db->prepare($query);
$stmt->execute();
$activity_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

//kullanıcı countunu alyıorum
$query = "SELECT COUNT(*) as count FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$user_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activibud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section {
            position: relative;
            height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/main.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .hero-content {
            max-width: 800px;
            padding: 2rem;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .hero-button {
            padding: 1rem 2rem;
            font-size: 1.2rem;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .hero-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .stats-section {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 1rem 2rem;
            border-radius: 50px;
            margin-top: 2rem;
        }

        .stat-item {
            display: inline-block;
            margin: 0 1.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.2rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .stats-section {
                padding: 1rem;
            }

            .stat-item {
                margin: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Aktivibud</h1>
            <p class="hero-subtitle">Yeni insanlar, yeni deneyimler </p>

            <div class="hero-buttons">
                <a href="activities.php" class="btn btn-light btn-lg hero-button">
                    <i class="bi bi-list-ul me-2"></i>Aktiviteleri Keşfet
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="create_activity.php" class="btn btn-primary btn-lg hero-button">
                        <i class="bi bi-plus-circle me-2"></i>Aktivite Oluştur
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-lg hero-button">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap
                    </a>
                <?php endif; ?>
            </div>

            <div class="stats-section">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $activity_count; ?></div>
                    <div class="stat-label">Aktivite</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $user_count; ?></div>
                    <div class="stat-label">Üye</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css"></script>
</body>

</html>