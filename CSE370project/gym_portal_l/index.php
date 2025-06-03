<?php

?>
<!DOCTYPE html>
<html lang="en">
 <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome to Awesome Gym!</title>

    <link rel="stylesheet" href="css/style.css">

    <style>
        body { font-family: "Arial", sans-serif; margin: 0; padding: 0; background-color: #f0f2f5; color: #333; line-height: 1.6; }
        .landing-container { width: 90%; max-width: 1100px; margin: 20px auto; padding: 0 15px; }
        header#landing-header { background: #2c3e50; color: #ecf0f1; padding: 30px 0; text-align: center; border-bottom: #1abc9c 5px solid;}
        header#landing-header h1 { margin: 0; font-size: 2.8em; font-weight: 300; color: #fff; }
        nav#landing-nav { background: #34495e; padding: 15px 0; text-align: center; margin-bottom: 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        nav#landing-nav a { color: #ecf0f1; text-decoration: none; padding: 10px 25px; margin: 0 10px; font-size: 1.1em; border-radius: 4px; transition: background-color 0.3s ease, color 0.3s ease; }
        nav#landing-nav a:hover { background-color: #1abc9c; color: #fff; }
        .hero-section { text-align: center; padding: 80px 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-bottom:40px; }
        .hero-section h2 { font-size: 3em; margin-bottom: 20px; color: #2c3e50; font-weight: 600;}
        .hero-section p { font-size: 1.3em; margin-bottom: 30px; color: #555; max-width: 700px; margin-left:auto; margin-right:auto;}
        .cta-button { display: inline-block; background: #1abc9c; color: #fff; padding: 15px 35px; text-decoration: none; font-size: 1.25em; border-radius: 5px; transition: background-color 0.3s ease, transform 0.2s ease; font-weight: bold; }
        .cta-button:hover { background-color: #16a085; transform: translateY(-3px); }
        footer#landing-footer { background: #2c3e50; color: #bdc3c7; text-align: center; padding: 25px 0; margin-top: 50px;}
        footer#landing-footer p { margin: 5px 0; }
    </style>
 </head>
 <body>
    <header id="landing-header">
        <div class="landing-container">
            <h1>Gym Management System</h1>
        </div>
    </header>

    <nav id="landing-nav">
        <div class="landing-container">
            <a href="login.php">Member / Staff Login</a>
            <a href="register.php">New Member Registration</a>
            <a href="admin_login.php">Admin Portal</a>
        </div>
    </nav>

    <div class="landing-container hero-section">
        <h2>Transform Your Body, Elevate Your Life!</h2>
        <p>
            Access state-of-the-art equipment, benefit from expert trainers, and join a motivating
            community dedicated to your fitness success.
        </p>
        <a href="register.php" class="cta-button">Join Our Gym Today!</a>
    </div>


    <footer id="landing-footer">
        <div class="landing-container">
            <p>© <?php echo date("Y"); ?> Gym Management System. All Rights Reserved.</p>
        </div>
    </footer>
 </body>
</html>