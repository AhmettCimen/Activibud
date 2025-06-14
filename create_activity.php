<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    die();
}
$database = new Database();//veritabanı bağlantısını belki include a alabilirm
$db = $database->getConnection();
$error = '';
$success = '';
$stmt = $db->query("SELECT id, name FROM categories ORDER BY name");//kategorileri alıyorum
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $location = $_POST['location'];
    $activity_date = $_POST['activity_date'];
    $max_participants = $_POST['max_participants'];
    $creator_id = $_SESSION['user_id'];
    try {
        $stmt = $db->prepare("INSERT INTO activities (title, description, category_id, creator_id, location, activity_date, max_participants) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $category_id, $creator_id, $location, $activity_date, $max_participants]);
        $success = "Aktivite başarıyla oluşturuldu!";
    } catch (PDOException $e) {
        $error = "Aktivite oluşturulurken bir hata oluştu!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivite Oluştur - Activibud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .create-container {
            background-image: url('assets/images/create.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: calc(100vh - 56px);
            padding: 2rem 0;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.95);
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="create-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Yeni Aktivite Oluştur</h4>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success"><?= $success ?></div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Aktivite Başlığı</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>

                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Kategori</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Kategori Seçin</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Aktivite Açıklaması</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"
                                        required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="location" class="form-label">Konum</label>
                                    <input type="text" class="form-control" id="location" name="location" required>
                                </div>

                                <div class="mb-3">
                                    <label for="activity_date" class="form-label">Aktivite Tarihi ve Saati</label>
                                    <input type="datetime-local" class="form-control" id="activity_date"
                                        name="activity_date" required>
                                </div>

                                <div class="mb-3">
                                    <label for="max_participants" class="form-label">Maksimum Katılımcı Sayısı</label>
                                    <input type="number" class="form-control" id="max_participants"
                                        name="max_participants" min="2" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Aktivite Oluştur</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>