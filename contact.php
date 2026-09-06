<?php
$pageTitle = "Contact Us - SNA Catalogue";
require_once 'includes/header.php';

$name = $email = $subject = $message = '';
$successMsg = $errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $errorMsg = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please enter a valid email address.";
    } else {
        // Basic mail example (configure as needed or log instead)
        $to      = "info@furniturecatalogue.com";
        $fullSub = $subject !== '' ? $subject : "New enquiry from website";
        $body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";

        // Uncomment and configure if mail() works on your server:
        // if (@mail($to, $fullSub, $body, "From: $email\r\n")) {
        //     $successMsg = "Thank you for contacting us. We will get back to you soon.";
        //     $name = $email = $subject = $message = '';
        // } else {
        //     $errorMsg = "Could not send email right now. Please try again later.";
        // }

        // For localhost/dev without mail:
        $successMsg = "Thank you for contacting us. (Mail not sent in dev, but form submission works.)";
        $name = $email = $subject = $message = '';
    }
}
?>

<!-- <section class="page-title-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>Contact Us</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                        <li class="breadcrumb-item active">Contact</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section> -->

<section class="contact-area py-5">
    <div class="container">
        <div class="row">
            <!-- Contact form -->
            <div class="col-md-7 mb-4 mb-md-0">
                <h3 class="mb-3">Get in touch</h3>

                <?php if ($successMsg): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
                <?php endif; ?>
                <?php if ($errorMsg): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars($name) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control"
                               value="<?= htmlspecialchars($subject) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message *</label>
                        <textarea name="message" rows="5" class="form-control" required><?= htmlspecialchars($message) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>

            <!-- Contact info -->
            <div class="col-md-5">
                <h3 class="mb-3">Contact Info</h3>
                <p>Have questions about our furniture catalogue or need a custom quote? Reach out to us using the details below.</p>
                <p>
                    <strong>Address:</strong><br>
                    Ground Floor, 53/1, Outer Ring Rd, opposite Vokkaligara Sangha High School, Kottigepalya, Bengaluru, Karnataka 560091
                </p>
                <p>
                    <strong>Phone:</strong><br>
                    +91 99008 25976
                </p>
                <h5 class="mt-4">Business hours</h5>
                <p>Mon – Sat: 10:00 AM – 7:00 PM<br>Sunday: Closed</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>