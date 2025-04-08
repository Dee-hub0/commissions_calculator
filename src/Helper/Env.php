<?php

namespace App\Helper;

final class Env
{
    public static function getEnv(string $index = null)
    {
        if($index == null){
            return !empty(getEnv()) ? getenv() : $_ENV;
        }
        return !empty(getenv($index)) ? getenv($index) : (isset($_ENV[$index]) ? $_ENV[$index] : '');
    }
}