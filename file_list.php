<?php

include "connect.php";

class File
{
    public $id;
    public $name;
    public $date;
    public $size;
    public $path;
}

class FileHandler
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getFiles($limit = 100)
    {
        $stmt = $this->pdo->prepare('SELECT id, name, date, size, path FROM files LIMIT :limit');
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'File');
    }

    public function renderFileTable($files)
    {
        echo "<div class='back-link'>";
        echo "<a href='index.html'>Вернуться на главную</a>";
        echo "</div>";
        echo "</div>";

        echo "<div class='container'>";
        echo "<h1>Список загруженных файлов</h1>";
        echo "<table class='file-table'>";
        echo "<tr class='file-table-header'>";
        echo "<th>Имя файла</th>";
        echo "<th>Дата загрузки</th>";
        echo "<th>Размер (МБ)</th>";
        echo "<th>Просмотр</th>";
        echo "</tr>";

        foreach ($files as $file) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($file->name) . "</td>";
            echo "<td>" . htmlspecialchars($file->date) . "</td>";
            echo "<td>" . htmlspecialchars($file->size) . " МБ</td>";
            echo "<td><a href='file.php?id=" . htmlspecialchars($file->id) . "' class='view-link'>Открыть</a></td>";
            echo "</tr>";
        }

        echo "</table>";
       
    }
}


$DB = new DB('sqlite:Database.db', null, null);
$pdo = $DB->Conn();

// Создаем экземпляр FileHandler
$fileHandler = new FileHandler($pdo);

// Получаем список файлов
$files = $fileHandler->getFiles();

// Подключаем CSS
echo "<link rel='stylesheet' href='styles/file_list.css'>";

// Отображаем таблицу файлов
$fileHandler->renderFileTable($files);

?>
