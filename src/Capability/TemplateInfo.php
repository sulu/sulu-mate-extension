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

class TemplateInfo
{
    private const NAMESPACE_URI = 'http://schemas.sulu.io/template/template';

    /**
     * @var array<string, string>
     */
    private const DIRECTORIES = [
        'page' => '/config/templates/pages',
        'article' => '/config/templates/articles',
    ];

    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return list<array{
     *     key: string,
     *     type: string,
     *     view: string,
     *     controller: string,
     *     controller_file: string|null,
     *     titles: array<string, string>,
     *     twig_templates: list<string>,
     *     properties: list<array{
     *         name: string,
     *         type: string,
     *         mandatory: bool,
     *         multilingual: bool,
     *         block_types?: list<array{
     *             name: string,
     *             properties: list<array{
     *                 name: string,
     *                 type: string,
     *                 mandatory: bool,
     *                 multilingual: bool,
     *             }>
     *         }>
     *     }>
     * }>
     */
    #[McpTool('sulu-templates', 'Get the page and article template definitions of the Sulu installation')]
    public function templates(): array
    {
        $templates = [];

        foreach (self::DIRECTORIES as $type => $dir) {
            $path = $this->projectDir.$dir;

            if (!is_dir($path)) {
                continue;
            }

            $files = glob($path.'/*.xml');
            if (false === $files) {
                continue;
            }

            foreach ($files as $file) {
                $template = $this->parseTemplate($file, $type);
                if (null !== $template) {
                    $templates[] = $template;
                }
            }
        }

        return $templates;
    }

    /**
     * @return array{
     *     key: string,
     *     type: string,
     *     view: string,
     *     controller: string,
     *     controller_file: string|null,
     *     titles: array<string, string>,
     *     twig_templates: list<string>,
     *     properties: list<array{
     *         name: string,
     *         type: string,
     *         mandatory: bool,
     *         multilingual: bool,
     *         block_types?: list<array{
     *             name: string,
     *             properties: list<array{
     *                 name: string,
     *                 type: string,
     *                 mandatory: bool,
     *                 multilingual: bool,
     *             }>
     *         }>
     *     }>
     * }|null
     */
    private function parseTemplate(string $file, string $type): ?array
    {
        try {
            $xml = $this->loadXml($file);
            if (!$xml instanceof \SimpleXMLElement) {
                return null;
            }

            $ns = self::NAMESPACE_URI;
            $children = $xml->children($ns);

            $key = (string) $children->key;
            $view = (string) $children->view;
            $controller = (string) $children->controller;

            if ('' === $key) {
                return null;
            }

            return [
                'key' => $key,
                'type' => $type,
                'view' => $view,
                'controller' => $controller,
                'controller_file' => $this->resolveControllerFile($controller),
                'titles' => $this->parseTitles($children),
                'twig_templates' => $this->findTwigTemplates($view),
                'properties' => $this->parseProperties($children->properties),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolves a global block reference (e.g. "text_block") by loading
     * the corresponding template from config/templates/blocks/.
     *
     * @return array{
     *     name: string,
     *     properties: list<array{name: string, type: string, mandatory: bool, multilingual: bool}>
     * }|null
     */
    private function resolveGlobalBlock(string $ref): ?array
    {
        $blockFile = $this->projectDir.'/config/templates/blocks/'.$ref.'.xml';

        if (!is_file($blockFile)) {
            return null;
        }

        $xml = $this->loadXml($blockFile);
        if (!$xml instanceof \SimpleXMLElement) {
            return null;
        }

        $ns = self::NAMESPACE_URI;
        $children = $xml->children($ns);

        $propertiesNode = $children->properties;
        if (null === $propertiesNode) {
            return null;
        }

        $properties = [];
        foreach ($propertiesNode->children($ns) as $propNode) {
            if ('property' === $propNode->getName()) {
                $properties[] = $this->parseProperty($propNode);
            }
        }

        return [
            'name' => $ref,
            'properties' => $properties,
        ];
    }

    /**
     * Loads an XML file with XInclude support.
     */
    private function loadXml(string $file): ?\SimpleXMLElement
    {
        $dom = new \DOMDocument();
        $cwd = getcwd();
        @chdir(\dirname($file));

        $loaded = @$dom->load($file);

        @chdir((string) $cwd);

        if (!$loaded) {
            return null;
        }

        @$dom->xinclude();

        return simplexml_import_dom($dom);
    }

    /**
     * @return array<string, string>
     */
    private function parseTitles(\SimpleXMLElement $children): array
    {
        $titles = [];

        if (!property_exists($children, 'meta') || null === $children->meta) {
            return $titles;
        }

        $ns = self::NAMESPACE_URI;
        foreach ($children->meta->children($ns) as $element) {
            if ('title' !== $element->getName()) {
                continue;
            }

            $lang = (string) $element->attributes()['lang'];
            if ('' !== $lang) {
                $titles[$lang] = (string) $element;
            }
        }

        return $titles;
    }

    /**
     * Resolves a controller reference (e.g. "App\Controller\PageController::indexAction")
     * to its file path, relative to the project directory when possible.
     */
    private function resolveControllerFile(string $controller): ?string
    {
        if ('' === $controller) {
            return null;
        }

        $className = str_contains($controller, '::')
            ? strstr($controller, '::', true)
            : $controller;

        if (false === $className || '' === $className || !class_exists($className)) {
            return null;
        }

        $reflection = new \ReflectionClass($className);
        $filePath = $reflection->getFileName();
        if (false === $filePath) {
            return null;
        }

        $prefix = $this->projectDir.'/';
        if (str_starts_with($filePath, $prefix)) {
            return substr($filePath, \strlen($prefix));
        }

        return $filePath;
    }

    /**
     * Finds Twig template files for a given view.
     *
     * Sulu resolves views as "{view}.{format}.twig" (e.g. "pages/default.html.twig").
     *
     * @return list<string>
     */
    private function findTwigTemplates(string $view): array
    {
        if ('' === $view) {
            return [];
        }

        $pattern = $this->projectDir.'/templates/'.$view.'.*.twig';
        $files = glob($pattern);
        if (false === $files) {
            return [];
        }

        $templatesDir = $this->projectDir.'/templates/';
        $result = [];
        foreach ($files as $file) {
            $result[] = str_starts_with($file, $templatesDir)
                ? substr($file, \strlen($templatesDir))
                : basename($file);
        }

        sort($result);

        return $result;
    }

    /**
     * @return list<array{
     *     name: string,
     *     type: string,
     *     mandatory: bool,
     *     multilingual: bool,
     *     block_types?: list<array{
     *         name: string,
     *         properties: list<array{
     *             name: string,
     *             type: string,
     *             mandatory: bool,
     *             multilingual: bool,
     *         }>
     *     }>
     * }>
     */
    private function parseProperties(\SimpleXMLElement $propertiesNode): array
    {
        $result = [];
        $ns = self::NAMESPACE_URI;

        foreach ($propertiesNode->children($ns) as $child) {
            $name = $child->getName();

            if ('property' === $name) {
                $result[] = $this->parseProperty($child);
            } elseif ('section' === $name) {
                $sectionProperties = $child->children($ns)->properties;
                if (null !== $sectionProperties) {
                    foreach ($this->parseProperties($sectionProperties) as $prop) {
                        $result[] = $prop;
                    }
                }
            } elseif ('block' === $name) {
                $result[] = $this->parseProperty($child, 'block');
            }
        }

        return $result;
    }

    /**
     * @return array{
     *     name: string,
     *     type: string,
     *     mandatory: bool,
     *     multilingual: bool,
     *     block_types?: list<array{
     *         name: string,
     *         properties: list<array{
     *             name: string,
     *             type: string,
     *             mandatory: bool,
     *             multilingual: bool,
     *             block_types?: list<array{
     *                 name: string,
     *                 properties: list<array{
     *                     name: string,
     *                     type: string,
     *                     mandatory: bool,
     *                     multilingual: bool,
     *                 }>
     *             }>
     *         }>
     *     }>
     * }
     */
    private function parseProperty(\SimpleXMLElement $node, ?string $typeOverride = null): array
    {
        $attrs = $node->attributes();

        $result = [
            'name' => (string) ($attrs['name'] ?? ''),
            'type' => $typeOverride ?? (string) ($attrs['type'] ?? ''),
            'mandatory' => 'true' === (string) ($attrs['mandatory'] ?? 'false'),
            'multilingual' => 'false' !== (string) ($attrs['multilingual'] ?? 'true'),
        ];

        $types = $this->parseTypes($node);
        if ([] !== $types) {
            $result['block_types'] = $types;
        }

        return $result;
    }

    /**
     * Parses <types> children from a property or block element.
     * Handles inline types and global block references.
     *
     * @return list<array{
     *     name: string,
     *     properties: list<array{
     *         name: string,
     *         type: string,
     *         mandatory: bool,
     *         multilingual: bool,
     *         block_types?: list<array{
     *             name: string,
     *             properties: list<array{
     *                 name: string,
     *                 type: string,
     *                 mandatory: bool,
     *                 multilingual: bool,
     *             }>
     *         }>
     *     }>
     * }>
     */
    private function parseTypes(\SimpleXMLElement $node): array
    {
        $ns = self::NAMESPACE_URI;
        $typesNode = $node->children($ns)->types;

        if (0 === $typesNode->count()) {
            return [];
        }

        $blockTypes = [];
        foreach ($typesNode->children($ns) as $typeNode) {
            $typeAttrs = $typeNode->attributes();
            $typeName = (string) ($typeAttrs['name'] ?? '');
            $typeRef = (string) ($typeAttrs['ref'] ?? '');

            if ('' !== $typeRef) {
                $resolved = $this->resolveGlobalBlock($typeRef);
                if (null !== $resolved) {
                    $blockTypes[] = $resolved;
                }

                continue;
            }

            $typeProperties = [];
            $propsNode = $typeNode->children($ns)->properties;
            if (null !== $propsNode) {
                $typeProperties = $this->parseProperties($propsNode);
            }

            $blockTypes[] = [
                'name' => $typeName,
                'properties' => $typeProperties,
            ];
        }

        return $blockTypes;
    }
}
