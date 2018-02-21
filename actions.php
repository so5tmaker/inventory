<?php

include "classes/db.php";

try
{
    //Open database connection
    $db = new db();
    $action   = filter_input(INPUT_GET, 'action');
    $table    = filter_input(INPUT_GET, 'table');
    $idname   = 'Id';
    $params   = filter_input(INPUT_GET, 'params');
    $paramid  = filter_input(INPUT_GET, 'paramid');
    $paramyes = filter_input(INPUT_GET, 'paramyes');
    $sql      = filter_input(INPUT_GET, 'sql');
    $barcode  = filter_input(INPUT_POST, 'BarCode');
    $record   = filter_input(INPUT_POST, 'record');
    $folderid = filter_input(INPUT_POST, 'FolderID');
    $parentid = filter_input(INPUT_POST, 'ParentId');
    $level = filter_input(INPUT_POST, 'Level');
    
    $cMonth  = filter_input(INPUT_POST, 'cMonth');
    $cYear   = filter_input(INPUT_POST, 'cYear');
    
    if (empty($params)){
        $params = "";
        if (!empty($barcode)){
            $params = "upper($record) LIKE upper('%$barcode%')";
        }elseif (!empty($cMonth) AND !empty($cYear)){
            $strStartDate = "01.$cMonth.$cYear";
            $cDay = date('t',strtotime($strStartDate));
            $params = "Date BETWEEN Convert(date,'$strStartDate',103) AND Convert(date,'$cDay.$cMonth.$cYear',103)";
        }else{
            $paramyes = null;
        }
    } else {
        $ids = explode(",", $params);
        $params = "";
        foreach ($ids as $column => $value) {
            $params .= ($params == "") ? "" : ", ";
            $params .= "'$value'";
        }
        $params = "$paramid IN ($params)";
    }
    
    if ($table == "ZzItem"){
        $and = ($params == "") ? '' : " AND ";
        if (isset($folderid) AND !empty($folderid)){
            $params .= "$and(Parent = $folderid OR Id = $folderid)";
        } else {
            if ($params == ""){
                $params = " (ISNULL(Parent,-1) = -1 OR Folder = 1)"; // AND Level = 0
            }
        }
    }
    
    $flds = filter_input(INPUT_GET, 'fields');
    $fields = explode(",", $flds);
    
    //Getting records (listAction)
    if($action == "list")
    {
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
                    $sql = "SELECT * FROM ($sql) a WHERE row > $_GET[jtStartIndex] and row <= ($_GET[jtPageSize] + $_GET[jtStartIndex]) $sort";
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
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        if (!empty($_GET["jtPageSize"])){
            $jTableResult['TotalRecordCount'] = $recordCount;
        }
        if(empty($rows) === FALSE){
            $jTableResult['Records'] = $db->fixcells($rows);
        }
        print json_encode($jTableResult);
    }
    //Creating a new record (createAction)
    else if($action == "create")
    {
        foreach ($fields as $key => $value) {
            if ($value === 'Pass'){
                $data[$value] = md5(filter_input(INPUT_POST, $value));
            } elseif ($value === 'BarCode'){
                $BarCode = filter_input(INPUT_POST, $value);
                if(empty($BarCode)){
                    $data[$value] = $db->genbarcode();
                }else{
                    $data[$value] = $BarCode;
                }
            } elseif ($value === 'IncomeId'){
                $data[$value] = filter_input(INPUT_GET, 'IncomeId');
            } else {
                $data[$value] = filter_input(INPUT_POST, $value);
            }
        }
        //Insert record into database
        $q = $db->insert($data, $table);
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        //Get last inserted record (to return to jTable)
        $rows = $q->all();
        $q = $db->select("*", $table, "$idname = (SELECT IDENT_CURRENT ('$table') AS Id)");
        $row = $q->single();

        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['Record'] = $row;
        print json_encode($jTableResult);
    }
    //Updating a record (updateAction)
    else if($action == "update")
    {

        foreach ($fields as $key => $value) {
            if ($value === 'Pass'){
                $data[$value] = md5(filter_input(INPUT_POST, $value));
            } else {
                $data[$value] = filter_input(INPUT_POST, $value);
            }
        }
        
        if ($table == "ZzItem"){
            $data[Date] = date("Ymd G:i:s");
        }

        //Update record in database
        $q = $db->update($data, $table, "$idname = ".$_POST[$idname]);
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        print json_encode($jTableResult);
    }
    //Deleting a record (deleteAction)
    else if($action == "delete")
    {
        //Delete from database
        $q = $db->delete($table, "$idname = ".$_POST[$idname]);
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        print json_encode($jTableResult);
    }
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
