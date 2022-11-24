<?php

namespace App\Security;

class TokenGenerator
{

    public function getRandomSecureToken(): string
    {
        return md5(uniqid(rand(), true));
    }
}
