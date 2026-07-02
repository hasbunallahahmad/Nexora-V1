<?php

declare(strict_types=1);

namespace App\Calendar\Enums;

enum CalendarAudience: string
{
    case Public = 'public';
    case Admin = 'admin';
}
