<?php

include "classes/db.php";

//Open database connection
$db = new db();

$barcode = filter_input(INPUT_POST, 'BarCode'); //$db->clearstr(filter_input(INPUT_POST, 'barcode'));
$price   = trim(str_replace(',', '.', filter_input(INPUT_POST, 'price')));

try
{
    $table   = 'ZzItem';
    // создаем новый товар
    $data = array(
                "Name" => "Без названия",
                "BarCode" => $barcode
            );

    //Insert record into database
    $q = $db->insert($data, $table);
    if ($q->error <>'') {
        throw new Exception($q->error);
    }
    //Get last inserted record (to return to jTable)
    $rows = $q->all();
    $q = $db->select("*", $table, "Id = (SELECT IDENT_CURRENT ('$table') AS Id)");
    $itemrow = $q->single();
    
    // добавляем новую цену
    $data = array(
        "Date" => date("Ymd G:i:s"),
        "ItemId"   => $itemrow[Id],
        "Value" => $price
    );
    
    $table   = 'ZzPrice';
    //Insert record into database
    $q = $db->insert($data, $table);
    if ($q->error <>'') {
        throw new Exception($q->error);
    }
    //Get last inserted record (to return to jTable)
    $q = $db->select("*", $table, "Id = (SELECT IDENT_CURRENT ('$table') AS Id)");
    $rows = $q->single();
    
    $jTableResult = array();
    $jTableResult['Result'] = "OK";
    $jTableResult['Options'] = $rows;
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
