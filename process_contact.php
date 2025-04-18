<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['contact_message'] = "Please fill in all fields.";
        $_SESSION['contact_status'] = false;
        header("Location: contact.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_message'] = "Please enter a valid email address.";
        $_SESSION['contact_status'] = false;
        header("Location: contact.php");
        exit();
    }

    // Prepare email content
    $to = "your-email@example.com"; // Replace with your email address
    $email_subject = "New Contact Form Submission: " . $subject;
    $email_body = "You have received a new message from your website contact form.\n\n" .
        "Name: $name\n" .
        "Email: $email\n" .
        "Subject: $subject\n\n" .
        "Message:\n$message";
    
    $headers = "From: $email\n";
    $headers .= "Reply-To: $email";

    // Send email
    if (mail($to, $email_subject, $email_body, $headers)) {
        $_SESSION['contact_message'] = "Thank you for your message. We'll get back to you soon!";
        $_SESSION['contact_status'] = true;
    } else {
        $_SESSION['contact_message'] = "Sorry, there was an error sending your message. Please try again later.";
        $_SESSION['contact_status'] = false;
    }

    header("Location: contact.php");
    exit();
} else {
    // If someone tries to access this file directly
    header("Location: contact.php");
    exit();
}
?>
