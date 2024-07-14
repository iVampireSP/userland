<?php

namespace App\Helpers;

trait UserClaimsTrait
{
    public function getClaims($scopes = []): array
    {
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'avatar' => $this->avatar(),
        ];

        if (in_array('profile', $scopes)) {
            $data['name'] = $this->name;
            $data['email_verified'] = $this->email_verified_at !== null;
            $data['real_name_verified'] = $this->real_name_verified_at !== null;
            $data['phone_verified'] = $this->phone_verified_at !== null;
        }

        if (in_array('email', $scopes)) {
            $data['email'] = $this->email;
        }

        if (in_array('realname', $scopes)) {
            $data['real_name'] = $this->real_name;
            $data['id_card'] = $this->id_card;
        }

        if (in_array('phone', $scopes)) {
            $data['phone'] = $this->phone;
        }

        $data['created_at'] = $this->created_at;

        return $data;
    }
}
