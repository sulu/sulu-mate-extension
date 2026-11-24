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

class ContentTypeInfo
{
    public function __construct(
        private readonly TypeCatalog $catalog,
    ) {
    }

    /**
     * @return array{total: int, count: int, truncated: bool, items: list<array<string, mixed>>, note?: string}
     */
    #[MateTool(
        name: 'sulu-content-types',
        description: 'List the content types registered in Sulu with service id and class, for example single_page_selection or media_selection. Sulu 3 lists property resolvers (plain types such as text_line use the default resolver), Sulu 2.6 lists content types. Read from the installed sources, the kernel is not booted. Optional name_prefix and limit.',
    )]
    public function contentTypes(?string $name_prefix = null, int $limit = Listing::DEFAULT_LIMIT): array
    {
        return Listing::build(
            $this->catalog->contentTypes(),
            $name_prefix,
            $limit,
            'No content types found. Looked for the tags sulu_content.property_resolver (Sulu 3) and sulu.content.type (Sulu 2.6) in the installed sulu/* packages. Check that sulu/sulu is installed.',
        );
    }
}
