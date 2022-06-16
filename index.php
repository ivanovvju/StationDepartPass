<?php

/**
 * Загрузка данных для справки:
 *  - ОТЧЕТ О ПОСТАНЦИОННОМ ОТПРАВЛЕНИИ/ПРИБЫТИИ ПАССАЖИРОВ В ДАЛЬНЕМ СЛЕДОВАНИИ ПО КУЙБЫШЕВСКОЙ Ж.Д. ПО СУБЪЕКТАМ
 *  - ОТЧЕТ О ПОСТАНЦИОННОМ ОТПРАВЛЕНИИ/ПРИБЫТИИ ПАССАЖИРОВ В ДАЛЬНЕМ СЛЕДОВАНИИ ПО КУЙБЫШЕВСКОЙ Ж.Д. ПО СУБЪЕКТАМ В СРАВНЕНИИ С 2019г.
 * Иванов В.Ю.
 * февраль 2022г.
 */

require_once 'Log.php';
include 'ParseFile.php';

Log::Info("--- Start program ---");

$date = 0;
$dateToFile = 0;

if (isset($_REQUEST['date'])) {
    $dateToFile = $_REQUEST['date']; // Дата для файлов.
    $dateObj = new DateTime($dateToFile);
    $dateObjPr = $dateObj->modify('0 day');
    $dateToFile = $dateObjPr->format('d.m.Y');

    // решаем какая дата должна далее использоваться в программе.
    $date = whereDate($dateToFile);

    Log::Info("Дата для файлов: {$dateToFile}   Дата для БД: {$date}");

} elseif (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 1) {
    Log::Info("Произведен запуск ПО через шедулер задач");
    $dateObj = new DateTime();
    $dateObjPr = $dateObj->modify('-1 day');
    $dateToFile = $dateObjPr->format('d.m.Y');

    // решаем какая дата должна далее использоваться в программе.
    $date = whereDate($dateToFile);

    Log::Info("Дата для файлов: {$dateToFile}   Дата для БД: {$date}");
} else {
    echo "Не указана дата для работы с файлами! Прекращаем работу программы...";
    Log::Error("Не указана дата для работы с файлами! Прекращаем работу программы...");
}

$files = getNamesFiles($dateToFile);

// 2022 год
parseDataAndLoadToDb($date, $files[0], $files[1]);

// 2021 год
$dateObj = new DateTime($date);
$dateObjPr = $dateObj->modify('-1 year');
$date = $dateObjPr->format('Y-m-d');
parseDataAndLoadToDb($date, $files[2], $files[3]);

// 2020 год
$dateObj = new DateTime($date);
$dateObjPr = $dateObj->modify('-1 year');
$date = $dateObjPr->format('Y-m-d');
parseDataAndLoadToDb($date, $files[4], $files[5]);

// 2019 год
$dateObj = new DateTime($date);
$dateObjPr = $dateObj->modify('-1 year');
$date = $dateObjPr->format('Y-m-d');
parseDataAndLoadToDb($date, $files[6], $files[7]);

echo "Программа выполнена успешно";
Log::Info("--- End program ---");


function getNamesFiles($date)
{
    $dateObj = new DateTime($date);
    $dateObjPr = $dateObj->modify('0 day');
    $dateForFile = $dateObjPr->format('d.m.Y');

    $files[] = "inp_pass_{$dateForFile}.xls";
    $files[] = "out_pass_{$dateForFile}.xls";

    // ---------------

    $dateObj = new DateTime($dateForFile);
    $dateObjPr = $dateObj->modify('-1 year');
    $dateForFile = $dateObjPr->format('d.m.Y');

    $files[] = "inp_pass_{$dateForFile}.xls";
    $files[] = "out_pass_{$dateForFile}.xls";

    // ---------------

    $dateObj = new DateTime($dateForFile);
    $dateObjPr = $dateObj->modify('-1 year');
    $dateForFile = $dateObjPr->format('d.m.Y');

    $files[] = "inp_pass_{$dateForFile}.xls";
    $files[] = "out_pass_{$dateForFile}.xls";

    // ---------------

    $dateObj = new DateTime($dateForFile);
    $dateObjPr = $dateObj->modify('-1 year');
    $dateForFile = $dateObjPr->format('d.m.Y');

    $files[] = "inp_pass_{$dateForFile}.xls";
    $files[] = "out_pass_{$dateForFile}.xls";

    return $files;
}

function parseDataAndLoadToDb($date, $fileInput, $fileOutput)
{
    $parse = new ParseFile($fileInput, $fileOutput);
    $dataWithYear = $parse->getParseData();
    if (!$dataWithYear) {
        echo "Возникли ошибки во время парсинга. См. лог парсера";
        exit();
    }
    $db = new WorkDb2();
    $result = $db->insertWithYear($date, $dataWithYear);
}

function whereDate($date)
{
    $dateTimeNow = '2022-02-03 00:20:00';
    $dateTimeNow = date('Y-m-d', time());

    $dateProgram = $date;

    $dayToFile = date('j', strtotime($date));
    $dayNow = date('j', strtotime($dateTimeNow));
    $hourNow = date('G', strtotime($dateTimeNow));

    if ($dayToFile == $dayNow && $hourNow >= 18) {
        $dateObj = new DateTime($dateTimeNow);
        $dateObjPr = $dateObj->modify('0 day');
        $dateProgram = $dateObjPr->format('Y-m-d');
//        echo "Отчетные сутки должны совпадать с датой на файлах";
        Log::Info("Отчетные сутки должны совпадать с датой на файлах");
    } elseif ($dayToFile == $dayNow && $hourNow < 18) {
        $dateObj = new DateTime($dateTimeNow);
        $dateObjPr = $dateObj->modify('-1 day');
        $dateProgram = $dateObjPr->format('Y-m-d');
//        echo "Отчетна дата должна быть на 1 меньше даты на файлах";
        Log::Info("Отчетна дата должна быть на 1 меньше даты на файлах");
    } elseif ($dayToFile < $dayNow) {
        $dateObj = new DateTime($dateTimeNow);
        $dateObjPr = $dateObj->modify('-1 day');
        $dateProgram = $dateObjPr->format('Y-m-d');
//        echo "Отчетна дата должна быть на 1 меньше даты на файлах";
        Log::Info("Отчетна дата должна быть на 1 меньше даты на файлах");
    }

    return $dateProgram;
}

