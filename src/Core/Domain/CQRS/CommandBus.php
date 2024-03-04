<?php

declare(strict_types=1);

namespace App\Core\Domain\CQRS;

interface CommandBus
{
    public function execute(Command $command): mixed;
}
