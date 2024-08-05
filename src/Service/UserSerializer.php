<?php

namespace App\Service;

use App\Entity\User;

class UserSerializer
{
    public function serialize(User $user): array
    {
        return [
            'user_id' => $user->getUserId(),
            'username' => $user->getUsername(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'email' => $user->getEmail(),
            'phone_number' => $user->getPhoneNumber(),
            'is_admin' => $user->isAdmin(),
            'date_created' => $user->getDateCreated()->format('Y-m-d H:i:s'),
        ];
    }
}
