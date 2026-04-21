## Sulu Mate Extension

This extension provides Sulu-specific MCP tools for AI-assisted content management.

### Available Tools

**`sulu-info`** — Returns version information for all installed `sulu/*` packages. Use this to understand which Sulu components are available in the project and at what versions.

**`sulu-webspaces`** — Returns the webspace configuration of the Sulu installation. Each webspace includes its key, name, localizations, default templates, and portal definitions. Use this to understand the content structure, available languages, and URL routing of the project.

**`sulu-templates`** — Returns the page and article template definitions of the Sulu installation. Each template includes its key, type (page or article), view, localized titles, and a structured list of properties with their types. Block properties include their available block types and sub-properties. Use this to understand the content structure expected by each template when creating or editing pages and articles.
