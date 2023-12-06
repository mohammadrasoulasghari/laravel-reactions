<?php

namespace Qirolab\Laravel\Reactions\Traits;

use Qirolab\Laravel\Reactions\Contracts\ReactableInterface;
use Qirolab\Laravel\Reactions\Enums\ReactionAggressionTypeEnum;
use Qirolab\Laravel\Reactions\Events\OnDeleteReaction;
use Qirolab\Laravel\Reactions\Events\OnReaction;
use Qirolab\Laravel\Reactions\Models\Reaction;
use Qirolab\Laravel\Reactions\Utility\ReactionUtility;

trait Reacts
{
    /**
     * Reaction on reactable model.
     *
     * @param ReactableInterface $reactable
     * @param mixed $type
     * @return Reaction
     */
    public function reactTo(ReactableInterface $reactable, $type, $value = null)
    {
        $reaction = $reactable->reactions()->where([
            'user_id' => $this->getKey(),
            'type' => $type,
        ])->first();

        if (!$reaction) {
            $reaction = $this->storeReaction($reactable, $type, $value);
        }

        if ($reaction->type == $type) {
            return $reaction;
        }

        //$this->deleteReaction($reaction, $reactable);

        return $reaction;
    }

    /**
     * Remove reaction from reactable model.
     *
     * @param ReactableInterface $reactable
     * @return void
     */
    public function removeReactionFrom(ReactableInterface $reactable)
    {
        $reaction = $reactable->reactions()->where([
            'user_id' => $this->getKey(),
        ])->first();

        if (!$reaction) {
            return;
        }

        $this->deleteReaction($reaction, $reactable);
    }

    /**
     * Toggle reaction on reactable model.
     *
     * @param ReactableInterface $reactable
     * @param mixed $type
     * @return void
     */
    public function toggleReactionOn(ReactableInterface $reactable, $type, $value = null)
    {
        $reaction = $reactable->reactions()->where([
            'user_id' => $this->getKey(),
        ])->first();

        if (!$reaction) {
            return $this->storeReaction($reactable, $type, $value);
        }

        $this->deleteReaction($reaction, $reactable);

        if ($reaction->type == $type) {
            return;
        }

        return $this->storeReaction($reactable, $type, $value);
    }

    /**
     * Reaction on reactable model.
     *
     * @param ReactableInterface $reactable
     * @return Reaction
     */
    public function ReactedOn(ReactableInterface $reactable)
    {
        return $reactable->reacted($this);
    }

    /**
     * Check is reacted on reactable model.
     *
     * @param ReactableInterface $reactable
     * @param mixed $type
     * @return bool
     */
    public function isReactedOn(ReactableInterface $reactable, $type = null, $value = null)
    {
        $isReacted = $reactable->reactions()->where([
            'user_id' => $this->getKey(),
        ]);

        if ($type) {
            $isReacted->where([
                'type' => $type,
            ]);
        }
        if ($value) {
            $isReacted->where([
                'value' => $value,
            ]);
        }

        return $isReacted->exists();
    }

    /**
     * Store reaction.
     *
     * @param ReactableInterface $reactable
     * @param mixed $type
     * @return \Qirolab\Laravel\Reactions\Models\Reaction
     */
    protected function storeReaction(ReactableInterface $reactable, $type, $value = null)
    {
        $reaction = $reactable->reactions()->create([
            'user_id' => $this->getKey(),
            'type' => $type,
            'value' => $value
        ]);

        event(new OnReaction($reactable, $reaction, $this));

        $this->processColumnsReactions($reactable, $type);
        return $reaction;
    }


    /**
     * Delete reaction.
     *
     * @param Reaction $reaction
     * @param ReactableInterface $reactable
     * @return void
     */
    protected function deleteReaction(Reaction $reaction, ReactableInterface $reactable)
    {
        $type = $reaction->type;
        $response = $reaction->delete();

        event(new OnDeleteReaction($reactable, $reaction, $this));

        $this->processColumnsReactions($reactable, $type);
        return $response;
    }

    protected function processColumnsReactions(ReactableInterface $reactable, $type)
    {
        $aggression = ReactionUtility::getAggression($reactable, $type);
        if (!is_null($aggression) && $aggression['enabled']) {
            $operation = $aggression['type']->value;
            if (in_array($operation, array_column(ReactionAggressionTypeEnum::cases(), 'value'))) {
                $columnName = $type . '_' . $operation;
                $row = $reactable->reactions()->where([
                    'type' => $type
                ]);
                if ($operation === 'count') {
                    $updatedValue = $row->count();
                } else {
                    $updatedValue = $row->$operation('value');
                }
                $reactable->update([
                    $columnName => $updatedValue,
                ]);
            }
        }
    }

}
