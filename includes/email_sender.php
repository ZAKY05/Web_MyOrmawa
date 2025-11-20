<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function sendOTPEmail($to_email, $otp_code, $type = 'register') {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'myormawa@gmail.com';
        $mail->Password = 'dxcm ffwv qblm vsre';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Email settings
        $mail->setFrom('myormawa@gmail.com', 'MyOrmawa');
        $mail->addAddress($to_email);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        
        // Common CSS styles
        $common_styles = "
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Poppins', sans-serif;
                line-height: 1.6;
                color: #333333;
                background-color: #f5f5f5;
                -webkit-font-smoothing: antialiased;
            }
            
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
            }
            
            .email-header {
                background-color: #2C4EEF;
                padding: 40px 32px;
                text-align: center;
            }
            
            .logo-text {
                font-size: 32px;
                font-weight: 700;
                color: #ffffff;
                margin-bottom: 4px;
            }
            
            .subtitle {
                font-size: 14px;
                font-weight: 400;
                color: rgba(255, 255, 255, 0.95);
            }
            
            .email-body {
                padding: 40px 32px;
            }
            
            .title {
                font-size: 24px;
                font-weight: 600;
                color: #1a1a1a;
                margin-bottom: 16px;
            }
            
            .description {
                font-size: 15px;
                font-weight: 400;
                color: #666666;
                line-height: 1.7;
                margin-bottom: 32px;
            }
            
            .otp-box {
                background-color: #f8f9fa;
                border: 2px solid #2C4EEF;
                border-radius: 12px;
                padding: 32px 24px;
                text-align: center;
                margin: 32px 0;
            }
            
            .otp-label {
                font-size: 12px;
                font-weight: 600;
                color: #2C4EEF;
                text-transform: uppercase;
                letter-spacing: 1.5px;
                margin-bottom: 16px;
            }
            
            .otp-code {
                font-size: 40px;
                font-weight: 700;
                color: #2C4EEF;
                letter-spacing: 12px;
                font-family: 'Poppins', monospace;
                line-height: 1.2;
                word-spacing: 8px;
            }
            
            .info-box {
                background-color: #f8f9fa;
                border-left: 3px solid #2C4EEF;
                border-radius: 6px;
                padding: 20px 24px;
                margin: 32px 0;
            }
            
            .info-title {
                font-size: 13px;
                font-weight: 600;
                color: #2C4EEF;
                margin-bottom: 12px;
            }
            
            .info-list {
                margin: 0;
                padding-left: 18px;
                list-style-type: disc;
            }
            
            .info-list li {
                font-size: 13px;
                font-weight: 400;
                color: #666666;
                margin-bottom: 6px;
                line-height: 1.6;
            }
            
            .info-list li:last-child {
                margin-bottom: 0;
            }
            
            .info-list strong {
                font-weight: 600;
                color: #333333;
            }
            
            .divider {
                height: 1px;
                background-color: #e5e7eb;
                margin: 32px 0;
            }
            
            .footer-note {
                font-size: 13px;
                font-weight: 400;
                color: #999999;
                line-height: 1.6;
            }
            
            .email-footer {
                background-color: #fafafa;
                padding: 32px;
                text-align: center;
                border-top: 1px solid #e5e7eb;
            }
            
            .footer-brand {
                font-size: 15px;
                font-weight: 600;
                color: #333333;
                margin-bottom: 8px;
            }
            
            .footer-address {
                font-size: 12px;
                font-weight: 400;
                color: #666666;
                line-height: 1.6;
                margin-bottom: 16px;
            }
            
            .footer-copyright {
                font-size: 11px;
                font-weight: 400;
                color: #999999;
                padding-top: 16px;
                border-top: 1px solid #e5e7eb;
            }
        </style>
        ";
        
        // Email content based on type
        switch($type) {
            case 'register':
                $mail->Subject = 'MyOrmawa - Verifikasi Akun Anda';
                $mail->Body = "
                <!DOCTYPE html>
                <html lang='id'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
                    {$common_styles}
                </head>
                <body>
                    <div class='email-container'>
                        <div class='email-header'>
                            <div class='logo-text'>MyOrmawa</div>
                            <div class='subtitle'>Politeknik Negeri Jember</div>
                        </div>

                        <div class='email-body'>
                            <div class='title'>Verifikasi Akun Anda</div>
                            
                            <div class='description'>
                                Terima kasih telah mendaftar di MyOrmawa! Untuk menyelesaikan pendaftaran, silakan masukkan kode verifikasi berikut di aplikasi:
                            </div>

                            <div class='otp-box'>
                                <div class='otp-label'>Kode Verifikasi</div>
                                <div class='otp-code'>{$otp_code}</div>
                            </div>

                            <div class='info-box'>
                                <div class='info-title'>Informasi Penting</div>
                                <ul class='info-list'>
                                    <li>Kode ini akan kadaluarsa dalam <strong>10 menit</strong></li>
                                    <li>Jangan bagikan kode kepada siapapun</li>
                                    <li>Tim MyOrmawa tidak akan pernah meminta kode OTP Anda</li>
                                </ul>
                            </div>

                            <div class='divider'></div>

                            <div class='footer-note'>
                                Jika Anda tidak mendaftar di MyOrmawa, abaikan email ini.
                            </div>
                        </div>

                        <div class='email-footer'>
                            <div class='footer-brand'>MyOrmawa</div>
                            <div class='footer-address'>
                                Politeknik Negeri Jember<br>
                                Jl. Mastrip PO BOX 164, Jember 68121
                            </div>
                            <div class='footer-copyright'>
                                &copy; 2025 Politeknik Negeri Jember
                            </div>
                        </div>
                    </div>
                </body>
                </html>
                ";
                break;
                
            case 'forgot_password':
                $mail->Subject = 'MyOrmawa - Reset Password Anda';
                $mail->Body = "
                <!DOCTYPE html>
                <html lang='id'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
                    {$common_styles}
                </head>
                <body>
                    <div class='email-container'>
                        <div class='email-header'>
                            <div class='logo-text'>MyOrmawa</div>
                            <div class='subtitle'>Politeknik Negeri Jember</div>
                        </div>

                        <div class='email-body'>
                            <div class='title'>Reset Password</div>
                            
                            <div class='description'>
                                Kami menerima permintaan untuk mereset password akun Anda. Gunakan kode verifikasi berikut untuk melanjutkan:
                            </div>

                            <div class='otp-box'>
                                <div class='otp-label'>Kode Verifikasi</div>
                                <div class='otp-code'>{$otp_code}</div>
                            </div>

                            <div class='info-box'>
                                <div class='info-title'>Informasi Penting</div>
                                <ul class='info-list'>
                                    <li>Kode berlaku selama <strong>10 menit</strong></li>
                                    <li>Jangan bagikan kode kepada siapapun</li>
                                    <li>Jika bukan Anda, segera amankan akun</li>
                                </ul>
                            </div>

                            <div class='divider'></div>

                            <div class='footer-note'>
                                Jika Anda tidak meminta reset password, abaikan email ini.
                            </div>
                        </div>

                        <div class='email-footer'>
                            <div class='footer-brand'>MyOrmawa</div>
                            <div class='footer-address'>
                                Politeknik Negeri Jember<br>
                                Jl. Mastrip PO BOX 164, Jember 68121
                            </div>
                            <div class='footer-copyright'>
                                &copy; 2025 Politeknik Negeri Jember
                            </div>
                        </div>
                    </div>
                </body>
                </html>
                ";
                break;
                
            case 'change_email':
                $mail->Subject = 'MyOrmawa - Verifikasi Email Baru';
                $mail->Body = "
                <!DOCTYPE html>
                <html lang='id'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
                    {$common_styles}
                </head>
                <body>
                    <div class='email-container'>
                        <div class='email-header'>
                            <div class='logo-text'>MyOrmawa</div>
                            <div class='subtitle'>Politeknik Negeri Jember</div>
                        </div>

                        <div class='email-body'>
                            <div class='title'>Verifikasi Email Baru</div>
                            
                            <div class='description'>
                                Anda telah meminta untuk mengubah email akun MyOrmawa. Gunakan kode verifikasi berikut untuk mengkonfirmasi:
                            </div>

                            <div class='otp-box'>
                                <div class='otp-label'>Kode Verifikasi</div>
                                <div class='otp-code'>{$otp_code}</div>
                            </div>

                            <div class='info-box'>
                                <div class='info-title'>Informasi Penting</div>
                                <ul class='info-list'>
                                    <li>Kode berlaku selama <strong>10 menit</strong></li>
                                    <li>Email lama tidak dapat digunakan setelah verifikasi</li>
                                    <li>Pastikan Anda memiliki akses ke email baru</li>
                                </ul>
                            </div>

                            <div class='divider'></div>

                            <div class='footer-note'>
                                Jika Anda tidak melakukan permintaan ini, segera hubungi support.
                            </div>
                        </div>

                        <div class='email-footer'>
                            <div class='footer-brand'>MyOrmawa</div>
                            <div class='footer-address'>
                                Politeknik Negeri Jember<br>
                                Jl. Mastrip PO BOX 164, Jember 68121
                            </div>
                            <div class='footer-copyright'>
                                &copy; 2025 Politeknik Negeri Jember
                            </div>
                        </div>
                    </div>
                </body>
                </html>
                ";
                break;
        }
        
        $mail->send();
        
        // Log untuk debugging
        $log_file = __DIR__ . '/../logs/email_log.txt';
        $log_dir = dirname($log_file);
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        
        $log_message = "\n" . str_repeat("=", 50) . "\n";
        $log_message .= date('Y-m-d H:i:s') . "\n";
        $log_message .= "To: $to_email\n";
        $log_message .= "Type: $type\n";
        $log_message .= "OTP Code: $otp_code\n";
        $log_message .= "Status: EMAIL SENT ✓\n";
        $log_message .= str_repeat("=", 50) . "\n";
        
        file_put_contents($log_file, $log_message, FILE_APPEND);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Email error: {$mail->ErrorInfo}");
        
        // Log error
        $log_file = __DIR__ . '/../logs/email_log.txt';
        $log_message = "\n[ERROR] " . date('Y-m-d H:i:s') . "\n";
        $log_message .= "To: $to_email\n";
        $log_message .= "Error: {$mail->ErrorInfo}\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
        
        return false;
    }
}
?>