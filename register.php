<?php


require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');



session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);
$error = "";


if (isset($_SESSION['email'])) {

    header("Location: index.php");

    exit();

}



if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = ($_POST['email'] ?? '');

    $password = ($_POST['password'] ?? '');

    $confirm_password = ($_POST['confirm_password'] ?? '');


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {


        $error = "invalid email address stinky.";

    } elseif (strlen($password) < 8) {

        $error = "your password must be a t least 8 characters bro.";

    } elseif ($password !== $confirm_password) {

        $error = "You Fool! Passwords do not match.";

    } else {


        try {
            $client = new rabbitMQClient("testRabbitMQ.ini", "oddsLoginServer");

            $request = [

        	'type'       => 'register',
        	'user'   => $email,
        	'password'   => $password,
        	'session_id' => session_id(),
    	];


    	$response = $client->send_request($request);


            if (!is_array($response)) {

                $error = "Server error. Try again chuckle nuts.";

            } elseif ($response['status'] === 'ok') {
                $_SESSION['email'] = $email;
                $_SESSION['session_key'] = session_id();

                header("Location: index.php");

                exit();

            } else {

                $error = $response['message'] ?? "sowwy, but the registration failed.";

            }

        } catch (Exception $e) {

            $error = "Error: " . $e->getMessage();

        }

    }

}

?>


<!DOCTYPE html>
<html lang="en">

<head>
<title>Register</title>
</head>

<body>
<h2>Register</h2>

<p style="color:red;"><?php echo htmlspecialchars($error); ?></p>

<form method="post" action="">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>
    <label>Confirm Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <input type="submit" value="Register">
</form>

<p>Already have an account? <a href="login.php">Login Here!!!</a>.</p>

</body>
</html>