<?php

include "../classes/db.php";

$table  = filter_input(INPUT_GET, 'table');
$fields = filter_input(INPUT_GET, 'fields');
$orderby= filter_input(INPUT_GET, 'orderby');
$Folder = filter_input(INPUT_GET, 'Folder');
$Folder = (isset($Folder)) ? $Folder : 0;

if(empty($fields)){
    $fields = "Id, Name";
}
if(empty($orderby)){
    $orderby = "Name";
}
try
{
    if(empty($table)){
        $jTableResult = array();
	$jTableResult['Result'] = "ERROR";
	$jTableResult['Message'] = "Options: Не указана таблица для получения данных!";
	print json_encode($jTableResult);
    } else {
        //Open database connection
        $db = new db();
        
        $where = 'id = 1';
        $q = $db->select($fields, $table, $where, "ORDER BY $orderby");
        $rows = $q->all();
        
        foreach ($rows as $key => $value) {

            $DisplayText = $value[Name];
            $Value       = $value[Id];

            $row[] = array(
            "DisplayText" => $DisplayText,
            "Value"       => $Value
            );
        }
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['Options'] = $row;
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
