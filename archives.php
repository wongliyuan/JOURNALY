<?php
session_start();
include '../config.php';

// Fetch editions from the database
$sql = "SELECT * FROM editions ORDER BY publication_date DESC"; // You can change the sorting based on your needs
$stmt = $pdo->query($sql);
$editions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'login' && isset($_POST['email'], $_POST['password'])) {
        // Login logic
        $email = $_POST['email'];
        $password = $_POST['password'];
        $alertMessage = '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'User':
                        header("Location: ../USERS/my-submission.php");
                        break;
                    case 'Editor':
                        header("Location: ../EDITOR/my-dashboard.php");
                        break;
                    case 'Admin':
                        header("Location: ../ADMIN/my-user.php");
                        break;
                    default:
                        echo "<script>Swal.fire('Error', 'Role not defined for this user.', 'error');</script>";
                        break;
                }
                exit();
            } else {
                echo $alertMessage = "<script>Swal.fire('Error', 'Incorrect password.', 'error');</script>";
            }
        } else {
            echo $alertMessage = "<script>Swal.fire('Error', 'No user found with that email.', 'error');</script>";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'signup' && isset($_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
        // Signup logic
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        $alertMessage = '';

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $alertMessage = "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Email',
                    text: 'Please provide a valid email address.',
                });
            </script>";
        } elseif ($password !== $confirm_password) {
            $alertMessage = "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Passwords do not match!',
                });
            </script>";
        } else {
            $check_stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $check_stmt->bindParam(':email', $email);
            $check_stmt->execute();

            if ($check_stmt->rowCount() > 0) {
                $alertMessage = "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Email is already registered!',
                    });
                </script>";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (:email, :password, 'User')");
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);

                if ($stmt->execute()) {
                    $alertMessage = "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Account created successfully. You can now log in!',
                        }).then(() => {
                            window.location.href = 'login.php';
                        });
                    </script>";
                } else {
                    $alertMessage = "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Database Error',
                            text: 'Could not create account.',
                        });
                    </script>";
                }
            }
        }

        echo $alertMessage;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Journaly - Archives</title>
  <link rel="stylesheet" href="Gstyle.css" />
  <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet" />
</head>
<body>
    <?php include 'navbar.php';?>

    <section class="home">
    <main>
        <section style="margin-top:50px;margin-left:50px;" class="editions-list">
            <!-- <h2>Archives</h2> -->
            <div class="product-grid">
                <?php foreach ($editions as $edition): ?>
                    <a href="submissions.php?edition_id=<?= $edition['id'] ?>" class="product-card">
                        <img src="../uploads/<?= $edition['cover_image'] ?>" alt="Edition Cover" class="product-img">
                        <h3 class="product-title"><?= htmlspecialchars($edition['title']) ?></h3>
                        <p class="product-date"><?= date('F d, Y', strtotime($edition['publication_date'])) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <div class="form_container">
        <i class='bx bx-x form_close'></i>
        <!-- Login From -->
        <div class="form login_form">
            <form action="" method="POST">
                <input type="hidden" name="action" value="login">
                <h2>Login</h2>
                <div class="input_box">
                    <input type="email" name="email" placeholder="Enter your email" required />
                    <i class='bx bx-envelope email'></i>
                </div>
                <div class="input_box">
                    <input type="password" name="password" placeholder="Enter your password" required />
                    <i class='bx bx-lock password'></i>
                    <i class='bx bx-hide pw_hide'></i>
                </div>
                <div class="option_field">
                    <span class="checkbox">
                        <input type="checkbox" id="check" />
                        <label for="check">Remember me</label>
                    </span>
                    <a href="forgot-password.php" class="forgot_pw">Forgot password?</a>
                </div>
                <button class="button">Login Now</button>
                <div class="login_signup">Don't have an account? <a href="#" id="signup">Signup</a></div>
            </form>
        </div>
        <!-- Signup From -->
        <div class="form signup_form">
            <form action="" method="POST">
                <input type="hidden" name="action" value="signup">
                <h2>Sign Up</h2>
                <div class="input_box">
                    <input type="email" name="email" placeholder="Enter your email" required />
                    <i class='bx bx-envelope email'></i>
                </div>
                <div class="input_box">
                    <input type="password" name="password" placeholder="Create password" required />
                    <i class='bx bx-lock password'></i>
                    <i class='bx bx-hide pw_hide'></i>
                </div>
                <div class="input_box">
                    <input type="password" name="confirm_password" placeholder="Confirm password" required />
                    <i class='bx bx-lock password'></i>
                    <i class='bx bx-hide pw_hide'></i>
                </div>
                <button class="button">Signup Now</button>
                <div class="login_signup">Already have an account? <a href="#" id="login">Login</a></div>
            </form>
        </div>
      </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php
    // Include SweetAlert script if any alert message is set
    if (isset($alertMessage)) {
        echo $alertMessage;
    }
    ?>

    <script>
        const formOpenBtn = document.querySelector("#form-open"),
        home = document.querySelector(".home"),
        formContainer = document.querySelector(".form_container"),
        formCloseBtn = document.querySelector(".form_close"),
        signupBtn = document.querySelector("#signup"),
        loginBtn = document.querySelector("#login"),
        pwShowHide = document.querySelectorAll(".pw_hide");

        formOpenBtn.addEventListener("click", () => home.classList.add("show"));
        formCloseBtn.addEventListener("click", () => home.classList.remove("show"));

        pwShowHide.forEach((icon) => {
        icon.addEventListener("click", () => {
            let getPwInput = icon.parentElement.querySelector("input");
            if (getPwInput.type === "password") {
            getPwInput.type = "text";
            icon.classList.replace("uil-eye-slash", "uil-eye");
            } else {
            getPwInput.type = "password";
            icon.classList.replace("uil-eye", "uil-eye-slash");
            }
        });
        });

        signupBtn.addEventListener("click", (e) => {
        formContainer.classList.add("active");
        });
        loginBtn.addEventListener("click", (e) => {
        e.preventDefault();
        formContainer.classList.remove("active");
        });
    </script>

</body>
</html>