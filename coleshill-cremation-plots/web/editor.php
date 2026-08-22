<?php
require __DIR__ . '/lib.php';
$state = load_state();
?><!doctype html>
<meta charset="utf-8">
<title>Coleshill cremation plots — map editor</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root{--bg:#f4f3ef;--panel:#fffefb;--line:#d9d6cd;--text:#1f2933;--muted:#6b7280;--accent:#3f5f7d;
  font-family:Inter,-apple-system,"Segoe UI",Helvetica,Arial,sans-serif}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-size:14px}
header{display:flex;align-items:center;gap:18px;flex-wrap:wrap;
  padding:10px 20px;border-bottom:1px solid var(--line);background:var(--panel)}
header h1{font-size:14px;margin:0;font-weight:600}
.right{margin-left:auto;display:flex;align-items:center;gap:8px}
#msg{font-size:12px;color:var(--muted)}
#msg.dirty{color:#b45309}#msg.bad{color:#a4302a}#msg.good{color:#2f6b45}
a.btn,button{font:inherit;font-size:13px;padding:6px 12px;border:1px solid var(--line);
  background:#fff;border-radius:5px;cursor:pointer;color:var(--text);text-decoration:none;display:inline-block}
a.btn:hover,button:hover{border-color:var(--accent)}
button.primary{background:var(--accent);color:#fff;border-color:var(--accent)}
button[disabled]{opacity:.5;cursor:default}
main{padding:22px}
#map{background:var(--panel);border:1px solid var(--line);border-radius:6px;padding:10px}
#map svg{max-width:100%;height:auto;display:block}
#map svg .cell[data-plot]{cursor:pointer}
#map svg .cell[data-plot]:hover .edge{stroke-width:2.6}
#map svg .cell.sel .edge{stroke:var(--accent);stroke-width:3}
#modal{position:fixed;inset:0;background:rgba(31,41,51,.42);display:flex;align-items:center;
  justify-content:center;z-index:9}
#modal[hidden]{display:none}
.card{background:var(--panel);border-radius:8px;padding:20px 22px;width:330px;
  box-shadow:0 14px 40px rgba(0,0,0,.28)}
.card h3{margin:0 0 3px;font-size:17px}
.card .meta{color:var(--muted);font-size:12.5px;margin-bottom:15px}
.card select{width:100%;font:inherit;font-size:14px;padding:8px;border:1px solid var(--line);
  border-radius:5px;background:#fff;margin-bottom:18px}
.card .btns{display:flex;gap:8px;justify-content:flex-end}
</style>

<header>
  <h1>Coleshill cremation plots</h1>
  <div class="right">
    <span id="msg">v<?= (int) $state['version'] ?></span>
    <button id="save" class="primary" disabled>Save</button>
    <a class="btn" href="pdf.php">PDF</a>
    <a class="btn" href="svg.php">SVG</a>
  </div>
</header>

<main><div id="map"><?= map_inline($state) ?></div></main>

<div id="modal" hidden>
  <div class="card">
    <h3 id="mTitle">Plot</h3>
    <div class="meta" id="mMeta"></div>
    <select id="mSelect"></select>
    <div class="btns"><button id="mCancel">Cancel</button><button id="mOk" class="primary">OK</button></div>
  </div>
</div>

<script>
const svg = document.querySelector('#map svg');
const ALL = ['free','reserved','occupied-reserved','occupied','unrecorded'];
const LABEL = {free:'Free', reserved:'Reserved',
               'occupied-reserved':'Occupied, reserved for further ashes',
               occupied:'Occupied', unrecorded:'No status recorded'};
let version = <?= (int) $state['version'] ?>;
let dirty   = false;
const msg = document.getElementById('msg'), saveBtn = document.getElementById('save');
function setDirty(){ dirty = true; saveBtn.disabled = false;
  msg.className = 'dirty'; msg.textContent = 'unsaved changes'; }

/* click a plot -> modal -> dropdown -> Cancel / OK */
const modal = document.getElementById('modal'), sel = document.getElementById('mSelect');
sel.innerHTML = ALL.map(s=>`<option value="${s}">${LABEL[s]}</option>`).join('');
let target = null;
svg.addEventListener('click', e => {
  const g = e.target.closest('.cell[data-plot]'); if (!g) return;
  target = g;
  svg.querySelectorAll('.cell.sel').forEach(n=>n.classList.remove('sel'));
  g.classList.add('sel');
  document.getElementById('mTitle').textContent = 'Plot ' + g.dataset.plot;
  document.getElementById('mMeta').textContent = 'Row ' + g.dataset.row
    + (g.dataset.detached === '1' ? ' · drawn detached; position on the ground unconfirmed' : '');
  sel.value = g.dataset.status;
  modal.hidden = false; sel.focus();
});
function close(){ modal.hidden = true;
  svg.querySelectorAll('.cell.sel').forEach(n=>n.classList.remove('sel')); target = null; }
document.getElementById('mCancel').onclick = close;
document.getElementById('mOk').onclick = () => {
  if (target && target.dataset.status !== sel.value){ target.dataset.status = sel.value; setDirty(); }
  close();
};
modal.addEventListener('click', e => { if (e.target === modal) close(); });
addEventListener('keydown', e => {
  if (modal.hidden) return;
  if (e.key === 'Escape') close();
  if (e.key === 'Enter') document.getElementById('mOk').click();
});

/* save to disk */
saveBtn.onclick = async () => {
  const statuses = {};
  svg.querySelectorAll('.cell[data-plot]').forEach(g => statuses[g.dataset.plot] = g.dataset.status);
  saveBtn.disabled = true; msg.className = ''; msg.textContent = 'saving…';
  try {
    const r = await fetch('api.php', {method:'PUT', headers:{'Content-Type':'application/json'},
                                      body: JSON.stringify({version, statuses})});
    const j = await r.json();
    if (r.status === 409){
      msg.className = 'bad';
      msg.textContent = `someone else saved v${j.current} — reload before saving`;
      saveBtn.disabled = false; return;
    }
    if (!r.ok) throw new Error(j.error || r.statusText);
    version = j.version; dirty = false;
    msg.className = 'good'; msg.textContent = `saved · v${version}`;
  } catch (err) {
    msg.className = 'bad'; msg.textContent = 'save failed: ' + err.message;
    saveBtn.disabled = false;
  }
};
addEventListener('beforeunload', e => { if (dirty) e.preventDefault(); });
</script>
