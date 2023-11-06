<?php
namespace CarParkAPI\Utils;
class InputValidator {
    public static function sanitize($input, $type) {
        switch ($type) {
            case 'date':
                // Validates a date in 'YYYY-MM-DD' format
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
                    $date = \DateTime::createFromFormat('Y-m-d', $input);
                    return $date && $date->format('Y-m-d') === $input ? $input : false;
                }
                break;
            case 'string':
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            case 'int':
                return filter_var($input, FILTER_VALIDATE_INT) ? intval($input) : false;
            case 'email':
                return filter_var($input, FILTER_VALIDATE_EMAIL) ? filter_var($input, FILTER_SANITIZE_EMAIL) : false;
           
        }
        return false;
    }
}



?>