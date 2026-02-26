<?php
// views/refer.php
// Static refer page — same design as image

// Simulate user data (replace with real DB calls)
$referCode      = 'i8e1hj2';
$friendsInvited = 0;
$totalEarned    = 0;
$referLink      = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '?ref=' . $referCode;
?>

<style>
/* ══════════════════════════════════════════
   REFER PAGE
══════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');

.ref-wrap {
    max-width: 480px;
    margin: 0 auto;
    padding-bottom: 90px;
    font-family: 'Nunito', sans-serif;
}

/* ── Hero Banner ── */
.ref-hero {
    background: linear-gradient(160deg, #8b5cf6 0%, #7c3aed 60%, #6d28d9 100%);
    border-radius: 0 0 32px 32px;
    padding: 28px 24px 32px;
    text-align: center;
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
}
.ref-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
}
.ref-hero::after {
    content: '';
    position: absolute;
    bottom: -40px; left: -40px;
    width: 150px; height: 150px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
}

/* App icon */
.ref-app-icon {
    width: 80px; height: 80px;
    border-radius: 22px;
    background: rgba(255,255,255,.18);
    margin: 0 auto 18px;
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(8px);
    border: 2px solid rgba(255,255,255,.25);
    position: relative; z-index: 1;
}
.ref-app-icon i { color: #fff; font-size: 34px; }

.ref-hero-title {
    font-size: 30px;
    font-weight: 900;
    color: #fff;
    line-height: 1.15;
    margin-bottom: 8px;
    position: relative; z-index: 1;
    text-shadow: 0 2px 12px rgba(0,0,0,.15);
}
.ref-hero-sub {
    font-size: 14px;
    color: rgba(255,255,255,.82);
    font-weight: 500;
    line-height: 1.5;
    position: relative; z-index: 1;
    max-width: 260px;
    margin: 0 auto 24px;
}

/* Code box */
.ref-code-box {
    background: rgba(255,255,255,.14);
    border: 1.5px solid rgba(255,255,255,.25);
    border-radius: 16px;
    padding: 14px 24px 16px;
    display: inline-block;
    backdrop-filter: blur(6px);
    position: relative; z-index: 1;
    margin-bottom: 22px;
}
.ref-code-label {
    font-size: 10px;
    color: rgba(255,255,255,.65);
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.ref-code-value {
    font-size: 28px;
    font-weight: 900;
    color: #fff;
    letter-spacing: 3px;
    font-family: 'Courier New', monospace;
}

/* Action buttons */
.ref-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    position: relative; z-index: 1;
}
.ref-btn-copy {
    flex: 1;
    max-width: 140px;
    background: rgba(255,255,255,.18);
    border: 1.5px solid rgba(255,255,255,.3);
    border-radius: 50px;
    padding: 12px 20px;
    color: #fff;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all .2s;
    font-family: 'Nunito', sans-serif;
    backdrop-filter: blur(4px);
}
.ref-btn-copy:hover { background: rgba(255,255,255,.28); }
.ref-btn-copy:active { transform: scale(.96); }

.ref-btn-share {
    flex: 1;
    max-width: 160px;
    background: #fff;
    border: none;
    border-radius: 50px;
    padding: 12px 20px;
    color: #7c3aed;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all .2s;
    font-family: 'Nunito', sans-serif;
    box-shadow: 0 4px 16px rgba(0,0,0,.15);
}
.ref-btn-share:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,.2); }
.ref-btn-share:active { transform: scale(.96); }

/* ── Stats Row ── */
.ref-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 14px;
    padding: 0 4px;
}
.ref-stat-card {
    background: #fff;
    border-radius: 18px;
    padding: 18px 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    border: 1px solid #f3f4f6;
}
.ref-stat-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 10px;
}
.ref-stat-icon.purple { background: #ede9fe; }
.ref-stat-icon.green  { background: #d1fae5; }
.ref-stat-icon.purple i { color: #7c3aed; font-size: 16px; }
.ref-stat-icon.green  i { color: #10b981; font-size: 16px; }

.ref-stat-num {
    font-size: 26px;
    font-weight: 900;
    color: #111;
    line-height: 1;
    margin-bottom: 4px;
}
.ref-stat-num.green { color: #10b981; }
.ref-stat-label {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 600;
}

/* ── Sub Tabs (Rankings / My Referrals) ── */
.ref-sub-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
    padding: 0 4px;
}
.ref-sub-tab {
    flex: 1;
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 14px;
    padding: 12px 8px;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    font-size: 13px;
    font-weight: 800;
    color: #374151;
    cursor: pointer;
    transition: all .2s;
    font-family: 'Nunito', sans-serif;
    box-shadow: 0 1px 6px rgba(0,0,0,.04);
}
.ref-sub-tab:hover { border-color: #7c3aed; color: #7c3aed; }
.ref-sub-tab.active {
    background: #ede9fe;
    border-color: #7c3aed;
    color: #7c3aed;
}
.ref-sub-tab i { font-size: 16px; }

/* ── Empty State Card ── */
.ref-empty-card {
    background: #fff;
    border-radius: 20px;
    padding: 40px 24px;
    text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
    border: 1px solid #f3f4f6;
    margin-bottom: 16px;
    margin: 0 4px 16px;
}
.ref-empty-icon {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: #f3f4f6;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px;
}
.ref-empty-icon i { font-size: 28px; color: #d1d5db; }
.ref-empty-title {
    font-size: 15px;
    font-weight: 800;
    color: #374151;
    margin-bottom: 4px;
}
.ref-empty-sub {
    font-size: 13px;
    color: #9ca3af;
    font-weight: 500;
}

/* ── How It Works ── */
.ref-how {
    background: #fff;
    border-radius: 20px;
    padding: 20px 20px 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.05);
    border: 1px solid #f3f4f6;
    margin: 0 4px 16px;
}
.ref-how-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 15px; font-weight: 900; color: #111;
    margin-bottom: 18px;
}
.ref-how-title i { color: #7c3aed; font-size: 18px; }

.ref-step {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 16px;
}
.ref-step:last-child { margin-bottom: 0; }
.ref-step-num {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: #7c3aed;
    color: #fff;
    font-size: 13px;
    font-weight: 900;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}
.ref-step-body {}
.ref-step-title { font-size: 14px; font-weight: 800; color: #111; }
.ref-step-sub   { font-size: 12px; color: #9ca3af; font-weight: 500; margin-top: 2px; }

/* ── Toast ── */
.ref-toast {
    position: fixed;
    bottom: 90px; left: 50%; transform: translateX(-50%) translateY(20px);
    background: #111; color: #fff;
    padding: 10px 22px; border-radius: 50px;
    font-size: 13px; font-weight: 700;
    opacity: 0; pointer-events: none;
    transition: all .3s;
    z-index: 999; white-space: nowrap;
    font-family: 'Nunito', sans-serif;
}
.ref-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}
</style>

<div class="ref-wrap">

    <!-- ① HERO -->
    <div class="ref-hero">
        <div class="ref-app-icon">
            <i class="fas fa-user-friends"></i>
        </div>

        <div class="ref-hero-title">Earn 50% Per Friend</div>
        <div class="ref-hero-sub">Share your code and earn when friends complete offers</div>

        <div class="ref-code-box">
            <div class="ref-code-label">Your Code</div>
            <div class="ref-code-value" id="refCodeDisplay"><?php echo htmlspecialchars($referCode); ?></div>
        </div>

        <div class="ref-actions">
            <button class="ref-btn-copy" onclick="copyCode()">
                <i class="fas fa-copy"></i> Copy
            </button>
            <button class="ref-btn-share" onclick="shareLink()">
                <i class="fas fa-share-alt"></i> Share Link
            </button>
        </div>
    </div>

    <!-- ② STATS -->
    <div class="ref-stats">
        <div class="ref-stat-card">
            <div class="ref-stat-icon purple">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="ref-stat-num"><?php echo $friendsInvited; ?></div>
            <div class="ref-stat-label">Friends Invited</div>
        </div>
        <div class="ref-stat-card">
            <div class="ref-stat-icon green">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="ref-stat-num green">₹<?php echo number_format($totalEarned, 0); ?></div>
            <div class="ref-stat-label">Total Earned</div>
        </div>
    </div>

    <!-- ③ SUB TABS -->
    <div class="ref-sub-tabs">
        <button class="ref-sub-tab active" onclick="setRefTab('rankings', this)">
            <i class="fas fa-chart-bar"></i> Rankings
        </button>
        <button class="ref-sub-tab" onclick="setRefTab('referrals', this)">
            <i class="fas fa-user-friends"></i> My Referrals
        </button>
    </div>

    <!-- ④ CONTENT AREA -->
    <div id="refTabContent">
        <!-- Rankings tab (default) -->
        <div id="ref-tab-rankings">
            <div class="ref-empty-card">
                <div class="ref-empty-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="ref-empty-title">No friends invited yet</div>
                <div class="ref-empty-sub">Share your code to start earning!</div>
            </div>
        </div>

        <!-- My Referrals tab -->
        <div id="ref-tab-referrals" style="display:none;">
            <div class="ref-empty-card">
                <div class="ref-empty-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="ref-empty-title">No referrals yet</div>
                <div class="ref-empty-sub">Invite friends to see them here!</div>
            </div>
        </div>
    </div>

    <!-- ⑤ HOW IT WORKS -->
    <div class="ref-how">
        <div class="ref-how-title">
            <i class="fas fa-lightbulb"></i> How It Works
        </div>
        <div class="ref-step">
            <div class="ref-step-num">1</div>
            <div class="ref-step-body">
                <div class="ref-step-title">Share Your Code</div>
                <div class="ref-step-sub">Send your referral link to friends</div>
            </div>
        </div>
        <div class="ref-step">
            <div class="ref-step-num">2</div>
            <div class="ref-step-body">
                <div class="ref-step-title">Friend Signs Up</div>
                <div class="ref-step-sub">They join using your code</div>
            </div>
        </div>
        <div class="ref-step">
            <div class="ref-step-num">3</div>
            <div class="ref-step-body">
                <div class="ref-step-title">Earn Together</div>
                <div class="ref-step-sub">Get 50% when they complete offers</div>
            </div>
        </div>
    </div>

</div>

<!-- Toast -->
<div class="ref-toast" id="refToast">✅ Copied to clipboard!</div>

<script>
const REFER_CODE = '<?php echo addslashes($referCode); ?>';
const REFER_LINK = '<?php echo addslashes($referLink); ?>';

/* ── Copy code ── */
function copyCode() {
    navigator.clipboard.writeText(REFER_CODE).then(() => {
        showToast('✅ Code copied!');
    }).catch(() => {
        // Fallback
        const el = document.createElement('textarea');
        el.value = REFER_CODE;
        document.body.appendChild(el);
        el.select(); document.execCommand('copy');
        document.body.removeChild(el);
        showToast('✅ Code copied!');
    });
}

/* ── Share link ── */
function shareLink() {
    const text = `🎉 Join me on FolloPay and earn real money!\nUse my referral code: *${REFER_CODE}*\n👉 ${REFER_LINK}`;

    if (navigator.share) {
        navigator.share({ title: 'Join FolloPay', text, url: REFER_LINK })
            .catch(() => {});
    } else {
        navigator.clipboard.writeText(text).then(() => {
            showToast('✅ Link copied!');
        }).catch(() => {
            showToast('✅ Link copied!');
        });
    }
}

/* ── Tab switch ── */
function setRefTab(tab, btn) {
    document.querySelectorAll('.ref-sub-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('ref-tab-rankings').style.display  = tab === 'rankings'  ? 'block' : 'none';
    document.getElementById('ref-tab-referrals').style.display = tab === 'referrals' ? 'block' : 'none';
}

/* ── Toast ── */
function showToast(msg) {
    const t = document.getElementById('refToast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2200);
}
</script>