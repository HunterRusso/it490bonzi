<?php


session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);



//this is the authentication check for logging in

if (empty($_SESSION['email']) || empty($_SESSION['session_token'])) {
    header("Location: login.php"); //go back to login page loser L

    exit();

}



//user info

$email = htmlspecialchars($_SESSION['email']);
$token = htmlspecialchars($_SESSION['session_token']);
$expires = htmlspecialchars($_SESSION['expires_at']);

?>


<!DOCTYPE html>
</script>
<h1>Welcome To BetBetter!</h1>
<body>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BetBetter!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  </head>
  <body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="/index.html">Navbar</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link text-dark" href="/index.html">Home <span class="sr-only">(current)</span></a>
      </li>
    </ul>

<ul class="navbar-nav me-auto">
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle text-dark" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      User
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="/profile.php">Profile</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="/logout.php">Logout</a>
        </div>
      </li>
        </ul>
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
        <a href="/register.php" class="btn btn-outline-primary me-2">Register</a>
        </li>
        <li class="nav-item">
    <a href="/login.php" class="btn btn-outline-primary">Login</a>
        </li>
        </ul>
  </div>
  </div>
</nav>

 <img src="images/Social.jpeg" alt="Girl in a jacket">
  <img src="images/Lose.jpeg" alt="Girl in a jacket">
<img src="images/wolf.gif" alt="Girl in a jacket" style="width:480px;height:480px;">
  <img src="images/sweet.webp" alt="Girl in a jacket" style="width:480px;height:480px;">


<div id="textResponse">
awaiting response
</div>
<script>
SendLoginRequest("kehoed","12345");
</script>
</body>
</html>
