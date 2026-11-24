<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\MateExtension\Tests\Capability;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sulu\MateExtension\Capability\SuluInfo;
use Sulu\MateExtension\Capability\TemplateInfo;
use Sulu\MateExtension\Capability\WebspaceInfo;
use Symfony\AI\Mate\Attribute\MateTool;

class MateToolAttributeTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string, string, string}>
     */
    public static function provideTools(): iterable
    {
        yield 'info' => [SuluInfo::class, 'info', 'sulu-info'];
        yield 'webspaces' => [WebspaceInfo::class, 'webspaces', 'sulu-webspaces'];
        yield 'templates' => [TemplateInfo::class, 'templates', 'sulu-templates'];
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('provideTools')]
    public function testToolHasNameAndDescription(string $class, string $method, string $name): void
    {
        $attributes = (new \ReflectionMethod($class, $method))->getAttributes(MateTool::class);

        $this->assertCount(1, $attributes);
        $tool = $attributes[0]->newInstance();
        $this->assertSame($name, $tool->name);
        $this->assertNotEmpty($tool->description);
    }
}
