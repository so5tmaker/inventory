<?php

include "../classes/db.php";


try
{
    //Open database connection
    $db = new db();
    $sql = "SELECT Id, z.Name, z.Parent, ISNULL((SELECT Level FROM ZzItem zi WHERE zi.Id = z.Parent),-1)+1 AS Level FROM ZzItem z";
    $q = $db->query($sql);
    $rows = $q->all();
    foreach ($rows as $key => $value) {
        $data['Level'] = $value[Level];
        //Update record in database
        $q = $db->update($data, 'ZzItem', "Id = $value[Id]");
        if ($q->error <>'') {
            throw new Exception($q->error);
        }
        echo "Id: $value[Id], z.Name: $value[Name], z.Parent: $value[Parent], z.Level: $value[Level]<br>";
    }
}
catch(Exception $ex)
{
    echo $ex->getMessage();
}
?>
