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

use Composer\InstalledVersions;
use Symfony\AI\Mate\Attribute\MateTool;

class SuluInfo
{
    /**
     * @return array<string, string>
     */
    #[MateTool(name: 'sulu-info', description: 'Get version information about all installed Sulu packages')]
    public function info(): array
    {
        $packages = [];
        foreach (InstalledVersions::getInstalledPackages() as $package) {
            if (!str_starts_with($package, 'sulu/')) {
                continue;
            }

            $version = InstalledVersions::getPrettyVersion($package);
            if (null === $version) {
                continue;
            }

            $packages[$package] = $version;
        }

        ksort($packages);

        return $packages;
    }
}
