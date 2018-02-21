<?php 
$dir = "../";
$title_here = "Список пользователей";
include("header.html");
?>
<div id="ItemTableContainer" class="tablecontainer"></div>
    <script type="text/javascript">

        $(document).ready(function () {
            //Prepare jTable
            $('#ItemTableContainer').jtable({
                title: 'Пользователи',
                sorting: true,
                defaultSorting: 'Name ASC',
                actions: {
                        listAction:   '../actions.php?action=list&table=ZzUser',
                        createAction: '../actions.php?action=create&table=ZzUser&fields=Name,Pass,Root',
                        updateAction: '../actions.php?action=update&table=ZzUser&fields=Name,Pass,Root',
                        deleteAction: '../actions.php?action=delete&table=ZzUser'
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
                            width: '50%'
                    },
                    Pass: {
                            title: 'Пароль',
                            width: '25%'
                    },
                    Root: {
                            title: 'Право администратора',
                            width: '25%'
                    }
                }
            });
            //Load user list from server
            $('#ItemTableContainer').jtable('load');
        });
    </script>
<?php require_once ("footer.html");?>

