<?php

declare(strict_types=1);

namespace App\Traits;

use JsonException;

trait JsonExportTrait
{
    /** @throws JsonException */
    public function exportToJson(): string
    {
        return json_encode(
            get_object_vars($this),
            JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }
}
