# Application form — source and contract reference

Prepared by **Thomas Kamau** · 28 October 2026

Companion to [the technical analysis](APPLICATION-FORM-TECHNICAL-ANALYSIS-2026-10-28.md). This is an implementation inventory, not a server upload manifest. Shared files include unrelated website features; only their admissions connections are described in the main analysis. Reception paths refer to its separate repository and may require both checkouts when following links.

## Source baseline

- WordPress checkout: `75c37e9`; theme release `2026.10.27.1`.
- Reception checkout: `885335e`.
- SHA256 digests below pin reviewed bytes; they do not constitute a fresh live-server checksum.
- Line numbers locate definitions/registrations, not proof that a hook or schedule is currently active.

## WordPress admissions implementation

| File | Lines | SHA256 |
| --- | ---: | --- |
| [wp-content/themes/eduma-child/functions.php](../../wp-content/themes/eduma-child/functions.php) | 2099 | `91fbf0fcf0faaa684d0107fc8a0f4079ed447a9a11027e6cbffc1e0ec31f1446` |
| [wp-content/themes/eduma-child/template-parts/pages/apply.php](../../wp-content/themes/eduma-child/template-parts/pages/apply.php) | 78 | `818b8494624c16554f6b99fb68ff76c9410af8ec4d29f745bd27e7fde5a382ce` |
| [wp-content/themes/eduma-child/assets/js/application-form.js](../../wp-content/themes/eduma-child/assets/js/application-form.js) | 294 | `a15adfecc41f908025096a79c663c29d21df40f86b2a271ebe9535cb08dcb2a5` |
| [wp-content/themes/eduma-child/inc/application-adapter.php](../../wp-content/themes/eduma-child/inc/application-adapter.php) | 1143 | `793704df923af8b680a7735d6a2cdfa09a4d9e437bf8023517a262d44a456ea1` |
| [wp-content/themes/eduma-child/inc/application-relay-queue.php](../../wp-content/themes/eduma-child/inc/application-relay-queue.php) | 143 | `467993dd9a4729d724fd93e54f2886a47b1c09c6d26cc067ceaa8f533b0e3f0a` |
| [wp-content/themes/eduma-child/inc/application-catalog-cache.php](../../wp-content/themes/eduma-child/inc/application-catalog-cache.php) | 128 | `d4e3fdd55a65eec7cca2879d9a227fe4182ace40d1a30b133b64ed3cca9a9dc9` |
| [wp-content/themes/eduma-child/inc/PhoneNumbers.php](../../wp-content/themes/eduma-child/inc/PhoneNumbers.php) | 66 | `867d1fed0421c6575a1d056c574351e43cc2658c0530e8d4f3ec87033ceca78f` |
| [wp-content/themes/eduma-child/inc/PhoneCountries.php](../../wp-content/themes/eduma-child/inc/PhoneCountries.php) | 6 | `1a74038f6c9ae18dafa76fa9f2ceaa74be5f206dcecaa7c0f5ade432d8f1bdd2` |
| [wp-content/themes/eduma-child/assets/js/phone-input.js](../../wp-content/themes/eduma-child/assets/js/phone-input.js) | 79 | `78711d510ca31a666a1bd190180935b20c22f9a0b202d605d135a0a4f9e4366b` |
| [wp-content/themes/eduma-child/assets/js/phone-countries.js](../../wp-content/themes/eduma-child/assets/js/phone-countries.js) | 2 | `bf0b23195444393dc390d61db4cf56e25700e570a64e707a0828a2003274a658` |
| [wp-content/themes/eduma-child/assets/css/phone-input.css](../../wp-content/themes/eduma-child/assets/css/phone-input.css) | 5 | `b1b954cc7816c6f18218d6762df6728448534ab74510976cc19c8c236b288c13` |

## Calling letters and signed interoperability

| File | Lines | SHA256 |
| --- | ---: | --- |
| [wp-content/themes/eduma-child/inc/calling-letters.php](../../wp-content/themes/eduma-child/inc/calling-letters.php) | 1066 | `6e26d2beac8e2928ee916a1e9c5c4e759be81fa717b85a8e06d853d993671bb6` |
| [wp-content/themes/eduma-child/inc/calling-letter-pdf.php](../../wp-content/themes/eduma-child/inc/calling-letter-pdf.php) | 719 | `0a94400fc2ddf27a0bba47f41d0f008f07e2c9a9a857c49db0e232fa3eaf4b8f` |
| [wp-content/themes/eduma-child/inc/private-file-boundary.php](../../wp-content/themes/eduma-child/inc/private-file-boundary.php) | 12 | `df56268d72ce476d876bfeb9f9477737a1295ded83bfd39607c832d257fd3db5` |
| [wp-content/themes/eduma-child/inc/reception-application-sync.php](../../wp-content/themes/eduma-child/inc/reception-application-sync.php) | 104 | `a0a392bad7a33049cde59cbc8829126528846714b91a10d031d8dad16d60043b` |
| [wp-content/themes/eduma-child/inc/reception-calling-letters.php](../../wp-content/themes/eduma-child/inc/reception-calling-letters.php) | 120 | `41a4857d647180f865c90a9c17d3e30bf7f63093552a2eceffccf872d2be9efb` |
| [wp-content/themes/eduma-child/inc/reception-integration.php](../../wp-content/themes/eduma-child/inc/reception-integration.php) | 149 | `be5ee1ad61a7b024ed071f02d210617ce1bbdd0534a28a51583b4d256aa4ec1a` |

## Shared page, admin, catalogue and metrics dependencies

| File | Lines | SHA256 |
| --- | ---: | --- |
| [wp-content/themes/eduma-child/footer.php](../../wp-content/themes/eduma-child/footer.php) | 80 | `218dcedf09b37d7ee1c0f8429d5091a0dd25787d45bd2d1ed2a1082f4c9c5195` |
| [wp-content/themes/eduma-child/brand-tokens.css](../../wp-content/themes/eduma-child/brand-tokens.css) | 71 | `d8c0355eaa5994fdb87724819ce3f889d93e1f75f1c93d56c14beb774bb66183` |
| [wp-content/themes/eduma-child/page-redesign.css](../../wp-content/themes/eduma-child/page-redesign.css) | 474 | `6d578ec19ce9bdb7b461ad2298af30a1b770ecec8482a7ee1d5568dfdd729d1d` |
| [wp-content/themes/eduma-child/page-redesign.js](../../wp-content/themes/eduma-child/page-redesign.js) | 97 | `c6d2b8e317e98e62858561b9d5bf7cc003cd629af3d50606a0399eb1887fe8c2` |
| [wp-content/themes/eduma-child/inc/toolkit-admin.php](../../wp-content/themes/eduma-child/inc/toolkit-admin.php) | 19 | `7b357174f8d74f799e9176f7650de7fa36b40b5c7c7aeaade9ba729120c43c44` |
| [wp-content/themes/eduma-child/inc/support-hub.php](../../wp-content/themes/eduma-child/inc/support-hub.php) | 402 | `932f3144a426c75f69d9247f26b316c75fa4f78de0090f62a9da085fd6960b46` |
| [wp-content/themes/eduma-child/inc/site-metrics.php](../../wp-content/themes/eduma-child/inc/site-metrics.php) | 268 | `1f41a179e0fc4b078efe8b933338d6292869229d3badeb5b2e34c101da39785e` |
| [wp-content/themes/eduma-child/assets/js/toolkit-metrics.js](../../wp-content/themes/eduma-child/assets/js/toolkit-metrics.js) | 83 | `2c73e97d97a8b2bf3b9f1e2459d14ada12a81c73dba18894dad0f14a700bfe25` |
| [wp-content/themes/eduma-child/inc/course-catalog.php](../../wp-content/themes/eduma-child/inc/course-catalog.php) | 182 | `c8f2472f479872801cdf63314b6620329667a738d3c9f006293c36ed680ce48b` |
| [wp-content/themes/eduma-child/inc/seo-discovery.php](../../wp-content/themes/eduma-child/inc/seo-discovery.php) | 94 | `e3376548d4724028732ebed13ddafc7198d1e68f9649b64715247fe49c76da63` |
| [wp-content/themes/eduma-child/assets/images/application-qr.png](../../wp-content/themes/eduma-child/assets/images/application-qr.png) | binary | `ff4dcc1a434c8149c521110fb1792ace2f32b68bc240dba2af077227def6c925` |
| [wp-content/themes/eduma-child/assets/images/application-qr.svg](../../wp-content/themes/eduma-child/assets/images/application-qr.svg) | 2 | `42db019f2bd015d91f154d75892c263a2396a15bf2a6d506cd89298ec3bd4eef` |

## Reception consumers and persistence

| File | Lines | SHA256 |
| --- | ---: | --- |
| [../reception-system/app/Services/WebsiteApplicationSync.php](../../../reception-system/app/Services/WebsiteApplicationSync.php) | 126 | `45c75bd453c3f5bf9b10851e8e0e5c087525f1ebc31b51d7247f53716f45d601` |
| [../reception-system/app/Models/WebsiteApplication.php](../../../reception-system/app/Models/WebsiteApplication.php) | 21 | `8f29f61b89390b42bb1eeb0387b346aeea8b65262c6affe620f0268fb894cb09` |
| [../reception-system/app/Console/Commands/SyncWebsiteApplications.php](../../../reception-system/app/Console/Commands/SyncWebsiteApplications.php) | 17 | `760aeebd44d9d06853120c36756cd544113a16fef7e7b71cebc33d7a392fc589` |
| [../reception-system/app/Http/Controllers/Staff/WebsiteApplicationController.php](../../../reception-system/app/Http/Controllers/Staff/WebsiteApplicationController.php) | 28 | `ea974d1fbe24678b82aad7114c0bcd730bfcbaf5c7bbed52a1fe57a149cb860e` |
| [../reception-system/app/Services/CallingLetterGateway.php](../../../reception-system/app/Services/CallingLetterGateway.php) | 70 | `3bd8d78178226eaa9cece035c75a9c53b3005238d1a5970e009569aafd647388` |
| [../reception-system/app/Http/Controllers/Staff/CallingLetterController.php](../../../reception-system/app/Http/Controllers/Staff/CallingLetterController.php) | 118 | `7fc8b7150859194cee68995deae44778b368d79c73adcbdc59660935f888b1d2` |
| [../reception-system/app/Http/Controllers/Staff/CallingLetterWorkspaceController.php](../../../reception-system/app/Http/Controllers/Staff/CallingLetterWorkspaceController.php) | 186 | `a076f057f97933e8c395da5a739f146e2ad711d38a1850417ef0fefb6c143989` |
| [../reception-system/app/Models/CallingLetter.php](../../../reception-system/app/Models/CallingLetter.php) | 11 | `65f5f0bb71aeaebb953e845d34f05140d477bc6a10aa0015ef7febe95a4aad37` |
| [../reception-system/app/Models/ManualCallingLetter.php](../../../reception-system/app/Models/ManualCallingLetter.php) | 13 | `8d111d9ad99260afe06c408ecb57063c5aa240feeacf9baef13c48f4496f679f` |
| [../reception-system/app/Console/Commands/ApplyReceptionRetention.php](../../../reception-system/app/Console/Commands/ApplyReceptionRetention.php) | 38 | `5754def2a7beb6cc617463e446ab4e57292211a967efedad32f2875b1bc4ab94` |
| [../reception-system/database/migrations/2026_10_26_090000_create_website_applications_table.php](../../../reception-system/database/migrations/2026_10_26_090000_create_website_applications_table.php) | 33 | `5dd163facc9534ef3ed89127ba0d92a9361ac24bf03ac8128998c18a8eaa0427` |
| [../reception-system/config/services.php](../../../reception-system/config/services.php) | 61 | `5207c568803002ec98791b54bfaa374f0bdaf6a9e402ab80ad619c42d1c5736b` |
| [../reception-system/routes/web.php](../../../reception-system/routes/web.php) | 120 | `349bc0cedb8c114b799a2a7fd30ca4101ce877fa2e553793726b56f8235688ba` |
| [../reception-system/routes/console.php](../../../reception-system/routes/console.php) | 7 | `b3fc2cf29684fd4225a507469aee6fcefbeeec2e0c5707652af664badb3d5d8f` |
| [../reception-system/resources/views/staff/website-applications/index.blade.php](../../../reception-system/resources/views/staff/website-applications/index.blade.php) | 16 | `e8a8afb705d48c1175d209307dadaafeb936effc0d6e6d11750a2334578d0e7a` |
| [../reception-system/resources/views/staff/website-applications/show.blade.php](../../../reception-system/resources/views/staff/website-applications/show.blade.php) | 15 | `b78216c085d718c1856950771e7dcd59c3f597a0f4f0f78521661c0b11bacda2` |

## Local tests, data tooling and private worker

| File | Lines | SHA256 |
| --- | ---: | --- |
| [scripts/tests/system-audit-application.php](../../scripts/tests/system-audit-application.php) | 74 | `94e09081a8ae2df2692cf1eeacb138627c8409bc4c166254e66f93eda4e1d661` |
| [scripts/tests/mzizi-relay.php](../../scripts/tests/mzizi-relay.php) | 183 | `5d9bc5a6120fee5ab94d23746c884e4e587f006d4e5b3f613e566766a9296df5` |
| [scripts/tests/reception-application-sync.php](../../scripts/tests/reception-application-sync.php) | 47 | `29849847b54cfb75947aa891ee1162988fde155331014d2f875de0c07d512210` |
| [scripts/tests/reception-calling-letters.php](../../scripts/tests/reception-calling-letters.php) | 107 | `fbd65d465ac224bd10d528022637ba850ce399eaf5d6f5ce2b2a718efbd59769` |
| [scripts/tests/international-phone-parity.js](../../scripts/tests/international-phone-parity.js) | 30 | `4b49ddee2ea08ace7659dbfbda2ad7d312d4d066bd6e5bbe9551604a90987275` |
| [scripts/tests/international-phone-browser.py](../../scripts/tests/international-phone-browser.py) | 72 | `61672b673865d2ef316214611336a7abc9e9dc1a4c5f7f949bc005c488bdfdc8` |
| [scripts/tests/application-qr-metrics.cjs](../../scripts/tests/application-qr-metrics.cjs) | 26 | `dc9a68d00102e27fb16a71e69151b81c80ce8c2fbe4e1e71aa5aecbdfbb2e444` |
| [scripts/data/phone-numbers/generate.py](../../scripts/data/phone-numbers/generate.py) | 24 | `9456278bc6848f5add18701499cbb80d7c4d11ff5069b0e1cf530d506bffec5f` |
| [scripts/data/phone-numbers/source.json](../../scripts/data/phone-numbers/source.json) | 5 | `0dac0b8b31b797857ed7439a4a261465678434436dada466321d2bb833906805` |
| [scripts/data/phone-numbers/LICENSE](../../scripts/data/phone-numbers/LICENSE) | 176 | `a6cba85bc92e0cff7a450b1d873c0eaa2e9fc96bf472df0247a26bec77bf3ff9` |
| [scripts/deployment/mzizi-relay-worker.php](../../scripts/deployment/mzizi-relay-worker.php) | 34 | `ab67506f17a57401e90f770e2f82192898e533d2ed7eb65e9b3b2555d641ad68` |
| [scripts/maintenance/application-encryption-runner.php](../../scripts/maintenance/application-encryption-runner.php) | 72 | `07065872f8400735510d231e6da32da5a945845e1eb24816f01f6e273e618f51` |
| [scripts/deployment/runtime_payload.py](../../scripts/deployment/runtime_payload.py) | 36 | `b05bf30556510d0fe3b55882bd38ae1806e99c31dc5f0d761b418ffdba2791ce` |
| [scripts/deployment/build-runtime-package.py](../../scripts/deployment/build-runtime-package.py) | 53 | `668b8a57cb0920f4e574b7ba6573c4bf45e2ac64e6283a7495d0f797bff891a1` |
| [scripts/deployment/releases/deploy-mzizi-relay-2026.10.17.1.py](../../scripts/deployment/releases/deploy-mzizi-relay-2026.10.17.1.py) | 154 | `90ef2187f63a60d94cb09443405adc2a6d373dedcd0d3b32751de2756c962557` |
| [scripts/deployment/releases/deploy-calling-letters-2026.10.26.py](../../scripts/deployment/releases/deploy-calling-letters-2026.10.26.py) | 207 | `4ce71c5f27cb5a3d1b3124e547e2e871bf167dbb1c60880673c2c18647b888ec` |

## Function, method and class definitions

The following index includes all named definitions in the core admissions/letter/integration modules, browser form/phone modules, metrics collector and Reception consumers. It deliberately does not expand unrelated shared `functions.php` story/gallery/SEO routines or generated country-regex data. Shared entry points are mapped separately below.

### wp-content/themes/eduma-child/assets/js/application-form.js

| Line | Definition |
| ---: | --- |
| 32 | `function api(url, payload)` |
| 52 | `function fillSelect(select, items, placeholder)` |
| 67 | `function showMessage(text, type, action)` |
| 88 | `function autoDismissMessage(delay)` |
| 103 | `function loadOptions()` |
| 121 | `function loadCourses()` |
| 134 | `function loadIntakes()` |
| 147 | `function fieldsIn(panel)` |
| 151 | `function validateContact(field)` |
| 184 | `function validateThrough(index)` |
| 191 | `function validateStep(index)` |
| 203 | `function setStep(index, shouldScroll)` |
| 225 | `function selectedText(name)` |
| 230 | `function renderReview()` |
| 247 | `function escapeHtml(value)` |
| 253 | `function updateCourseGuide()` |

### wp-content/themes/eduma-child/inc/application-adapter.php

| Line | Definition |
| ---: | --- |
| 9 | `function toolkit_mzizi_application_url()` |
| 13 | `function toolkit_mzizi_submission_enabled()` |
| 20 | `function toolkit_mzizi_relay_enabled()` |
| 26 | `function toolkit_application_turnstile_site_key()` |
| 32 | `function toolkit_application_turnstile_secret_key()` |
| 38 | `function toolkit_application_security_enabled()` |
| 44 | `function toolkit_application_security_option( $field )` |
| 51 | `function toolkit_application_security_source( $field )` |
| 57 | `function toolkit_application_table_name()` |
| 62 | `function toolkit_application_install_storage()` |
| 117 | `function toolkit_application_log_event( $application_id, $event_type, $message = '', $actor_id = null )` |
| 163 | `function toolkit_application_configured_keyring()` |
| 182 | `function toolkit_application_current_key()` |
| 198 | `function toolkit_application_decryption_keys()` |
| 209 | `function toolkit_application_encrypt_payload( array $payload )` |
| 231 | `function toolkit_application_decrypt_payload( $envelope )` |
| 264 | `function toolkit_application_encryption_key_status()` |
| 311 | `function toolkit_application_sanitized_storage_payload( array $data )` |
| 339 | `function toolkit_application_store( array $data )` |
| 354 | `function toolkit_application_fingerprint( array $data )` |
| 358 | `function toolkit_application_store_locked( array $data )` |
| 399 | `function toolkit_application_update_record( $id, array $values )` |
| 411 | `function toolkit_application_update_payload_names( $record, array $names )` |
| 469 | `function toolkit_application_form_token( $window_offset = 0 )` |
| 476 | `function toolkit_application_verify_form_token( $token )` |
| 488 | `function toolkit_application_request_guard( WP_REST_Request $request, $limit = 30 )` |
| 523 | `function toolkit_mzizi_session()` |
| 536 | `function toolkit_mzizi_post( $service, $payload, $cookies, $timeout = 8 )` |
| 565 | `function toolkit_application_public_items( $items, $label_key = 'Name' )` |
| 582 | `function toolkit_application_source_items( $items )` |
| 594 | `function toolkit_application_visible_options( array $options )` |
| 605 | `function toolkit_application_counties()` |
| 616 | `function toolkit_application_available_intakes( $course_id, $cookies )` |
| 629 | `function toolkit_application_options( WP_REST_Request $request )` |
| 633 | `function toolkit_application_courses( WP_REST_Request $request )` |
| 637 | `function toolkit_application_intakes( WP_REST_Request $request )` |
| 641 | `function toolkit_application_verify_turnstile( $token )` |
| 659 | `function toolkit_application_normalize_mobile( $value, $country = 'KE' )` |
| 663 | `function toolkit_application_qualified_email( $value )` |
| 667 | `function toolkit_application_submit( WP_REST_Request $request )` |
| 763 | `function toolkit_application_relay_after_response( $record_id )` |
| 776 | `function toolkit_application_resolve_names( $record_id )` |
| 836 | `function toolkit_application_mask_key( $key )` |
| 841 | `function toolkit_application_render_settings_page()` |
| 926 | `function toolkit_application_csv_cell( $value )` |
| 963 | `function toolkit_application_status_label( $status )` |
| 979 | `function toolkit_application_admin_header( $title, $description )` |
| 984 | `function toolkit_application_render_admin_page()` |
| 1068 | `function toolkit_application_render_record( $record )` |

### wp-content/themes/eduma-child/inc/application-relay-queue.php

| Line | Definition |
| ---: | --- |
| 4 | `function toolkit_application_relay_error_summary( $error, $phase )` |
| 12 | `function toolkit_application_relay_finish( $record, array $values, $event, $message )` |
| 21 | `function toolkit_application_relay_preflight_failure( $record, $error, $phase, $validation = false )` |
| 36 | `function toolkit_application_relay_record( $record_id, $release_source = 'manual' )` |
| 122 | `function toolkit_application_process_relay_queue()` |

### wp-content/themes/eduma-child/inc/application-catalog-cache.php

| Line | Definition |
| ---: | --- |
| 4 | `function toolkit_application_catalog_option( $key )` |
| 8 | `function toolkit_application_catalog_entry( $key )` |
| 22 | `function toolkit_application_catalog_fetch( $key )` |
| 54 | `function toolkit_application_catalog_refresh( $key, $force = false )` |
| 79 | `function toolkit_application_catalog_get( $key )` |
| 85 | `function toolkit_application_catalog_response( $request, $type )` |
| 107 | `function toolkit_application_refresh_catalogues()` |

### wp-content/themes/eduma-child/inc/PhoneNumbers.php

| Line | Definition |
| ---: | --- |
| 6 | `class PhoneNumbers` |
| 8 | `public static function countries(): array` |
| 14 | `public static function normalize(mixed $value, mixed $country = 'KE'): ?string` |
| 47 | `public static function countryFor(mixed $value, string $preferred = 'KE'): ?string` |
| 61 | `public static function display(mixed $value): ?string` |

### wp-content/themes/eduma-child/assets/js/phone-input.js

| Line | Definition |
| ---: | --- |
| 4 | `function normalize(value, country)` |
| 33 | `function countryFor(value, preferred)` |
| 40 | `function selector(field)` |
| 41 | `function validate(field)` |
| 51 | `function init()` |
| 59 | `function update(convert, detect)` |

### wp-content/themes/eduma-child/inc/calling-letters.php

| Line | Definition |
| ---: | --- |
| 12 | `function toolkit_calling_letter_table_name()` |
| 17 | `function toolkit_calling_letter_install_storage()` |
| 92 | `function toolkit_calling_letter_private_dir()` |
| 108 | `function toolkit_calling_letter_template_path()` |
| 123 | `function toolkit_calling_letter_output_dir()` |
| 133 | `function toolkit_calling_letter_file_path( $application_id, $reference )` |
| 143 | `function toolkit_calling_letter_pdf_file_path( $application_id, $reference )` |
| 147 | `function toolkit_calling_letter_ordinal_date( $timestamp = null )` |
| 168 | `function toolkit_calling_letter_use_ziparchive()` |
| 174 | `function toolkit_calling_letter_load_pclzip()` |
| 181 | `function toolkit_calling_letter_zip_entries( $path )` |
| 201 | `function toolkit_calling_letter_zip_read( $path, $entry )` |
| 220 | `function toolkit_calling_letter_rmdir( $dir )` |
| 231 | `function toolkit_calling_letter_files_in( $dir )` |
| 248 | `function toolkit_calling_letter_zip_write( $template, $destination, array $replacements )` |
| 317 | `function toolkit_calling_letter_find_header_entry( $path )` |
| 329 | `function toolkit_calling_letter_expand_header_contact_box( $header_xml )` |
| 431 | `function toolkit_calling_letter_docx_paragraphs( DOMXPath $xpath )` |
| 435 | `function toolkit_calling_letter_replace_run( DOMElement $paragraph, DOMXPath $xpath, $needle, $replacement )` |
| 447 | `function toolkit_calling_letter_paragraph_text( DOMElement $paragraph, DOMXPath $xpath )` |
| 460 | `function toolkit_calling_letter_render_docx( $template_path, $destination_path, array $tokens )` |
| 576 | `function toolkit_calling_letter_channel_enabled( $channel )` |
| 582 | `function toolkit_calling_letter_send_email( $letter_row, $payload )` |
| 608 | `function toolkit_calling_letter_send_sms( $letter_row, $payload )` |
| 612 | `function toolkit_calling_letter_send_print( $letter_row, $payload )` |
| 630 | `function toolkit_calling_letter_deliver( $application_id, $force = false )` |
| 672 | `* attempt rather than letting a missing class throw a fatal mid-submission.` |
| 674 | `function toolkit_calling_letter_renderer_available()` |
| 692 | `function toolkit_calling_letter_generate_safely( $application_id )` |
| 717 | `function toolkit_calling_letter_generate( $application_id, $deliver = false )` |
| 743 | `* this exact function for the same application. Record the deferred` |
| 813 | `function toolkit_calling_letter_upsert( $application_id, array $fields )` |
| 946 | `function toolkit_calling_letter_status_label( $status )` |
| 956 | `function toolkit_calling_letter_channel_label( $status )` |
| 968 | `function toolkit_calling_letter_render_admin_page()` |

### wp-content/themes/eduma-child/inc/calling-letter-pdf.php

| Line | Definition |
| ---: | --- |
| 24 | `function toolkit_pdf_widths( $bold )` |
| 48 | `function toolkit_pdf_winansi( $text )` |
| 95 | `function toolkit_pdf_text_width( $text, $bold, $size )` |
| 106 | `function toolkit_pdf_escape( $text )` |
| 117 | `function toolkit_pdf_png_decode( $binary, $ink_only = false )` |
| 235 | `class Toolkit_Pdf_Page` |
| 243 | `protected function flip( $y )` |
| 247 | `public function rect( $x, $y, $width, $height, array $rgb )` |
| 260 | `public function text( $x, $baseline, $string, $bold, $size, array $rgb = array( 0, 0, 0 ), $italic = false )` |
| 278 | `public function line( $x1, $y1, $x2, $y2, $thickness, array $rgb )` |
| 292 | `public function image( $handle, array $decoded, $x, $y, $width, $height )` |
| 305 | `public function output()` |
| 389 | `function toolkit_pdf_wrap_runs( array $runs, $size, $max_width )` |
| 414 | `function toolkit_pdf_line_width( array $line, $size )` |
| 426 | `function toolkit_pdf_draw_paragraph( Toolkit_Pdf_Page $page, array $runs, $x, $y, $max_width, $size, $leading, $options = array() )` |
| 467 | `function toolkit_calling_letter_pdf_asset( $entry, $ink_only = false )` |
| 500 | `function toolkit_calling_letter_intake_label()` |
| 510 | `function toolkit_calling_letter_render_pdf( $destination_path, array $tokens, array $options = array() )` |

### wp-content/themes/eduma-child/inc/private-file-boundary.php

| Line | Definition |
| ---: | --- |
| 3 | `function toolkit_confined_private_file( $path, array $roots, $expected_filename )` |

### wp-content/themes/eduma-child/inc/reception-application-sync.php

| Line | Definition |
| ---: | --- |
| 4 | `function toolkit_reception_application_sync_enabled()` |
| 12 | `function toolkit_reception_application_sync_permission( WP_REST_Request $request )` |
| 47 | `function toolkit_reception_application_sync_page( WP_REST_Request $request )` |

### wp-content/themes/eduma-child/inc/reception-calling-letters.php

| Line | Definition |
| ---: | --- |
| 5 | `function toolkit_reception_letters_enabled()` |
| 12 | `function toolkit_reception_letter_permission( WP_REST_Request $request )` |
| 42 | `function toolkit_reception_pdf_response( $path, $reference, array $metadata = array() )` |
| 56 | `function toolkit_reception_existing_letter( WP_REST_Request $request )` |
| 69 | `function toolkit_reception_render_native_letter( WP_REST_Request $request )` |

### wp-content/themes/eduma-child/inc/reception-integration.php

| Line | Definition |
| ---: | --- |
| 9 | `function toolkit_reception_api_url()` |
| 14 | `function toolkit_reception_api_secret()` |
| 19 | `function toolkit_reception_submission_enabled()` |
| 24 | `function toolkit_reception_request_origin_is_valid( WP_REST_Request $request )` |
| 40 | `function toolkit_reception_rate_limit_key()` |
| 45 | `function toolkit_reception_submit( WP_REST_Request $request )` |

### wp-content/themes/eduma-child/inc/site-metrics.php

| Line | Definition |
| ---: | --- |
| 7 | `function toolkit_metrics_table_name()` |
| 13 | `function toolkit_metrics_install_storage()` |
| 77 | `function toolkit_record_site_metric( WP_REST_Request $request )` |
| 152 | `function toolkit_metrics_summary( $days = 30 )` |
| 172 | `function toolkit_application_qr_scans( $days = 30 )` |
| 183 | `function toolkit_dashboard_counts()` |
| 196 | `function toolkit_render_metrics_page()` |
| 240 | `function toolkit_render_analytics_page()` |
| 266 | `function toolkit_render_control_toggle( $name, $label, $enabled, $description )` |

### wp-content/themes/eduma-child/assets/js/toolkit-metrics.js

| Line | Definition |
| ---: | --- |
| 11 | `function send(event, value, label)` |
| 46 | `function measureScroll()` |
| 78 | `function finish()` |

### ../reception-system/app/Services/WebsiteApplicationSync.php

| Line | Definition |
| ---: | --- |
| 15 | `class WebsiteApplicationSync` |
| 19 | `public static function source(): ?array` |
| 29 | `public static function state(): array` |
| 36 | `private function saveState(array $state): void` |
| 41 | `public function run(): array` |

### ../reception-system/app/Models/WebsiteApplication.php

| Line | Definition |
| ---: | --- |
| 8 | `class WebsiteApplication extends Model` |
| 12 | `protected function casts(): array` |

### ../reception-system/app/Console/Commands/SyncWebsiteApplications.php

| Line | Definition |
| ---: | --- |
| 5 | `class SyncWebsiteApplications extends Command` |
| 9 | `public function handle(WebsiteApplicationSync $sync): int` |

### ../reception-system/app/Http/Controllers/Staff/WebsiteApplicationController.php

| Line | Definition |
| ---: | --- |
| 9 | `class WebsiteApplicationController extends Controller` |
| 11 | `public function index(Request $request): View` |
| 22 | `public function show(Request $request, WebsiteApplication $websiteApplication): View` |

### ../reception-system/app/Services/CallingLetterGateway.php

| Line | Definition |
| ---: | --- |
| 10 | `class CallingLetterGateway` |
| 12 | `public static function enabled(): bool` |
| 19 | `public function website(WebsiteApplication $application): array` |
| 26 | `public function render(array $details): array` |
| 31 | `private function request(string $method, string $resource, string $path, array $details, string $reference, ?int $applicationId = null): array` |

### ../reception-system/app/Http/Controllers/Staff/CallingLetterController.php

| Line | Definition |
| ---: | --- |
| 17 | `class CallingLetterController extends Controller` |
| 19 | `private function available(bool $native = false): void` |
| 25 | `private function audit(Request $request, string $action, string $type, int $id, array $metadata = []): void` |
| 31 | `private function pdf(string $bytes, string $filename)` |
| 37 | `public function website(Request $request, WebsiteApplication $websiteApplication, CallingLetterGateway $gateway)` |
| 50 | `public function index(Request $request, Applicant $applicant)` |
| 57 | `public function issue(Request $request, Applicant $applicant, CallingLetterGateway $gateway)` |
| 108 | `public function download(Request $request, Applicant $applicant, CallingLetter $callingLetter)` |

### ../reception-system/app/Http/Controllers/Staff/CallingLetterWorkspaceController.php

| Line | Definition |
| ---: | --- |
| 16 | `class CallingLetterWorkspaceController extends Controller` |
| 21 | `private function available(): void` |
| 27 | `private function page(string $view, array $data = [])` |
| 32 | `private function audit(Request $request, string $action, int $id, array $metadata = []): void` |
| 38 | `public function index(Request $request)` |
| 74 | `public function create(Request $request)` |
| 84 | `private function details(Request $request): array` |
| 100 | `public function review(Request $request)` |
| 108 | `public function editDetails(Request $request)` |
| 115 | `public function store(Request $request, CallingLetterGateway $gateway)` |
| 153 | `public function show(Request $request, ManualCallingLetter $letter)` |
| 161 | `public function download(Request $request, ManualCallingLetter $letter)` |
| 171 | `public function destroy(Request $request, ManualCallingLetter $letter)` |

### ../reception-system/app/Models/CallingLetter.php

| Line | Definition |
| ---: | --- |
| 5 | `class CallingLetter extends Model` |
| 9 | `protected function casts(): array` |
| 10 | `public function issuer()` |

### ../reception-system/app/Models/ManualCallingLetter.php

| Line | Definition |
| ---: | --- |
| 7 | `class ManualCallingLetter extends Model` |
| 11 | `protected function casts(): array` |
| 12 | `public function issuer()` |

### ../reception-system/app/Console/Commands/ApplyReceptionRetention.php

| Line | Definition |
| ---: | --- |
| 14 | `class ApplyReceptionRetention extends Command` |
| 19 | `public function handle(): int` |

## Shared WordPress routing, assets, release and headers

| Source location in `functions.php` | Responsibility |
| --- | --- |
| 3–16 | Module loading: metrics, adapter, PDF, letters, Reception feed/render and shared modules |
| 31 | Theme release used in assets and custom-surface header |
| 186 | Release + filemtime asset version; following init callback purges caches |
| 211–223 | Configured redesign switch and host defaults |
| 250–305 | Parent/brand/page assets; phone dependencies; form URLs/token/sitekey/contact localization |
| Around400–550 | Page template map, application alias rewrite, canonical redirect suppression and legacy redirects |
| Around938,1097,1169–1246 | Page metadata, canonical, Yoast schema/breadcrumbs and sitemap URL aliases |
| Around1647 | Public response security headers |

## Hook and route registrations in the admissions modules

Locations below are registration sites. Callbacks can also be invoked directly by the private worker, a CLI command or tests. Named public REST routes have additional checks inside their callbacks.

### wp-content/themes/eduma-child/inc/application-adapter.php

| Line | Registration/emission |
| ---: | --- |
| 127 | `add_action( 'after_switch_theme', 'toolkit_application_install_storage' );` |
| 128 | `add_action( 'init', 'toolkit_application_install_storage', 3 );` |
| 130 | `add_filter( 'cron_schedules', function( $schedules ) {` |
| 134 | `add_action( 'init', function() {` |
| 143 | `add_action( 'toolkit_application_process_queue', 'toolkit_application_process_relay_queue' );` |
| 145 | `add_action( 'toolkit_application_resolve_course_names', function() {` |
| 270 | `add_action( 'cli_init', function() {` |
| 392 | `do_action( 'toolkit_application_stored', $new_id, $reference, $data );` |
| 424 | `add_action( 'rest_api_init', function() {` |
| 425 | `register_rest_route( 'toolkit/v1', '/application/options', array(` |
| 430 | `register_rest_route( 'toolkit/v1', '/application/courses', array(` |
| 435 | `register_rest_route( 'toolkit/v1', '/application/intakes', array(` |
| 440 | `register_rest_route( 'toolkit/v1', '/application/submit', array(` |
| 627 | `add_action( 'toolkit_application_process_queue', 'toolkit_application_refresh_catalogues', 20 );` |
| 765 | `add_action( 'shutdown', function () use ( $record_id ) {` |
| 799 | `do_action( 'toolkit_application_names_resolved', (int) $record_id );` |
| 804 | `add_action( 'admin_menu', function() {` |
| 809 | `add_action( 'admin_post_toolkit_application_save_settings', function() {` |
| 863 | `add_action( 'admin_post_toolkit_application_workflow', function() {` |
| 884 | `add_action( 'admin_post_toolkit_application_release', function() {` |
| 899 | `add_action( 'admin_post_toolkit_application_confirm_delivery', function() {` |
| 916 | `add_action( 'admin_post_toolkit_application_resolve_names', function() {` |
| 931 | `add_action( 'admin_post_toolkit_application_export', function() {` |

### wp-content/themes/eduma-child/inc/calling-letters.php

| Line | Registration/emission |
| ---: | --- |
| 62 | `add_action( 'after_switch_theme', 'toolkit_calling_letter_install_storage' );` |
| 63 | `add_action( 'init', 'toolkit_calling_letter_install_storage', 3 );` |
| 71 | `add_action( 'after_switch_theme', 'toolkit_calling_letter_private_dir' );` |
| 72 | `add_action( 'init', 'toolkit_calling_letter_private_dir', 3 );` |
| 850 | `add_action( 'toolkit_application_stored', 'toolkit_calling_letter_generate_safely' );` |
| 854 | `add_action( 'toolkit_application_names_resolved', 'toolkit_calling_letter_generate_safely' );` |
| 858 | `add_action( 'admin_post_toolkit_calling_letter_download', function() {` |
| 890 | `add_action( 'admin_post_toolkit_calling_letter_regenerate', function() {` |
| 907 | `add_action( 'admin_post_toolkit_calling_letter_send', function() {` |
| 928 | `add_action( 'admin_post_toolkit_calling_letter_save_settings', function() {` |
| 942 | `add_action( 'admin_menu', function() {` |

### wp-content/themes/eduma-child/inc/reception-application-sync.php

| Line | Registration/emission |
| ---: | --- |
| 98 | `add_action( 'rest_api_init', static function () {` |
| 99 | `register_rest_route( 'toolkit/v1', '/reception/applications', array(` |

### wp-content/themes/eduma-child/inc/reception-calling-letters.php

| Line | Registration/emission |
| ---: | --- |
| 113 | `add_action( 'rest_api_init', static function () {` |
| 114 | `register_rest_route( 'toolkit/v1', '/reception/applications/(?P<id>[0-9]+)/calling-letter', array(` |
| 117 | `register_rest_route( 'toolkit/v1', '/reception/calling-letters/render', array(` |

### wp-content/themes/eduma-child/inc/reception-integration.php

| Line | Registration/emission |
| ---: | --- |
| 139 | `add_action( 'rest_api_init', function() {` |
| 140 | `register_rest_route(` |

### wp-content/themes/eduma-child/inc/site-metrics.php

| Line | Registration/emission |
| ---: | --- |
| 55 | `add_action( 'after_switch_theme', 'toolkit_metrics_install_storage' );` |
| 56 | `add_action( 'init', 'toolkit_metrics_install_storage', 2 );` |
| 58 | `add_action( 'wp_enqueue_scripts', function() {` |
| 69 | `add_action( 'rest_api_init', function() {` |
| 70 | `register_rest_route( 'toolkit/v1', '/metrics', array(` |
| 122 | `add_action( 'admin_menu', function() {` |
| 127 | `add_action( 'admin_enqueue_scripts', function( $hook ) {` |
| 135 | `add_action( 'admin_post_toolkit_save_controls', function() {` |

## Constants, options and environment-variable names

Names are code identifiers, not secret values. Dynamic option expressions are retained as patterns. Constant presence alone does not prove active configuration; the managed-settings precedence in the analysis applies.

### wp-content/themes/eduma-child/functions.php

- Constants: `TOOLKIT_REDESIGN_ENABLED`.
- Options/literal option prefixes: `eduma_child_apply_route_version`, `eduma_child_reception_route_version`, `toolkit_theme_release`.

### wp-content/themes/eduma-child/inc/application-adapter.php

- Constants: `TOOLKIT_APPLICATION_ENCRYPTION_CURRENT_KEY_ID`, `TOOLKIT_APPLICATION_ENCRYPTION_KEY`, `TOOLKIT_APPLICATION_ENCRYPTION_KEYS`, `TOOLKIT_APPLICATION_TURNSTILE_ENABLED`, `TOOLKIT_APPLICATION_TURNSTILE_SECRET_KEY`, `TOOLKIT_APPLICATION_TURNSTILE_SITE_KEY`, `TOOLKIT_MZIZI_SUBMISSION_ENABLED`.
- Options/literal option prefixes: `toolkit_application_relay_last_run`, `toolkit_application_resolver_last_run`, `toolkit_application_security_audit`, `toolkit_application_security_keys`, `toolkit_application_settings_managed`, `toolkit_application_storage_version`, `toolkit_application_turnstile_enabled`, `toolkit_mzizi_relay_enabled`.

### wp-content/themes/eduma-child/inc/application-relay-queue.php

- Constants: `TOOLKIT_MZIZI_AUTOMATIC_RELEASE_ENABLED`.
- Options/literal option prefixes: `toolkit_application_relay_last_run`.

### wp-content/themes/eduma-child/inc/application-catalog-cache.php

- Options/literal option prefixes: `toolkit_application_catalog_last_run`.

### wp-content/themes/eduma-child/inc/calling-letters.php

- Constants: `TOOLKIT_CALLING_LETTER_FORCE_PCLZIP`, `TOOLKIT_CALLING_LETTER_WORD_NS`.
- Options/literal option prefixes: `toolkit_calling_letter_email_enabled`, `toolkit_calling_letter_storage_version`, `toolkit_calling_letter_{$channel}_enabled`.

### wp-content/themes/eduma-child/inc/calling-letter-pdf.php

- Options/literal option prefixes: `toolkit_calling_letter_intake`.

### wp-content/themes/eduma-child/inc/reception-application-sync.php

- Constants: `TOOLKIT_RECEPTION_APPLICATION_SYNC_ENABLED`.
- Options/literal option prefixes: `toolkit_reception_application_sync_enabled`.

### wp-content/themes/eduma-child/inc/reception-calling-letters.php

- Constants: `TOOLKIT_RECEPTION_CALLING_LETTERS_ENABLED`.
- Options/literal option prefixes: `toolkit_reception_calling_letters_enabled`.

### wp-content/themes/eduma-child/inc/reception-integration.php

- Constants: `TOOLKIT_RECEPTION_API_SECRET`, `TOOLKIT_RECEPTION_API_URL`, `TOOLKIT_RECEPTION_FORM_ENABLED`.

### wp-content/themes/eduma-child/inc/site-metrics.php

- Options/literal option prefixes: `toolkit_metrics_option_migrated`, `toolkit_metrics_storage_version`, `toolkit_site_metrics`.

### ../reception-system/config/services.php

- Reception environment identifiers: `APP_URL`, `AWS_ACCESS_KEY_ID`, `AWS_DEFAULT_REGION`, `AWS_SECRET_ACCESS_KEY`, `CALLING_LETTERS_ENABLED`, `POSTMARK_API_KEY`, `RECEPTION_WEBSITE_SHARED_SECRET`, `RECEPTION_WEBSITE_SIGNATURE_TOLERANCE`, `RESEND_API_KEY`, `SLACK_BOT_USER_DEFAULT_CHANNEL`, `SLACK_BOT_USER_OAUTH_TOKEN`, `WEBSITE_APPLICATIONS_SOURCE_URL`, `WHATSAPP_ACCESS_TOKEN`, `WHATSAPP_APP_SECRET`, `WHATSAPP_BUSINESS_ACCOUNT_ID`, `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_WEBHOOK_VERIFY_TOKEN`.

## Contracts to preserve when editing

| Boundary | Invariant |
| --- | --- |
| Browser → public REST | Public HMAC window token; explicit same-origin JSON; no stale cached user nonce |
| Public save → DB | Validate and normalize first, encrypted allowlisted payload, serialized fingerprint check, durable reference |
| DB → Mzizi | Fresh session/live catalogue; exact18 keys; submitting marker before irreversible POST |
| Relay completion | Only owning lease can finish; ambiguous submissions never auto-replay |
| Catalogue → browser | Last-good public data; known-key refresh; no personal information in snapshots |
| Website → Reception mirror | GET-only signed cursor/limit/purpose;14 fields; no-store; source/environment/strict cursor |
| Reception → website PDF | Signed method/resource/body; private confined file or stateless render; bounded integrity-checked PDF |
| Admin → record/export | manage_options + scoped nonce; safe SQL/escaping; no anonymous mutation |
| Metrics → reports | Anonymous aggregate arrivals, not authenticated applicants or confirmed upstream conversions |
| Git → server | Exact allowlisted runtime files only; fresh drift check, demo first, private evidence |

## Related detailed runbooks

- [Mzizi relay](../MZIZI-RELAY.md)
- [Encryption keyring](../APPLICATION-ENCRYPTION-KEYRING.md)
- [Application QR](../APPLICATION-QR.md)
- [Toolkit admin and Reception sync](../TOOLKIT-ADMIN-RECEPTION-SYNC-2026-10-26.md)
- [Reception calling letters](../RECEPTION-CALLING-LETTERS-2026-10-26.md)
- Reception `docs/CALLING-LETTER-WORKSPACE.md` for standalone/manual/native issue flow.

Private source/test evidence is local under ignored `reports/application-architecture-2026-10-28/`. These documents and the evidence directory must never be included in runtime deployment payloads.

## Error-code index

These are literal WP_Error codes in the core adapters. A dash means no explicit numeric REST status is set at that construction site; internal callers may classify/wrap it. This is not a promise that every internal code is returned verbatim to a browser.

### application-adapter.php

| Line | Code | Explicit status |
| ---: | --- | ---: |
| 211 | `application_encryption` | — |
| 219 | `application_encryption` | — |
| 234 | `application_decryption_format` | — |
| 238 | `application_decryption_format` | — |
| 244 | `application_decryption_damaged` | — |
| 252 | `application_decryption_key_missing` | — |
| 261 | `application_decryption_auth` | — |
| 345 | `application_busy` | 503 |
| 370 | `duplicate_application` | 409 |
| 396 | `application_storage` | 500 |
| 421 | `application_storage` | — |
| 505 | `invalid_origin` | 403 |
| 509 | `invalid_token` | 403 |
| 517 | `rate_limited` | 429 |
| 530 | `mzizi_unavailable` | 503 |
| 533 | `mzizi_session` | 503 |
| 548 | `mzizi_unavailable` | 503 |
| 560 | `mzizi_response` | 502 |
| 674 | `invalid_submission` | 400 |
| 678 | `captcha_failed` | 422 |
| 684 | `missing_field` | 422 |
| 691 | `invalid_email` | 422 |
| 694 | `invalid_gender` | 422 |
| 697 | `invalid_consent` | 422 |
| 701 | `invalid_study_mode` | 422 |
| 705 | `invalid_sponsorship` | 422 |
| 708 | `invalid_county` | 422 |
| 714 | `invalid_phone` | 422 |
| 779 | `application_missing` | — |
| 785 | `school_lookup` | — |
| 788 | `name_lookup` | — |
| 793 | `course_lookup` | — |

### application-relay-queue.php

| Line | Code | Explicit status |
| ---: | --- | ---: |
| 16 | `relay_storage` | — |
| 33 | `relay_not_sent` | 503 |
| 38 | `invalid_release_source` | — |
| 39 | `relay_disabled` | — |
| 40 | `duplicate_check_required` | — |
| 51 | `relay_not_claimed` | — |
| 53 | `relay_storage` | — |
| 57 | `invalid_stored_choices` | — |
| 65 | `invalid_school` | — |
| 75 | `stale_selection` | — |
| 77 | `invalid_source` | — |
| 102 | `relay_storage` | — |
| 114 | `delivery_unconfirmed` | 202 |

### application-catalog-cache.php

| Line | Code | Explicit status |
| ---: | --- | ---: |
| 31 | `invalid_catalogue` | — |
| 37 | `invalid_school` | — |
| 41 | `invalid_catalogue` | — |
| 48 | `invalid_catalogue` | — |
| 51 | `invalid_catalogue_key` | — |
| 56 | `invalid_catalogue_key` | — |
| 58 | `catalogue_busy` | — |
| 63 | `catalogue_cooldown` | — |
| 93 | `invalid_selection` | 422 |
| 102 | `invalid_selection` | 422 |

### reception-application-sync.php

| Line | Code | Explicit status |
| ---: | --- | ---: |
| 14 | `sync_unavailable` | 503 |
| 22 | `sync_request` | 400 |
| 30 | `sync_auth` | 401 |
| 34 | `sync_auth` | 401 |
| 42 | `sync_replay` | 409 |
| 54 | `sync_storage` | 503 |
| 61 | `sync_storage` | 503 |
| 66 | `sync_storage` | 503 |

### reception-calling-letters.php

| Line | Code | Explicit status |
| ---: | --- | ---: |
| 13 | `letters_unavailable` | 503 |
| 19 | `letters_request` | 400 |
| 27 | `letters_auth` | 401 |
| 31 | `letters_auth` | 401 |
| 37 | `letters_replay` | 409 |
| 45 | `letters_not_ready` | 404 |
| 61 | `letters_not_ready` | 404 |
| 65 | `letters_not_ready` | 404 |
| 73 | `letters_request` | 422 |
| 78 | `letters_request` | 422 |
| 83 | `letters_request` | 422 |
| 87 | `letters_layout` | 422 |
| 107 | `letters_render` | 503 |

## Event vocabulary

Literal event names are indexed below; some paths choose event names dynamically based on retry/recovery outcome. Read the state-machine discussion alongside these names. They are operational events, not applicant-visible status endpoints.

- `application-adapter.php`: `applications_exported`, `delivery_reconciled`, `names_resolved`, `queued`, `received`, `stored`.

- `application-relay-queue.php`: `relay_started`.

- `calling-letters.php`: `calling_letter_email_failed`, `calling_letter_email_submitted`, `calling_letter_failed`, `calling_letter_generated`, `calling_letter_pdf_failed`.
