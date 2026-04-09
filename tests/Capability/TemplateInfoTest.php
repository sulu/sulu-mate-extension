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
use Sulu\MateExtension\Capability\TemplateInfo;

class TemplateInfoTest extends TestCase
{
    public function testTemplatesReturnsEmptyArrayWhenDirectoriesDoNotExist(): void
    {
        $info = new TemplateInfo('/nonexistent/path');

        $this->assertSame([], $info->templates());
    }

    public function testTemplatesReturnsEmptyArrayWhenNoXmlFiles(): void
    {
        $info = new TemplateInfo(__DIR__);

        $this->assertSame([], $info->templates());
    }

    public function testTemplatesSkipsInvalidXml(): void
    {
        $tmpDir = sys_get_temp_dir().'/sulu-mate-test-'.uniqid();
        $pagesDir = $tmpDir.'/config/templates/pages';
        mkdir($pagesDir, 0777, true);
        file_put_contents($pagesDir.'/broken.xml', 'not valid xml');

        try {
            $info = new TemplateInfo($tmpDir);

            $this->assertSame([], $info->templates());
        } finally {
            unlink($pagesDir.'/broken.xml');
            rmdir($pagesDir);
            rmdir($tmpDir.'/config/templates');
            rmdir($tmpDir.'/config');
            rmdir($tmpDir);
        }
    }

    public function testTemplatesParsesPageTemplate(): void
    {
        $tmpDir = sys_get_temp_dir().'/sulu-mate-test-'.uniqid();
        $pagesDir = $tmpDir.'/config/templates/pages';
        $twigDir = $tmpDir.'/templates/pages';
        mkdir($pagesDir, 0777, true);
        mkdir($twigDir, 0777, true);
        copy(\dirname(__DIR__).'/Fixtures/template_default.xml', $pagesDir.'/default.xml');
        file_put_contents($twigDir.'/default.html.twig', '{# html #}');
        file_put_contents($twigDir.'/default.json.twig', '{# json #}');

        try {
            $info = new TemplateInfo($tmpDir);
            $result = $info->templates();

            $this->assertCount(1, $result);

            $template = $result[0];
            $this->assertSame('default', $template['key']);
            $this->assertSame('page', $template['type']);
            $this->assertSame('pages/default', $template['view']);
            $this->assertSame('Sulu\Bundle\WebsiteBundle\Controller\DefaultController::indexAction', $template['controller']);
            $this->assertNull($template['controller_file']);
            $this->assertSame(['en' => 'Default', 'de' => 'Standard'], $template['titles']);
            $this->assertSame(['pages/default.html.twig', 'pages/default.json.twig'], $template['twig_templates']);

            // Properties: title, url (direct), article (from section), blocks (from section)
            $this->assertCount(4, $template['properties']);

            // title property
            $this->assertSame('title', $template['properties'][0]['name']);
            $this->assertSame('text_line', $template['properties'][0]['type']);
            $this->assertTrue($template['properties'][0]['mandatory']);
            $this->assertTrue($template['properties'][0]['multilingual']);

            // url property (multilingual=false)
            $this->assertSame('url', $template['properties'][1]['name']);
            $this->assertSame('resource_locator', $template['properties'][1]['type']);
            $this->assertFalse($template['properties'][1]['multilingual']);

            // article property (flattened from section)
            $this->assertSame('article', $template['properties'][2]['name']);
            $this->assertSame('text_editor', $template['properties'][2]['type']);
            $this->assertFalse($template['properties'][2]['mandatory']);
            $this->assertTrue($template['properties'][2]['multilingual']);

            // blocks (flattened from section)
            $this->assertSame('blocks', $template['properties'][3]['name']);
            $this->assertSame('block', $template['properties'][3]['type']);
            $this->assertArrayHasKey('block_types', $template['properties'][3]);

            $blockTypes = $template['properties'][3]['block_types'];
            $this->assertCount(2, $blockTypes);

            $this->assertSame('editor', $blockTypes[0]['name']);
            $this->assertCount(1, $blockTypes[0]['properties']);
            $this->assertSame('text', $blockTypes[0]['properties'][0]['name']);
            $this->assertSame('text_editor', $blockTypes[0]['properties'][0]['type']);

            $this->assertSame('image', $blockTypes[1]['name']);
            $this->assertCount(2, $blockTypes[1]['properties']);
            $this->assertSame('media', $blockTypes[1]['properties'][0]['name']);
            $this->assertTrue($blockTypes[1]['properties'][0]['mandatory']);
            $this->assertSame('caption', $blockTypes[1]['properties'][1]['name']);
            $this->assertFalse($blockTypes[1]['properties'][1]['mandatory']);
        } finally {
            unlink($pagesDir.'/default.xml');
            unlink($twigDir.'/default.html.twig');
            unlink($twigDir.'/default.json.twig');
            rmdir($twigDir);
            rmdir($tmpDir.'/templates');
            rmdir($pagesDir);
            rmdir($tmpDir.'/config/templates');
            rmdir($tmpDir.'/config');
            rmdir($tmpDir);
        }
    }

    public function testTemplatesParsesArticleTemplate(): void
    {
        $tmpDir = sys_get_temp_dir().'/sulu-mate-test-'.uniqid();
        $articlesDir = $tmpDir.'/config/templates/articles';
        mkdir($articlesDir, 0777, true);
        copy(\dirname(__DIR__).'/Fixtures/template_article.xml', $articlesDir.'/blog.xml');

        try {
            $info = new TemplateInfo($tmpDir);
            $result = $info->templates();

            $this->assertCount(1, $result);
            $this->assertSame('blog', $result[0]['key']);
            $this->assertSame('article', $result[0]['type']);
            $this->assertSame('articles/blog', $result[0]['view']);
            $this->assertSame('', $result[0]['controller']);
            $this->assertNull($result[0]['controller_file']);
            $this->assertSame(['en' => 'Blog Article'], $result[0]['titles']);
            $this->assertSame([], $result[0]['twig_templates']);
        } finally {
            unlink($articlesDir.'/blog.xml');
            rmdir($articlesDir);
            rmdir($tmpDir.'/config/templates');
            rmdir($tmpDir.'/config');
            rmdir($tmpDir);
        }
    }

    public function testTemplatesMergesBothDirectories(): void
    {
        $tmpDir = sys_get_temp_dir().'/sulu-mate-test-'.uniqid();
        $pagesDir = $tmpDir.'/config/templates/pages';
        $articlesDir = $tmpDir.'/config/templates/articles';
        mkdir($pagesDir, 0777, true);
        mkdir($articlesDir, 0777, true);
        copy(\dirname(__DIR__).'/Fixtures/template_default.xml', $pagesDir.'/default.xml');
        copy(\dirname(__DIR__).'/Fixtures/template_article.xml', $articlesDir.'/blog.xml');

        try {
            $info = new TemplateInfo($tmpDir);
            $result = $info->templates();

            $this->assertCount(2, $result);

            $types = array_column($result, 'type');
            $this->assertContains('page', $types);
            $this->assertContains('article', $types);
        } finally {
            unlink($pagesDir.'/default.xml');
            unlink($articlesDir.'/blog.xml');
            rmdir($pagesDir);
            rmdir($articlesDir);
            rmdir($tmpDir.'/config/templates');
            rmdir($tmpDir.'/config');
            rmdir($tmpDir);
        }
    }

    public function testTemplatesHandlesImageMapAndNestedBlocks(): void
    {
        $tmpDir = sys_get_temp_dir().'/sulu-mate-test-'.uniqid();
        $pagesDir = $tmpDir.'/config/templates/pages';
        mkdir($pagesDir, 0777, true);
        copy(\dirname(__DIR__).'/Fixtures/template_complex.xml', $pagesDir.'/complex.xml');

        try {
            $info = new TemplateInfo($tmpDir);
            $result = $info->templates();

            $this->assertCount(1, $result);
            $properties = $result[0]['properties'];
            $this->assertCount(3, $properties);

            // image_map with types (like a block but type="image_map")
            $imageMap = $properties[1];
            $this->assertSame('image_map', $imageMap['name']);
            $this->assertSame('image_map', $imageMap['type']);
            $this->assertArrayHasKey('block_types', $imageMap);
            $this->assertCount(2, $imageMap['block_types']);
            $this->assertSame('basic', $imageMap['block_types'][0]['name']);
            $this->assertCount(1, $imageMap['block_types'][0]['properties']);
            $this->assertSame('advanced', $imageMap['block_types'][1]['name']);
            $this->assertCount(2, $imageMap['block_types'][1]['properties']);

            // Outer block
            $blocks = $properties[2];
            $this->assertSame('blocks', $blocks['name']);
            $this->assertSame('block', $blocks['type']);
            $this->assertArrayHasKey('block_types', $blocks);
            $this->assertCount(2, $blocks['block_types']);

            // Nested block inside "nested" type
            $nestedType = $blocks['block_types'][1];
            $this->assertSame('nested', $nestedType['name']);
            $this->assertCount(1, $nestedType['properties']);

            $innerBlock = $nestedType['properties'][0];
            $this->assertSame('inner_blocks', $innerBlock['name']);
            $this->assertSame('block', $innerBlock['type']);
            $this->assertArrayHasKey('block_types', $innerBlock);
            $this->assertCount(1, $innerBlock['block_types']);
            $this->assertSame('inner_text', $innerBlock['block_types'][0]['name']);
            $this->assertSame('inner_content', $innerBlock['block_types'][0]['properties'][0]['name']);
        } finally {
            unlink($pagesDir.'/complex.xml');
            rmdir($pagesDir);
            rmdir($tmpDir.'/config/templates');
            rmdir($tmpDir.'/config');
            rmdir($tmpDir);
        }
    }

    public function testTemplatesResolvesGlobalBlockRef(): void
    {
        $tmpDir = sys_get_temp_dir().'/sulu-mate-test-'.uniqid();
        $pagesDir = $tmpDir.'/config/templates/pages';
        $blocksDir = $tmpDir.'/config/templates/blocks';
        mkdir($pagesDir, 0777, true);
        mkdir($blocksDir, 0777, true);
        copy(\dirname(__DIR__).'/Fixtures/template_with_ref.xml', $pagesDir.'/with-ref.xml');
        copy(\dirname(__DIR__).'/Fixtures/text_block.xml', $blocksDir.'/text_block.xml');

        try {
            $info = new TemplateInfo($tmpDir);
            $result = $info->templates();

            $this->assertCount(1, $result);

            $blocks = $result[0]['properties'][1];
            $this->assertSame('blocks', $blocks['name']);
            $this->assertSame('block', $blocks['type']);
            $this->assertArrayHasKey('block_types', $blocks);
            $this->assertCount(2, $blocks['block_types']);

            // Inline type
            $this->assertSame('editor', $blocks['block_types'][0]['name']);
            $this->assertCount(1, $blocks['block_types'][0]['properties']);

            // Global block ref
            $this->assertSame('text_block', $blocks['block_types'][1]['name']);
            $this->assertCount(2, $blocks['block_types'][1]['properties']);
            $this->assertSame('title', $blocks['block_types'][1]['properties'][0]['name']);
            $this->assertSame('description', $blocks['block_types'][1]['properties'][1]['name']);
        } finally {
            unlink($pagesDir.'/with-ref.xml');
            unlink($blocksDir.'/text_block.xml');
            rmdir($pagesDir);
            rmdir($blocksDir);
            rmdir($tmpDir.'/config/templates');
            rmdir($tmpDir.'/config');
            rmdir($tmpDir);
        }
    }

    public function testTemplatesHandlesXInclude(): void
    {
        $tmpDir = sys_get_temp_dir().'/sulu-mate-test-'.uniqid();
        $pagesDir = $tmpDir.'/config/templates/pages';
        mkdir($pagesDir, 0777, true);
        copy(\dirname(__DIR__).'/Fixtures/template_xinclude.xml', $pagesDir.'/xinclude.xml');
        copy(\dirname(__DIR__).'/Fixtures/template_xinclude_properties.xml', $pagesDir.'/template_xinclude_properties.xml');

        try {
            $info = new TemplateInfo($tmpDir);
            $result = $info->templates();

            $this->assertCount(1, $result);
            $this->assertSame('xinclude', $result[0]['key']);
            $this->assertCount(2, $result[0]['properties']);
            $this->assertSame('title', $result[0]['properties'][0]['name']);
            $this->assertSame('article', $result[0]['properties'][1]['name']);
        } finally {
            unlink($pagesDir.'/xinclude.xml');
            unlink($pagesDir.'/template_xinclude_properties.xml');
            rmdir($pagesDir);
            rmdir($tmpDir.'/config/templates');
            rmdir($tmpDir.'/config');
            rmdir($tmpDir);
        }
    }
}
