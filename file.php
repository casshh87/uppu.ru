<?php
require 'vendor/autoload.php';
include "connect.php";

class FileHandler
{
    private $pdo;
    private $getID3;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->getID3 = new getID3();
    }

    public function displayFileInfo($fileId)
    {
        $file = $this->getFileById($fileId);

        if (!$file) {
            echo "<p>Файл не найден.</p>";
            return;
        }

        $this->renderFileInfo($file);

        if (!file_exists($file['path'])) {
            echo "<p>Файл не найден на сервере.</p>";
            return;
        }

        $filePath = $this->getRelativePath($file['path']);
        $fileExtension = strtolower(pathinfo($file['path'], PATHINFO_EXTENSION));

        $this->renderFileContent($file, $filePath, $fileExtension);

        echo "<p><a href='" . htmlspecialchars($filePath) . "' download>Скачать файл</a></p>";
    }

    private function getFileById($fileId)
    {
        $stmt = $this->pdo->prepare('SELECT id, name, date, size, path, comment FROM files WHERE id = :id');
        $stmt->execute(['id' => $fileId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getRelativePath($absolutePath)
    {
        return str_replace($_SERVER['DOCUMENT_ROOT'], '', $absolutePath);
    }

    private function renderFileInfo($file)
    {
        echo "<h1>Информация о файле</h1>";
        echo "<p><strong>Имя файла:</strong> " . htmlspecialchars($file['name']) . "</p>";
        echo "<p><strong>Дата загрузки:</strong> " . htmlspecialchars($file['date']) . "</p>";
        echo "<p><strong>Размер:</strong> " . htmlspecialchars($file['size']) . " МБ</p>";
        echo "<p><strong>Комментарий автора:</strong> " . htmlspecialchars($file['comment']) . "</p>";
    }

    private function renderFileContent($file, $filePath, $fileExtension)
    {
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

        if (in_array($fileExtension, $imageTypes)) {
            $this->renderImage($filePath);
        } else {
            $this->analyzeFile($file);
        }
    }

    private function renderImage($filePath)
    {
        echo "<p><strong>Изображение:</strong></p>";
        echo "<img src='" . htmlspecialchars($filePath) . "' alt='Изображение' style='width: 150px; height: 150px; object-fit: cover;'>";
    }

    private function analyzeFile($file)
    {
        $fileInfo = $this->getID3->analyze($file['path']);

        if (isset($fileInfo['error'])) {
            echo "<p><strong>Ошибка анализа файла:</strong> " . implode(', ', $fileInfo['error']) . "</p>";
            return;
        }

        echo "<h2>Дополнительная информация о файле:</h2>";

        if (isset($fileInfo['audio'])) {
            echo "<p><strong>Аудио формат:</strong> " . htmlspecialchars($fileInfo['audio']['dataformat'] ?? 'Неизвестно') . "</p>";
            echo "<p><strong>Кодек:</strong> " . htmlspecialchars($fileInfo['audio']['codec'] ?? 'Неизвестно') . "</p>";
            echo "<p><strong>Частота:</strong> " . htmlspecialchars($fileInfo['audio']['sample_rate'] ?? 'Неизвестно') . " Гц</p>";
        }

        if (isset($fileInfo['video'])) {
            echo "<p><strong>Видео формат:</strong> " . htmlspecialchars($fileInfo['video']['dataformat'] ?? 'Неизвестно') . "</p>";
            echo "<p><strong>Ширина:</strong> " . htmlspecialchars($fileInfo['video']['resolution_x'] ?? 'Неизвестно') . " px</p>";
            echo "<p><strong>Высота:</strong> " . htmlspecialchars($fileInfo['video']['resolution_y'] ?? 'Неизвестно') . " px</p>";
        }

        echo "<p><strong>Длительность:</strong> " . gmdate("H:i:s", (int)($fileInfo['playtime_seconds'] ?? 0)) . "</p>";
    }
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Неверный идентификатор файла.";
    exit;
}

$fileId = (int)$_GET['id'];
$DB = new DB('sqlite:Database.db', null, null);
$pdo = $DB->Conn();
$fileHandler = new FileHandler($pdo);
$fileHandler->displayFileInfo($fileId);
