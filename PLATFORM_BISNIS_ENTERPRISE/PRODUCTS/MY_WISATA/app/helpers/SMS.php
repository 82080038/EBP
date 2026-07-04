<?php
/**
 * MyWisata Application - SMS Helper
 * 
 * Handles SMS sending functionality.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class SMS {
    
    /**
     * Send SMS
     * 
     * @param string $to Recipient phone number
     * @param string $message SMS message
     * @return bool
     */
    public static function send($to, $message) {
        // Placeholder for SMS integration
        // In production, integrate with SMS gateway like Twilio, Nexmo, etc.
        
        Logger::info('SMS sent (simulated)', ['to' => $to, 'message_length' => strlen($message)]);
        
        return true;
    }
    
    /**
     * Send booking confirmation SMS
     * 
     * @param string $to Recipient phone number
     * @param string $bookingCode Booking code
     * @return bool
     */
    public static function sendBookingConfirmation($to, $bookingCode) {
        $message = "Booking Anda dengan kode {$bookingCode} telah dikonfirmasi. Terima kasih telah menggunakan MyWisata.";
        return self::send($to, $message);
    }
    
    /**
     * Send OTP
     * 
     * @param string $to Recipient phone number
     * @param string $otp OTP code
     * @return bool
     */
    public static function sendOTP($to, $otp) {
        $message = "Kode OTP Anda adalah: {$otp}. Jangan bagikan kode ini kepada siapapun.";
        return self::send($to, $message);
    }
}
