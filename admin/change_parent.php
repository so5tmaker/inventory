<?php

include "../classes/db.php";

$table  = filter_input(INPUT_GET, 'table');
$ids = filter_input(INPUT_POST, 'id');
$ids = explode(",", $ids);

try
{
    if((count($ids) < 1) OR (!isset($table))){
//        throw new Exception("Не указана таблица для получения данных!");
        $jTableResult = array();
	$jTableResult['Result'] = "ERROR";
	$jTableResult['Message'] = "Не указана таблица для получения данных!";
	print json_encode($jTableResult);
    } else {
        //Open database connection
        $db = new db();
        $parent = $ids[0];
        unset($ids[0]); 
        $params = "";
        foreach ($ids as $column => $value) {
            $data['Parent'] = $parent;
            //Update record in database
            $q = $db->update($data, $table, "Id = $value");
            if ($q->error <>'') {
                throw new Exception($q->error);
            }
        }
        
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
