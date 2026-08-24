# Application encryption keyring

The admissions payload must use a dedicated key that is independent of
WordPress login/session salts. Configure the keyring in each environment's
private `wp-config.php` (or an equally protected private include); never put
real values in Git, reports, deployment scripts, Windows transfer folders or
shell history.

Generate one random 32-byte key for the current key ID and base64-encode it.
The legacy key is the 32-byte derived application key from the pre-rotation
production configuration. It is needed only while version-1 records are being
migrated. The names below are examples, not secrets:

```php
define( 'TOOLKIT_APPLICATION_ENCRYPTION_CURRENT_KEY_ID', 'application-2026-08' );
define( 'TOOLKIT_APPLICATION_ENCRYPTION_KEYS', array(
    'application-2026-08' => 'BASE64_CURRENT_32_BYTE_KEY',
    'legacy-auth-k2'      => 'BASE64_PRE_ROTATION_DERIVED_KEY',
) );
```

The code continues to read version-1 records encrypted with the current auth
salt for compatibility, and it will try every explicitly configured legacy
key. Once the dedicated key is configured, new submissions and updated
payloads use version 2 and include the current key ID (`kid`).

## Migration

After taking a fresh private database backup and confirming the key IDs, run
the count-only dry run first:

```text
wp toolkit applications-migrate-encryption --dry-run
```

Review the JSON counts. A clean migration run is then:

```text
wp toolkit applications-migrate-encryption
```

The command decrypts each version-1 record with the configured K2/K3/current
key candidates, validates a re-encryption round trip, and updates only when the
original ciphertext is unchanged. It emits counts only and never prints
applicant details, references, ciphertext or keys. Any `failed` or `conflicts`
count requires investigation before legacy keys are removed.

The encrypted dashboard Turnstile option is migrated when its normal settings
are next saved; if deployment constants override it, no database migration is
needed. Verify an existing authorised application detail page, calling-letter
generation, and CSV export after migration.
