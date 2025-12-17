<?php
declare(strict_types=1);

/**
 * api/calendar.php
 *
 * Requires:
 *  - api/_bootstrap.php  (json_ok/json_fail/read_json/require_admin/db/clean_email)
 *  - api/_mail.php       (send_email)
 *
 * Tables expected:
 *  - calendar_items
 *  - calendar_email_log
 *  - admins (email, is_active)
 *  - users (email, is_active)
 *  - newsletter_subscribers (email, status='active')
 */

require __DIR__ . '/_bootstrap.php';
require __DIR__ . '/_mail.php';

/* extra anti-cache */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

const BRAND_APP_NAME   = 'Travelo';
const BRAND_MAIL_TITLE = 'Travelo Events';
const BRAND_MAIL_SUB   = 'Events & Calendar Notifications';

/**
 * ⚠️ لازم يكون اللوجو موجود: /assets/images/logo.svg
 * إذا بدك اسم ثاني: غيّري المسار هنا.
 */
const LOGO_URL_PATH = '/assets/images/logo.svg';

$action = strtolower((string)($_GET['action'] ?? 'list'));

if ($action !== 'list_public') {
  require_admin(); // كل عمليات الأدمن
}

switch ($action) {
  case 'list':        list_admin();   break;
  case 'list_public': list_public();  break;
  case 'create':      create_item();  break;
  case 'update':      update_item();  break;
  case 'delete':      delete_item();  break;
  default:            json_fail('Unknown action', 400);
}

/* ===================== LIST (ADMIN) ===================== */
function list_admin(): void {
  $pdo = db();

  // FullCalendar: start/end
  $from = trim((string)($_GET['from'] ?? ($_GET['start'] ?? '')));
  $to   = trim((string)($_GET['to']   ?? ($_GET['end']   ?? '')));
  $type = trim((string)($_GET['type'] ?? '')); // optional: event/meeting/task

  $where = "WHERE is_deleted = 0";
  $params = [];

  if ($type !== '' && in_array($type, ['event','meeting','task'], true)) {
    $where .= " AND type = ?";
    $params[] = $type;
  }

  // IMPORTANT: FullCalendar end is exclusive
  if ($from !== '' && $to !== '') {
    $where .= " AND start_at >= ? AND start_at < ?";
    $params[] = normalize_dt_start($from);
    $params[] = normalize_dt_end_exclusive($to);
  }

  $sql = "SELECT id, title, type, start_at, end_at, all_day, priority, description
          FROM calendar_items
          $where
          ORDER BY start_at ASC";
  $st = $pdo->prepare($sql);
  $st->execute($params);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  // admin sees everything
  $events = array_map(fn($r) => to_fc_event_admin($r), $rows);

  json_ok(['rows' => $rows, 'events' => $events]);
}

/* ===================== LIST (PUBLIC) ===================== */
function list_public(): void {
  $pdo = db();

  $from = trim((string)($_GET['from'] ?? ($_GET['start'] ?? '')));
  $to   = trim((string)($_GET['to']   ?? ($_GET['end']   ?? '')));

  // public: events + meetings فقط (no tasks)
  $where = "WHERE is_deleted = 0 AND type IN ('event','meeting')";
  $params = [];

  if ($from !== '' && $to !== '') {
    $where .= " AND start_at >= ? AND start_at < ?";
    $params[] = normalize_dt_start($from);
    $params[] = normalize_dt_end_exclusive($to);
  }

  // ✅ ما بنرجّع priority للـ public
  $sql = "SELECT id, title, type, start_at, end_at, all_day, description
          FROM calendar_items
          $where
          ORDER BY start_at ASC";
  $st = $pdo->prepare($sql);
  $st->execute($params);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  $events = array_map(fn($r) => to_fc_event_public($r), $rows);

  json_ok(['events' => $events]);
}

/* ===================== CREATE ===================== */
function create_item(): void {
  $pdo = db();
  $data = read_json();

  $title = trim((string)($data['title'] ?? ''));
  $type  = trim((string)($data['type'] ?? 'event'));

  // compat: ممكن JS يبعت start بدل start_at
  $start = trim((string)($data['start_at'] ?? ($data['start'] ?? '')));
  $end   = trim((string)($data['end_at']   ?? ($data['end']   ?? '')));

  $allDay   = (int)($data['all_day'] ?? ($data['allDay'] ?? 0)) ? 1 : 0;
  $priority = trim((string)($data['priority'] ?? 'normal'));
  $desc     = trim((string)($data['description'] ?? ''));

  if ($title === '' || $start === '') json_fail('title/start_at required', 422);
  if (!in_array($type, ['event','meeting','task'], true)) json_fail('bad type', 422);
  if (!in_array($priority, ['low','normal','high','urgent'], true)) $priority = 'normal';

  $createdBy = $_SESSION['admin_id'] ?? null;

  $sql = "INSERT INTO calendar_items
          (created_by_admin_id, title, type, start_at, end_at, all_day, priority, description, is_deleted)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
  $st = $pdo->prepare($sql);
  $st->execute([
    $createdBy,
    $title,
    $type,
    normalize_dt_store($start),
    ($end !== '' ? normalize_dt_store($end) : null),
    $allDay,
    $priority,
    ($desc !== '' ? $desc : null),
  ]);

  $id = (int)$pdo->lastInsertId();

  $item = fetch_item($id);
  if ($item) notify_by_type_from_item($item, 'create');

  json_ok(['id' => $id]);
}

/* ===================== UPDATE ===================== */
function update_item(): void {
  $pdo = db();
  $data = read_json();

  $id = (int)($data['id'] ?? 0);
  if ($id <= 0) json_fail('id required', 422);

  $old = fetch_item($id);
  if (!$old) json_fail('not found', 404);

  $title = trim((string)($data['title'] ?? $old['title']));
  $type  = trim((string)($data['type']  ?? $old['type']));

  $start = trim((string)($data['start_at'] ?? ($data['start'] ?? $old['start_at'])));

  $end   = (array_key_exists('end_at', $data) || array_key_exists('end', $data))
         ? trim((string)($data['end_at'] ?? ($data['end'] ?? '')))
         : (string)($old['end_at'] ?? '');

  $allDay = (array_key_exists('all_day', $data) || array_key_exists('allDay', $data))
          ? ((int)($data['all_day'] ?? ($data['allDay'] ?? 0)) ? 1 : 0)
          : (int)$old['all_day'];

  $priority = trim((string)($data['priority'] ?? $old['priority']));
  $desc = array_key_exists('description', $data)
        ? trim((string)$data['description'])
        : (string)($old['description'] ?? '');

  if ($title === '' || $start === '') json_fail('title/start_at required', 422);
  if (!in_array($type, ['event','meeting','task'], true)) json_fail('bad type', 422);
  if (!in_array($priority, ['low','normal','high','urgent'], true)) $priority = 'normal';

  $sql = "UPDATE calendar_items
          SET title=?, type=?, start_at=?, end_at=?, all_day=?, priority=?, description=?
          WHERE id=? AND is_deleted=0";
  $st = $pdo->prepare($sql);
  $st->execute([
    $title,
    $type,
    normalize_dt_store($start),
    ($end !== '' ? normalize_dt_store($end) : null),
    $allDay,
    $priority,
    ($desc !== '' ? $desc : null),
    $id,
  ]);

  $item = fetch_item($id);
  if ($item) notify_by_type_from_item($item, 'update');

  json_ok(['id' => $id]);
}

/* ===================== DELETE (SOFT) ===================== */
function delete_item(): void {
  $pdo = db();
  $data = read_json();

  $id = (int)($data['id'] ?? 0);
  if ($id <= 0) json_fail('id required', 422);

  $old = fetch_item($id);
  if (!$old) json_fail('not found', 404);

  $st = $pdo->prepare("UPDATE calendar_items SET is_deleted=1 WHERE id=?");
  $st->execute([$id]);

  // نستخدم $old حتى بعد الحذف
  notify_by_type_from_item($old, 'delete');

  json_ok(['id' => $id]);
}

/* ===================== DB HELPERS ===================== */
function fetch_item(int $id): ?array {
  $pdo = db();
  $st = $pdo->prepare("SELECT * FROM calendar_items WHERE id=? LIMIT 1");
  $st->execute([$id]);
  $r = $st->fetch(PDO::FETCH_ASSOC);
  return $r ?: null;
}

/* ===================== FULLCALENDAR MAPPERS ===================== */
function to_fc_event_admin(array $r): array {
  return [
    'id' => (int)$r['id'],
    'title' => (string)$r['title'],
    'start' => (string)$r['start_at'],
    'end' => ($r['end_at'] ? (string)$r['end_at'] : null),
    'allDay' => ((int)$r['all_day'] === 1),
    'extendedProps' => [
      'type' => (string)$r['type'],
      'priority' => (string)($r['priority'] ?? 'normal'),
      'description' => (string)($r['description'] ?? ''),
    ],
  ];
}

function to_fc_event_public(array $r): array {
  return [
    'id' => (int)$r['id'],
    'title' => (string)$r['title'],
    'start' => (string)$r['start_at'],
    'end' => ($r['end_at'] ? (string)$r['end_at'] : null),
    'allDay' => ((int)$r['all_day'] === 1),
    'extendedProps' => [
      'type' => (string)$r['type'],
      // ✅ public ما يشوف priority
      'description' => (string)($r['description'] ?? ''),
    ],
  ];
}

/**
 * event   -> admins + users + subscribers
 * meeting -> users + subscribers
 * task    -> admins only
 */
function recipients_for_type(string $type): array {
  $pdo = db();
  $emails = [];

  $get_admins = function() use ($pdo) {
    $st = $pdo->query("SELECT email FROM admins WHERE is_active=1 AND email IS NOT NULL");
    return $st->fetchAll(PDO::FETCH_COLUMN);
  };

  $get_users = function() use ($pdo) {
    $st = $pdo->query("SELECT email FROM users WHERE is_active=1 AND email IS NOT NULL");
    return $st->fetchAll(PDO::FETCH_COLUMN);
  };

  $get_subs = function() use ($pdo) {
    $st = $pdo->query("SELECT email FROM newsletter_subscribers WHERE status='active' AND email IS NOT NULL");
    return $st->fetchAll(PDO::FETCH_COLUMN);
  };

  if ($type === 'event') {
    $emails = array_merge($emails, $get_admins(), $get_users(), $get_subs());
  } elseif ($type === 'task') {
    $emails = array_merge($emails, $get_admins());
  } elseif ($type === 'meeting') {
    $emails = array_merge($emails, $get_users(), $get_subs());
  }

  $clean = [];
  foreach ($emails as $e) {
    $c = clean_email((string)$e);
    if ($c) $clean[] = strtolower($c);
  }
  return array_values(array_unique($clean));
}

function notify_by_type_from_item(array $item, string $action): void {
  $type = (string)($item['type'] ?? 'event');
  $recipients = recipients_for_type($type);
  if (!$recipients) return;

  $built = build_calendar_email($item, $action);

  foreach ($recipients as $email) {
    $r = send_email($email, $built['subject'], $built['html'], $built['text']);
    log_email((int)($item['id'] ?? 0), $action, $email, (bool)$r['ok'], (string)($r['error'] ?? ''));
  }
}

function log_email(int $calendarId, string $action, string $email, bool $ok, string $err): void {
  $pdo = db();
  $st = $pdo->prepare("INSERT INTO calendar_email_log (calendar_id, action, recipient_email, sent_ok, error_msg)
                       VALUES (?, ?, ?, ?, ?)");
  $st->execute([$calendarId, $action, $email, $ok ? 1 : 0, $ok ? null : mb_substr($err, 0, 500)]);
}

/* ===================== EMAIL TEMPLATE (NA3EM + LOGO) ===================== */
function build_calendar_email(array $item, string $action): array {
  $type = (string)($item['type'] ?? 'event');
  $title = (string)($item['title'] ?? '');
  $desc  = (string)($item['description'] ?? '');
  $priority = (string)($item['priority'] ?? 'normal');
  $allDay = ((int)($item['all_day'] ?? 0) === 1);

  $tz = new DateTimeZone('Asia/Hebron');

  $startRaw = (string)($item['start_at'] ?? '');
  $endRaw   = (string)($item['end_at'] ?? '');

  $startFmt = fmt_dt($startRaw, $tz, $allDay);
  $endFmt   = $endRaw ? fmt_dt($endRaw, $tz, $allDay) : '';
  $when = $allDay ? "All day" : trim($startFmt . ($endFmt ? " → " . $endFmt : ""));

  $typeMeta = [
    'event'   => ['label' => 'Event',   'emoji' => '📅', 'color' => '#7c3aed', 'hint' => 'A new Travelo event is available.'],
    'meeting' => ['label' => 'Meeting', 'emoji' => '🤝', 'color' => '#22c55e', 'hint' => 'A meeting has been scheduled on Travelo.'],
    'task'    => ['label' => 'Task',    'emoji' => '✅', 'color' => '#f59e0b', 'hint' => 'A new admin task has been assigned.'],
  ];
  $tLabel = $typeMeta[$type]['label'] ?? 'Event';
  $tEmoji = $typeMeta[$type]['emoji'] ?? '📅';
  $tColor = $typeMeta[$type]['color'] ?? '#7c3aed';
  $tHint  = $typeMeta[$type]['hint']  ?? 'A new calendar update is available.';

  $actionMap = [
    'create' => ['label' => 'Created', 'pill' => '#2563eb', 'line' => 'This item was created on the Travelo calendar.'],
    'update' => ['label' => 'Updated', 'pill' => '#0ea5e9', 'line' => 'This item has been updated. Please review the details below.'],
    'delete' => ['label' => 'Deleted', 'pill' => '#ef4444', 'line' => 'This item has been removed from the calendar.'],
  ];
  $aLabel = $actionMap[$action]['label'] ?? strtoupper($action);
  $aColor = $actionMap[$action]['pill']  ?? '#2563eb';
  $aLine  = $actionMap[$action]['line']  ?? 'Calendar update.';

  // Subject رسمي/شامل
  $subject = BRAND_MAIL_TITLE . " • {$aLabel} • {$title}";

  $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
  $safeType  = htmlspecialchars($tLabel, ENT_QUOTES, 'UTF-8');
  $safeWhen  = htmlspecialchars($when, ENT_QUOTES, 'UTF-8');
  $safePrio  = htmlspecialchars(ucfirst($priority), ENT_QUOTES, 'UTF-8');
  $safeHint  = htmlspecialchars($tHint, ENT_QUOTES, 'UTF-8');
  $safeLine  = htmlspecialchars($aLine, ENT_QUOTES, 'UTF-8');

  $safeDesc  = $desc !== '' ? nl2br(htmlspecialchars($desc, ENT_QUOTES, 'UTF-8')) : '';

  $logoUrl = build_absolute_url(LOGO_URL_PATH);
  $logoHtml = $logoUrl
    ? "<img src='".htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8')."' alt='".BRAND_APP_NAME."' style='display:block;width:44px;height:44px;border-radius:14px;object-fit:contain;background:#fff;border:1px solid #eef0f6'/>"
    : "<div style='width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,#7c3aed,#6c63ff);display:inline-block'></div>";

  // ✅ ملاحظة: كثير عملاء بريد ما بحبوا Google Fonts — بس ما بتضر
  $html = "
  <div style='margin:0;padding:0;background:#f6f7fb'>
    <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background:#f6f7fb;padding:26px 0'>
      <tr>
        <td align='center'>
          <table role='presentation' width='640' cellpadding='0' cellspacing='0' style='width:640px;max-width:92vw'>

            <tr>
              <td style='padding:0 14px 14px'>
                <div style='font-family:Inter,Arial,sans-serif;color:#0f172a;display:flex;align-items:center;gap:12px'>
                  {$logoHtml}
                  <div>
                    <div style='font-size:18px;font-weight:800;line-height:1'>".htmlspecialchars(BRAND_APP_NAME, ENT_QUOTES, 'UTF-8')."</div>
                    <div style='font-size:12px;color:#64748b;margin-top:3px'>".htmlspecialchars(BRAND_MAIL_SUB, ENT_QUOTES, 'UTF-8')."</div>
                  </div>
                </div>
              </td>
            </tr>

            <tr>
              <td style='background:#ffffff;border:1px solid #e8eaf3;border-radius:16px;overflow:hidden'>
                <div style='padding:18px 18px 10px;font-family:Inter,Arial,sans-serif'>

                  <div style='display:flex;align-items:center;gap:10px;flex-wrap:wrap'>
                    <span style='display:inline-block;padding:6px 10px;border-radius:999px;background:{$aColor};color:#fff;font-size:12px;font-weight:800'>
                      {$aLabel}
                    </span>
                    <span style='display:inline-block;padding:6px 10px;border-radius:999px;background:rgba(2,6,23,.06);color:#0f172a;font-size:12px;font-weight:800'>
                      {$tEmoji} {$safeType}
                    </span>
                    ".($type === 'task' ? "
                    <span style='display:inline-block;padding:6px 10px;border-radius:999px;background:rgba(124,43,255,.10);color:#5b21b6;font-size:12px;font-weight:800'>
                      Priority: {$safePrio}
                    </span>
                    " : "")."
                  </div>

                  <h2 style='margin:12px 0 6px;font-size:20px;line-height:1.3;color:#0f172a'>{$safeTitle}</h2>
                  <div style='color:#64748b;font-size:13px;line-height:1.6;margin-bottom:10px'>{$safeHint}</div>

                  <div style='display:inline-block;width:100%;height:1px;background:#eef0f6;margin:10px 0 14px'></div>

                  <div style='color:#0f172a;font-size:14px;line-height:1.7;margin:0 0 10px'>
                    <span style='color:#64748b'>Update:</span> {$safeLine}
                  </div>

                  <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='font-size:14px;color:#0f172a'>
                    <tr>
                      <td style='padding:8px 0;color:#64748b;width:120px'>When</td>
                      <td style='padding:8px 0;font-weight:800'>{$safeWhen}</td>
                    </tr>
                    <tr>
                      <td style='padding:8px 0;color:#64748b'>Category</td>
                      <td style='padding:8px 0;font-weight:800'>
                        <span style='display:inline-flex;align-items:center;gap:8px'>
                          <span style='width:10px;height:10px;border-radius:999px;background:{$tColor};display:inline-block'></span>
                          {$safeType}
                        </span>
                      </td>
                    </tr>
                    ".($safeDesc ? "
                    <tr>
                      <td style='padding:8px 0;color:#64748b;vertical-align:top'>Details</td>
                      <td style='padding:8px 0;color:#0f172a;line-height:1.7'>{$safeDesc}</td>
                    </tr>
                    " : "")."
                  </table>

                  <div style='margin:14px 0 0;color:#64748b;font-size:12px;line-height:1.6'>
                    You’re receiving this email because you are subscribed to ".htmlspecialchars(BRAND_APP_NAME, ENT_QUOTES, 'UTF-8')." updates.
                  </div>
                </div>

                <div style='padding:14px 18px;background:#fbfbfe;border-top:1px solid #eef0f6;font-family:Inter,Arial,sans-serif'>
                  <div style='font-size:12px;color:#64748b'>
                    © ".date('Y')." ".htmlspecialchars(BRAND_APP_NAME, ENT_QUOTES, 'UTF-8').". All rights reserved.
                  </div>
                </div>
              </td>
            </tr>

          </table>
        </td>
      </tr>
    </table>
  </div>
  ";

  $text = BRAND_MAIL_TITLE . " ({$aLabel})\n"
        . "Title: {$title}\n"
        . "Type: {$tLabel}\n"
        . "When: {$when}\n"
        . ($type === 'task' ? "Priority: {$priority}\n" : "")
        . ($desc ? "Details: {$desc}\n" : "");

  return ['subject' => $subject, 'html' => $html, 'text' => $text];
}

function build_absolute_url(string $path): string {
  $path = trim($path);
  if ($path === '') return '';

  // لو path أصلاً URL
  if (preg_match('~^https?://~i', $path)) return $path;

  $host = (string)($_SERVER['HTTP_HOST'] ?? '');
  if ($host === '') return ''; // إذا ما في request context

  $https = (string)($_SERVER['HTTPS'] ?? '');
  $scheme = (!empty($https) && strtolower($https) !== 'off') ? 'https' : 'http';

  if ($path[0] !== '/') $path = '/' . $path;
  return $scheme . '://' . $host . $path;
}

function fmt_dt(string $raw, DateTimeZone $tz, bool $allDay): string {
  $raw = trim($raw);
  if ($raw === '') return '';

  // خليها صيغة DB دايمًا
  $raw = str_replace('T', ' ', $raw);
  $raw = preg_replace('/Z$/', '', $raw);

  try {
    // اعتبري الوقت المخزن "محلي" Asia/Hebron (لأنه DATETIME بلا timezone)
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $raw, $tz)
       ?: DateTime::createFromFormat('Y-m-d H:i', $raw, $tz)
       ?: new DateTime($raw, $tz);

    return $allDay ? $dt->format('Y-m-d') : $dt->format('Y-m-d H:i');
  } catch (Throwable $e) {
    return $raw;
  }
}


/* ===================== DATETIME NORMALIZERS ===================== */
// للـ list: إذا جاي تاريخ فقط، نخليه بداية اليوم
function normalize_dt_start(string $v): string {
  $v = trim($v);
  if ($v === '') return $v;
  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v . ' 00:00:00';
  return normalize_dt_store($v);
}

// للـ list: FullCalendar end is exclusive (YYYY-MM-DD => 00:00:00)
function normalize_dt_end_exclusive(string $v): string {
  $v = trim($v);
  if ($v === '') return $v;
  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v . ' 00:00:00';
  return normalize_dt_store($v);
}

// للتخزين: نخلي ISO أو "Y-m-d H:i(:s)" مقبولين
function normalize_dt_store(string $v): string {
  $v = trim($v);
  if ($v === '') return $v;

  $v = str_replace('T', ' ', $v);
  $v = preg_replace('/Z$/', '', $v);           // لو جاي Z
  $v = preg_replace('/\.\d+$/', '', $v);       // لو milliseconds

  // إذا "YYYY-MM-DD HH:MM" ضيف ثواني
  if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $v)) {
    $v .= ':00';
  }

  // إذا "YYYY-MM-DD" لحاله
  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
    $v .= ' 00:00:00';
  }

  return $v;
}
