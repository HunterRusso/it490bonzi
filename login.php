<?php


require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');



session_start();
error_reporting(E_ALL);
ini_set("show_errors", 1);
$error = "";


if (isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = ($_POST['email'] ?? '');

    $password = ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "theer is an invalid email address, lame-o!!.";

    } elseif (empty($password)) {

        $error = "the password cannot be blank, you fool!.";

    } else {

        try {
            $client = new rabbitMQClient("testRabbitMQ.ini", "oddsLoginServer");

            $request = [
        	'type'       => 'login',
        	'user'   => $email,
        	'password'   => $password,
        	'session_id' => session_id(),
    	];


    	$response = $client->send_request($request);
                

            if (!is_array($response)) {

                $error = "Sevrer error or no response from the dbworker.";

            } elseif (($response['status'] ?? '') === 'ok') {

                $_SESSION['email'] = $email;

                $_SESSION['session_key'] = $response['session_key'] ?? '';

                $_SESSION['expires_at'] = $response['expires_at'] ?? '';
                header("Location: index.php");
                exit();

            } else {

                $error = $response['message'] ?? "there is an invalid username or password, BOZO.";
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
<title>Login</title>
</head>

<body>
<h2>Login</h2>

<p style="color:red;"><?php echo htmlspecialchars($error); ?></p>

<form method="post" action="">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <input type="submit" value="Login">
</form>

<p>Don't have an account? <a href="register.php">Register Here!!</a>.</p>

</body>
</html>
