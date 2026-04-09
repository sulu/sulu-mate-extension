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
use Sulu\MateExtension\Capability\WebspaceInfo;

class WebspaceInfoTest extends TestCase
{
    public function testWebspacesReturnsEmptyArrayWhenDirectoryDoesNotExist(): void
    {
        $info = new WebspaceInfo('/nonexistent/path');

        $this->assertSame([], $info->webspaces());
    }

    public function testWebspacesReturnsEmptyArrayWhenNoXmlFiles(): void
    {
        $info = new WebspaceInfo(__DIR__);

        $this->assertSame([], $info->webspaces());
    }

    public function testWebspacesParsesFixture(): void
    {
        $fixtureDir = \dirname(__DIR__).'/Fixtures';

        // The fixture is at tests/Fixtures/example.xml but the tool looks in config/webspaces/
        // Create a temporary structure
        $tmpDir = sys_get_temp_dir().'/sulu-mate-test-'.uniqid();
        $webspacesDir = $tmpDir.'/config/webspaces';
        mkdir($webspacesDir, 0777, true);
        copy($fixtureDir.'/example.xml', $webspacesDir.'/example.xml');

        try {
            $info = new WebspaceInfo($tmpDir);
            $result = $info->webspaces();

            $this->assertCount(1, $result);

            $webspace = $result[0];
            $this->assertSame('example', $webspace['key']);
            $this->assertSame('Example', $webspace['name']);
            $this->assertSame(['en', 'en_us', 'de'], $webspace['localizations']);
            $this->assertSame([
                'page' => 'default',
                'homepage' => 'homepage',
            ], $webspace['default_templates']);

            $this->assertCount(1, $webspace['portals']);
            $this->assertSame('Example Portal', $webspace['portals'][0]['name']);
            $this->assertSame('example', $webspace['portals'][0]['key']);
            $this->assertSame(['en', 'de'], $webspace['portals'][0]['localizations']);
        } finally {
            unlink($webspacesDir.'/example.xml');
            rmdir($webspacesDir);
            rmdir($tmpDir.'/config');
            rmdir($tmpDir);
        }
    }

    public function testWebspacesSkipsInvalidXml(): void
    {
        $tmpDir = sys_get_temp_dir().'/sulu-mate-test-'.uniqid();
        $webspacesDir = $tmpDir.'/config/webspaces';
        mkdir($webspacesDir, 0777, true);
        file_put_contents($webspacesDir.'/broken.xml', 'not valid xml');

        try {
            $info = new WebspaceInfo($tmpDir);

            $this->assertSame([], $info->webspaces());
        } finally {
            unlink($webspacesDir.'/broken.xml');
            rmdir($webspacesDir);
            rmdir($tmpDir.'/config');
            rmdir($tmpDir);
        }
    }
}
