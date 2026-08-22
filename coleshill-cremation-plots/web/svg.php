<?php
/** The map as a standalone, flattened SVG - opens correctly outside a browser. */
require __DIR__ . '/lib.php';
header('Content-Type: image/svg+xml');
header('Content-Disposition: attachment; filename="coleshill-cremation-plots.svg"');
echo flatten(load_state());
