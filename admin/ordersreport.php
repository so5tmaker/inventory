<?php 
$dir = "../";

$detail = filter_input(INPUT_GET, 'detail');
$detail = (isset($detail)) ? $detail : 1;
$title_here = "Детальный отчет по заказам вефильцев";
if ($detail == 0){
    $title_here = "Общий отчет по заказам вефильцев (только превышение суммы)";
}

include("header.html");

if(!class_exists('db')){ include "../classes/db.php"; }
$db = new db();
?>
<div id="message" class="msgcontainer"></div>
<div class="adding" >
    <button id="MyButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
        <span class="ui-button-text">Сформировать</span>
    </button>
</div>

<div class="adding" >
    <label>Выберите, по какому складу хотите формировать отчет:</label>
    <select id="site" name="site" class="inputbar">
        <option value="0" selected="selected"><< Значение не выбрано >></option>
        <option value="4">Склад в 4 корпусе</option>
        <!--<option value="2">Склад в столовой</option>-->
    </select>
    <label>Выберите глубину периода (недели):</label>
    <input type="number" id="deep" name="deep" class="inputbar" value="10" />
    <label>Выберите, по какому периоду формировать отчет:</label>
    <select id="period" name="period" class="inputbar">
    </select>
    <label>Выберите охват периода:</label>
    <select id="inperiod" name="inperiod" class="inputbar">
    </select>
</div>  

<div id="ItemTableContainer" class="tablecontainer"></div>  
<script type="text/javascript">
    var site     = $("#site");
    var period   = $("#period");
    var inperiod = $("#inperiod");
    var deep     = $("#deep");
    var week     = <?php echo "'$week'"?>;
    var month    = <?php echo "'$month'"?>;
    var detail    = <?php echo $detail?>;
    $(document).ready(function () {
        //Prepare jTable
        $("#ItemTableContainer").jtable({
                title: <?php echo "'$title_here'"?>,
                sorting: true,
                defaultSorting: "Date ASC",
                actions: {
                        listAction:   "report.php"
                },
                fields: {
                    Id: {key: true, create: false, edit: false, list: false},
                    <?php
                    if ($detail == 0){
                    ?>
                        FIO: {title: "ФИО",width: "75%"},
                        Sum: {title: "Сумма",width: "10%"},
                        Date: {title: "Дата",width: "15%"}
                    <?php
                    }else{
                    ?>
                        FIO:      {title: "ФИО", width: "25%"},
                        Date:     {title: "Дата",width: "15%"},
                        Name:     {title: "Товар",width: "30%"},
                        BarCode:  {title: "Штрихкод",width: "10%"},
                        Quantity: {title: "Количество",width: "5%"},
                        Price:    {title: "Цена", width: "5%"},
                        Sum:      {title: "Сумма",width: "10%"}
                    <?php
                    }
                    ?>
            }
        });

        //Load person list from server
        $("#ItemTableContainer").jtable("load");

    });

    $("#MyButton").button().click(function () {
        $('#ItemTableContainer').jtable('load', {
//            BarCode: barcode.val(),
            site:     site.val(),
            period:   period.val(),
            inperiod: inperiod.val(),
            detail:   detail
        });
    });

    deep.change(function(){
        var per = get_option_period();
        $('#period').html(per.week);
    });

    function get_option_period() {       
        var options = [];
        $.ajax({ //Not found in cache, get from server
            url: 'report.php',
            type: 'POST',
            dataType: 'json',
            async: false,
            processData: false,
            data:"deep="+deep.val() ,
            success: function (data) {
                options = data.Options;
            },
            error: function(data) {
                showmess('Ошибка:'+data.responseText, 'red', false);
            }
        });
        return options;
    }

    $( "#site" ).change(function() {
        var val = $( this ).val();
        var period, inperiod;
        if (val === '0'){
            period = ''; 
            inperiod = '';
        } 
        if (val === '1'){
            period = get_option_period().week;
            inperiod = '<option value="0" selected="selected">Весь период</option>';
        } 
        if (val === '4'){
            period = get_option_period().week;
            inperiod = '<option value="1" selected="selected">Будние дни</option>';
//                '<option value="1" selected="selected">Будние дни</option>'+
//                '<option value="2">Выходные дни</option>';
        } 
        $('#period').html(period);
        $('#inperiod').html(inperiod);
    });
</script>
<?php 
require_once ("footer.html");
?>