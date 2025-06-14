<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $_POST['full_name'];
    if ($password !== $confirm_password) {
        $error = "Şifreler eşleşmiyor!";
    } else {

        $query = "SELECT id FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "Bu kullanıcı adı zaten kullanılıyor!";
        } else {

            $query = "SELECT id FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error = "Bu e-posta adresi zaten kullanılıyor!";
            } else {

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $query = "INSERT INTO users (username, email, password, full_name) 
                         VALUES (:username, :email, :password, :full_name)";

                $stmt = $db->prepare($query);
                $stmt->bindParam(":username", $username);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":password", $hashed_password);
                $stmt->bindParam(":full_name", $full_name);

                if ($stmt->execute()) {
                    $success = "Kayıt başarılı! Şimdi giriş yapabilirsiniz.";
                } else {
                    $error = "Kayıt sırasında bir hata oluştu!";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Aktivite Arkadaş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Kayıt Ol</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta Adresi</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Ad Soyad</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Şifre Tekrar</label>
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                        </form>

                        <div class="mt-3">
                            <p>Zaten hesabınız var mı? <a href="login.php">Giriş Yap</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>