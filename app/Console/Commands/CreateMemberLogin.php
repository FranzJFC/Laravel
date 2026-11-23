<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateMemberLogin extends Command
{
    protected $signature = 'app:create-member-login {member : The existing member ID}';

    protected $description = 'Create a login for an existing member without changing their history';

    public function handle(): int
    {
        $member = Member::find($this->argument('member'));
        if (! $member || $member->user_id) {
            $this->error('Member not found, or this member already has a login.');

            return self::FAILURE;
        }
        $data = [
            'email' => Str::lower(trim($this->ask('Member email', $member->email) ?? '')),
            'password' => $this->secret('Password (at least 8 characters, with letters and numbers)'),
            'password_confirmation' => $this->secret('Confirm password'),
        ];
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'max:255', 'unique:users,email', Rule::unique('members', 'email')->ignore($member->id)],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        DB::transaction(function () use ($data, $member) {
            $user = new User(['name' => $member->full_name, 'email' => $data['email'], 'password' => $data['password']]);
            $user->role = 'member';
            $user->save();
            $member->update(['user_id' => $user->id, 'email' => $data['email']]);
        });
        $this->info('Member login created. Sign in at '.route('member.login'));

        return self::SUCCESS;
    }
}
