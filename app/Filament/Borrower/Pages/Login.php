<?php

namespace App\Filament\Borrower\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

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

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('البريد الإلكتروني أو رقم الهاتف')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $key = str_contains($data['login'], '@') ? 'email' : 'phone';

        return [
            $key => $data['login'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}
