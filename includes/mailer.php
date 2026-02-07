<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

/**
 * Mailer configuration
 * 
 * IMPORTANT:
 * - These settings are for SMTP email sending (e.g. Gmail).
 * - Make sure you are using a valid SMTP host, username, and **App Password** (for Gmail).
 * - If you change any of these values, you do NOT need to touch the rest of the code.
 */
if (!defined('SMTP_ENABLED')) {
    // Enable SMTP so verification and order emails are actually sent.
    define('SMTP_ENABLED', true);
}
if (!defined('SMTP_HOST')) {
    // For Gmail: use smtp.gmail.com
    define('SMTP_HOST', 'smtp.gmail.com');
}
if (!defined('SMTP_PORT')) {
    // For Gmail with STARTTLS use 587
    define('SMTP_PORT', 587);
}
if (!defined('SMTP_USER')) {
    // Your SMTP username / email address
    define('SMTP_USER', 's2peed2@gmail.com');
}
if (!defined('SMTP_PASS')) {
    // Your SMTP password or Gmail App Password
    define('SMTP_PASS', 'vuse nfsb zbia qauk');
}
if (!defined('SMTP_FROM_EMAIL')) {
    define('SMTP_FROM_EMAIL', SMTP_USER);
}
if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', 'Danicop Hardware');
}

/**
 * Get a configured PHPMailer instance.
 */
function create_app_mailer(): ?PHPMailer
{
    if (!SMTP_ENABLED) {
        return null;
    }

    $mail = new PHPMailer(true);
    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    // Sender
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->isHTML(true);

    return $mail;
}

/**
 * Send an email.
 *
 * @param array|string $to Array of ['email' => 'Name'] or a single email string
 * @param string $subject
 * @param string $htmlBody
 * @param string|null $textBody
 * @return bool
 */
function send_app_email($to, string $subject, string $htmlBody, ?string $textBody = null): bool
{
    $mailer = create_app_mailer();
    if (!$mailer) {
        // Email disabled; fail silently
        return false;
    }

    try {
        if (is_array($to)) {
            foreach ($to as $email => $name) {
                if (is_int($email)) {
                    $mailer->addAddress($name);
                } else {
                    $mailer->addAddress($email, $name);
                }
            }
        } else {
            $mailer->addAddress($to);
        }

        $mailer->Subject = $subject;
        $mailer->Body    = $htmlBody;
        $mailer->AltBody = $textBody ?: strip_tags($htmlBody);

        $mailer->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Helper: format order items as HTML list.
 *
 * @param array $items
 * @return string
 */
function render_order_items_html(array $items): string
{
    if (empty($items)) {
        return '<p>No items found.</p>';
    }

    $rows = '';
    foreach ($items as $item) {
        $name = htmlspecialchars($item['name']);
        $qty  = (int)$item['quantity'];
        $price = number_format($item['price'], 2);
        $subtotal = number_format($item['price'] * $item['quantity'], 2);
        $rows .= "<tr>
            <td style=\"padding:6px 8px;border:1px solid #e5e7eb;\">{$name}</td>
            <td style=\"padding:6px 8px;border:1px solid #e5e7eb;text-align:center;\">{$qty}</td>
            <td style=\"padding:6px 8px;border:1px solid #e5e7eb;text-align:right;\">₱{$price}</td>
            <td style=\"padding:6px 8px;border:1px solid #e5e7eb;text-align:right;\">₱{$subtotal}</td>
        </tr>";
    }

    return '<table cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:100%;font-size:14px;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="padding:6px 8px;border:1px solid #e5e7eb;text-align:left;">Product</th>
                <th style="padding:6px 8px;border:1px solid #e5e7eb;text-align:center;">Qty</th>
                <th style="padding:6px 8px;border:1px solid #e5e7eb;text-align:right;">Price</th>
                <th style="padding:6px 8px;border:1px solid #e5e7eb;text-align:right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>' . $rows . '</tbody>
    </table>';
}


