<?php

namespace App\Services;

use BeviWebdevJm\HubAuthClient\Contracts\ResolvesHubUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Two\User as SocialiteUser;
use Spatie\Permission\Models\Role;

class HubUserResolver implements ResolvesHubUser
{
    public function resolve(SocialiteUser $hubUser): Authenticatable
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model&\Illuminate\Contracts\Auth\Authenticatable> $userModel */
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        // Return existing user linked by hub_user_id.
        $user = $userModel::where('hub_user_id', (string) $hubUser->getId())->first();

        if ($user) {
            return $user;
        }

        // Link a pre-existing local account that shares the same email or notify_email.
        $user = $userModel::where('email', $hubUser->getEmail())
            ->orWhere('notify_email', $hubUser->getEmail())
            ->first();

        if ($user) {
            $user->hub_user_id = (string) $hubUser->getId();
            $user->notify_email = $hubUser->getEmail();
            $user->save();

            return $user;
        }

        // Create a new local user from the hub user data.
        [$firstname, $lastname] = $this->splitName($hubUser->getName() ?? '');

        $user = $userModel::create([
            'hub_user_id'  => (string) $hubUser->getId(),
            'firstname'    => $firstname,
            'lastname'     => $lastname,
            'email'        => $hubUser->getEmail(),
            'notify_email' => $hubUser->getEmail(),
            'password'     => null,
        ]);

        $this->assignDefaultRole($user);

        return $user;
    }

    /**
     * Split a full name into firstname and lastname on the first space.
     *
     * @return array{string, string}
     */
    private function splitName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    /**
     * Assign the configured default role if it exists in the database.
     *
     * @param  \Illuminate\Database\Eloquent\Model&\Illuminate\Contracts\Auth\Authenticatable  $user
     */
    private function assignDefaultRole(mixed $user): void
    {
        $defaultRole = config('hub-auth.default_role', 'default_user');

        if (! Role::where('name', $defaultRole)->exists()) {
            return;
        }

        $user->assignRole($defaultRole);
    }
}
