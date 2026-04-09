# AGENTS.md

Guidelines for AI agents helping users customize this extension template.

## Agent Role

You are helping developers turn this template into a real Symfony AI Mate extension.

## Responsibilities

- Replace all `Example` and `ExampleExtension` placeholders
- Keep docs aligned with the actual file layout
- Register capabilities in `config/config.php`
- Keep examples consistent with MatesOfMate house style
- Explain the current Mate workflow: `mate init`, automatic discovery, generated agent instructions, and Codex wrappers

## Template Standards

- Do not add `declare(strict_types=1)` to examples.
- Do not make example classes `final`.
- Use `\JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT` in JSON examples.
- Keep file headers consistent with the repo.

## Capability Guidance

When creating tools:

- Use a clear `#[McpTool]` name in `{framework}-{action}` form.
- Write the description so the AI knows when to call the tool.
- Register the class in `config/config.php`.
- Prefer JSON string output for stable MatesOfMate-style structured responses.
- Remember that current AI Mate also supports array and scalar returns.

When creating resources:

- Use a framework-specific URI scheme.
- Return `uri`, `mimeType`, and `text`.
- Register the class in `config/config.php`.
- Keep MIME types aligned with the actual encoding strategy.

## Workflow Guidance

When helping users:

1. update package name, namespace, CODEOWNERS, and license placeholders
2. replace example tool and resource names, URIs, and descriptions
3. update README and `INSTRUCTIONS.md` with framework-specific guidance
4. run `composer test`
5. run `composer lint`

## Current Mate Notes

- `vendor/bin/mate init` prepares project-local Mate files
- current Mate workflows auto-discover extensions after Composer install and update
- `vendor/bin/mate discover` refreshes discovery and regenerates `mate/AGENT_INSTRUCTIONS.md`
- Codex should be launched with `./bin/codex` or `bin/codex.bat`
- use `mate debug:capabilities` and `mate debug:extensions` when capabilities do not show up

## Commit Messages

Never include AI attribution in commit messages. Focus on conceptual changes and outcomes.
