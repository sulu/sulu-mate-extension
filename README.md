<h1 align="center">Sulu Mate Extension</h1>

<p align="center">
    <a href="https://sulu.io/" target="_blank">
        <img width="30%" src="https://sulu.io/uploads/media/800x/00/230-Official%20Bundle%20Seal.svg?v=2-6&inline=1" alt="Official Sulu Bundle Badge">
    </a>
</p>

<p align="center">
    <a href="LICENSE" target="_blank">
        <img src="https://img.shields.io/github/license/sulu/sulu-mate-extension.svg" alt="GitHub license">
    </a>
    <a href="https://github.com/sulu/sulu-mate-extension/releases" target="_blank">
        <img src="https://img.shields.io/github/tag/sulu/sulu-mate-extension.svg" alt="GitHub tag (latest SemVer)">
    </a>
    <a href="https://github.com/sulu/sulu-mate-extension/actions" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/sulu/sulu-mate-extension/ci.yml" alt="Test workflow status">
    </a>
</p>
<br/>

The **Sulu Mate Extension** is a [Symfony Mate](https://symfony.com/doc/current/ai/components/mate.html) extension for
the [Sulu](https://sulu.io/) content management system. It gives coding agents **Sulu-aware tools** that read the real
installation: Sulu versions, webspaces and templates. Agents call them through the Mate CLI. No MCP server is needed.

It is a development tool. Install it as a `require-dev` dependency.

Have a look at the `require` section in the [composer.json](composer.json) to find an
**up-to-date list of the requirements** of the extension. It needs Symfony Mate 0.13 or newer.


## 🚀&nbsp; Installation and Documentation

Execute the following [composer](https://getcomposer.org/) command to add the extension to the dependencies of your
project:

```bash
composer require --dev sulu/sulu-mate-extension
```

Afterwards, initialize Mate and discover the extension:

```bash
vendor/bin/mate init
vendor/bin/mate discover
```

Extension discovery is handled automatically after Composer install and update. Run `vendor/bin/mate discover` when you
want to refresh discovery state and regenerate agent instruction artifacts manually.


## 🔧&nbsp; Available Tools

- `sulu-info`: installed `sulu/*` package versions
- `sulu-webspaces`: webspaces with localizations, default templates and portals
- `sulu-templates`: page and article templates with properties and block types

Agents call a tool with `vendor/bin/mate tools:call <tool>`. See [INSTRUCTIONS.md](INSTRUCTIONS.md) for when to call
which tool.


## 🧠&nbsp; Agent Skills

The extension ships [Agent Skills](https://agentskills.io/) in [skills](skills). `mate discover` installs them into
`.agents/skills/` and `.claude/skills/` of your project:

- `sulu-dev`: entry point for Sulu work. Tells the agent to call `sulu-info`, `sulu-webspaces` and `sulu-templates`
  before it generates code.
- `authoring-sulu-templates`: workflow to create or change a page or article template: look up, mirror, verify.

Run `vendor/bin/mate skills:list` to see their state.


## 💡&nbsp; Key Concepts

### Mate Extension

The extension plugs into [Symfony Mate](https://symfony.com/doc/current/ai/components/mate.html). Mate finds the tools
and skills of installed extensions and writes them into the agent instructions of your project. Coding agents then run
the tools through the Mate CLI (`vendor/bin/mate tools:call`) and get Sulu-specific facts instead of guessing them.

### Development

```bash
composer install
composer test
composer lint
composer fix
```

Useful Mate commands while developing:

```bash
vendor/bin/mate debug:capabilities
vendor/bin/mate debug:extensions
vendor/bin/mate tools:list
vendor/bin/mate tools:call sulu-info
```


## ❤️&nbsp; Support and Contributions

The Sulu content management system is a **community-driven open source project** backed by various partner companies.
We are committed to a fully transparent development process and **highly appreciate any contributions**.

In case you have questions, we are happy to welcome you in our official [Slack channel](https://sulu.io/services-and-support).
If you found a bug or miss a specific feature, feel free to **file a new issue** with a respective title and description
on the [sulu/sulu-mate-extension](https://github.com/sulu/sulu-mate-extension) repository.


## 📘&nbsp; License

The Sulu content management system is released under the terms of the [MIT License](LICENSE).
