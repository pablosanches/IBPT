<?php

namespace PabloSanches\IBPT\Validators;

use PabloSanches\IBPT\Exceptions\ValidatorException;

/**
 * Validator
 * 
 * @throws ValidatorException
 */
abstract class Validator
{
    /**
     * Rules list
     *
     * @var array
     */
    private static $rules = array();

    /**
     * Collection to validate
     *
     * @var array
     */
    private static $dataToValidate = array();

    /**
     * List of failures
     *
     * @var array
     */
    private static $failures = array();

    /**
     * Resource name
     *
     * @var string
     */
    private static $resourceKey = '';

    /**
     * Standard error messages 
     *
     * @var array
     */
    private static $defaultMessages = array(
        'required' => 'O campo {fieldName} é obrigatório.',
        'boolean' => 'O campo {fieldName} precisa ser verdadeiro ou falso.',
        'maxlength' => 'O campo {fieldName} precisa ter no máximo {extra} caracteres.',
        'minlength' => 'O campo {fieldName} precisa ter no mínimo {extra} caracteres.',
        'extensionFile' => 'O campo {fieldName} precisa ser um arquivo das seguintes extensões: {extra}.',
        'mimetype' => 'O campo {fieldName} precisa ser um arquivo dos tipos ({extra}).',
        'type' => [
            "cpf" => 'O campo {fieldName} precisa ser um CPF válido.',
            "cnpj" => 'O campo {fieldName} precisa ser um CNPJ válido.',
            "cpfcnpj" => 'O campo {fieldName} precisa ser um CPF ou CNPJ válido.',
            "email" => 'O campo {fieldName} precisa ser um E-MAIL válido.',
            "phone" => 'O campo {fieldName} precisa ser um TELEFONE válido.',
            'chosen' => 'O campo {fieldName} precisa estar compreendido entre as opções disponíveis ({extra}).',
            'zipcode' => 'O campo {fieldName} precisa ser um CEP válido.',
            'file' => 'O campo {fieldName} precisa ser um arquivo.'
        ]
    );

    /**
     * Validate an collection based on his rules
     *
     * @param string $resourceKey Key linked in the object that relates to the rules file 
     * @param array $dataToValidate Collection of data to be validated
     * @return mixed array/null Data collection with all errors
     */
    public static function validate($resourceKey, $dataToValidate)
    {
        self::$failures = array();

        self::setRules($resourceKey);
        self::setDataToValidate($dataToValidate);

        self::$resourceKey = $resourceKey;

        return self::init();
    }

    /**
     * Sets rules based on the object key
     *
     * @param string $resourceKey Key linked in the object that relates to the rules file
     * 
     * @throws ValidatorException If no validation file is found for that key
     * 
     * @return void
     */
    private static function setRules($resourceKey)
    {
        $rulesFile = dirname(__DIR__) . "/Validators/$resourceKey.json";
        $handle = @fopen($rulesFile, "r");
        if ($handle) {
            $data = fread($handle, filesize($rulesFile));
            self::$rules[$resourceKey] = json_decode($data, true);
        } else {
            throw new ValidatorException("Rules file not founded.");
        }
    }

    /**
     * Collection arrow that will be validated 
     *
     * @param array $dataToValidate
     * @return void
     */
    private static function setDataToValidate(array $dataToValidate)
    {
        self::$dataToValidate = $dataToValidate;
    }
    
    /**
     * Start validate process
     *
     * @return array Collection of all errors
     */
    private static function init()
    {
        return self::runValidator(self::$rules[self::$resourceKey]);
    }

    /**
     * Go through each rule stipulated recursively in your file by executing its validation methods.
     *
     * @param array $rules Collection of Rules
     * @param boolean $recursive Flag to control recursion
     * @param string $parent Name of field of parent rule (Recursion only) 
     * @return array Collection of all errors
     */
    private static function runValidator($rules, $recursive = false, $parent = null)
    {
        if (is_array($rules)) {
            foreach ($rules as $field => $rule) {
                if ($field == 'endereco') {
                    if (array_key_exists('childRule', $rule)) { // Rodas regras
                        self::runValidator($rules[$field], true, $field);
                    }
                }

                if ($recursive && is_array($rule)) { // Segundo nivel
                    self::checkField($rule, $field, self::$dataToValidate[$parent]);
                } else {
                    self::checkField($rule, $field, self::$dataToValidate);
                }
            }
        }

        return self::$failures;
    }

    /**
     * Check each field by his rule
     *
     * @param array $rules
     * @param string $field
     * @param array $dataArray
     * @return void
     */
    private static function checkField($rules, $field, $dataArray)
    {
        if (!array_key_exists($field, $dataArray)) {
            return;
        }
        $value = $dataArray[$field];
        
        if (is_array($rules)) {
            if (array_key_exists('required', $rules)) {
                if (!$rules['required'] && empty($value)) {
                    return;
                }
            }
        }

        if (is_array($rules)) {
            foreach ($rules as $ruleName => $ruleValue) {
                switch ($ruleName) {
                    case 'required':
                        if ($ruleValue) {
                            self::isRequired($field, $value);
                        } else {
                            if (!empty($value)) {
                                self::isRequired($field, $value);
                            }
                        }
                    break;

                    case 'type':
                        switch ($ruleValue) {
                            case 'chosen':
                                self::inChosen($rules, $field, $value);
                            break;

                            case 'cnpj':
                                self::isCNPJ($field, $value);
                            break;

                            case 'cpf':
                                self::isCPF($field, $value);
                            break;

                            case 'cpfcnpj':
                                self::isCPFCNPJ($field, $value);
                            break;

                            case 'email':
                                self::isEmail($field, $value);
                            break;

                            case 'phone':
                                self::isPhone($field, $value);
                            break;

                            case 'zipcode':
                                self::isZipcode($field, $value);
                            break;

                            case 'boolean':
                                self::isBoolean($field, $value);
                            break;

                            case 'number':
                                self::isNumber($field, $value);
                            break;
                            
                            case 'file':
                                self::isFile($field, $value);
                            break;

                            case 'double':
                                self::isDouble($field, $value);
                            break;
                        }
                    break;
                    
                    case 'extensionFile':
                        self::isExtension($rules, $field, $value);
                    break;

                    case 'maxlength':
                        self::isMaxlength($rules, $field, $value);
                    break;

                    case 'minlength':
                        self::isMinlength($rules, $field, $value);
                    break;

                    case 'mimetype':
                        self::isMimetype($rules, $field, $value);
                    break;
                }
            }
        }
    }

    /**
     * Set the error message
     *
     * @param string $fieldName
     * @param string $msg
     * @param string $extra
     * @return string
     */
    private static function setDefaultMessage($fieldName, $msg, $extra = '')
    {
        return strtr($msg, array(
            '{fieldName}' => $fieldName,
            '{extra}' => $extra
        ));
    }

    /**
     * Checks if the value of the field is required
     *
     * @param string $field
     * @param string $var
     * @return boolean
     */
    private static function isRequired($field, $var)
    {
        if (empty($var)) {
            $defaultMsg = self::$defaultMessages['required'];
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
        }
    }

    /**
     * Checks if the value of the field is a valid email
     *
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isEmail($field, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $defaultMsg = self::$defaultMessages['type']['email'];
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
        }
    }

    /**
     * Checks if the value of the field is a boolean
     *
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isBoolean($field, $value)
    {
        if (!is_bool($value)) {
            $defaultMsg = self::$defaultMessages['boolean'];
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
        }
    }

    /**
     * Checks if the value of the field is a number
     *
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isNumber($field, $value)
    {
        if (!is_numeric($value)) {
            $defaultMsg = self::$defaultMessages['boolean'];
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
        }
    }

    /**
     * Checks if a value of the field is a double
     *
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isDouble($field, $value)
    {
        if (!is_double($value)) {
            $defaultMsg = self::$defaultMessages['boolean'];
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
        }
    }

    /**
     * Checks if a value of the field is a file
     *
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isFile($field, $value)
    {
        if (!is_file($value) || !file_exists($value)) {
            $defaultMsg = self::$defaultMessages['file'];
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
        }
    }

    /**
     * Checks if the mimetype of the file is an expected mimetype
     *
     * @param array $rules
     * @param string $field
     * @param string $value
     * 
     * @throws ValidatorException If the comparison parameters were not passed 
     * 
     * @return boolean
     */
    private static function isMimetype($rules, $field, $value)
    {
        $mimetypeAllowed = $rules['mimetype'];
        if (!empty($mimetypeAllowed)) {
            $mimeType = mime_content_type($value);
            if (!in_array($mimeType, $mimetypeAllowed)) {
                $defaultMsg = self::$defaultMessages['mimetype'];
                self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg, implode(', ', $mimetypeAllowed));
            }
        } else {
            throw new ValidatorException("All mime type allowed by fields must to have an array with all expected values");
        }
    }

    /**
     * Check if the extension of the file is an extension expected
     *
     * @param array $rules
     * @param string $field
     * @param string $value
     * 
     * @throws ValidatorException If the comparison parameters were not passed
     * 
     * @return boolean
     */
    private static function isExtension($rules, $field, $value)
    {
        $extensionsAllowed = $rules['extensionFile'];
        if (!empty($extensionsAllowed)) {
            $info = pathinfo($value);
            if (!in_array($info['extension'], $extensionsAllowed)) {
                $defaultMsg = self::$defaultMessages['extensionFile'];
                self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg, implode(', ', $extensionsAllowed));
            }
        } else {
            throw new ValidatorException("All extensions allowed by fields must to have an array with all expected values");
        }
    }

    /**
     * Checks if a value of the field is a valid CPF or CNPJ
     *
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isCPFCNPJ($field, $value)
    {
        $cpfcnpj = self::onlyNumbers($value);
        if (!self::isCPF($field, $cpfcnpj) && !self::isCPFCNPJ($field, $cpfcnpj)) {
            $defaultMsg = self::$defaultMessages['type']['cpfcnpj'];
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
        }
    }

    /**
     * Checks if the value of the field is a valid CPF
     *
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isCPF($field, $value)
    {
        $cpf = self::onlyNumbers($value);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
        $defaultMsg = self::$defaultMessages['type']['cpf'];

        if (strlen($cpf) !== 11) {
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
                return false;
            }
        }
    }

    /**
     * Checks if the value of the field is a valid CNPJ
     *
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isCNPJ($field, $value)
    {
        $cnpj = self::onlyNumbers($value);
        $cnpj = str_pad($cnpj, 14, '0', STR_PAD_LEFT);
        $defaultMsg = self::$defaultMessages['type']['cnpj'];

        if (strlen($cnpj) != 14) {
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
            return false;
        } else if (
            $cnpj == '00000000000000' || 
            $cnpj == '11111111111111' || 
            $cnpj == '22222222222222' || 
            $cnpj == '33333333333333' || 
            $cnpj == '44444444444444' || 
            $cnpj == '55555555555555' || 
            $cnpj == '66666666666666' || 
            $cnpj == '77777777777777' || 
            $cnpj == '88888888888888' || 
            $cnpj == '99999999999999'
        ) {
		    self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
            return false;
        } else {
            $j = 5;
            $k = 6;
            $soma1 = "";
            $soma2 = "";

            for ($i = 0; $i < 13; $i++) {

                $j = $j == 1 ? 9 : $j;
                $k = $k == 1 ? 9 : $k;

                $soma2 += ($cnpj{$i} * $k);

                if ($i < 12) {
                    $soma1 += ($cnpj{$i} * $j);
                }

                $k--;
                $j--;

            }

            $digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
            $digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;

            if  (!(($cnpj{12} == $digito1) and ($cnpj{13} == $digito2))) {
                self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
                return false;
            }
        }
    }

    /**
     * Checks if the value of the field is a valid zipcode
     *
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isZipcode($field, $value)
    {
        if (is_array($value)) {
            $zipcode = $value;
            $zipcode = self::onlyNumbers($zipcode);
        } else {
            $zipcode = self::onlyNumbers($value);
        }

        if (strlen($zipcode) !== 8) {
            $defaultMsg = self::$defaultMessages['type']['zipcode'];
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
        }
    }

    /**
     * Checks if the value of the field has a maximum length
     *
     * @param array $rules
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isMaxlength($rules, $field, $value)
    {
        if (strlen(trim($value)) > $rules['maxlength']) {
            $defaultMsg = self::$defaultMessages['maxlength'];
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg, $rules['maxlength']);
        }
    }

    /**
     * Checks if the value of the field has at least a minimum length
     *
     * @param array $rules
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isMinlength($rules, $field, $value)
    {
        if (strlen(trim($value)) < $rules['minlength']) {
            $defaultMsg = self::$defaultMessages['minlength'];
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg, $rules['minlength']);
        }
    }

    /**
     * Checks if the value of the fields its on a list of expected values
     *
     * @param array $rules
     * @param string $field
     * @param string $value
     * @return void
     */
    private static function inChosen($rules, $field, $value)
    {
        $expected = $rules['expected'];
        if (!empty($expected)) {
            if (!in_array($value, $expected)) {
                $defaultMsg = self::$defaultMessages['type']['chosen'];
                self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg, implode(', ', $expected));
            }
        } else {
            throw new ValidatorException("All chosen fields must to have an array with all expected values");
        }
    }

    /**
     * Checks if the value of the field is a valid phone number
     *
     * @param string $field
     * @param string $value
     * @return boolean
     */
    private static function isPhone($field, $value)
    {
        $phone = self::onlyNumbers($value);
        $phoneLength = strlen($phone);

        if ($phoneLength != 11 && $phoneLength != 10) {
            $defaultMsg = self::$defaultMessages['type']['phone'];
            self::$failures[$field]['msg'][] = self::setDefaultMessage($field, $defaultMsg);
        }
    }

    /**
     * Return only the numbers of a string
     *
     * @param string $str
     * @return string
     */
    private static function onlyNumbers($str)
    {
        return preg_replace("/[^0-9]/", "", $str);
    }

    /**
     * Print all failures
     *
     * @return string
     */
    public static function printFailures()
    {
        $failsToPrint = array();
        $failString = '';

        if (!empty(self::$failures)) {
            foreach (self::$failures as $field => $fails) {
                $failsToPrint[] = implode("\r\n", $fails['msg']);
            }
        }
        $failString = implode("\r\n", $failsToPrint);
        
        return $failString;
    }
}