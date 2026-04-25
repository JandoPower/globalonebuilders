<?php
// ─── send_mail.php (SMTP version) ────────────────────────────────────────────
// Works with MailEnable and most shared hosting mail servers.
// Upload to the SAME folder as index.html.
// ─────────────────────────────────────────────────────────────────────────────

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { http_response_code(405); echo json_encode(['ok'=>false,'msg'=>'Method not allowed']); exit; }

// ── Read body ────────────────────────────────────────────────────────────────
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'Invalid JSON']); exit; }

// ── Sanitize ─────────────────────────────────────────────────────────────────
function clean($s) { return htmlspecialchars(strip_tags(trim((string)$s)), ENT_QUOTES, 'UTF-8'); }

$fname   = clean($data['fname']   ?? '');
$lname   = clean($data['lname']   ?? '');
$email   = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone   = clean($data['phone']   ?? '');
$service = clean($data['service'] ?? '');
$message = clean($data['message'] ?? '');

if (!$fname || !$lname || !$email || !$message) {
    http_response_code(422); echo json_encode(['ok'=>false,'msg'=>'Missing required fields']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422); echo json_encode(['ok'=>false,'msg'=>'Invalid email']); exit;
}

// ════════════════════════════════════════════════════════════════════════════
// ██  SMTP SETTINGS — fill in YOUR credentials below  ██
// ════════════════════════════════════════════════════════════════════════════
$smtp_host = 'mail.globalonebuilders.us'; // your mail server
$smtp_port = 587;                          // 587 (STARTTLS) · 465 (SSL) · 25 (plain)
$smtp_user = 'edgar@globalonebuilders.us'; // your email login
$smtp_pass = 'TuMadreHDLGP2026!';   // ← PUT YOUR ACTUAL PASSWORD HERE
$smtp_from = 'edgar@globalonebuilders.us';
$smtp_name = 'Global One Builders';
$smtp_to   = 'edgar@globalonebuilders.us';
// ════════════════════════════════════════════════════════════════════════════

$subject = "New Estimate Request from {$fname} {$lname}";
$date    = date('F j, Y  g:i A T');

// ── HTML email body ───────────────────────────────────────────────────────────
$htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f0f2f5;font-family:Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f2f5;padding:32px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.12);">
        <tr><td style="background:#003070;padding:28px 36px;">
          <p style="margin:0;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:#d4af37;font-weight:700;">Global One Builders LLC</p>
          <h1 style="margin:8px 0 0;font-size:22px;color:#ffffff;font-weight:700;letter-spacing:1px;">NEW ESTIMATE REQUEST</h1>
        </td></tr>
        <tr><td style="padding:32px 36px 0;">
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr><td style="padding:10px 0;border-bottom:1px solid #eee;">
              <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#888;">Name</span><br>
              <span style="font-size:16px;color:#111;font-weight:600;">{$fname} {$lname}</span>
            </td></tr>
            <tr><td style="padding:10px 0;border-bottom:1px solid #eee;">
              <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#888;">Email</span><br>
              <a href="mailto:{$email}" style="font-size:15px;color:#003070;text-decoration:none;">{$email}</a>
            </td></tr>
            <tr><td style="padding:10px 0;border-bottom:1px solid #eee;">
              <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#888;">Phone</span><br>
              <span style="font-size:15px;color:#111;">{$phone}</span>
            </td></tr>
            <tr><td style="padding:10px 0;">
              <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#888;">Service Requested</span><br>
              <span style="font-size:15px;color:#111;font-weight:600;">{$service}</span>
            </td></tr>
          </table>
        </td></tr>
        <tr><td style="padding:24px 36px;">
          <p style="margin:0 0 10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#888;">Project Details</p>
          <div style="background:#f8f9fa;border-left:4px solid #d4af37;padding:16px 20px;border-radius:0 6px 6px 0;font-size:15px;color:#333;line-height:1.7;white-space:pre-wrap;">{$message}</div>
        </td></tr>
        <tr><td style="padding:0 36px 32px;text-align:center;">
          <a href="mailto:{$email}?subject=Re: Your Estimate Request"
             style="display:inline-block;background:#003070;color:#ffffff;text-decoration:none;padding:13px 32px;border-radius:4px;font-weight:700;font-size:14px;letter-spacing:1px;text-transform:uppercase;">
            Reply to Client
          </a>
        </td></tr>
        <tr><td style="background:#060f1e;padding:16px 36px;text-align:center;">
          <p style="margin:0;font-size:12px;color:#8892b0;">Submitted {$date} &nbsp;·&nbsp; globalonebuilders.us &nbsp;·&nbsp; +1 574-386-8817</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

$plainBody  = "NEW ESTIMATE REQUEST — Global One Builders LLC\n";
$plainBody .= "================================================\n\n";
$plainBody .= "Name:    {$fname} {$lname}\n";
$plainBody .= "Email:   {$email}\n";
$plainBody .= "Phone:   " . ($phone ?: '(not provided)') . "\n";
$plainBody .= "Service: " . ($service ?: '(not selected)') . "\n\n";
$plainBody .= "Project Details:\n{$message}\n\n";
$plainBody .= "Submitted: {$date}\n";

// ── Raw SMTP sender ───────────────────────────────────────────────────────────
function smtp_send($host, $port, $user, $pass, $from, $fromName, $to, $replyTo, $subject, $html, $plain) {
    $boundary = '----=_Boundary_' . md5(uniqid('', true));

    $errno = 0; $errstr = '';
    $conn = ($port == 465)
        ? @fsockopen("ssl://{$host}", $port, $errno, $errstr, 15)
        : @fsockopen($host, $port, $errno, $errstr, 15);

    if (!$conn) return "Cannot connect to SMTP {$host}:{$port} — {$errstr} ({$errno})";

    function r($conn, $cmd = null) {
        if ($cmd !== null) fwrite($conn, $cmd . "\r\n");
        $out = '';
        while ($line = fgets($conn, 515)) { $out .= $line; if (substr($line,3,1)===' ') break; }
        return $out;
    }

    r($conn);                                          // greeting
    r($conn, "EHLO " . (gethostname() ?: 'localhost'));
    if ($port == 587) {
        r($conn, "STARTTLS");
        stream_socket_enable_crypto($conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        r($conn, "EHLO " . (gethostname() ?: 'localhost'));
    }
    r($conn, "AUTH LOGIN");
    r($conn, base64_encode($user));
    $auth = r($conn, base64_encode($pass));
    if (strpos($auth, '235') === false) { fclose($conn); return "Auth failed — check password. Server said: " . trim($auth); }

    r($conn, "MAIL FROM:<{$from}>");
    r($conn, "RCPT TO:<{$to}>");
    r($conn, "DATA");

    $msg  = "From: {$fromName} <{$from}>\r\n";
    $msg .= "Reply-To: {$replyTo}\r\n";
    $msg .= "To: {$to}\r\n";
    $msg .= "Subject: {$subject}\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";
    $msg .= "--{$boundary}\r\n";
    $msg .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
    $msg .= chunk_split(base64_encode($plain));
    $msg .= "--{$boundary}\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
    $msg .= chunk_split(base64_encode($html));
    $msg .= "--{$boundary}--\r\n";

    fwrite($conn, $msg . "\r\n.\r\n");
    $resp = r($conn);
    r($conn, "QUIT");
    fclose($conn);

    if (strpos($resp, '250') === false) return "DATA error: " . trim($resp);
    return true;
}

// ── Send to Edgar ─────────────────────────────────────────────────────────────
$result = smtp_send(
    $smtp_host, $smtp_port, $smtp_user, $smtp_pass,
    $smtp_from, $smtp_name,
    $smtp_to, $email,
    $subject, $htmlBody, $plainBody
);

if ($result !== true) {
    error_log("G1B mailer: " . $result);
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $result]);
    exit;
}

// ── Auto-reply to client ──────────────────────────────────────────────────────
$confirmSubject = 'We received your request — Global One Builders';
$confirmPlain   = "Hi {$fname},\n\nThank you for contacting Global One Builders LLC!\n\nWe've received your estimate request and will follow up within 24 hours.\n\nNeed us sooner?\nCall/Text: +1 574-386-8817\nEmail: edgar@globalonebuilders.us\n\nBest,\nEdgar Flores\nGlobal One Builders LLC";
$confirmHtml    = <<<HTML
<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f0f2f5;margin:0;padding:32px 0;">
<table width="600" cellpadding="0" cellspacing="0" align="center" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.1);">
  <tr><td style="background:#003070;padding:24px 32px;">
    <p style="margin:0;color:#d4af37;font-size:11px;letter-spacing:3px;text-transform:uppercase;font-weight:700;">Global One Builders LLC</p>
    <h2 style="margin:8px 0 0;color:#fff;font-size:20px;">Request Received ✓</h2>
  </td></tr>
  <tr><td style="padding:28px 32px;color:#333;font-size:15px;line-height:1.7;">
    <p>Hi <strong>{$fname}</strong>,</p>
    <p>Thanks for reaching out! We've received your estimate request and will get back to you <strong>within 24 hours</strong>.</p>
    <p>Need us sooner?<br>
    📞 <a href="tel:+15743868817" style="color:#003070;font-weight:700;">+1 574-386-8817</a></p>
    <p style="margin:0;">Best regards,<br><strong>Edgar Flores</strong><br>Global One Builders LLC</p>
  </td></tr>
  <tr><td style="background:#060f1e;padding:14px 32px;text-align:center;">
    <p style="margin:0;font-size:12px;color:#8892b0;">globalonebuilders.us</p>
  </td></tr>
</table>
</body></html>
HTML;

@smtp_send($smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_from, $smtp_name, $email, $smtp_from, $confirmSubject, $confirmHtml, $confirmPlain);

http_response_code(200);
echo json_encode(['ok' => true, 'msg' => 'Email sent successfully']);
