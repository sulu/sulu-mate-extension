---
name: sulu-dev
description: >-
  Entry point for any task in a Sulu CMS project (2.6 or 3.x): creating or changing page,
  article or snippet templates, Twig views, webspaces, content structure or localizations.
  Apply it before writing any template XML, Twig view or webspace config, and whenever the
  task depends on which Sulu version, webspaces, languages or templates the project has.
---

# Sulu development

Read the project before you write anything. The Mate tools of this extension return facts
about the running installation. Call them first, then generate code that matches them.

## Discovery

Run these from the project root. Add `--format=json` for output you want to parse.

| Need | Command |
|---|---|
| Installed `sulu/*` versions | `vendor/bin/mate tools:call sulu-info` |
| Webspaces, localizations, default templates, portals | `vendor/bin/mate tools:call sulu-webspaces` |
| Page and article templates with properties and block types | `vendor/bin/mate tools:call sulu-templates` |

Start with `sulu-info`. The major version (2.6 or 3.x) decides how much of your Sulu
knowledge applies.

If `vendor/bin/mate` is missing, the extension is not installed. Say so and stop. Do not
guess project facts from memory.

## What the tools tell you

- `sulu-webspaces`: webspace keys, localizations and `default_templates` (the `page` and
  `home` template keys). A new page template must fit a webspace that exists.
- `sulu-templates`: for every template the key, type (`page` or `article`), view, controller,
  localized titles, the Twig files that exist for the view, and the properties.
  Properties carry `name`, `type`, `mandatory`, `multilingual`. Block properties list their
  block types with sub-properties. Global block refs are already resolved.

What the tools do not tell you:

- Property tags (for example `sulu.rlp`) and `params`. Read the template XML for those.
- Snippet templates, form XML and custom template directories. The tool only reads
  `config/templates/pages` and `config/templates/articles`.
- Whether the Twig view renders what you expect. Read it.

## Hard rules

- Never invent a property type. Use types that appear in `sulu-templates` output or in the
  project's own XML. Which types exist depends on the installed Sulu packages.
- Never invent a template key. Take keys from `sulu-templates` and `default_templates`.
- A template key matches `[a-z0-9_-]+` and has at most 64 characters.
- Mirror an existing template of the same type before you write a new one.
  The existing XML is the best source for the controller, `cacheLifetime` and tags of
  this project and version.
- Keep titles in the localizations the webspace uses. Take them from `sulu-webspaces`.
- Do not apply 2.6 advice to a 3.x project or the reverse. 3.x stores content in the
  database, 2.6 stores it in PHPCR. The template XML format is the same, but check
  `sulu-info` before you rely on version-specific classes, commands or migrations.
- The Mate tools only read. Creating or changing files stays your job and the user's review.

## Where things live

- Page templates: `config/templates/pages/<key>.xml`
- Article templates: `config/templates/articles/<key>.xml`
- Global blocks: `config/templates/blocks/<name>.xml`, referenced as `<type ref="name"/>`
- Webspaces: `config/webspaces/*.xml`
- Twig views: `templates/<view>.html.twig`, where `<view>` is the template's `<view>` value

A project can configure other directories under `sulu_admin.templates`. If a template
you expect is missing from the tool output, check that configuration before you assume
it does not exist.

## Related skills

- The `sulu-templates` skill (installed as `mate-sulu-templates`): create or change a page or
  article template. It shares its name with the tool, but it is a workflow, not a tool call.
