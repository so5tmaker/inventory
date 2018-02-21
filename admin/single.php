<?php 
$dir = "../";
$title_here = "Табличная часть поступления";
include("header.html");

$id  = filter_input(INPUT_GET, 'id');
$date = filter_input(INPUT_GET, 'date');
$cap = 'Поступление товаров № ' . $id  . ' от '.  $date;

?>
<div class="adding" >
    Штрихкод
    <input type="text" onclick="this.select()" name="barcode" id="barcode" class="inputbar" autofocus />
</div>
<div id="DetailsContainer" class="tablecontainer"></div>
    <script type="text/javascript">
        var barcode = $("#barcode");
        var IncomeId = $('#IncomeId').text();
            $(document).ready(function () {

                //Prepare jTable
                $('#DetailsContainer').jtable({
                        title: <?php echo "'$cap'"?>,
                        sorting: true,
                        defaultSorting: 'Date ASC',
                        actions: {
                                listAction:   '../actions.php?action=list&table=ZzIncomeDetail&params=<?php echo $id;?>&paramid=IncomeId&paramyes=1',
                                createAction: '../actions.php?action=create&table=ZzIncomeDetail&fields=ItemId,Quantity,Price,Sum,IncomeId,ExpireDate&IncomeId=<?php echo $id;?>',
                                updateAction: '../actions.php?action=update&table=ZzIncomeDetail&fields=ItemId,Quantity,Price,Sum,ExpireDate,IncomeId',
                                deleteAction: '../actions.php?action=delete&table=ZzIncomeDetail'
                        },
                        fields: {
                            Id: {
                                    key: true,
                                    create: false,
                                    edit: false,
                                    list: false
                            },
                            ItemId: {
                                title: 'Номенклатура',
                                width: '58%',
                                options: '../options.php?table=ZzItem'
                            },
                            Quantity: {
                                title: 'Количество',
                                width: '10%'
                            },
                            Price: {
                                title: 'Цена',
                                width: '10%'
                            },
                            Sum: {
                                title: 'Сумма',
                                width: '10%'
                            },
                            IncomeId: {
                                list: false
                            },
                            ExpireDate: {
                                title: 'Срок годности',
                                width: '12%',
                                type: 'date',
                                displayFormat: 'yy.mm.dd'
                            }
                        },
                        // events
                        formCreated:  function(event,data){
                            $( "#Edit-Quantity" ).change(function() {
                                $("#Edit-Sum").val($('#Edit-Price').val()*$('#Edit-Quantity').val());
                            });
                            $( "#Edit-Price" ).change(function() {
                                $("#Edit-Sum").val($('#Edit-Price').val()*$('#Edit-Quantity').val());
                            });
                            $( "#Edit-Sum" ).change(function() {
                                $("#Edit-Price").val($('#Edit-Sum').val()/$('#Edit-Quantity').val());
                            });
                            $( "#Edit-IncomeId" ).parent().parent().hide();
                            var id = getid();
                            $("#Edit-ItemId [value='" + id + "']").attr("selected", "selected");
                        },
                        formSubmitting(event, data){
                            if($("#Edit-ItemId").val() === '0'){
                                alert('Значение товара не выбрано!');   
                                return false;
                            }
                            if($('#Edit-Quantity').val() === ''){
                                alert('Значение количества не выбрано!');   
                                return false;
                            }
                            if($("#Edit-Price").val() === ''){
                                alert('Значение цены не выбрано!');   
                                return false;
                            }
                            if($("#Edit-Sum").val() === ''){
                                alert('Значение суммы не выбрано!');   
                                return false;
                            }
                            if($('#Edit-ExpireDate').val() === ''){
                                alert('Значение срока годности не выбрано!');   
                                return false;
                            }
                        }
                });

                //Load person list from server
                $('#DetailsContainer').jtable('load');

            });
        // получу по штрихкоду id товара    
        function getid() {       
            var value = barcode.val();
            if (value === ''){
                return '';
            }
            var ItemId = '';
            $.ajax({ //Not found in cache, get from server
                url: 'getitemidbybarcode.php',
                type: 'POST',
                dataType: 'json',
                async: false,
                processData: false,
                data: "barcode="+value,
                success: function (data) {
                    if (data.Result != 'OK') {
                        alert(data.Message);
                        return;
                    }
                    ItemId = data.ItemId;
                } 
            });
            return  ItemId;
        }

        $(window).keyup(function(e) {
            if(e.keyCode === 13) { // клавиша "Enter"
                if (barcode.val() != ""){
                    $('.jtable-add-record').click();
                } 
                barcode.focus();

            }
            if(e.keyCode === 107 ) { // клавиша "+"
                $('.jtable-add-record').click();
            }
        }).keyup();
    </script>

</div>

<?php require_once ("footer.html");?>