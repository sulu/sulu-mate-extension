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

class WebspaceInfo
{
    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return list<array{key: string, name: string, localizations: list<string>, default_templates: array<string, string>, portals: list<array{name: string, key: string, localizations: list<string>}>}>
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
     * @return array{key: string, name: string, localizations: list<string>, default_templates: array<string, string>, portals: list<array{name: string, key: string, localizations: list<string>}>}|null
     */
    private function loadWebspace(string $file): ?array
    {
        $content = file_get_contents($file);
        if (false === $content) {
            return null;
        }

        // Strip XML namespace declarations to simplify SimpleXML element access
        $content = preg_replace('/\sxmlns(?::[a-z]+)?="[^"]*"/i', '', $content);

        $xml = @simplexml_load_string((string) $content);
        if (false === $xml) {
            return null;
        }

        return [
            'key' => (string) $xml->key,
            'name' => (string) $xml->name,
            'localizations' => $this->parseLocalizations($xml->localizations),
            'default_templates' => $this->parseDefaultTemplates($xml->{'default-templates'}),
            'portals' => $this->parsePortals($xml->portals),
        ];
    }

    /**
     * @return list<string>
     */
    private function parseLocalizations(?\SimpleXMLElement $localizations): array
    {
        if (!$localizations instanceof \SimpleXMLElement) {
            return [];
        }

        $result = [];
        foreach ($localizations->localization ?? [] as $localization) {
            $result[] = $this->buildLocale($localization);

            // Handle nested localizations (Sulu uses nesting for fallback chains)
            foreach ($localization->localization ?? [] as $child) {
                $result[] = $this->buildLocale($child);
            }
        }

        return $result;
    }

    private function buildLocale(\SimpleXMLElement $localization): string
    {
        $locale = (string) $localization['language'];
        $country = (string) $localization['country'];

        if ('' !== $country) {
            $locale .= '_'.$country;
        }

        return $locale;
    }

    /**
     * @return array<string, string>
     */
    private function parseDefaultTemplates(?\SimpleXMLElement $templates): array
    {
        if (!$templates instanceof \SimpleXMLElement) {
            return [];
        }

        $result = [];
        foreach ($templates->{'default-template'} ?? [] as $template) {
            $result[(string) $template['type']] = (string) $template;
        }

        return $result;
    }

    /**
     * @return list<array{name: string, key: string, localizations: list<string>}>
     */
    private function parsePortals(?\SimpleXMLElement $portals): array
    {
        if (!$portals instanceof \SimpleXMLElement) {
            return [];
        }

        $result = [];
        foreach ($portals->portal ?? [] as $portal) {
            $result[] = [
                'name' => (string) $portal->name,
                'key' => (string) $portal->key,
                'localizations' => $this->parseLocalizations($portal->localizations),
            ];
        }

        return $result;
    }
}
