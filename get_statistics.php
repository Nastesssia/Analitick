<?php
session_start();
header('Content-Type: application/json');

require_once 'DB_Connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lawyer') {
  echo json_encode(['success' => false, 'message' => 'Доступ запрещен.'], JSON_UNESCAPED_UNICODE);
  exit();
}

$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';
$items = (($_GET['items'] ?? '') === '1');                 // вернуть список
$list  = $_GET['list'] ?? 'created';                      // created|sent|resolved|revision

// Валидация формата YYYY-MM-DD
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
  echo json_encode(['success' => false, 'message' => 'Некорректный период. Формат даты: YYYY-MM-DD'], JSON_UNESCAPED_UNICODE);
  exit();
}

// Белый список типов
$allowedLists = ['created', 'sent', 'resolved', 'revision'];
if (!in_array($list, $allowedLists, true)) {
  $list = 'created';
}

try {
  $startDt = new DateTime($start . ' 00:00:00');
  $endDt   = new DateTime($end . ' 00:00:00');
  $endDt->modify('+1 day'); // end эксклюзивный

  if ($startDt > $endDt) {
    echo json_encode(['success' => false, 'message' => 'Дата "с" не может быть больше даты "по".'], JSON_UNESCAPED_UNICODE);
    exit();
  }

  $startStr = $startDt->format('Y-m-d H:i:s');
  $endStr   = $endDt->format('Y-m-d H:i:s');

  $db = new DB_Connect();
  $conn = $db->connect();

  // 1) Метрики за период (строго по timestamp колонкам)
  $sqlMetrics = "
    SELECT
      SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) AS total_created,

      SUM(CASE WHEN assistant_sent_at IS NOT NULL
                AND assistant_sent_at >= ? AND assistant_sent_at < ?
               THEN 1 ELSE 0 END) AS sent_to_assistant,

      SUM(CASE WHEN assistant_resolved_at IS NOT NULL
                AND assistant_resolved_at >= ? AND assistant_resolved_at < ?
               THEN 1 ELSE 0 END) AS assistant_resolved,

      SUM(CASE WHEN revision_requested_at IS NOT NULL
                AND revision_requested_at >= ? AND revision_requested_at < ?
               THEN 1 ELSE 0 END) AS sent_to_revision
    FROM form_submissions
  ";

  $stmt = $conn->prepare($sqlMetrics);
  if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Ошибка подготовки запроса метрик.'], JSON_UNESCAPED_UNICODE);
    exit();
  }

  $stmt->bind_param(
    "ssssssss",
    $startStr, $endStr,
    $startStr, $endStr,
    $startStr, $endStr,
    $startStr, $endStr
  );

  $stmt->execute();
  $res = $stmt->get_result();
  $m = $res->fetch_assoc() ?: [];
  $stmt->close();

  // 2) Список заявок (по выбранному типу)
  $itemsArr = [];
  if ($items) {
    // какую дату считаем "датой события" для списка
    $where = "";
    if ($list === 'created') {
      $where = "created_at >= ? AND created_at < ?";
    } elseif ($list === 'sent') {
      $where = "assistant_sent_at IS NOT NULL AND assistant_sent_at >= ? AND assistant_sent_at < ?";
    } elseif ($list === 'resolved') {
      $where = "assistant_resolved_at IS NOT NULL AND assistant_resolved_at >= ? AND assistant_resolved_at < ?";
    } else { // revision
      $where = "revision_requested_at IS NOT NULL AND revision_requested_at >= ? AND revision_requested_at < ?";
    }

    $sqlItems = "
      SELECT
        id,
        created_at,
        assistant_sent_at,
        assistant_resolved_at,
        revision_requested_at,
        surname, name, patronymic,
        problem,
        file_links
      FROM form_submissions
      WHERE $where
      ORDER BY id DESC
      LIMIT 500
    ";

    $stmt2 = $conn->prepare($sqlItems);
    if (!$stmt2) {
      echo json_encode(['success' => false, 'message' => 'Ошибка подготовки запроса списка.'], JSON_UNESCAPED_UNICODE);
      exit();
    }

    $stmt2->bind_param("ss", $startStr, $endStr);
    $stmt2->execute();

    $res2 = $stmt2->get_result();
    while ($r = $res2->fetch_assoc()) {
      $itemsArr[] = $r;
    }
    $stmt2->close();
  }

  $conn->close();

  echo json_encode([
    'success' => true,
    'period' => ['start' => $startStr, 'end' => $endStr],

    // метрики
    'total_created'     => (int)($m['total_created'] ?? 0),
    'sent_to_assistant' => (int)($m['sent_to_assistant'] ?? 0),
    'assistant_resolved'=> (int)($m['assistant_resolved'] ?? 0),
    'sent_to_revision'  => (int)($m['sent_to_revision'] ?? 0),

    // список
    'list'  => $list,
    'items' => $itemsArr
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Ошибка сервера: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
