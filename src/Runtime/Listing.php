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
 * Shared result shape of the list tools: filter by name prefix, cut at a limit.
 */
class Listing
{
    public const DEFAULT_LIMIT = 100;
    public const MAX_LIMIT = 500;

    /**
     * @param list<array<string, mixed>> $items must contain a string "name" key, sorted by the caller
     *
     * @return array{total: int, count: int, truncated: bool, items: list<array<string, mixed>>, note?: string}
     */
    public static function build(array $items, ?string $namePrefix, int $limit, ?string $emptyNote = null): array
    {
        if (null !== $namePrefix && '' !== $namePrefix) {
            $items = array_values(array_filter(
                $items,
                static fn (array $item): bool => str_starts_with((string) $item['name'], $namePrefix),
            ));
        }

        $limit = max(1, min($limit, self::MAX_LIMIT));
        $total = \count($items);
        $slice = \array_slice($items, 0, $limit);

        $result = [
            'total' => $total,
            'count' => \count($slice),
            'truncated' => $total > $limit,
            'items' => $slice,
        ];

        if (0 === $total && null !== $emptyNote) {
            $result['note'] = $emptyNote;
        }

        return $result;
    }
}
