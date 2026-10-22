<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;

/**
 * Sets a user's password from the server terminal. Needed while e-mail is not
 * configured, because the "forgot password" link cannot be delivered.
 */
class ResetPassword extends Command
{
    protected $signature = 'srafes:reset-password
        {email : Current e-mail address of the account}
        {--new-email= : Also change the account e-mail address}';

    protected $description = 'Set a new password (and optionally e-mail) for a user, typed securely in the terminal';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user with that e-mail address.');

            return self::FAILURE;
        }

        $newEmail = $this->option('new-email');

        if ($newEmail !== null) {
            $validator = Validator::make(['email' => $newEmail], ['email' => ['required', 'email', 'unique:users,email,'.$user->id]]);

            if ($validator->fails()) {
                $this->error($validator->errors()->first('email'));

                return self::FAILURE;
            }
        }

        $password = password(
            label: "New password for {$user->email}",
            required: true,
            validate: fn (string $value) => mb_strlen($value) < 10 ? 'Use at least 10 characters.' : null,
        );

        $confirmation = password(label: 'Type it again', required: true);

        if (! hash_equals($password, $confirmation)) {
            $this->error('The passwords do not match. Nothing was changed.');

            return self::FAILURE;
        }

        // Clearing the remember token signs out "remember me" sessions on other devices
        $user->forceFill(['password' => $password, 'remember_token' => null]);

        if ($newEmail !== null) {
            $user->email = $newEmail;
        }

        $user->save();

        $this->info('Password updated'.($newEmail ? " and e-mail changed to {$newEmail}" : '').'. Other sessions using "remember me" were signed out.');

        return self::SUCCESS;
    }
}
