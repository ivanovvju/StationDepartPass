<?php

require_once 'Log.php';
include 'D:\wwwnew\Classes\PHPExcel.php';
include 'WorkDb2.php';

class ParseFile
{
    /**
     * @var array Массив станций с кодами из АРМ КФР и субъектами РФ.
     */
    private $stations;

    /**
     * @var array Массив с субъектами РФ для справки.
     */
    private $subjects;

    /**
     * @var array Спарсенные данные.
     */
    private $parseData;

    /**
     * @var string Путь расположения файлов для парсинга.
     */
    private $pathToFiles;

    public function __construct($nameFileInp, $nameFileOut)
    {
        $this->pathToFiles = "E:\Diskor\doc_any\out\ARM-KFR\\";
        
        $workDb2 = new WorkDb2();

        if ($this->stations == null) {
            $this->stations = $workDb2->getStationsExpress();
        }

        if ($this->subjects == null) {
            $this->subjects = $workDb2->getSubjectsCountry();
        }

        if (!$this->stations) {
            Log::Error("Произошла ошибка во время получения списка станций из БД");
            return false;
        }

        if (!$this->subjects) {
            Log::Error("Произошла ошибка во время получения субъектов РФ из БД");
            return false;
        }

        Log::Info("Получили станции с кодами из АРМ Экспресс-3 и субъекты РФ");

        if (!$this->parseExcel($nameFileInp, 'inp')) {
            return false;
        }

        if (!$this->parseExcel($nameFileOut, 'out')) {
            return false;
        }

    }

    /**
     * Парсинг файлов.
     * @return bool True - При успешном получении данных из файла, False -При возникновении ошибок.
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    private function parseExcel($nameFile, $typeDoc)
    {
        try {
            $xls = PHPExcel_IOFactory::load($this->pathToFiles . $nameFile);
            Log::Info("Успешно получили файл: {$nameFile}");
        } catch (PHPExcel_Exception $excel_Exception) {
            Log::Error("Произошла ошибка во время получения файла: {$nameFile}.");
            Log::Error($excel_Exception->getMessage());

            return false;
        }

        try {
            $xls->setActiveSheetIndex(0);
            $sheet = $xls->getActiveSheet();
            Log::Info("Нашли нужный лист");
        } catch (PHPExcel_Reader_Exception $excel_Reader_Exception) {
            Log::Error("Не был найден необходимый лист.");
            Log::Error($excel_Reader_Exception->getMessage());

            return false;
        }

        $dataExcel = $sheet->toArray();

        $countItems = count($dataExcel);

        if ($countItems < 17) {
            Log::Error("Неверная структура файла или не хватает данных. Проверьте файл.");
            return false;
        }

        $itogo = $dataExcel[$countItems - 1];
        $sutki = $dataExcel[$countItems - 2];

        for ($column = 1; $column < count($itogo); $column++) {
            try {
                $station = iconv("UTF-8", "cp1251", trim($dataExcel[14][$column]));
                if (strlen($station) > 10) {
                    $codeStationExpress = substr($station, 0, 7);

                    if (isset($this->stations[$codeStationExpress])) {
                        $this->parseData[$typeDoc][$codeStationExpress]['SUTKI'] = $sutki[$column];
                        $this->parseData[$typeDoc][$codeStationExpress]['ITOGO'] = $itogo[$column];
                    } else {
                        Log::Warn("В базе данных отсутствует станция - {$codeStationExpress}. Добавьте ее в НСИ.");
                    }
                }
            } catch (Exception $exception) {
                Log::Error("Произошла ошибка во время  формирования файла с данными.");
                Log::Error($exception->getMessage());

                return false;
            }

        }

        return true;
    }

    /**
     * Получение массива с данными из файлов..
     * @return array Массив с данными.
     */
    public function getParseData()
    {
        return $this->parseData;
    }

}