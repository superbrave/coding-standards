# Superbrave coding standards

The Superbrave coding standards uses some existing standards combined, with a few exceptions.

For more details about a custom code guideline, see https://github.com/squizlabs/PHP_CodeSniffer/wiki/Coding-Standard-Tutorial

## Usage through composer

When using these coding standards through composer, modify the following files:

*composer.json*
~~~~
"require-dev" : {
  "superbrave/coding-standards": "dev-master"
}
~~~~
*phpcs.xml*
~~~~
<?xml version="1.0"?>
<ruleset>
  <rule ref="vendor/superbrave/coding-standards/Superbrave"/>
</ruleset>
~~~~
Don't forget to run a composer update after editing the composer.json file.
