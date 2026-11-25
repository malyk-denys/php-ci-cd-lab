<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\LoggerInterface;
use App\Models\AbstractEntity;

final class EntityService
{
    public function __construct(private LoggerInterface $logger) {}

    public function describe(AbstractEntity $entity): string
    {
        $details = $entity->getDetails();
        $this->logger->log('Оброблено сутність '.$entity->getId());

        return $details;
    }
}
