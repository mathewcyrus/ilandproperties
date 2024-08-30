<?php

namespace App\Service;

use App\Entity\User;

class UserSerializer
{
    public function serialize(User $user): array
    {
        return [
            'user_id' => $user->getUserId(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'avatar' => $user->getAvatar(),
            'email' => $user->getEmail(),
            'phone_number' => $user->getPhoneNumber(),
            'is_admin' => $user->isAdmin(),
            'is_verified' => $user->isVerified(),
            'date_created' => $user->getDateCreated()->format('Y-m-d H:i:s'),
        ];
    }
}
