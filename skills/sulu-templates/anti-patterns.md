# Template anti-patterns

- Writing a property `type` from memory. Take types from existing templates of the project.
- Writing the template from scratch instead of copying a sibling. The controller,
  cache lifetime and core tags are then wrong or missing.
- Dropping the tags of the title and url properties of a page when copying. The page
  loses its URL handling.
- Renaming a property that has content. Add the new property, migrate the content,
  then remove the old one.
- A `default-type` that matches no `<type name>` in the block.
- A new template key that collides with an existing one.
- Titles in one language only on a multilingual webspace.
- Creating the XML without the Twig view. The page then fails when it is rendered.
- Putting the file where `sulu-templates` does not look and then concluding the tool is
  broken. It reads `config/templates/pages` and `config/templates/articles` only.
- Pasting 2.6 PHPCR or content-migration steps into a 3.x project or the reverse.
