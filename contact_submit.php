<?php

// ==========================================
// DATABASE CONNECTION
// ==========================================

include('include/connection.php');


// ==========================================
// PHPMAILER
// ==========================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


// ==========================================
// ONLY POST REQUEST
// ==========================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: /contact");
    exit;
}


// ==========================================
// GET FORM DATA
// ==========================================

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$services = trim($_POST['services'] ?? '');
$message  = trim($_POST['message'] ?? '');


// ==========================================
// VALIDATION
// ==========================================

if (
    $name === '' ||
    $email === '' ||
    $phone === '' ||
    $services === ''
) {

    header("Location: contact.php?status=required");
    exit;
}


// ==========================================
// EMAIL VALIDATION
// ==========================================

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header("Location: contact.php?status=invalid_email");
    exit;
}


// ==========================================
// SAVE INTO DATABASE
// ==========================================

$stmt = mysqli_prepare(
    $con,
    "INSERT INTO contacts
    (
        name,
        email,
        phone,
        services,
        message
    )
    VALUES (?, ?, ?, ?, ?)"
);


if (!$stmt) {

    header("Location: contact.php?status=database_error");
    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "sssss",
    $name,
    $email,
    $phone,
    $services,
    $message
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header("Location: contact.php?status=database_error");
    exit;
}


mysqli_stmt_close($stmt);


// ==========================================
// SAFE DATA FOR HTML EMAIL
// ==========================================

$safe_name = htmlspecialchars(
    $name,
    ENT_QUOTES,
    'UTF-8'
);

$safe_email = htmlspecialchars(
    $email,
    ENT_QUOTES,
    'UTF-8'
);

$safe_phone = htmlspecialchars(
    $phone,
    ENT_QUOTES,
    'UTF-8'
);

$safe_services = htmlspecialchars(
    $services,
    ENT_QUOTES,
    'UTF-8'
);

$safe_message = nl2br(
    htmlspecialchars(
        $message,
        ENT_QUOTES,
        'UTF-8'
    )
);


// ==========================================
// ADMIN EMAIL TEMPLATE
// ==========================================

$email_message = '
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>New Contact Enquiry</title>

</head>


<body style="
    margin:0;
    padding:0;
    background:#f3f7f7;
    font-family:Arial, Helvetica, sans-serif;
">


<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        background:#f3f7f7;
        padding:30px 15px;
    "
>

<tr>

<td align="center">


<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        max-width:650px;
        background:#ffffff;
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 5px 20px rgba(0,0,0,0.08);
    "
>


<!-- HEADER -->

<tr>

<td style="
    background:#146368;
    padding:30px;
    text-align:center;
">

<h1 style="
    margin:0;
    color:#ffffff;
    font-size:26px;
    font-weight:600;
">

New Contact Enquiry

</h1>

</td>

</tr>


<!-- CONTENT -->

<tr>

<td style="padding:30px;">


<p style="
    margin-top:0;
    color:#555555;
    font-size:16px;
    line-height:1.7;
">

A new contact enquiry has been received from the website.

</p>


<table
    width="100%"
    cellpadding="12"
    cellspacing="0"
    border="0"
    style="
        border-collapse:collapse;
        margin-top:20px;
    "
>


<!-- NAME -->

<tr>

<td style="
    border:1px solid #dddddd;
    background:#f6fbfb;
    width:35%;
">

<strong>Name</strong>

</td>

<td style="
    border:1px solid #dddddd;
">

' . $safe_name . '

</td>

</tr>


<!-- EMAIL -->

<tr>

<td style="
    border:1px solid #dddddd;
    background:#f6fbfb;
">

<strong>Email</strong>

</td>

<td style="
    border:1px solid #dddddd;
">

' . $safe_email . '

</td>

</tr>


<!-- PHONE -->

<tr>

<td style="
    border:1px solid #dddddd;
    background:#f6fbfb;
">

<strong>Phone</strong>

</td>

<td style="
    border:1px solid #dddddd;
">

' . $safe_phone . '

</td>

</tr>


<!-- SERVICE -->

<tr>

<td style="
    border:1px solid #dddddd;
    background:#f6fbfb;
">

<strong>Service</strong>

</td>

<td style="
    border:1px solid #dddddd;
">

' . $safe_services . '

</td>

</tr>


<!-- MESSAGE -->

<tr>

<td style="
    border:1px solid #dddddd;
    background:#f6fbfb;
    vertical-align:top;
">

<strong>Message</strong>

</td>

<td style="
    border:1px solid #dddddd;
    line-height:1.7;
">

' . $safe_message . '

</td>

</tr>


</table>


<p style="
    margin-top:25px;
    color:#555555;
    font-size:15px;
    line-height:1.7;
">

Please contact the patient regarding this enquiry.

</p>


</td>

</tr>


<!-- FOOTER -->

<tr>

<td style="
    background:#f6fbfb;
    padding:18px;
    text-align:center;
    color:#777777;
    font-size:13px;
">

[Client] Website Contact Form

</td>

</tr>


</table>

</td>

</tr>

</table>


</body>

</html>
';


// ==========================================
// PHPMAILER
// ==========================================

$mail = new PHPMailer(true);


try {

    // ======================================
    // GMAIL SMTP
    // ======================================

    $mail->isSMTP();

    $mail->Host = 'smtp.gmail.com';

    $mail->SMTPAuth = true;


    // ======================================
    // GMAIL ACCOUNT
    // ======================================

    $mail->Username = '[client]@gmail.com'; //replace [client] with the valid mail ID.


    // ======================================
    // GOOGLE APP PASSWORD
    // ======================================
    //
    // IMPORTANT:
    // Normal Gmail password mat lagana.
    //
    // Google ka 16-character App Password
    // yahan lagana hai.
    //
    // Example:
    //
    // $mail->Password = 'abcdefghijklmnop';
    //
    // ======================================

    $mail->Password = ''; //add password


    // ======================================
    // SSL ENCRYPTION
    // ======================================

    $mail->SMTPSecure =
        PHPMailer::ENCRYPTION_SMTPS;

    $mail->Port = 465;


    // ======================================
    // CHARACTER SET
    // ======================================

    $mail->CharSet = 'UTF-8';


    // ======================================
    // TIMEOUT
    // ======================================

    $mail->Timeout = 30;


    // ======================================
    // FROM
    // ======================================

    $mail->setFrom(
        '[client]@gmail.com', //replace the [client] with the vaild mail id
        '[Client] Website' //replace with the name/title
    );


    // ======================================
    // ADMIN / RECEIVER
    // ======================================

    $mail->addAddress(
        '[client]@gmail.com', //replace the [client] with the vaild mail id
        '[Client]' //replace with the name/title
    );


    // ======================================
    // REPLY TO FORM USER
    // ======================================

    $mail->addReplyTo(
        $email,
        $name
    );


    // ======================================
    // HTML EMAIL
    // ======================================

    $mail->isHTML(true);


    // ======================================
    // SUBJECT
    // ======================================

    $mail->Subject =
        'New Contact Enquiry - ' . $name;


    // ======================================
    // EMAIL BODY
    // ======================================

    $mail->Body = $email_message;


    // ======================================
    // TEXT VERSION
    // ======================================

    $mail->AltBody =
        "New Contact Enquiry\n\n" .

        "Name: " .
        $name . "\n" .

        "Email: " .
        $email . "\n" .

        "Phone: " .
        $phone . "\n" .

        "Service: " .
        $services . "\n\n" .

        "Message:\n" .
        $message;


    // ======================================
    // SEND EMAIL
    // ======================================

    $mail->send();


    // ======================================
    // SUCCESS
    // ======================================

    header(
        "Location: contact.php?status=success"
    );

    exit;
} catch (Exception $e) {


    // ======================================
    // ERROR
    // ======================================

    // Debugging ke liye error URL me send
    // nahi kar rahe because SMTP details
    // expose ho sakti hain.

    header(
        "Location: contact.php?status=mail_error"
    );

    exit;
}
