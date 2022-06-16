<?php

require_once 'Log.php';
include 'D:\wwwnew\Classes\PHPExcel.php';
include 'WorkDb2.php';

class ParseFile
{
    /**
     * @var array ������ ������� � ������ �� ��� ��� � ���������� ��.
     */
    private $stations;

    /**
     * @var array ������ � ���������� �� ��� �������.
     */
    private $subjects;

    /**
     * @var array ���������� ������.
     */
    private $parseData;

    /**
     * @var string ���� ������������ ������ ��� ��������.
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
            Log::Error("��������� ������ �� ����� ��������� ������ ������� �� ��");
            return false;
        }

        if (!$this->subjects) {
            Log::Error("��������� ������ �� ����� ��������� ��������� �� �� ��");
            return false;
        }

        Log::Info("�������� ������� � ������ �� ��� ��������-3 � �������� ��");

        if (!$this->parseExcel($nameFileInp, 'inp')) {
            return false;
        }

        if (!$this->parseExcel($nameFileOut, 'out')) {
            return false;
        }

    }

    /**
     * ������� ������.
     * @return bool True - ��� �������� ��������� ������ �� �����, False -��� ������������� ������.
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    private function parseExcel($nameFile, $typeDoc)
    {
        try {
            $xls = PHPExcel_IOFactory::load($this->pathToFiles . $nameFile);
            Log::Info("������� �������� ����: {$nameFile}");
        } catch (PHPExcel_Exception $excel_Exception) {
            Log::Error("��������� ������ �� ����� ��������� �����: {$nameFile}.");
            Log::Error($excel_Exception->getMessage());

            return false;
        }

        try {
            $xls->setActiveSheetIndex(0);
            $sheet = $xls->getActiveSheet();
            Log::Info("����� ������ ����");
        } catch (PHPExcel_Reader_Exception $excel_Reader_Exception) {
            Log::Error("�� ��� ������ ����������� ����.");
            Log::Error($excel_Reader_Exception->getMessage());

            return false;
        }

        $dataExcel = $sheet->toArray();

        $countItems = count($dataExcel);

        if ($countItems < 17) {
            Log::Error("�������� ��������� ����� ��� �� ������� ������. ��������� ����.");
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
                        Log::Warn("� ���� ������ ����������� ������� - {$codeStationExpress}. �������� �� � ���.");
                    }
                }
            } catch (Exception $exception) {
                Log::Error("��������� ������ �� �����  ������������ ����� � �������.");
                Log::Error($exception->getMessage());

                return false;
            }

        }

        return true;
    }

    /**
     * ��������� ������� � ������� �� ������..
     * @return array ������ � �������.
     */
    public function getParseData()
    {
        return $this->parseData;
    }

}