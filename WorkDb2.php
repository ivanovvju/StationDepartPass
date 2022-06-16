<?php

require_once 'Log.php';
include 'D:\wwwnew\libPHP\AllDatabase.php';

/**
 * Class WorkDb2 Работа с БД.
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
     * Станции, которые имеют код из АРМ Экспресс-3.
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
     * Субъекты РФ, которые введены в БД.
     * @return array|false - Массив с субъектами РФ, либо false, если были ошибки.
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
     * Запись данных в БД.
     * @param $date string Отчетная дата.
     * @param $dataToDb array Данные для записи.
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
            Log::Info("Удаляем данные из БД за {$date}");

            $sqlDel = "DELETE FROM muratov.kfr_move_pass WHERE report_date = '{$date}'";

            try {
                Log::Info($sqlDel);
                $result = $this->connectDoclad->select($sqlDel);
            } catch (Exception $exception) {
                Log::Error("Не удалось удалить данные из таблицы за {$date}");
                Log::Error($exception->getMessage());

                return false;
            }
            Log::Info("Успешно очистили данные из БД за {$date}");
            // ---- Конец блока с удалением.


            Log::Info("Подготавливаем данные для INSERT в БД за {$date}");

            $lineValues = implode(',', $valuesInsert);
            $sqlIns = "INSERT INTO muratov.kfr_move_pass (code_stan_express_3, input, output, report_date) VALUES {$lineValues}";
            try {
                Log::Info($sqlIns);
                $result = $this->connectDoclad->select($sqlIns);
            } catch (Exception $exception) {
                Log::Error("Не удалось вставить данные в таблицу за {$date}");
                Log::Error($exception->getMessage());

                return false;
            }
            Log::Info("Успешно вставили данные в БД за {$date}");
            // ---- Конец блока с вставкой данных в БД.


        } else {
            Log::Error("Массив с данными для INSERT пуст!");

            return false;
        }

        return $result;

    }

}