<?php

include "../classes/db.php";

try
{
    //Open database connection
    $db = new db();
    
    $site     = filter_input(INPUT_POST, 'site');
    $period   = filter_input(INPUT_POST, 'period');
    $inperiod = filter_input(INPUT_POST, 'inperiod');
    $detail   = filter_input(INPUT_POST, 'detail');
    $deep     = filter_input(INPUT_POST, 'deep');
    
    if (isset($deep)) {
        $rows = array();
        $rows[week]  = $db->get_option_period($per = 'week', $deep);
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['Options'] = $rows;
        print json_encode($jTableResult);
        return;
    }
    
    $jTableResult = array();
    if (!isset($site) && !isset($period) && !isset($inperiod)){
        $jTableResult['Result'] = "OK";
        print json_encode($jTableResult);
        return;
    }
    
    date_default_timezone_set('Asia/Almaty');
    
    $weekend = 1;
    // Склад номер 2 в кухне, периоды по неделям
    $db->setcurdate(strtotime("-$period week"));
    $begin = $db->getdate(0, 0); // Monday, 00:00:00
    $end = $db->getdate(6, 3); // Sunday, 23:59:59
//    if ($site == '2') {
//        if ($inperiod == '1') { // если Будние дни
//            // Интервал с Понедельника, 00:00:00 по Пятницу, 11:59:59 
//            $begin = $db->getdate(0, 0); // Monday, 00:00:00
//            $end   = $db->getdate(4, 1); // Friday, 11:59:59
//            $weekend = 0;
//        }
//        if ($inperiod == '2') { // если Выходные дни
//            // Интервал с Пятницы, 12:00:00 по Воскресенье, 23:59:59  
//            $begin = $db->getdate(4, 2); // Friday, 12:00:00
//            $end = $db->getdate(6, 3); // Sunday, 23:59:59
//            $weekend = 1;
//        }
//    } elseif ($site == '1') {
////        $cmonth = date("n"); // текущий месяц
////        $lmonth = $cmonth - $period; // прошлые месяца
////        $begin = date("d.m.Y G:i:s", strtotime(date("Y-$lmonth-1"))); // начало месяца
////        $end   = date("d.m.Y 23:59:59", strtotime(date("Y-$lmonth-t"))); // конец месяца
//        $begin = $db->getdate(0, 0); // Monday, 00:00:00
//        $end = $db->getdate(6, 3); // Sunday, 23:59:59
//    }
    
    $where = "ord.Date BETWEEN Convert(datetime,'$begin',103) AND Convert(datetime,'$end',103) AND ord.SiteId = $site";
    $lsite = (isset($site) AND $site != "0") ? $where : "1=1";
    
//    if (val === '1'){
//        period = '<option value="2">Текущий месяц</option>'+
//              '<option value="3">Прошлый месяц</option>';
//        inperiod = '<option value="0">Весь период</option>';
//    } 
//    if (val === '2'){
//        period = '<option value="0">Текущая неделя</option>'+
//              '<option value="1">Прошлая неделя</option>';
//        inperiod = '<option value="1">Будние дни</option>'+
//              '<option value="2">Выходные дни</option>';
//    } 
    $length = strlen(db::DEPPREFIX) + 1;
    if ($detail == 0){
        $sql = "SELECT b.OrderId AS Id, b.FIO AS FIO, b.Summa AS Sum,b.Date AS Date FROM (
            SELECT  MAX(a.OrderId) AS OrderId,  a.FIO AS FIO, (SUM(a.Summa) - a.HolidaySum) AS Summa, MAX(a.Date) AS Date FROM (
             SELECT Zz.OrderId, ord.Date,  
              ISNULL(Admin2000.dbo.fnPersonName(ord.PersonId, DEFAULT),dbo.ZzGetDepName(ord.PersonId, $length)) AS FIO, 
              CASE 
                  WHEN ord.SiteId = 1 THEN 0 
                  WHEN ord.SiteId = 4 AND $weekend = 1 THEN dbo.ZzGetHolidaySum(ord.PersonId)
                  WHEN ord.SiteId = 4 AND $weekend = 0 THEN 0
              END AS HolidaySum, Zz.Sum AS Summa FROM(
              SELECT Zz.ItemId, SUM(Zz.Sum) As Sum, Zz.OrderId 
              FROM dbo.ZzOrderDetail Zz 
              GROUP BY Zz.ItemId, Zz.Price, Zz.OrderId
            ) Zz
            INNER join (
              SELECT ord.Id, ord.SiteId, ord.PersonId, ord.Date FROM ZzOrder ord 
               WHERE $lsite GROUP BY ord.Id, ord.SiteId, ord.PersonId, ord.Date
              ) ord 
              on (ord.Id = Zz.OrderId) 
                INNER join (
              SELECT * FROM ZzItem it   
              ) zzi 
              on (zzi.Id = Zz.ItemId)) a GROUP BY a.FIO, a.HolidaySum) b WHERE b.Summa>0 6=6";
    }else{
        $sql = "SELECT ord.Id, ISNULL(Admin2000.dbo.fnPersonName(ord.PersonId, DEFAULT),dbo.ZzGetDepName(ord.PersonId, $length)) AS FIO,
            ord.Date AS Date,zzi.Name AS Name, zzi.BarCode AS BarCode,
            Zz.Quantity AS Quantity,Zz.Price AS Price, Zz.Sum AS Sum FROM (
            SELECT Zz.ItemId, SUM(Zz.Quantity) As Quantity, Zz.Price,
                SUM(Zz.Sum) As Sum, Zz.OrderId FROM dbo.ZzOrderDetail Zz
                GROUP BY Zz.ItemId, Zz.Price, Zz.OrderId) Zz
            INNER join (
            SELECT * FROM ZzOrder ord
                WHERE $lsite
            ) ord
            on (ord.Id = Zz.OrderId)
            INNER join (
            SELECT * FROM ZzItem it
            ) zzi
            on (zzi.Id = Zz.ItemId) 6=6";
    }       

    // получим лимит суммы
    $siteid = filter_input(INPUT_GET, 'siteid');
    $spouseid = filter_input(INPUT_GET, 'spouseid');
    $limit = 0;
    if ($params == "" AND !empty($paramyes)) {
        $rows = array();
    }  else {
        $sort = (empty($_GET["jtSorting"])) ? "" : "ORDER BY " . $_GET["jtSorting"]; 
        if (empty($sql)){
            if (empty($flds)){
                $flds = "*";
            }
            $q = $db->select($flds, $table, $params, $sort);
        } else {
            if ($params !== ""){
                $sql = str_replace('5=5', $params, $sql);
            }

            if (!empty($_GET["jtPageSize"])){
                $sql = str_replace('6=6', "", $sql);
                //Get record count
                $q = $db->query("SELECT COUNT(*) AS RecordCount FROM ($sql) r");
                if ($q->error <>'') {
                    throw new Exception($q->error);
                }
                $row = $q->single();
                $recordCount = $row['RecordCount'];
                $page = (empty($_GET["jtPageSize"])) ? "" : "ROW_NUMBER() OVER (ORDER BY Name)";
                $sql = str_replace('999', $page, $sql);
                $sql = "SELECT * FROM ($sql) a WHERE row > $_GET[jtStartIndex] and row <= ($_GET[jtPageSize] + $_GET[jtStartIndex])";
            }else{
                $sql = str_replace('6=6', $sort, $sql);
            }
            $q = $db->query($sql);
        }
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        $rows = $q->all();
    }         

    //Return result to jTable
    $jTableResult['Result'] = "OK";
    if (!empty($_GET["jtPageSize"])){
        $jTableResult['TotalRecordCount'] = $recordCount;
    }
    if(empty($rows) === FALSE){
        $jTableResult['Records'] = $db->fixcells($rows);
    }
    print json_encode($jTableResult);
}
catch(Exception $ex)
{
    //Return error message
    $jTableResult = array();
    $jTableResult['Result'] = "ERROR";
    $jTableResult['Message'] = $ex->getMessage();
    print json_encode($jTableResult);
}
?>