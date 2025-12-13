<?php
// Email configuration using PHPMailer with Gmail App Password

// Load PHPMailer from lib folder
require '../lib/PHPMailer.php';
require '../lib/SMTP.php';

function send_reset_email($to_email, $username, $reset_link) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'moemoepetmart99@gmail.com'; // ⭐ YOUR GMAIL ADDRESS HERE
        $mail->Password   = 'afbb fxmg ybnw tiao';     // ⭐ YOUR 16-CHAR APP PASSWORD HERE
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('moemoepetmart99@gmail.com', 'Moe Moe Pet Mart');
        $mail->addAddress($to_email, $username);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - Moe Moe Pet Mart';
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: 'Arial', sans-serif; background: #fff5f9; padding: 20px; }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: white; 
                    border-radius: 20px; 
                    padding: 40px; 
                    box-shadow: 0 10px 30px rgba(255,105,180,0.2);
                    border: 4px solid #ffeef8;
                }
                h1 { 
                    color: #ff1493; 
                    font-size: 2rem; 
                    text-align: center;
                    margin-bottom: 20px;
                }
                p { color: #555; line-height: 1.8; font-size: 1.1rem; }
                .btn { 
                    display: inline-block; 
                    background: linear-gradient(135deg, #ff69b4, #ff1493);
                    color: white; 
                    padding: 15px 40px; 
                    text-decoration: none; 
                    border-radius: 50px;
                    font-weight: bold;
                    margin: 20px 0;
                    box-shadow: 0 8px 20px rgba(255,105,180,0.4);
                }
                .footer { 
                    text-align: center; 
                    margin-top: 30px; 
                    padding-top: 20px; 
                    border-top: 2px dashed #ffeef8;
                    color: #ff69b4;
                    font-size: 0.9rem;
                }
                .warning {
                    background: #fff8e0;
                    border-left: 4px solid #ffa502;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 10px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>🐾 Password Reset Request ♡</h1>
                <p>Hello <strong>" . htmlspecialchars($username) . "</strong>,</p>
                <p>We received a request to reset your password for your Moe Moe Pet Mart account.</p>
                <p>Click the button below to reset your password:</p>
                <p style='text-align: center;'>
                    <a href='" . htmlspecialchars($reset_link) . "' class='btn'>Reset My Password ♡</a>
                </p>
                <div class='warning'>
                    <strong>⚠️ Important:</strong>
                    <ul>
                        <li>This link will expire in 1 hour</li>
                        <li>If you didn't request this, please ignore this email</li>
                        <li>Never share this link with anyone</li>
                    </ul>
                </div>
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #ff69b4;'>" . htmlspecialchars($reset_link) . "</p>
                <div class='footer'>
                    <p>With love from Moe Moe Pet Mart ♡</p>
                    <p>© " . date('Y') . " Moe Moe Pet Mart. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Hello $username,\n\nWe received a request to reset your password.\n\nClick this link to reset: $reset_link\n\nThis link expires in 1 hour.\n\nIf you didn't request this, please ignore this email.\n\nMoe Moe Pet Mart ♡";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}