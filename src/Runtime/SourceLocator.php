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

use Composer\InstalledVersions;

/**
 * Finds the source files of the installed sulu/* packages.
 *
 * The tools read service tags and class definitions from these files instead
 * of booting the host kernel. This works without a warm cache and without a
 * database.
 */
class SourceLocator
{
    private const SKIPPED_DIRECTORIES = [
        'node_modules', 'vendor', 'tests', 'Tests', 'Test', 'assets', 'public', 'js', 'Fixtures', 'translations',
    ];

    /**
     * @var array<string, list<string>>
     */
    private array $cache = [];

    /**
     * @param list<string>|null $roots directories to scan, defaults to the install paths of all sulu/* packages
     */
    public function __construct(
        private readonly ?array $roots = null,
    ) {
    }

    /**
     * @return list<string>
     */
    public function roots(): array
    {
        if (null !== $this->roots) {
            return $this->roots;
        }

        $roots = [];
        foreach (InstalledVersions::getInstalledPackages() as $package) {
            if (!str_starts_with($package, 'sulu/')) {
                continue;
            }

            $path = InstalledVersions::getInstallPath($package);
            if (null === $path || !is_dir($path)) {
                continue;
            }

            $real = realpath($path);
            if (false !== $real) {
                $roots[] = $real;
            }
        }

        return array_values(array_unique($roots));
    }

    /**
     * @return list<string>
     */
    public function files(string $extension): array
    {
        if (isset($this->cache[$extension])) {
            return $this->cache[$extension];
        }

        $files = [];
        foreach ($this->roots() as $root) {
            foreach (self::collect($root, $extension) as $file) {
                $files[$file] = $file;
            }
        }

        $files = array_values($files);
        sort($files);

        return $this->cache[$extension] = $files;
    }

    /**
     * @return list<string>
     */
    public static function collect(string $directory, string $extension): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $filter = new \RecursiveCallbackFilterIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            static function (\SplFileInfo $current): bool {
                if ($current->isDir()) {
                    return !\in_array($current->getFilename(), self::SKIPPED_DIRECTORIES, true);
                }

                return true;
            },
        );

        $files = [];
        foreach (new \RecursiveIteratorIterator($filter) as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $extension === $file->getExtension()) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }
}
