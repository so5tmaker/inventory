<?php

include "classes/db.php";

$barcode = filter_input(INPUT_POST, 'barcode');
//$sql = "select
//       i.id As ItemId,
//       1 As Quantity,
//       p.Value AS Price,
//       p.Value As Sum
//from ZzItem i
//     inner join ZzPrice p on i.id =
//     p.ItemId
//where i.BarCode = ?";

try
{
    //Open database connection
    $db = new db();

    $q = $db->getitemprice($barcode);
    if ($q->error <>'') {
        throw new Exception($q->error);
    }
    $rows = $q->all();

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
