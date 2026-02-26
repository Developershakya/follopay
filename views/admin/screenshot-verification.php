<?php
// Safety check
if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}
?>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
/* ══════════════════════════════════════════════
   SCREENSHOT VERIFICATION — EMBEDDED IN INDEX
   All selectors scoped to #sv-root to avoid conflicts
══════════════════════════════════════════════ */
:root{
    --sv-purple:     #7c3aed;
    --sv-purple-d:   #6d28d9;
    --sv-purple-l:   #ede9fe;
    --sv-purple-mid: #a78bfa;
    --sv-green:      #10b981;
    --sv-red:        #ef4444;
    --sv-amber:      #f59e0b;
    --sv-bg:         #f5f3ff;
    --sv-card-bg:    #ffffff;
    --sv-border:     #e5e7eb;
    --sv-text:       #111827;
    --sv-muted:      #6b7280;
    --sv-sidebar-w:  300px;
}

/* Root wrapper fills the main content area */
#sv-root{
    display:flex;
    flex-direction:column;
    height:calc(100vh - 56px);
    overflow:hidden;
    font-family:'Sora',sans-serif;
    background:var(--sv-bg);
    margin:-1.5rem -1rem 0;
}

/* ── Mobile filter strip (Today/Week/Month) ── */
#sv-filter-strip{
    display:none; /* shown on mobile via media query */
    background:#5b21b6;
    padding:7px 10px;
    gap:7px;
    overflow-x:auto;
    align-items:center;
    flex-shrink:0;
    border-bottom:1px solid rgba(255,255,255,.15);
}
#sv-filter-strip::-webkit-scrollbar{display:none}

/* Desktop sub-header bar */
#sv-desktop-bar{
    background:#7c3aed;
    display:flex;
    align-items:center;
    padding:0 14px;
    height:50px;
    gap:10px;
    flex-shrink:0;
}
#sv-desktop-bar .sv-bar-title{
    color:#fff;font-size:15px;font-weight:800;flex:1;
}
#sv-desktop-bar .sv-bar-pills{display:flex;gap:6px;align-items:center}
#sv-desktop-bar .sv-badge{
    background:rgba(255,255,255,.2);color:#fff;
    border-radius:50px;padding:4px 10px;font-size:11px;font-weight:700;white-space:nowrap;
}

/* Date pills */
.sv-dpill{
    padding:5px 13px;border-radius:50px;font-size:12px;font-weight:700;white-space:nowrap;
    border:none;cursor:pointer;transition:all .2s;flex-shrink:0;
    background:rgba(255,255,255,.18);color:#fff;
}
.sv-dpill.active{background:#fff;color:#7c3aed;box-shadow:0 2px 8px rgba(0,0,0,.2)}
.sv-dpill-custom{
    background:rgba(255,255,255,.1);color:#fff;
    padding:5px 11px;border-radius:50px;font-size:12px;font-weight:700;
    border:1.5px dashed rgba(255,255,255,.5);cursor:pointer;
    display:flex;align-items:center;gap:5px;white-space:nowrap;flex-shrink:0;
}
.sv-dpill-custom.active{background:#fff;color:#7c3aed;border-style:solid}

/* Date range popover */
.sv-date-pop{
    display:none;position:fixed;top:110px;right:14px;background:#fff;
    border-radius:16px;padding:18px;box-shadow:0 10px 40px rgba(0,0,0,.2);
    z-index:600;min-width:260px;
}
.sv-date-pop.open{display:block}
.sv-dr-label{font-size:11px;color:var(--sv-muted);font-weight:700;margin-bottom:4px}
.sv-dr-input{
    width:100%;padding:8px 12px;border:2px solid var(--sv-border);border-radius:8px;
    font-size:13px;color:var(--sv-text);margin-bottom:12px;outline:none;font-family:inherit;
}
.sv-dr-input:focus{border-color:var(--sv-purple)}
.sv-dr-btn{
    width:100%;background:var(--sv-purple);color:#fff;border:none;border-radius:8px;
    padding:9px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;
}

/* ══ LAYOUT (sidebar + main) ══ */
#sv-layout{
    display:flex;
    flex:1;
    overflow:hidden;
    min-height:0;
}

/* ── SIDEBAR ── */
#sv-sidebar{
    width:var(--sv-sidebar-w);flex-shrink:0;
    display:flex;flex-direction:column;
    background:#fff;border-right:2px solid var(--sv-purple-l);
    height:100%;overflow:hidden;transition:transform .3s ease;
}
.sv-sb-head{padding:12px 14px 8px;border-bottom:1px solid var(--sv-border);flex-shrink:0}
.sv-sb-title{font-size:13px;font-weight:800;color:var(--sv-text);margin-bottom:8px}
.sv-sb-toggle-wrap{display:flex;background:var(--sv-bg);border-radius:10px;padding:3px;gap:2px}
.sv-sb-toggle-btn{
    flex:1;padding:5px 0;border:none;border-radius:7px;font-size:11px;font-weight:700;
    cursor:pointer;transition:all .2s;color:var(--sv-muted);background:transparent;font-family:inherit;
}
.sv-sb-toggle-btn.active{background:var(--sv-purple);color:#fff;box-shadow:0 2px 8px rgba(124,58,237,.3)}
.sv-sb-search{padding:7px 14px;border-bottom:1px solid var(--sv-border);position:relative;flex-shrink:0}
.sv-sb-search input{
    width:100%;padding:7px 12px 7px 30px;border:1.5px solid var(--sv-border);border-radius:8px;
    font-size:12px;outline:none;font-family:inherit;color:var(--sv-text);background:var(--sv-bg);
}
.sv-sb-search input:focus{border-color:var(--sv-purple)}
.sv-sb-search i{position:absolute;left:24px;top:50%;transform:translateY(-50%);color:var(--sv-muted);font-size:11px;pointer-events:none}
.sv-sb-list{flex:1;overflow-y:auto;padding:6px;min-height:0}
.sv-sb-list::-webkit-scrollbar{width:4px}
.sv-sb-list::-webkit-scrollbar-thumb{background:var(--sv-purple-l);border-radius:4px}
.sv-sb-item{
    display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:11px;
    cursor:pointer;transition:all .2s;margin-bottom:3px;border:2px solid transparent;
}
.sv-sb-item:hover{background:var(--sv-purple-l)}
.sv-sb-item.active{background:var(--sv-purple-l);border-color:var(--sv-purple)}
.sv-sb-avatar{
    width:36px;height:36px;border-radius:9px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;
}
.sv-sb-app-logo{
    width:36px;height:36px;border-radius:9px;flex-shrink:0;
    background:var(--sv-bg);border:1.5px solid var(--sv-border);overflow:hidden;
    display:flex;align-items:center;justify-content:center;
}
.sv-sb-app-logo img{width:100%;height:100%;object-fit:cover;border-radius:7px;opacity:0;transition:opacity .3s}
.sv-sb-app-logo img.sv-loaded{opacity:1}
.sv-sb-item-info{flex:1;min-width:0}
.sv-sb-item-name{font-size:12px;font-weight:700;color:var(--sv-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sv-sb-item-sub{font-size:10px;color:var(--sv-muted);margin-top:1px}
.sv-sb-item-date{font-size:10px;color:var(--sv-purple);margin-top:2px;font-weight:600}
.sv-sb-prog{flex-shrink:0;display:flex;flex-direction:column;align-items:flex-end;gap:2px}
.sv-sb-prog-count{font-size:11px;font-weight:800;color:var(--sv-purple);background:var(--sv-purple-l);padding:2px 7px;border-radius:50px;white-space:nowrap}

/* ── MAIN CONTENT ── */
#sv-main{flex:1;display:flex;flex-direction:column;min-width:0;height:100%}
.sv-main-head{
    padding:10px 14px 8px;background:#fff;border-bottom:2px solid var(--sv-purple-l);
    display:flex;align-items:center;gap:8px;flex-wrap:wrap;flex-shrink:0;
}
.sv-main-head-left{flex:1;min-width:0}
.sv-main-head-title{font-size:13px;font-weight:800;color:var(--sv-text)}
.sv-main-head-sub{font-size:11px;color:var(--sv-muted);margin-top:1px}
.sv-mh-sort{
    padding:5px 10px;border:1.5px solid var(--sv-border);border-radius:50px;
    font-size:11px;font-weight:700;background:#fff;cursor:pointer;color:var(--sv-text);
    appearance:none;outline:none;font-family:inherit;
}

/* ══════════════════════════════════════════════
   FIX 1: Cards ka size decrease na ho
   — Fixed column width, scroll aayegi
══════════════════════════════════════════════ */
#sv-cards-wrap{
    
    overflow-y:auto;
    overflow-x:hidden;
    -webkit-overflow-scrolling:touch;
    padding:12px 12px 20px;
    display:grid;
    grid-template-columns:repeat(auto-fill, 190px);
    grid-auto-rows: 1fr;
    gap:11px;
    align-content:start;
    min-height:0;
    justify-content:start;
}
#sv-cards-wrap::-webkit-scrollbar{width:4px}
#sv-cards-wrap::-webkit-scrollbar-thumb{background:var(--sv-purple-l);border-radius:4px}

/* Cards kabhi shrink nahi honge */
.sv-card{
    min-width:180px;
    background:#fff;border-radius:14px;border:2px solid var(--sv-border);
    overflow:hidden;cursor:pointer;transition:all .2s;display:flex;flex-direction:column;
}
.sv-card:hover{border-color:var(--sv-purple);box-shadow:0 4px 18px rgba(124,58,237,.12);transform:translateY(-2px)}

/* ── SKELETON ── */
.sv-skeleton-card{border-radius:14px;background:#fff;border:1.5px solid var(--sv-border);overflow:hidden}
.sv-skel-img{height:150px;background:linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);background-size:200% 100%;animation:sv-skel 1.4s infinite}
.sv-skel-line{height:9px;border-radius:6px;margin:9px 11px 0;background:linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);background-size:200% 100%;animation:sv-skel 1.4s infinite}
.sv-skel-line.sv-short{width:60%;margin-bottom:9px}
@keyframes sv-skel{0%{background-position:200% 0}100%{background-position:-200% 0}}

/* ── EMPTY ── */
.sv-empty{grid-column:1/-1;text-align:center;padding:50px 20px;color:var(--sv-muted)}
.sv-empty i{font-size:36px;color:var(--sv-purple-l);display:block;margin-bottom:10px}
.sv-empty p{font-size:13px;font-weight:600}

/* ── SCREENSHOT CARD INNER ── */
.sv-card-img{height:150px;background:var(--sv-bg);position:relative;overflow:hidden;flex-shrink:0}
.sv-card-img img{width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity .3s;display:block}
.sv-card-img img.sv-loaded{opacity:1}
.sv-card-img-shimmer{position:absolute;inset:0;background:linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);background-size:200% 100%;animation:sv-skel 1.4s infinite}
.sv-card-icon{position:absolute;bottom:7px;left:7px;width:30px;height:30px;border-radius:7px;background:#fff;border:1.5px solid var(--sv-border);overflow:hidden;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.12)}
.sv-card-icon img{width:100%;height:100%;object-fit:cover;border-radius:5px;opacity:0;transition:opacity .3s}
.sv-card-icon img.sv-loaded{opacity:1}
.sv-card-body{padding:9px 11px 11px}
.sv-card-name{font-size:12px;font-weight:700;color:var(--sv-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sv-card-user{font-size:10px;color:var(--sv-muted);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sv-card-meta{display:flex;align-items:center;justify-content:space-between;margin-top:5px;gap:6px}
.sv-card-price{display:inline-block;background:var(--sv-green);color:#fff;border-radius:50px;padding:2px 8px;font-size:10px;font-weight:800}
.sv-card-date{font-size:9px;color:var(--sv-muted);font-weight:600;white-space:nowrap}
.sv-card-id{font-size:9px;color:var(--sv-purple);font-weight:700;opacity:.7}
.sv-card-actions{display:flex;gap:5px;margin-top:7px}
.sv-card-btn{flex:1;border:none;border-radius:7px;padding:6px;font-size:11px;font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:3px}
.sv-card-btn.sv-approve{background:#d1fae5;color:#065f46}
.sv-card-btn.sv-approve:hover{background:#10b981;color:#fff}
.sv-card-btn.sv-reject{background:#fee2e2;color:#991b1b}
.sv-card-btn.sv-reject:hover{background:#ef4444;color:#fff}
.sv-card-btn.sv-view{background:var(--sv-purple-l);color:var(--sv-purple)}
.sv-card-btn.sv-view:hover{background:var(--sv-purple);color:#fff}

/* ══ OVERLAY ══ */
#sv-overlay{
    position:fixed;inset:0;z-index:1000;
    display:flex;align-items:center;justify-content:center;
    background:rgba(0,0,0,.6);
    opacity:0;pointer-events:none;transition:opacity .25s;
}
#sv-overlay.open{opacity:1;pointer-events:auto}

/* ══════════════════════════════════════════════
   FULLSCREEN VIEWER
   FIX 3: Topbar buttons overflow fix — wrap to next row
══════════════════════════════════════════════ */
#sv-fs-viewer{
    position:fixed;inset:0;z-index:950;
    background:#111;display:flex;flex-direction:column;
    opacity:0;pointer-events:none;transition:opacity .25s;
}
#sv-fs-viewer.open{opacity:1;pointer-events:auto}

.sv-fs-topbar{
    background:rgba(0,0,0,.85);
    padding:9px 13px;
    display:flex;
    align-items:center;
    flex-wrap:wrap; /* FIX: buttons wrap to next row when needed */
    gap:8px;
    border-bottom:1px solid rgba(255,255,255,.1);
    flex-shrink:0;
}

.sv-fs-info{
    flex:1;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(90px,1fr));
    gap:5px;
    min-width:0;
    min-width:200px; /* enough space before wrapping */
}
.sv-fs-info-item p:first-child{font-size:9px;color:rgba(255,255,255,.45);font-weight:600;margin-bottom:1px}
.sv-fs-info-item p:last-child{font-size:12px;color:#fff;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

.sv-fs-actions{
    display:flex;
    gap:5px;
    flex-shrink:0;
    flex-wrap:wrap; /* FIX: buttons wrap among themselves too */
    justify-content:flex-end;
}

.sv-fs-btn{
    padding:7px 12px;border-radius:7px;font-size:11px;font-weight:700;border:none;cursor:pointer;
    display:flex;align-items:center;gap:4px;transition:all .2s;white-space:nowrap;font-family:inherit;
}
.sv-fs-btn.sv-approve{background:#16a34a;color:#fff}
.sv-fs-btn.sv-reject{background:#dc2626;color:#fff}
.sv-fs-btn.sv-download{background:#2563eb;color:#fff}
.sv-fs-btn.sv-close{background:rgba(255,255,255,.12);color:#fff}
.sv-fs-btn:hover{filter:brightness(1.15)}

.sv-fs-body{flex:1;display:flex;overflow:hidden;min-height:0}
.sv-fs-img-area{
    flex:1;display:flex;align-items:center;justify-content:center;
    padding:10px;overflow:auto;
}
.sv-fs-img-area img{
    max-width:100%;max-height:100%;border-radius:7px;
    object-fit:contain;box-shadow:0 4px 28px rgba(0,0,0,.5);
    transition:transform .3s;cursor:zoom-in;
}

/* Desktop comment sidebar */
.sv-fs-sidebar{
    width:250px;background:rgba(0,0,0,.4);border-left:1px solid rgba(255,255,255,.08);
    padding:13px;overflow-y:auto;flex-shrink:0;display:flex;flex-direction:column;
}
.sv-fs-sidebar::-webkit-scrollbar{width:3px}
.sv-fs-sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.15);border-radius:3px}
.sv-detail-label{font-size:9px;color:rgba(255,255,255,.4);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px}
.sv-detail-val{
    font-size:12px;color:#fff;font-weight:600;line-height:1.5;word-break:break-word;
    margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.07);
}

.sv-comment-block{
    background:rgba(0,0,0,.35);border-radius:8px;padding:10px;margin-bottom:12px;
    padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,.07);
}
.sv-comment-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:5px}
.sv-comment-label{font-size:9px;color:rgba(255,255,255,.4);font-weight:700;text-transform:uppercase;letter-spacing:.5px}
.sv-copy-btn{
    background:rgba(124,58,237,.4);color:#c4b5fd;border:none;border-radius:5px;
    padding:3px 8px;font-size:10px;font-weight:700;cursor:pointer;
    display:flex;align-items:center;gap:3px;transition:all .2s;font-family:inherit;
}
.sv-copy-btn:hover{background:rgba(124,58,237,.7);color:#fff}
.sv-copy-btn.sv-copied{background:#16a34a;color:#fff}
.sv-comment-text{font-family:monospace;font-size:11px;color:#c4b5fd;line-height:1.6;word-break:break-word}

/* Mobile comment strip */
.sv-mobile-comment{
    display:none;
    background:rgba(0,0,0,.75);border-top:1px solid rgba(255,255,255,.1);
    padding:7px 13px 9px;flex-shrink:0;
}
.sv-mobile-comment-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:3px}
.sv-mobile-comment-label{font-size:9px;color:rgba(255,255,255,.4);font-weight:700;text-transform:uppercase;letter-spacing:.5px}
.sv-mobile-copy-btn{
    background:rgba(124,58,237,.4);color:#c4b5fd;border:none;border-radius:5px;
    padding:3px 8px;font-size:10px;font-weight:700;cursor:pointer;
    display:flex;align-items:center;gap:3px;font-family:inherit;
}
.sv-mobile-copy-btn.sv-copied{background:#16a34a;color:#fff}
.sv-mobile-comment-text{
    font-family:monospace;font-size:11px;color:#c4b5fd;line-height:1.5;
    word-break:break-word;max-height:55px;overflow-y:auto;
}
.sv-mobile-comment-text::-webkit-scrollbar{width:2px}
.sv-mobile-comment-text::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}

/* Nav arrows */
.sv-nav-arrow{
    position:fixed;top:50%;transform:translateY(-50%);
    background:rgba(255,255,255,.12);border:none;color:#fff;
    width:38px;height:38px;border-radius:50%;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    transition:all .2s;z-index:960;
}
.sv-nav-arrow:hover{background:rgba(255,255,255,.25)}
.sv-nav-arrow.sv-prev{left:10px}
.sv-nav-arrow.sv-next{right:10px}
.sv-nav-arrow:disabled{opacity:.2;cursor:not-allowed}

/* ══ REJECT MODAL ══ */
.sv-reject-modal{
    background:#fff;border-radius:18px;padding:22px;
    max-width:400px;width:92%;
    transform:scale(.95);transition:transform .25s;font-family:'Sora',sans-serif;
}
#sv-overlay.open .sv-reject-modal{transform:scale(1)}
.sv-rm-title{font-size:16px;font-weight:800;margin-bottom:14px;color:var(--sv-text)}
.sv-rm-select{
    width:100%;padding:9px 11px;border:2px solid var(--sv-border);border-radius:9px;
    font-size:12px;color:var(--sv-text);margin-bottom:9px;outline:none;font-family:inherit;
    appearance:none;cursor:pointer;
    background-image:url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat:no-repeat;background-position:right 10px center;background-size:1.1em;padding-right:1.8rem;
}
.sv-rm-select:focus{border-color:var(--sv-purple)}
.sv-rm-textarea{
    width:100%;padding:9px 11px;border:2px solid var(--sv-border);border-radius:9px;
    font-size:12px;color:var(--sv-text);resize:none;height:80px;font-family:inherit;outline:none;
    display:none;margin-bottom:9px;
}
.sv-rm-textarea:focus{border-color:var(--sv-purple)}
.sv-rm-preview{
    background:var(--sv-bg);border:2px solid var(--sv-purple-l);border-radius:9px;
    padding:9px 11px;font-size:12px;font-weight:700;color:var(--sv-text);
    margin-bottom:14px;display:none;
}
.sv-rm-actions{display:flex;gap:9px;justify-content:flex-end}
.sv-rm-btn{padding:8px 18px;border-radius:7px;font-size:12px;font-weight:700;border:none;cursor:pointer;font-family:inherit}
.sv-rm-btn.sv-cancel{background:var(--sv-bg);color:var(--sv-muted)}
.sv-rm-btn.sv-submit{background:var(--sv-red);color:#fff}

/* Sidebar backdrop (mobile) */
#sv-sb-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:149}
#sv-sb-backdrop.open{display:block}

/* ══════════════════════════════════════════════
   FIX 2: Mobile — sirf ek header dikhao
   Desktop bar mobile pe hide, filter strip show
══════════════════════════════════════════════ */
@media(max-width:768px){
    #sv-root{height:calc(100vh - 56px)}

    /* Desktop bar completely hide on mobile */
    #sv-desktop-bar{display:none !important}

    /* Filter strip show on mobile */
    #sv-filter-strip{display:flex !important}

    #sv-sidebar{
        position:fixed;left:0;z-index:150;transform:translateX(-100%);
        bottom:64px;
        top:auto;
        height:auto;
        max-height:70vh;
    }
    #sv-sidebar.open{transform:translateX(0)}

    /* Cards — 2 columns on mobile with fixed size */
    #sv-cards-wrap{
        grid-template-columns:repeat(2, minmax(148px, 1fr)) !important;
        overflow-x:hidden;
    }

    .sv-fs-sidebar{display:none}
    .sv-mobile-comment{display:block}

    /* FIX 3: Fullscreen viewer topbar on mobile */
    .sv-fs-topbar{
        flex-direction:column;
        align-items:stretch;
        padding:10px 13px;
        gap:8px;
    }
    .sv-fs-info{
        width:100%;
        grid-template-columns:1fr 1fr;
    }
    .sv-fs-actions{
        width:100%;
        justify-content:flex-start;
        gap:6px;
        flex-wrap:wrap;
    }
    .sv-fs-btn{
        flex:1;
        justify-content:center;
        padding:8px 6px;
        font-size:11px;
    }

    /* Hide ID col on mobile, show Date */
    .sv-card-id{display:none}
    .sv-card-date{display:block}
}

@media(min-width:769px){
    /* Desktop bar show */
    #sv-desktop-bar{display:flex !important}

    /* Filter strip hide on desktop */
    #sv-filter-strip{display:none !important}

    .sv-card-date{display:none}
    .sv-card-id{display:block}
}

/* Very small screens */
@media(max-width:380px){
    #sv-cards-wrap{grid-template-columns:1fr 1fr !important}

    .sv-fs-btn .sv-btn-txt{display:none} /* sirf icon dikhao */
    .sv-fs-btn{flex:0 0 auto;padding:8px 10px}
}
</style>

<div id="sv-root">

    <!-- ── Desktop bar with pills ── -->
    <div id="sv-desktop-bar">
        <!-- Mobile: sidebar toggle -->
        <button class="sv-fs-btn sv-close" onclick="svToggleSidebar()" style="display:none" id="sv-mob-sidebar-btn">
            <i class="fas fa-bars"></i>
        </button>
        <div class="sv-bar-title"><i class="fas fa-shield-alt" style="margin-right:7px"></i>Screenshot Verification</div>
        <div class="sv-bar-pills">
            <button class="sv-dpill active" id="svdp-today"  onclick="svSetDate('today',this)">Today</button>
            <button class="sv-dpill"        id="svdp-week"   onclick="svSetDate('week',this)">Week</button>
            <button class="sv-dpill"        id="svdp-month"  onclick="svSetDate('month',this)">Month</button>
            <button class="sv-dpill-custom" id="svdp-custom" onclick="svToggleDatePop()"><i class="fas fa-calendar"></i> Custom</button>
        </div>
        <span class="sv-badge" id="svBadge">0 Pending</span>
    </div>

    <!-- ── Mobile filter strip (below desktop bar) ── -->
    <div id="sv-filter-strip">
        <button class="sv-fs-btn sv-close" onclick="svToggleSidebar()" style="padding:5px 9px;font-size:12px;background:rgba(255,255,255,.18)">
            <i class="fas fa-bars"></i>
        </button>
        <button class="sv-dpill active" id="svdps-today"  onclick="svSetDate('today',this)">Today</button>
        <button class="sv-dpill"        id="svdps-week"   onclick="svSetDate('week',this)">Week</button>
        <button class="sv-dpill"        id="svdps-month"  onclick="svSetDate('month',this)">Month</button>
        <button class="sv-dpill-custom" id="svdps-custom" onclick="svToggleDatePop()"><i class="fas fa-calendar"></i> Custom</button>
        <span style="background:rgba(255,255,255,.2);color:#fff;border-radius:50px;padding:4px 10px;font-size:11px;font-weight:700;white-space:nowrap;flex-shrink:0" id="svBadgeMobile">0 Pending</span>
    </div>

    <!-- Date range popover -->
    <div class="sv-date-pop" id="svDatePop">
        <div class="sv-dr-label">From Date</div>
        <input type="date" class="sv-dr-input" id="svDrFrom">
        <div class="sv-dr-label">To Date</div>
        <input type="date" class="sv-dr-input" id="svDrTo">
        <button class="sv-dr-btn" onclick="svApplyCustomDate()">Apply</button>
    </div>

    <div id="sv-sb-backdrop" onclick="svCloseSidebar()"></div>

    <!-- ── LAYOUT ── -->
    <div id="sv-layout">

        <!-- SIDEBAR -->
        <div id="sv-sidebar">
            <div class="sv-sb-head">
                <div class="sv-sb-title">Filter By</div>
                <div class="sv-sb-toggle-wrap">
                    <button class="sv-sb-toggle-btn active" id="svStbUser" onclick="svSetSidebarMode('user')">
                        <i class="fas fa-user" style="margin-right:3px"></i>User
                    </button>
                    <button class="sv-sb-toggle-btn" id="svStbApp" onclick="svSetSidebarMode('app')">
                        <i class="fas fa-th" style="margin-right:3px"></i>App
                    </button>
                </div>
            </div>
            <div class="sv-sb-search">
                <i class="fas fa-search"></i>
                <input type="text" id="svSbSearch" placeholder="Search..." oninput="svFilterSidebar()">
            </div>
            <div class="sv-sb-list" id="svSbList"></div>
        </div>

        <!-- MAIN -->
        <div id="sv-main">
            <div class="sv-main-head">
                <div class="sv-main-head-left">
                    <div class="sv-main-head-title" id="svMainTitle">All Screenshots</div>
                    <div class="sv-main-head-sub" id="svMainSub">Select a filter from sidebar</div>
                </div>
                <select class="sv-mh-sort" onchange="svSetSort(this.value)">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="price_high">Price ↑</option>
                    <option value="price_low">Price ↓</option>
                </select>
            </div>

            <div id="sv-cards-wrap"></div>
        </div>
    </div>
</div>

<!-- ══ FULLSCREEN VIEWER ══ -->
<div id="sv-fs-viewer">
    <div class="sv-fs-topbar">
        <div class="sv-fs-info">
            <div class="sv-fs-info-item"><p>User</p><p id="svFvUser">-</p></div>
            <div class="sv-fs-info-item"><p>App</p><p id="svFvApp">-</p></div>
            <div class="sv-fs-info-item"><p>Reward</p><p id="svFvPrice" style="color:#4ade80">₹0</p></div>
            <!-- Desktop: ID | Mobile: Date -->
            <div class="sv-fs-info-item" id="svFvMetaItem"><p id="svFvMetaLabel">Date</p><p id="svFvMeta" style="color:#a78bfa">-</p></div>
        </div>
        <div class="sv-fs-actions">
            <button class="sv-fs-btn sv-download" onclick="svDownloadImg()" title="Download"><i class="fas fa-download"></i></button>
            <button class="sv-fs-btn sv-approve"  onclick="svApproveFromViewer()"><i class="fas fa-check"></i> <span class="sv-btn-txt">Approve</span></button>
            <button class="sv-fs-btn sv-reject"   onclick="svOpenRejectModal()"><i class="fas fa-times"></i> <span class="sv-btn-txt">Reject</span></button>
            <button class="sv-fs-btn sv-close"    onclick="svCloseViewer()"><i class="fas fa-times"></i></button>
        </div>
    </div>

    <div class="sv-fs-body">
        <div class="sv-fs-img-area">
            <img id="svFvImage" src="" alt="Screenshot" onclick="svToggleZoom(this)">
        </div>

        <!-- Desktop sidebar -->
        <div class="sv-fs-sidebar">
            <div class="sv-comment-block">
                <div class="sv-comment-header">
                    <span class="sv-comment-label">📋 Assigned Comment</span>
                    <button class="sv-copy-btn" id="svDesktopCopyBtn" onclick="svCopyComment('svDesktopCopyBtn')">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <div class="sv-comment-text" id="svFvComment">-</div>
            </div>
            <div class="sv-detail-label">Submission Time</div>
            <div class="sv-detail-val" id="svFvTime">-</div>
            <div class="sv-detail-label">App Link</div>
            <div class="sv-detail-val" style="font-family:monospace;font-size:10px;color:#a78bfa;background:rgba(0,0,0,.3);padding:5px;border-radius:5px" id="svFvLink">-</div>
            <div class="sv-detail-label" style="margin-top:10px">Phone</div>
            <div class="sv-detail-val" id="svFvPhone">-</div>
        </div>
    </div>

    <!-- Mobile comment strip -->
    <div class="sv-mobile-comment">
        <div class="sv-mobile-comment-row">
            <span class="sv-mobile-comment-label">📋 Comment</span>
            <button class="sv-mobile-copy-btn" id="svMobileCopyBtn" onclick="svCopyComment('svMobileCopyBtn')">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
        <div class="sv-mobile-comment-text" id="svFvCommentMobile">-</div>
    </div>
</div>

<!-- Nav arrows -->
<button class="sv-nav-arrow sv-prev" id="svNavPrev" onclick="svNavigate(-1)" style="display:none"><i class="fas fa-chevron-left"></i></button>
<button class="sv-nav-arrow sv-next" id="svNavNext" onclick="svNavigate(1)"  style="display:none"><i class="fas fa-chevron-right"></i></button>

<!-- ══ REJECT MODAL ══ -->
<div id="sv-overlay">
    <div class="sv-reject-modal">
        <div class="sv-rm-title">Rejection Reason</div>
        <select class="sv-rm-select" id="svRmSelect" onchange="svHandleRejectSelect(this.value)">
            <option value="">-- Select reason --</option>
            <option value="Fake / Edited Screenshot">🖼️ Fake / Edited Screenshot</option>
            <option value="Wrong Task Completed">❌ Wrong Task Completed</option>
            <option value="Screenshot Not Clear">👁️ Screenshot Not Clear</option>
            <option value="App Not Installed Properly">📱 App Not Installed Properly</option>
            <option value="Duplicate Submission">📋 Duplicate Submission</option>
            <option value="Wrong Image Uploaded">🔄 Wrong Image Uploaded</option>
            <option value="Comment Assignment Not Match">💬 Comment Assignment Not Match</option>
            <option value="Comment Not Showing Also Use Given Comment Next Time">📝 Comment Not Showing – Use Given Comment Next Time</option>
            <option value="App Name Not Showing">📛 App Name Not Showing</option>
            <option value="App Not Installed">⚠️ App Not Installed</option>
            <option value="Review Not post">⭐ Review Not Posted</option>
            <option value="Review Goes in Spam">🚫 Review Goes in Spam</option>
            <option value="other">✏️ Other (Write Manually)</option>
        </select>
        <textarea class="sv-rm-textarea" id="svRmTextarea" placeholder="Write custom reason..."></textarea>
        <div class="sv-rm-preview" id="svRmPreview"></div>
        <div class="sv-rm-actions">
            <button class="sv-rm-btn sv-cancel" onclick="svCloseRejectModal()">Cancel</button>
            <button class="sv-rm-btn sv-submit" onclick="svSubmitReject()">Reject</button>
        </div>
    </div>
</div>

<script>
/* ══════════════════════════════════════════════════════
   SV STATE  (all vars prefixed sv_ to avoid conflicts)
══════════════════════════════════════════════════════ */
var svAllData        = [];
var svViewData       = [];
var svSidebarMode    = 'user';
var svActiveSbKey    = null;
var svDateFilter     = 'today';
var svCustomFrom     = '';
var svCustomTo       = '';
var svSortMode       = 'newest';
var svViewerIdx      = 0;
var svCurrentId      = null;
var svRejectReason   = '';
var svRendered       = 0;
var svRendering      = false;
var SV_CHUNK         = 20;

var svIsMobile = () => window.innerWidth <= 768;

/* ── BOOT ── */
document.addEventListener('DOMContentLoaded', function(){
    svLoadData();
    svSetupScroll();

    // Show mobile sidebar btn in desktop bar on mobile
    var mBtn = document.getElementById('sv-mob-sidebar-btn');
    if (mBtn) mBtn.style.display = svIsMobile() ? 'flex' : 'none';
    window.addEventListener('resize', function(){
        if (mBtn) mBtn.style.display = svIsMobile() ? 'flex' : 'none';
    });

    // Update viewer meta label per screen
    svUpdateViewerMetaLabel();
    window.addEventListener('resize', svUpdateViewerMetaLabel);

    // Close date pop on outside click
    document.addEventListener('click', function(e){
        if (!e.target.closest('#svDatePop') &&
            !e.target.closest('#svdp-custom') &&
            !e.target.closest('#svdps-custom')) {
            document.getElementById('svDatePop').classList.remove('open');
        }
    });
});

function svUpdateViewerMetaLabel(){
    var lbl = document.getElementById('svFvMetaLabel');
    if (lbl) lbl.textContent = svIsMobile() ? 'Date' : 'ID';
}

/* ── DATA LOAD ── */
function svLoadData(){
    svShowSkeletons();
    fetch('ajax/admin.php?action=get_pending_screenshots')
        .then(function(r){ return r.json(); })
        .then(function(d){
            svAllData = d.screenshots || d.submissions || [];
            svApplyFilters();
        })
        .catch(function(){ svShowEmpty('Failed to load. Try refreshing.'); });
}

/* ── DATE FILTER ── */
function svSetDate(f, el){
    svDateFilter = f;
    svCustomFrom = ''; svCustomTo = '';
    document.querySelectorAll('.sv-dpill, .sv-dpill-custom').forEach(function(b){ b.classList.remove('active'); });
    var map = { today:['svdp-today','svdps-today'], week:['svdp-week','svdps-week'], month:['svdp-month','svdps-month'] };
    if (map[f]) map[f].forEach(function(id){ var b=document.getElementById(id); if(b) b.classList.add('active'); });
    svApplyFilters();
}

function svToggleDatePop(){
    document.getElementById('svDatePop').classList.toggle('open');
}

function svApplyCustomDate(){
    svCustomFrom = document.getElementById('svDrFrom').value;
    svCustomTo   = document.getElementById('svDrTo').value;
    if (!svCustomFrom || !svCustomTo){ if(typeof showToast!=='undefined') showToast('Select both dates','warning'); return; }
    svDateFilter = 'custom';
    document.querySelectorAll('.sv-dpill').forEach(function(b){ b.classList.remove('active'); });
    ['svdp-custom','svdps-custom'].forEach(function(id){ var b=document.getElementById(id); if(b) b.classList.add('active'); });
    document.getElementById('svDatePop').classList.remove('open');
    svApplyFilters();
}

function svInDateRange(row){
    var d = new Date(row.submitted_time || row.created_at || '');
    var today = new Date(); today.setHours(0,0,0,0);
    if (svDateFilter==='today') return d >= today;
    if (svDateFilter==='week'){  var w=new Date(today); w.setDate(today.getDate()-6); return d>=w; }
    if (svDateFilter==='month'){ var m=new Date(today); m.setDate(today.getDate()-29); return d>=m; }
    if (svDateFilter==='custom' && svCustomFrom && svCustomTo){
        var fr=new Date(svCustomFrom), to=new Date(svCustomTo); to.setHours(23,59,59,999);
        return d>=fr && d<=to;
    }
    return true;
}

/* ── APPLY ALL FILTERS ── */
function svApplyFilters(){
    var dated = svAllData.filter(svInDateRange);
    var badge = dated.length + ' Pending';
    document.getElementById('svBadge').textContent = badge;
    var bm = document.getElementById('svBadgeMobile');
    if (bm) bm.textContent = badge;
    svBuildSidebar(dated);
    svApplyViewFilter(dated);
}

/* ── SIDEBAR BUILD ── */
function svBuildSidebar(dated){
    var search = document.getElementById('svSbSearch').value.toLowerCase();
    var mode = svSidebarMode;

    var map = {};
    dated.forEach(function(row){
        var k = mode==='user' ? (row.username||'Unknown') : (row.app_name||'Unknown App');
        if (!map[k]) map[k] = { key:k, items:[], appLink: row.app_link||'', latestDate: null };
        map[k].items.push(row);
        // track latest date for app mode
        var d = new Date(row.submitted_time || row.created_at || 0);
        if (!map[k].latestDate || d > map[k].latestDate) map[k].latestDate = d;
    });

    var groups = Object.values(map).sort(function(a,b){ return b.items.length - a.items.length; });
    if (search) groups = groups.filter(function(g){ return g.key.toLowerCase().includes(search); });

    var total = groups.reduce(function(s,g){ return s+g.items.length; }, 0);
    var html = '<div class="sv-sb-item '+(svActiveSbKey===null?'active':'')+'" onclick="svSelectSbItem(null)">'
        + '<div class="sv-sb-avatar" style="background:var(--sv-purple)"><i class="fas fa-layer-group" style="font-size:12px"></i></div>'
        + '<div class="sv-sb-item-info"><div class="sv-sb-item-name">All</div>'
        + '<div class="sv-sb-item-sub">'+total+' total</div></div>'
        + '<div class="sv-sb-prog"><span class="sv-sb-prog-count">'+total+'</span></div></div>';

    groups.forEach(function(g){
        var count = g.items.length;
        var isActive = svActiveSbKey === g.key;
        if (mode==='user'){
            var initials = g.key.slice(0,2).toUpperCase();
            var hue = svStrHue(g.key);
            html += '<div class="sv-sb-item '+(isActive?'active':'')+'" onclick="svSelectSbItem(\''+svEscJ(g.key)+'\')">'
                + '<div class="sv-sb-avatar" style="background:hsl('+hue+',55%,48%)">'+initials+'</div>'
                + '<div class="sv-sb-item-info"><div class="sv-sb-item-name">'+svEsc(g.key)+'</div>'
                + '<div class="sv-sb-item-sub">'+count+' pending</div></div>'
                + '<div class="sv-sb-prog"><span class="sv-sb-prog-count">'+count+'</span></div></div>';
        } else {
            // App mode — show logo + date
            var dateStr = g.latestDate ? svFmtDate(g.latestDate) : '';
            html += '<div class="sv-sb-item '+(isActive?'active':'')+'" onclick="svSelectSbItem(\''+svEscJ(g.key)+'\')">'
                + '<div class="sv-sb-app-logo"><img data-src="'+svGetLogoUrl(g.appLink)+'" alt="" onload="this.classList.add(\'sv-loaded\')" onerror="this.src=\'https://www.google.com/s2/favicons?sz=64&domain=android.com\';this.classList.add(\'sv-loaded\')"></div>'
                + '<div class="sv-sb-item-info"><div class="sv-sb-item-name">'+svEsc(g.key)+'</div>'
                + '<div class="sv-sb-item-sub">'+count+' screenshots</div>'
                + (dateStr ? '<div class="sv-sb-item-date"><i class="fas fa-clock" style="font-size:8px;margin-right:2px"></i>'+dateStr+'</div>' : '')
                + '</div>'
                + '<div class="sv-sb-prog"><span class="sv-sb-prog-count">'+count+'</span></div></div>';
        }
    });

    var list = document.getElementById('svSbList');
    list.innerHTML = html || '<div style="text-align:center;padding:28px 10px;color:var(--sv-muted);font-size:12px;font-weight:600">No items</div>';

    // Lazy load app logos
    if (mode==='app'){
        list.querySelectorAll('img[data-src]').forEach(function(img){
            img.src = img.dataset.src;
        });
    }
}

function svFmtDate(d){
    if (!d) return '';
    return d.toLocaleDateString('en-IN', { day:'2-digit', month:'short' });
}

function svFilterSidebar(){
    var dated = svAllData.filter(svInDateRange);
    svBuildSidebar(dated);
}

function svSetSidebarMode(mode){
    svSidebarMode = mode;
    svActiveSbKey = null;
    document.getElementById('svStbUser').classList.toggle('active', mode==='user');
    document.getElementById('svStbApp').classList.toggle('active', mode==='app');
    svApplyFilters();
}

function svSelectSbItem(key){
    svActiveSbKey = key;
    var dated = svAllData.filter(svInDateRange);
    svBuildSidebar(dated);
    svApplyViewFilter(dated);
    svCloseSidebar();
}

/* ── VIEW FILTER + SORT ── */
function svApplyViewFilter(dated){
    var data = dated;
    if (svActiveSbKey !== null){
        if (svSidebarMode==='user'){
            data = dated.filter(function(r){ return (r.username||'Unknown')===svActiveSbKey; });
        } else {
            data = dated.filter(function(r){ return (r.app_name||'Unknown App')===svActiveSbKey; });
        }
    }
    data = svSortItems(data.slice());
    svViewData = data;

    var title = svActiveSbKey ? svEsc(svActiveSbKey) : 'All Screenshots';
    document.getElementById('svMainTitle').innerHTML = title;
    document.getElementById('svMainSub').textContent = data.length + ' screenshot'+(data.length!==1?'s':'');

    svRendered = 0;
    document.getElementById('sv-cards-wrap').innerHTML = '';
    svRenderChunk();
}

function svSetSort(v){
    svSortMode = v;
    svApplyFilters();
}

function svSortItems(arr){
    return arr.sort(function(a,b){
        var da=new Date(a.submitted_time||a.created_at||0), db=new Date(b.submitted_time||b.created_at||0);
        var pa=parseFloat(a.price||0), pb=parseFloat(b.price||0);
        if (svSortMode==='newest')     return db-da;
        if (svSortMode==='oldest')     return da-db;
        if (svSortMode==='price_high') return pb-pa;
        if (svSortMode==='price_low')  return pa-pb;
        return 0;
    });
}

/* ── CHUNK RENDER ── */
function svRenderChunk(){
    if (svRendering) return;
    if (svRendered >= svViewData.length){
        if (svViewData.length===0) svShowEmpty('No screenshots here.');
        return;
    }
    svRendering = true;
    var slice = svViewData.slice(svRendered, svRendered + SV_CHUNK);
    var wrap  = document.getElementById('sv-cards-wrap');
    var frag  = document.createDocumentFragment();
    slice.forEach(function(row){ frag.appendChild(svBuildCard(row)); });
    wrap.appendChild(frag);
    svRendered += slice.length;
    svRendering = false;

    wrap.querySelectorAll('img[data-src]:not([data-ob])').forEach(function(img){
        img.dataset.ob = '1';
        svImgObs.observe(img);
    });
}

function svSetupScroll(){
    var wrap = document.getElementById('sv-cards-wrap');
    wrap.addEventListener('scroll', function(){
        if (wrap.scrollTop + wrap.clientHeight >= wrap.scrollHeight - 200){
            svRenderChunk();
        }
    });
}

var svImgObs = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
        if (!entry.isIntersecting) return;
        var img = entry.target;
        var src = img.dataset.src; if (!src) return;
        img.src = src;
        img.onload  = function(){ img.classList.add('sv-loaded'); var sh=img.closest('.sv-card-img')?.querySelector('.sv-card-img-shimmer'); if(sh) sh.style.opacity='0'; };
        img.onerror = function(){ img.src='https://via.placeholder.com/200x150?text=No+Image'; img.classList.add('sv-loaded'); };
        svImgObs.unobserve(img);
    });
}, { rootMargin:'150px', threshold:0 });

/* ── BUILD CARD ── */
function svBuildCard(row){
    var el = document.createElement('div');
    el.className = 'sv-card';
    var dateStr = row.submitted_time ? svFmtDate(new Date(row.submitted_time)) : (row.created_at ? svFmtDate(new Date(row.created_at)) : '-');
    el.innerHTML =
        '<div class="sv-card-img" onclick="svOpenViewer('+row.id+')">'
        + '<div class="sv-card-img-shimmer"></div>'
        + '<img data-src="'+svEsc(row.screenshot_path||'')+'" alt="screenshot">'
        + '<div class="sv-card-icon"><img data-src="'+svGetLogoUrl(row.app_link||'')+'" alt="" onload="this.classList.add(\'sv-loaded\')" onerror="this.src=\'https://www.google.com/s2/favicons?sz=64&domain=android.com\';this.classList.add(\'sv-loaded\')"></div>'
        + '</div>'
        + '<div class="sv-card-body">'
        + '<div class="sv-card-name">'+svEsc(row.app_name||'Unknown App')+'</div>'
        + '<div class="sv-card-user"><i class="fas fa-user" style="font-size:8px;margin-right:2px"></i>'+svEsc(row.username||'-')+'</div>'
        + '<div class="sv-card-meta">'
        + '<span class="sv-card-price">₹'+parseFloat(row.price||0).toFixed(2)+'</span>'
        + '<span class="sv-card-date"><i class="fas fa-calendar-alt" style="font-size:8px;margin-right:2px"></i>'+dateStr+'</span>'
        + '<span class="sv-card-id">#'+row.id+'</span>'
        + '</div>'
        + '<div class="sv-card-actions">'
        + '<button class="sv-card-btn sv-view"    onclick="svOpenViewer('+row.id+')"><i class="fas fa-expand"></i></button>'
        + '<button class="sv-card-btn sv-approve" onclick="svQuickApprove(event,'+row.id+')"><i class="fas fa-check"></i></button>'
        + '<button class="sv-card-btn sv-reject"  onclick="svQuickReject(event,'+row.id+')"><i class="fas fa-times"></i></button>'
        + '</div></div>';
    return el;
}

/* ── VIEWER ── */
function svOpenViewer(id){
    svViewerIdx = svViewData.findIndex(function(r){ return r.id==id; });
    if (svViewerIdx<0) return;
    svShowViewerItem(svViewerIdx);
    document.getElementById('sv-fs-viewer').classList.add('open');
    document.getElementById('svNavPrev').style.display = 'flex';
    document.getElementById('svNavNext').style.display = 'flex';
    svUpdateArrows();
    document.body.style.overflow = 'hidden';
}

function svShowViewerItem(idx){
    var row = svViewData[idx]; if (!row) return;
    svCurrentId = row.id;
    document.getElementById('svFvUser').textContent  = row.username  || '-';
    document.getElementById('svFvApp').textContent   = row.app_name  || '-';
    document.getElementById('svFvPrice').textContent = '₹'+parseFloat(row.price||0).toFixed(2);

    // Desktop = ID, Mobile = Date
    var metaEl = document.getElementById('svFvMeta');
    if (svIsMobile()){
        var ds = row.submitted_time || row.created_at;
        metaEl.textContent = ds ? new Date(ds).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'2-digit'}) : '-';
    } else {
        metaEl.textContent = '#'+row.id;
    }

    var img = document.getElementById('svFvImage');
    img.src = row.screenshot_path || '';
    img.style.transform = 'scale(1)';
    img.dataset.zoomed = '0';
    img.style.cursor = 'zoom-in';

    var comment = row.comment_text || 'No comment assigned';
    document.getElementById('svFvComment').textContent       = comment;
    document.getElementById('svFvCommentMobile').textContent = comment;
    document.getElementById('svFvTime').textContent  = row.submitted_time ? new Date(row.submitted_time).toLocaleString() : '-';
    document.getElementById('svFvLink').textContent  = row.app_link || '-';
    document.getElementById('svFvPhone').textContent = row.phone    || '-';
}

function svToggleZoom(img){
    var z = img.dataset.zoomed==='1';
    img.style.transform = z ? 'scale(1)' : 'scale(1.8)';
    img.style.cursor    = z ? 'zoom-in' : 'zoom-out';
    img.dataset.zoomed  = z ? '0' : '1';
}

function svCopyComment(btnId){
    var text = document.getElementById('svFvComment')?.textContent || '';
    if (!text || text==='-'){ if(typeof showToast!=='undefined') showToast('No comment to copy', 1500, 'warning'); return; }
    navigator.clipboard.writeText(text).then(function(){
        var btn = document.getElementById(btnId); if (!btn) return;
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.add('sv-copied');
        setTimeout(function(){ btn.innerHTML = orig; btn.classList.remove('sv-copied'); }, 2000);
        if (typeof showToast!=='undefined') showToast('Comment copied!', 1500, 'success');
    }).catch(function(){
        // fallback
        var ta = document.createElement('textarea');
        ta.value = text; ta.style.position='fixed'; ta.style.opacity='0';
        document.body.appendChild(ta); ta.select(); document.execCommand('copy');
        document.body.removeChild(ta);
        if (typeof showToast!=='undefined') showToast('Comment copied!', 1500, 'success');
    });
}

function svCloseViewer(){
    document.getElementById('sv-fs-viewer').classList.remove('open');
    document.getElementById('svNavPrev').style.display = 'none';
    document.getElementById('svNavNext').style.display = 'none';
    document.body.style.overflow = '';
}

function svNavigate(dir){
    var ni = svViewerIdx + dir;
    if (ni<0||ni>=svViewData.length) return;
    svViewerIdx = ni;
    svShowViewerItem(svViewerIdx);
    svUpdateArrows();
}

function svUpdateArrows(){
    document.getElementById('svNavPrev').disabled = svViewerIdx<=0;
    document.getElementById('svNavNext').disabled = svViewerIdx>=svViewData.length-1;
}

document.addEventListener('keydown', function(e){
    if (!document.getElementById('sv-fs-viewer').classList.contains('open')) return;
    if (e.key==='ArrowLeft')  svNavigate(-1);
    if (e.key==='ArrowRight') svNavigate(1);
    if (e.key==='Escape')     svCloseViewer();
});

/* ── APPROVE ── */
function svApproveFromViewer(){ svDoApprove(svCurrentId); }
function svQuickApprove(e,id){ e.stopPropagation(); svDoApprove(id); }
function svDoApprove(id){
    var fd = new FormData();
    fd.append('action','approve_submission');
    fd.append('assignment_id',id);
    fetch('ajax/admin.php',{method:'POST',body:fd})
        .then(function(r){return r.json();})
        .then(function(d){
            if (d.success){ if(typeof showToast!=='undefined') showToast('✅ Approved!',2000,'success'); svRemoveFromData(id); svCloseViewer(); }
            else { if(typeof showToast!=='undefined') showToast('Error: '+(d.message||'Failed'),3000,'error'); }
        }).catch(function(){ if(typeof showToast!=='undefined') showToast('Network error',3000,'error'); });
}

/* ── REJECT ── */
function svOpenRejectModal(){
    document.getElementById('svRmSelect').value='';
    document.getElementById('svRmTextarea').style.display='none';
    document.getElementById('svRmTextarea').value='';
    document.getElementById('svRmPreview').style.display='none';
    svRejectReason='';
    document.getElementById('sv-overlay').classList.add('open');
}
function svCloseRejectModal(){ document.getElementById('sv-overlay').classList.remove('open'); }
function svQuickReject(e,id){ e.stopPropagation(); svCurrentId=id; svOpenRejectModal(); }
function svHandleRejectSelect(val){
    var ta=document.getElementById('svRmTextarea'), pre=document.getElementById('svRmPreview');
    if (val==='other'){ ta.style.display='block'; ta.focus(); pre.style.display='none'; svRejectReason=''; }
    else if (!val){ ta.style.display='none'; pre.style.display='none'; svRejectReason=''; }
    else { ta.style.display='none'; pre.style.display='block'; pre.textContent=val; svRejectReason=val; }
}
function svSubmitReject(){
    var reason = svRejectReason || document.getElementById('svRmTextarea').value.trim();
    if (!reason){ if(typeof showToast!=='undefined') showToast('Select or enter reason',2000,'warning'); return; }
    var fd = new FormData();
    fd.append('action','reject_submission');
    fd.append('assignment_id',svCurrentId);
    fd.append('reason',reason);
    fetch('ajax/admin.php',{method:'POST',body:fd})
        .then(function(r){return r.json();})
        .then(function(d){
            if (d.success){ if(typeof showToast!=='undefined') showToast('❌ Rejected',2000,'success'); svRemoveFromData(svCurrentId); svCloseRejectModal(); svCloseViewer(); }
            else { if(typeof showToast!=='undefined') showToast('Error: '+(d.message||'Failed'),3000,'error'); }
        }).catch(function(){ if(typeof showToast!=='undefined') showToast('Network error',3000,'error'); });
}

/* ── DOWNLOAD ── */
function svDownloadImg(){
    var src = document.getElementById('svFvImage').src; if (!src) return;
    fetch(src).then(function(r){return r.blob();}).then(function(blob){
        var a=document.createElement('a');
        a.href=URL.createObjectURL(blob);
        a.download='ss-'+svCurrentId+'-'+Date.now()+'.jpg';
        a.click(); URL.revokeObjectURL(a.href);
    }).catch(function(){ if(typeof showToast!=='undefined') showToast('Download failed',2000,'error'); });
}

/* ── REMOVE FROM DATA ── */
function svRemoveFromData(id){
    svAllData  = svAllData.filter(function(r){return r.id!=id;});
    svViewData = svViewData.filter(function(r){return r.id!=id;});
    document.getElementById('sv-cards-wrap').querySelectorAll('.sv-card').forEach(function(card){
        if (card.innerHTML.indexOf('svQuickApprove(event,'+id+')')>=0 || card.innerHTML.indexOf('svOpenViewer('+id+')')>=0){
            card.remove();
        }
    });
    svRendered = Math.max(0, svRendered-1);
    var cnt = svAllData.filter(svInDateRange).length;
    document.getElementById('svBadge').textContent = cnt+' Pending';
    var bm=document.getElementById('svBadgeMobile'); if(bm) bm.textContent=cnt+' Pending';
    if (svViewerIdx>=svViewData.length) svViewerIdx=svViewData.length-1;
    svUpdateArrows();
    if (svViewData.length===0) svShowEmpty('No more screenshots!');
    var dated=svAllData.filter(svInDateRange);
    svBuildSidebar(dated);
    document.getElementById('svMainSub').textContent=svViewData.length+' screenshot'+(svViewData.length!==1?'s':'');
}

/* ── SIDEBAR MOBILE ── */
function svToggleSidebar(){
    var sb=document.getElementById('sv-sidebar'), bd=document.getElementById('sv-sb-backdrop');
    sb.classList.toggle('open'); bd.classList.toggle('open');
}
function svCloseSidebar(){
    document.getElementById('sv-sidebar').classList.remove('open');
    document.getElementById('sv-sb-backdrop').classList.remove('open');
}

/* ── SKELETON / EMPTY ── */
function svShowSkeletons(){
    document.getElementById('sv-cards-wrap').innerHTML = Array(8).fill(0).map(function(){
        return '<div class="sv-skeleton-card"><div class="sv-skel-img"></div><div class="sv-skel-line"></div><div class="sv-skel-line sv-short"></div></div>';
    }).join('');
}
function svShowEmpty(msg){
    document.getElementById('sv-cards-wrap').innerHTML='<div class="sv-empty"><i class="fas fa-inbox"></i><p>'+msg+'</p></div>';
}

/* ── HELPERS ── */
function svGetLogoUrl(appLink){
    if (!appLink) return '';
    try {
        var l=appLink.toLowerCase();
        if (l.includes('youtube.com')||l.includes('youtu.be')) return 'https://www.google.com/s2/favicons?sz=128&domain=youtube.com';
        if (l.includes('maps.google')||l.includes('google.com/maps')||l.includes('maps.app')) return 'https://www.google.com/s2/favicons?sz=128&domain=maps.google.com';
        if (l.includes('instagram.com')) return 'https://www.google.com/s2/favicons?sz=128&domain=instagram.com';
        if (l.includes('facebook.com'))  return 'https://www.google.com/s2/favicons?sz=128&domain=facebook.com';
        if (l.includes('twitter.com')||l.includes('x.com')) return 'https://www.google.com/s2/favicons?sz=128&domain=x.com';
        if (l.includes('play.google.com')){
            var m=appLink.match(/[?&]id=([a-zA-Z0-9._]+)/);
            var domain=m?m[1].split('.').slice(-2).join('.'):'play.google.com';
            return 'https://www.google.com/s2/favicons?sz=128&domain='+domain;
        }
        var u=new URL(appLink.startsWith('http')?appLink:'https://'+appLink);
        return 'https://www.google.com/s2/favicons?sz=128&domain='+u.hostname.replace('www.','');
    } catch(e){ return ''; }
}
function svStrHue(str){ var h=0; for(var i=0;i<str.length;i++) h=(h*31+str.charCodeAt(i))%360; return h; }
function svEsc(s){ var d=document.createElement('div'); d.textContent=String(s||''); return d.innerHTML; }
function svEscJ(s){ return String(s||'').replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }
</script>