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
 * Reads tagged service definitions from PHP and XML service configuration.
 *
 * The PHP reader understands the fluent ContainerConfigurator style that Sulu
 * uses ($services->set(...)->class(...)->tag(...)). It reads source text and
 * does not execute any file.
 */
class ServiceTagReader
{
    /**
     * @param list<string> $files
     * @param list<string> $tags  tag names to look for
     *
     * @return list<array{id: string, class: string|null, tag: string, attributes: array<string, string>, file: string}>
     */
    public function read(array $files, array $tags): array
    {
        $definitions = [];

        foreach ($files as $file) {
            $contents = @file_get_contents($file);
            if (false === $contents || !$this->mentionsTag($contents, $tags)) {
                continue;
            }

            $found = str_ends_with($file, '.xml')
                ? $this->readXml($contents, $tags)
                : $this->readPhp($contents, $tags);

            foreach ($found as $definition) {
                $definitions[] = $definition + ['file' => $file];
            }
        }

        return $definitions;
    }

    /**
     * @param list<string> $tags
     *
     * @return list<array{id: string, class: string|null, tag: string, attributes: array<string, string>}>
     */
    public function readXml(string $contents, array $tags): array
    {
        $previous = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string($contents);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (!$xml instanceof \SimpleXMLElement) {
            return [];
        }

        $definitions = [];
        $services = $xml->xpath('//*[local-name()="service"]');
        foreach ($services ?: [] as $service) {
            $id = (string) $service['id'];
            $class = (string) $service['class'];

            $serviceTags = $service->xpath('*[local-name()="tag"]');
            foreach ($serviceTags ?: [] as $tag) {
                $name = (string) $tag['name'];
                if (!\in_array($name, $tags, true)) {
                    continue;
                }

                $attributes = [];
                foreach ($tag->attributes() ?? [] as $key => $value) {
                    if ('name' !== $key) {
                        $attributes[(string) $key] = (string) $value;
                    }
                }

                $definitions[] = [
                    'id' => $id,
                    'class' => '' !== $class ? $class : null,
                    'tag' => $name,
                    'attributes' => $attributes,
                ];
            }
        }

        return $definitions;
    }

    /**
     * @param list<string> $tags
     *
     * @return list<array{id: string, class: string|null, tag: string, attributes: array<string, string>}>
     */
    public function readPhp(string $contents, array $tags): array
    {
        $namespace = '';
        if (1 === preg_match('/^namespace\s+([^;\s]+);/m', $contents, $match)) {
            $namespace = $match[1];
        }

        $imports = $this->imports($contents);
        $definitions = [];

        foreach ($this->statements($contents) as $statement) {
            $set = '/->set\(\s*(?:\'([^\']+)\'|([\w\\\\]+)::class)\s*(?:,\s*(?:([\w\\\\]+)::class|\'([^\']+)\'))?/';
            if (1 !== preg_match($set, $statement, $setMatch)) {
                continue;
            }

            $classReference = $this->classReference($statement, $setMatch);
            $class = '' !== $classReference ? $this->qualify($classReference, $namespace, $imports) : null;
            $id = '' !== ($setMatch[1] ?? '') ? $setMatch[1] : ($class ?? '');

            preg_match_all('/->(?:tag|addTag)\(\s*\'([^\']+)\'(?:\s*,\s*\[([^\]]*)\])?/', $statement, $tagMatches, \PREG_SET_ORDER);
            foreach ($tagMatches as $tagMatch) {
                if (!\in_array($tagMatch[1], $tags, true)) {
                    continue;
                }

                $attributes = [];
                preg_match_all('/\'([^\']+)\'\s*=>\s*\'([^\']*)\'/', $tagMatch[2] ?? '', $pairs, \PREG_SET_ORDER);
                foreach ($pairs as $pair) {
                    $attributes[$pair[1]] = $pair[2];
                }

                $definitions[] = [
                    'id' => $id,
                    'class' => $class,
                    'tag' => $tagMatch[1],
                    'attributes' => $attributes,
                ];
            }
        }

        return $definitions;
    }

    /**
     * Returns the keys below the base keys of a `field_type_options` array,
     * for example ['selection' => ['page_selection'], 'single_selection' => ['single_page_selection']].
     *
     * @return array<string, list<string>>
     */
    public function readFieldTypeOptions(string $contents): array
    {
        $result = [];
        $offset = 0;
        $length = \strlen($contents);

        while (false !== ($position = strpos($contents, "'field_type_options'", $offset))) {
            $offset = $position + 1;
            $start = strpos($contents, '[', $position);
            if (false === $start) {
                continue;
            }

            $depth = 0;
            $baseKey = null;

            for ($i = $start; $i < $length; ++$i) {
                $char = $contents[$i];

                if ("'" === $char) {
                    $end = $i + 1;
                    while ($end < $length && ("'" !== $contents[$end] || '\\' === $contents[$end - 1])) {
                        ++$end;
                    }

                    $string = substr($contents, $i + 1, $end - $i - 1);
                    $isKey = 1 === preg_match('/\G\s*=>/', $contents, $unused, 0, $end + 1);

                    if ($isKey && 1 === $depth) {
                        $baseKey = $string;
                    } elseif ($isKey && 2 === $depth && null !== $baseKey) {
                        $result[$baseKey][] = $string;
                    }

                    $i = $end;
                    continue;
                }

                if ('[' === $char) {
                    ++$depth;
                } elseif (']' === $char) {
                    --$depth;
                    if (1 === $depth) {
                        $baseKey = null;
                    }
                    if (0 === $depth) {
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array<int, string> $setMatch
     */
    private function classReference(string $statement, array $setMatch): string
    {
        if (1 === preg_match('/->class\(\s*(?:([\w\\\\]+)::class|\'([^\']+)\')\s*\)/', $statement, $classMatch)) {
            return '' !== $classMatch[1] ? $classMatch[1] : ($classMatch[2] ?? '');
        }

        foreach ([3, 4, 2] as $index) {
            if ('' !== ($setMatch[$index] ?? '')) {
                return $setMatch[$index];
            }
        }

        return '';
    }

    /**
     * @param list<string> $tags
     */
    private function mentionsTag(string $contents, array $tags): bool
    {
        foreach ($tags as $tag) {
            if (str_contains($contents, $tag)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Splits a PHP file into chunks that end at a line-final semicolon and contain a `->set(` call.
     *
     * @return list<string>
     */
    private function statements(string $contents): array
    {
        $parts = preg_split('/;\s*$/m', $contents) ?: [];

        return array_values(array_filter($parts, static fn (string $part): bool => str_contains($part, '->set(')));
    }

    /**
     * @return array<string, string>
     */
    private function imports(string $contents): array
    {
        preg_match_all('/^use\s+([\w\\\\]+?)(?:\s+as\s+(\w+))?;/m', $contents, $matches, \PREG_SET_ORDER);

        $imports = [];
        foreach ($matches as $match) {
            $alias = $match[2] ?? '';
            $short = '' !== $alias ? $alias : substr('\\'.$match[1], (int) strrpos('\\'.$match[1], '\\') + 1);
            $imports[$short] = $match[1];
        }

        return $imports;
    }

    /**
     * @param array<string, string> $imports
     */
    private function qualify(string $reference, string $namespace, array $imports): string
    {
        if (str_starts_with($reference, '\\')) {
            return ltrim($reference, '\\');
        }

        $parts = explode('\\', $reference, 2);
        if (isset($imports[$parts[0]])) {
            return $imports[$parts[0]].(isset($parts[1]) ? '\\'.$parts[1] : '');
        }

        if (str_contains($reference, '\\') || '' === $namespace) {
            return $reference;
        }

        return $namespace.'\\'.$reference;
    }
}
