<?php

include 'connect.php';

$uploadsDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
$name = $_FILES['file']['name'];
$name = uniqid('file_', true) . '.' . $fileExtension; //заменяем имя файла на уникальное значение
$size = $_FILES['file']['size'] / 1048576;
$size = round($size, 2); // Округляем до двух знаков после запятой
$path = $uploadsDir . basename($name); // Полный путь, по которому будет сохранен файл
$type = $_FILES['file']['type'];
$date = date("Y-m-d H:i:s"); // Получаем текущую дату и время
$comment = $_POST['comment'];

$maxFileSize = 20 * 1024 * 1024; // 20 MB
$maxCommentLength = 200; 


if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die("Ошибка загрузки файла.");
}


if ($_FILES['file']['size'] > $maxFileSize) {
    die("Файл слишком большой. Максимальный размер: 20 MB.");
}


$comment = trim($_POST['comment'] ?? '');
if (strlen($comment) > $maxCommentLength) {
    die("Комментарий слишком длинный. Максимальная длина: 200 символов.");
}

// Защита от XSS
$comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');


class File
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function addFile($name, $size, $path, $type, $date, $comment)
    {
        $sql = "INSERT INTO files (name, size, path, type, date, comment) VALUES (:name, :size, :path, :type, :date, :comment)";

        $stmt = $this->db->prepare($sql);

        $stmt->bindparam(':name', $name);
        $stmt->bindparam(':size', $size);
        $stmt->bindparam(':path', $path); 
        $stmt->bindparam(':type', $type);
        $stmt->bindparam(':date', $date);
        $stmt->bindparam(':comment', $comment);
        $stmt->execute();
        move_uploaded_file($_FILES['file']['tmp_name'], $path);
        header('Location: file_list.php');
        exit;
    }
}

$DB = new DB('sqlite:Database.db', null, null);
$conn = $DB->Conn();
$file = new File($conn);

$file->addFile($name, $size, $path, $type, $date, $comment); 