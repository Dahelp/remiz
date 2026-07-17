<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');

function clean_text(string $value, int $limit = 800): string
{
    $value = trim(strip_tags($value));
    $value = preg_replace('/[\r\n]+/u', ' ', $value) ?? $value;
    return mb_substr($value, 0, $limit);
}

function render_result(string $title, string $message, bool $success): void
{
    $status = $success ? 'ok' : 'error';
    http_response_code($success ? 200 : 422);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title><link rel="stylesheet" href="assets/css/remiz.css"></head>';
    echo '<body><main class="send-result send-result--' . $status . '"><section class="send-card">';
    echo '<a class="brand" href="./"><span class="brand-mark">Р</span><span>РЕМИЗ<small>кухни и шкафы</small></span></a>';
    echo '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<div class="hero-actions"><a class="btn btn-primary" href="./">На главную</a><a class="btn btn-outline" href="kontakty/">Контакты</a></div>';
    echo '</section></main></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    render_result('Форма заявки', 'Отправьте заявку через форму на сайте.', false);
}

if (!empty($_POST['website'] ?? '')) {
    render_result('Заявка принята', 'Спасибо. Мы свяжемся с вами в ближайшее рабочее время.', true);
}

$name = clean_text((string)($_POST['name'] ?? ''), 120);
$phone = clean_text((string)($_POST['phone'] ?? ''), 80);
$type = clean_text((string)($_POST['type'] ?? ''), 160);
$budget = clean_text((string)($_POST['budget'] ?? ''), 160);
$comment = clean_text((string)($_POST['comment'] ?? ''), 1200);
$source = clean_text((string)($_POST['source'] ?? 'Сайт remizmebel.ru'), 200);

$digits = preg_replace('/\D+/', '', $phone) ?? '';
if ($name === '' || mb_strlen($name) < 2) {
    render_result('Проверьте имя', 'Пожалуйста, укажите имя, чтобы менеджер понял, как к вам обращаться.', false);
}

if (strlen($digits) < 10) {
    render_result('Проверьте телефон', 'Пожалуйста, укажите телефон для связи.', false);
}

$to = 'kuhniremiz@mail.ru';
$subject = 'Заявка с remizmebel.ru';
$lines = [
    'Новая заявка с сайта remizmebel.ru',
    '',
    'Имя: ' . $name,
    'Телефон: ' . $phone,
    'Что нужно: ' . ($type ?: 'Не указано'),
    'Бюджет: ' . ($budget ?: 'Не указан'),
    'Источник: ' . ($source ?: 'Сайт'),
    '',
    'Комментарий:',
    $comment ?: 'Не указан',
    '',
    'Дата: ' . date('d.m.Y H:i:s'),
    'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
];

$body = implode("\n", $lines);
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: remizmebel.ru <no-reply@remizmebel.ru>',
    'Reply-To: ' . $to,
];

$sent = mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));

if (!$sent) {
    render_result('Заявка не отправилась', 'Сейчас не удалось отправить форму. Позвоните, пожалуйста: +7 (995) 301-58-58.', false);
}

render_result('Заявка отправлена', 'Спасибо. Менеджер РЕМИЗ свяжется с вами и уточнит детали проекта.', true);
