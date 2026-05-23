<?php
session_start();

// Database connection (configure with your DB details)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'circusas_db';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Handle login
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $login_error = "Invalid password";
        }
    } else {
        $login_error = "Email not found";
    }
}

// Handle registration
$register_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $register_error = "Passwords don't match";
    } else if (strlen($password) < 6) {
        $register_error = "Password must be at least 6 characters";
    } else {
        $check_email = "SELECT * FROM users WHERE email = '$email'";
        if ($conn->query($check_email)->num_rows > 0) {
            $register_error = "Email already exists";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
            if ($conn->query($insert)) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $username;
                header("Location: index.php");
                exit();
            }
        }
    }
}

$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Circusas</title>
    <link rel="stylesheet" href="style1.css">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Cormorant+Garamond:wght@400;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        #loginForm, #registerForm {
            display: none;
            background-color: #222;
            padding: 20px;
            margin: 20px auto;
            width: 300px;
            border: 1px solid #666;
        }
        #loginForm.show, #registerForm.show {
            display: block;
        }
        .form-box input {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #999;
            background-color: #111;
            color: #ccc;
        }
        .form-box button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background-color: #666;
            color: white;
            border: 1px solid #999;
            cursor: pointer;
        }
        .form-box button:hover {
            background-color: #888;
        }
        .error {
            color: #ff9999;
            font-size: 12px;
            margin: 10px 0;
        }
        .toggle-link {
            font-size: 12px;
            color: #999;
            text-align: center;
            margin: 10px 0;
        }
        .toggle-link a {
            color: #ccc;
            cursor: pointer;
            text-decoration: underline;
        }
        .user-info {
            color: #ccc;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <main>
        <section id="home">
            <article class="po">
                <nav>
                    <a href="#home" id="innav">HOME</a>
                    <a href="#about" id="innav">ABOUT</a>
                    <a href="#gallery" id="innav">GALLERY</a>
                    <a href="#contact" id="innav">CONTACT</a>
                    <?php if ($is_logged_in): ?>
                        <span class="user-info">Hi <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <button class="login-btn"><a href="?logout=true">Logout</a></button>
                    <?php else: ?>
                        <button class="login-btn"><a onclick="toggleLogin()">Login</a></button>
                    <?php endif; ?>
                </nav>
                
                <h1 id="welcoming">WELCOME TO THE <br> <span id="brown-un">UNEXPECTED.</span></h1>
                <br>
                <h5 id="pretty">-----<span class="ds">circusas</span>------<span class="ds">design</span>-----<span class="ds">nermine&maroua</span>-----</h5>
                <p id="tit">Where the talent meet fashion ,Discover the <br>shop now!</p>
                <button class="discover">
                    <a href="#about">Explore Now</a>
                </button>
            </article>
        </section>

        <!-- Login/Register Forms -->
        <div id="loginForm" class="form-box">
            <h3>Login</h3>
            <?php if ($login_error): ?>
                <div class="error"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Sign In</button>
            </form>
            <div class="toggle-link">
                No account? <a onclick="toggleRegister()">Sign up</a>
            </div>
        </div>

        <div id="registerForm" class="form-box">
            <h3>Create Account</h3>
            <?php if ($register_error): ?>
                <div class="error"><?php echo $register_error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" name="register">Sign Up</button>
            </form>
            <div class="toggle-link">
                Have an account? <a onclick="toggleLogin()">Login</a>
            </div>
        </div>
        
        <hr>
        <section id="about">
            <div id="general">
                <div class="d1">
                    <img src="1.jpg" alt="clownmask1">
                </div>
                <div class="d2">
                    <h2 class="samestyle2">About</h2>
                    <h1 id="titreabout">OUR STORY</h1>
                    <p class="pabout">Cirque was born from a feeling , he kind you get as a child  sitting in front of a screen  watching the magic unfold
                    We grew up watching circuses in movies, wondering what it would feel like to be part of that world not just to watch it, but to live inside it.
                    But when the screen turned off, that world disappeared. 
                    So we created a place where it doesnt have 
                    <br><br><span id="dream">
                    Time to turn the dream into Reality</span>
                    <br><br><br>
                    This is more than a shop.Its a doorway</p>
                </div>
                <div class="d3">
                    <img src="2.jpg" alt="clownmask2">
                </div>
            </div>
        </section>

        <section id="gallery">
            <h2 class="samestyle">GALLERY</h2>
            <div class="gallery-g">
                <img src="img1.jpg" id="left" alt="bangs">
                <img src="img2.jpg" id="left2" alt="cloth">
                <img src="img3.jpg" id="right2" alt="head">
                <img src="img4.jpg" id="right" alt="head">
            </div>
            <p id="pop">you can buy of those style login and get those cool items and more with a good price</p>
        </section>

        <section id="seccontact">
            <section id="contact">
                <div id="con">
                    <h2 class="samestyle" id="tired">CONTACT</h2>
                    <p class="fin">you can always contact us for more informations <br>
                        If you need help have questions or feel a little lost reach out anytime!</p>
                </div>
                <div id="contactus">
                    <ul>
                        <div class="contact-box">
                            <li><h3 class="bye">Email Us</h3></li>
                            <p class="pbye">support@cirque.com</p>
                        </div>
                        <div class="contact-box">
                            <li><h3 class="bye">Call Us</h3></li>
                            <p class="pbye">+213 663 456 789</p>
                        </div>
                        <div class="contact-box">
                            <li><h3 class="bye">Constomer Service</h3></li>
                            <p class="pbye">Available 24/7 to guide you through your experience.</p>
                        </div>
                        <div class="contact-box">
                            <li><h3 class="bye">Donate</h3></li>
                            <p class="pbye">youcan donate here to improve the webpage and for more access</p>
                        </div>
                    </ul>
                </div>
            </section>
        </section>
    </main>

    <script>
        function toggleLogin() {
            let form = document.getElementById('loginForm');
            form.classList.toggle('show');
            document.getElementById('registerForm').classList.remove('show');
        }

        function toggleRegister() {
            let form = document.getElementById('registerForm');
            form.classList.toggle('show');
            document.getElementById('loginForm').classList.remove('show');
        }
    </script>
</body>
</html>
