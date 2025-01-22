<?php
$errors = [];
$receipt_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation for Student Name
    if (preg_match("/^[A-Za-z ]+$/", $_POST["student_name"])) {
        $student_name = $_POST['student_name'];
        $receipt_data['Student Name'] = $student_name;
    } else {
        $errors[] = "Invalid Student Name";
    }

    // Validation for Student ID
    if (preg_match("/^[0-9]{2}-[0-9]{5}-[0-9]{1}$/", $_POST["student_id"])) {
        $student_id = $_POST['student_id'];
        $receipt_data['Student ID'] = $student_id;
        setcookie("student_id", $student_id, time() + 10, "/");
    } else {
        $errors[] = "Invalid Student ID Format";
    }

    // Validation for Email
    if (preg_match("/^[a-zA-Z0-9._%+-]+@student\.aiub\.edu$/", $_POST['email'])) {
        $email = $_POST['email'];
        setcookie("email", $email, time() + 10, "/");
    } else {
        $errors[] = "Provide the correct email format.";
    }

    // Validation for Fees
    if (preg_match("/^[0-9]+$/", $_POST["fees"])) {
        $fees = $_POST['fees'];
        $receipt_data['Fees'] = $fees;
    } else {
        $errors[] = "Invalid Fees Format";
    }

    // Validation for Book Title
    if (!empty($_POST['book_title'])) {
        $book_title = $_POST['book_title'];
        $receipt_data['Book Title'] = $book_title;
        setcookie("book_title", $book_title, time() + 10, "/");
    } else {
        $errors[] = "No Book Selected";
    }

    // Validation for Borrow and Return Dates
    $borrow_date = $_POST['borrow_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $token = $_POST['token'] ?? '';  // Get token value from POST

    if (!empty($borrow_date) && !empty($return_date)) {
        // Skip the validation if a token is provided
        if (empty($token)) {
            $borrow_date_obj = DateTime::createFromFormat('Y-m-d', $borrow_date);
            $return_date_obj = DateTime::createFromFormat('Y-m-d', $return_date);
            $date_diff = $borrow_date_obj->diff($return_date_obj)->days;

            if ($date_diff <= 10) {
                $receipt_data['Borrow Date'] = $borrow_date;
                $receipt_data['Return Date'] = $return_date;
            } else {
                $errors[] = "Borrow and Return Date gap exceeds 10 days. You need a token to borrow for more than 10 days.";
            }
        } else {
            // If token is provided, proceed without the date restriction
            $receipt_data['Borrow Date'] = $borrow_date;
            $receipt_data['Return Date'] = $return_date;
        }
    } else {
        $errors[] = "Both Borrow and Return Dates are required.";
    }

    // Token Validation and Removal
    if (!empty($token)) {
        // Database configuration
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "library";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the token exists
        $stmt = $conn->prepare("SELECT * FROM tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $errors[] = "Token not found or already used.";
        } else {
            // Remove the token
            $stmt = $conn->prepare("DELETE FROM tokens WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
        }

        $stmt->close();

        // Insert Borrowing Details into Database
        if (empty($errors)) {
            $sql = "INSERT INTO borrowings (student_name, student_id, email, book_title, borrow_date, return_date, fees, token) 
                    VALUES ('$student_name', '$student_id', '$email', '$book_title', '$borrow_date', '$return_date', '$fees', '$token')";

            if ($conn->query($sql) === TRUE) {
                echo "Borrowing details added successfully.";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        $conn->close();
    } else {
        $errors[] = "Token is required.";
    }

    // If no errors, display receipt
    if (empty($errors)) {
        echo "<h2>Receipt</h2>";
        echo "<div style='border: 1px solid #000; padding: 20px; max-width: 400px; margin: auto;'>";
        foreach ($receipt_data as $key => $value) {
            echo "<p><strong>$key:</strong> $value</p>";
        }
        echo "</div>";
    } else {
        echo "<h2>Errors:</h2><ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    }
}

// Display Cookies (Optional)
echo "<h3>Cookies Created:</h3>";
foreach ($_COOKIE as $key => $value) {
    if ($key == "student_id" || $key == "book_title" || $key == "borrow_date" || $key == "return_date" || $key == "token" || $key == "fees") {
        echo "<p><strong>$key:</strong> $value</p>";
    }
}
?>
