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
use Sulu\MateExtension\Runtime\TypeCatalog;
use Symfony\AI\Mate\Attribute\MateTool;

class PropertyTypeInfo
{
    public function __construct(
        private readonly TypeCatalog $catalog,
    ) {
    }

    /**
     * @return array{total: int, count: int, truncated: bool, items: list<array<string, mixed>>, note?: string}
     */
    #[MateTool(
        name: 'sulu-property-types',
        description: 'List the property type names usable in <property type="..."> of Sulu template XML. Each entry shows where the type comes from (sources), its resolver or content type class if there is one, and how often the project templates use it. Optional name_prefix and limit.',
    )]
    public function propertyTypes(?string $name_prefix = null, int $limit = Listing::DEFAULT_LIMIT): array
    {
        return Listing::build(
            $this->catalog->propertyTypes(),
            $name_prefix,
            $limit,
            'No property types found. Check that sulu/sulu is installed.',
        );
    }
}
