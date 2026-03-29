<?php

namespace App\Enums\Central;

enum UserRole: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case ACADEMY = 'academy';
    case INSTRUCTOR = 'instructor';
}
