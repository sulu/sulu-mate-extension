<h1 align="center">SuluMateExtension</h1>

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

The SuluMateExtension is an [AI Mate](https://symfony.com/doc/current/ai/components/mate.html) extension for the [Sulu](https://sulu.io/) content management system. It provides Sulu-aware MCP tools and resources for AI-assisted content management workflows.

## Requirements

- PHP >= 8.2
- [symfony/ai-mate](https://github.com/symfony/ai-mate) ^0.6

## Installation

```bash
composer require --dev sulu/sulu-mate-extension
vendor/bin/mate init
```

Extension discovery is handled automatically after Composer install and update. Run `vendor/bin/mate discover` to refresh discovery state and regenerate agent instruction artifacts manually.

## Structure

```text
sulu-mate-extension/
├── config/
│   └── config.php
├── src/
│   └── Capability/
├── tests/
├── INSTRUCTIONS.md
├── README.md
└── LICENSE
```

## Development

```bash
composer install
composer test
composer lint
composer fix
```

## Support and Community

Sulu is an open-source project driven by its community. If you need help or want to contribute:

- [GitHub Issues](https://github.com/sulu/sulu-mate-extension/issues) -- Report bugs and request features
- [Sulu Slack](https://sulu.io/services-and-support) -- Join the community for questions and discussions
- [Sulu Documentation](https://docs.sulu.io/) -- Official Sulu documentation

## License

This package is available under the [MIT License](LICENSE).
