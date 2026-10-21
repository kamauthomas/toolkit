<?php
// Private, one-time CLI import. The source contains only authorized public names and courses.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
umask(0077);
ini_set('display_errors', '0');
$roots = array('demo' => '/home/bfyigiln/demo.toolkitafrica.ac.ke', 'production' => '/home/bfyigiln/public_html');
$environment = $argv[1] ?? '';
if (!isset($roots[$environment])) exit(2);
define('WP_USE_THEMES', false);
define('DOING_CRON', true);
try {
    $csv = base64_decode('__APPROVED_CSV_BASE64__', true);
    if (false === $csv || !hash_equals('__APPROVED_CSV_SHA256__', hash('sha256', $csv))) throw new RuntimeException('Source checksum mismatch');
    require $roots[$environment] . '/wp-load.php';
    $rows = toolkit_graduands_parse_csv($csv);
    if (is_wp_error($rows) || count($rows) !== 294) throw new RuntimeException('Roster validation failed');
    $done = __DIR__ . '/graduation-published-' . $environment . '-20261021.json';
    if (is_file($done)) {
        $result = json_decode(file_get_contents($done), true);
        if (($result['source_sha256'] ?? '') !== hash('sha256', $csv)) throw new RuntimeException('Existing publication has a different source');
    } else {
        $before = array();
        foreach (array('toolkit_graduands_2026_draft', 'toolkit_graduands_2026_published', 'toolkit_graduands_2026_visible') as $key) $before[$key] = get_option($key, null);
        $backup = __DIR__ . '/graduation-before-' . $environment . '-20261021.json';
        if (is_file($backup)) throw new RuntimeException('Incomplete earlier import; inspect rollback before retry');
        if (false === file_put_contents($backup, json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX)) throw new RuntimeException('Private rollback could not be saved');
        $revision = hash('sha256', wp_json_encode($rows));
        update_option('toolkit_graduands_2026_draft', array('rows' => $rows, 'revision' => $revision), false);
        update_option('toolkit_graduands_2026_published', array('rows' => $rows, 'approved' => true, 'published_at' => gmdate('c'), 'source_sha256' => hash('sha256', $csv), 'publication_method' => 'operator-authorized-cli'), false);
        update_option('toolkit_graduands_2026_visible', true, false);
        $saved = toolkit_graduands_published();
        if (count($saved['rows'] ?? array()) !== 294 || hash('sha256', wp_json_encode($saved['rows'])) !== $revision) throw new RuntimeException('Published data verification failed');
        do_action('litespeed_purge_all');
        wp_cache_flush();
        $courses = array_count_values(array_column($rows, 'course'));
        $result = array('environment' => $environment, 'release' => toolkit_theme_release(), 'rows' => count($rows), 'courses' => $courses, 'source_sha256' => hash('sha256', $csv), 'revision' => $revision, 'published_at' => $saved['published_at'], 'private_fields_imported' => 0);
        if (false === file_put_contents($done, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX)) throw new RuntimeException('Publication record could not be saved');
    }
    $actual = toolkit_graduands_published();
    if (hash('sha256', wp_json_encode($actual['rows'] ?? array())) !== hash('sha256', wp_json_encode($rows))) throw new RuntimeException('The current publication changed; review before continuing');
    // Idempotent verification/refinement must also invalidate previously cached markup.
    do_action('litespeed_purge_all');
    wp_cache_flush();
    echo json_encode($result, JSON_UNESCAPED_SLASHES) . "\nGRADUATION_PUBLISH_OK\n";
    // Leave the completion record and private rollback; the executable import is temporary.
    unlink(__FILE__);
} catch (Throwable $error) {
    fwrite(STDERR, 'GRADUATION_PUBLISH_FAILED ' . get_class($error) . "\n"); exit(1);
}
