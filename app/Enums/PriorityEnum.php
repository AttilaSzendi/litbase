<?php

namespace App\Enums;

enum PriorityEnum: int
{
    use EnumHelper;

    case WAIT_FOR_DEVELOPMENT = 1;
    case IN_PROGRESS = 2;
    case RELEASED = 3;
}
