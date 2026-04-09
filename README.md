<h1 align="center">SuluMateExtension</h1>

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

The **SuluMateExtension** is an [AI Mate](https://symfony.com/doc/current/ai/components/mate.html) extension for the
[Sulu](https://sulu.io/) content management system. It provides **Sulu-aware MCP tools and resources** for
AI-assisted content management workflows.

Have a look at the `require` section in the [composer.json](composer.json) to find an
**up-to-date list of the requirements** of the extension.


## 🚀&nbsp; Installation and Documentation

Execute the following [composer](https://getcomposer.org/) command to add the extension to the dependencies of your
project:

```bash
composer require --dev sulu/sulu-mate-extension
```

Afterwards, initialize the Mate environment and discover the extension:

```bash
vendor/bin/mate init
vendor/bin/mate discover
```

Extension discovery is handled automatically after Composer install and update. Run `vendor/bin/mate discover` when you
want to refresh discovery state and regenerate agent instruction artifacts manually.


## 💡&nbsp; Key Concepts

### AI Mate Extension

The SuluMateExtension integrates with the [Symfony AI Mate](https://symfony.com/doc/current/ai/components/mate.html)
ecosystem. It uses the **Model Context Protocol (MCP)** to expose Sulu-specific tools and resources to AI assistants,
enabling them to interact with the Sulu CMS in a structured and framework-aware way.

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
vendor/bin/mate mcp:tools:list
```


## ❤️&nbsp; Support and Contributions

The Sulu content management system is a **community-driven open source project** backed by various partner companies.
We are committed to a fully transparent development process and **highly appreciate any contributions**.

In case you have questions, we are happy to welcome you in our official [Slack channel](https://sulu.io/services-and-support).
If you found a bug or miss a specific feature, feel free to **file a new issue** with a respective title and description
on the [sulu/sulu-mate-extension](https://github.com/sulu/sulu-mate-extension) repository.


## 📘&nbsp; License

The Sulu content management system is released under the under terms of the [MIT License](LICENSE).
