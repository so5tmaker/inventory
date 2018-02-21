<?php

include "../classes/db.php";

$barcode = filter_input(INPUT_POST, 'barcode');

try
{
    //Open database connection
    $db = new db();

    $q = $db->select('Id', 'ZzItem', "BarCode = '$barcode'"); //, "BarCode LIKE '$barcode'"
//    $q = $db->query("SELECT zz.Id FROM ZzItem zz WHERE zz.BarCode = '$barcode'");
    
//     $q = $db->query("SELECT Id FROM ZzItem");
    
    if ($q->error <>'') {
        throw new Exception($q->error);
    }
    $rows = $q->single();

    $jTableResult = array();
    $jTableResult['Result'] = "OK";
    $jTableResult['ItemId'] = $rows[Id];
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
