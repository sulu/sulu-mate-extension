# Extension Template for Symfony AI Mate

A starter template for building [MatesOfMate](https://github.com/matesofmate) extensions that follow the current Symfony AI Mate workflow.

## Quick Start

1. Use this template on GitHub.
2. Replace all `example` and `ExampleExtension` placeholders with your framework name.
3. Run `composer install`.
4. Add your tools and resources in `src/Capability/`.
5. Run `composer test` and `composer lint`.

## Current AI Mate Flow

This template is aligned with the current `symfony/ai-mate` `0.6.x` workflow and the latest `symfony/ai` `main` branch guidance:

- initialize projects with `vendor/bin/mate init`
- extension discovery is handled automatically on Composer install and update in current Mate setups
- `mate/extensions.php` controls which discovered extensions are enabled
- `vendor/bin/mate discover` still refreshes discovery state and regenerates agent instruction artifacts
- Codex should be started via `./bin/codex` or `bin/codex.bat`

Useful Mate commands while developing:

```bash
vendor/bin/mate debug:capabilities
vendor/bin/mate debug:extensions
vendor/bin/mate mcp:tools:list
vendor/bin/mate mcp:tools:inspect example-hello
```

## Structure

```text
extension-template/
├── .github/
├── composer.json
├── README.md
├── LICENSE
├── .gitignore
├── phpunit.xml.dist
├── phpstan.dist.neon
├── rector.php
├── .php-cs-fixer.php
├── src/
│   └── Capability/
│       ├── ExampleTool.php
│       └── ExampleResource.php
├── config/
│   └── config.php
└── tests/
    └── Capability/
        ├── ExampleToolTest.php
        └── ExampleResourceTest.php
```

## Installation in a Project

```bash
composer require --dev matesofmate/your-extension
vendor/bin/mate init
```

In current AI Mate setups, extension discovery is handled automatically after install and update. Run `vendor/bin/mate discover` when you want to refresh generated instruction artifacts or re-scan the project manually.

For Codex, use the generated wrapper instead of relying on `mcp.json` alone:

```bash
./bin/codex
```

## Creating Tools

Tools are PHP classes with methods marked with `#[McpTool]`.

```php
<?php

namespace MatesOfMate\ExampleExtension\Capability;

use Mcp\Capability\Attribute\McpTool;

/**
 * Example tool showing the default MatesOfMate style.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ListEntitiesTool
{
    public function __construct(
        private readonly SomeService $service,
    ) {
    }

    #[McpTool(
        name: 'example-list-entities',
        description: 'List available entities. Use when the user asks which entities, models, or tables exist.'
    )]
    public function execute(): string
    {
        $entities = $this->service->getEntities();

        return json_encode([
            'entities' => $entities,
            'count' => count($entities),
        ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
    }
}
```

Tool guidance:

- Use `{framework}-{action}` for tool names.
- Write descriptions that say when the AI should call the tool.
- MatesOfMate examples default to JSON strings with `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT`.
- Current AI Mate also supports array and scalar tool returns. Use JSON when you want stable structured output as part of your package style.
- Register tool classes in `config/config.php`.

## Creating Resources

Resources provide static or semi-static context to the AI.

```php
<?php

namespace MatesOfMate\ExampleExtension\Capability;

use Mcp\Capability\Attribute\McpResource;

/**
 * Example resource showing the default MatesOfMate style.
 *
 * @author Johannes Wachter <johannes@sulu.io>
 */
class ConfigurationResource
{
    #[McpResource(
        uri: 'example://config',
        name: 'example_config',
        mimeType: 'application/json'
    )]
    public function getConfiguration(): array
    {
        return [
            'uri' => 'example://config',
            'mimeType' => 'application/json',
            'text' => json_encode([
                'version' => '1.0.0',
                'features' => ['feature_a' => true],
            ], \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT),
        ];
    }
}
```

Resource guidance:

- Use a custom URI scheme such as `example://config`.
- Return `uri`, `mimeType`, and `text`.
- JSON resources commonly use `application/json`.
- If you later adopt an optional TOON-or-JSON encoder like the upstream `symfony/ai` PR `#1439` direction, review MIME types deliberately instead of changing them blindly.

## Registering Services

Register capabilities in `config/config.php`:

```php
<?php

use MatesOfMate\ExampleExtension\Capability\ListEntitiesTool;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ListEntitiesTool::class);
};
```

## Agent Instructions

`INSTRUCTIONS.md` should help AI agents map common user intents to your MCP capabilities. Keep it short, concrete, and focused on when to use your tools instead of CLI commands.

Current Mate workflows also materialize aggregated instructions into `mate/AGENT_INSTRUCTIONS.md` and maintain a managed AI Mate block in the project `AGENTS.md` when discovery is refreshed.

## Testing and Quality

```bash
composer test
composer lint
composer fix
```

Useful direct commands:

```bash
vendor/bin/phpunit
vendor/bin/phpstan analyse
vendor/bin/rector process --dry-run
vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Checklist Before Publishing

- [ ] Replace all `example` and `ExampleExtension` placeholders
- [ ] Update `composer.json` package name and description
- [ ] Update `.github/CODEOWNERS`
- [ ] Update `LICENSE`
- [ ] Replace example tool and resource names, URIs, and descriptions
- [ ] Update README install and usage docs for your framework
- [ ] Make sure `composer test` passes
- [ ] Make sure `composer lint` passes
- [ ] Tag a release and submit to Packagist

## Resources

- [Symfony AI Mate docs](https://symfony.com/doc/current/ai/components/mate.html)
- [Creating MCP extensions](https://symfony.com/doc/current/ai/components/mate/creating-extensions.html)
- [MatesOfMate contributing guide](https://github.com/matesofmate/.github/blob/main/CONTRIBUTING.md)

---

*"Because every Mate needs Mates"*
