<?php
class Validation {

    private $errors = [];
    private $data = [];
    private $db;

    public function __construct() {
        // agar tum PDO use kar rahe ho
        $this->db = Database::getInstance()->getConnection();
    }

    public function validate($data, $rules) {
        $this->errors = [];
        $this->data   = $data;

        foreach ($rules as $field => $ruleString) {

            $rulesArray = explode('|', $ruleString);

            foreach ($rulesArray as $rule) {

                if (strpos($rule, ':') !== false) {
                    list($ruleName, $ruleValue) = explode(':', $rule, 2);
                } else {
                    $ruleName  = $rule;
                    $ruleValue = null;
                }

                $methodName = 'validate' . ucfirst($ruleName);

                if (method_exists($this, $methodName)) {
                    $value = $this->data[$field] ?? null;

                    if (!$this->$methodName($field, $value, $ruleValue)) {
                        break; // stop further rules for this field
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function errors() {
        return $this->errors;
    }

    /* =========================
       BASIC RULES
    ==========================*/

    private function validateRequired($field, $value, $param) {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->errors[$field] = "The $field field is required";
            return false;
        }
        return true;
    }

    private function validateEmail($field, $value, $param) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "The $field must be a valid email address";
            return false;
        }
        return true;
    }

    private function validateNumeric($field, $value, $param) {
        if (!is_numeric($value)) {
            $this->errors[$field] = "The $field must be a number";
            return false;
        }
        return true;
    }

    private function validateUrl($field, $value, $param) {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = "The $field must be a valid URL";
            return false;
        }
        return true;
    }

    private function validateMatches($field, $value, $param) {
        if ($value !== ($this->data[$param] ?? null)) {
            $this->errors[$field] = "The $field must match $param";
            return false;
        }
        return true;
    }

    /* =========================
       MIN / MAX (ARRAY + STRING SAFE)
    ==========================*/

    private function validateMin($field, $value, $param) {

        // ARRAY (eg: comments)
        if (is_array($value)) {
            if (count($value) < (int)$param) {
                $this->errors[$field] = "The $field must have at least $param item(s)";
                return false;
            }
            return true;
        }

        // STRING
        if (is_string($value)) {
            if (mb_strlen(trim($value)) < (int)$param) {
                $this->errors[$field] = "The $field must be at least $param characters";
                return false;
            }
        }

        return true;
    }

    private function validateMax($field, $value, $param) {

        // ARRAY
        if (is_array($value)) {
            if (count($value) > (int)$param) {
                $this->errors[$field] = "The $field may not have more than $param items";
                return false;
            }
            return true;
        }

        // STRING
        if (is_string($value)) {
            if (mb_strlen(trim($value)) > (int)$param) {
                $this->errors[$field] = "The $field may not be greater than $param characters";
                return false;
            }
        }

        return true;
    }

    /* =========================
       ARRAY
    ==========================*/

    private function validateArray($field, $value, $param) {
        if (!is_array($value)) {
            $this->errors[$field] = "The $field must be an array";
            return false;
        }
        return true;
    }

    /* =========================
       PLAY STORE LINK (CUSTOM RULE)
    ==========================*/

    private function validatePlaystore($field, $value, $param) {

        $pattern = '/^https:\/\/play\.google\.com\/store\/apps\/details\?id=[a-zA-Z0-9._]+$/';

        if (!is_string($value) || !preg_match($pattern, $value)) {
            $this->errors[$field] = "The $field must be a valid Play Store app link";
            return false;
        }

        return true;
    }

    /* =========================
       UNIQUE (DB CHECK)
    ==========================*/

    private function validateUnique($field, $value, $param) {

        list($table, $column) = array_pad(explode(',', $param), 2, $field);

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
        $stmt->execute([$value]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $this->errors[$field] = "The $field is already taken";
            return false;
        }

        return true;
    }

    /* =========================
       IN RULE
    ==========================*/

    private function validateIn($field, $value, $param) {
        $allowed = explode(',', $param);

        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = "The $field must be one of: " . implode(', ', $allowed);
            return false;
        }

        return true;
    }
}
