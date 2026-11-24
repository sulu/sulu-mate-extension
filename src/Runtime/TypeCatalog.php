<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\MateExtension\Runtime;

/**
 * Collects content types and property types from the installed Sulu sources.
 *
 * Sulu 3 registers property resolvers (tag sulu_content.property_resolver).
 * Sulu 2.6 registers content types (tag sulu.content.type with an alias).
 * Both are read, whichever exists in the installation.
 */
class TypeCatalog
{
    public const TAG_PROPERTY_RESOLVER = 'sulu_content.property_resolver';
    public const TAG_CONTENT_TYPE = 'sulu.content.type';
    public const TAG_METADATA_MAPPER = 'sulu_admin.property_metadata_mapper';

    private const RESOLVER_INTERFACE = \Sulu\Content\Application\PropertyResolver\Resolver\PropertyResolverInterface::class;

    /**
     * Field types that the Sulu admin ships for forms and templates.
     */
    private const CORE_FIELD_TYPES = [
        'block', 'changelog_line', 'checkbox', 'color', 'date', 'datetime', 'email', 'heading',
        'link', 'number', 'password_confirmation', 'phone', 'qrcode', 'select', 'single_icon_selection',
        'single_select', 'smart_content', 'text_area', 'text_editor', 'text_line', 'time', 'url',
    ];

    /**
     * @var list<array{id: string, class: string|null, tag: string, attributes: array<string, string>, file: string}>|null
     */
    private ?array $definitions = null;

    public function __construct(
        private readonly SourceLocator $locator,
        private readonly ServiceTagReader $reader,
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return list<array{name: string, kind: string, service_id: string|null, class: string|null}>
     */
    public function contentTypes(): array
    {
        $types = [];

        foreach ($this->definitions() as $definition) {
            if (self::TAG_PROPERTY_RESOLVER === $definition['tag']) {
                $name = $definition['attributes']['type'] ?? $this->resolverType($definition['class']);
                $kind = 'property_resolver';
            } elseif (self::TAG_CONTENT_TYPE === $definition['tag']) {
                $name = $definition['attributes']['alias'] ?? null;
                $kind = 'content_type';
            } else {
                continue;
            }

            if (null === $name || '' === $name) {
                continue;
            }

            $types[$kind.':'.$name] ??= [
                'name' => $name,
                'kind' => $kind,
                'service_id' => '' !== $definition['id'] ? $definition['id'] : null,
                'class' => $definition['class'],
            ];
        }

        foreach ($this->autoconfiguredResolvers() as $name => $class) {
            $types['property_resolver:'.$name] ??= [
                'name' => $name,
                'kind' => 'property_resolver',
                'service_id' => null,
                'class' => $class,
            ];
        }

        $types = array_values($types);
        usort($types, static fn (array $a, array $b): int => [$a['name'], $a['kind']] <=> [$b['name'], $b['kind']]);

        return $types;
    }

    /**
     * @return list<array{name: string, sources: list<string>, class: string|null, used_in_templates: int}>
     */
    public function propertyTypes(): array
    {
        /** @var array<string, list<string>> $sources */
        $sources = [];
        /** @var array<string, string> $classes */
        $classes = [];
        $add = static function (string $name, string $source, ?string $class = null) use (&$sources, &$classes): void {
            $sources[$name] ??= [];
            if (!\in_array($source, $sources[$name], true)) {
                $sources[$name][] = $source;
            }
            if (null !== $class) {
                $classes[$name] ??= $class;
            }
        };
        $usage = $this->usedInTemplates();

        foreach (self::CORE_FIELD_TYPES as $name) {
            $add($name, 'admin_core');
        }

        foreach ($this->contentTypes() as $contentType) {
            $add($contentType['name'], $contentType['kind'], $contentType['class']);
        }

        foreach ($this->definitions() as $definition) {
            if (self::TAG_METADATA_MAPPER === $definition['tag'] && isset($definition['attributes']['type'])) {
                $add($definition['attributes']['type'], 'admin_metadata_mapper');
            }
        }

        foreach ($this->fieldTypeOptions() as $names) {
            foreach ($names as $name) {
                $add($name, 'field_type_option');
            }
        }

        foreach (array_keys($usage) as $name) {
            $add($name, 'template');
        }

        ksort($sources);

        $types = [];
        foreach ($sources as $name => $typeSources) {
            $types[] = [
                'name' => $name,
                'sources' => $typeSources,
                'class' => $classes[$name] ?? null,
                'used_in_templates' => $usage[$name] ?? 0,
            ];
        }

        return $types;
    }

    /**
     * @return list<array{id: string, class: string|null, tag: string, attributes: array<string, string>, file: string}>
     */
    private function definitions(): array
    {
        if (null !== $this->definitions) {
            return $this->definitions;
        }

        $files = [...$this->locator->files('php'), ...$this->locator->files('xml')];
        foreach (['php', 'xml'] as $extension) {
            $files = [...$files, ...SourceLocator::collect($this->projectDir.'/config', $extension)];
        }

        return $this->definitions = $this->reader->read($files, [
            self::TAG_PROPERTY_RESOLVER,
            self::TAG_CONTENT_TYPE,
            self::TAG_METADATA_MAPPER,
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    private function fieldTypeOptions(): array
    {
        $result = [];

        foreach ($this->locator->files('php') as $file) {
            $contents = @file_get_contents($file);
            if (false === $contents || !str_contains($contents, "'field_type_options'")) {
                continue;
            }

            foreach ($this->reader->readFieldTypeOptions($contents) as $base => $names) {
                $result[$base] = array_values(array_unique([...($result[$base] ?? []), ...$names]));
            }
        }

        return $result;
    }

    /**
     * @return array<string, int>
     */
    private function usedInTemplates(): array
    {
        $counts = [];

        foreach (SourceLocator::collect($this->projectDir.'/config/templates', 'xml') as $file) {
            $contents = @file_get_contents($file);
            if (false === $contents) {
                continue;
            }

            preg_match_all('/<property\b[^>]*?\btype="([^"]+)"/', $contents, $matches);
            foreach ($matches[1] as $type) {
                $counts[$type] = ($counts[$type] ?? 0) + 1;
            }
        }

        return $counts;
    }

    /**
     * Resolvers in the project that are tagged through autoconfiguration have no tag in any file.
     *
     * @return array<string, class-string>
     */
    private function autoconfiguredResolvers(): array
    {
        $resolvers = [];

        foreach (SourceLocator::collect($this->projectDir.'/src', 'php') as $file) {
            $contents = @file_get_contents($file);
            if (false === $contents || !str_contains($contents, 'PropertyResolverInterface')) {
                continue;
            }

            $namespace = 1 === preg_match('/^namespace\s+([^;\s]+);/m', $contents, $ns) ? $ns[1].'\\' : '';
            if (1 !== preg_match('/^(?:final\s+)?class\s+(\w+)/m', $contents, $class)) {
                continue;
            }

            $className = $namespace.$class[1];
            if (!class_exists($className) || !is_subclass_of($className, self::RESOLVER_INTERFACE)) {
                continue;
            }

            $type = $this->resolverType($className);
            if (null !== $type) {
                $resolvers[$type] = $className;
            }
        }

        return $resolvers;
    }

    private function resolverType(?string $class): ?string
    {
        if (null === $class || !class_exists($class) || !method_exists($class, 'getType')) {
            return null;
        }

        try {
            $method = new \ReflectionMethod($class, 'getType');
            $value = $method->isStatic()
                ? $method->invoke(null)
                : $method->invoke((new \ReflectionClass($class))->newInstanceWithoutConstructor());
        } catch (\Throwable) {
            return null;
        }

        return \is_string($value) && '' !== $value ? $value : null;
    }
}
