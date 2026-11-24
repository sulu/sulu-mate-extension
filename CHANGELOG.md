CHANGELOG
=========

0.5.0
-----

 * Add `sulu-content-types` tool for the registered content types (Sulu 3 property resolvers, Sulu 2.6 content types)
 * Add `sulu-property-types` tool for the type names usable in template XML properties
 * Add `sulu-twig-list` and `sulu-twig-verify` tools for the Twig functions, filters and tests of the installed Sulu packages
 * Read these tools from the installed `sulu/*` sources, without booting the host kernel

0.4.0
-----

 * Add the Agent Skills `sulu-dev` and `authoring-sulu-templates` in `skills/` and declare them in `extra.ai-mate.skills`
 * Require `symfony/ai-mate` `^0.13 || ^0.14 || ^0.15 || ^1.0.0-BETA1` and drop `^0.11` and `^0.12`
 * Replace the `mcp/sdk` `#[McpTool]` attribute with the native `#[MateTool]` attribute, because Mate no longer runs an MCP server
 * Fix the empty tool descriptions in `tools:list`
 * Raise the dev tool floors to `rector/rector` `^2.7` and `friendsofphp/php-cs-fixer` `^3.57`

0.3.0
-----

 * Require `symfony/ai-mate` `^0.11 || ^0.12`

0.2.0
-----

 * Add `sulu-templates` tool for page and article template definitions
 * Add `sulu-info` tool for installed Sulu package versions
 * Add `sulu-webspaces` tool for webspace configuration

0.1.0
-----

 * Initial release of the Sulu AI Mate extension
 * Package scaffolding with quality tools (PHPStan level 8, PHP CS Fixer, Rector)
 * CI/CD workflow
