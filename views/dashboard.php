<?php
// views/dashboard.php
$whatsappLink = 'https://chat.whatsapp.com/FLxdBKN9kMr9Zq1iAgo99y?mode=gi_t';
?>

<!-- AppIconLoader — ek baar include karo, poore project mein reuse hoga -->
<script src="asserts/js/app-icon-loader.js"></script>

<style>
/* ══════════════════════════════════════════
   DASHBOARD – Purple Theme
══════════════════════════════════════════ */
.db-wrap { max-width: 600px; margin: 0 auto; padding: 0 0 80px; }

/* ── Marquee ── */
.marquee-bar {
    background: #7c3aed; border-radius: 14px; padding: 11px 14px;
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 14px; overflow: hidden;
}
.marquee-icon {
    width: 34px; height: 34px; border-radius: 9px;
    background: rgba(255,255,255,.18);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.marquee-icon i { color: #fff; font-size: 14px; }
.marquee-text-wrap { overflow: hidden; flex: 1; }
.marquee-text {
    color: #fff; font-size: 13px; font-weight: 600;
    white-space: nowrap; display: inline-block;
    animation: marqueeScroll 22s linear infinite;
}
@keyframes marqueeScroll {
    0%   { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}
.marquee-complete {
    color: #fff; font-size: 12px; font-weight: 700; white-space: nowrap; flex-shrink: 0;
    background: rgba(255,255,255,.18); padding: 3px 10px; border-radius: 50px;
}

/* ── Balance Card ── */
.balance-card {
    background: #7c3aed; border-radius: 20px; padding: 20px;
    margin-bottom: 18px; position: relative; overflow: hidden;
}
.balance-card::before {
    content: ''; position: absolute; top: -40px; right: -40px;
    width: 160px; height: 160px; border-radius: 50%; background: rgba(255,255,255,.07);
}
.balance-card::after {
    content: ''; position: absolute; bottom: -30px; left: 20px;
    width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,.05);
}
.bal-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.bal-left { display: flex; align-items: center; gap: 12px; }
.bal-wallet-icon {
    width: 42px; height: 42px; border-radius: 12px; background: rgba(255,255,255,.18);
    display: flex; align-items: center; justify-content: center;
}
.bal-wallet-icon i { color: #fff; font-size: 18px; }
.bal-label { color: rgba(255,255,255,.85); font-size: 13px; font-weight: 500; }
.bal-amount { color: #fff; font-size: 28px; font-weight: 800; letter-spacing: -0.5px; }
.bal-wallet-btn {
    background: #fff; color: #7c3aed; border: none; border-radius: 50px;
    padding: 10px 18px; font-size: 13px; font-weight: 800;
    cursor: pointer; display: flex; align-items: center; gap: 7px;
    transition: transform .15s; text-decoration: none;
}
.bal-wallet-btn:active { transform: scale(.96); }
.bal-actions { display: flex; gap: 10px; }
.bal-action-btn {
    flex: 1; background: rgba(255,255,255,.18); border: none; border-radius: 50px;
    padding: 10px; color: #fff; font-size: 13px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px;
    transition: background .2s; text-decoration: none;
}
.bal-action-btn:hover { background: rgba(255,255,255,.28); }

/* ── Section Header ── */
.sec-head { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
.sec-head-icon {
    width: 36px; height: 36px; border-radius: 10px; background: #ede9fe;
    display: flex; align-items: center; justify-content: center;
}
.sec-head-icon i { color: #7c3aed; font-size: 16px; }
.sec-head-title { font-size: 18px; font-weight: 800; color: #111; }

/* ── Slider ── */
.slider-outer {
    border-radius: 16px; overflow: hidden;
    background: #ddd6fe; height: 185px; margin-bottom: 22px; position: relative;
}
.slider-track { display: flex; height: 100%; will-change: transform; }
.slider-slide { flex-shrink: 0; width: 100%; height: 100%; }
.slider-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }
.slider-btn {
    position: absolute; top: 50%; transform: translateY(-50%);
    background: rgba(0,0,0,.35); border: none; color: #fff;
    border-radius: 50%; width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; z-index: 5; transition: background .2s;
}
.slider-btn:hover { background: rgba(0,0,0,.6); }
.slider-btn.prev { left: 10px; }
.slider-btn.next { right: 10px; }
.slider-dots {
    position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%);
    display: flex; gap: 5px; z-index: 5;
}
.s-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: rgba(255,255,255,.5); border: none; cursor: pointer; padding: 0;
    transition: background .2s, transform .2s;
}
.s-dot.active { background: #fff; transform: scale(1.3); }

/* ── Filter Tabs ── */
.filter-tabs { display: flex; gap: 8px; margin-bottom: 12px; overflow-x: auto; padding-bottom: 4px; }
.filter-tabs::-webkit-scrollbar { display: none; }
.f-tab {
    padding: 7px 18px; border-radius: 50px; font-size: 13px; font-weight: 700;
    border: 2px solid #e5e7eb; background: #fff; color: #6b7280;
    cursor: pointer; white-space: nowrap; transition: all .2s; flex-shrink: 0;
}
.f-tab.active { background: #7c3aed; border-color: #7c3aed; color: #fff; }

/* ── Period Filter ── */
.period-filter { display: flex; gap: 6px; margin-bottom: 10px; }
.p-btn {
    padding: 5px 14px; border-radius: 50px; font-size: 11px; font-weight: 700;
    border: 1.5px solid #e5e7eb; background: #fff; color: #9ca3af; cursor: pointer; transition: all .2s;
}
.p-btn.active { background: #7c3aed; border-color: #7c3aed; color: #fff; }

/* ── Sticky Tabs ── */
.tasks-sticky-header {
    position: sticky;
    top: 56px;
    z-index: 10;
    background: #f5f3ff;
    padding: 10px 0 0;
}
@media (min-width: 768px) {
    .tasks-sticky-header { top: 64px; }
}
.tasks-header-inner {
    background: #fff; border-radius: 20px 20px 0 0;
    padding: 16px 18px 12px;
    box-shadow: 0 2px 8px rgba(124,58,237,.08);
}
.tasks-body {
    background: #fff; border-radius: 0 0 20px 20px;
    padding: 12px 18px 18px;
    box-shadow: 0 4px 16px rgba(0,0,0,.05);
    margin-bottom: 18px;
}

/* ── Task Card (Available) ── */
.task-card {
    background: #fff; border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06); border: 1px solid #f3f4f6;
    display: flex; align-items: center; padding: 0; margin-bottom: 12px;
    overflow: hidden; transition: box-shadow .2s;
}
.task-card:hover { box-shadow: 0 4px 20px rgba(124,58,237,.1); }
.task-card-accent { width: 5px; background: #7c3aed; align-self: stretch; flex-shrink: 0; }
.task-card-logo {
    width: 60px; height: 60px; border-radius: 14px;
    margin: 14px 12px; flex-shrink: 0;
    background: #f3f4f6; overflow: hidden; border: 1px solid #e5e7eb;
    display: flex; align-items: center; justify-content: center;
    position: relative;
}
.task-card-logo img { width: 100%; height: 100%; object-fit: cover; border-radius: 12px; transition: opacity .3s; }

.task-card-body { flex: 1; padding: 14px 12px 14px 0; min-width: 0; }
.task-card-name { font-size: 15px; font-weight: 800; color: #111; }
.task-card-sub  { font-size: 12px; color: #9ca3af; margin-top: 2px; display: flex; align-items: center; gap: 4px; }
.task-card-sub .star { color: #f59e0b; }
.task-card-bottom { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
.task-price-pill { background: #10b981; color: #fff; border-radius: 50px; padding: 5px 14px; font-size: 14px; font-weight: 800; }
.claim-btn {
    background: #ede9fe; color: #7c3aed; border: none; border-radius: 50px;
    padding: 7px 16px; font-size: 13px; font-weight: 800; cursor: pointer;
    transition: background .2s; display: flex; align-items: center; gap: 5px;
}
.claim-btn:hover { background: #ddd6fe; }
.claim-btn:disabled { opacity: .5; cursor: not-allowed; }

/* ── My Task Card ── */
.my-task-card {
    background: #fff; border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06); border: 1px solid #f3f4f6;
    display: flex; align-items: center; overflow: hidden; margin-bottom: 12px;
    transition: box-shadow .2s;
}
.my-task-card:hover { box-shadow: 0 4px 20px rgba(124,58,237,.1); }
.my-task-accent-pending  { width: 5px; background: #f59e0b; align-self: stretch; flex-shrink: 0; }
.my-task-accent-approved { width: 5px; background: #10b981; align-self: stretch; flex-shrink: 0; }
.my-task-accent-rejected { width: 5px; background: #ef4444; align-self: stretch; flex-shrink: 0; }
.my-task-logo {
    width: 52px; height: 52px; border-radius: 12px;
    margin: 14px 12px; flex-shrink: 0;
    background: #f3f4f6; overflow: hidden; border: 1px solid #e5e7eb;
    display: flex; align-items: center; justify-content: center;
    position: relative;
}
.my-task-logo img { width: 100%; height: 100%; object-fit: cover; border-radius: 10px; transition: opacity .3s; }
.my-task-body { flex: 1; padding: 14px 12px 14px 0; min-width: 0; }
.my-task-name { font-size: 14px; font-weight: 800; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.my-task-date { font-size: 11px; color: #9ca3af; margin-top: 2px; }
.my-task-right { padding-right: 14px; text-align: right; flex-shrink: 0; }
.my-task-price { font-size: 16px; font-weight: 900; color: #7c3aed; }
.status-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 50px; margin-top: 4px; display: inline-block; }
.badge-pending  { background: #fef3c7; color: #92400e; }
.badge-approved { background: #d1fae5; color: #065f46; }
.badge-rejected { background: #fee2e2; color: #991b1b; }

/* ── Reject Box ── */
.reject-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; overflow: hidden; margin-top: 8px; }
.reject-toggle {
    width: 100%; background: none; border: none; padding: 8px 12px;
    display: flex; align-items: center; gap: 7px; cursor: pointer; text-align: left;
}
.reject-body {
    padding: 0 12px; max-height: 0; overflow: hidden;
    transition: max-height .3s ease, padding .3s;
    font-size: 12px; color: #b91c1c; line-height: 1.5;
}
.reject-body.open { max-height: 120px; padding: 8px 12px; }

/* ── Empty ── */
.empty-state { text-align: center; padding: 36px 16px; color: #9ca3af; }
.empty-state i { font-size: 36px; color: #ddd6fe; display: block; margin-bottom: 10px; }
</style>

<div class="db-wrap">

    <!-- ① MARQUEE -->
    <div class="marquee-bar">
        <div class="marquee-icon"><i class="fas fa-bullhorn"></i></div>
        <div class="marquee-text-wrap">
            <span class="marquee-text">
                Complete your tasks and get your rewards instantly! New tasks added daily. Withdraw directly to UPI within 24 hours. Refer friends and earn more!
            </span>
        </div>
        <span class="marquee-complete">Complete</span>
    </div>

    <!-- ② BALANCE CARD -->
    <div class="balance-card">
        <div class="bal-top">
            <div class="bal-left">
                <div class="bal-wallet-icon"><i class="fas fa-wallet"></i></div>
                <div>
                    <div class="bal-label">Available Balance</div>
                    <div class="bal-amount" id="dashboardBalance">₹0.00</div>
                </div>
            </div>
            <a href="?page=wallet" class="bal-wallet-btn">
                <i class="fas fa-wallet"></i> Wallet
            </a>
        </div>
        <div class="bal-actions">
            <button class="bal-action-btn" onclick="comingSoon()">
                <i class="fas fa-user-plus"></i> Invite Friends
            </button>
            <a href="<?php echo $whatsappLink; ?>" target="_blank" class="bal-action-btn">
                <i class="fab fa-whatsapp"></i> Get Help
            </a>
        </div>
    </div>

    <!-- ③ SLIDER -->
    <div class="sec-head">
        <div class="sec-head-icon"><i class="fas fa-chart-line"></i></div>
        <span class="sec-head-title">Trending Offers</span>
    </div>
    <div class="slider-outer" id="sliderOuter">
        <div class="slider-track" id="sliderTrack">
            <div class="slider-slide" style="background:linear-gradient(135deg,#7c3aed,#a78bfa);display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-spinner fa-spin" style="color:#fff;font-size:28px;"></i>
            </div>
        </div>
        <button class="slider-btn prev" onclick="sliderMove(-1)" id="sliderPrev">
            <i class="fas fa-chevron-left" style="font-size:11px;"></i>
        </button>
        <button class="slider-btn next" onclick="sliderMove(1)" id="sliderNext">
            <i class="fas fa-chevron-right" style="font-size:11px;"></i>
        </button>
        <div class="slider-dots" id="sliderDots"></div>
    </div>

    <!-- ④ TASKS — Sticky header + body -->
    <div class="tasks-sticky-header">
        <div class="tasks-header-inner">
            <!-- ✅ Sirf "Rejected" tab add kiya — baaki sab same -->
            <div class="filter-tabs">
                <button class="f-tab active" onclick="setMainTab('all')"      id="mtab-all">All</button>
                <button class="f-tab"        onclick="setMainTab('pending')"  id="mtab-pending">Pending</button>
                <button class="f-tab"        onclick="setMainTab('complete')" id="mtab-complete">Complete</button>
                <button class="f-tab"        onclick="setMainTab('rejected')" id="mtab-rejected">Rejected</button>
            </div>
            <div class="period-filter" id="periodFilter" style="display:none;">
                <button class="p-btn active" onclick="setPeriod('today')" id="p-today">Today</button>
                <button class="p-btn"        onclick="setPeriod('month')" id="p-month">This Month</button>
            </div>
        </div>
    </div>
    <div class="tasks-body">
        <div id="tasksContainer">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin" style="color:#8b5cf6;font-size:28px;"></i>
                <p>Loading...</p>
            </div>
        </div>
    </div>

</div>

<!-- Coming Soon Modal -->
<div id="comingSoonModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:20px;padding:28px 24px;max-width:300px;width:90%;text-align:center;">
        <div style="width:60px;height:60px;border-radius:50%;background:#ede9fe;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <i class="fas fa-clock" style="color:#7c3aed;font-size:24px;"></i>
        </div>
        <h3 style="font-size:18px;font-weight:800;color:#111;margin-bottom:8px;">Coming Soon!</h3>
        <p style="color:#6b7280;font-size:14px;margin-bottom:20px;">Refer &amp; Earn feature is coming very soon. Stay tuned!</p>
        <button onclick="document.getElementById('comingSoonModal').style.display='none'"
            style="background:#7c3aed;color:#fff;border:none;border-radius:50px;padding:10px 28px;font-size:14px;font-weight:700;cursor:pointer;width:100%;">
            OK, Got it!
        </button>
    </div>
</div>

<script>
/* ══════════════════════════════════════════
   INIT
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    loadBalance();
    loadSlider();
    loadAllTasks();
});
setInterval(loadBalance, 60000);

function comingSoon() { document.getElementById('comingSoonModal').style.display = 'flex'; }

/* ══ BALANCE ══ */
function loadBalance() {
    fetch('ajax/wallet.php?action=get_balance')
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const bal = parseFloat(d.wallet.wallet_balance) || 0;
                document.getElementById('dashboardBalance').textContent = '₹' + bal.toFixed(2);
            }
        }).catch(() => {});
}

/* ══════════════════════════════════════════
   SLIDER — Infinite Clone Loop
══════════════════════════════════════════ */
let slides = [], sIdx = 0, sPos = 1, sTimer = null, isTransitioning = false;

function loadSlider() {
    fetch('ajax/slider.php?action=get_slides')
        .then(r => r.json())
        .then(d => {
            if (!d.success || !d.slides?.length) { showDefaultSlide(); return; }
            slides = d.slides;
            buildSlider();
        }).catch(showDefaultSlide);
}
function showDefaultSlide() {
    document.getElementById('sliderTrack').innerHTML = `
        <div class="slider-slide" style="background:linear-gradient(135deg,#7c3aed,#a78bfa);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;">
            <i class="fas fa-rupee-sign" style="color:rgba(255,255,255,.6);font-size:40px;"></i>
            <span style="color:#fff;font-weight:800;font-size:16px;">Earn Money Easily!</span>
        </div>`;
    ['sliderPrev','sliderNext'].forEach(id => document.getElementById(id).style.display = 'none');
}
function buildSlider() {
    const track = document.getElementById('sliderTrack');
    const dots  = document.getElementById('sliderDots');
    track.innerHTML = dots.innerHTML = '';
    if (slides.length === 1) {
        ['sliderPrev','sliderNext'].forEach(id => document.getElementById(id).style.display = 'none');
        const el = document.createElement('div');
        el.className = 'slider-slide';
        el.innerHTML = `<img src="${esc(slides[0].image_url)}" alt="">`;
        track.appendChild(el);
        setTrackPos(0, false); return;
    }
    [slides[slides.length-1], ...slides, slides[0]].forEach(s => {
        const el = document.createElement('div');
        el.className = 'slider-slide';
        if (s.redirect_url) { el.style.cursor = 'pointer'; el.onclick = () => window.open(s.redirect_url, '_blank'); }
        el.innerHTML = `<img src="${esc(s.image_url)}" alt="${esc(s.title||'')}" loading="lazy">`;
        track.appendChild(el);
    });
    slides.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.className = 's-dot' + (i === 0 ? ' active' : '');
        dot.onclick = () => { if (!isTransitioning) { goToReal(i); resetAuto(); } };
        dots.appendChild(dot);
    });
    sPos = 1; sIdx = 0;
    setTrackPos(sPos, false);
    startAuto();
}
function setTrackPos(pos, animate) {
    const t = document.getElementById('sliderTrack');
    t.style.transition = animate ? 'transform .48s cubic-bezier(.4,0,.2,1)' : 'none';
    t.style.transform  = `translateX(-${pos * 100}%)`;
}
function updateDots(i) {
    document.querySelectorAll('.s-dot').forEach((d, j) => d.classList.toggle('active', j === i));
}
function goToReal(i) { sIdx = i; sPos = i + 1; setTrackPos(sPos, true); updateDots(sIdx); }
function sliderMove(dir) {
    if (isTransitioning || slides.length <= 1) return;
    isTransitioning = true;
    sPos += dir;
    sIdx = sPos <= 0 ? slides.length - 1 : sPos >= slides.length + 1 ? 0 : sPos - 1;
    setTrackPos(sPos, true);
    updateDots(sIdx);
    const t = document.getElementById('sliderTrack');
    t.addEventListener('transitionend', function onEnd() {
        t.removeEventListener('transitionend', onEnd);
        isTransitioning = false;
        if (sPos <= 0)                  { sPos = slides.length; sIdx = slides.length - 1; setTrackPos(sPos, false); updateDots(sIdx); }
        else if (sPos >= slides.length + 1) { sPos = 1; sIdx = 0; setTrackPos(sPos, false); updateDots(sIdx); }
    }, { once: true });
}
function startAuto() { if (slides.length > 1) sTimer = setInterval(() => sliderMove(1), 3500); }
function resetAuto() { clearInterval(sTimer); startAuto(); }

/* ══════════════════════════════════════════
   TABS
   mainTab: 'all' | 'pending' | 'complete' | 'rejected'
══════════════════════════════════════════ */
let mainTab = 'all', period = 'today', allAvailable = [], allMyTasks = [];

function setMainTab(tab) {
    mainTab = tab;
    ['all','pending','complete','rejected'].forEach(t => {
        document.getElementById('mtab-' + t).classList.toggle('active', t === tab);
    });
    // Period filter sirf 'all' tab pe hide hoga
    document.getElementById('periodFilter').style.display = tab === 'all' ? 'none' : 'flex';

    if (tab === 'all') renderAvailable();
    else               loadMyTasks(tab);
}

function setPeriod(p) {
    period = p;
    document.getElementById('p-today').className = 'p-btn' + (p === 'today' ? ' active' : '');
    document.getElementById('p-month').className = 'p-btn' + (p === 'month' ? ' active' : '');
    loadMyTasks(mainTab);
}

/* ══════════════════════════════════════════
   AVAILABLE TASKS  (All tab)
══════════════════════════════════════════ */
function loadAllTasks() {
    showLoading();
    fetch('ajax/posts.php?action=get_available')
        .then(r => r.json())
        .then(d => {
            allAvailable = (d.success && d.posts) ? d.posts : [];
            renderAvailable();
        }).catch(showError);
}

function renderAvailable() {
    const c = document.getElementById('tasksContainer');
    if (!allAvailable.length) {
        c.innerHTML = `<div class="empty-state"><i class="fas fa-inbox"></i><p>No available tasks right now</p></div>`;
        return;
    }
    c.innerHTML = allAvailable.map(availCard).join('');

    const playStoreBatch = [];
    allAvailable.forEach(post => {
        const img = document.getElementById('aicon-' + post.id);
        if (!img) return;
        const link  = post.app_link || '';
        const lower = link.toLowerCase();
        if (lower.includes('play.google.com')) {
            const pkg = extractPkg(link);
            if (pkg) playStoreBatch.push({ imgEl: img, pkg });
            else     AppIconLoader.fromUrl(img, link);
        } else {
            AppIconLoader.fromUrl(img, link);
        }
    });
    if (playStoreBatch.length) AppIconLoader.loadBatch(playStoreBatch);
}

function availCard(post) {
    const name  = esc(post.app_name || 'Unknown App');
    const price = parseFloat(post.price || 0).toFixed(2);
    const id    = post.id;
    const type  = getTaskType(post.app_link || '');
    return `
    <div class="task-card">
        <div class="task-card-accent"></div>
        <div class="task-card-logo">
            <img id="aicon-${id}" src="" alt="${name}">
        </div>
        <div class="task-card-body">
            <div class="task-card-name">${name}</div>
            <div class="task-card-sub">${type} <span>•</span> <span class="star">★</span> 5.0</div>
            <div class="task-card-bottom">
                <span class="task-price-pill">₹${price}</span>
                <button class="claim-btn" onclick="startEarning(${id})" id="cb-${id}">
                    Claim <i class="fas fa-arrow-right" style="font-size:11px;"></i>
                </button>
            </div>
        </div>
    </div>`;
}

/* ══════════════════════════════════════════
   MY TASKS  (Pending / Complete / Rejected)
══════════════════════════════════════════ */
function loadMyTasks(tabType) {
    showLoading();
    // Tab → DB status mapping
    const statusMap = { pending: 'submitted', complete: 'approved', rejected: 'rejected' };
    const statusParam = statusMap[tabType] || 'submitted';

    fetch(`ajax/posts.php?action=get_user_tasks&period=${period}&status=${statusParam}`)
        .then(r => r.json())
        .then(d => {
            allMyTasks = (d.success && d.tasks) ? d.tasks : [];
            renderMyTasks(tabType);
        }).catch(showError);
}

function renderMyTasks(tabType) {
    const c = document.getElementById('tasksContainer');
    const periodLabel = period === 'today' ? 'Today' : 'This Month';
    const labelMap = { pending: 'Pending', complete: 'Completed', rejected: 'Rejected' };

    if (!allMyTasks.length) {
        c.innerHTML = `<div class="empty-state"><i class="fas fa-inbox"></i><p>No ${labelMap[tabType]||tabType} tasks (${periodLabel})</p></div>`;
        return;
    }
    c.innerHTML = allMyTasks.map(myTaskCard).join('');

    const playStoreBatch = [];
    allMyTasks.forEach(task => {
        const img = document.getElementById('micon-' + task.id);
        if (!img) return;
        const link  = task.app_link || '';
        const lower = link.toLowerCase();
        if (lower.includes('play.google.com')) {
            const pkg = extractPkg(link);
            if (pkg) playStoreBatch.push({ imgEl: img, pkg });
            else     AppIconLoader.fromUrl(img, link);
        } else {
            AppIconLoader.fromUrl(img, link);
        }
    });
    if (playStoreBatch.length) AppIconLoader.loadBatch(playStoreBatch);
}

function myTaskCard(task) {
    const name         = esc(task.app_name || 'Unknown App');
    const price        = parseFloat(task.price || 0).toFixed(2);
    const status       = task.status || 'submitted';
    const date         = (task.submitted_time || '').split(' ')[0];
    const id           = task.id;
    const rejectReason = task.reject_reason || '';

    const accentClass = status === 'approved' ? 'my-task-accent-approved'
                      : status === 'rejected'  ? 'my-task-accent-rejected'
                      : 'my-task-accent-pending';
    const badgeClass  = status === 'approved' ? 'badge-approved'
                      : status === 'rejected'  ? 'badge-rejected'
                      : 'badge-pending';
    const badgeText   = status === 'approved' ? 'Approved'
                      : status === 'rejected'  ? 'Rejected' : 'Pending';

    let rejectHtml = '';
    if (status === 'rejected' && rejectReason) {
        rejectHtml = `
        <div class="reject-box">
            <button class="reject-toggle" onclick="toggleReject('rj-${id}','ri-${id}')">
                <span>❌</span>
                <span style="font-size:11px;font-weight:700;color:#991b1b;">Reason</span>
                <i class="fas fa-chevron-down" id="ri-${id}" style="font-size:10px;color:#dc2626;margin-left:auto;transition:transform .2s;"></i>
            </button>
            <div class="reject-body" id="rj-${id}">${esc(rejectReason)}</div>
        </div>`;
    }

    return `
    <div class="my-task-card">
        <div class="${accentClass}"></div>
        <div class="my-task-logo">
            <img id="micon-${id}" src="" alt="${name}">
        </div>
        <div class="my-task-body">
            <div class="my-task-name">${name}</div>
            <div class="my-task-date"><i class="fas fa-calendar-alt" style="margin-right:3px;"></i>${date}</div>
            ${rejectHtml}
        </div>
        <div class="my-task-right">
            <div class="my-task-price">₹${price}</div>
            <span class="status-badge ${badgeClass}">${badgeText}</span>
        </div>
    </div>`;
}

/* ══ START EARNING ══ */
function startEarning(postId) {
    const btn = document.getElementById('cb-' + postId);
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }
    fetch('ajax/posts.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=assign_comment&post_id=${postId}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) window.location.href = '?page=earn';
        else {
            alert(d.message || 'Failed. Please try again.');
            if (btn) { btn.disabled = false; btn.innerHTML = 'Claim <i class="fas fa-arrow-right" style="font-size:11px;"></i>'; }
        }
    }).catch(() => {
        alert('Error. Please try again.');
        if (btn) { btn.disabled = false; btn.innerHTML = 'Claim <i class="fas fa-arrow-right" style="font-size:11px;"></i>'; }
    });
}

/* ══ REJECT TOGGLE ══ */
function toggleReject(bodyId, iconId) {
    const body = document.getElementById(bodyId);
    const icon = document.getElementById(iconId);
    body.classList.toggle('open');
    if (icon) icon.style.transform = body.classList.contains('open') ? 'rotate(180deg)' : '';
}

/* ══ HELPERS ══ */
function showLoading() {
    document.getElementById('tasksContainer').innerHTML =
        `<div class="empty-state"><i class="fas fa-spinner fa-spin" style="color:#8b5cf6;font-size:28px;"></i><p>Loading...</p></div>`;
}
function showError() {
    document.getElementById('tasksContainer').innerHTML =
        `<div class="empty-state"><i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i><p>Failed to load. Please refresh.</p></div>`;
}
function getTaskType(appLink) {
    if (!appLink) return 'Task';
    const l = appLink.toLowerCase();
    if (l.includes('youtube.com') || l.includes('youtu.be')) return 'YouTube Subscribe';
    if (l.includes('maps'))            return 'Google Review';
    if (l.includes('play.google.com')) return 'Play Store Review';
    if (l.includes('instagram.com'))   return 'Instagram Follow';
    if (l.includes('facebook.com'))    return 'Facebook Like';
    return 'App Task';
}
function extractPkg(url) {
    try { const m = (url || '').match(/[?&]id=([a-zA-Z0-9._]+)/); return m ? m[1] : null; }
    catch(e) { return null; }
}
function esc(str) {
    const d = document.createElement('div'); d.textContent = String(str || ''); return d.innerHTML;
}
</script>