<?php

namespace App\Support;

use App\Models\User;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use OpenIDConnect\Interfaces\IdentityEntityInterface;

class IdentitySupport implements IdentityEntityInterface
{
    use EntityTrait;

    /**
     * The user to collect the additional information for
     */
    protected User $user;

    /**
     * The identity repository creates this entity and provides the user id
     *
     * @param  mixed  $identifier
     */
    public function setIdentifier($identifier): void
    {
        $this->identifier = $identifier;
        $this->user = User::findOrFail($identifier);
    }

    /**
     * @return string[]
     */
    public function getClaims(): array
    {
        /**
         * For a complete list of default claim sets
         *
         * @see \OpenIDConnect\ClaimExtractor
         */
        return [
            // profile
            'name' => $this->user->name,

            // email
            'email' => $this->user->email,
            'email_verified' => ! empty($this->user->email_verified_at),
            'email_verified_at' => $this->user->email_verified_at,

            // phone
            //            'phone_number' => '0031 493 123 456',
            //            'phone_number_verified' => true,

            // address
            //            'address' => 'Castle Black, The Night\'s Watch, The North',

            // realname
            'realname' => $this->user->real_name,
            'realname_verified' => ! empty($this->user->real_name_verified_at),
            'realname_verified_at' => $this->user->real_name_verified_at,

            'avatar' => $this->user->avatar(),
        ];
    }
}
