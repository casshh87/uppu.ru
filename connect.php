<?php
/*
Открыть SQLite: Если SQLite уже установлен, выполните команду:

sqlite3 database.db

SQLite version 3.x.x 2024-xx-xx 12:00:00
Enter ".help" for usage hints.
sqlite>
Создать таблицу: В консоли выполните SQL-запрос:


CREATE TABLE IF NOT EXISTS files (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    size INTEGER NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    comment TEXT,
    path TEXT NOT NULL,
    type TEXT
);

После нажатия Enter, если запрос выполнен успешно, ничего не будет выведено (SQLite тихо принимает команды, если нет ошибок).

Проверить, что таблица создана: Выполните команду, чтобы увидеть список таблиц:

.tables
Вы должны увидеть files в списке.

Посмотреть структуру таблицы: Чтобы убедиться, что таблица создана с нужными столбцами:

PRAGMA table_info(files);
*/

class DB
{
    private $db;
    
    function __construct($dsn, $username, $password)
    {
        try {
            
            $this->db = new PDO($dsn, $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            
            echo "Ошибка подключения к базе данных: " . $e->getMessage();
        }
    }
    public function Conn()
    {
        return $this->db;
    }
}

/*
$DB = new DB("pgsql:host=postgres-container;dbname=postgres", "admin", "root");
$conn = $DB->Conn();

if ($conn) {
    echo "Подключение успешно!";
} else {
    echo "Ошибка подключения к базе данных.";
}
*/

?>