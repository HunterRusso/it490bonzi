<?php


require_once(__DIR__ . "/../vendor/autoload.php");
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//rabbitmq broker
$mqhost  = '172.29.193.35';
$mquser  = 'odds_app';
$mqpass  = 'Bonzi123!'; //our group name
$mqvhost = '/odds'; //for gambling!!

//db
$dbhost = 'localhost'; //this connects over local host on my machine
$dbuser = 'testadmin';
$dbpass = '1984';
$dbame = 'authdb';


$conn = new AMQPStreamConnection($mqhost, 5672, $mquser, $mqpass, $mqvhost);
$ch   = $conn->channel();
$ch->queue_declare('odds.db.rpc', false, true, false, false); //declares usable queue

echo "now listening for traffic on odds.db.rpc";

//defines what will happen when messages come in
$callback = function ($msg) use ($dbhost, $dbuser, $dbpass, $dbname) {

    echo "new traffic: {$msg->body}\n";

    $data = json_decode($msg->body, true); //decodes received data

    if (!$data) {
        echo "invalid jsomn message.\n";
        $msg->ack();
        return;
    }

    $type       = $data['type'] ?? '';
    $user   = $data['user'] ?? '';
    $password   = $data['password'] ?? '';
    $session_id = $data['session_id'] ?? '';

    $response = ['status' => 'fail', 'message' => 'unknown error'];

    $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname); //connects to the db

    if ($mysqli->connect_error) {
        $response['message'] = "bd connection failed: " . $mysqli->connect_error;
        sendReply($msg, $response);
        $msg->ack();
        return;
    }

//handles users logging in and gets their session into the session db

    if ($type === 'login') {

        if (!$user || !$password) {
            $response['message'] = "there is a missing email or password.";

        } else {

            $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email=?")
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            $stmt->close();


            if ($user && $password === $user['password']) {

                $expiry = date('Y-m-d H:i:s', strtotime('+2 hours')); //add expires time in 2 hours

//pulls the session token for the sessions db
                $stmt = $mysqli->prepare("INSERT INTO sessions (user_id, session_key, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE expires_at = VALUES(expires_at) "); //puts the session token into the session table with time and expire time
                $stmt->bind_param("iss", $user['id'], $session_id, $expiry);
                $stmt->execute();
                $stmt->close();

                echo "the user '$user' has logged in successfully!!!\n";

                $response = [
                    'status'        => 'ok',
                    'session_key' => $session_id,
                    'expires_at'    => $expiry,
                    'message'       => 'login successful'
                ];
            } else {
                $response['message'] = "L BOZO! invalid email or password.";
                echo "there was a recent failed login attempt for '$user'.\n";
            }
        }
    }



//handles new users registering and puts theri credentials into the user db
    elseif ($type === 'register') {

        if (!$user || !$password) {

            $response['message'] = "there is a missing email or password.";

        } else {

            $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?");
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $result = $stmt->get_result();


            if ($result->fetch_assoc()) {

                $response['message'] = "this user already exists.";
                echo "registration has denied: user '$user' as the user already exists in the db.\n";

            } else {

                $stmt->close();

                $stmt = $mysqli->prepare("INSERT INTO users (email, password) VALUES (?, ?)"); //puts the data into the user table
                $stmt->bind_param("ss", $user, $password);
            $stmt->execute();
                $stmt->close();

                echo "the user '$user' has in registered!!!\n";
                $response = [
                    'status'  => 'ok',
                    'message' => 'your registration was successful!'
                ];
            }
        }
    }

//handles unkwown message types

    else {

        $response['message'] = "unkwnown request type '$type'.";
        echo "we do not know what this message is: $type\n";
    }

//sends a reply back
    sendReply($msg, $response);
    $msg->ack();
    $mysqli->close();

};

//logic for sending replies

function sendReply($msg, $response) {
    try {

        if (isset($msg->get_properties()['reply_to'])) {

            $replyTo = $msg->get('reply_to');
            $corrId = $msg->get('correlation_id') ?? null;

            $replyMsg = new AMQPMessage(
                json_encode($response),
                ['correlation_id' => $corrId]

            );

            // publish reply on same channel used for receiving
            $msg->getChannel()->basic_publish($replyMsg, '', $replyTo);

            echo "reply sent to queue: $replyTo\n";

        } else {

            echo "there is not a relpy for this, continuing with traffic\n";
        }

    } catch (Exception $e) {
        echo "error while sending reply: " . $e->getMessage() . "\n";

    }

}


$ch->basic_consume('odds.db.rpc', '', false, false, false, false, $callback);

while ($ch->is_consuming()) { //this will keep the listeenr running
    $ch->wait();
}
