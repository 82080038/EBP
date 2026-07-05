<?php

/**
 * EBP Core - Request Validation Middleware
 * 
 * This middleware validates incoming request data
 * 
 * @package EBP\Core\Middleware
 * @version 1.0.0
 */

class ValidationMiddleware
{
    private $errors = [];

    /**
     * Validate request data against rules
     * 
     * @param array $data Request data to validate
     * @param array $rules Validation rules
     * @return bool True if validation passes
     */
    public function validate($data, $rules)
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $ruleArray = explode('|', $fieldRules);
            
            foreach ($ruleArray as $rule) {
                $this->applyRule($field, $data[$field] ?? null, $rule, $data);
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply a single validation rule
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $rule Rule to apply
     * @param array $data Full data array
     * @return void
     */
    private function applyRule($field, $value, $rule, $data)
    {
        // Parse rule with parameters (e.g., "min:5")
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $ruleParam = $ruleParts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->errors[$field][] = "Field '{$field}' is required";
                }
                break;

            case 'email':
                if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "Field '{$field}' must be a valid email";
                }
                break;

            case 'numeric':
                if ($value !== null && !is_numeric($value)) {
                    $this->errors[$field][] = "Field '{$field}' must be numeric";
                }
                break;

            case 'integer':
                if ($value !== null && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->errors[$field][] = "Field '{$field}' must be an integer";
                }
                break;

            case 'min':
                if ($value !== null && strlen($value) < $ruleParam) {
                    $this->errors[$field][] = "Field '{$field}' must be at least {$ruleParam} characters";
                }
                break;

            case 'max':
                if ($value !== null && strlen($value) > $ruleParam) {
                    $this->errors[$field][] = "Field '{$field}' must not exceed {$ruleParam} characters";
                }
                break;

            case 'min_num':
                if ($value !== null && is_numeric($value) && $value < $ruleParam) {
                    $this->errors[$field][] = "Field '{$field}' must be at least {$ruleParam}";
                }
                break;

            case 'max_num':
                if ($value !== null && is_numeric($value) && $value > $ruleParam) {
                    $this->errors[$field][] = "Field '{$field}' must not exceed {$ruleParam}";
                }
                break;

            case 'in':
                $allowedValues = explode(',', $ruleParam);
                if ($value !== null && !in_array($value, $allowedValues)) {
                    $this->errors[$field][] = "Field '{$field}' must be one of: " . implode(', ', $allowedValues);
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (isset($data[$confirmField]) && $value !== $data[$confirmField]) {
                    $this->errors[$field][] = "Field '{$field}' confirmation does not match";
                }
                break;

            case 'url':
                if ($value !== null && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field][] = "Field '{$field}' must be a valid URL";
                }
                break;

            case 'date':
                if ($value !== null && !strtotime($value)) {
                    $this->errors[$field][] = "Field '{$field}' must be a valid date";
                }
                break;

            case 'array':
                if ($value !== null && !is_array($value)) {
                    $this->errors[$field][] = "Field '{$field}' must be an array";
                }
                break;
        }
    }

    /**
     * Get validation errors
     * 
     * @return array Validation errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get first error message
     * 
     * @return string First error message
     */
    public function getFirstError()
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }
        return null;
    }

    /**
     * Static handle method for middleware chain
     * 
     * @param array $request Request data
     * @param array $rules Validation rules
     * @return array Request data if validation passes
     */
    public static function handle($request, $rules)
    {
        $middleware = new self();
        
        if (!$middleware->validate($request['body'] ?? [], $rules)) {
            Response::validationError($middleware->getErrors());
        }
        
        return $request;
    }

    /**
     * Validate specific field
     * 
     * @param mixed $value Value to validate
     * @param string $rule Rule to apply
     * @param mixed $param Rule parameter
     * @return bool True if valid
     */
    public static function validateField($value, $rule, $param = null)
    {
        $middleware = new self();
        $field = 'field';
        $data = [$field => $value];
        $ruleString = $param ? "{$rule}:{$param}" : $rule;
        
        $middleware->applyRule($field, $value, $ruleString, $data);
        
        return empty($middleware->getErrors());
    }
}
