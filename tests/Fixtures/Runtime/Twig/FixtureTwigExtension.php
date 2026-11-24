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
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class FixtureTwigExtension extends AbstractExtension
{
    public function __construct(private readonly \stdClass $dependency)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sulu_fixture_path', $this->path(...)),
            new TwigFunction('sulu_fixture_other', $this->path(...)),
        ];
    }

    public function getFilters(): array
    {
        return [new TwigFilter('sulu_fixture_filter', $this->path(...))];
    }

    public function getTests(): array
    {
        return [new TwigTest('sulu_fixture_test', $this->path(...))];
    }

    public function path(string $url, ?string $webspace = null, int ...$more): string
    {
        return $url.$webspace.json_encode($this->dependency).implode('', $more);
    }
}
