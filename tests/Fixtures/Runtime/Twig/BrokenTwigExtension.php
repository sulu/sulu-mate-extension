<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\MateExtension\Tests\Fixtures\Runtime\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BrokenTwigExtension extends AbstractExtension
{
    public function __construct(private readonly \DateTimeImmutable $dependency)
    {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('sulu_broken_function', $this->dependency->getTimestamp(...))];
    }
}
