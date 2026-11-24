<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\MateExtension\Capability;

use Sulu\MateExtension\Runtime\Listing;
use Sulu\MateExtension\Runtime\SourceLocator;
use Sulu\MateExtension\Runtime\TwigExtensionReader;
use Symfony\AI\Mate\Attribute\MateTool;

class TwigInfo
{
    private const KINDS = ['function', 'filter', 'test'];

    public function __construct(
        private readonly SourceLocator $locator,
        private readonly TwigExtensionReader $reader,
    ) {
    }

    /**
     * @return array{total: int, count: int, truncated: bool, items: list<array<string, mixed>>, note?: string}
     */
    #[MateTool(
        name: 'sulu-twig-list',
        description: 'List the Twig functions, filters and tests that the installed Sulu packages register (sulu_* helpers) with class and signature. kind is function, filter or test, empty for all. Read from the installed sources, the kernel is not booted. Optional name_prefix and limit.',
    )]
    public function list(?string $kind = null, ?string $name_prefix = null, int $limit = Listing::DEFAULT_LIMIT): array
    {
        if (null !== $kind && '' !== $kind && !\in_array($kind, self::KINDS, true)) {
            return ['total' => 0, 'count' => 0, 'truncated' => false, 'items' => [], 'note' => 'Unknown kind "'.$kind.'". Use function, filter or test.'];
        }

        $items = $this->extensions();
        if (null !== $kind && '' !== $kind) {
            $items = array_values(array_filter($items, static fn (array $item): bool => $item['kind'] === $kind));
        }

        return Listing::build(
            $items,
            $name_prefix,
            $limit,
            'No Sulu Twig extensions found. Check that sulu/sulu is installed.',
        );
    }

    /**
     * @return array{name: string, exists: bool, matches: list<array<string, mixed>>, suggestions: list<string>}
     */
    #[MateTool(
        name: 'sulu-twig-verify',
        description: 'Check whether an exact Twig function, filter or test name is registered by Sulu, for example sulu_content_path. Call this before using a sulu_* helper in a template. Returns the matches, or similar names when there is none. kind is optional.',
    )]
    public function verify(string $name, ?string $kind = null): array
    {
        $items = $this->extensions();
        $matches = array_values(array_filter(
            $items,
            static fn (array $item): bool => $item['name'] === $name && (null === $kind || '' === $kind || $item['kind'] === $kind),
        ));

        $suggestions = [];
        if ([] === $matches) {
            $scored = [];
            foreach ($items as $item) {
                $candidate = $item['name'];
                $distance = levenshtein($name, $candidate);
                if (str_contains($candidate, $name) || str_contains($name, $candidate) || $distance <= 3) {
                    $scored[$candidate] = $distance;
                }
            }
            asort($scored);
            $suggestions = \array_slice(array_map(strval(...), array_keys($scored)), 0, 5);
        }

        return [
            'name' => $name,
            'exists' => [] !== $matches,
            'matches' => $matches,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * @return list<array{name: string, kind: string, class: string, callable: string|null, signature: string|null}>
     */
    private function extensions(): array
    {
        return $this->reader->read($this->locator->files('php'));
    }
}
