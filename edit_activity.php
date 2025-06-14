<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: activities.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$activity_id = $_GET['id'];
$error = '';
$success = '';


$query = "SELECT * FROM activities WHERE id = :id AND creator_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $activity_id);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    header("Location: activities.php");
    exit();
}

$activity = $stmt->fetch(PDO::FETCH_ASSOC);

$query = "SELECT id, name FROM categories ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $location = $_POST['location'];
    $activity_date = $_POST['activity_date'];
    $max_participants = $_POST['max_participants'];

   
    $query = "SELECT COUNT(*) as count FROM activity_participants WHERE activity_id = :activity_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":activity_id", $activity_id);
    $stmt->execute();
    $current_participants = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($max_participants < $current_participants) {
        $error = "Maksimum katılımcı sayısı mevcut katılımcı sayısından az olamaz!";
    } else {
        $query = "UPDATE activities 
                 SET title = :title, 
                     description = :description, 
                     category_id = :category_id, 
                     location = :location, 
                     activity_date = :activity_date, 
                     max_participants = :max_participants 
                 WHERE id = :id AND creator_id = :user_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":activity_date", $activity_date);
        $stmt->bindParam(":max_participants", $max_participants);
        $stmt->bindParam(":id", $activity_id);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);

        if ($stmt->execute()) {
            $success = "Aktivite başarıyla güncellendi!";
            $query = "SELECT * FROM activities WHERE id = :id AND creator_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $activity_id);
            $stmt->bindParam(":user_id", $_SESSION['user_id']);
            $stmt->execute();
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Aktivite güncellenirken bir hata oluştu!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivite Düzenle - Aktivite Arkadaş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Aktivite Düzenle</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="needs-validation" novalidate id="editActivityForm">
                            <div class="mb-3">
                                <label for="title" class="form-label">Aktivite Başlığı</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($activity['title']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategori</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Kategori Seçin</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $activity['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Aktivite Açıklaması</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="4" required><?php echo htmlspecialchars($activity['description']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="location" class="form-label">Konum</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?php echo htmlspecialchars($activity['location']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="activity_date" class="form-label">Aktivite Tarihi ve Saati</label>
                                <input type="datetime-local" class="form-control" id="activity_date" name="activity_date" 
                                       value="<?php echo date('Y-m-d\TH:i', strtotime($activity['activity_date'])); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="max_participants" class="form-label">Maksimum Katılımcı Sayısı</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants" 
                                       value="<?php echo $activity['max_participants']; ?>" min="2" required>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="submitBtn">Güncelle</button>
                                <a href="activity_details.php?id=<?php echo $activity_id; ?>" class="btn btn-secondary">İptal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editActivityForm');
            const submitBtn = document.getElementById('submitBtn');
            const originalBtnText = submitBtn.innerHTML;

            form.addEventListener('submit', function(e) {
                if (form.checkValidity()) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Yükleniyor...';
                }
            });

          
            form.addEventListener('invalid', function(e) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }, true);

           
            <?php if ($error): ?>
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            <?php endif; ?>
        });
    </script>
</body>
</html> 