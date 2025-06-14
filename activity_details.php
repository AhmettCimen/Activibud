<?php
session_start();
require_once 'config/database.php';
if (!isset($_GET['id'])) {
    header("Location: activities.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$activity_id = $_GET['id'];
$query = "SELECT a.*, c.name as category_name, u.username as creator_name,
          (SELECT COUNT(*) FROM activity_participants WHERE activity_id = a.id) as participant_count
          FROM activities a
          LEFT JOIN categories c ON a.category_id = c.id
          LEFT JOIN users u ON a.creator_id = u.id
          WHERE a.id = :id";

$stmt = $db->prepare($query);
$stmt->bindParam(":id", $activity_id);
$stmt->execute();
if ($stmt->rowCount() == 0) {
    header("Location: activities.php");
    exit();
}
$activity = $stmt->fetch(PDO::FETCH_ASSOC);
$query = "SELECT u.username, u.full_name, ap.joined_at
          FROM activity_participants ap
          JOIN users u ON ap.user_id = u.id
          WHERE ap.activity_id = :activity_id
          ORDER BY ap.joined_at ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(":activity_id", $activity_id);
$stmt->execute();
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';
if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['join'])) {
        $query = "SELECT id FROM activity_participants 
                 WHERE activity_id = :activity_id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":activity_id", $activity_id);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "Bu aktiviteye zaten katılıyorsunuz!";
        } else {

            if ($activity['participant_count'] >= $activity['max_participants']) {
                $error = "Bu aktivite maksimum katılımcı sayısına ulaştı!";
            } else {
                $query = "INSERT INTO activity_participants (activity_id, user_id) 
                         VALUES (:activity_id, :user_id)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":activity_id", $activity_id);
                $stmt->bindParam(":user_id", $_SESSION['user_id']);

                if ($stmt->execute()) {
                    $success = "Aktiviteye başarıyla katıldınız!";

                    header("Location: activity_details.php?id=" . $activity_id);
                    exit();
                } else {
                    $error = "Katılım sırasında bir hata oluştu!";
                }
            }
        }
    } elseif (isset($_POST['leave'])) {

        $query = "DELETE FROM activity_participants 
                 WHERE activity_id = :activity_id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":activity_id", $activity_id);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);

        if ($stmt->execute()) {
            $success = "Aktiviteden ayrıldınız!";

            header("Location: activity_details.php?id=" . $activity_id);
            exit();
        } else {
            $error = "Ayrılma sırasında bir hata oluştu!";
        }
    } elseif (isset($_POST['delete']) && $activity['creator_id'] == $_SESSION['user_id']) {

        $query = "DELETE FROM activity_participants WHERE activity_id = :activity_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":activity_id", $activity_id);
        $stmt->execute();


        $query = "DELETE FROM activities WHERE id = :activity_id AND creator_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":activity_id", $activity_id);
        $stmt->bindParam(":user_id", $_SESSION['user_id']);

        if ($stmt->execute()) {
            header("Location: activities.php?deleted=1");
            exit();
        } else {
            $error = "Aktivite silinirken bir hata oluştu!";
        }
    }
}
$is_participating = false;
if (isset($_SESSION['user_id'])) {
    $query = "SELECT id FROM activity_participants 
             WHERE activity_id = :activity_id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":activity_id", $activity_id);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->execute();
    $is_participating = $stmt->rowCount() > 0;
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($activity['title']); ?> - Activibud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .details-container {
            background-image: url('assets/images/details.jpeg');
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

    <div class="details-container">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title"><?php echo htmlspecialchars($activity['title']); ?></h2>
                            <h6 class="card-subtitle mb-3 text-muted">
                                Kategori: <?php echo htmlspecialchars($activity['category_name']); ?>
                            </h6>

                            <div class="mb-4">
                                <h5>Aktivite Detayları</h5>
                                <p class="card-text">
                                    <?php echo nl2br(htmlspecialchars($activity['description'])); ?>
                                </p>
                            </div>

                            <div class="mb-4">
                                <h5>Bilgiler</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Konum:</strong> <?php echo htmlspecialchars($activity['location']); ?>
                                    </li>
                                    <li><strong>Tarih ve Saat:</strong>
                                        <?php echo date('d.m.Y H:i', strtotime($activity['activity_date'])); ?></li>
                                    <li><strong>Oluşturan:</strong>
                                        <?php echo htmlspecialchars($activity['creator_name']); ?></li>
                                    <li><strong>Katılımcılar:</strong>
                                        <?php echo $activity['participant_count']; ?>/<?php echo $activity['max_participants']; ?>
                                    </li>
                                </ul>
                            </div>

                            <div class="d-flex gap-2">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <?php if ($activity['creator_id'] == $_SESSION['user_id']): ?>
                                        <a href="edit_activity.php?id=<?php echo $activity['id']; ?>" class="btn btn-warning">
                                            Düzenle
                                        </a>
                                        <form method="POST" action="" class="d-inline"
                                            onsubmit="return confirm('Bu aktiviteyi silmek istediğinizden emin misiniz?');">
                                            <button type="submit" name="delete" class="btn btn-danger">
                                                Sil
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="">
                                            <?php if ($is_participating): ?>
                                                <button type="submit" name="leave" class="btn btn-danger">
                                                    Aktiviteten Ayrıl
                                                </button>
                                            <?php else: ?>
                                                <?php if ($activity['participant_count'] < $activity['max_participants']): ?>
                                                    <button type="submit" name="join" class="btn btn-primary">
                                                        Katıl
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-secondary" disabled>
                                                        Aktivite Dolu
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">Katılmak için <a href="login.php">giriş yapın</a>.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Katılımcılar</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($participants)): ?>
                                <p class="text-muted">Henüz katılımcı yok.</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($participants as $participant): ?>
                                        <li class="list-group-item">
                                            <?php echo htmlspecialchars($participant['full_name']); ?>
                                            <small class="text-muted d-block">
                                                @<?php echo htmlspecialchars($participant['username']); ?>
                                            </small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>