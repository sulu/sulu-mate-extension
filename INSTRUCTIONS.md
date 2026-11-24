## Sulu Mate Extension

Read-only tools. They take no parameters, except the optional filters below.

### Tool index

| Tool | Use when |
|---|---|
| `sulu-info` | You need the Sulu version, to tell Sulu 2.6 from 3.x. |
| `sulu-webspaces` | You need webspace keys, locales or default templates. |
| `sulu-templates` | You write, change or fill a page or article template. |
| `sulu-twig-list` | You look for a `sulu_*` Twig function, filter or test. Filter with `kind`, `name_prefix` and `limit`. |
| `sulu-twig-verify` | You are about to use a `sulu_*` Twig function, filter or test in a template. |

### Hard rules

- **Call `sulu-info` before advice or code that depends on the Sulu version.** Sulu 2.6 and 3.x differ in storage and APIs.
- **Call `sulu-webspaces` before using a webspace key or locale.** Both are defined per project.
- **Call `sulu-templates` before writing or changing template XML, Twig or content.** Template keys, property names and types are defined per project.
- **Call `sulu-twig-verify` before using a `sulu_*` Twig helper.** The installed Sulu version decides which helpers exist.
- Reuse the property types and block types the tool reports. Do not invent new ones.
- `sulu-templates` lists only `config/templates/pages` and `config/templates/articles`. Snippets are not listed.
- `sulu-twig-list` and `sulu-twig-verify` scan the installed `sulu/*` packages only, not project or other vendor Twig extensions.
