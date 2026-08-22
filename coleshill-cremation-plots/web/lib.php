<?php
/**
 * Shared helpers for the Coleshill cremation plot map.
 *
 * The map SVG carries geometry only: every fill resolves through CSS custom
 * properties, and each cell is tagged data-plot / data-status. Colour and
 * status live in the state JSON, which is the only thing that ever changes.
 */

const STATES   = ['free', 'reserved', 'occupied-reserved', 'occupied', 'unrecorded'];

const MAP_SVG   = __DIR__ . '/map.svg';
const DATA_DIR  = __DIR__ . '/../private';          // must sit outside the web root
const STATE     = DATA_DIR . '/plots-state.json';
const BACKUPS   = DATA_DIR . '/backups';

function map_svg(): string {
    $s = @file_get_contents(MAP_SVG);
    if ($s === false) throw new RuntimeException('map.svg missing - run render.py');
    return $s;
}

/** Plot ids and their surveyed statuses, read straight from the drawing. */
function surveyed(): array {
    $doc = new DOMDocument();
    $doc->loadXML(map_svg());
    $out = [];
    foreach ((new DOMXPath($doc))->query('//*[@data-plot]') as $g) {
        $out[$g->getAttribute('data-plot')] = $g->getAttribute('data-status');
    }
    ksort($out, SORT_NUMERIC);
    return $out;
}

function default_state(): array {
    return [
        'version'  => 1,
        'updated'  => gmdate('c'),
        'palette'  => ['free' => '#ffffff', 'reserved' => '#d9a441', 'occupied' => '#6e8ca8',
                       'ink' => '#1f2933', 'ground' => '#f7f6f3', 'hairline' => '#5b6672'],
        'statuses' => surveyed(),
    ];
}

/** Seeds itself from the drawing the first time it is asked for. */
function load_state(): array {
    if (!is_file(STATE)) {
        @mkdir(DATA_DIR, 0770, true);
        @mkdir(BACKUPS, 0770, true);
        write_state(default_state());
    }
    $s = json_decode(file_get_contents(STATE), true);
    if (!is_array($s)) throw new RuntimeException('state file is not valid JSON');
    return $s;
}

/** Temp file plus atomic rename, so a torn write can never be observed. */
function write_state(array $s): void {
    $tmp = tempnam(DATA_DIR, 'plots');
    file_put_contents($tmp, json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    chmod($tmp, 0640);
    if (!rename($tmp, STATE)) { @unlink($tmp); throw new RuntimeException('could not replace state file'); }
}

/**
 * Statuses only. Every plot in the drawing must be present exactly once, and
 * every value must be one of STATES. This is a burial record; a malformed
 * status is worse than a refused save.
 */
function validate(array $in): array {
    $known = surveyed();
    $st = [];
    foreach ($in['statuses'] ?? [] as $id => $v) {
        if (!isset($known[(string) $id])) throw new InvalidArgumentException("unknown plot $id");
        if (!in_array($v, STATES, true))  throw new InvalidArgumentException("bad status '$v' on plot $id");
        $st[(string) $id] = $v;
    }
    if (count($st) !== count($known))
        throw new InvalidArgumentException(sprintf('expected %d plots, got %d', count($known), count($st)));
    return $st;
}

/** Number colour that stays legible over every half of a cell. */
function lum(string $hex): float {
    $f = function ($c) { $c /= 255; return $c <= .03928 ? $c / 12.92 : (($c + .055) / 1.055) ** 2.4; };
    return .2126 * $f(hexdec(substr($hex, 1, 2)))
         + .7152 * $f(hexdec(substr($hex, 3, 2)))
         + .0722 * $f(hexdec(substr($hex, 5, 2)));
}
function ink_for(string $state, array $p): string {
    $parts = match ($state) {
        'occupied-reserved' => [$p['reserved'], $p['occupied']],
        'unrecorded'        => [$p['ground']],
        default             => [$p[$state]],
    };
    $score = function ($t) use ($parts) {
        return min(array_map(function ($c) use ($t) {
            $a = lum($c); $b = lum($t);
            return (max($a, $b) + .05) / (min($a, $b) + .05);
        }, $parts));
    };
    return $score('#ffffff') > $score($p['ink']) ? '#ffffff' : $p['ink'];
}

/** The palette as CSS custom properties, for the browser to override live. */
function palette_css(array $p): string {
    $out = '';
    foreach ($p as $k => $v) $out .= "--$k:$v;";
    $out .= '--occupied-reserved:' . $p['occupied'] . ';';
    foreach (STATES as $s) $out .= "--on-$s:" . ink_for($s, $p) . ';';
    return $out;
}

/**
 * Resolve every var() and attribute selector into literal presentation
 * attributes. Nothing outside a browser understands CSS custom properties --
 * rsvg-convert and Inkscape both render the unflattened drawing solid black --
 * so this is required for PDF, and for any SVG leaving the site.
 */
function flatten(array $state): string {
    $p = $state['palette'];
    $doc = new DOMDocument();
    $doc->loadXML(map_svg());
    $xp = new DOMXPath($doc);

    foreach ($xp->query('//*[contains(@class,"cell")]') as $g) {
        $id = $g->getAttribute('data-plot');
        $s  = $id !== '' ? ($state['statuses'][$id] ?? $g->getAttribute('data-status'))
                         : $g->getAttribute('data-status');
        $g->setAttribute('data-status', $s);
        $base = match ($s) {
            'unrecorded'        => 'url(#hatch)',
            'occupied-reserved' => $p['reserved'],   // half reserved, half occupied
            default             => $p[$s],
        };
        foreach ($g->childNodes as $el) {
            if (!($el instanceof DOMElement)) continue;
            switch ($el->getAttribute('class')) {
                case 'base': $el->setAttribute('fill', $base); break;
                case 'half':
                    $el->setAttribute('fill', $p['occupied']);
                    if ($s !== 'occupied-reserved') $el->setAttribute('style', 'display:none');
                    break;
                case 'edge': $el->setAttribute('fill', 'none'); $el->setAttribute('stroke', $p['ink']); break;
                case 'num':
                    $el->setAttribute('fill', ink_for($s, $p));
                    $el->setAttribute('font-size', '12.5');
                    $el->setAttribute('text-anchor', 'middle');
                    break;
            }
        }
    }
    foreach ($xp->query('//*[@class]') as $el) {
        switch ($el->getAttribute('class')) {
            case 'ground':     $el->setAttribute('fill', $p['ground']); break;
            case 'title':      $el->setAttribute('fill', $p['ink']); break;
            case 'sub':        $el->setAttribute('fill', $p['hairline']); break;
            case 'rule':       $el->setAttribute('stroke', $p['hairline']); break;
            case 'hatch-bg':   $el->setAttribute('fill', $p['ground']); break;
            case 'hatch-line': $el->setAttribute('stroke', $p['hairline']);
                               $el->setAttribute('stroke-width', '1.4'); break;
        }
    }
    foreach (iterator_to_array($xp->query('//*[local-name()="style"]')) as $st) {
        $st->parentNode->removeChild($st);
    }
    return $doc->saveXML();
}

/**
 * Wrap the flattened map in an A4 landscape sheet. Doing the page geometry
 * here rather than with converter flags means rsvg-convert and Inkscape
 * produce the same page.
 */
function a4_landscape(string $flat): string {
    [$w, $h] = [1303.0, 872.0];
    if (preg_match('/viewBox="0 0 ([\d.]+) ([\d.]+)"/', $flat, $m)) { $w = (float) $m[1]; $h = (float) $m[2]; }
    $PW = 1122.52; $PH = 793.70; $M = 28.0;          // A4 landscape at 96dpi, ~7mm margin
    $s  = min(($PW - 2 * $M) / $w, ($PH - 2 * $M) / $h);
    $tx = ($PW - $w * $s) / 2; $ty = ($PH - $h * $s) / 2;

    $inner = preg_replace('/^.*?<svg[^>]*>/s', '', $flat, 1);
    $inner = preg_replace('/<\/svg>\s*$/s', '', $inner);
    return sprintf(
        '<?xml version="1.0" encoding="UTF-8"?>' .
        '<svg xmlns="http://www.w3.org/2000/svg" width="297mm" height="210mm" viewBox="0 0 %s %s" ' .
        'font-family="Inter, Helvetica Neue, Arial, sans-serif">' .
        '<rect width="%s" height="%s" fill="#ffffff"/><g transform="translate(%.3f %.3f) scale(%.5f)">%s</g></svg>',
        $PW, $PH, $PW, $PH, $tx, $ty, $s, $inner);
}

/** First available SVG->PDF converter, or null. */
function converter(): ?array {
    foreach ([['rsvg-convert', ['-f', 'pdf', '-o']], ['inkscape', ['--export-type=pdf', '--export-filename']]] as [$bin, $args]) {
        $path = trim((string) @shell_exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null'));
        if ($path !== '') return [$path, $bin, $args];
    }
    return null;
}

/**
 * The map for embedding in a page: sized by CSS, coloured by CSS variables.
 *
 * Statuses must be replayed from the state file onto the drawing. map.svg
 * carries the statuses as surveyed, so serving it unmodified would show every
 * saved status change reverting on the next page load.
 */
function map_inline(array $state): string {
    $doc = new DOMDocument();
    $doc->loadXML(map_svg());
    foreach ((new DOMXPath($doc))->query('//*[@data-plot]') as $g) {
        $id = $g->getAttribute('data-plot');
        if (isset($state['statuses'][$id])) $g->setAttribute('data-status', $state['statuses'][$id]);
    }
    $root = $doc->documentElement;
    $root->removeAttribute('width');          // the page sizes it
    $root->removeAttribute('height');
    $root->setAttribute('style', palette_css($state['palette']));
    return $doc->saveXML($root);
}
