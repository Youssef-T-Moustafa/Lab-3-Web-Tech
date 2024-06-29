<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=lab3database', 'root', '');
    echo "PDO connection successful!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
