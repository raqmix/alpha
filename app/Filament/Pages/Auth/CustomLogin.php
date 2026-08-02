<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login;

class CustomLogin extends  Login
{

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => config('auth.providers.users.model')::STATUS_ACTIVE,
        ];
    }


}
