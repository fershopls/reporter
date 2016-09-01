<?php
 
namespace lib\Query;

class DatabaseQuery
{

    public function getDatabaseDic()
    {
        return "SELECT [RutaEmpresa] FROM [nomGenerales].[dbo].[NOM10000] GROUP BY [RutaEmpresa]";
    }

    public function getDatabaseWorkerDic()
    {
        return "SELECT w.idempleado, w.nombrelargo, w.codigoempleado FROM nom10001 w ORDER BY w.codigoempleado";
    }

    public function getPeriodTypeDic()
    {
        return "SELECT idtipoperiodo, nombretipoperiodo FROM nom10023 ORDER BY idtipoperiodo";
    }

    public function getPeriodDic()
    {
        return "SELECT a.idperiodo, a.ejercicio, a.idtipoperiodo, a.numeroperiodo, a.fechainicio, a.fechafin FROM nom10002 a ORDER BY a.idtipoperiodo, a.ejercicio";
    }

    public function getRegPatDic ()
    {
        return "SELECT cidregistropatronal,cregistroimss FROM NOM10035 ORDER BY cidregistropatronal";
    }

    public function getConceptDic ()
    {
        return "SELECT conc.idconcepto, conc.descripcion, conc.tipoconcepto FROM nom10004 conc ORDER BY conc.tipoconcepto";
    }

    public function getWorkerMovement ($worker_id = ':worker_id', $period_begin = ':period_begin', $period_end = ':period_end')
    {
        return "SELECT mv.idconcepto, mv.idperiodo, mv.importetotal FROM [nom10007] mv, [nom10002] pr WHERE mv.idempleado = {$worker_id} AND pr.idperiodo = mv.idperiodo AND pr.fechainicio BETWEEN {$period_begin} AND {$period_end} AND mv.importetotal != 0 ORDER BY mv.idperiodo";
    }
    
}