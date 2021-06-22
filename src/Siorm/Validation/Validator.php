<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2018/12/25
 * Time: 11:14
 */

namespace Sifra\Siorm\Validation;

use InvalidArgumentException;
use RuntimeException;
use Sifra\Http\Request;
use Sifra\Siorm\Exception\ValidationException;
use Sifra\Siorm\QueryBuilder;

class Validator
{
    private $rules;
    private $customMessages;
    private $errors;

    private $currentField;

    const VALIDATION_RULES = [
        'minLength',
        'maxLength',
        'min',
        'max',
        'match',
        'type',
        'required',
        'unique',
        'pattern',
        'email',
        'numeric',
        'int' ,
        'nullable' //TODO
    ];

    public function __construct()
    {
        $this->rules = array();
        $this->customMessages = array();
        $this->errors = new ValidationResult();
    }

    /**
     * @param mixed $source The data to validate. Can be a Request or an array.
     * @return ValidationResult
     */
    public function validate($source)
    {
        // Check if the source is not a Request or an array.
        if (!($source instanceof Request || is_array($source))) {
            throw new RuntimeException("source must be a Request or an array");
        }

        $source = is_array($source) ? $source : $source->toArray();

        if (func_num_args() == 2) {
            $this->rules = array_map(function ($v) {
                return self::formatRules($v);
            }, func_get_arg(1));

        } else if (func_num_args() == 3) {
            $this->rules = array_map(function ($v) {
                return self::formatRules($v);
            }, func_get_arg(1));

            $this->customMessages = func_get_arg(2);
        }

        /**
         * If the source is an array we use it has it is and
         * if it's a Request we call the all() method on the Request
         * which returns an array.
         */
        $source = is_array($source) ? $source : $source->all();

        $errors = array();

        foreach ($this->rules as $fieldName => $fieldRules) {

            $fieldErrors = array();

            // Too many edge cases when it comes to the 'required' rule validation.
            // So needs to be handled separate.
            if (isset($fieldRules['required'])
                && !isset($source[$fieldName])
                || isNullOrEmpty($source[$fieldName])) {

                $fieldErrors['required'] = $this->getError($fieldName, 'required', true);

            } else {
                // Note: $ruleValue is the value passed to the validation rule
                // For example, min:6, '6' is the $ruleValue
                foreach ($fieldRules as $rule => $ruleValue) {
                    $validationResult = call_user_func_array([$this, $rule], [$source, $fieldName, $ruleValue]);

                    if (!$validationResult) {
                        $fieldErrors[$rule] = $this->getError($fieldName, $rule, $ruleValue);
                    }
                }
            }

            if (count($fieldErrors)) {
                $errors[$fieldName] = $fieldErrors;
            }
        }


        $this->errors->__setErrors($errors);
        
        request()->__setValidation($this->errors);

        return $this->errors;
    }

    public function validateOrFail($source)
    {
        call_user_func_array([$this, 'validate'], func_get_args());

        if (!$this->errors->isEmpty()) {
            throw new ValidationException();
        }

        return $this->errors;
    }

    private function getError($fieldName, $rule, $ruleValue)
    {
        if (isset($this->customMessages["{$fieldName}.{$rule}"])) {
            return $this->customMessages["{$fieldName}.{$rule}"];
        }

        $msg = str_replace('{0}', strtolower($ruleValue),  self::MESSAGES[$rule]);

        return $msg;
    }

    public function isValid()
    {
        return $this->errors->isEmpty();
    }

    // Initializes Fluent validation
    public function ruleFor($fieldName, $rules, $condition)
    {
        if ($condition) {
            $this->rules[$fieldName] = self::formatRules($rules);
            $this->currentField = $fieldName;
        }

        return $this;
    }

    public function withMessage($rule, $message)
    {
        if (self::ruleExists($rule)) {
            $this->customMessages["{$this->currentField}.{$rule}"] = $message;
        }

        return $this;
    }

    public function errors()
    {
        return $this->errors;
    }

    private function required($source, $fieldName)
    {
        return isset($source[$fieldName]) && !empty($source[$fieldName]);
    }

    private function min($source, $fieldName, $ruleValue)
    {
        return (is_numeric($source[$fieldName]) && is_numeric($ruleValue)) && $source[$fieldName] >= $ruleValue;
    }

    private function max($source, $fieldName, $ruleValue)
    {
        return (is_numeric($source[$fieldName]) && is_numeric($ruleValue)) && $source[$fieldName] <= $ruleValue;
    }

    private function minLength($source, $fieldName, $ruleValue)
    {
        return strlen($source[$fieldName]) >= (int)$ruleValue;
    }

    private function maxLength($source, $fieldName, $ruleValue)
    {
        return strlen($source[$fieldName]) <= (int)$ruleValue;
    }

    private function email($source, $fieldName, $ruleValue)
    {
        return $ruleValue && filter_var($source[$fieldName], FILTER_VALIDATE_EMAIL);
    }

    private function match($source, $fieldName, $ruleValue)
    {
        return (isset($source[$fieldName]) && isset($source[$ruleValue])) && $source[$fieldName] == $source[$ruleValue];
    }

    private function numeric($source, $fieldName)
    {
        return filter_var($source[$fieldName], FILTER_VALIDATE_INT) &&
               filter_var($source[$fieldName], FILTER_VALIDATE_FLOAT);
    }

    private function pattern($source, $fieldName, $ruleValue)
    {
        return preg_match($source[$fieldName], $ruleValue) === true;
    }

    private function unique($source, $fieldName, $ruleValue)
    {
        $count = QueryBuilder::table($ruleValue)
            ->select()
            ->where($fieldName, $source[$fieldName])
            ->count()
            ->get();

        return !$count;
    }

    public static function make()
    {
        return new Validator();
    }

    private static function formatRules($rules) {
        if (!(is_array($rules) || is_string($rules))) {
            throw new InvalidArgumentException('$rules must be an array or a pipe separated string.');
        }

        return is_array($rules) ? $rules : pipedStringToArray($rules);
    }

    private static function ruleExists($rule)
    {
        return in_array($rule,self::VALIDATION_RULES);
    }

    const MESSAGES = [
        'minLength' => 'Please enter at least {0} characters.',
        'maxLength' => 'Please enter no more than {0} characters.',
        'min' => 'Please enter a value greater than or equal to {0}.',
        'max' => 'Please enter a value less than or equal to {0}.',
        'match' => 'Please enter the same value again.',
        'type' => 'The type must be the same.',
        'required' => 'This field is required.',
        'unique' => 'This already exists.',
        'pattern' => 'Please enter a value that matches the required format.',
        'email' => 'Please enter a valid email.',
        'numeric' => 'Please enter a valid number.',
        'int' => 'Please enter a valid integer.',
    ];
}