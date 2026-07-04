<?php
namespace Kewer\Validation;

class Validator {
    private $data;
    private $rules;
    private $errors = [];
    
    public function __construct(array $data, array $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }
    
    /**
     * Validate data against rules
     */
    public function validate() {
        $this->errors = [];
        
        foreach ($this->rules as $field => $ruleSet) {
            $rules = explode('|', $ruleSet);
            $value = $this->data[$field] ?? null;
            
            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Apply individual validation rule
     */
    private function applyRule($field, $value, $rule) {
        // Parse rule with parameters
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $params = array_slice($parts, 1);
        
        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = "{$field} is required";
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "{$field} must be a valid email";
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < $params[0]) {
                    $this->errors[$field][] = "{$field} must be at least {$params[0]} characters";
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > $params[0]) {
                    $this->errors[$field][] = "{$field} must not exceed {$params[0]} characters";
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = "{$field} must be numeric";
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->errors[$field][] = "{$field} must be an integer";
                }
                break;
                
            case 'min_value':
                if (!empty($value) && is_numeric($value) && $value < $params[0]) {
                    $this->errors[$field][] = "{$field} must be at least {$params[0]}";
                }
                break;
                
            case 'max_value':
                if (!empty($value) && is_numeric($value) && $value > $params[0]) {
                    $this->errors[$field][] = "{$field} must not exceed {$params[0]}";
                }
                break;
                
            case 'in':
                $allowed = explode(',', $params[0]);
                if (!empty($value) && !in_array($value, $allowed)) {
                    $this->errors[$field][] = "{$field} must be one of: {$params[0]}";
                }
                break;
                
            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    $this->errors[$field][] = "{$field} must be a valid date";
                }
                break;
                
            case 'phone':
                if (!empty($value) && !preg_match('/^[0-9+\-\s()]+$/', $value)) {
                    $this->errors[$field][] = "{$field} must be a valid phone number";
                }
                break;
                
            case 'ktp':
                if (!empty($value) && !preg_match('/^[0-9]{16}$/', $value)) {
                    $this->errors[$field][] = "{$field} must be a valid 16-digit KTP number";
                }
                break;
                
            case 'unique':
                $this->validateUnique($field, $value, $params);
                break;
                
            case 'confirmed':
                $confirmField = $params[0] ?? $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->errors[$field][] = "{$field} confirmation does not match";
                }
                break;
        }
    }
    
    /**
     * Validate unique field in database
     */
    private function validateUnique($field, $value, $params) {
        if (empty($value)) {
            return;
        }
        
        $table = $params[0] ?? null;
        $ignoreId = $params[1] ?? null;
        
        if (!$table) {
            return;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$field} = ?";
        $paramsQuery = [$value];
        
        if ($ignoreId) {
            $sql .= " AND id != ?";
            $paramsQuery[] = $ignoreId;
        }
        
        $result = query($sql, $paramsQuery);
        
        if ($result[0]['count'] > 0) {
            $this->errors[$field][] = "{$field} already exists";
        }
    }
    
    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get first error message
     */
    public function getFirstError() {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }
        return null;
    }
    
    /**
     * Get errors as associative array
     */
    public function getErrorsArray() {
        $errors = [];
        foreach ($this->errors as $field => $fieldErrors) {
            $errors[$field] = $fieldErrors[0];
        }
        return $errors;
    }
}

/**
 * Helper function for quick validation
 */
function validateRequest(array $data, array $rules) {
    $validator = new Validator($data, $rules);
    
    if (!$validator->validate()) {
        return [
            'valid' => false,
            'errors' => $validator->getErrorsArray()
        ];
    }
    
    return ['valid' => true];
}
?>
