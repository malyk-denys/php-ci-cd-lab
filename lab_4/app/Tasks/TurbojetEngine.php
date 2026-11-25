<?php

declare(strict_types=1);

namespace App\Tasks;

final class TurbojetEngine extends DieselEngine
{
    public function startEngine(): string
    {
        return parent::startEngine().'; активовано турбореактивну тягу';
    }
}
