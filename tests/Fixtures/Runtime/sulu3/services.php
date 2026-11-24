<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Sulu\MateExtension\Tests\Fixtures\Runtime\Twig\FixtureResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('fixture.resolver')
        ->class(FixtureResolver::class)
        ->tag('sulu_content.property_resolver');

    $services->set('fixture.explicit', FixtureResolver::class)
        ->tag('sulu_content.property_resolver', ['type' => 'explicit_type']);

    $services->set('fixture.mapper', FixtureResolver::class)
        ->tag('sulu_admin.property_metadata_mapper', ['type' => 'fixture_mapped'])
        ->tag('sulu_admin.property_metadata_mapper', ['type' => 'fixture_mapped_two']);

    $services->set('fixture.unrelated', FixtureResolver::class)
        ->tag('some.other_tag');
};
