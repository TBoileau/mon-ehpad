<?php

declare(strict_types=1);

namespace App\Core\Domain\CQRS;

interface QueryBus
{
    public function fetch(Query $query): mixed;
}
