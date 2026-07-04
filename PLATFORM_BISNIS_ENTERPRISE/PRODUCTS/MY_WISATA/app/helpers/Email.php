<?php
/**
 * MyWisata Application - Email Helper
 * 
 * Handles email sending functionality.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Email {
    
    /**
     * Send email
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body
     * @param array $headers Optional headers
     * @return bool
     */
    public static function send($to, $subject, $body, $headers = []) {
        $defaultHeaders = [
            'From' => defined('EMAIL_FROM') ? EMAIL_FROM : 'noreply@mywisata.com',
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
        
        $headers = array_merge($defaultHeaders, $headers);
        
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= $key . ': ' . $value . "\r\n";
        }
        
        $result = mail($to, $subject, $body, $headerString);
        
        if ($result) {
            Logger::info('Email sent', ['to' => $to, 'subject' => $subject]);
        } else {
            Logger::error('Failed to send email', ['to' => $to, 'subject' => $subject]);
        }
        
        return $result;
    }
    
    /**
     * Send booking confirmation email
     * 
     * @param string $to Recipient email
     * @param array $bookingData Booking data
     * @return bool
     */
    public static function sendBookingConfirmation($to, $bookingData) {
        $subject = 'Konfirmasi Booking - MyWisata';
        $body = self::renderTemplate('booking_confirmation', $bookingData);
        return self::send($to, $subject, $body);
    }
    
    /**
     * Send password reset email
     * 
     * @param string $to Recipient email
     * @param string $resetLink Password reset link
     * @return bool
     */
    public static function sendPasswordReset($to, $resetLink) {
        $subject = 'Reset Password - MyWisata';
        $body = self::renderTemplate('password_reset', ['reset_link' => $resetLink]);
        return self::send($to, $subject, $body);
    }
    
    /**
     * Render email template
     * 
     * @param string $template Template name
     * @param array $data Template data
     * @return string
     */
    private static function renderTemplate($template, $data) {
        $templatePath = APP_ROOT . '/app/views/emails/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            return '<p>Email template not found.</p>';
        }
        
        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
