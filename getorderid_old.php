<?php

include "classes/db.php";

$table   = 'ZzPrice';
$ords    = filter_input(INPUT_POST, 'ords');
$siteid  = filter_input(INPUT_POST, 'siteid');
$prsnid  = filter_input(INPUT_POST, 'prsnid');
$begin   = filter_input(INPUT_POST, 'begin');
$end     = filter_input(INPUT_POST, 'end');
try
{
    if (empty($ords)) {
        $params = '';
    } else {
        $ids = explode(",", $ords);
        $params = "";
        foreach ($ids as $column => $value) {
            $params .= ($params == "") ? "" : ", ";
            $params .= "'$value'";
        }
        $params = "AND Zz.Id IN ($params)";
    }

    $sql = "select top 1 Zz.Id, Zz.Date from dbo.ZzOrder Zz
    WHERE Zz.Date BETWEEN Convert(datetime,'$begin',103) AND Convert(datetime,'$end',103)
    $params ORDER BY  Zz.Date DESC, Zz.Id DESC";
    //Open database connection
    $db = new db();

    $q = $db->query($sql);
    if ($q->error <>'') {
        throw new Exception($q->error);
    }
    $rows = $q->single();
    
    if ($params == '' OR empty($rows)) {
        // добавляем новый заказ, если не нашли...
        $q = $db->add_new_order($siteid, $prsnid);
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        $rows = $q->single();
    }
    
    $ItemId   = filter_input(INPUT_POST, 'ItemId');
    $Quantity = filter_input(INPUT_POST, 'Quantity'); 
    $Price    = filter_input(INPUT_POST, 'Price');
    $Sum      = filter_input(INPUT_POST, 'Sum');
    $OrderId  = filter_input(INPUT_POST, 'OrderId');
    $OrderId  = (empty($OrderId)) ? $rows[Id] : $OrderId ;
    $Date     = date("Ymd G:i:s", $q->curdate);
    
    // добавляем новую строку в заказ
    $data = array(
        "ItemId"   => $ItemId,
        "Quantity" => $Quantity,
        "Price"    => $Price,
        "Sum"      => $Sum,
        "OrderId"  => $OrderId,
        "Date"     => $Date
        );
    // проверим есть ли уже такая номенклатура
    $table = 'ZzOrderDetail';
    $where = "ItemId = '$ItemId' AND OrderId = '$OrderId' AND Price = '$Price' AND Date = '$Date'";
    $q = $db->select("TOP 1 *", $table, $where);
    $rows = $q->single(); 
    if (!empty($rows)) {
        $data = array(
        "Quantity" => $Quantity+$rows[Quantity],
        "Price"    => $Price,
        "Sum"      => $Sum+$rows[Sum]
        );
        // обновляем номенклатуру, если её нашли...
        $q = $db->update($data, $table, "Id = '$rows[Id]'");
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        $rows = $data;
    }  else {
        //Добавляем новую, если такой нет.
        $q = $db->insert($data, 'ZzOrderDetail');
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        //Get last inserted record (to return to jTable)
        $q = $db->select("*", $table, "Id = (SELECT IDENT_CURRENT ('$table') AS Id)");
        $rows = $q->single();
    }

    $jTableResult = array();
    $jTableResult['Result'] = "OK";
    $jTableResult['Options'] = $rows;
    print json_encode($jTableResult);
}
catch(Exception $ex)
{
    //Return error message
	$jTableResult = array();
	$jTableResult[1] = $ex->getMessage();
	print json_encode($jTableResult);
}
?>
