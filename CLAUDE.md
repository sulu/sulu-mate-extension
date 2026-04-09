# CLAUDE.md

This file provides guidance when working on the SuluMateExtension.

## Project Overview

This package is the Sulu AI Mate extension (`sulu/sulu-mate-extension`). It provides Sulu-aware MCP tools and resources for AI-assisted content management workflows.

The extension currently has no capabilities yet. When adding capabilities, follow the Sulu file header convention and register them in `config/config.php`.

## Current Mate Baseline

The extension is aligned with:

- released `symfony/ai-mate` `0.6.x`
- current `symfony/ai` `main` branch conventions where they are already established

Current workflow assumptions:

- projects are initialized with `vendor/bin/mate init`
- Composer install and update handle extension discovery
- `vendor/bin/mate discover` refreshes discovery state and regenerates `mate/AGENT_INSTRUCTIONS.md`
- debugging commands include `mate debug:capabilities`, `mate debug:extensions`, and `mate mcp:tools:*`

## Common Commands

```bash
composer install
composer test
composer lint
composer fix
vendor/bin/mate debug:capabilities
vendor/bin/mate debug:extensions
```

## Package Structure

- `src/Capability/` is where tools and resources go
- `config/config.php` registers services
- `INSTRUCTIONS.md` provides agent guidance for MCP capabilities

## Service Registration

All capabilities should be registered in `config/config.php`.

```php
$services = $container->services()
    ->defaults()
    ->autowire()
    ->autoconfigure();

$services->set(YourTool::class);
```

## Coding Style

- File headers use the Sulu convention (`This file is part of Sulu. (c) Sulu GmbH`)
- No `declare(strict_types=1)` in capability examples
- No `final` classes in capability examples
- JSON encoding uses `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT`

## Testing Expectations

- `composer test` must pass
- `composer lint` must pass
- Docs must match the actual file layout and commands

## Authoring Guidance

When updating this package:

1. Keep `README.md`, `CLAUDE.md`, `AGENTS.md`, and `INSTRUCTIONS.md` mutually consistent
2. Prefer concrete capability examples over placeholder prose
3. Avoid documenting steps that are obsolete in current Mate workflows

## Commit Message Convention

Keep commit messages clean and free of AI attribution.
