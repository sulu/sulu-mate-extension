## Sulu Mate Extension

This extension provides Sulu-specific tools for AI-assisted content management.

### Available Tools

**`sulu-info`** — Returns version information for all installed `sulu/*` packages. Use this to understand which Sulu components are available in the project and at what versions.

**`sulu-webspaces`** — Returns the webspace configuration of the Sulu installation. Each webspace includes its key, name, localizations, default templates, and portal definitions. Use this to understand the content structure, available languages, and URL routing of the project.

**`sulu-templates`** — Returns the page and article template definitions of the Sulu installation. Each template includes its key, type (page or article), view, localized titles, and a structured list of properties with their types. Block properties include their available block types and sub-properties. Use this to understand the content structure expected by each template when creating or editing pages and articles.

**`sulu-content-types`**: Lists the content types registered in Sulu with their service id and class. Sulu 3 lists the property resolvers (for example `single_page_selection`, `media_selection`). Plain types such as `text_line` and `text_editor` are handled by the `default` resolver. Sulu 2.6 lists the content types by alias. Optional `name_prefix` and `limit`. The data is read from the installed sources, so no kernel is booted. If nothing is found, the result carries a `note`.

**`sulu-property-types`**: Lists the type names that can be used in `<property type="...">` of template XML. Each entry has its `sources` (admin core types, content types or resolvers, admin metadata mappers, selection field types, or only the project templates), the resolver or content type `class` when there is one, and `used_in_templates` with the number of properties in the project templates that use it. Call this before writing or changing a template. Optional `name_prefix` and `limit`.

**`sulu-twig-list`**: Lists the Twig functions, filters and tests that the installed Sulu packages register, with the extension class and the PHP signature. Use it to find `sulu_*` helpers for templates. Optional `kind` (`function`, `filter` or `test`), `name_prefix` and `limit`.

**`sulu-twig-verify`**: Checks one exact Twig function, filter or test name, for example `sulu_content_path`. Returns `exists`, the matches, and similar names when there is no match. Call this before using a `sulu_*` helper in a template, so no helper name is invented.
