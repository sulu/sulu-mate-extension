<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Sulu\MateExtension\Capability\SuluInfo;
use Sulu\MateExtension\Capability\TemplateInfo;
use Sulu\MateExtension\Capability\WebspaceInfo;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(SuluInfo::class);

    $services->set(WebspaceInfo::class)
        ->arg('$projectDir', '%mate.root_dir%');

    $services->set(TemplateInfo::class)
        ->arg('$projectDir', '%mate.root_dir%');
};
