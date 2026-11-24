## Sulu Mate Extension

This extension provides Sulu-specific tools for AI-assisted content management.

### Available Tools

**`sulu-info`** — Returns version information for all installed `sulu/*` packages. Use this to understand which Sulu components are available in the project and at what versions.

**`sulu-webspaces`** — Returns the webspace configuration of the Sulu installation. Each webspace includes its key, name, localizations, default templates, and portal definitions. Use this to understand the content structure, available languages, and URL routing of the project.

**`sulu-templates`** — Returns the page and article template definitions of the Sulu installation. Each template includes its key, type (page or article), view, localized titles, and a structured list of properties with their types. Block properties include their available block types and sub-properties. Use this to understand the content structure expected by each template when creating or editing pages and articles.

**`sulu-twig-list`**: Lists the Twig functions, filters and tests that the installed Sulu packages register, with the extension class and the PHP signature. Use it to find `sulu_*` helpers for templates. Optional `kind` (`function`, `filter` or `test`), `name_prefix` and `limit`.

**`sulu-twig-verify`**: Checks one exact Twig function, filter or test name, for example `sulu_content_path`. Returns `exists`, the matches, and similar names when there is no match. Call this before using a `sulu_*` helper in a template, so no helper name is invented.
