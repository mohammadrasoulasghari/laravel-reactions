<?php

namespace Qirolab\Laravel\Reactions\Utility;

use Qirolab\Laravel\Reactions\Contracts\ReactableInterface;
use Qirolab\Laravel\Reactions\Enums\ReactionAggressionTypeEnum;

class ReactionUtility
{
    public static function getAggression(ReactableInterface $reactable,$type)
    {
        $options = $reactable::reactionOptions();
        if (in_array($type, array_keys($options))) {
            $reactionsOptionKey = $options[$type];
            $isAggression = $reactionsOptionKey['aggression'];
            $aggressionType = $reactionsOptionKey['aggression_type'];
            return [
                'enabled'=> $isAggression,
                'type'=> ReactionAggressionTypeEnum::tryFrom($aggressionType)
            ];
        }
        return null;
    }

    public static function getType(ReactableInterface $reactable,$type):string
    {
        $value = self::getAggression($reactable,$type);
        if (is_null($value) || !$value['enabled']) {
            return ReactionAggressionTypeEnum::Count->value;
        }
        return $value['type']->value;
    }
}
