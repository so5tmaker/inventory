<?php 
$dir = "../";

$title_here = "Остатки товаров на складе";

include("header.html");

//if(!class_exists('db')){ include "../classes/db.php"; }
//$db = new db();

?>
<div id="message" class="msgcontainer"></div>
<div class="adding" >
    <button id="MyButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
        <span class="ui-button-text">Сформировать</span>
    </button>
</div>

<div class="adding" >
    <label>Выберите, на какую дату формировать отчет:</label>
    <input type="text" id="tdate" name="tdate" class="inputbar" placeholder="ГГГГ.ММ.ДД" value="<?php echo date("Y.m.d")?>" />
</div>  
<div class="adding" >
    Поле поиска (выберите по какому полю нужно искать и нажмите клавишу «Ввод»):
    <select id="record" name="record" class="inputbar">
        <option value="Name">Наименование</option>
        <option value="BarCode" selected="selected">Штрихкод</option>
    </select>
    <input onclick="this.select()" type="text" name="barcode" id="barcode" class="inputbar" />
</div>
<div class="adding" >
    <label>Введите количество дней до конца срока годности:</label>
    <input type="number" id="deep" name="deep" class="inputbar" value="" />
</div>
<div class="adding" >
    <label for="zero">Показать нулевые остатки:</label>
    <input type="checkbox" id="zero" name="zero" class="inputbar" />
</div>
<div id="ItemTableContainer" class="tablecontainer"></div>  
<script type="text/javascript">
    var tdate   = $("#tdate");
    var barcode = $("#barcode");
    var record  = $("#record");
    var deep    = $("#deep");
    var zero    = $("#zero");
    $(document).ready(function () {
        zero.val('off');
        //Prepare jTable
        $("#ItemTableContainer").jtable({
                title: <?php echo "'$title_here'"?>,
                sorting: true,
                defaultSorting: "ExpireDate ASC",
                actions: {
                        listAction:   "treport.php"
                },
                fields: {
                    Id: {key: true, create: false, edit: false, list: false},
                    ItemId:      {title: "Товар", width: "60%",options: '../options.php?table=ZzItem'},
                    ExpireDate:  {title: "Срок годности",width: "20%"},
                    Quantity:    {title: "Количество",width: "20%"}
            }
        });

        //Load person list from server
        $("#ItemTableContainer").jtable("load");

    });
    
    function loadtbl(){
        $('#ItemTableContainer').jtable('load', {
            tdate:   tdate.val(),
            BarCode: barcode.val(),
            record:  record.val(),
            deep:    deep.val(),
            zero:    zero.val()
        });
    }

    $("#MyButton").button().click(function () {
        loadtbl();
    });
    
    $(function() {
         $( "#tdate" ).datepicker({ dateFormat: 'yy.mm.dd'}); 
    });
    
    barcode.keypress(function(e) {
        if(e.keyCode === 13) {
            loadtbl();
        }
    }).keypress();
    
    deep.keypress(function(e) {
        if(e.keyCode === 13) {
            loadtbl();
        }
    }).keypress();
    
    zero.change(function () {
        var checked = zero.is(':checked');
        if (checked) {
            zero.val('on');
            loadtbl();
        } else {
            zero.val('off');
            loadtbl();
        }
    });
    
</script>
<?php 
require_once ("footer.html");
