<?php
session_start();
require __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Travelo · Admin</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.10/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="./assets/css/dashboard.css" />

  <style>
    :root {
      --tbl-bg: #ffffff;
      --tbl-head-bg: #f6f7fb;
      --tbl-head-text: #12131a;
      --tbl-row: #ffffff;
      --tbl-row-alt: #fafbff;
      --tbl-row-hover: #eef0f7;
      --tbl-border: #e5e7f2;

      --p-accent: #7c3aed;
      --p-accent2:#6c63ff;
      --p-ink: #0f172a;
      --p-muted:#64748b;
    }
    html.dark {
      --tbl-bg: #0f1222;
      --tbl-head-bg: #141832;
      --tbl-head-text: #e9ecff;
      --tbl-row: #0f1222;
      --tbl-row-alt: #0b0e1a;
      --tbl-row-hover: #1a1e3a;
      --tbl-border: #242949;

      --p-ink: #e9ecff;
      --p-muted: rgba(233,236,255,.72);
    }

    .table { background: var(--tbl-bg); color: inherit; }
    .table thead th {
      background: var(--tbl-head-bg) !important;
      color: var(--tbl-head-text) !important;
      border-bottom: 1px solid var(--tbl-border) !important;
    }
    .table tbody tr { background: var(--tbl-row); }
    .table tbody tr:nth-child(even) { background: var(--tbl-row-alt); }
    .table tbody tr:hover { background: var(--tbl-row-hover) !important; }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
      border: 1px solid var(--tbl-border) !important;
      background: transparent !important;
      color: inherit !important;
      border-radius: 8px;
      margin: 0 .125rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
      background: var(--tbl-head-bg) !important;
      color: var(--tbl-head-text) !important;
    }
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
      background: var(--tbl-row);
      color: inherit;
      border: 1px solid var(--tbl-border);
      border-radius: 8px;
    }

    .thumb { width: 56px; height: 56px; object-fit: cover; border-radius: 10px; }
    .avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
    .status { padding: .2rem .5rem; border-radius: 999px; font-size: .75rem; }
    .status.success { background: #16a34a24; color: #16a34a; }
    .status.pending { background: #f59e0b24; color: #f59e0b; }
    .status.canceled { background: #ef444424; color: #ef4444; }

    .topbar { background: var(--tbl-head-bg); }
    #flightsMap { height: 360px; border-radius: 12px; overflow: hidden; }
    .page-title { font-weight: 700; font-size: 1.1rem; }
    .btn-action { padding: .35rem .5rem; }

    /* ==============================
       Prettier Admin Letter Badge
       ============================== */
    .admin-badge{
      width: 46px;
      height: 46px;
      border: 0;
      padding: 0;
      border-radius: 999px;
      position: relative;
      display: grid;
      place-items: center;
      cursor: pointer;

      background:
        radial-gradient(circle at 30% 25%, rgba(255,255,255,.55), transparent 48%),
        linear-gradient(135deg, #7c3aed 0%, #6c63ff 45%, #22c55e 130%);

      box-shadow:
        0 16px 34px rgba(124,58,237,.20),
        0 0 0 1px rgba(124,58,237,.18) inset;

      transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
    }
    .admin-badge:hover{
      transform: translateY(-1px);
      filter: brightness(1.03);
      box-shadow:
        0 22px 48px rgba(124,58,237,.24),
        0 0 0 1px rgba(124,58,237,.22) inset;
    }
    .admin-badge:focus{
      outline: none;
      box-shadow:
        0 0 0 4px rgba(124,58,237,.18),
        0 22px 48px rgba(124,58,237,.24);
    }
    .admin-badge.dropdown-toggle::after{ display:none; }

    .admin-badge__ring{
      position: absolute;
      inset: -2px;
      border-radius: 999px;
      background:
        conic-gradient(from 180deg, rgba(124,58,237,.55), rgba(108,99,255,.55), rgba(124,58,237,.55));
      filter: blur(.2px);
      opacity: .65;
      z-index: 0;
    }
    .admin-badge__ring::after{
      content:"";
      position:absolute;
      inset: 2px;
      border-radius: 999px;
      background: rgba(255,255,255,.12);
      box-shadow: 0 0 0 1px rgba(255,255,255,.12) inset;
    }
    .admin-badge__letter{
      position: relative;
      z-index: 1;
      width: 40px;
      height: 40px;
      border-radius: 999px;
      display: grid;
      place-items: center;
      color: #fff;
      font-weight: 900;
      font-size: 15px;
      letter-spacing: -0.03em;
      background:
        radial-gradient(circle at 35% 30%, rgba(255,255,255,.24), transparent 55%),
        rgba(15,23,42,.18);
      box-shadow:
        0 10px 20px rgba(15,23,42,.18),
        0 0 0 1px rgba(255,255,255,.16) inset;
    }

    .admin-menu{
      border-radius: 16px;
      border: 1px solid var(--tbl-border);
      box-shadow: 0 18px 45px rgba(15,23,42,.18);
      padding: 8px;
      min-width: 210px;
    }
    .admin-menu .dropdown-item{
      border-radius: 12px;
      padding: 10px 12px;
    }
    .admin-menu .dropdown-item:hover{
      background: rgba(124,58,237,.10);
      color: #7c3aed;
    }

    .topbar .btn-outline-secondary{
      border-radius: 14px;
      transition: background .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }
    .topbar .btn-outline-secondary:hover{
      background: rgba(124,58,237,.10) !important;
      border-color: rgba(124,58,237,.35) !important;
      color: #7c3aed !important;
      transform: translateY(-1px);
    }
    .topbar .btn-outline-secondary:focus{
      box-shadow: 0 0 0 4px rgba(124,58,237,.16) !important;
    }
    .topbar .form-control:focus{
      border-color: rgba(124,58,237,.35) !important;
      box-shadow: 0 0 0 4px rgba(124,58,237,.14) !important;
    }
    .topbar .input-group-text{
      border-radius: 14px 0 0 14px;
    }
    .topbar .form-control{
      border-radius: 0 14px 14px 0;
    }

    /* =========================
       Ask AI Floating Button + Bubble
       ========================= */
    .ask-ai-btn{
      position: fixed;
      left: 22px;
      bottom: 40px;
      width: 60px;
      height: 60px;
      border: 0;
      border-radius: 999px;
      cursor: pointer;
      z-index: 99999;

      display: flex;
      align-items: center;
      justify-content: center;

      background: radial-gradient(circle at 30% 25%, rgba(255,255,255,.35), transparent 40%),
                  linear-gradient(135deg, #7c3aed, #6c63ff);
      box-shadow: 0 16px 40px rgba(124,58,237,.35);
      transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;

      overflow: visible;
      padding: 0;
    }
    .ask-ai-btn i{
      font-size: 24px;
      color: #fff;
      line-height: 1;
      filter: drop-shadow(0 6px 10px rgba(0,0,0,.18));
    }
    .ask-ai-btn:hover{
      transform: translateY(-2px);
      box-shadow: 0 22px 55px rgba(124,58,237,.45);
      filter: brightness(1.03);
    }
    .ask-ai-btn::after{
      content:"";
      position:absolute;
      inset:-8px;
      border-radius:999px;
      background: radial-gradient(circle, rgba(124,58,237,.35), transparent 60%);
      opacity: 0;
      transform: scale(.9);
      transition: .25s ease;
      pointer-events: none;
    }
    .ask-ai-btn:hover::after{
      opacity: 1;
      transform: scale(1);
    }
    .ask-ai-bubble{
      position: absolute;
      left: calc(100% + 12px);
      top: 50%;
      transform: translateY(-50%) translateX(-6px);

      background: rgba(124, 58, 237, 0.30);
      color: #0f172a;
      padding: 10px 12px;
      border-radius: 999px;
      font-weight: 800;
      font-size: 13px;
      white-space: nowrap;

      box-shadow: 0 14px 30px rgba(15,23,42,.18);
      opacity: 0;
      pointer-events: none;
      transition: .2s ease;
    }
    .ask-ai-bubble::before{
      content:"";
      position:absolute;
      left:-6px;
      top:50%;
      width:10px;
      height:10px;
      background: rgba(124, 58, 237, 0.30);
      transform: translateY(-50%) rotate(45deg);
      border-radius: 2px;
    }
    .ask-ai-btn:hover .ask-ai-bubble,
    .ask-ai-btn:focus-visible .ask-ai-bubble{
      opacity: 1;
      transform: translateY(-50%) translateX(0);
    }

    /* =====================================
       POSTS (Cards) - Clean & Clear (scoped)
       ===================================== */
    #posts .card{
      border-radius: 18px;
      border: 1px solid var(--tbl-border);
      box-shadow: 0 14px 34px rgba(15,23,42,.08);
    }

    #posts .posts-toolbar{ margin-bottom: 12px; }

    #posts .form-select,
    #posts .form-control,
    #posts .input-group-text{
      border-radius: 14px !important;
      border: 1px solid var(--tbl-border) !important;
    }
    #posts .input-group-text{
      background: transparent !important;
      color: #94a3b8;
    }
    #posts .form-control:focus,
    #posts .form-select:focus{
      border-color: rgba(124,58,237,.28) !important;
      box-shadow: 0 0 0 4px rgba(124,58,237,.10) !important;
    }

    #posts .posts-grid{
      display:grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
    }
    @media (max-width: 1100px){ #posts .posts-grid{ grid-template-columns: 1fr; } }

    #posts .post-card{
      position: relative;
      background: var(--tbl-bg);
      border: 1px solid var(--tbl-border);
      border-radius: 18px;
      padding: 16px 16px 14px;
      box-shadow: 0 10px 26px rgba(15,23,42,.06);
      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
      overflow: hidden;
    }
    #posts .post-card::before{
      content:"";
      position:absolute;
      left:0; top:14px; bottom:14px;
      width:3px;
      border-radius: 999px;
      background: linear-gradient(180deg, rgba(124,58,237,.55), rgba(108,99,255,.25));
      opacity:.55;
    }
    #posts .post-card:hover{
      transform: translateY(-2px);
      border-color: rgba(124,58,237,.18);
      box-shadow: 0 18px 44px rgba(15,23,42,.10);
    }

    #posts .post-top{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 12px;
    }

    #posts .post-user{
      display:flex;
      align-items:center;
      gap: 10px;
      min-width: 0;
    }

    #posts .post-avatar{
      width: 40px;
      height: 40px;
      border-radius: 999px;
      display:grid;
      place-items:center;
      color:#fff;
      font-weight: 800;
      font-size: 14px;
      background: linear-gradient(135deg, rgba(124,58,237,.95), rgba(108,99,255,.95));
      box-shadow: 0 10px 18px rgba(124,58,237,.14);
      flex: 0 0 auto;
    }

    #posts .post-name{
      margin:0;
      font-size: 14.5px;
      font-weight: 700;
      color: var(--p-ink);
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
    }

    #posts .post-email{
      margin: 2px 0 0;
      font-size: 12.5px;
      font-weight: 500;
      color: #94a3b8;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
    }

    #posts .post-right{
      display:flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 8px;
      flex: 0 0 auto;
    }

    #posts .post-status{
      font-size: 12px;
      font-weight: 700;
      padding: .26rem .60rem;
      border-radius: 999px;
      border: 1px solid rgba(124,58,237,.22);
      background: rgba(124,58,237,.08);
      color: rgba(124,58,237,.95);
      white-space: nowrap;
    }

    #posts .post-date{
      font-size: 12.5px;
      font-weight: 500;
      color: var(--p-muted);
      display:flex;
      align-items:center;
      gap: 6px;
    }

    #posts .post-meta{
      margin-top: 10px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      color: var(--p-muted);
    }

    #posts .post-stars{
      letter-spacing: 1px;
      font-size: 14px;
      color: rgba(124,58,237,.95);
      opacity: .95;
    }

    #posts .post-title{
      margin: 10px 0 0;
      font-size: 14px;
      font-weight: 700;
      color: var(--tbl-head-text);
    }

    #posts .post-msg{
      margin: 8px 0 0;
      font-size: 13.8px;
      font-weight: 500;
      color: var(--p-muted);
      line-height: 1.75;

      display:-webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow:hidden;
      min-height: calc(1.75em * 3);
    }

    #posts .post-foot{
      margin-top: 14px;
      padding-top: 12px;
      border-top: 1px solid var(--tbl-border);
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      flex-wrap: wrap;
    }

    #posts .post-booking{
      display:flex;
      align-items:center;
      gap: 8px;
      font-size: 13px;
      font-weight: 500;
      color: var(--p-muted);
    }

    #posts .post-booking .badge{
      border-radius: 999px;
      padding: .28rem .55rem;
      font-weight: 700;
    }

    #posts .post-actions{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    #posts .post-actions .btn{
      border-radius: 999px !important;
      padding: .40rem .82rem !important;
      font-weight: 700 !important;
      box-shadow: none !important;
      transition: transform .16s ease, background .16s ease, border-color .16s ease, filter .16s ease;
    }
    #posts .post-actions .btn:hover{ transform: translateY(-1px); }

    #posts .btn-success,
    #posts .btn-approve{
      border: 0 !important;
      color: #fff !important;
      background: linear-gradient(135deg, rgba(124,58,237,1), rgba(108,99,255,1)) !important;
      box-shadow: 0 14px 26px rgba(124,58,237,.16) !important;
    }
    #posts .btn-success:hover,
    #posts .btn-approve:hover{
      filter: brightness(1.03);
      box-shadow: 0 18px 34px rgba(124,58,237,.20) !important;
    }

    #posts .btn-danger,
    #posts .btn-reject{
      background: transparent !important;
      color: rgba(124,58,237,.98) !important;
      border: 1px solid rgba(124,58,237,.38) !important;
    }
    #posts .btn-danger:hover,
    #posts .btn-reject:hover{
      background: rgba(124,58,237,.10) !important;
      border-color: rgba(124,58,237,.55) !important;
    }

    #posts .btn-outline-secondary,
    #posts .btn-soft{
      background: rgba(108,99,255,.10) !important;
      border: 1px solid rgba(108,99,255,.22) !important;
      color: rgba(108,99,255,.98) !important;
    }
    #posts .btn-outline-secondary:hover,
    #posts .btn-soft:hover{
      background: rgba(108,99,255,.14) !important;
      border-color: rgba(108,99,255,.40) !important;
    }

    #posts .btn-purple{
      border: 1px solid rgba(124,58,237,.28) !important;
      background: rgba(124,58,237,.10) !important;
      color: rgba(124,58,237,.98) !important;
      border-radius: 999px !important;
      font-weight: 700 !important;
    }
    #posts .btn-purple:hover{
      background: rgba(124,58,237,.14) !important;
      border-color: rgba(124,58,237,.45) !important;
    }

    @media (max-width: 720px){
      #posts .post-foot{ flex-direction: column; align-items: stretch; }
      #posts .post-actions .btn{ flex: 1; justify-content:center; }
    }

    html.dark #posts .post-card{
      box-shadow: 0 14px 40px rgba(0,0,0,.35);
    }
    html.dark #posts .post-card:hover{
      border-color: rgba(124,58,237,.22);
    }

    /* =========================
       CALENDAR (Travelo style)
       ========================= */
    #calendar{
      --bs-primary: #7c3aed;
      --bs-primary-rgb: 124, 58, 237;
    }

    #calendar .travelo-cal{
      display:grid;
      grid-template-columns: 320px 1fr;
      gap: 14px;
      align-items: start;
    }
    @media (max-width: 1100px){
      #calendar .travelo-cal{ grid-template-columns: 1fr; }
    }

    #calendar .travelo-cal__side{
      position: sticky;
      top: 12px;
    }
    @media (max-width: 1100px){
      #calendar .travelo-cal__side{ position: static; }
    }

    #calendar .cal-card{
      background: var(--tbl-bg);
      border: 1px solid var(--tbl-border);
      border-radius: 18px;
      padding: 12px;
      box-shadow: 0 14px 34px rgba(15,23,42,.08);
    }
    #calendar .cal-card__title{
      font-weight: 900;
      color: var(--p-ink);
      display:flex;
      align-items:center;
      gap:6px;
    }

    #calendar .cal-cats{
      display:flex;
      flex-direction: column;
      gap: 10px;
    }
    #calendar .cal-cat{
      display:flex;
      align-items:center;
      gap: 10px;
      cursor:pointer;
      user-select:none;
    }
    #calendar .cal-cat .form-check-input{ margin-top:0; }
    #calendar .cal-cat .dot{
      width: 10px; height: 10px;
      border-radius: 999px;
      box-shadow: 0 0 0 4px rgba(124,58,237,.10);
    }
    #calendar .dot-event{ background:#7c3aed; box-shadow:0 0 0 4px rgba(124,58,237,.12); }
    #calendar .dot-meeting{ background:#22c55e; box-shadow:0 0 0 4px rgba(34,197,94,.12); }
    #calendar .dot-task{ background:#f59e0b; box-shadow:0 0 0 4px rgba(245,158,11,.14); }

    #calendar .cal-upcoming .u-item{
      border: 1px solid var(--tbl-border);
      border-radius: 14px;
      padding: 10px;
      background: var(--tbl-head-bg);
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 10px;
      margin-bottom: 10px;
      cursor:pointer;
      transition: transform .15s ease, box-shadow .15s ease;
    }
    #calendar .cal-upcoming .u-item:hover{
      transform: translateY(-1px);
      box-shadow: 0 12px 22px rgba(15,23,42,.10);
    }
    #calendar .cal-upcoming .u-title{ font-weight: 900; color: var(--p-ink); }
    #calendar .cal-upcoming .u-meta{ font-size: 12px; color: var(--p-muted); }
    #calendar .cal-upcoming .u-dot{
      width: 10px; height: 10px; border-radius:999px; margin-top: 6px;
    }

    #calendar .travelo-cal__main{
      background: var(--tbl-bg);
      border: 1px solid var(--tbl-border);
      border-radius: 18px;
      padding: 12px;
      box-shadow: 0 14px 34px rgba(15,23,42,.08);
    }

    #calendar .fc{
      --fc-border-color: var(--tbl-border);
      --fc-page-bg-color: transparent;
      --fc-neutral-bg-color: transparent;
      --fc-today-bg-color: rgba(124,58,237,.10);
      --fc-event-border-color: transparent;
      --fc-button-border-color: var(--tbl-border);
      --fc-button-bg-color: rgba(124,58,237,.10);
      --fc-button-text-color: rgba(124,58,237,.98);
      --fc-button-hover-bg-color: rgba(124,58,237,.14);
      --fc-button-active-bg-color: rgba(124,58,237,.18);
    }
    #calendar .fc .fc-toolbar-title{
      font-weight: 1000;
      color: var(--p-ink);
    }
    #calendar .fc .fc-button{
      border-radius: 14px !important;
      font-weight: 800;
    }
    #calendar .fc .fc-button-primary{
      border: 1px solid rgba(124,58,237,.22) !important;
    }
    #calendar .fc a{ text-decoration:none; }

    .btn-travelo{
      border: 0;
      color:#fff;
      background: linear-gradient(135deg, #7c3aed, #6c63ff);
      border-radius: 12px;
      font-weight: 800;
    }
    .btn-travelo:hover{ filter: brightness(1.03); }
 /* =====================================
   BLOGS (Admin Cards) - 4 per row
   ===================================== */
#blogs .blogs-admin-list{
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

@media (max-width: 1200px){
  #blogs .blogs-admin-list{ grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 900px){
  #blogs .blogs-admin-list{ grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 560px){
  #blogs .blogs-admin-list{ grid-template-columns: 1fr; }
}

#blogs .blog-card{
  position: relative;
  border-radius: 18px;
  border: 1px solid var(--tbl-border);
  background: var(--tbl-bg);
  overflow: hidden;

  box-shadow: 0 14px 34px rgba(15,23,42,.08);
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  animation: blogPop .35s ease both;

  min-height: 320px;        /* ✅ طول ثابت مرتب */
  display: flex;
  flex-direction: column;
}

@keyframes blogPop{
  from{ opacity:0; transform: translateY(10px) scale(.98); }
  to{ opacity:1; transform: translateY(0) scale(1); }
}

#blogs .blog-card:hover{
  transform: translateY(-3px);
  border-color: rgba(124,58,237,.22);
  box-shadow: 0 20px 55px rgba(15,23,42,.12);
}

#blogs .blog-cover-wrap{
  position: relative;
  height: 150px; /* ✅ ارتفاع الصورة */
  background: var(--tbl-head-bg);
  overflow: hidden;
}

#blogs .blog-cover{
  width: 100%;
  height: 100%;
  object-fit: cover;
  transform: scale(1.02);
  transition: transform .35s ease;
}

#blogs .blog-card:hover .blog-cover{
  transform: scale(1.06);
}

/* Gradient overlay */
#blogs .blog-cover-wrap::after{
  content:"";
  position:absolute;
  inset:0;
  background: linear-gradient(180deg, rgba(0,0,0,.00), rgba(0,0,0,.30));
  pointer-events:none;
}

#blogs .blog-badges{
  position:absolute;
  left: 12px;
  bottom: 12px;
  display:flex;
  gap: 8px;
  flex-wrap: wrap;
  z-index: 2;
}

#blogs .badge-pill{
  display:inline-flex;
  align-items:center;
  gap: 6px;
  padding: .25rem .55rem;
  border-radius: 999px;
  font-weight: 900;
  font-size: 12px;
  color: #fff;
  background: rgba(15,23,42,.55);
  border: 1px solid rgba(255,255,255,.18);
  backdrop-filter: blur(6px);
}

#blogs .badge-status{
  background: rgba(124,58,237,.80);
  border-color: rgba(255,255,255,.20);
}

#blogs .blog-body{
  padding: 12px 12px 10px;
  flex: 1;
  display:flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
}

#blogs .blog-title{
  margin: 0;
  font-weight: 1000;
  font-size: 14.8px;
  color: var(--tbl-head-text);

  display: -webkit-box;
  -webkit-line-clamp: 2;     /* ✅ سطرين */
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: calc(1.35em * 2);
  line-height: 1.35;
}

#blogs .blog-excerpt{
  margin: 0;
  color: var(--p-muted);
  font-weight: 600;
  font-size: 13px;
  line-height: 1.7;

  display: -webkit-box;
  -webkit-line-clamp: 3;   /* ✅ 3 أسطر */
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: calc(1.7em * 3);
}

#blogs .blog-meta{
  margin-top: auto;
  display:flex;
  align-items:center;
  justify-content: space-between;
  gap: 10px;
  padding-top: 10px;
  border-top: 1px solid var(--tbl-border);

  font-size: 12.5px;
  font-weight: 700;
  color: var(--p-muted);
}

#blogs .blog-meta .m{
  display:flex;
  align-items:center;
  gap: 6px;
  white-space: nowrap;
}

#blogs .blog-actions{
  display:flex;
  gap: 8px;
  padding: 10px 12px 12px;
}

#blogs .blog-actions .btn{
  border-radius: 999px !important;
  font-weight: 900 !important;
  padding: .42rem .0 !important;
  flex: 1;
}

#blogs .btn-viewdetails{
  border: 0 !important;
  color: #fff !important;
  background: linear-gradient(135deg, rgba(124,58,237,1), rgba(108,99,255,1)) !important;
  box-shadow: 0 14px 26px rgba(124,58,237,.16) !important;
}
#blogs .btn-viewdetails:hover{ filter: brightness(1.03); }

#blogs .btn-edit{
  background: rgba(108,99,255,.10) !important;
  border: 1px solid rgba(108,99,255,.22) !important;
  color: rgba(108,99,255,.98) !important;
}
#blogs .btn-edit:hover{
  background: rgba(108,99,255,.14) !important;
  border-color: rgba(108,99,255,.40) !important;
}

#blogs .btn-delete{
  background: transparent !important;
  color: #ef4444 !important;
  border: 1px solid rgba(239,68,68,.45) !important;
}
#blogs .btn-delete:hover{
  background: rgba(239,68,68,.10) !important;
  border-color: rgba(239,68,68,.65) !important;
}

/* ✅ خلي مودال التفاصيل يسكرول من جوّا */
#blogDetailsModal .modal-dialog{ max-height: calc(100vh - 2rem); }
#blogDetailsModal .modal-content{ max-height: calc(100vh - 2rem); }
#blogDetailsModal .modal-body{ overflow:auto; max-height: 70vh; }

  </style>
</head>

<body>
<div class="app">
  <!-- SIDEBAR -->
  <aside id="sidebar" class="sidebar">
    <div class="brand">
      <img class="logo" src="./assets/images/logo.svg" alt="Travelo logo" />
      Travelo Admin
    </div>
    <div class="mb-3">
      <a class="btn btn-outline-secondary d-lg-none w-100" href="#" onclick="toggleSidebar(event)">
        <i class="bi bi-layout-sidebar-inset"></i> Menu
      </a>
    </div>

    <nav class="nav flex-column gap-1" id="mainNav">
      <a class="nav-link active" href="#dashboard" data-page="dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
      <a class="nav-link" href="#users" data-page="users"><i class="bi bi-people me-2"></i>Users</a>
      <a class="nav-link" href="#admins" data-page="admins"><i class="bi bi-person-gear me-2"></i>Admins</a>
      <a class="nav-link" href="#destinations" data-page="destinations"><i class="bi bi-geo-alt me-2"></i>Destinations</a>
      <a class="nav-link" href="#flights" data-page="flights"><i class="bi bi-airplane me-2"></i>Flights</a>
      <a class="nav-link" href="#hotels" data-page="hotels"><i class="bi bi-building me-2"></i>Hotels</a>
      <a class="nav-link" href="#packages" data-page="packages"><i class="bi bi-box-seam me-2"></i>Packages</a>
      <a class="nav-link" href="#bookings" data-page="bookings"><i class="bi bi-receipt me-2"></i>Bookings</a>
      <a class="nav-link" href="#payments" data-page="payments"><i class="bi bi-credit-card me-2"></i>Payments</a>

      <a class="nav-link" href="#posts" data-page="posts">
        <i class="bi bi-chat-square-text me-2"></i>Posts
      </a>

      <a class="nav-link" href="#calendar" data-page="calendar">
        <i class="bi bi-calendar-event me-2"></i>Calendar
      </a>
      <a class="nav-link" href="#faqs" data-page="faqs">
  <i class="bi bi-question-circle me-2"></i>FAQs
</a>
<a class="nav-link" href="#blogs" data-page="blogs">
  <i class="bi bi-journal-text me-2"></i>Blogs
</a>


    </nav>

    <hr />
    <div class="sub">© 2025 Travelo</div>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <!-- TOPBAR -->
    <div class="topbar container-fluid rounded-4 p-3 mb-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary d-lg-none" onclick="toggleSidebar(event)">
          <i class="bi bi-list"></i>
        </button>
        <div class="page-title" id="pageTitle">Dashboard</div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <div class="input-group" style="max-width:360px;">
          <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
          <input id="globalSearch" class="form-control border-start-0" placeholder="Search (Ctrl + K)" />
        </div>

        <button id="themeToggle" class="btn btn-outline-secondary" title="Toggle theme">
          <i class="bi bi-moon-stars" id="themeIcon"></i>
        </button>

        <?php
          $adminName = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? $_SESSION['username'] ?? 'Admin';
          $adminName = trim((string)$adminName);
          $adminLetter = strtoupper(mb_substr($adminName !== '' ? $adminName : 'A', 0, 1));
        ?>

        <div class="dropdown">
          <button
            class="admin-badge dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            title="<?= htmlspecialchars($adminName ?: 'Admin') ?>"
          >
            <span class="admin-badge__ring"></span>
            <span class="admin-badge__letter"><?= htmlspecialchars($adminLetter) ?></span>
          </button>

          <ul class="dropdown-menu dropdown-menu-end admin-menu">
            <li>
              <a class="dropdown-item" href="./adminprofile.php">
                <i class="bi bi-person me-2"></i> My profile
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form action="./logout.php" method="post" class="m-0 p-0">
                <button type="submit" class="dropdown-item">
                  <i class="bi bi-box-arrow-right me-2"></i> Log out
                </button>
              </form>
            </li>
          </ul>

        </div>
      </div>
    </div>

    <!-- DASHBOARD -->
    <section id="dashboard" class="section active">
      <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card p-3 h-100">
            <div class="card-title">Total Users</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="metric" id="kpiUsers">--</div>
              <i class="bi bi-people fs-2" style="color:var(--p1)"></i>
            </div>
            <div class="sub">Active users</div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card p-3 h-100">
            <div class="card-title">Total Bookings</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="metric" id="kpiBookings">--</div>
              <i class="bi bi-ticket-perforated fs-2" style="color:var(--pink1)"></i>
            </div>
            <div class="sub">All types</div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card p-3 h-100">
            <div class="card-title">Total Revenue</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="metric" id="kpiRevenue">--</div>
              <i class="bi bi-currency-dollar fs-2" style="color:var(--p3)"></i>
            </div>
            <div class="sub">Payments captured</div>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card p-3 h-100">
            <div class="card-title">On-Time Flights</div>
            <div class="d-flex align-items-center justify-content-between">
              <div class="metric" id="kpiOTP">--</div>
              <i class="bi bi-clock-history fs-2" style="color:var(--p2)"></i>
            </div>
            <div class="sub">From flights data</div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-12 col-lg-8">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Revenue vs Bookings (Sample)</div>
            <div class="chart-wrap"><canvas id="dashLine"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Booking Status (Sample)</div>
            <div class="chart-wrap"><canvas id="dashDonut"></canvas></div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Revenue (Last 30 Days – Sample)</div>
            <div class="chart-wrap"><canvas id="dashArea"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Daily Bookings (Sample)</div>
            <div class="chart-wrap"><canvas id="dashBarMini"></canvas></div>
          </div>
        </div>
      </div>

      <div class="card p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Unified Activity (Bookings & Payments)</div>
        </div>
        <div class="table-responsive">
          <table id="dashTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Type</th>
              <th>Ref</th>
              <th>User</th>
              <th>From</th>
              <th>To</th>
              <th>Date</th>
              <th>Amount</th>
              <th>Status</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="card p-3 mt-3">
        <div class="fw-bold mb-2">Live Routes Map</div>
        <div id="flightsMap"></div>
      </div>
    </section>

    <!-- USERS -->
    <section id="users" class="section">
      <div class="card p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Users</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addUser" data-entity="users" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="usersTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Name</th>
              <th>Username</th>
              <th>Email</th>
              <th>BIRTH DATE</th>
              <th>Active</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ADMINS -->
    <section id="admins" class="section">
      <div class="card p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Admins</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addAdmin" data-entity="admins" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="adminsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Admin</th>
              <th>Email</th>
              <th>Is Super</th>
              <th>Active</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- DESTINATIONS -->
    <section id="destinations" class="section">
      <div class="card p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Destinations</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addDestination" data-entity="destinations" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="destinationsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Image</th>
              <th>Name</th>
              <th>City</th>
              <th>Country</th>
              <th>Category</th>
              <th>Base Price</th>
              <th>Currency</th>
              <th>Top</th>
              <th>Active</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- FLIGHTS -->
    <section id="flights" class="section">
      <div class="card p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Flights</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addFlight" data-entity="flights" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="flightsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Airline</th>
              <th>Flight #</th>
              <th>Destination</th>
              <th>Route</th>
              <th>Trip Type</th>
              <th>Depart Date</th>
              <th>Return Date</th>
              <th>Depart Time</th>
              <th>Arrive Time</th>
              <th>Duration (h)</th>
              <th>Stops</th>
              <th>Price</th>
              <th>Currency</th>
              <th>Active</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">On-Time / Delay / Cancel (Sample)</div>
            <div class="chart-wrap"><canvas id="flightsDonut"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Airline OTP (%) (Sample)</div>
            <div class="chart-wrap"><canvas id="flightsBar"></canvas></div>
          </div>
        </div>
      </div>
    </section>

    <!-- HOTELS -->
    <section id="hotels" class="section">
      <div class="card p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Hotels</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addHotel" data-entity="hotels" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="hotelsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Image</th>
              <th>Name</th>
              <th>Destination</th>
              <th>Location</th>
              <th>Rating</th>
              <th>Reviews</th>
              <th>Price/Night</th>
              <th>Currency</th>
              <th>Discount %</th>
              <th>Active</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Occupancy Rate (Sample)</div>
            <div class="chart-wrap"><canvas id="hotelsLine"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Star Rating Mix (Sample)</div>
            <div class="chart-wrap"><canvas id="hotelsPie"></canvas></div>
          </div>
        </div>
      </div>
    </section>

    <!-- PACKAGES -->
    <section id="packages" class="section">
      <div class="card p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Packages</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addPackage" data-entity="packages" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="packagesTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Image</th>
              <th>Title</th>
              <th>Destination</th>
              <th>Hotel</th>
              <th>Flight</th>
              <th>From City</th>
              <th>Location</th>
              <th>Duration (Days)</th>
              <th>Price (USD)</th>
              <th>Rating</th>
              <th>Category</th>
              <th>Featured</th>
              <th>Active</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- BOOKINGS -->
    <section id="bookings" class="section">
      <div class="card p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Bookings</div>
          <div class="toolbar d-flex">
            <button class="btn btn-sm btn-primary" id="addBooking" data-entity="bookings" data-action="add">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table id="bookingsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>Booking Code</th>
              <th>User</th>
              <th>Type</th>
              <th>Package</th>
              <th>Start</th>
              <th>End</th>
              <th>Total</th>
              <th>Currency</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Bookings by Type (Sample)</div>
            <div class="chart-wrap"><canvas id="bookingsTypeChart"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Daily Bookings (Sample)</div>
            <div class="chart-wrap"><canvas id="bookingsDailyChart"></canvas></div>
          </div>
        </div>
      </div>
    </section>

    <!-- PAYMENTS -->
    <section id="payments" class="section">
      <div class="card p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Payments</div>
        </div>
        <div class="table-responsive">
          <table id="paymentsTable" class="table table-hover align-middle datatable" style="width:100%">
            <thead>
            <tr>
              <th>ID</th>
              <th>Booking</th>
              <th>User</th>
              <th>Method</th>
              <th>Amount</th>
              <th>Currency</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Methods Split</div>
            <div class="chart-wrap"><canvas id="payDoughnut"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card p-3 h-100">
            <div class="fw-bold mb-2">Daily Payments</div>
            <div class="chart-wrap"><canvas id="payLine"></canvas></div>
          </div>
        </div>
      </div>
    </section>

    <!-- ✅ POSTS (NEW) -->
    <section id="posts" class="section">
      <div class="card p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-bold">Posts (Testimonials)</div>
          <div class="small text-muted">Approve / Reject user posts</div>
        </div>

        <div class="posts-toolbar">
          <div class="posts-left">
            <select class="form-select form-select-sm" id="postsStatusFilter" style="max-width:180px;">
              <option value="">All</option>
              <option value="pending" selected>Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>

            <div class="input-group input-group-sm" style="max-width:360px;">
              <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
              <input class="form-control" id="postsSearch" placeholder="Search name / message..." />
            </div>
          </div>

          <button class="btn btn-sm btn-purple" id="refreshPosts">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
          </button>
        </div>

        <div class="posts-grid" id="postsGrid"></div>
      </div>
    </section>

    <!-- ✅ CALENDAR -->
    <section id="calendar" class="section">
      <div class="card p-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <div class="fw-bold" style="font-size:1.05rem;">Calendar</div>
            <div class="small text-muted">Manage Events / Meetings / Tasks</div>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" id="calTodayBtn">
              <i class="bi bi-calendar2-check me-1"></i>Today
            </button>
            <button class="btn btn-primary btn-sm" id="calAddBtn">
              <i class="bi bi-plus-lg me-1"></i>Add
            </button>
          </div>
        </div>

        <div class="travelo-cal mt-3">
          <aside class="travelo-cal__side">
            <div class="cal-card">
              <div class="cal-card__title">
                <i class="bi bi-calendar3 me-2"></i>Quick Date
              </div>
              <div class="d-flex gap-2 mt-2">
                <input type="date" class="form-control form-control-sm" id="calJumpDate">
                <button class="btn btn-outline-secondary btn-sm" id="calJumpBtn">Go</button>
              </div>
            </div>

            <div class="cal-card mt-3">
              <div class="cal-card__title">
                <i class="bi bi-tags me-2"></i>Categories
              </div>

              <div class="cal-cats mt-2">
                <label class="cal-cat">
                  <input class="form-check-input" type="checkbox" value="event" checked id="catEvent">
                  <span class="dot dot-event"></span>
                  <span class="name">Events</span>
                </label>

                <label class="cal-cat">
                  <input class="form-check-input" type="checkbox" value="meeting" checked id="catMeeting">
                  <span class="dot dot-meeting"></span>
                  <span class="name">Meetings</span>
                </label>

                <label class="cal-cat">
                  <input class="form-check-input" type="checkbox" value="task" checked id="catTask">
                  <span class="dot dot-task"></span>
                  <span class="name">Tasks</span>
                </label>
              </div>
            </div>

            <div class="cal-card mt-3">
              <div class="cal-card__title">
                <i class="bi bi-clock-history me-2"></i>Upcoming (next 5)
              </div>
              <div class="cal-upcoming mt-2" id="calUpcoming">
                <div class="text-muted small">No upcoming items.</div>
              </div>
            </div>
          </aside>

          <div class="travelo-cal__main">
            <div id="traveloCalendar"></div>
          </div>
        </div>
      </div>
    </section>
 <!-- ✅ FAQS -->
<section id="faqs" class="section">
  <div class="card p-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="fw-bold">FAQs</div>
      <div class="toolbar d-flex gap-2">
        <button class="btn btn-sm btn-primary" id="addFaq">
          <i class="bi bi-plus-lg me-1"></i>Add
        </button>
      </div>
    </div>

    <div class="table-responsive">
     <table id="faqsTable" class="table table-hover align-middle" style="width:100%">
  <thead>
    <tr>
      <th>Category</th>
      <th>Question</th>
      <th>Answer</th>   <!-- ✅ ضيفيه -->
      <th>Popular</th>
      <th>Active</th>
      <th>Sort</th>
      <th>Updated</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

    </div>
  </div>
</section>
<!-- ✅ BLOGS -->
<section id="blogs" class="section">
  <div class="card p-3">
    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
      <div>
        <div class="fw-bold">Blogs</div>
        <div class="small text-muted">Manage published & draft blog posts</div>
      </div>

      <div class="d-flex gap-2 align-items-center">
        <select class="form-select form-select-sm" id="blogsStatusFilter" style="max-width:170px;">
          <option value="">All</option>
          <option value="published" selected>Published</option>
          <option value="draft">Draft</option>
        </select>

        <div class="input-group input-group-sm" style="max-width:360px;">
          <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
          <input class="form-control" id="blogsSearch" placeholder="Search title / category..." />
        </div>

        <button class="btn btn-sm btn-purple" id="refreshBlogs">
          <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>

        <button class="btn btn-sm btn-primary" id="addBlogBtn">
          <i class="bi bi-plus-lg me-1"></i>New Blog
        </button>
      </div>
    </div>

    <div class="blogs-admin-list" id="blogsList"></div>
  </div>
</section>

  </main>
</div>

<!-- FORM MODAL -->
<div class="modal fade" id="formModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formModalTitle">Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="entityForm">
        <div class="modal-body" id="formModalBody"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="formSubmitBtn">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- CONFIRM MODAL -->
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="confirmModalBody">Are you sure?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmYes">Yes</button>
      </div>
    </div>
  </div>
</div>

<!-- DETAILS MODAL -->
<div class="modal fade" id="detailsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsTitle">Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detailsBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ✅ POST DETAILS MODAL -->
<div class="modal fade" id="postDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Post Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="d-flex align-items-start justify-content-between gap-3">
          <div class="d-flex align-items-center gap-2">
            <div class="post-avatar" id="pdAvatar">A</div>
            <div>
              <div class="fw-bold" id="pdName">User</div>
              <div class="text-muted small" id="pdEmail">email</div>
            </div>
          </div>
          <span class="post-status pending" id="pdStatus">Pending</span>
        </div>

        <hr>

        <div class="d-flex flex-wrap gap-3 align-items-center mb-2">
          <div class="post-stars" id="pdStars">★★★★★</div>
          <div class="text-muted small"><i class="bi bi-clock"></i> <span id="pdDate">—</span></div>
          <div class="text-muted small"><i class="bi bi-ticket-perforated"></i> Booking: <span id="pdBooking" class="badge bg-warning-subtle text-warning-emphasis">Unknown</span></div>
        </div>

        <div class="fw-bold mb-1" id="pdTitle">Title</div>
        <div class="p-3 rounded-4" style="background:var(--tbl-head-bg);border:1px solid var(--tbl-border);">
          <div id="pdMessage" style="white-space:pre-wrap;line-height:1.75;">Message</div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-success" id="pdApproveBtn">
          <i class="bi bi-check2 me-1"></i>Approve
        </button>
        <button class="btn btn-danger" id="pdRejectBtn">
          <i class="bi bi-x me-1"></i>Reject
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ✅ REJECT MODAL -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reject Post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="rejectPostId" value="">
        <label class="form-label fw-bold">Reason (optional)</label>
        <textarea class="form-control" id="rejectReason" rows="3" placeholder="Write a short reason..."></textarea>
        <div class="small text-muted mt-2">Later we will save reject_reason in DB.</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger" id="rejectSubmitBtn">
          <i class="bi bi-x me-1"></i>Reject
        </button>
      </div>
    </div>
  </div>
</div>
<!-- ✅ BLOG DETAILS MODAL -->
<div class="modal fade" id="blogDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Blog Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="blogDetailsBody"></div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ✅ CALENDAR MODAL -->
<div class="modal fade" id="calModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-bold mb-0"><i class="bi bi-calendar-plus me-2"></i>Add Item</h5>
          <div class="small text-muted">Event / Meeting / Task</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="calForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Title *</label>
            <input class="form-control" id="calTitle" required placeholder="e.g. Team sync, Deploy, Call...">
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-bold">Type</label>
              <select class="form-select" id="calType">
                <option value="event">📅 Event</option>
                <option value="meeting">🤝 Meeting</option>
                <option value="task">✅ Task</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-bold">Date *</label>
              <input type="date" class="form-control" id="calDate" required>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-bold">Time</label>
              <input type="time" class="form-control" id="calTime" value="09:00">
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-bold">Description</label>
            <textarea class="form-control" id="calDesc" rows="3" placeholder="Notes..."></textarea>
          </div>

          <input type="hidden" id="calEditId" value="">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-outline-secondary" id="calDeleteBtn" style="display:none;">
            <i class="bi bi-trash me-1"></i>Delete
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ✅ Ask AI Floating Button -->
<button id="askAiBtn" class="ask-ai-btn" type="button" aria-label="Ask AI">
  <i class="bi bi-airplane-fill" aria-hidden="true"></i>
  <span class="ask-ai-bubble">Ask AI</span>
</button>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.10/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.10/js/dataTables.bootstrap5.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-ant-path@1.3.0/dist/leaflet-ant-path.min.js"></script>

<script src="./assets/js/dashboard.js"></script>

<!-- POSTS JS -->
<script>
(() => {
  const API = './API/posts.php';

  const statusSel = document.getElementById('postsStatusFilter');
  const searchInp = document.getElementById('postsSearch');
  const refreshBtn = document.getElementById('refreshPosts');
  const grid = document.getElementById('postsGrid');

  const pdModalEl = document.getElementById('postDetailsModal');
  const pdAvatar  = document.getElementById('pdAvatar');
  const pdName    = document.getElementById('pdName');
  const pdEmail   = document.getElementById('pdEmail');
  const pdStatus  = document.getElementById('pdStatus');
  const pdStars   = document.getElementById('pdStars');
  const pdDate    = document.getElementById('pdDate');
  const pdBooking = document.getElementById('pdBooking');
  const pdTitle   = document.getElementById('pdTitle');
  const pdMessage = document.getElementById('pdMessage');
  const pdApprove = document.getElementById('pdApproveBtn');
  const pdReject  = document.getElementById('pdRejectBtn');

  const rejectModalEl = document.getElementById('rejectModal');
  const rejectPostId  = document.getElementById('rejectPostId');
  const rejectReason  = document.getElementById('rejectReason');
  const rejectSubmit  = document.getElementById('rejectSubmitBtn');

  if (!grid || !statusSel || !searchInp || !refreshBtn) return;

  const esc = (s) => String(s ?? '')
    .replaceAll('&','&amp;').replaceAll('<','&lt;')
    .replaceAll('>','&gt;').replaceAll('"','&quot;')
    .replaceAll("'","&#039;");

  const fmtDate = (s) => {
    if (!s) return '—';
    return String(s).replace('T',' ').slice(0, 19);
  };

  const starsText = (n) => {
    n = Math.max(0, Math.min(5, parseInt(n || 0, 10)));
    return '★★★★★'.slice(0, n) + '☆☆☆☆☆'.slice(0, 5 - n);
  };

  const statusClass = (st) => {
    st = String(st || 'pending');
    if (st === 'approved') return 'approved';
    if (st === 'rejected') return 'rejected';
    return 'pending';
  };

  const statusLabel = (st) => {
    st = String(st || 'pending');
    if (st === 'approved') return 'Approved';
    if (st === 'rejected') return 'Rejected';
    return 'Pending';
  };

  function modalInstance(el){
    if (!el || !window.bootstrap) return null;
    return bootstrap.Modal.getOrCreateInstance(el);
  }

  function toast(msg){
    console.log(msg);
  }

  async function getJson(url){
    const res = await fetch(url, { credentials: 'same-origin' });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.success) throw new Error(data.message || 'Request failed');
    return data;
  }

  async function postJson(url, payload){
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify(payload || {}),
      credentials: 'same-origin'
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.success) throw new Error(data.message || 'Request failed');
    return data;
  }

  let rows = [];
  let currentId = null;

  function render(list){
    if (!Array.isArray(list) || !list.length){
      grid.innerHTML = `<div class="text-muted small">No posts found.</div>`;
      return;
    }

    grid.innerHTML = list.map(r => {
      const name = esc(r.name);
      const title = esc(r.title || '');
      const msg = esc(r.message || '');
      const st = String(r.status || 'pending');
      const letter = esc((name.trim()[0] || 'U').toUpperCase());
      const date = fmtDate(r.created_at);
      const stars = starsText(r.rating);

      return `
        <div class="post-card" data-id="${esc(r.id)}">
          <div class="post-top">
            <div class="post-user">
              <div class="post-avatar">${letter}</div>
              <div style="min-width:0">
                <div class="post-name">${name}</div>
                <div class="post-email">${title || '&nbsp;'}</div>
              </div>
            </div>

            <div class="post-right">
              <span class="post-status ${statusClass(st)}">${statusLabel(st)}</span>
              <div class="post-date"><i class="bi bi-clock"></i> ${esc(date)}</div>
            </div>
          </div>

          <div class="post-meta">
            <div class="post-stars">${esc(stars)}</div>
          </div>

          <div class="post-title">${title || '—'}</div>
          <div class="post-msg">${msg}</div>

          <div class="post-foot">
            <div class="post-booking">
              <i class="bi bi-ticket-perforated"></i> Booking:
              <span class="badge bg-warning-subtle text-warning-emphasis">Unknown</span>
            </div>

            <div class="post-actions">
              <button class="btn btn-outline-secondary btn-sm btn-view" type="button">
                <i class="bi bi-eye me-1"></i>View
              </button>

              <button class="btn btn-success btn-sm btn-approve" type="button" ${st==='approved'?'disabled':''}>
                <i class="bi bi-check2 me-1"></i>Approve
              </button>

              <button class="btn btn-danger btn-sm btn-reject" type="button">
                <i class="bi bi-x me-1"></i>Reject
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  async function load(){
    const status = statusSel.value || '';
    const q = (searchInp.value || '').trim();

    const statusParam = status ? status : 'all';
    const url = `${API}?action=list&status=${encodeURIComponent(statusParam)}&q=${encodeURIComponent(q)}&limit=200&_=${Date.now()}`;

    refreshBtn.disabled = true;
    try{
      const data = await getJson(url);
      rows = data.rows || [];
      render(rows);
    }catch(e){
      grid.innerHTML = `<div class="text-danger small">${esc(e.message || 'Failed')}</div>`;
    }finally{
      refreshBtn.disabled = false;
    }
  }

  function openDetails(r){
    if (!r) return;
    currentId = r.id;

    const name = String(r.name || 'User');
    const letter = (name.trim()[0] || 'U').toUpperCase();
    const st = String(r.status || 'pending');

    pdAvatar.textContent = letter;
    pdName.textContent = name;
    pdEmail.textContent = r.title || '';
    pdStatus.textContent = statusLabel(st);
    pdStatus.className = `post-status ${statusClass(st)}`;

    pdStars.textContent = starsText(r.rating);
    pdDate.textContent = fmtDate(r.created_at);
    pdBooking.textContent = 'Unknown';
    pdTitle.textContent = r.title || '—';
    pdMessage.textContent = r.message || '';

    pdApprove.disabled = (st === 'approved');
    modalInstance(pdModalEl)?.show();
  }

  refreshBtn.addEventListener('click', load);
  statusSel.addEventListener('change', load);

  let t = null;
  searchInp.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(load, 300);
  });

  grid.addEventListener('click', async (e) => {
    const card = e.target.closest('.post-card');
    if (!card) return;

    const id = parseInt(card.getAttribute('data-id') || '0', 10);
    const r = rows.find(x => parseInt(x.id,10) === id);
    if (!r) return;

    if (e.target.closest('.btn-view')) {
      openDetails(r);
      return;
    }

    if (e.target.closest('.btn-approve')) {
      try{
        await postJson(`${API}?action=approve`, { id });
        toast('Approved');
        await load();
      }catch(err){
        toast(err.message || 'Approve failed');
      }
      return;
    }

    if (e.target.closest('.btn-reject')) {
      rejectPostId.value = String(id);
      rejectReason.value = '';
      modalInstance(rejectModalEl)?.show();
      return;
    }

    openDetails(r);
  });

  pdApprove?.addEventListener('click', async () => {
    if (!currentId) return;
    try{
      pdApprove.disabled = true;
      await postJson(`${API}?action=approve`, { id: currentId });
      modalInstance(pdModalEl)?.hide();
      toast('Approved');
      await load();
    }catch(err){
      toast(err.message || 'Approve failed');
    }finally{
      pdApprove.disabled = false;
    }
  });

  pdReject?.addEventListener('click', () => {
    if (!currentId) return;
    rejectPostId.value = String(currentId);
    rejectReason.value = '';
    modalInstance(rejectModalEl)?.show();
  });

  rejectSubmit?.addEventListener('click', async () => {
    const id = parseInt(rejectPostId.value || '0', 10);
    if (!id) return;

    rejectSubmit.disabled = true;
    try{
      await postJson(`${API}?action=reject`, { id, reason: rejectReason.value || '' });

      modalInstance(rejectModalEl)?.hide();
      modalInstance(pdModalEl)?.hide();

      toast('Rejected (Deleted)');
      await load();
    }catch(err){
      toast(err.message || 'Reject failed');
    }finally{
      rejectSubmit.disabled = false;
    }
  });

  document.addEventListener('DOMContentLoaded', load);
})();
</script>

<!-- FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/bootstrap5@6.1.19/index.global.min.js"></script>

<!-- Calendar JS -->
<script>
(() => {
  const API_URL = './API/calendar.php';
  const calEl   = document.getElementById('traveloCalendar');
  if (!calEl || !window.FullCalendar) return;

  const todayBtn = document.getElementById('calTodayBtn');
  const addBtn   = document.getElementById('calAddBtn');
  const jumpDate = document.getElementById('calJumpDate');
  const jumpBtn  = document.getElementById('calJumpBtn');
  const upcoming = document.getElementById('calUpcoming');

  const catEvent   = document.getElementById('catEvent');
  const catMeeting = document.getElementById('catMeeting');
  const catTask    = document.getElementById('catTask');

  const modalEl = document.getElementById('calModal');
  const form    = document.getElementById('calForm');
  const fTitle  = document.getElementById('calTitle');
  const fType   = document.getElementById('calType');
  const fDate   = document.getElementById('calDate');
  const fTime   = document.getElementById('calTime');
  const fDesc   = document.getElementById('calDesc');
  const fEditId = document.getElementById('calEditId');
  const delBtn  = document.getElementById('calDeleteBtn');

  const modal = (modalEl && window.bootstrap) ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

  const pad2 = (n) => String(n).padStart(2, '0');
  function toLocalDateStr(d){ return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`; }
  function toLocalTimeStr(d){ return `${pad2(d.getHours())}:${pad2(d.getMinutes())}`; }

  function toMySQLDateTimeLocal(d){
    return `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())} ${pad2(d.getHours())}:${pad2(d.getMinutes())}:${pad2(d.getSeconds())}`;
  }

  function esc(s){
    return String(s ?? '')
      .replaceAll('&','&amp;').replaceAll('<','&lt;')
      .replaceAll('>','&gt;').replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function typeColor(t){
    if (t === 'meeting') return '#22c55e';
    if (t === 'task') return '#f59e0b';
    return '#7c3aed';
  }

  function visibleTypesArr(){
    const arr = [];
    if (catEvent?.checked)   arr.push('event');
    if (catMeeting?.checked) arr.push('meeting');
    if (catTask?.checked)    arr.push('task');
    return arr;
  }

  async function getJson(url){
    const res = await fetch(url + (url.includes('?') ? '&' : '?') + `_=${Date.now()}`, {
      credentials: 'same-origin',
      cache: 'no-store'
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.success) throw new Error(data.message || 'Request failed');
    return data;
  }

  async function postJson(url, payload){
    const res = await fetch(url + (url.includes('?') ? '&' : '?') + `_=${Date.now()}`, {
      method: 'POST',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify(payload || {}),
      credentials: 'same-origin',
      cache: 'no-store'
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.success) throw new Error(data.message || 'Request failed');
    return data;
  }

  function toast(msg){ console.log('[Calendar]', msg); }

  let calendar = null;
  let didInit = false;

  function openModal(data){
    if (!modal) return;

    fEditId.value = data.id || '';
    fTitle.value  = data.title || '';
    fType.value   = data.type || 'event';
    fDate.value   = data.date || '';
    fTime.value   = data.time || '09:00';
    fDesc.value   = data.description || '';

    delBtn.style.display = data.id ? '' : 'none';
    modal.show();
  }

  function buildStartISO(dateStr, timeStr){
    const t = (timeStr && String(timeStr).trim()) ? String(timeStr).trim() : '09:00';
    return `${dateStr}T${t}:00`;
  }

  function mapRowToFc(row){
    const id = String(row.id);
    const type = String(row.type || 'event');
    const bg = typeColor(type);

    return {
      id,
      title: row.title || '',
      start: row.start_at || null,
      end: row.end_at || null,
      allDay: !!(row.all_day),
      backgroundColor: bg,
      borderColor: bg,
      textColor: '#fff',
      extendedProps: {
        type,
        description: row.description || ''
      }
    };
  }

  function renderUpcoming(){
    if (!upcoming || !calendar) return;

    const now = new Date();
    const types = new Set(visibleTypesArr());

    const list = calendar.getEvents()
      .map(ev => {
        const ext = ev.extendedProps || {};
        const type = String(ext.type || 'event');
        return {
          id: ev.id,
          title: ev.title || '',
          type,
          start: ev.start ? new Date(ev.start) : null,
          color: typeColor(type),
          desc: ext.description || ''
        };
      })
      .filter(x => x.start && !isNaN(+x.start))
      .filter(x => x.start >= now)
      .filter(x => types.has(x.type))
      .sort((a,b) => a.start - b.start)
      .slice(0, 5);

    if (!list.length){
      upcoming.innerHTML = `<div class="text-muted small">No upcoming items.</div>`;
      return;
    }

    upcoming.innerHTML = list.map(x => {
      const ds = toLocalDateStr(x.start);
      const ts = toLocalTimeStr(x.start);
      return `
        <div class="u-item" data-id="${esc(x.id)}">
          <div>
            <div class="u-title">${esc(x.title)}</div>
            <div class="u-meta">${esc(ds)} · ${esc(ts)}</div>
          </div>
          <div class="u-dot" style="background:${esc(x.color)}"></div>
        </div>
      `;
    }).join('');

    upcoming.querySelectorAll('.u-item').forEach(el => {
      el.addEventListener('click', () => {
        const id = el.getAttribute('data-id');
        const ev = calendar.getEventById(id);
        if (!ev || !ev.start) return;

        calendar.gotoDate(ev.start);
        const ext = ev.extendedProps || {};
        openModal({
          id: ev.id,
          title: ev.title || '',
          type: ext.type || 'event',
          date: toLocalDateStr(new Date(ev.start)),
          time: toLocalTimeStr(new Date(ev.start)),
          description: ext.description || ''
        });
      });
    });
  }

  async function updateEventFromCalendar(ev){
    const ext = ev.extendedProps || {};
    await postJson(`${API_URL}?action=update`, {
      id: parseInt(ev.id, 10),
      title: ev.title || '',
      type: ext.type || 'event',
      start_at: ev.start ? toMySQLDateTimeLocal(new Date(ev.start)) : null,
      end_at: ev.end ? toMySQLDateTimeLocal(new Date(ev.end)) : null,
      all_day: ev.allDay ? 1 : 0,
      description: ext.description || ''
    });

    calendar.refetchEvents();
  }

  function initCalendarOnce(){
    if (didInit) return;
    didInit = true;

    calendar = new FullCalendar.Calendar(calEl, {
      themeSystem: 'bootstrap5',
      initialView: 'dayGridMonth',
      height: 'auto',
      nowIndicator: true,
      selectable: true,
      editable: true,
      dayMaxEvents: true,

      headerToolbar: {
        left: 'prev,next',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },

      events: async (info, success, failure) => {
        try {
          const data = await getJson(
            `${API_URL}?action=list&start=${encodeURIComponent(info.startStr)}&end=${encodeURIComponent(info.endStr)}`
          );

          const rows = Array.isArray(data.rows) ? data.rows : [];
          const types = new Set(visibleTypesArr());
          const filtered = rows.filter(r => types.has(String(r.type || 'event')));

          success(filtered.map(mapRowToFc));
          setTimeout(renderUpcoming, 0);
        } catch (e) {
          failure(e);
        }
      },

      select: (sel) => {
        const date = sel.startStr.slice(0,10);
        openModal({
          id: '',
          title: '',
          type: 'event',
          date,
          time: '09:00',
          description: ''
        });
      },

      eventClick: (arg) => {
        const ev = arg.event;
        const ext = ev.extendedProps || {};
        const start = ev.start ? new Date(ev.start) : null;

        openModal({
          id: ev.id,
          title: ev.title || '',
          type: ext.type || 'event',
          date: start ? toLocalDateStr(start) : '',
          time: start ? toLocalTimeStr(start) : '09:00',
          description: ext.description || ''
        });
      },

      eventDrop: async (info) => {
        try {
          await updateEventFromCalendar(info.event);
          toast('Updated');
          renderUpcoming();
        } catch (e) {
          info.revert();
          toast(e.message || 'Update failed');
        }
      },

      eventResize: async (info) => {
        try {
          await updateEventFromCalendar(info.event);
          toast('Updated');
          renderUpcoming();
        } catch (e) {
          info.revert();
          toast(e.message || 'Update failed');
        }
      }
    });

    calendar.render();

    todayBtn?.addEventListener('click', () => {
      calendar.today();
      setTimeout(() => calendar.updateSize(), 60);
    });

    addBtn?.addEventListener('click', () => {
      const d = new Date();
      openModal({
        id: '',
        title: '',
        type: 'event',
        date: toLocalDateStr(d),
        time: '09:00',
        description: ''
      });
    });

    jumpBtn?.addEventListener('click', () => {
      const v = (jumpDate?.value || '').trim();
      if (v) calendar.gotoDate(v);
      setTimeout(() => calendar.updateSize(), 60);
    });

    [catEvent, catMeeting, catTask].forEach(el => {
      el?.addEventListener('change', () => {
        calendar.refetchEvents();
        setTimeout(renderUpcoming, 0);
      });
    });

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();

      const id    = (fEditId.value || '').trim();
      const title = (fTitle.value || '').trim();
      const type  = (fType.value || 'event').trim();
      const date  = (fDate.value || '').trim();
      const time  = (fTime.value || '09:00').trim();
      const desc  = (fDesc.value || '').trim();

      if (!title || !date) return;

      const start_at = buildStartISO(date, time);

      try {
        if (id) {
          await postJson(`${API_URL}?action=update`, {
            id: parseInt(id, 10),
            title,
            type,
            start_at,
            end_at: null,
            all_day: 0,
            description: desc
          });
        } else {
          await postJson(`${API_URL}?action=create`, {
            title,
            type,
            start_at,
            end_at: null,
            all_day: 0,
            description: desc
          });
        }

        modal?.hide();
        calendar.refetchEvents();
        setTimeout(renderUpcoming, 0);
      } catch (err) {
        toast(err.message || 'Save failed');
      }
    });

    delBtn?.addEventListener('click', async () => {
      const id = (fEditId.value || '').trim();
      if (!id) return;

      try {
        await postJson(`${API_URL}?action=delete`, { id: parseInt(id, 10) });
        modal?.hide();
        calendar.refetchEvents();
        setTimeout(renderUpcoming, 0);
      } catch (err) {
        toast(err.message || 'Delete failed');
      }
    });

    setTimeout(renderUpcoming, 250);
  }

  function isCalendarSectionActive(){
    const sec = document.getElementById('calendar');
    if (!sec) return true;
    return sec.classList.contains('active');
  }

  function onCalendarShown(){
    initCalendarOnce();
    requestAnimationFrame(() => {
      setTimeout(() => {
        calendar?.updateSize();
        renderUpcoming();
      }, 80);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (isCalendarSectionActive()) onCalendarShown();
  });

  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[data-page]');
    if (!a) return;
    if ((a.getAttribute('data-page') || '') === 'calendar') {
      setTimeout(onCalendarShown, 0);
    }
  });
})();
</script>

<!-- Ask AI (Jotform) Lazy Loader -->
<script>
(() => {
  const AGENT_ID = "019b189a507c7f0e98a0580ad136880f79ad";
  const SRC = `https://cdn.jotfor.ms/agent/embedjs/${AGENT_ID}/embed.js`;

  function loadWidget(){
    return new Promise((resolve, reject) => {
      if (document.querySelector(`script[src="${SRC}"]`)) return resolve();

      const s = document.createElement("script");
      s.src = SRC;
      s.async = true;
      s.onload = resolve;
      s.onerror = () => reject(new Error("Failed to load widget"));
      document.body.appendChild(s);
    });
  }

  function openLauncherWhenReady(timeoutMs = 8000){
    return new Promise((resolve) => {
      const start = Date.now();

      const tryOpen = () => {
        const launcher =
          document.querySelector('button[aria-label*="Ask AI" i]') ||
          document.querySelector('button[aria-label*="Chat" i]') ||
          document.querySelector('[data-testid*="launcher" i]') ||
          document.querySelector('.jotform-ai-launcher, .agent-launcher, .chat-launcher');

        if (launcher) { launcher.click(); resolve(true); return true; }
        if (Date.now() - start > timeoutMs) { resolve(false); return true; }
        return false;
      };

      if (tryOpen()) return;

      const obs = new MutationObserver(() => {
        if (tryOpen()) obs.disconnect();
      });
      obs.observe(document.documentElement, { childList:true, subtree:true });
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("askAiBtn");
    if (!btn) return;

    btn.addEventListener("click", async () => {
      btn.style.display = "none";
      try{
        await loadWidget();
        await openLauncherWhenReady();
      }catch(e){
        console.error(e);
        btn.style.display = "";
        alert("AI widget failed to load.");
      }
    }, { once:true });
  });
})();
</script>
<script src="./assets/js/faqs_admin.js"></script>
<script src="./assets/js/blogs_admin.js"></script>

</body>
</html>
