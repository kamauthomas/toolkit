# Hosted development-package cleanup — 26 October 2026

The same 33 development packages are selected for removal from Reception demo,
Reception production and Campus test. The selection comes from their fetched
Composer `dev-package-names`, checked against the local locked installations.
These are test, debugging and development tools; their supporting packages are
included. This is not a runtime library upgrade.

This list follows the current Composer classification. Both applications also
declare Laravel Tinker as a production requirement, so `--no-dev` retains it.
Its interactive-shell role is an additional removal candidate for a separate
dependency/usage review; the 33-package plan does not claim every retained library
is essential. No Tinker usage was found in the custom application/routes search.

| Installation | Currently installed | Runtime packages to retain | Development packages to remove |
| --- | ---: | ---: | ---: |
| Reception demo | 112 | 79 | 33 |
| Reception production | 112 | 79 | 33 |
| Campus test | 109 | 76 | 33 |

Exact package names:

```text
fakerphp/faker
filp/whoops
hamcrest/hamcrest-php
laravel/agent-detector
laravel/pail
laravel/pao
laravel/pint
mockery/mockery
myclabs/deep-copy
nunomaduro/collision
phar-io/manifest
phar-io/version
phpunit/php-code-coverage
phpunit/php-file-iterator
phpunit/php-invoker
phpunit/php-text-template
phpunit/php-timer
phpunit/phpunit
sebastian/cli-parser
sebastian/comparator
sebastian/complexity
sebastian/diff
sebastian/environment
sebastian/exporter
sebastian/global-state
sebastian/lines-of-code
sebastian/object-enumerator
sebastian/object-reflector
sebastian/recursion-context
sebastian/type
sebastian/version
staabm/side-effects-detector
theseer/tokenizer
```

Keep the full development installation locally. Production preparation uses a
separate staging copy, removes only this locked development set and checks that
all runtime package versions are unchanged. The earlier isolated Reception and
Campus preparations passed their migration, discovery, route, environment and
synthetic login checks. They do not establish the hosting platform requirements.

Removal on hosting remains blocked. On the actual EA-PHP 8.4.27 interpreter,
`fileinfo` and its `finfo` class are absent. The configured module directory is
readable, but its matching `fileinfo.so` does not exist; no fileinfo INI is loaded.
An isolated run without INI also has no built-in fileinfo. The secondary 32-bit
module path is unavailable. The package-query utility is unavailable to this
account, so this is account-visible module evidence, not a root-level RPM audit.
No matching module was available for an isolated load test. No configuration,
PHP branch or worker setting was changed; no platform check was bypassed.

`league/flysystem-local` and `league/mime-type-detection` remain required runtime
packages and require `ext-fileinfo`. Changing Composer metadata or uploading a
locally prepared vendor tree would not supply the missing extension. The safe
resume path still requires actual target-platform success on demo before promotion.
[Composer platform checks](https://getcomposer.org/doc/03-cli.md#check-platform-reqs),
[cPanel Fileinfo provisioning](https://support.cpanel.net/hc/en-us/articles/1500000107181-How-do-I-install-Fileinfo).

Private module/configuration evidence and the fresh eight-root file inventory
are under `reports/fileinfo-followup-2026-10-26/`. The inspection job was removed.
The previous demo dependency attempt remains rolled back; no new dependency
operation was attempted during this investigation.
