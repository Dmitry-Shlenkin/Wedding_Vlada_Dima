<?php
// save_rsvp.php - сохраняет данные RSVP в текстовый файл
// Формат записи: ========== ЗАПИСЬ ========== | Дата и время | Имя гостя | Email | Статус | Пожелания | Приглашен как | Персональное сообщение

header('Content-Type: application/json');

// Файл для хранения данных
$filename = 'rsvp_records.txt';

// Получаем данные из POST
$guestName = isset($_POST['guest_name']) ? trim($_POST['guest_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$attendance = isset($_POST['attendance']) ? trim($_POST['attendance']) : '';
$wishes = isset($_POST['wishes']) ? trim($_POST['wishes']) : '';
$invitedAs = isset($_POST['invited_as']) ? trim($_POST['invited_as']) : '';
$personalMessage = isset($_POST['personal_message']) ? trim($_POST['personal_message']) : '';

// Валидация
if (empty($guestName)) {
    echo json_encode(['success' => false, 'message' => 'Пожалуйста, укажите ваше имя.']);
    exit;
}

if (empty($attendance)) {
    echo json_encode(['success' => false, 'message' => 'Пожалуйста, выберите вариант присутствия.']);
    exit;
}

// Преобразуем статус в читаемый вид
$attendanceText = '';
switch ($attendance) {
    case 'yes': $attendanceText = 'Приду'; break;
    case 'no': $attendanceText = 'Не смогу прийти'; break;
    case 'plus': $attendanceText = 'Приду с партнёром/партнёршей'; break;
    default: $attendanceText = $attendance;
}

// Экранируем специальные символы для безопасного сохранения
function escapeForTxt($str) {
    if (empty($str)) return '-';
    $str = str_replace(["\r\n", "\n", "\r"], ' ', $str);
    $str = str_replace('|', '\\|', $str);
    return trim($str);
}

// Формируем строку записи с разделителями
// Формат: === ЗАПИСЬ N === | ДАТА | ИМЯ | EMAIL | СТАТУС | ПОЖЕЛАНИЯ | ПРИГЛАШЕН КАК | СООБЩЕНИЕ
$separator = ' | ';
$recordNumber = 0;

// Подсчитываем существующие записи для нумерации
if (file_exists($filename)) {
    $content = file_get_contents($filename);
    $recordNumber = substr_count($content, '========== ЗАПИСЬ');
}

$date = date('Y-m-d H:i:s');
$recordData = "========== ЗАПИСЬ " . ($recordNumber + 1) . " ==========\n";
$recordData .= "Дата и время: " . $date . "\n";
$recordData .= "--- ДАННЫЕ ГОСТЯ ---\n";
$recordData .= "Имя гостя: " . escapeForTxt($guestName) . "\n";
$recordData .= "Email: " . escapeForTxt($email) . "\n";
$recordData .= "Статус RSVP: " . escapeForTxt($attendanceText) . "\n";
$recordData .= "Пожелания/комментарии: " . escapeForTxt($wishes) . "\n";
$recordData .= "--- ИНФОРМАЦИЯ О ПРИГЛАШЕНИИ ---\n";
$recordData .= "Приглашен как: " . escapeForTxt($invitedAs) . "\n";
$recordData .= "Персональное сообщение: " . escapeForTxt($personalMessage) . "\n";
$recordData .= str_repeat('-', 50) . "\n\n";

// Сохраняем в файл
if (file_put_contents($filename, $recordData, FILE_APPEND | LOCK_EX)) {
    echo json_encode(['success' => true, 'message' => 'Спасибо! Ваш ответ сохранен.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении. Пожалуйста, попробуйте позже.']);
}
?>