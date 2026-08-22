<?php
/**
 * GET  -> current palette and statuses (public)
 * PUT  -> replace them (authenticated by .htaccess)
 *
 * A PUT carries the version it was based on. If the file has moved on since,
 * the write is refused with 409 rather than overwriting someone else's edit.
 */
require __DIR__ . '/lib.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode(load_state(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        http_response_code(405); header('Allow: GET, PUT');
        exit(json_encode(['error' => 'method not allowed']));
    }

    $in = json_decode(file_get_contents('php://input'), true);
    if (!is_array($in)) { http_response_code(400); exit(json_encode(['error' => 'body is not JSON'])); }

    @mkdir(BACKUPS, 0770, true);
    $lock = fopen(DATA_DIR . '/.lock', 'c');
    flock($lock, LOCK_EX);
    try {
        $cur = load_state();
        if (($in['version'] ?? null) !== $cur['version']) {
            http_response_code(409);
            exit(json_encode(['error' => 'stale version',
                              'yours' => $in['version'] ?? null, 'current' => $cur['version']]));
        }
        $clean = validate($in);

        // dated backup of what is about to be replaced, kept before the write
        copy(STATE, BACKUPS . '/plots-state-' . gmdate('Ymd-His') . '.json');

        $next = ['version'  => $cur['version'] + 1,
                 'updated'  => gmdate('c'),
                 'editor'   => $_SERVER['PHP_AUTH_USER'] ?? 'unknown',
                 'palette'  => $clean['palette'],
                 'statuses' => $clean['statuses']];
        write_state($next);
        echo json_encode(['ok' => true, 'version' => $next['version'], 'updated' => $next['updated']]);
    } finally {
        flock($lock, LOCK_UN); fclose($lock);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(422); echo json_encode(['error' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500); echo json_encode(['error' => $e->getMessage()]);
}
