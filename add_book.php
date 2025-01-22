<?php
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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from the form
    $new_book_title = $conn->real_escape_string($_POST['new_book_title']);
    $book_author = $conn->real_escape_string($_POST['book_author']);
    $publish_year = $conn->real_escape_string($_POST['publish_year']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $quantity = (int)$_POST['quantity']; // Ensure quantity is an integer

    // Insert the new book into the database
    $sql = "INSERT INTO books (title, author, publish_year, isbn, quantity) 
            VALUES ('$new_book_title', '$book_author', '$publish_year', '$isbn', $quantity)";

    if ($conn->query($sql) === TRUE) {
        echo "New book added successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close the connection
    $conn->close();
}
?>
