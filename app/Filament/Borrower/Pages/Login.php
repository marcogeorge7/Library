<?php

namespace App\Filament\Borrower\Pages;

use Filament\Actions\Action;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),

            Action::make('admin_login')
                ->label('Login as Admin')
                ->link()
                ->url('/admin/login')
                ->color('gray'),
        ];
    }
}
