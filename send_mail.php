<?php
// ─── send_mail.php ───────────────────────────────────────────────────────────
// Receives JSON POST from the contact form and sends it via PHP mail()
// Place this file in the SAME directory as index.html on your server.
// ─────────────────────────────────────────────────────────────────────────────

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Method not allowed']);
    exit;
}

// ── Read + decode JSON body ──────────────────────────────────────────────────
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Invalid JSON']);
    exit;
}

// ── Sanitize inputs ──────────────────────────────────────────────────────────
function clean($str) {
    return htmlspecialchars(strip_tags(trim((string)$str)), ENT_QUOTES, 'UTF-8');
}

$fname   = clean($data['fname']   ?? '');
$lname   = clean($data['lname']   ?? '');
$email   = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone   = clean($data['phone']   ?? '');
$service = clean($data['service'] ?? '');
$message = clean($data['message'] ?? '');

// ── Validate required fields ─────────────────────────────────────────────────
if (!$fname || !$lname || !$email || !$message) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'msg' => 'Missing required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'msg' => 'Invalid email address']);
    exit;
}

// ── Mail configuration ───────────────────────────────────────────────────────
$to      = 'edgar@globalonebuilders.us';
$from    = 'noreply@globalonebuilders.us';   // must be a valid address on your domain
$replyTo = $email;

$subject = "New Estimate Request from {$fname} {$lname}";

// ── Build plain-text body ────────────────────────────────────────────────────
$body  = "═══════════════════════════════════════════\n";
$body .= " NEW ESTIMATE REQUEST — Global One Builders\n";
$body .= "═══════════════════════════════════════════\n\n";
$body .= "Name:    {$fname} {$lname}\n";
$body .= "Email:   {$email}\n";
$body .= "Phone:   " . ($phone ?: '(not provided)') . "\n";
$body .= "Service: " . ($service ?: '(not selected)') . "\n\n";
$body .= "─── Project Details ────────────────────────\n";
$body .= "{$message}\n\n";
$body .= "─── Submitted ──────────────────────────────\n";
$body .= date('Y-m-d H:i:s T') . "\n";
$body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

// ── Build HTML body ──────────────────────────────────────────────────────────
$htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 24px; }
    .card { background: #fff; border-radius: 6px; max-width: 600px; margin: 0 auto; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
    .header { background: #003070; padding: 28px 32px; }
    .header h1 { color: #d4af37; margin: 0; font-size: 1.3rem; letter-spacing: .05em; text-transform: uppercase; }
    .header p  { color: #ccd6f6; margin: 4px 0 0; font-size: .85rem; }
    .body { padding: 32px; }
    .row { display: flex; border-bottom: 1px solid #eee; padding: 10px 0; }
    .row:last-child { border-bottom: none; }
    .label { color: #666; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; width: 110px; flex-shrink: 0; padding-top: 2px; }
    .value { color: #111; font-size: .95rem; }
    .details-box { background: #f9f9f9; border-left: 3px solid #d4af37; padding: 14px 16px; border-radius: 0 4px 4px 0; margin-top: 20px; white-space: pre-wrap; font-size: .95rem; color: #333; }
    .footer { background: #060f1e; padding: 16px 32px; text-align: center; color: #8892b0; font-size: .75rem; }
    .footer a { color: #d4af37; text-decoration: none; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">
      <h1>New Estimate Request</h1>
      <p>Global One Builders LLC — globalonebuilders.us</p>
    </div>
    <div class="body">
      <div class="row"><span class="label">Name</span><span class="value">{$fname} {$lname}</span></div>
      <div class="row"><span class="label">Email</span><span class="value"><a href="mailto:{$email}" style="color:#003070;">{$email}</a></span></div>
      <div class="row"><span class="label">Phone</span><span class="value">{$phone}</span></div>
      <div class="row"><span class="label">Service</span><span class="value">{$service}</span></div>
      <p style="margin:20px 0 8px;font-weight:700;font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;color:#666;">Project Details</p>
      <div class="details-box">{$message}</div>
    </div>
    <div class="footer">
      Submitted {$_SERVER['REQUEST_TIME']} &nbsp;|&nbsp; IP: {$_SERVER['REMOTE_ADDR']} &nbsp;|&nbsp;
      <a href="mailto:{$email}">Reply to client</a>
    </div>
  </div>
</body>
</html>
HTML;

// ── Compose headers ──────────────────────────────────────────────────────────
$boundary = '----=_Part_' . md5(uniqid('', true));

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "From: Global One Builders <{$from}>\r\n";
$headers .= "Reply-To: {$fname} {$lname} <{$replyTo}>\r\n";
$headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$fullBody  = "--{$boundary}\r\n";
$fullBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
$fullBody .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$fullBody .= $body . "\r\n";
$fullBody .= "--{$boundary}\r\n";
$fullBody .= "Content-Type: text/html; charset=UTF-8\r\n";
$fullBody .= "Content-Transfer-Encoding: base64\r\n\r\n";
$fullBody .= chunk_split(base64_encode($htmlBody)) . "\r\n";
$fullBody .= "--{$boundary}--";

// ── Send ─────────────────────────────────────────────────────────────────────
$sent = mail($to, $subject, $fullBody, $headers);

if ($sent) {
    // Optional: also send a confirmation reply to the client
    $confirmSubject = 'We received your request — Global One Builders';
    $confirmBody    = "Hi {$fname},\n\nThank you for reaching out to Global One Builders LLC.\n\nWe've received your estimate request and will get back to you within 24 hours.\n\nIf you need to reach us sooner:\n  📞 +1 574-386-8817\n  📧 edgar@globalonebuilders.us\n\nBest regards,\nEdgar Flores\nGlobal One Builders LLC";
    $confirmHeaders = "From: Global One Builders <{$from}>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    @mail($email, $confirmSubject, $confirmBody, $confirmHeaders);

    http_response_code(200);
    echo json_encode(['ok' => true, 'msg' => 'Email sent']);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'mail() returned false — check server mail config']);
}
