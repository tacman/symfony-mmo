<?php

namespace App\GameElement\ItemEquiment\Component;

use App\GameElement\Core\GameComponent\GameComponentInterface;

class ItemConditionComponent implements GameComponentInterface
{
    public function __construct(
        protected float $maxCondition,
        protected ?float $currentCondition = null,
    )
    {
        $this->currentCondition = $currentCondition ?? $maxCondition;
    }

    public function getMaxCondition(): float
    {
        return $this->maxCondition;
    }

    public function setMaxCondition(float $maxCondition): void
    {
        $this->maxCondition = $maxCondition;
    }

    public function getCurrentCondition(): ?float
    {
        return $this->currentCondition;
    }

    public function setCurrentCondition(?float $currentCondition): void
    {
        $this->currentCondition = $currentCondition;
    }
}