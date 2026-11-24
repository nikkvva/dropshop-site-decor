<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

/**
 * Вход в админку со строгой защитой от перебора паролей.
 *
 * Стандартный вход Filament разрешает 5 попыток в минуту с одного IP.
 * Здесь те же 5 попыток, но блокировка — на 15 минут.
 */
class Login extends BaseLogin
{
    protected function rateLimit($maxAttempts, $decaySeconds = 60, $method = null, $component = null)
    {
        parent::rateLimit($maxAttempts, 15 * 60, $method ?? 'authenticate', $component);
    }
}
