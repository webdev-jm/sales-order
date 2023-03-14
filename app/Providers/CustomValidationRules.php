<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;

class CustomValidationRules
{
    public function validateYyyyANumber($attribute, $value, $parameters, $validator)
    {
        return preg_match('/^[0-9]{4}-[A-Za-z]-[0-9]{5}$/', $value);
    }
}