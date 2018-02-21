<?php 
$dir = "../";
$title_here = "Список номенклатуры";
include("header.html");
$dir = "../";
?>
<!--<div class="adding" >
    <button id="CrtBars" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
        <span class="ui-button-text">Создать штрихкоды</span>
    </button>
</div>-->
<div id="helpDivTitle">
    В названии продуктов не должно быть символов "," и ";" !
</div>
<!-- Upload Button-->
<div id="upload" class="adding">Загрузить файл</div>
<div id="message" class="msgcontainer"></div>
<!--List Files-->
<ul id="files" ></ul>

<div class="adding" >
    Поле поиска (выберите по какому полю нужно искать и нажмите клавишу «Ввод»):
    <select id="record" name="record" class="inputbar">
        <option value="Name">Наименование</option>
        <option value="BarCode" selected="selected">Штрихкод</option>
    </select>
    <input onclick="this.select()" type="text" name="barcode" id="barcode" class="inputbar" />
    <!--<button type="submit" id="LoadRecordsButton">Load records</button>float: left;  width: 25%;-->
</div>
<div class="printbarcode">
    <div class="floatleftbtn">
        <button id="PrintPrice" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
            <span class="ui-button-text">Распечатать ценник</span>
        </button>
    </div>
    <div class="floatleftbtn">
        <button id="PrintAllButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
            <span class="ui-button-text">Распечатать штрихкоды</span>
        </button>
    </div>
    <div class="floatleftcap"><div class="divcap">Количество:</div></div>
    <div class="floatleft"><input maxlength="3" type="text" name="quant" id="quant" class="inputamount" /> </div>
</div>
<div style="clear: both;"></div>

<!--<div class="adding" >
    <input type="button" name="MyButton" id="MyButton" value="Добавить" />
    <button id="MyButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
        <span class="ui-button-text">Добавить</span>
    </button>
</div>-->

<div id="ItemTableContainer" class="tablecontainer"></div>
    <script type="text/javascript">
            var barcode = $("#barcode");
            var record = $("#record");
            var paramid;
            $(document).ready(function () {

                //Prepare jTable
                $('#ItemTableContainer').jtable({
                        title: 'Номенклатура',
                        sorting: true,
                        defaultSorting: 'Date ASC',
                        selecting: true, //Enable selecting
                        multiselect: true, //Allow multiple selecting
                        selectingCheckboxes: true, //Show checkboxes on first column
//                        selectOnRowClick: false, //Enable this to only select using checkboxes
                        actions: {
                                listAction:   '../actions.php?action=list&table=ZzItem&paramyes=1',
//                                createAction: '../actions.php?action=create&table=ZzItem&fields=Name,BarCode',
                                updateAction: '../actions.php?action=update&table=ZzItem&fields=Name,BarCode'
//                                deleteAction: '../actions.php?action=delete&table=ZzItem'
                        },
                        fields: {
                            Id: {
                                    key: true,
                                    create: false,
                                    edit: false,
                                    list: false
                            },
                            //CHILD TABLE DEFINITION FOR "Prices"
                            Prices: {
                                title: '',
                                width: '5%',
                                sorting: false,
                                edit: false,
                                create: false,
                                display: function (ItemData) {
                                    //Create an image that will be used to open child table
                                    var $img = $('<img src="../img/list_metro.png" title="Редактирование цены товара" />');
                                    //Open child table when user clicks the image
                                    $img.click(function () {
                                        $('#ItemTableContainer').jtable('openChildTable',
                                                $img.closest('tr'), //Parent row
                                                {
                                                title: 'Цена товара - ' + ItemData.record.Name,
                                                actions: {
                                                    listAction:   '../actions.php?action=list&table=ZzPrice&paramid=ItemId&paramyes=1&params='+ItemData.record.Id,
                                                    createAction: '../actions.php?action=create&table=ZzPrice&fields=Date,ItemId,Value',
                                                    updateAction: '../actions.php?action=update&table=ZzPrice&fields=Date,ItemId,Value'
//                                                    deleteAction: '../actions.php?action=delete&table=ZzPrice'
                                                },
                                                fields: { 
                                                    Id: {
                                                        key: true,
                                                        create: false,
                                                        edit: false,
                                                        list: false
                                                    },
                                                    Date: {
                                                        title: 'Дата',
                                                        width: '15%',
                                                        type: 'date',
                                                        displayFormat: 'yy-mm-dd'
                                                    },
                                                    ItemId: {
                                                        type: 'hidden',
                                                        defaultValue: ItemData.record.Id
                                                    },
                                                    Value: {
                                                        title: 'Цена',
                                                        width: '10%'
                                                    }
                                                }
                                            }, function (data) { //opened handler
                                                data.childTable.jtable('load');
                                            });
                                    });
                                    //Return image to show on the person row
                                    return $img;
                                }
                            },
                            Name: {
                                    title: 'Наименование',
                                    width: '60%'
                            },
                            BarCode: {
                                    title: 'Штрихкод',
                                    width: '20%'
                            },
                            Date: {
                                title: 'Изменен',
                                width: '20%',
                                edit: false
                            }
                    },
                    // events
                    formCreated:  function(event,data){
                        if (data.formType === 'create'){
                            if (record.val() === 'Name') {
                            $('#Edit-Name').val(barcode.val()); 
                            } else {
                                $('#Edit-BarCode').val(barcode.val());
                            }
                            $('#AddRecordDialogSaveButton .ui-button-text').text('Добавить (Ctrl + Enter)');
                        }
                        if (data.formType === 'edit'){
                            $('#EditDialogSaveButton .ui-button-text').text('Изменить (Ctrl + Enter)');
                        } 
                        $('#Edit-Name').width('400px');
                        $('#Edit-Value').css({
                            'text-align': 'Right'
                        });
                    },
                    addRecordButton: $('#MyButton'),
                    //Register to selectionChanged event to hanlde events
                    selectionChanged: function () {
                        //Get all selected rows
                        var $selectedRows = $('#ItemTableContainer').jtable('selectedRows');

                        $('#message').empty();
                        $('#back-top').html('<a href="#top">^Наверх^</a>');
                        if ($selectedRows.length > 0) {
                            //Show selected rows
                            $selectedRows.each(function () {
                                $('#message').html(
                                    'Выделено строк: ' + $selectedRows.length
                                );
                                $('#back-top').html(
                                        '<a href="#top">^Наверх^</a><br>Выделено строк: ' + $selectedRows.length
                                );
                            });
                        } 
                    }
                });

                //Load person list from server
                $('#ItemTableContainer').jtable('load');
                var $upload = $('#upload');
                $upload.focus();
                var $barcode = $('#barcode');
                $barcode.focus();
            });
            
            //Print selected items
            $('#PrintAllButton').button().click(function () {
                var $selectedRows = $('#ItemTableContainer').jtable('selectedRows');
                var Id = "", glue = "";
                $selectedRows.each(function () {
                    var rcrd = $(this).data('record');
                    glue = (Id === '') ? '' : ",";
                    Id+= glue + rcrd.Id;
                });
                
                if (Id !== ""){
                    $.ajax({ //Not found in cache, get from server
                        url: 'barcode/barcode.php',
                        type: 'POST',
                        dataType: 'json',
                        async: false,
                        processData: false,
                        data: "id="+Id+'&quant='+$('#quant').val() 
                    });
                    window.open("barcode/barcode.php?print", '_blank');
//                    window.open('barcode/barcode.php?id='+Id+'&quant='+$('#quant').val()+'&print', '_blank');
                }
            });
            
            $('#PrintPrice').button().click(function () {
                var $selectedRows = $('#ItemTableContainer').jtable('selectedRows');
                var glue = "", Id = '';
                $selectedRows.each(function () {
                    var rcrd = $(this).data('record');
                    glue = (Id === '') ? '' : ",";
                    Id+= glue + rcrd.Id;
                });
                if (Id !== ""){
                    $.ajax({ //Not found in cache, get from server
                        url: 'barcode/barcode.php',
                        type: 'POST',
                        dataType: 'json',
                        async: false,
                        processData: false,
                        data: "id="+Id 
                    });
                    window.open("barcode/barcode.php?print", '_blank');
//                    window.open("barcode/barcode.php?BarCode="+BarCode+'&quant='+$('#quant').val()+'&Name='+Name+'&id='+Id, '_blank');
                }
            });
            
            $('#CrtBars').button().click(function () {
                $.ajax({ //Not found in cache, get from server
                    url: 'barcode/createbarcode.php',
                    type: 'POST',
                    dataType: 'json',
                    async: false,
                    processData: false,
                    data: "create=1000", 
                    success: function (data) {
                        if (data.Result != 'OK') {
                            alert(data.Message);
                            return;
                        } else {
                            alert(data.Message);
                            return;
                        }    
                    } 
                });           
            });

            $(function(){
		var btnUpload=$('#upload');
		var status=$('#message');
		new AjaxUpload(btnUpload, {
			action: 'upload-file.php',
			name: 'uploadfile',
			onSubmit: function(file, ext){
                            if (! (ext && /^(csv|csv)$/.test(ext))){ 
                                // extension is not allowed 
                                showmess('Можно загружать только файлы csv!', 'red', false);
                                return false;
                            }
                            status.text('Загрузка...');
			},
			onComplete: function(file, response){
                            var data = JSON.parse(response);
                            //On completion clear the status
                            status.text('');
                            //Add uploaded file to list
                            if(data.Result === "OK"){
                                if (data.Message === ""){
                                    showmess('Загрузка файла успешно завершена!', 'green', false);
                                }else{
                                    showmess(data.Message, 'blue', false);
                                }
                                $('#ItemTableContainer').jtable('reload');
                            } else{
                                showmess(data.Message, 'red', false);
                            }
			}
//                        error: function (data, status, msg) {
//                            alert('Error');
//                        }
		});
            });

            barcode.keypress(function(e) {
                if(e.keyCode === 13) {
                    var value = barcode.val();
                    if (value === ''){
                        $('#ItemTableContainer').jtable('load');
                        return false;
                    }
                    $('#ItemTableContainer').jtable('load', {
                        BarCode: barcode.val(),
                        record:  record.val()
                    });
                }
            }).keypress();
            
            $(window).keyup(function(e) {
                if(e.keyCode === 13 && e.ctrlKey) {
                    if ($('#AddRecordDialogSaveButton .ui-button-text').text() === 'Добавить (Ctrl + Enter)') {
                        $('#AddRecordDialogSaveButton').click();
                    } 
                    if ($('#EditDialogSaveButton .ui-button-text').text() === 'Изменить (Ctrl + Enter)') {
                        $('#EditDialogSaveButton').click();
                    }
                }
            }).keyup();
            
            record.change(function() {
              barcode.focus();
            });
    </script>
    
<?php require_once ("footer.html");?>

