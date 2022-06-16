<?php

/**
 * �������� ������ ��� �������:
 *  - ����� � ������������� �����������/�������� ���������� � ������� ���������� �� ������������ �.�. �� ���������
 *  - ����� � ������������� �����������/�������� ���������� � ������� ���������� �� ������������ �.�. �� ��������� � ��������� � 2019�.
 * ������ �.�.
 * ������� 2022�.
 */

require_once 'Log.php';
include 'ParseFile.php';

Log::Info("--- Start program ---");

$date = 0;
$dateToFile = 0;

if (isset($_REQUEST['date'])) {
    $dateToFile = $_REQUEST['date']; // ���� ��� ������.
    $dateObj = new DateTime($dateToFile);
    $dateObjPr = $dateObj->modify('0 day');
    $dateToFile = $dateObjPr->format('d.m.Y');

    // ������ ����� ���� ������ ����� �������������� � ���������.
    $date = whereDate($dateToFile);

    Log::Info("���� ��� ������: {$dateToFile}   ���� ��� ��: {$date}");

} elseif (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 1) {
    Log::Info("���������� ������ �� ����� ������� �����");
    $dateObj = new DateTime();
    $dateObjPr = $dateObj->modify('-1 day');
    $dateToFile = $dateObjPr->format('d.m.Y');

    // ������ ����� ���� ������ ����� �������������� � ���������.
    $date = whereDate($dateToFile);

    Log::Info("���� ��� ������: {$dateToFile}   ���� ��� ��: {$date}");
} else {
    echo "�� ������� ���� ��� ������ � �������! ���������� ������ ���������...";
    Log::Error("�� ������� ���� ��� ������ � �������! ���������� ������ ���������...");
}

$files = getNamesFiles($dateToFile);

// 2022 ���
parseDataAndLoadToDb($date, $files[0], $files[1]);

// 2021 ���
$dateObj = new DateTime($date);
$dateObjPr = $dateObj->modify('-1 year');
$date = $dateObjPr->format('Y-m-d');
parseDataAndLoadToDb($date, $files[2], $files[3]);

// 2020 ���
$dateObj = new DateTime($date);
$dateObjPr = $dateObj->modify('-1 year');
$date = $dateObjPr->format('Y-m-d');
parseDataAndLoadToDb($date, $files[4], $files[5]);

// 2019 ���
$dateObj = new DateTime($date);
$dateObjPr = $dateObj->modify('-1 year');
$date = $dateObjPr->format('Y-m-d');
parseDataAndLoadToDb($date, $files[6], $files[7]);

echo "��������� ��������� �������";
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
        echo "�������� ������ �� ����� ��������. ��. ��� �������";
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
//        echo "�������� ����� ������ ��������� � ����� �� ������";
        Log::Info("�������� ����� ������ ��������� � ����� �� ������");
    } elseif ($dayToFile == $dayNow && $hourNow < 18) {
        $dateObj = new DateTime($dateTimeNow);
        $dateObjPr = $dateObj->modify('-1 day');
        $dateProgram = $dateObjPr->format('Y-m-d');
//        echo "������� ���� ������ ���� �� 1 ������ ���� �� ������";
        Log::Info("������� ���� ������ ���� �� 1 ������ ���� �� ������");
    } elseif ($dayToFile < $dayNow) {
        $dateObj = new DateTime($dateTimeNow);
        $dateObjPr = $dateObj->modify('-1 day');
        $dateProgram = $dateObjPr->format('Y-m-d');
//        echo "������� ���� ������ ���� �� 1 ������ ���� �� ������";
        Log::Info("������� ���� ������ ���� �� 1 ������ ���� �� ������");
    }

    return $dateProgram;
}

