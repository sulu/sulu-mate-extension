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

use PHPUnit\Framework\TestCase;
use Sulu\MateExtension\Capability\TwigInfo;
use Sulu\MateExtension\Runtime\SourceLocator;
use Sulu\MateExtension\Runtime\TwigExtensionReader;
use Symfony\AI\Mate\Attribute\MateTool;

class TwigToolsTest extends TestCase
{
    private const FIXTURES = __DIR__.'/../Fixtures/Runtime';

    public function testTwigListReadsAllKindsWithoutConstructorArguments(): void
    {
        $result = $this->twig()->list(null, 'sulu_fixture_');

        $this->assertSame(
            ['filter:sulu_fixture_filter', 'function:sulu_fixture_other', 'function:sulu_fixture_path', 'test:sulu_fixture_test'],
            array_map(static fn (array $item): string => $item['kind'].':'.$item['name'], $result['items']),
        );
        $this->assertSame('(string $url, ?string $webspace = null, int ...$more): string', $result['items'][0]['signature']);
    }

    public function testTwigListFiltersByKindAndLimit(): void
    {
        $result = $this->twig()->list('filter');

        $this->assertSame(['sulu_fixture_filter'], array_column($result['items'], 'name'));

        $limited = $this->twig()->list(null, 'sulu_fixture_', 2);
        $this->assertSame(2, $limited['count']);
        $this->assertSame(4, $limited['total']);
        $this->assertTrue($limited['truncated']);

        $invalid = $this->twig()->list('nonsense');
        $this->assertSame([], $invalid['items']);
        $this->assertStringContainsString('Unknown kind', $invalid['note'] ?? '');
    }

    public function testTwigFallsBackToSourceWhenExtensionCannotBeBuilt(): void
    {
        $result = $this->twig()->verify('sulu_broken_function');

        $this->assertTrue($result['exists']);
        $this->assertNull($result['matches'][0]['signature']);
        $this->assertStringEndsWith('BrokenTwigExtension', $result['matches'][0]['class']);
    }

    public function testTwigVerifyExactNameAndSuggestions(): void
    {
        $found = $this->twig()->verify('sulu_fixture_path', 'function');
        $this->assertTrue($found['exists']);
        $this->assertCount(1, $found['matches']);

        $wrongKind = $this->twig()->verify('sulu_fixture_path', 'filter');
        $this->assertFalse($wrongKind['exists']);

        $typo = $this->twig()->verify('sulu_fixture_pth');
        $this->assertFalse($typo['exists']);
        $this->assertContains('sulu_fixture_path', $typo['suggestions']);
    }

    public function testTwigWithoutSourcesExplainsWhy(): void
    {
        $info = new TwigInfo(new SourceLocator([__DIR__.'/does-not-exist']), new TwigExtensionReader());
        $result = $info->list();

        $this->assertSame(0, $result['total']);
        $this->assertNotEmpty($result['note'] ?? '');
    }

    public function testToolsHaveNameAndDescription(): void
    {
        foreach ([['list', 'sulu-twig-list'], ['verify', 'sulu-twig-verify']] as [$method, $name]) {
            $attributes = (new \ReflectionMethod(TwigInfo::class, $method))->getAttributes(MateTool::class);

            $this->assertCount(1, $attributes);
            $tool = $attributes[0]->newInstance();
            $this->assertSame($name, $tool->name);
            $this->assertNotEmpty($tool->description);
        }
    }

    public function testToolsWorkAgainstTheInstalledSulu(): void
    {
        $twig = new TwigInfo(new SourceLocator(), new TwigExtensionReader());

        $this->assertNotEmpty($twig->list(null, 'sulu_')['items']);
    }

    private function twig(): TwigInfo
    {
        return new TwigInfo(new SourceLocator([self::FIXTURES]), new TwigExtensionReader());
    }
}
