---
name: authoring-sulu-templates
description: >-
  Create or change a Sulu page or article template (config/templates/pages/*.xml,
  config/templates/articles/*.xml) and its Twig view: new template, new property, new block
  type, renamed property, or a page type for a webspace. Apply it whenever template XML is
  about to be written or edited, including requests like "add a hero block" or "new landing
  page template".
---

# Authoring Sulu templates

A template is one XML file plus one Twig view. Work in this order. Do not skip the lookup.
`<mate>` is the Mate command from the project's agent instructions, usually `vendor/bin/mate`.

## 1. Look up

1. `<mate> tools:call sulu-info`: note the Sulu version.
2. `<mate> tools:call sulu-webspaces`: note the localizations and default templates.
3. `<mate> tools:call sulu-templates --format=json`: list existing templates.

Pick the closest existing template of the same type (`page` or `article`). Open its XML
and its Twig view. If a template with the wanted key already exists, you are changing it,
not creating it.

## 2. Mirror

Copy the closest template and edit the copy.

- Keep `<controller>`, `<cacheLifetime>` and the schema header as in the source file.
- Keep the tags and params the source uses on its core properties (for example the
  title and url properties of a page). The tool output does not show them, so read the XML.
  A page that loses them loses its URL handling.
- Use property types you saw in `sulu-templates` output or in project XML. If the project
  has no example of the type you need, look it up in the Sulu documentation of the installed
  major version. If you still cannot find it, ask. Do not guess.
- Give the template and every property a `<meta><title lang="..."/></meta>` with the same
  `lang` set as the source template. Admin titles follow the UI locale of the admin user
  (`sulu_core.locales`), not the locales of the webspace.
- A property name is the key of the stored content. Renaming one orphans existing content.
  Add the new property, migrate the content, then remove the old one.
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
`sulu-templates` lists the Twig files it found in `twig_templates`. It only looks in the
project's `templates/` directory. An empty list means the view was not found there. Check
custom Twig paths and bundle notation before you create a second file.

## 4. Verify

1. Validate the XML against the schema shipped with Sulu:
   `xmllint --noout --schema vendor/sulu/sulu/src/Sulu/Bundle/AdminBundle/Resources/config/schema/template-1.0.xsd <file>`
   If the path does not exist in your version, locate the file with
   `find vendor/sulu/sulu -name template-1.0.xsd`.
2. Run `bin/adminconsole cache:clear`. Sulu rejects a template here that the schema and
   the tool accept, for example a `default-type` that matches no block type. Run
   `bin/websiteconsole cache:clear` too.
3. Call `sulu-templates` again. The template must appear with the key, type, view,
   properties and block types you intended. A template that is missing from the output
   failed to parse or sits in a directory the tool does not read (`config/templates/pages`
   and `config/templates/articles` only).
4. Run the project's own checks (lint and tests) if it has them. For a page template,
   make sure editors can choose it in the admin or that a webspace default points to it.

Report what you created, which template you mirrored and what you verified.
