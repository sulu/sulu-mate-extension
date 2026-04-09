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
use Sulu\MateExtension\Capability\SuluInfo;

class SuluInfoTest extends TestCase
{
    public function testInfoReturnsInstalledSuluPackages(): void
    {
        $info = new SuluInfo();
        $result = $info->info();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sulu/sulu-mate-extension', $result);

        foreach ($result as $package => $version) {
            $this->assertStringStartsWith('sulu/', $package);
            $this->assertIsString($version);
        }
    }

    public function testInfoIsSortedAlphabetically(): void
    {
        $info = new SuluInfo();
        $result = $info->info();

        $keys = array_keys($result);
        $sorted = $keys;
        sort($sorted);

        $this->assertSame($sorted, $keys);
    }
}
