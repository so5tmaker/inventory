<?php 
$dir = "../";
$title_here = "Список поступлений";
include("header.html");
?>
<div class="adding" >
    <button id="MyButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
        <span class="ui-button-text">Добавить</span>
    </button>
</div>
<div class="adding" >
    <button id="MyPeriod" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
        <span class="ui-button-text">Выбрать период</span>
    </button>
</div>
<div style="display: none;" id="dialog" title="Выберите период">
    <label>Выберите месяц:</label>
    <select id="cMonth" name="cMonth" class="inputbar">
        <option value="0"><<Значение не выбрано>></option>
        <option value="1" selected>Январь</option>
        <option value="2">Февраль</option>
        <option value="3">Март</option>
        <option value="4">Апрель</option>
        <option value="5">Май</option>
        <option value="6">Июнь</option>
        <option value="7">Июль</option>
        <option value="8">Август</option>
        <option value="9">Сентябрь</option>
        <option value="10">Октябрь</option>
        <option value="11">Ноябрь</option>
        <option value="12">Декабрь</option>
    </select>
    <label>Выберите год:</label>
    <input type="number" id="cYear" name="cYear" class="inputbar" value="2016" />
    <div style='text-align: center; margin-top:15px'>
        <button id="сButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false">
            <span class="ui-button-text">Выбрать</span>
        </button>
    </div>
</div>
<div id="ItemTableContainer" class="tablecontainer"></div>
    <script type="text/javascript">
            $(document).ready(function () {

                //Prepare jTable
                $('#ItemTableContainer').jtable({
                        title: 'Поступления товаров',
                        sorting: true,
                        defaultSorting: 'Id ASC',
                        actions: {
                                listAction:   '../actions.php?action=list&table=ZzIncome',
                                createAction: '../actions.php?action=create&table=ZzIncome&fields=Date,SiteId',
                                updateAction: '../actions.php?action=update&table=ZzIncome&fields=Date,SiteId'
//                                deleteAction: '../actions.php?action=delete&table=ZzIncome'
                        },
                        fields: {
                            Opens: {
                                title: '',
                                width: '2%',
                                sorting: false,
                                edit: false,
                                create: false,
                                display: function (data) {
                                    //Create an image that will be used to open child table
                                    var $img = $('<img style="cursor: pointer;" src="../img/edit.png" title="Редактировать табличную часть документа" />');
                                    //Open child table when user clicks the image
                                    $img.click(function () {
                                        window.open("single.php?id=" + data.record.Id + '&date=' + new_date(data.record.Date), '_blank');
//                                        $(function() {
//                                            $("#dialog").dialog({ 
//                                                width: '95%',
//                                                height: 'auto',
//                                                title: 'Поступление товаров № ' + data.record.Id  + ' от ' +  data.record.Date
//                                            });
//                                        });
                                    });
                                    //Return image to show on the person row
                                    return $img;
                                }
                            },
                            Id: {
                                title: '№',
                                width: '4%',
                                key: true,
                                create: false,
                                edit: false
                            },
                            SiteId: {
                                title: 'Склад',
                                width: '45%',
                                options: 'siteoptions.php?table=ZzSite'
                            },
                            Date: {
                                title: 'Дата',
                                width: '45%',
                                edit: false,
                                type: 'date',
                                displayFormat: 'yy.mm.dd'
                            }
                        },
                        addRecordButton: $('#MyButton')
                });

                //Load person list from server
                $('#ItemTableContainer').jtable('load');

            });
            // Открываю диалог выбора периода
            $('#MyPeriod').button().click(function () {
                $(function() {
                    $( "#dialog" ).dialog();
                });
            });
            
            // Нажатие на кнопку выбора диалога
            $('#сButton').button().click(function () {
                var cMonth = $('#cMonth').val();
                var cYear = $('#cYear').val();
                if (cMonth === '0'){
                    $('#ItemTableContainer').jtable('load');
                    $( "#dialog" ).dialog('close');
                    return false;
                }
                $('#ItemTableContainer').jtable('load', {
                    cMonth: cMonth,
                    cYear:  cYear
                });
                $( "#dialog" ).dialog('close');
            }).click();
    </script> 
<?php require_once ("footer.html");?>