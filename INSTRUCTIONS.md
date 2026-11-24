## Sulu Mate Extension

Read-only tools, no parameters.

### Tool index

| Tool | Use when |
|---|---|
| `sulu-info` | You need the Sulu version, to tell Sulu 2.6 from 3.x. |
| `sulu-webspaces` | You need webspace keys, locales or default templates. |
| `sulu-templates` | You write, change or fill a page or article template. |

### Hard rules

- **Call `sulu-info` before advice or code that depends on the Sulu version.** Sulu 2.6 and 3.x differ in storage and APIs.
- **Call `sulu-webspaces` before using a webspace key or locale.** Both are defined per project.
- **Call `sulu-templates` before writing or changing template XML, Twig or content.** Template keys, property names and types are defined per project.
- Reuse the property types and block types the tool reports. Do not invent new ones.
- `sulu-templates` lists only `config/templates/pages` and `config/templates/articles`. Snippets are not listed.
