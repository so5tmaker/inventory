<?php
$dir = "../";
$title_here = "Таблица заказов ТЧ";
include("header.html");
?> 

<div id="ItemTableContainer" class="tablecontainer"></div>  
    <script type="text/javascript">
        $(document).ready(function () {
            //Prepare jTable
            $('#ItemTableContainer').jtable({
                    title: 'Таблица заказов',
                    sorting: true,
                    defaultSorting: 'ItemId ASC',
                    actions: {
                            listAction:   '../actions.php?action=list&table=ZzOrderDetail',
                            createAction: '../actions.php?action=create&table=ZzOrderDetail&fields=ItemId,Quantity,Price,Sum,OrderId',
                            updateAction: '../actions.php?action=update&table=ZzOrderDetail&fields=ItemId,Quantity,Price,Sum,OrderId',
                            deleteAction: '../actions.php?action=delete&table=ZzOrderDetail'
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
                            width: '40%',
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
                        OrderId: {
                            title: 'Номер заказа',
                            width: '10%'
                        }
                }
            });

            //Load person list from server
            $('#ItemTableContainer').jtable('load');

        });
    </script>
<?php require_once ("footer.html");?>
