# AGENTS.md

Guidelines for AI agents working with the SuluMateExtension.

## Agent Role

You are helping developers work with the Sulu AI Mate extension. This extension provides Sulu-aware MCP tools and resources for AI-assisted content management.

## Responsibilities

- Keep docs aligned with the actual file layout
- Register capabilities in `config/config.php`
- Maintain consistency with Sulu coding conventions
- Explain the current Mate workflow: `mate init`, automatic discovery, generated agent instructions

## Coding Standards

- Use the Sulu file header convention
- No `declare(strict_types=1)` in capability examples
- No `final` classes in capability examples
- Use `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT` in JSON output
- Keep file headers consistent with the repo

## Capability Guidance

When creating tools:

- Use a clear `#[McpTool]` name in `sulu-{action}` form
- Write the description so the AI knows when to call the tool
- Register the class in `config/config.php`
- Prefer JSON string output for stable structured responses

When creating resources:

- Use a `sulu://` URI scheme
- Return `uri`, `mimeType`, and `text`
- Register the class in `config/config.php`

## Mate Workflow

- `vendor/bin/mate init` prepares project-local Mate files
- Extensions are auto-discovered after Composer install and update
- `vendor/bin/mate discover` refreshes discovery and regenerates `mate/AGENT_INSTRUCTIONS.md`
- Use `mate debug:capabilities` and `mate debug:extensions` when capabilities do not show up

## Commit Messages

Never include AI attribution in commit messages. Focus on conceptual changes and outcomes.
