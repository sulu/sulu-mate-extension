<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\MateExtension\Tests\Fixtures\Runtime\sulu3;

class Bundle
{
    /**
     * @return array<string, mixed>
     */
    public function prependConfig(): array
    {
        return [
            'sulu_admin' => [
                'field_type_options' => [
                    'selection' => [
                        'fixture_selection' => [
                            'default_type' => 'list_overlay',
                            'types' => ['list_overlay' => ['icon' => 'su-x']],
                        ],
                    ],
                    'single_selection' => [
                        'fixture_single_selection' => [
                            'default_type' => 'list_overlay',
                        ],
                    ],
                ],
            ],
        ];
    }
}
