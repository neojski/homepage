<?php
/** Public, read-only map. No editing, no auth. */
require __DIR__ . '/lib.php';
$state = load_state();
?><!doctype html>
<meta charset="utf-8">
<title>Cremation plots — All Saints’ Church, Coleshill</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{margin:0;background:#f4f3ef;padding:22px;
  font-family:Inter,-apple-system,"Segoe UI",Helvetica,Arial,sans-serif}
#map{background:#fffefb;border:1px solid #d9d6cd;border-radius:6px;padding:10px;
  max-width:1400px;margin:0 auto}
#map svg{max-width:100%;height:auto;display:block}
p.foot{max-width:1400px;margin:12px auto 0;font-size:12.5px;color:#6b7280}
p.foot a{color:#3f5f7d}
</style>
<div id="map"><?= map_inline($state) ?></div>
<p class="foot">
  Updated <?= htmlspecialchars(substr($state['updated'], 0, 10)) ?> ·
  <a href="pdf.php">PDF</a> · <a href="svg.php">SVG</a>
</p>
