<?php
 
namespace lib\Query;

class DatabaseQuery
{

    public function getDatabaseDic()
    {
        return "SELECT [RutaEmpresa], [NombreEmpresa] FROM [nomGenerales].[dbo].[NOM10000];";
    }

    public function getDatabaseWorkerDic()
    {
        return "SELECT w.idempleado, w.nombrelargo, w.codigoempleado, w.bajaimss, w.campoextra1 FROM nom10001 w ORDER BY w.codigoempleado;";
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

    public function getWorkerMovement ($worker_id = ':worker_id', $date_begin = ':date_begin', $date_end = ':date_end', $exercise = ':exercise', $period_type = ':period_type')
    {
        return "SELECT mv.idconcepto, mv.idperiodo, mv.importetotal FROM [nom10007] mv, [nom10002] pr WHERE mv.idempleado = {$worker_id} AND pr.idperiodo = mv.idperiodo AND pr.fechainicio BETWEEN {$date_begin} AND {$date_end} AND pr.ejercicio = {$exercise} AND mv.importetotal != 0 ". ($period_type==false?'':" AND pr.idtipoperiodo = {$period_type}");
    }
    
}