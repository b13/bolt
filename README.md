# Easier Integrations for TYPO3 sites


## Introduction

This package is a TYPO3 extension that makes integration work easier.

Simply put, the extension allows running a TYPO3 instance without any
database driven TypoScript template (sys_template) records and without
PageTsConfig page record entries, enabling file-driven (as in: not database-driven)
deployment of TypoScript and PageTsConfig. This is done by connecting a Site configuration
(those `.yaml` site configuration files) with a "Site extension" and using some simple
events or hooks of the TYPO3 core.


## Background

We consider it best practice to run a site and all custom Backend Layouts, TypoScript, PageTS,
Fluid templates and similar in one place: In a "site extension". We prefix them with "site_", something
like `site_myproject`. This site extension is the general entry point for configuration of a
single Site page tree.

The "bolt" extension provides a Site configuration setting called "sitePackage" that connects a
Site with this site package / extension. This is simply an entry in the Site's .yaml
file, and can be manually added to the file, or clicked in the TYPO3 "Sites" Backend module.

Providing "everything" as files without database records is in general possible for nearly
everything in current TYPO3, except for sys_template records and PageTsConfig settings. The
extension thus provides some hooks that look up the connected "site extension" of a site,
to TypoScript "constants" and "setup", as well as PageTsConfig, from files, provided
by the site extension. This avoids these database entries.


## Installation

* Require the extension via composer (`composer require b13/bolt`) or load it from
[TER](https://extensions.typo3.org/extension/bolt/) (extension name "bolt") using the
extension manager.

* Create a site extension, having at least a composer.json and an ext_emconf.php file,
  prefixed with `site_`. Have this extension loaded.

* Either manually edit the Site Configuration `.yaml` file and add `sitePackage: '<my_extension_key>'`
  as top-level key, or edit the Site Configuration in the Backend "Sites" module, select
  the site package / extension in the drop and save it.

* Add extension file `Configuration/TypoScript/constants.typoscript`. This is the main
  TypoScript "constants / settings" entry point for this Site in the page tree. It should
  typically contain `@import` lines to load further "static includes" from other extensions
  as well as own TypoScript provided by the site extension itself. This file is automatically
  loaded by convention using a hook or event of the bolt extension. Since TYPO3 v12, the Backend
  "Template Analyzer" reflects such includes.

* Add extension file `Configuration/TypoScript/setup.typoscript`. This is the main TypoScript "setup"
  entry point for this Site in the page tree. It should typically contain `@import` lines to load further
  "static includes" from other extensions as well as own TypoScript provided by the site extension itself.
  This file is automatically loaded by convention using a hook or event of the bolt extension. Since
  TYPO3 v12, the Backend "Template Analyzer" reflects such includes.

* Add extension file `Configuration/PageTs/main.tsconfig` (if needed). This is the main PageTsConfig entry
  point for this Site in the page tree. It should typically contain further `@import` lines. This file is
  automatically loaded by convention using a hook or event of the bolt extension.

* Add further files like Frontend rendering Templates, ViewHelper classes or TCA overrides as needed: Make
  the site extension the single entry point of your Site configuration that provides all site specific
  settings!


## Disabled Backend settings

Extension `bolt` adds default PageTsConfig that disallows adding new `sys_template` records in the
backend, and it hides the `PageTsConfig` related fields when editing page records. Those defaults are
added  in `ext_tables.php`, they follow our best practices, but can be rewritten again if really needed.


## License

The extension is licensed under GPL v2+, same as the TYPO3 Core. See the LICENSE file.


## Sharing our expertise

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help
us deliver value in client projects. As part of the way we work, we focus on testing and best practices to ensure
long-term performance, reliability, and results in all our code.
