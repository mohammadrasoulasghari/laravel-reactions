<?php

namespace Qirolab\Laravel\Reactions\Enums;

enum ReactionAggressionTypeEnum: string
{
    case Sum = 'sum';
    case Count = 'count';
    case Avg = 'avg';
    case Min = 'min';
    case Max = 'max';
}
