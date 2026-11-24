## Sulu Mate Extension

Read-only tools. They take no parameters, except the optional filters below.

### Tool index

| Tool | Use when |
|---|---|
| `sulu-info` | You need the Sulu version, to tell Sulu 2.6 from 3.x. |
| `sulu-webspaces` | You need webspace keys, locales or default templates. |
| `sulu-templates` | You write, change or fill a page or article template. |
| `sulu-property-types` | You pick the `type` of a template property. Filter with `name_prefix` and `limit`. |
| `sulu-content-types` | You need the content type or resolver behind a property type. Filter with `name_prefix` and `limit`. |
| `sulu-twig-list` | You look for a `sulu_*` Twig function, filter or test. Filter with `kind`, `name_prefix` and `limit`. |
| `sulu-twig-verify` | You are about to use a `sulu_*` Twig function, filter or test in a template. |

### Hard rules

- **Call `sulu-info` before advice or code that depends on the Sulu version.** Sulu 2.6 and 3.x differ in storage and APIs.
- **Call `sulu-webspaces` before using a webspace key or locale.** Both are defined per project.
- **Call `sulu-templates` before writing or changing template XML, Twig or content.** Template keys, property names and types are defined per project.
- **Call `sulu-property-types` before using a property type that no template of the project uses.** The installed Sulu packages decide which types exist.
- **Call `sulu-twig-verify` before using a `sulu_*` Twig helper.** The installed Sulu version decides which helpers exist.
- Reuse the block types the tool reports. Do not invent new ones.
- `sulu-templates` lists only `config/templates/pages` and `config/templates/articles`. Snippets are not listed.
- `sulu-property-types`, `sulu-content-types`, `sulu-twig-list` and `sulu-twig-verify` read the installed sources and do not boot the kernel. `sulu-twig-list` and `sulu-twig-verify` scan `sulu/*` packages only. Plain types such as `text_line` and `text_editor` are not listed by `sulu-content-types`. They go through the default resolver.
