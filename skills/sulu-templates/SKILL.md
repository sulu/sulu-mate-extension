---
name: sulu-templates
description: >-
  Create or change a Sulu page or article template (config/templates/pages/*.xml,
  config/templates/articles/*.xml) and its Twig view: new template, new property, new block
  type, renamed property, or a page type for a webspace. Apply it whenever template XML is
  about to be written or edited, including requests like "add a hero block" or "new landing
  page template".
---

# Sulu templates

A template is one XML file plus one Twig view. Work in this order. Do not skip the lookup.

## 1. Look up

1. `vendor/bin/mate tools:call sulu-info`: note the Sulu version.
2. `vendor/bin/mate tools:call sulu-webspaces`: note the localizations and default templates.
3. `vendor/bin/mate tools:call sulu-templates --format=json`: list existing templates.

Pick the closest existing template of the same type (`page` or `article`). Open its XML
and its Twig view. If a template with the wanted key already exists, you are changing it,
not creating it.

## 2. Mirror

Copy the closest template and edit the copy.

- Keep `<controller>`, `<cacheLifetime>` and the schema header as in the source file.
- Keep the tags and params the source uses on its core properties (for example the
  title and url properties of a page). The tool output does not show them, so read the XML.
- Use property types you saw in `sulu-templates` output or in project XML. For a type you
  have not seen, look for it in the project first. If you cannot find it, ask. Do not guess.
- Give the template and every property a `<meta><title lang="..."/></meta>` for each
  localization of the webspace.
- A property name is the key of the stored content. Renaming one orphans existing content.
- Reuse a global block with `<type ref="name"/>` when `config/templates/blocks/` has one.
  Otherwise define the block type inline.

A block looks like this:

```xml
<block name="blocks" default-type="text" minOccurs="0">
    <meta>
        <title lang="en">Blocks</title>
    </meta>

    <types>
        <type name="text">
            <meta>
                <title lang="en">Text</title>
            </meta>

            <properties>
                <property name="text" type="text_editor">
                    <meta>
                        <title lang="en">Text</title>
                    </meta>
                </property>
            </properties>
        </type>
    </types>
</block>
```

`default-type` must name one of the `<type>` entries. Blocks can be nested.

## 3. Add the view

The `<view>` value maps to `templates/<view>.html.twig`. A new template needs that file.
Copy the Twig file of the source template and render the new properties from it.
`sulu-templates` lists the Twig files it found in `twig_templates`. An empty list means
the view is missing.

## 4. Verify

1. Validate the XML against the schema shipped with Sulu:
   `xmllint --noout --schema vendor/sulu/sulu/src/Sulu/Bundle/AdminBundle/Resources/config/schema/template-1.0.xsd <file>`
   If the path does not exist in your version, locate the file with
   `find vendor/sulu/sulu -name template-1.0.xsd`.
2. Call `sulu-templates` again. The template must appear with the key, type, view,
   properties and block types you intended. A template that is missing from the output
   failed to parse or sits in a directory the tool does not read.
3. Check that `twig_templates` is not empty for the new view.
4. Run the project's own checks (lint and tests) if it has them. For a page template,
   make sure editors can choose it in the admin or that a webspace default points to it.

Report what you created, which template you mirrored and what you verified.

Common mistakes are listed in [anti-patterns.md](anti-patterns.md).
