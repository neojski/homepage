<?php
/**
 * Flattened map -> A4 landscape PDF.
 *
 * The drawing must be flattened first: rsvg-convert and Inkscape both ignore
 * CSS custom properties and would render every fill black.
 */
require __DIR__ . '/lib.php';

$conv = converter();
if (!$conv) {
    http_response_code(503);
    header('Content-Type: text/plain');
    exit("No SVG converter found. Install one of:\n  apt install librsvg2-bin   # rsvg-convert\n  apt install inkscape\n");
}
[$path, $bin, $args] = $conv;

$svg = tempnam(sys_get_temp_dir(), 'map') . '.svg';
$pdf = tempnam(sys_get_temp_dir(), 'map') . '.pdf';
file_put_contents($svg, a4_landscape(flatten(load_state())));

$cmd = escapeshellarg($path) . ' ' . implode(' ', array_map('escapeshellarg', $args));
$cmd .= ($bin === 'inkscape' ? '=' : ' ') . escapeshellarg($pdf) . ' ' . escapeshellarg($svg) . ' 2>&1';
$out = shell_exec('HOME=' . escapeshellarg(sys_get_temp_dir()) . ' ' . $cmd);

if (!is_file($pdf) || filesize($pdf) === 0) {
    http_response_code(500); header('Content-Type: text/plain');
    @unlink($svg); @unlink($pdf);
    exit("Conversion failed ($bin):\n$out\n");
}
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="coleshill-cremation-plots.pdf"');
header('Content-Length: ' . filesize($pdf));
readfile($pdf);
@unlink($svg); @unlink($pdf);
