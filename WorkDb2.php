<?php

require_once 'Log.php';
include 'D:\wwwnew\libPHP\AllDatabase.php';

/**
 * Class WorkDb2 ������ � ��.
 */
class WorkDb2
{
    /**
     * @var AlLDatabase
     */
    protected $connectDoclad;

    public function __construct()
    {
        $this->connectDoclad = new AlLDatabase('DOCLAD');
        $this->connectDoclad->connect();
    }

    /**
     * �������, ������� ����� ��� �� ��� ��������-3.
     */
    public function getStationsExpress()
    {
        $data = array();

        $sql = "
            SELECT code_stan_express, code_subject, naim_stan, name_region
            FROM nsi_api.stations
            LEFT JOIN (
                SELECT id_reg, name_region FROM muratov.kfr_list_regions
            )
            ON id_reg = code_subject
            WHERE code_subject IS NOT NULL
        ";

        try {
            $result = $this->connectDoclad->select($sql);

            foreach ($result as $item) {
                $codeStationexpress = $item['CODE_STAN_EXPRESS'];
                $nameStation = $item['NAIM_STAN'];
                $nameSubject = $item['NAME_REGION'];

                $data[$codeStationexpress]['NAME_STATION'] = $nameStation;
                $data[$codeStationexpress]['NAME_SUBJECT'] = $nameSubject;
            }
        } catch (Exception $exception) {
            Log::Error($exception->getMessage());

            return false;
        }

        return $data;
    }

    /**
     * �������� ��, ������� ������� � ��.
     * @return array|false - ������ � ���������� ��, ���� false, ���� ���� ������.
     */
    public function getSubjectsCountry()
    {
        $data = array();

        $sql = "
            SELECT id_reg, name_region
            FROM muratov.kfr_list_regions
        ";

        try {
            $result = $this->connectDoclad->select($sql);
            foreach ($result as $item) {
                $codeSubject = $item['ID_REG'];
                $nameSubject = $item['NAME_REGION'];

                $data[$codeSubject]['NAME_SUBJECT'] = $nameSubject;
            }
        } catch (Exception $exception) {
            Log::Error($exception->getMessage());

            return false;
        }

        return $data;
    }

    /**
     * ������ ������ � ��.
     * @param $date string �������� ����.
     * @param $dataToDb array ������ ��� ������.
     */
    public function insertWithYear($date, $dataToDb)
    {
        $stations = $this->getStationsExpress();

        $valuesInsert = array();
        $result = null;

        foreach ($stations as $codeStationExpress => $infoStation) {
            $itogoInput = (isset($dataToDb['inp'][$codeStationExpress]['ITOGO'])) ? $dataToDb['inp'][$codeStationExpress]['ITOGO'] : 0;
            $itogoOutput = (isset($dataToDb['out'][$codeStationExpress]['ITOGO'])) ? $dataToDb['out'][$codeStationExpress]['ITOGO'] : 0;
            $valuesInsert[] = "({$codeStationExpress}, {$itogoInput}, {$itogoOutput}, '{$date}')";
        }

        if (count($valuesInsert) > 0) {
            Log::Info("������� ������ �� �� �� {$date}");

            $sqlDel = "DELETE FROM muratov.kfr_move_pass WHERE report_date = '{$date}'";

            try {
                Log::Info($sqlDel);
                $result = $this->connectDoclad->select($sqlDel);
            } catch (Exception $exception) {
                Log::Error("�� ������� ������� ������ �� ������� �� {$date}");
                Log::Error($exception->getMessage());

                return false;
            }
            Log::Info("������� �������� ������ �� �� �� {$date}");
            // ---- ����� ����� � ���������.


            Log::Info("�������������� ������ ��� INSERT � �� �� {$date}");

            $lineValues = implode(',', $valuesInsert);
            $sqlIns = "INSERT INTO muratov.kfr_move_pass (code_stan_express_3, input, output, report_date) VALUES {$lineValues}";
            try {
                Log::Info($sqlIns);
                $result = $this->connectDoclad->select($sqlIns);
            } catch (Exception $exception) {
                Log::Error("�� ������� �������� ������ � ������� �� {$date}");
                Log::Error($exception->getMessage());

                return false;
            }
            Log::Info("������� �������� ������ � �� �� {$date}");
            // ---- ����� ����� � �������� ������ � ��.


        } else {
            Log::Error("������ � ������� ��� INSERT ����!");

            return false;
        }

        return $result;

    }

}