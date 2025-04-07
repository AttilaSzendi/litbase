<?php

namespace App\Enums;

enum PriorityEnum: int
{
    use EnumHelper;

    case HIGH = 1;
    case NORMAL = 2;
    case LOW = 3;
}
