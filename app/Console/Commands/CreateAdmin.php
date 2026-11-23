<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Create an administrator using a hidden password prompt';

    public function handle(): int
    {
        $data = [
            'name' => $this->ask('Admin name'),
            'email' => Str::lower(trim($this->ask('Admin email') ?? '')),
            'password' => $this->secret('Password (at least 8 characters, with letters and numbers)'),
            'password_confirmation' => $this->secret('Confirm password'),
        ];
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:members,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        $user = new User(collect($data)->only(['name', 'email', 'password'])->all());
        $user->role = 'admin';
        $user->save();
        $this->info('Admin created. Sign in at '.route('admin.login'));

        return self::SUCCESS;
    }
}
