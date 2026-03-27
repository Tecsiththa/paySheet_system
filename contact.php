<?php
require_once 'config/database.php';

// Handle form submission
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean($conn, $_POST['name']);
    $email = clean($conn, $_POST['email']);
    $phone = clean($conn, $_POST['phone']);
    $subject = clean($conn, $_POST['subject']);
    $message = clean($conn, $_POST['message']);
    
    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($subject)) $errors[] = "Subject is required";
    if (empty($message)) $errors[] = "Message is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    
    if (empty($errors)) {
        // Here you can save to database or send email
        // For now, we'll just show success message
        $success = true;
        
        // Optional: Save to database
        // $query = "INSERT INTO contact_messages (name, email, phone, subject, message, created_at) 
        //          VALUES ('$name', '$email', '$phone', '$subject', '$message', NOW())";
        // mysqli_query($conn, $query);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - PaySheetPro</title>
    <link rel="stylesheet" href="accests/css/style.css">
    <link rel="stylesheet" href="accests/css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .contact-hero {
            background: var(--gradient-primary);
            color: white;
            padding: 100px 20px;
            text-align: center;
        }

        .contact-hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .contact-hero p {
            font-size: 20px;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }

        .contact-container {
            max-width: 1200px;
            margin: -50px auto 80px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .contact-info {
            background: white;
            padding: 50px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
        }

        .contact-info h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .contact-info p {
            font-size: 16px;
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-icon {
            font-size: 32px;
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            border-radius: var(--border-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .info-content h3 {
            font-size: 18px;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .info-content p {
            font-size: 16px;
            color: var(--text-secondary);
            margin: 0;
        }

        .contact-form {
            background: white;
            padding: 50px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
        }

        .contact-form h2 {
            font-size: 32px;
            margin-bottom: 30px;
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-md);
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            transition: border-color var(--transition-normal);
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-textarea {
            resize: vertical;
            min-height: 150px;
        }

        .btn-submit {
            width: 100%;
            padding: 18px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--border-radius-md);
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform var(--transition-normal);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .alert {
            padding: 15px 20px;
            border-radius: var(--border-radius-md);
            margin-bottom: 25px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #059669;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #dc2626;
        }

        .map-section {
            max-width: 1200px;
            margin: 80px auto;
            padding: 0 20px;
        }

        .map-container {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
        }

        .map-container h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: var(--text-primary);
            text-align: center;
        }

        .map-placeholder {
            width: 100%;
            height: 400px;
            background: var(--gray-100);
            border-radius: var(--border-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
            }

            .contact-hero h1 {
                font-size: 32px;
            }

            .contact-info,
            .contact-form {
                padding: 30px;
            }
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <div class="contact-hero">
        <h1>Get In Touch</h1>
        <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
    </div>

    <!-- Contact Container -->
    <div class="contact-container">

        <!-- Contact Info -->
        <div class="contact-info">
            <h2>Contact Information</h2>
            <p>Feel free to reach out to us through any of these channels. Our team is here to help!</p>

            <div class="info-item">
                <div class="info-icon">📍</div>
                <div class="info-content">
                    <h3>Our Address</h3>
                    <p>No. 123, Main Street<br>Colombo 00700<br>Sri Lanka</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">📞</div>
                <div class="info-content">
                    <h3>Phone Number</h3>
                    <p>+94 11 234 5678<br>+94 77 123 4567</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">✉️</div>
                <div class="info-content">
                    <h3>Email Address</h3>
                    <p>info@paysheetpro.lk<br>support@paysheetpro.lk</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">🕒</div>
                <div class="info-content">
                    <h3>Working Hours</h3>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 9:00 AM - 1:00 PM<br>Sunday: Closed</p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form">
            <h2>Send Us a Message</h2>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✓ Thank you for contacting us! We'll get back to you soon.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name" class="form-label">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-input" 
                           placeholder="Enter your full name" required
                           value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="your@email.com" required
                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-input" 
                           placeholder="0XX XXX XXXX"
                           value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="subject" class="form-label">Subject *</label>
                    <input type="text" id="subject" name="subject" class="form-input" 
                           placeholder="What is this regarding?" required
                           value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="message" class="form-label">Message *</label>
                    <textarea id="message" name="message" class="form-textarea" 
                              placeholder="Tell us more about your inquiry..." required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn-submit">Send Message</button>
            </form>

            <p style="text-align: center; margin-top: 20px; color: var(--text-secondary); font-size: 14px;">
                <a href="index.php" style="color: var(--primary); text-decoration: none;">← Back to Home</a>
            </p>
        </div>

    </div>

   
</body>
</html>