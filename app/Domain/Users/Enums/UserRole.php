<?php

namespace App\Domain\Users\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';
}
