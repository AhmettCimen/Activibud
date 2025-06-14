<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();


$query = "SELECT id, name FROM categories ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$category_id = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT a.*, c.name as category_name, u.username as creator_name,
          (SELECT COUNT(*) FROM activity_participants WHERE activity_id = a.id) as participant_count
          FROM activities a
          LEFT JOIN categories c ON a.category_id = c.id
          LEFT JOIN users u ON a.creator_id = u.id";

$params = array();

if ($category_id) {
    $query .= " AND a.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

if ($search) {
    $query .= " AND (a.title LIKE :search OR a.description LIKE :search OR a.location LIKE :search)";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY a.activity_date ASC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktiviteler - Activibud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .activities-container {
            background-image: url('assets/images/activities.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: calc(100vh - 56px);
            padding: 2rem 0;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.95);
        }
        .filter-card {
            background-color: rgba(255, 255, 255, 0.95);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="activities-container">
        <div class="container">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">
                    Aktivite başarıyla silindi!
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-3">
                    <div class="card filter-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Filtreler</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Kategori</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">Tüm Kategoriler</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="search" class="form-label">Arama</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <h2>Aktiviteler</h2>
                    
                    <?php if (empty($activities)): ?>
                        <div class="alert alert-info">
                            Henüz aktivite bulunmuyor.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($activities as $activity): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($activity['title']); ?></h5>
                                            <h6 class="card-subtitle mb-2 text-muted">
                                                <?php echo htmlspecialchars($activity['category_name']); ?>
                                            </h6>
                                            <p class="card-text">
                                                <?php echo nl2br(htmlspecialchars(substr($activity['description'], 0, 150))); ?>...
                                            </p>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <strong>Konum:</strong> <?php echo htmlspecialchars($activity['location']); ?><br>
                                                    <strong>Tarih:</strong> <?php echo date('d.m.Y H:i', strtotime($activity['activity_date'])); ?><br>
                                                    <strong>Katılımcılar:</strong> <?php echo $activity['participant_count']; ?>/<?php echo $activity['max_participants']; ?>
                                                </small>
                                            </p>
                                            <a href="activity_details.php?id=<?php echo $activity['id']; ?>" 
                                               class="btn btn-primary">Detayları Gör</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 