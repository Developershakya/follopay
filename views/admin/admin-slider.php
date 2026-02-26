<?php include 'header.php'; ?>
<!-- views/admin/admin-slider.php -->
<div class="w-full max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Manage Slider</h1>
        <p class="text-gray-500 text-sm mt-1">Add/remove banner images shown on dashboard</p>
    </div>

    <!-- ADD SLIDE CARD -->
    <div class="bg-white rounded-xl shadow-lg p-5 md:p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-plus-circle text-green-500"></i> Add New Slide
        </h2>

        <!-- Method toggle -->
        <div class="flex gap-2 mb-5 bg-gray-100 rounded-lg p-1">
            <button onclick="swTab('url')" id="stab-url"
                class="sl-tab flex-1 py-2 rounded-md text-sm font-bold bg-white shadow text-green-700 border-0 cursor-pointer transition">
                <i class="fas fa-link mr-1"></i> Image URL
            </button>
            <button onclick="swTab('upload')" id="stab-upload"
                class="sl-tab flex-1 py-2 rounded-md text-sm font-bold text-gray-500 border-0 cursor-pointer transition">
                <i class="fas fa-upload mr-1"></i> Upload Image
            </button>
        </div>

        <!-- Common fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">
                    Title <span class="text-gray-400 font-normal normal-case">(optional)</span>
                </label>
                <input id="sTitle" type="text" placeholder="e.g. Withdraw Instantly"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">
                    Redirect URL <span class="text-gray-400 font-normal normal-case">(on click)</span>
                </label>
                <input id="sRedirect" type="url" placeholder="https://..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">
                    Sort Order <span class="text-gray-400 font-normal normal-case">(lower = first)</span>
                </label>
                <input id="sSort" type="number" value="0" min="0"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-green-500">
            </div>
        </div>

        <!-- ── URL Tab ── -->
        <div id="stab-url-form">
            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">
                Image URL <span class="text-red-500">*</span>
            </label>
            <input id="sImgUrl" type="url" placeholder="https://res.cloudinary.com/..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:outline-none focus:border-green-500">
            <!-- Preview -->
            <div id="urlPreviewBox" class="hidden mb-4">
                <img id="urlPreviewImg" class="rounded-xl max-h-36 border border-gray-200">
            </div>
            <button onclick="addByUrl()" id="btnUrl"
                class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-2.5 px-6 rounded-lg flex items-center gap-2 shadow-md border-0 cursor-pointer transition">
                <i class="fas fa-plus"></i> Add Slide
            </button>
        </div>

        <!-- ── Upload Tab (Server-side, no preset needed) ── -->
        <div id="stab-upload-form" style="display:none;">
            <label class="block text-xs font-bold text-gray-600 mb-1 uppercase tracking-wide">
                Image File <span class="text-red-500">*</span>
            </label>
            <!-- Drop zone -->
            <label for="sFile"
                class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-green-50 hover:border-green-400 transition mb-3"
                id="dropZone">
                <input type="file" id="sFile" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
                    class="hidden" onchange="previewFile(this)">
                <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 mb-2" id="uploadIco"></i>
                <span class="text-sm text-gray-500" id="uploadLabel">Click or drag image here</span>
                <span class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP, GIF • Max 5MB</span>
            </label>

            <!-- Image preview -->
            <div id="filePreviewBox" class="hidden mb-3">
                <img id="filePreviewImg" class="rounded-xl max-h-36 border border-gray-200">
                <p id="fileNameLabel" class="text-xs text-gray-500 mt-1"></p>
            </div>

            <!-- Progress bar -->
            <div id="uploadProg" class="hidden mb-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-green-700" id="progTxt">Uploading...</span>
                    <span class="text-xs text-gray-500" id="progPct">0%</span>
                </div>
                <div class="h-2.5 bg-gray-200 rounded-full overflow-hidden">
                    <div id="progFill" class="h-full bg-green-500 rounded-full transition-all duration-300" style="width:0%"></div>
                </div>
            </div>

            <button onclick="addByUpload()" id="btnUpload"
                class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-2.5 px-6 rounded-lg flex items-center gap-2 shadow-md border-0 cursor-pointer transition">
                <i class="fas fa-upload"></i> Upload & Add Slide
            </button>
        </div>
    </div>

    <!-- SLIDE LIST -->
    <div class="bg-white rounded-xl shadow-lg p-5 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-images text-green-500"></i> Current Slides
            </h2>
            <button onclick="loadList()"
                class="text-sm font-bold text-green-600 bg-green-50 border border-green-200 px-3 py-1.5 rounded-lg hover:bg-green-100 transition cursor-pointer">
                <i class="fas fa-sync mr-1"></i> Refresh
            </button>
        </div>
        <div id="slideList">
            <div class="text-center py-10 text-gray-400">
                <i class="fas fa-spinner fa-spin text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadList);

/* ── Tab Switch ── */
function swTab(t) {
    document.getElementById('stab-url-form').style.display    = t === 'url'    ? 'block' : 'none';
    document.getElementById('stab-upload-form').style.display = t === 'upload' ? 'block' : 'none';
    document.getElementById('stab-url').className    = 'sl-tab flex-1 py-2 rounded-md text-sm font-bold border-0 cursor-pointer transition ' + (t==='url'    ? 'bg-white shadow text-green-700' : 'text-gray-500');
    document.getElementById('stab-upload').className = 'sl-tab flex-1 py-2 rounded-md text-sm font-bold border-0 cursor-pointer transition ' + (t==='upload' ? 'bg-white shadow text-green-700' : 'text-gray-500');
}

/* ── URL Preview ── */
document.getElementById('sImgUrl').addEventListener('input', function() {
    const v = this.value.trim();
    const box = document.getElementById('urlPreviewBox');
    const img = document.getElementById('urlPreviewImg');
    if (v && (v.startsWith('http://') || v.startsWith('https://'))) {
        img.src = v; box.classList.remove('hidden');
        img.onerror = () => box.classList.add('hidden');
    } else {
        box.classList.add('hidden');
    }
});

/* ── File Preview ── */
function previewFile(inp) {
    const f = inp.files[0];
    if (!f) return;
    if (f.size > 5 * 1024 * 1024) {
        alert('File too large! Max 5MB.'); inp.value = ''; return;
    }
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('filePreviewImg').src = e.target.result;
        document.getElementById('filePreviewBox').classList.remove('hidden');
        document.getElementById('fileNameLabel').textContent = f.name + ' (' + (f.size/1024).toFixed(1) + ' KB)';
        document.getElementById('uploadLabel').textContent = '✅ ' + f.name;
        document.getElementById('uploadIco').className = 'fas fa-check-circle text-3xl text-green-500 mb-2';
    };
    reader.readAsDataURL(f);
}

/* ── Add via URL ── */
function addByUrl() {
    const imgUrl = document.getElementById('sImgUrl').value.trim();
    if (!imgUrl) { alert('Please enter an image URL'); return; }
    const btn = document.getElementById('btnUrl');
    setBtn(btn, true, '<i class="fas fa-spinner fa-spin"></i> Adding...');
    postAjax('ajax/slider.php', {
        action:       'add_url',
        title:        val('sTitle'),
        image_url:    imgUrl,
        redirect_url: val('sRedirect'),
        sort_order:   val('sSort')
    })
    .then(d => {
        if (d.success) { toast('✅ Slide added!'); clearForm(); loadList(); }
        else alert('Error: ' + (d.message || 'Failed'));
    })
    .catch(() => alert('Network error'))
    .finally(() => setBtn(btn, false, '<i class="fas fa-plus"></i> Add Slide'));
}

/* ── Add via Server-side Upload ── */
function addByUpload() {
    const inp = document.getElementById('sFile');
    const f   = inp.files[0];
    if (!f) { alert('Please select an image file'); return; }
    if (f.size > 5 * 1024 * 1024) { alert('File too large! Max 5MB.'); return; }

    const btn = document.getElementById('btnUpload');
    setBtn(btn, true, '<i class="fas fa-spinner fa-spin"></i> Uploading...');
    showProgress(10, 'Preparing upload...');

    // Build FormData — server will upload to Cloudinary via PHP
    const fd = new FormData();
    fd.append('action',       'upload_image');
    fd.append('slider_image', f);
    fd.append('title',        val('sTitle'));
    fd.append('redirect_url', val('sRedirect'));
    fd.append('sort_order',   val('sSort'));

    // Simulate progress while uploading
    let prog = 10;
    const progTimer = setInterval(() => {
        prog = Math.min(prog + 8, 85);
        showProgress(prog, 'Uploading to Cloudinary...');
    }, 300);

    fetch('ajax/slider.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            clearInterval(progTimer);
            if (d.success) {
                showProgress(100, 'Done!');
                setTimeout(() => {
                    hideProgress();
                    toast('✅ Image uploaded & slide added!');
                    clearForm();
                    loadList();
                }, 600);
            } else {
                hideProgress();
                alert('Upload failed: ' + (d.message || 'Unknown error'));
            }
        })
        .catch(() => {
            clearInterval(progTimer);
            hideProgress();
            alert('Network error. Please try again.');
        })
        .finally(() => setBtn(btn, false, '<i class="fas fa-upload"></i> Upload & Add Slide'));
}

/* ── Load Slide List ── */
function loadList() {
    document.getElementById('slideList').innerHTML =
        '<div class="text-center py-10 text-gray-400"><i class="fas fa-spinner fa-spin text-2xl"></i></div>';

    fetch('ajax/slider.php?action=admin_get_all')
        .then(r => r.json())
        .then(d => {
            const el = document.getElementById('slideList');
            if (!d.success || !d.slides || !d.slides.length) {
                el.innerHTML = `
                    <div class="text-center py-10 text-gray-400">
                        <i class="fas fa-images text-4xl mb-3 block text-gray-200"></i>
                        <p class="font-medium">No slides yet</p>
                        <p class="text-sm mt-1">Add your first banner above</p>
                    </div>`;
                return;
            }
            el.innerHTML = d.slides.map(s => slideRow(s)).join('');
        })
        .catch(() => {
            document.getElementById('slideList').innerHTML =
                '<div class="text-center py-6 text-red-400"><i class="fas fa-exclamation-triangle mr-2"></i>Failed to load</div>';
        });
}

function slideRow(s) {
    const active = parseInt(s.is_active);
    return `
    <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 mb-3 hover:bg-gray-50 transition" id="srow-${s.id}">
        <img src="${eH(s.image_url)}" alt="${eH(s.title||'')}"
             class="w-20 h-12 rounded-lg object-cover border border-gray-200 flex-shrink-0"
             onerror="this.src='https://via.placeholder.com/80x48?text=IMG'">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <span class="font-bold text-sm text-gray-800">${eH(s.title || 'Untitled')}</span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full ${active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}">
                    ${active ? 'Active' : 'Inactive'}
                </span>
            </div>
            <div class="text-xs text-gray-400 truncate mt-0.5">
                ${s.redirect_url ? '🔗 ' + eH(s.redirect_url) : '<span class="italic">No redirect</span>'}
            </div>
            <div class="text-xs text-purple-400 mt-0.5">Order: ${s.sort_order}</div>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <button onclick="toggleSlide(${s.id})"
                class="text-xs font-bold px-3 py-1.5 rounded-lg border cursor-pointer transition ${active ? 'border-orange-200 text-orange-600 bg-orange-50 hover:bg-orange-100' : 'border-green-200 text-green-700 bg-green-50 hover:bg-green-100'}">
                <i class="fas ${active ? 'fa-eye-slash' : 'fa-eye'}"></i> ${active ? 'Hide' : 'Show'}
            </button>
            <button onclick="delSlide(${s.id}, '${eH(s.title || 'this slide')}')"
                class="text-xs font-bold px-3 py-1.5 rounded-lg border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 cursor-pointer transition">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>`;
}

/* ── Toggle / Delete ── */
function toggleSlide(id) {
    postAjax('ajax/slider.php', { action: 'toggle', id })
        .then(d => { if (d.success) loadList(); else alert(d.message); });
}

function delSlide(id, name) {
    if (!confirm(`Delete "${name}"? This cannot be undone.`)) return;
    postAjax('ajax/slider.php', { action: 'delete', id })
        .then(d => {
            if (d.success) { toast('🗑️ Slide deleted'); loadList(); }
            else alert(d.message);
        });
}

/* ── Helpers ── */
function showProgress(pct, txt) {
    document.getElementById('uploadProg').classList.remove('hidden');
    document.getElementById('progFill').style.width = pct + '%';
    document.getElementById('progTxt').textContent  = txt;
    document.getElementById('progPct').textContent  = pct + '%';
}
function hideProgress() {
    document.getElementById('uploadProg').classList.add('hidden');
    document.getElementById('progFill').style.width = '0%';
}
function setBtn(btn, disabled, html) {
    btn.disabled = disabled; btn.innerHTML = html;
}
function clearForm() {
    ['sTitle','sRedirect','sImgUrl'].forEach(id => { const el = document.getElementById(id); if(el) el.value = ''; });
    document.getElementById('sSort').value = '0';
    const fi = document.getElementById('sFile'); if(fi) fi.value = '';
    document.getElementById('filePreviewBox').classList.add('hidden');
    document.getElementById('urlPreviewBox').classList.add('hidden');
    document.getElementById('uploadLabel').textContent = 'Click or drag image here';
    document.getElementById('uploadIco').className = 'fas fa-cloud-upload-alt text-3xl text-gray-300 mb-2';
}
function val(id) { const el = document.getElementById(id); return el ? el.value.trim() : ''; }
function eH(s)   { const d = document.createElement('div'); d.textContent = String(s||''); return d.innerHTML; }
function postAjax(url, data) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data)
    }).then(r => r.json());
}
function toast(msg) {
    const t = document.createElement('div');
    t.textContent = msg;
    Object.assign(t.style, {
        position:'fixed', bottom:'90px', left:'50%', transform:'translateX(-50%)',
        background:'#1f2937', color:'#fff', padding:'10px 22px',
        borderRadius:'50px', fontSize:'13px', fontWeight:'700',
        zIndex:'9999', boxShadow:'0 4px 20px rgba(0,0,0,.25)',
        transition:'opacity .3s'
    });
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 2500);
}
</script>