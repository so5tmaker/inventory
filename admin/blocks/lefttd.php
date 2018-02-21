<?php 
if ($usr[Root] == 1){
    $sites        = "<a href='sites.php'>Склады</a>";
    $orders       = "<a href='orders.php'>Заказы</a>";
    $orderdetails = "<a href='orderdetails.php'>Заказы ТЧ</a>";
    $params       = "<a href='params.php'>Параметры</a>";
} else {
    $sites        = "";
    $orders       = "";
    $orderdetails = "";
    $params       = "";
}
?>

<td width="182px" valign="top" class="left">

<p align="center" class="title">Основное</p>
<div id="coolmenu">
<a href="index.php">Главная</a>
<?php echo $params?>
<a href="?action=logout">Выйти</a>
</div>

<p align="center" class="title">Справочники</p>
<div id="coolmenu">
<?php echo $sites?>
<a href="items_new_new.php">Товары с папками</a>
<a href="items_new.php">Товары с ценами</a>
<a href="items.php">Номенклатура</a>
<a href="prices.php">Цены</a>
</div>

<!--<p align="center" class="title">Документы</p>
<div id="coolmenu">
<?php echo $orders?>
<?php echo $orderdetails?>
</div>-->

<p align="center" class="title">Отчеты</p>
<div id="coolmenu">
<a href="ordersreport.php?detail=0">Только превышение</a>
<a href="ordersreport.php">Детальный</a>
<a href="totalsreport.php">Остатки товаров</a>
</div>

<p align="center" class="title">Поступления</p>
<div id="coolmenu">
<a href="incomelist.php">Список</a>
</div>

</td>
