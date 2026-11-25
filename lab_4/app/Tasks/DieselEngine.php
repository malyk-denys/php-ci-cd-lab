<?php

declare(strict_types=1);

namespace App\Tasks;

class DieselEngine extends Engine
{
    public function startEngine(): string
    {
        return parent::getRoleDetails().': запуск запалюванням паливно-повітряної суміші від стискання';
    }
}
