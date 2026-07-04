<?php
/**
 * Input Validation Helper
 * 
 * Provides comprehensive input validation and sanitization functions
 * Prevents XSS, SQL injection, and validates common data types
 */

// Sanitize input (prevent XSS)
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate required field
function required($value) {
    return !empty(trim($value));
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate numeric
function validateNumeric($value) {
    return is_numeric($value);
}

// Validate integer
function validateInteger($value) {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

// Validate float
function validateFloat($value) {
    return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
}

// Validate phone number (Indonesia format)
function validatePhone($phone) {
    // Remove non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Validate Indonesian phone format (08xxxxxxxx or 628xxxxxxxx)
    return preg_match('/^(08[0-9]{9,12}|628[0-9]{9,12})$/', $phone);
}

// Validate KTP (16 digits)
function validateKTP($ktp) {
    $ktp = preg_replace('/[^0-9]/', '', $ktp);
    return preg_match('/^[0-9]{16}$/', $ktp);
}

// Validate date
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Validate future date
function validateFutureDate($date, $format = 'Y-m-d') {
    if (!validateDate($date, $format)) {
        return false;
    }
    return strtotime($date) > strtotime(date($format));
}

// Validate past date
function validatePastDate($date, $format = 'Y-m-d') {
    if (!validateDate($date, $format)) {
        return false;
    }
    return strtotime($date) < strtotime(date($format));
}

// Validate min length
function validateMinLength($value, $min) {
    return strlen(trim($value)) >= $min;
}

// Validate max length
function validateMaxLength($value, $max) {
    return strlen(trim($value)) <= $max;
}

// Validate between
function validateBetween($value, $min, $max) {
    $num = floatval($value);
    return $num >= $min && $num <= $max;
}

// Validate enum values
function validateEnum($value, $allowed) {
    return in_array($value, $allowed, true);
}

// Validate file upload
function validateFile($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'], $maxSize = 2097152) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    return true;
}

// Comprehensive validation function
function validate($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? null;
        $ruleArray = explode('|', $fieldRules);
        
        foreach ($ruleArray as $rule) {
            // Handle rules with parameters (e.g., min:3, max:100)
            if (strpos($rule, ':') !== false) {
                list($ruleName, $param) = explode(':', $rule, 2);
            } else {
                $ruleName = $rule;
                $param = null;
            }
            
            switch ($ruleName) {
                case 'required':
                    if (!required($value)) {
                        $errors[$field][] = "Field $field is required";
                    }
                    break;
                    
                case 'email':
                    if ($value && !validateEmail($value)) {
                        $errors[$field][] = "Field $field must be a valid email";
                    }
                    break;
                    
                case 'numeric':
                    if ($value && !validateNumeric($value)) {
                        $errors[$field][] = "Field $field must be numeric";
                    }
                    break;
                    
                case 'integer':
                    if ($value && !validateInteger($value)) {
                        $errors[$field][] = "Field $field must be an integer";
                    }
                    break;
                    
                case 'phone':
                    if ($value && !validatePhone($value)) {
                        $errors[$field][] = "Field $field must be a valid phone number";
                    }
                    break;
                    
                case 'ktp':
                    if ($value && !validateKTP($value)) {
                        $errors[$field][] = "Field $field must be a valid 16-digit KTP number";
                    }
                    break;
                    
                case 'date':
                    if ($value && !validateDate($value)) {
                        $errors[$field][] = "Field $field must be a valid date";
                    }
                    break;
                    
                case 'min':
                    if ($value && !validateMinLength($value, $param)) {
                        $errors[$field][] = "Field $field must be at least $param characters";
                    }
                    break;
                    
                case 'max':
                    if ($value && !validateMaxLength($value, $param)) {
                        $errors[$field][] = "Field $field must not exceed $param characters";
                    }
                    break;
                    
                case 'between':
                    list($min, $max) = explode(',', $param);
                    if ($value && !validateBetween($value, $min, $max)) {
                        $errors[$field][] = "Field $field must be between $min and $max";
                    }
                    break;
                    
                case 'in':
                    $allowed = explode(',', $param);
                    if ($value && !validateEnum($value, $allowed)) {
                        $errors[$field][] = "Field $field must be one of: " . implode(', ', $allowed);
                    }
                    break;
            }
        }
    }
    
    return $errors;
}

// Get validation error message
function getValidationErrors($errors) {
    $messages = [];
    foreach ($errors as $field => $fieldErrors) {
        $messages = array_merge($messages, $fieldErrors);
    }
    return $messages;
}
?>
