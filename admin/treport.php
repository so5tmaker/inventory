<?php

include "../classes/db.php";

try
{
    //Open database connection
    $db = new db();
    $barcode = filter_input(INPUT_POST, 'BarCode');
    $record  = filter_input(INPUT_POST, 'record');
    $tdate   = filter_input(INPUT_POST, 'tdate');
    $deep    = filter_input(INPUT_POST, 'deep');
    $zero    = filter_input(INPUT_POST, 'zero');
    
    
    $t_date = str_replace('.', '', $tdate);
    $firstday = date("Ymd", strtotime("first day of $t_date"));
    
    $filter = "";
    $and    = " WHERE";
    if (!empty($barcode)){
        $filter = "WHERE ztt.ItemId IN (SELECT Id from ZzItem WHERE upper($record) LIKE upper('%$barcode%'))";
        $and    = " AND";
    }
    if (!empty($deep)){
        $newdate = new DateTime();
        $begin = $newdate->format('Y.m.d');
        $newdate->add(new DateInterval('P'.$deep.'D'));
        $end = $newdate->format('Y.m.d');
        $filter .= "$and ztt.ExpireDate BETWEEN '$begin' AND '$end'";
    }
    
    if($zero == 'on'){
        $date1 = '2016.01.01';
        $sql = "SELECT 0 As Id, ztt.ItemId, '0000.00.00' AS ExpireDate, SUM(ztt.Quantity) AS Quantity from (        
          SELECT zww.ItemId, zww.ExpireDate, SUM(zww.Quantity) AS Quantity from (
              SELECT zmv.ItemId, zmv.ExpireDate, SUM(zmv.Quantity) AS Quantity from (
              SELECT zid.ItemId, zid.ExpireDate, zid.Quantity from ZzIncomeDetail zid
                INNER JOIN ZzIncome zi on zid.IncomeId=zi.Id
              WHERE zi.Date BETWEEN '$date1' AND '$tdate'
              UNION
              SELECT zod.ItemId, zod.ExpireDate, -zod.Quantity from ZzOrderDetail zod
                INNER JOIN ZzOrder zo on zod.OrderId=zo.Id
              WHERE zo.Date BETWEEN '$date1' AND '$tdate') zmv GROUP BY zmv.ItemId, zmv.ExpireDate
              ) zww GROUP BY zww.ItemId, zww.ExpireDate HAVING SUM(zww.Quantity)=0
        ) ztt $filter GROUP BY ztt.ItemId";
    } else {
        if($firstday == $t_date OR empty($tdate)){

            $sql = "SELECT zt.Id, ztt.ItemId, ztt.ExpireDate, zt.Quantity FROM (SELECT Id, ItemId, ExpireDate, Quantity, Date FROM ZzTotal WHERE Quantity<>0) zt
                    INNER JOIN (
            SELECT ItemId, ExpireDate, MAX(Date) AS Date FROM ZzTotal where Date<='$tdate'
            GROUP BY ItemId, ExpireDate) ztt 
            on ztt.ItemId=zt.ItemId AND ztt.ExpireDate=zt.ExpireDate AND ztt.Date=zt.Date $filter 6=6";
        } else {
            $newdate = new DateTime($firstday);
            $newdate->add(new DateInterval('P1D'));
            $date1 = $newdate->format('Y.m.d');

            $sql = "SELECT 0 As Id, ztt.ItemId, ztt.ExpireDate,  SUM(ztt.Quantity) AS Quantity FROM (
              SELECT ztp.ItemId, ztp.ExpireDate, zt.Quantity FROM (SELECT ItemId, ExpireDate, Quantity, Date FROM ZzTotal WHERE Quantity<>0) zt
                  INNER JOIN (
                SELECT ItemId, ExpireDate, MAX(Date) AS Date FROM ZzTotal ztp where Date<='$firstday'
                GROUP BY ItemId, ExpireDate) ztp 
                on ztp.ItemId=zt.ItemId AND ztp.ExpireDate=zt.ExpireDate AND ztp.Date=zt.Date
              UNION 
              SELECT zww.ItemId, zww.ExpireDate, zww.Quantity from (
                  SELECT zmv.ItemId, zmv.ExpireDate, SUM(zmv.Quantity) AS Quantity from (
                  SELECT zid.ItemId, zid.ExpireDate, zid.Quantity from ZzIncomeDetail zid
                    INNER JOIN ZzIncome zi on zid.IncomeId=zi.Id
                  WHERE zi.Date BETWEEN '$date1' AND '$tdate'
                  UNION
                  SELECT zod.ItemId, zod.ExpireDate, -zod.Quantity from ZzOrderDetail zod
                    INNER JOIN ZzOrder zo on zod.OrderId=zo.Id
                  WHERE zo.Date BETWEEN '$date1' AND '$tdate') zmv GROUP BY zmv.ItemId, zmv.ExpireDate
              ) zww
            ) ztt $filter GROUP BY ztt.ItemId, ztt.ExpireDate HAVING SUM(ztt.Quantity)<>0 6=6";
        }
    }

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