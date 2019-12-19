<?php 

namespace TaskForce\Fixtures; 

use TaskForce\Exs\FileFormatException;
use TaskForce\Exs\SourceFileException;

class FileDataFormImporter 
{

    /*** СВОЙСТВА ***/

    public $filesPath; // директория/папка с файлами
    public $fileNames; // массив названий файлов в директории
    public $fileName; // полное имя файла, все выбранные файлы
    public $fileNo; // номер в директротии первый файл
    public $mergedFilesNo; // массив номеров файлов, выбранных для слияния
    private $fp; // подключение к файлу
    private $header_data = []; // первая строка выбранного файла, буфер-переменные
    private $row_data = []; // все строки выбранного файла, буфер-переменные

    private $error = null;

    public function __construct (string $filesPath, ?string $fileNo, ?array $mergedFilesNo) {

        $this->filesPath = $filesPath; // директория файлов, папка со всеми файлами
        $this->fileNo = $fileNo; // первый выбранный файл в первом поле формы (кнопка выбрать)
        $this->mergedFilesNo = $mergedFilesNo; // массив с номерами для слияния с первый файл, добавляют колонки, количество строк должно совпадать
    }

    // Директория/папка абсолютный адрес от корня системы
    public function getFilesPath(): ?string {
        return $this->filesPath;
    }
    // Название файла
    public function getFileName(): ?string {
        return $this->fileName;
    }

    // Получение имен файлов в директории
    public function getFilesList(): array {
        return array_slice(scandir($this->filesPath), 2);
    }

    // Проверка верности номера файлов введенных в поля
    public function selectFileName($filesNo): ?string {

        if(!isset($this->getFilesList()[$filesNo])) {
            throw new SourceFileException('Wrong file number!');
        }

        return $this->fileName = $this->filesPath . '/' . $this->getFilesList()[$filesNo] ?? NULL;  
    }

    // Массив со списком выбранных файлов, включая первый
    public function getSelectedFiles(): ?array {
        $mergedFilesNo = array_filter($this->mergedFilesNo, function($value) {return  $value !== '' ?? $value;});
        array_unshift($mergedFilesNo, $this->fileNo);

        if($mergedFilesNo !== array_unique($mergedFilesNo)) {
            throw new SourceFileException('Есть повторяющиеся файлы!');
        }

        $mergedFiles = [];
        foreach($mergedFilesNo as $key => $value) {
            $this->selectFileName($value);
            $mergedFiles[] = $this->fileName;
        }

        return $mergedFiles;
    }

    // Строка запроса и форма с полями
    public function pageForma (): void {
        $sum = count($this->getFilesList());
        $number = $this->fileNo;
        echo "
        <form method='GET' action='http://localhost/_test_csvToSql_form.php'>
        ";

        echo "
        Найдено $sum файл(ов). Введите номер для загрузки: 
        <br><input type='text' name='fileNo' value='$number'>
        <button type='submit' name='button' value='select'>Выбрать</button>
        ";

        if(count($this->mergedFilesNo)) {
            echo "<br>Объединение с файлами (добавление столбцов в 1ый файл)";
            foreach($this->mergedFilesNo as $key => $value) {
                echo "<br><input type='text' name='file-$key' value='$value'> ";
                echo "<button type='submit' name='delete' value='$key'>Удалить</button> 
                ";
            }

            echo "<br><br><button type='submit' name='button' value='select'>Слияние</button> ";
        }

        echo "<button type='submit' name='add' value='add'>Добавить</button> 
        </form>";
    } 

    // Собираем все Выбранные файлы - Загружамем данные в буфер-переменные data
    public function mergeFiles() {
        $mergedFiles = $this->getSelectedFiles();

        // Слияние файлов
        foreach($mergedFiles as $key => $value) {

            // Берем данные из буфера-переменных, если они не пусты
            $header_data_before = $this->header_data;
            $row_data_before = $this->row_data;

            // Читаем данные из каждого файла и загружаем в буфер-переменные
            $this->importIntoBuffer($value);

            $this->header_data = array_merge($header_data_before, $this->header_data);

            if(!empty($row_data_before)) {

                if(count($row_data_before) !== count($this->row_data) ) {
                    throw new SourceFileException('Файлы не совместимы по количеству строк!');
                }

                for($i=0; $i < count($row_data_before); $i++) {
                    $this->row_data[$i] = array_merge($row_data_before[$i], $this->row_data[$i]);
                }
            }           
        }

    }

    // Проверка каждого файла, чтение, отправка в буфер-переменные
    public function importIntoBuffer($fileName): void
    {
        echo "<br>Загрузка ... $fileName";

        if (!file_exists($fileName)) {
            throw new SourceFileException('Файл не существует - ' . $fileName);
        }

        $this->fp = fopen($fileName, 'r');
        if (!$this->fp) {
            throw new SourceFileException("Не удалось открыть файл на чтение");
        }

        $this->header_data = $this->getHeaderData();

        $lines = [];
        while ($line = $this->getNextLine()) {
            $lines[] = $line;
        }
        $this->row_data = $lines;
    }

    private function getNextLine(): ?array {
        $data = fgetcsv($this->fp, ',');
        // При последнем проходе влюбом случае возвратит false 2 раза, поэтому просим вернуть NULL
        if($data) {
            return $data;
        }
        return NULL;
    }

    private function getHeaderData():?array {
        rewind($this->fp);
        $data = fgetcsv($this->fp);
        if (!$data) {
            throw new FileFormatException("Заголовки не верны или отсутствуют");
        }
        return $data;
    }

    // Печатать SQL код файлов
    // пример ('city','latitude','longitude')
    public function convertInSQL(): void {
        print '<br>';
        $sql_head = "INSERT INTO " . pathinfo($this->selectFileName($this->fileNo))['filename'] 
        . " <br>(" . trim(implode(",", $this->header_data)) . ")<br>" ;

        printPre($sql_head);

        $sql_values = "VALUES <br>";
        for ($i=0; $i < count($this->row_data) - 1; $i++) {
            $sql_values .=  "('" . implode("','", $this->row_data[$i]) . "'),<br>";
        }
        $sql_values .= "('" . implode("','", array_pop($this->row_data)) . "');";

        printPre($sql_values);
    }


    public function convertInCSV(): void {
        print '<br>';
        print(trim(implode(",", $this->header_data)) . "<br>");

        foreach ($this->row_data as $row) {
            print(implode(",", $row) . "<br>");
        }
    }

    public function convertInCSV2(): void {
        print '<br>';
        print(trim(implode(",", $this->header_data)) . "<br>");

        for ($i=0; $i < count($this->row_data) - 1; $i++) {
            print '"' . implode('","', $this->row_data[$i]) . '"<br>';
        }
        print '"' . implode('","', array_pop($this->row_data)) . '"<br>';
    }

}