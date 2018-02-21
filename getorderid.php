<?php

include "classes/db.php";

$table   = 'ZzPrice';
$ords    = filter_input(INPUT_POST, 'ords');
$siteid  = filter_input(INPUT_POST, 'siteid');
$prsnid  = filter_input(INPUT_POST, 'prsnid');
$begin   = filter_input(INPUT_POST, 'begin');
$end     = filter_input(INPUT_POST, 'end');

$grows   = array();
$gerror  = "";

function insert($data, $Quantity) {
    global $db, $grows, $gerror;
    // проверим есть ли уже такая номенклатура
    $table = 'ZzOrderDetail';
    $where = "ItemId = '$data[ItemId]' AND OrderId = '$data[OrderId]' AND Price = '$data[Price]' AND Date = '$data[Date]' AND ExpireDate = '$data[ExpireDate]'";
    $q = $db->select("TOP 1 *", $table, $where);
    $rows = $q->single(); 
    if (!empty($rows)) {
        $data[Quantity] = $Quantity+$rows[Quantity];
        $data[Price]    = $rows[Price];
        $data[Sum]      = $data[Price]*$Quantity+$rows[Sum];

        // обновляем номенклатуру в документе ZzOrderDetail, если её нашли...
        $q = $db->update($data, $table, "Id = '$rows[Id]'");
        if ($q->error <>'') {
            $gerror = $q->error;
            return false;
        }
        $grows = $data;
    }  else {
        $data[Quantity] = $Quantity;
        $data[Sum] = $data[Price]*$Quantity;
        //Добавляем новую номенклатуру в документ ZzOrderDetail, если такой нет.
        $db->isTotals = true;
        $q = $db->insert($data, 'ZzOrderDetail');
        
        if ($q->error <>'') {
            $gerror = $q->error;
            return false;
        }
        //Get last inserted record (to return to jTable)
        $q = $db->select("*", $table, "Id = (SELECT IDENT_CURRENT ('$table') AS Id)");
        $grows = $q->single();
    }
    return true;
}

function getArrSum($arr) {
    $sum = 0;
    foreach ($arr as $key => $value) {
        $sum += $value[Quantity];
    }
    return $sum;
}

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
    
    $sql = "SELECT zt.ItemId, zt.Quantity, zt.ExpireDate, zt.Date FROM (SELECT MAX(Id) AS Id,
           MAX(ExpireDate) AS ExpireDate
    FROM ZzTotal
    WHERE (Date <= dateadd(month,1,dateadd(day,1-day('$Date'),'$Date'))) And (ItemId = '$ItemId')
    GROUP BY ExpireDate) ztw
    LEFT JOIN ZzTotal zt ON zt.Id = ztw.Id WHERE zt.Quantity <> 0";

    $q = $db->query($sql);
    if ($q->error <>'') {
        throw new Exception($q->error);
    }
    $items = $q->all();
    $ostalos_spisat = $Quantity;
    if (empty($items)) {
        $sum = 0;
        $cnt = 0;
        $data[Quantity] = $Quantity;
        insert($data, $Quantity);
    } else {
        $sum = getArrSum($items);
        $cnt = count($items);
    }
    $ExpireDate = Null;
    $diff = $sum - $Quantity;
//    foreach ($items as $key => $item) {
    for ($i = 0; $i < $cnt; $i++) {  
        $item = $items[$i];
        $spisat = min($ostalos_spisat, $item[Quantity]);
        if ($ExpireDate == $item[ExpireDate]) {
            continue;
        }
//        if ($spisat == $item[Quantity]){
//            $sebestoimost = $item[Summa];
//        } else {
//            $sebestoimost = $spisat / $item[Quantity] * $item[Summa];
//        } 
        $data[Quantity] = $spisat;
        $data[ExpireDate] = $item[ExpireDate];
        $data[Date] = $item[Date];
        if ($i == $cnt - 1 AND $diff < 0) {
            $data[Quantity] = -$diff + $spisat;
            insert($data, -$diff + $spisat);
        } else {
            if (insert($data, $spisat) AND $ostalos_spisat !== 0) {
                if($ostalos_spisat <= 0){break;} 
                $ostalos_spisat = $ostalos_spisat - $spisat;
                if($ostalos_spisat == 0){break;} 
            } else {
                throw new Exception($gerror);
            }
            $ExpireDate = $item[ExpireDate];
        }
    }
    
//    if ($ostalos_spisat > 0) {
//        $data[Quantity] = $ostalos_spisat;
//        insert($data, $ostalos_spisat);
//    }

    $jTableResult = array();
    $jTableResult['Result'] = "OK";
    $jTableResult['Options'] = $grows;
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
