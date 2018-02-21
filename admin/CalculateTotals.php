<?php
$title_here = "Пересчет итогов";
include("header.html");
$db = new db();
$sql = "EXEC dbo.ZzCalculateTotals";
try {
    $db->query($sql);
} catch (Exception $ex) {
    echo $ex->getMessage();
}

require_once ("footer.html");