## Sulu Mate Extension

Read-only tools, no parameters: `vendor/bin/mate tools:call <tool> --format=json`.

### Tool index

| Tool | Use when |
|---|---|
| `sulu-info` | You need the Sulu version, to tell Sulu 2.6 from 3.x. Returns an object of `sulu/*` package name to version. |
| `sulu-webspaces` | You need webspace keys, locales or default templates. Returns a list with `key`, `name`, `localizations`, `default_templates` (type to template key) and `portals` (`name`, `key`, `localizations`). |
| `sulu-templates` | You write, change or fill a page or article template. Returns a list with `key`, `type` (`page` or `article`), `view`, `controller`, `controller_file`, `titles` (locale to title), `twig_templates` and `properties` (`name`, `type`, `mandatory`, `multilingual`, and `block_types` with `name` and nested `properties` for blocks). |

### Hard rules

- **Call `sulu-info` first** in a Sulu task. Sulu 2.6 and 3.x differ in storage and APIs.
- **Call `sulu-webspaces` before using a webspace key or locale.** Never guess them.
- **Call `sulu-templates` before writing or changing template XML, Twig or content.** Never guess template keys, property names or property types.
- Reuse the property types and block types the tool reports. Do not invent new ones.
- `sulu-templates` lists only `config/templates/pages` and `config/templates/articles`. Snippets are not listed.
