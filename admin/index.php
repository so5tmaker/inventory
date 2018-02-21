<?php
$text = 'Добро пожаловать в админский блок.';
$title_here = "Главная страница блока администратора"; include_once ("header.html");

?>
<p align="center"><?php echo $text; phpinfo();?></p>
<!--Подключаем нижний графический элемент-->    
<?php  include_once ("footer.html");?>

