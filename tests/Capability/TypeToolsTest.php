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
use Sulu\MateExtension\Capability\ContentTypeInfo;
use Sulu\MateExtension\Capability\PropertyTypeInfo;
use Sulu\MateExtension\Runtime\ServiceTagReader;
use Sulu\MateExtension\Runtime\SourceLocator;
use Sulu\MateExtension\Runtime\TypeCatalog;
use Symfony\AI\Mate\Attribute\MateTool;

class TypeToolsTest extends TestCase
{
    private const FIXTURES = __DIR__.'/../Fixtures/Runtime';

    public function testContentTypesReadSulu3AndSulu2Tags(): void
    {
        $result = $this->contentTypes()->contentTypes();

        $byKey = [];
        foreach ($result['items'] as $item) {
            $byKey[$item['kind'].':'.$item['name']] = $item;
        }

        $this->assertSame(['name', 'kind', 'service_id', 'class'], array_keys($byKey['property_resolver:fixture_type']));
        $this->assertSame('fixture.resolver', $byKey['property_resolver:fixture_type']['service_id']);
        $this->assertSame(\Sulu\MateExtension\Tests\Fixtures\Runtime\Twig\FixtureResolver::class, $byKey['property_resolver:fixture_type']['class']);
        $this->assertSame('fixture.explicit', $byKey['property_resolver:explicit_type']['service_id']);
        $this->assertSame('sulu.content.type.text_line', $byKey['content_type:text_line']['service_id']);
        $this->assertSame(\Sulu\MateExtension\Tests\Fixtures\Runtime\Twig\FixtureResolver::class, $byKey['content_type:block']['class']);
        $this->assertArrayNotHasKey('property_resolver:fixture_mapped', $byKey);
        $this->assertSame(4, $result['total']);
    }

    public function testNamePrefixAndLimit(): void
    {
        $result = $this->contentTypes()->contentTypes('fixture_');

        $this->assertSame(1, $result['total']);
        $this->assertSame('fixture_type', $result['items'][0]['name']);

        $limited = $this->contentTypes()->contentTypes(null, 2);
        $this->assertSame(2, $limited['count']);
        $this->assertSame(4, $limited['total']);
        $this->assertTrue($limited['truncated']);
    }

    public function testContentTypesWithoutSourcesExplainWhy(): void
    {
        $info = new ContentTypeInfo($this->catalog([__DIR__.'/does-not-exist'], __DIR__.'/does-not-exist'));
        $result = $info->contentTypes();

        $this->assertSame(0, $result['total']);
        $this->assertSame([], $result['items']);
        $this->assertStringContainsString('sulu_content.property_resolver', $result['note'] ?? '');
        $this->assertStringContainsString('sulu.content.type', $result['note'] ?? '');
    }

    public function testPropertyTypesMergeAllSources(): void
    {
        $result = (new PropertyTypeInfo($this->catalog()))->propertyTypes();

        $byName = [];
        foreach ($result['items'] as $item) {
            $byName[$item['name']] = $item;
        }

        $this->assertContains('admin_core', $byName['text_line']['sources']);
        $this->assertContains('content_type', $byName['text_line']['sources']);
        $this->assertContains('template', $byName['text_line']['sources']);
        $this->assertGreaterThan(0, $byName['text_line']['used_in_templates']);
        $this->assertSame(['admin_metadata_mapper'], $byName['fixture_mapped']['sources']);
        $this->assertContains('admin_metadata_mapper', $byName['fixture_mapped_two']['sources']);
        $this->assertSame(['field_type_option'], $byName['fixture_selection']['sources']);
        $this->assertSame(['field_type_option'], $byName['fixture_single_selection']['sources']);
        $this->assertSame(['property_resolver'], $byName['explicit_type']['sources']);
        $this->assertSame(['template'], $byName['resource_locator']['sources']);
    }

    public function testPropertyTypePrefix(): void
    {
        $result = (new PropertyTypeInfo($this->catalog()))->propertyTypes('text_');

        $this->assertSame(['text_area', 'text_editor', 'text_line'], array_column($result['items'], 'name'));
    }

    public function testFieldTypeOptionsReader(): void
    {
        $contents = (string) file_get_contents(self::FIXTURES.'/sulu3/Bundle.php');

        $this->assertSame(
            ['selection' => ['fixture_selection'], 'single_selection' => ['fixture_single_selection']],
            (new ServiceTagReader())->readFieldTypeOptions($contents),
        );
    }

    public function testPhpReaderResolvesImportedClassAndTagAttributes(): void
    {
        $definitions = (new ServiceTagReader())->readPhp(
            (string) file_get_contents(self::FIXTURES.'/sulu3/services.php'),
            ['sulu_admin.property_metadata_mapper'],
        );

        $this->assertCount(2, $definitions);
        $this->assertSame('fixture.mapper', $definitions[0]['id']);
        $this->assertSame(\Sulu\MateExtension\Tests\Fixtures\Runtime\Twig\FixtureResolver::class, $definitions[0]['class']);
        $this->assertSame(['type' => 'fixture_mapped'], $definitions[0]['attributes']);
    }

    public function testToolsHaveNameAndDescription(): void
    {
        $tools = [
            [ContentTypeInfo::class, 'contentTypes', 'sulu-content-types'],
            [PropertyTypeInfo::class, 'propertyTypes', 'sulu-property-types'],
        ];

        foreach ($tools as [$class, $method, $name]) {
            $attributes = (new \ReflectionMethod($class, $method))->getAttributes(MateTool::class);

            $this->assertCount(1, $attributes);
            $tool = $attributes[0]->newInstance();
            $this->assertSame($name, $tool->name);
            $this->assertNotEmpty($tool->description);
        }
    }

    public function testToolsWorkAgainstTheInstalledSulu(): void
    {
        $projectDir = self::FIXTURES.'/project';
        $catalog = new TypeCatalog(new SourceLocator(), new ServiceTagReader(), $projectDir);

        $names = array_column((new ContentTypeInfo($catalog))->contentTypes()['items'], 'name');
        $this->assertNotEmpty($names);
    }

    private function contentTypes(): ContentTypeInfo
    {
        return new ContentTypeInfo($this->catalog());
    }

    /**
     * @param list<string>|null $roots
     */
    private function catalog(?array $roots = null, ?string $projectDir = null): TypeCatalog
    {
        return new TypeCatalog(
            new SourceLocator($roots ?? [self::FIXTURES.'/sulu3', self::FIXTURES.'/sulu2', self::FIXTURES.'/Twig']),
            new ServiceTagReader(),
            $projectDir ?? self::FIXTURES.'/project',
        );
    }
}
