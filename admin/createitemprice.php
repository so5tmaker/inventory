<?php

include "../classes/db.php";

$action  = filter_input(INPUT_GET, 'action');
$id      = filter_input(INPUT_POST, 'Id');
$priceid = filter_input(INPUT_POST, 'priceid');
$barcode = filter_input(INPUT_POST, 'BarCode'); //$db->clearstr(filter_input(INPUT_POST, 'barcode'));
$name    = trim(filter_input(INPUT_POST, 'Name'));
$price   = trim(str_replace(',', '.', filter_input(INPUT_POST, 'Value')));
$Folder  = filter_input(INPUT_POST, 'Folder');
$Folder  = (isset($Folder)) ? $Folder : 0;
$parent  = filter_input(INPUT_POST, 'Parent');

try
{
    $db = new db();
    
    if(empty($name)){
        throw new Exception("Для добавления нужно ввести наименование товара!");
    }
    if(empty($price) AND $Folder == 0){
        throw new Exception("Для добавления нужно ввести цену товара!");
    }
    
    $data = array(
                    "Parent" => $parent,
                    "Name" => $name,
                    "Folder" => $Folder,
                    "BarCode" => $barcode,
                    "Date" => date("Ymd G:i:s")
                );
    
    if($action == "create")
    {
        // создаем новый товар
        $table   = 'ZzItem';
        $price_exists = FALSE;
        if(empty($barcode) AND $Folder == 0){
            // проверим есть ли уже такой штрихкод в резерве 
            $where = "Name = 'РезервныйШтрихкод'";
            $sql = "SELECT MIN(Id) AS Id, MIN(BarCode) AS BarCode  FROM ZzItem WHERE Name = 'РезервныйШтрихкод'";
            $q = $db->query($sql);
//            $q = $db->select("MIN(Id) AS Id, MIN(BarCode) AS BarCode", $table, $where);
            $itemrow = $q->single(); 
//            $itemrow = $q->all(); 
            if (empty($itemrow)) {
                $barcode = $db->genbarcode();
                $data[BarCode] = $barcode;
//                $data[Level] = $db->get_level($parent);
                //Insert record into database
                $q = $db->insert($data, $table);
                if ($q->error <>'') {
                    throw new Exception($q->error);
                }
                //Get last inserted record (to return to jTable)
                $q = $db->select("*", $table, "Id = (SELECT IDENT_CURRENT ('$table') AS Id)");
                $itemrow = $q->single();
            } else {
                $table   = 'ZzItem';
                $data[BarCode] = $itemrow[BarCode];
//                $data[Level] = $db->get_level($itemrow['Parent']);
                // обновляем товар
                $q = $db->update($data, $table, "Id = ".$itemrow[Id]);
                if ($q->error <>'') {
                    throw new Exception($q->error);
                }
                
                $table   = 'ZzPrice';
                // проверим есть ли уже такая цена
                $where = "ItemId = ".$itemrow[Id];
                $q = $db->select("TOP 1 *", $table, $where);
                $rows = $q->single(); 
                
                if (!empty($rows)) {
                    // обновляем цену
                    $data = array(
                        "Date" => date("Ymd G:i:s"),
                        "Value" => $price,
                        "Date" => date("Ymd G:i:s")
                    );
                    //Update record in database
                    $q = $db->update($data, $table, "Id = ".$rows[Id]);
                    if ($q->error <>'') {
                        throw new Exception($q->error);
                    }
                    $price_exists = TRUE;
                } 
            }
        } else {
            
            if($Folder == 0){
                // проверим есть ли уже такая номенклатура
                $where = "BarCode = '$barcode'";
                $q = $db->select("*", $table, $where);
                $rows = $q->single(); 
                if (!empty($rows)) {
                    throw new Exception("Товар «$rows[Name]» со штрихкодом «".$barcode."» уже есть в базе!");
                }
                $data[BarCode] = $barcode;
//                $data[Level] = $db->get_level($rows['Parent']);
            } else {
                $data[BarCode] = $db->generate_code();
//                $data[Level] = $db->get_level($parent);
            }
            
            //Insert record into database
            $q = $db->insert($data, $table);
            if ($q->error <>'') {
                throw new Exception($q->error);
            }
            //Get last inserted record (to return to jTable)
            $q = $db->select("*", $table, "Id = (SELECT IDENT_CURRENT ('$table') AS Id)");
            $itemrow = $q->single();
        }
        
        if (!$price_exists AND $Folder == 0) {
            $table   = 'ZzPrice';
            // добавляем новую цену
            $data = array(
                "Date" => date("Ymd G:i:s"),
                "ItemId"   => $itemrow[Id],
                "Value" => $price
            );
            //Insert record into database
            $q = $db->insert($data, $table);
            if ($q->error <>'') {
                throw new Exception($q->error);
            }
            //Get last inserted record (to return to jTable)
            $q = $db->select("*", $table, "Id = (SELECT IDENT_CURRENT ('$table') AS Id)");
            $rows = $q->single();   
        }
        
        $newrows = array(
            "Id"   => $itemrow[Id],
            "Folder" => $itemrow[Folder],
            "priceid"   => $rows[Id],
            "Name" => $itemrow[Name],
            "BarCode"   => $itemrow[BarCode],
            "Value" => $price
        );
        
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['Record'] = $newrows;
        print json_encode($jTableResult);
    }
    //Updating a record (updateAction)
    else if($action == "update")
    {
        $table   = 'ZzItem';
        if($Folder == 1){
            $data[BarCode] = $db->generate_code();
        }
//        $data[Level] = $db->get_level($parent);
//        $dataLevel = $db->get_level($parent);
        $newrows = $data;
        // обновляем товар
        $q = $db->update($data, $table, "Id = ".$id);
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        
        if($Folder == 0){
            $table   = 'ZzPrice';
            // обновляем цену
            $data = array(
                "Date" => date("Ymd G:i:s"),
                "Value" => $price
            );
            // проверим есть ли уже такая цена
            $where = "ItemId = ".$id;
            $q = $db->select("TOP 1 *", $table, $where);
            $rows = $q->single(); 
                
            if (!empty($rows)) {
                $newrows[Value] = $price;
                //Update record in database
                $q = $db->update($data, $table, "ItemId = ".$id);
                if ($q->error <>'') {
                    throw new Exception($q->error);
                }
            } else {
                // добавляем новую цену
                $data = array(
                    "Date" => date("Ymd G:i:s"),
                    "ItemId"   => $id,
                    "Value" => $price
                );
                //Insert record into database
                $q = $db->insert($data, $table);
                if ($q->error <>'') {
                    throw new Exception($q->error);
                }
                //Get last inserted record (to return to jTable)
                $q = $db->select("*", $table, "Id = (SELECT IDENT_CURRENT ('$table') AS Id)");
                $newrows = $q->single();
            }
        }

        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['Record'] = $newrows;
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
