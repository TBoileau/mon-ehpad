<?php

declare(strict_types=1);

use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\Expression\ForClasses\NotHaveDependencyOutsideNamespace;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\Rules\Rule;

return static function (Config $config): void {
    $symfonySet = ClassSet::fromDir(__DIR__.'/../src');

    $rules = [];

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\*\Domain'))
        ->should(
            new NotHaveDependencyOutsideNamespace(
                'App\*\Domain',
                [
                    'Attribute',
                    Symfony\Component\Uid\Ulid::class,
                    Symfony\Component\Validator\Constraint::class,
                    'Symfony\Component\Validator\Constraints',
                    'Symfony\Component\Validator\ConstraintValidator',
                ]
            )
        )
        ->because('we want protect our domain');

    $config->add($symfonySet, ...$rules);
};
