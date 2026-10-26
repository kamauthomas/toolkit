# PHP security and upgrade assessment — 26 October 2026

Keep the current patched branches while preparing a separate PHP 8.5 demo test.
The installed interpreters match the upstream fixes for the two main PHP2Shell
issues. PHP 8.5.12 is the latest stable release, but neither cPanel's installed
version list nor the account selector currently offers PHP 8.5.
[PHP security announcement and downloads](https://www.php.net/archive/2026.php).

| System | Configured web branch | Corresponding installed CLI |
| --- | --- | --- |
| Production WordPress | EA-PHP 8.3 | 8.3.36 |
| Demo WordPress | EA-PHP 8.4 | 8.4.27 |
| Reception production and demo | EA-PHP 8.4 | 8.4.27 |
| Campus test | EA-PHP 8.4 | 8.4.27 |

cPanel's internal interpreter also reports 8.4.27. All four ordinary
WordPress/Reception/Campus page checks returned 200; both WordPress responses
carry release 2026.10.26.1 and cache misses. Public responses omit PHP version
headers, so these observations do not independently attest the version already
loaded in every web worker or its restart. RPM/changelog information is not
available in the account's command environment. Hosting should confirm worker
restart and current vendor builds. No PHP version or extension setting was changed.

## Compatibility with PHP 8.5

Both WordPress installations are 7.1.3. WordPress lists core 7.1 as compatible
with PHP 8.5; theme and plugin compatibility still requires separate testing.
[WordPress compatibility matrix](https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/).

The 79 Reception and 76 Campus locked runtime packages have no PHP-version
constraint excluding 8.5.12. Both dependency sets require PHP 8.4.1 or newer,
so PHP 8.3 is not a compatible fileinfo workaround. This is a dependency screen, not a successful
execution test. An actual PHP 8.5 interpreter was unavailable locally and on the
host. Eduma, plugins, custom forms, calling letters, QR features, permissions,
database operations and scheduled sync have not been verified on PHP 8.5.
The existing local suite passes on PHP 8.4: 95 Reception tests / 1,364 assertions,
with separate deployment/privacy/layout and isolated dependency checks.

A production PHP 8.5 switch is not ready for promotion. Have hosting provision a
patched PHP 8.5 build and the matching required modules for demo, preserve the
current configuration for rollback, then test the real application flows there, including the CLI and scheduled-job
interpreters.
Do not select a prerelease as a production upgrade.

## Fileinfo and remaining dependency cleanup

The current account exposes CloudLinux PHP Selector with native (8.4) selected.
Native extension controls are disabled. ALT-PHP 8.4's selector flag says fileinfo
is enabled, but direct EA-PHP 8.4 and ALT-PHP 8.4 CLI checks both report it absent
in their current configurations. EA-PHP 8.3 and cPanel's internal PHP report it
present. Switching unrelated interpreter binaries would not establish that the
actual application requirement has been met.

The native EA-PHP extension needs installation/enabling through hosting's
administration controls; an ALT-PHP checkbox does not enable it in EA-PHP.
[cPanel Fileinfo installation procedure](https://support.cpanel.net/hc/en-us/articles/1500000107181-How-do-I-install-Fileinfo),
[native PHP Selector restriction](https://support.cpanel.net/hc/en-us/articles/12092609681943-How-do-I-enable-a-PHP-extension-for-Cloudlinux-ALT-PHP-via-cPanel).

Composer therefore refused the demo development-dependency removal. Exact
original dependency metadata, normal login 200, absent maintenance files and
absent temporary cron were verified after rollback; temporary Composer was
removed. Production/Campus dependency trees were untouched. Thirty-three
development dependencies per installation remain pending the hosting prerequisite.
The local runtime-only preparations and guarded recovery procedure are ready;
no platform check was bypassed. The admin UI/read-only sync release and 279-file
active-code cleanup remain verified.

## Additional PHP guidance and hosting handoff

PHP's 23 October notice describes a further session-upload-progress condition and
recommends disabling that feature pending the next regular fix. The captured CLI
defaults show upload progress enabled and the ordinary php serializer. No serializer
switch was found in custom application code; this review does not cover every
deployed third-party plugin. Hosting should apply the recommended setting at the
appropriate startup/server level and restart workers, rather than relying on
application ini_set or FPM user.ini. This hardening complements the released fixes.
[PHP's 21/23 October advisories](https://www.php.net/archive/2026.php).

Ready-to-send hosting request (not sent by the agent):

> Please install/enable ea-php84-php-fileinfo for the existing EA-PHP 8.4
> applications. Confirm the installed fixed builds and that applicable PHP web
> workers were restarted after patching. Review session.upload_progress.enabled
> against PHP's current advisory and disable it before request parsing where unused.
> If PHP 8.5 is provisioned, start with demo and matching modules; leave production
> interpreter assignments unchanged until the compatibility checks pass.

Private evidence: reports/php-upgrade-assessment-2026-10-26 and
reports/runtime-minimum-audit-2026-10-26. No public diagnostics, exploit payloads,
production test applications or uncertain admissions replays were used. The
assessment job was removed. One automatic permission review expired before a
normal web check started; its permitted single retry completed successfully.
