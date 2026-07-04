<?php
/**
 * MyWisata Application - Validator Class
 * 
 * Handles input validation with various validation rules.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class Validator {
    private $data;
    private $errors = [];
    
    /**
     * Constructor
     * 
     * @param array $data Data to validate
     */
    public function __construct($data) {
        $this->data = $data;
    }
    
    /**
     * Validate required fields
     * 
     * @param array $fields Fields to check
     * @return self
     */
    public function required($fields) {
        foreach ($fields as $field) {
            if (empty($this->data[$field]) && $this->data[$field] !== '0') {
                $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' wajib diisi';
            }
        }
        return $this;
    }
    
    /**
     * Validate email format
     * 
     * @param string $field Field name
     * @return self
     */
    public function email($field) {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Format email tidak valid';
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     * 
     * @param string $field Field name
     * @param int $length Minimum length
     * @return self
     */
    public function min($field, $length) {
        if (!empty($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = 'Minimal ' . $length . ' karakter';
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     * 
     * @param string $field Field name
     * @param int $length Maximum length
     * @return self
     */
    public function max($field, $length) {
        if (!empty($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = 'Maksimal ' . $length . ' karakter';
        }
        return $this;
    }
    
    /**
     * Validate numeric value
     * 
     * @param string $field Field name
     * @return self
     */
    public function numeric($field) {
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = 'Harus berupa angka';
        }
        return $this;
    }
    
    /**
     * Validate integer value
     * 
     * @param string $field Field name
     * @return self
     */
    public function integer($field) {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
            $this->errors[$field] = 'Harus berupa bilangan bulat';
        }
        return $this;
    }
    
    /**
     * Validate match with another field
     * 
     * @param string $field Field name
     * @param string $matchField Field to match
     * @return self
     */
    public function match($field, $matchField) {
        if (!empty($this->data[$field]) && $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field] = ucfirst(str_replace('_', ' ', $matchField)) . ' tidak cocok';
        }
        return $this;
    }
    
    /**
     * Validate phone number format
     * 
     * @param string $field Field name
     * @return self
     */
    public function phone($field) {
        if (!empty($this->data[$field]) && !preg_match('/^[0-9\+\-\(\)\s]+$/', $this->data[$field])) {
            $this->errors[$field] = 'Format nomor telepon tidak valid';
        }
        return $this;
    }
    
    /**
     * Validate URL format
     * 
     * @param string $field Field name
     * @return self
     */
    public function url($field) {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->errors[$field] = 'Format URL tidak valid';
        }
        return $this;
    }
    
    /**
     * Validate date format
     * 
     * @param string $field Field name
     * @param string $format Date format (default: Y-m-d)
     * @return self
     */
    public function date($field, $format = 'Y-m-d') {
        if (!empty($this->data[$field])) {
            $date = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field] = 'Format tanggal tidak valid (gunakan: ' . $format . ')';
            }
        }
        return $this;
    }
    
    /**
     * Validate value is in allowed values
     * 
     * @param string $field Field name
     * @param array $allowed Allowed values
     * @return self
     */
    public function in($field, $allowed) {
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $allowed)) {
            $this->errors[$field] = 'Nilai tidak valid';
        }
        return $this;
    }
    
    /**
     * Check if validation failed
     * 
     * @return bool
     */
    public function fails() {
        return !empty($this->errors);
    }
    
    /**
     * Check if validation passed
     * 
     * @return bool
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Get all errors
     * 
     * @return array
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Get first error
     * 
     * @return string|null
     */
    public function firstError() {
        return reset($this->errors) ?: null;
    }
    
    /**
     * Get error for specific field
     * 
     * @param string $field Field name
     * @return string|null
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }
}
