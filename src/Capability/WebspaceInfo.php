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

use Mcp\Capability\Attribute\McpTool;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Loader\XmlFileLoader10;
use Sulu\Component\Webspace\Loader\XmlFileLoader11;
use Sulu\Component\Webspace\Portal;
use Sulu\Component\Webspace\Webspace;
use Symfony\Component\Config\FileLocator;

class WebspaceInfo
{
    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return list<array{
     *     key: string,
     *     name: string,
     *     localizations: list<string>,
     *     default_templates: array<string, string>,
     *     portals: list<array{
     *         name: string,
     *         key: string,
     *         localizations: list<string>
     *     }>
     * }>
     */
    #[McpTool('sulu-webspaces', 'Get the webspace configuration of the Sulu installation')]
    public function webspaces(): array
    {
        $webspacesDir = $this->projectDir.'/config/webspaces';

        if (!is_dir($webspacesDir)) {
            return [];
        }

        $files = glob($webspacesDir.'/*.xml');
        if (false === $files) {
            return [];
        }

        $webspaces = [];
        foreach ($files as $file) {
            $webspace = $this->loadWebspace($file);
            if (null !== $webspace) {
                $webspaces[] = $webspace;
            }
        }

        return $webspaces;
    }

    /**
     * @return array{
     *     key: string,
     *     name: string,
     *     localizations: list<string>,
     *     default_templates: array<string, string>,
     *     portals: list<array{
     *         name: string,
     *         key: string,
     *         localizations: list<string>
     *     }>
     * }|null
     */
    private function loadWebspace(string $file): ?array
    {
        $webspace = $this->loadWebspaceDefinition($file);
        if (!$webspace instanceof Webspace) {
            return null;
        }

        return [
            'key' => $webspace->getKey(),
            'name' => $webspace->getName(),
            'localizations' => $this->formatLocalizations($webspace->getAllLocalizations()),
            'default_templates' => $webspace->getDefaultTemplates(),
            'portals' => $this->parsePortals($webspace->getPortals()),
        ];
    }

    private function loadWebspaceDefinition(string $file): ?Webspace
    {
        $locator = new FileLocator();
        $loaders = [
            new XmlFileLoader11($locator),
            new XmlFileLoader10($locator),
        ];

        foreach ($loaders as $loader) {
            try {
                if (!$loader->supports($file)) {
                    continue;
                }

                return $loader->load($file);
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    /**
     * @param iterable<Localization> $localizations
     *
     * @return list<string>
     */
    private function formatLocalizations(iterable $localizations): array
    {
        $result = [];
        foreach ($localizations as $localization) {
            $result[] = $localization->getLocale();
        }

        return $result;
    }

    /**
     * @param iterable<Portal> $portals
     *
     * @return list<array{
     *     name: string,
     *     key: string,
     *     localizations: list<string>
     * }>
     */
    private function parsePortals(iterable $portals): array
    {
        $result = [];
        foreach ($portals as $portal) {
            if (!$portal instanceof Portal) {
                continue;
            }

            $result[] = [
                'name' => $portal->getName(),
                'key' => $portal->getKey(),
                'localizations' => $this->formatLocalizations($portal->getLocalizations()),
            ];
        }

        return $result;
    }
}
