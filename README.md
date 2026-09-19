# PHP Contact Form with PHPMailer & MySQL

A simple and reusable **PHP Contact Form** that stores contact enquiries
in a **MySQL database** and sends enquiry details to the administrator
by email using **PHPMailer and Gmail SMTP**.

This project can be integrated into business websites, clinic websites,
portfolio websites, service websites, landing pages, and other PHP-based
projects.

------------------------------------------------------------------------

## Features

-   Contact form submission using PHP
-   MySQL database storage
-   PHPMailer SMTP email integration
-   Gmail SMTP support
-   Google App Password authentication
-   Prepared statements for database insertion
-   Server-side email validation
-   Required-field validation
-   HTML email template
-   Plain-text email fallback
-   Reply-To configured with the visitor's email
-   Success and error redirects
-   UTF-8 email support
-   Easy to customize for another website/client

------------------------------------------------------------------------

## Project Structure

``` text
your-project/
├── contact.php
├── contact_submit.php
├── include/
│   └── connection.php
└── PHPMailer/
    └── src/
        ├── Exception.php
        ├── PHPMailer.php
        └── SMTP.php
```

The three PHPMailer files must be placed inside `PHPMailer/src/`.

------------------------------------------------------------------------

## How It Works

``` text
Visitor
   ↓
contact.php
   ↓
HTML Contact Form
   ↓
POST Request
   ↓
contact_submit.php
   ↓
Validate Form Data
   ↓
Save Enquiry in MySQL
   ↓
Send Email using PHPMailer
   ↓
Redirect Back to contact.php
```

------------------------------------------------------------------------

## 1. Configure `contact.php`

The contact form must submit data to `contact_submit.php` using the POST
method:

``` html
<form action="contact_submit.php" method="POST">
```

Example:

``` html
<form action="contact_submit.php" method="POST">

    <input type="text" name="name" placeholder="Your Name" required>

    <input type="email" name="email" placeholder="Your Email" required>

    <input type="tel" name="phone" placeholder="Phone Number" required>

    <select name="services" required>
        <option value="">Select Service</option>
        <option value="Service 1">Service 1</option>
        <option value="Service 2">Service 2</option>
        <option value="Service 3">Service 3</option>
    </select>

    <textarea name="message" placeholder="Your Message"></textarea>

    <button type="submit">Submit</button>

</form>
```

### Required Form Field Names

The current `contact_submit.php` expects:

``` text
name
email
phone
services
message
```

If you rename a field in `contact.php`, update the corresponding
`$_POST` variable in `contact_submit.php`.

------------------------------------------------------------------------

## 2. Database Configuration

The project expects the database connection file at:

``` text
include/connection.php
```

Example:

``` php
<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "your_database_name";

$con = mysqli_connect($host, $username, $password, $database);

if (!$con) {
    die("Database connection failed.");
}
```

Replace the database credentials with the values for your local
environment or hosting provider.

------------------------------------------------------------------------

## 3. Create the Contact Table

The handler stores enquiries in a table named `contacts`.

``` sql
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    services VARCHAR(255) NOT NULL,
    message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

The current handler expects these columns:

``` text
name
email
phone
services
message
```

If your schema is different, update the INSERT query in
`contact_submit.php`.

------------------------------------------------------------------------

## 4. PHPMailer Setup

Keep the PHPMailer source files here:

``` text
PHPMailer/
└── src/
    ├── Exception.php
    ├── PHPMailer.php
    └── SMTP.php
```

The project loads them with:

``` php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
```

If you move the PHPMailer directory, update these paths.

------------------------------------------------------------------------

## 5. Gmail SMTP Configuration

Open `contact_submit.php` and configure the SMTP section.

``` php
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;

$mail->Username = 'your-email@gmail.com';
$mail->Password = 'YOUR_GOOGLE_APP_PASSWORD';

$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port = 465;
$mail->CharSet = 'UTF-8';
$mail->Timeout = 30;
```

### Google App Password

Do **not** use your normal Gmail password.

Use a Google App Password:

1.  Sign in to your Google account.
2.  Enable **2-Step Verification**.
3.  Open the **App Passwords** section.
4.  Generate an App Password.
5.  Copy the generated password.
6.  Add it to `$mail->Password`.

``` php
$mail->Password = 'YOUR_GOOGLE_APP_PASSWORD';
```

------------------------------------------------------------------------

## 6. Sender Configuration

Change:

``` php
$mail->setFrom(
    'your-email@gmail.com',
    'Your Website Name'
);
```

For Gmail SMTP, the sender email should normally match the authenticated
Gmail account.

------------------------------------------------------------------------

## 7. Receiver Email

Configure the email address that should receive contact enquiries:

``` php
$mail->addAddress(
    'admin@example.com',
    'Website Admin'
);
```

------------------------------------------------------------------------

## 8. Reply-To

The project uses:

``` php
$mail->addReplyTo($email, $name);
```

This allows the administrator to click **Reply** and respond directly to
the visitor who submitted the form.

------------------------------------------------------------------------

## 9. Email Subject

The current subject format is:

``` php
$mail->Subject = 'New Contact Enquiry - ' . $name;
```

You can customize it, for example:

``` php
$mail->Subject = 'New Website Lead - ' . $name;
```

------------------------------------------------------------------------

## 10. Success and Error Redirects

The handler redirects back to `contact.php` with one of these statuses:

``` text
contact.php?status=success
contact.php?status=required
contact.php?status=invalid_email
contact.php?status=database_error
contact.php?status=mail_error
```

Example status handling:

``` php
<?php

if (isset($_GET['status'])) {

    if ($_GET['status'] === 'success') {
        echo '<p>Thank you! Your enquiry has been submitted successfully.</p>';
    }

    if ($_GET['status'] === 'required') {
        echo '<p>Please fill in all required fields.</p>';
    }

    if ($_GET['status'] === 'invalid_email') {
        echo '<p>Please enter a valid email address.</p>';
    }

    if ($_GET['status'] === 'database_error') {
        echo '<p>Unable to save your enquiry. Please try again.</p>';
    }

    if ($_GET['status'] === 'mail_error') {
        echo '<p>Your enquiry was saved, but the notification email could not be sent.</p>';
    }
}

?>
```

You can replace these messages with Bootstrap alerts, SweetAlert, toast
notifications, or your own UI.

------------------------------------------------------------------------

## 11. What You Must Change

  -----------------------------------------------------------------------
  Setting                 File                    What to Change
  ----------------------- ----------------------- -----------------------
  Database host           `connection.php`        Your DB host

  Database username       `connection.php`        Your DB username

  Database password       `connection.php`        Your DB password

  Database name           `connection.php`        Your database

  Table/columns           `contact_submit.php`    Change if your schema
                                                  differs

  Gmail username          `contact_submit.php`    Your Gmail address

  App Password            `contact_submit.php`    Google App Password

  Sender email            `contact_submit.php`    Sending email

  Sender name             `contact_submit.php`    Website/company name

  Receiver email          `contact_submit.php`    Admin email

  Receiver name           `contact_submit.php`    Admin/company name

  Email subject           `contact_submit.php`    Optional

  Email template          `contact_submit.php`    Optional branding

  `[Client]` placeholders `contact_submit.php`    Your company/client
                                                  name

  Form fields             `contact.php`           Your website form

  Form action             `contact.php`           `contact_submit.php`
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## 12. Testing on XAMPP

Place the project inside:

``` text
C:/xampp/htdocs/
```

Example:

``` text
C:/xampp/htdocs/contact-form/
```

Start **Apache** and **MySQL** from the XAMPP Control Panel, then open
the project through localhost.

### Before Testing

Make sure:

``` text
✓ Apache is running
✓ MySQL is running
✓ Database exists
✓ contacts table exists
✓ connection.php credentials are correct
✓ contact.php exists
✓ Form action points to contact_submit.php
✓ Form uses POST
✓ PHPMailer/src/ exists
✓ Exception.php exists
✓ PHPMailer.php exists
✓ SMTP.php exists
✓ Gmail address is configured
✓ Google App Password is configured
✓ Receiver email is configured
```

------------------------------------------------------------------------

## 13. Common Errors

### PHPMailer File Not Found

If you see an error similar to:

``` text
Failed opening required 'PHPMailer/src/Exception.php'
```

verify this structure:

``` text
PHPMailer/
└── src/
    ├── Exception.php
    ├── PHPMailer.php
    └── SMTP.php
```

### SMTP Authentication Failed

Check:

``` php
$mail->Username
$mail->Password
```

Use a valid Google App Password instead of your normal Gmail password.

### Database Error

Verify:

``` text
Database name
Database username
Database password
Table name
Column names
```

### Form Opens but Nothing Happens

Check:

``` html
<form action="contact_submit.php" method="POST">
```

Also verify the required input `name` attributes.

### Enquiry Saves but Email Does Not Send

If the database entry is created but the page returns
`status=mail_error`, check:

``` text
Gmail username
Google App Password
SMTP host
SMTP port
SMTPSecure setting
Hosting SMTP restrictions
```

------------------------------------------------------------------------

## 14. Production Recommendations

For production websites, consider adding:

-   CSRF protection
-   CAPTCHA or Cloudflare Turnstile
-   Server-side phone validation
-   Rate limiting
-   Spam protection
-   Environment variables for credentials
-   Error logging
-   HTTPS
-   Better user-facing success/error notifications

Avoid displaying raw SMTP, database, or PHP errors to visitors.

------------------------------------------------------------------------

## Security Warning

Never upload real credentials to a public GitHub repository.

Do not commit:

``` text
Gmail passwords
Google App Passwords
Database passwords
API keys
Private SMTP credentials
```

Use placeholders such as:

``` php
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'YOUR_GOOGLE_APP_PASSWORD';
```

For production, environment variables or another secure configuration
mechanism are recommended.

------------------------------------------------------------------------

## PHPMailer Files

The included PHPMailer source files provide the email and SMTP
functionality used by `contact_submit.php`.

Normally, you should not modify:

``` text
PHPMailer.php
SMTP.php
Exception.php
```

Customize your own files instead:

``` text
contact.php
contact_submit.php
include/connection.php
```

------------------------------------------------------------------------

## Technologies Used

-   PHP
-   MySQL / MySQLi
-   PHPMailer
-   Gmail SMTP
-   HTML
-   SMTP / SMTPS

------------------------------------------------------------------------

## Final Setup Checklist

``` text
your-project/
├── contact.php
│   └── Form action → contact_submit.php
├── contact_submit.php
│   ├── Validates form
│   ├── Saves enquiry
│   ├── Loads PHPMailer
│   ├── Sends email
│   └── Redirects back to contact.php
├── include/
│   └── connection.php
└── PHPMailer/
    └── src/
        ├── Exception.php
        ├── PHPMailer.php
        └── SMTP.php
```

Once the database credentials, Gmail SMTP account, Google App Password,
receiver email, and contact form are configured, the contact form is
ready for testing.

------------------------------------------------------------------------

## License / Third-Party Library

This project uses **PHPMailer** as its email-sending library.

PHPMailer remains subject to its own license and copyright terms. Do not
remove or misrepresent the original PHPMailer licensing information from
the library source files.

------------------------------------------------------------------------

## Note

This repository provides the backend contact-form implementation and
PHPMailer integration. Your frontend `contact.php` can use any design as
long as its form fields and action remain compatible with
`contact_submit.php`.
