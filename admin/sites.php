<?php 
$dir = "../";
$title_here = "Список складов";
include("header.html");
?>
<div id="ItemTableContainer" class="tablecontainer"></div>
    <script type="text/javascript">

            $(document).ready(function () {

                //Prepare jTable
                    $('#ItemTableContainer').jtable({
                            title: 'Склады',
                            sorting: true,
                            defaultSorting: 'Name ASC',
                            actions: {
                                    listAction:   '../actions.php?action=list&table=ZzSite',
                                    createAction: '../actions.php?action=create&table=ZzSite&fields=Name',
                                    updateAction: '../actions.php?action=update&table=ZzSite&fields=Name'
//                                    deleteAction: '../actions.php?action=delete&table=ZzSite'
                            },
                            fields: {
                                Id: {
                                        key: true,
                                        create: false,
                                        edit: false,
                                        list: false
                                },
                                Name: {
                                        title: 'Наименование',
                                        width: '100%'
                                },
                        }
                    });

                    //Load person list from server
                    $('#ItemTableContainer').jtable('load');

            });

    </script>
<?php require_once ("footer.html");?>

