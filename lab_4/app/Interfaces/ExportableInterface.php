<?php

declare(strict_types=1);

namespace App\Interfaces;

interface ExportableInterface
{
    public function exportToJson(): string;
}
