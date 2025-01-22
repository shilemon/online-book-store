<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newToken = $_POST['new_token'] ?? '';

    if (!empty($newToken)) {
        if (!preg_match('/^[A-Za-z0-9]+$/', $newToken) || strlen($newToken) < 4 || strlen($newToken) > 20) {
            echo "Token must be alphanumeric and between 4 and 20 characters!";
            exit;
        }

        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "library";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("SELECT * FROM tokens WHERE token = ?");
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("s", $newToken);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "Token already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO tokens (token) VALUES (?)");
            if (!$stmt) {
                die("Error preparing insert statement: " . $conn->error);
            }

            $stmt->bind_param("s", $newToken);
            if ($stmt->execute()) {
                echo "Token saved successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Please enter a valid token.";
    }
} else {
    echo "Invalid request method!";
}
?>
