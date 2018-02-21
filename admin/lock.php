<?php
include "../classes/db.php";

$table = 'ZzUser';
$db = new db();
$q = $db->select("Pass, Root", $table, "Name = '".$_SERVER['PHP_AUTH_USER']."'");
if ($q->error <>'') {
    throw new Exception($q->error);
}
$usr = $q->single();
$SCRIPT = filter_input(INPUT_SERVER, 'SCRIPT_NAME');
$rus = strstr($SCRIPT, 'users.php'); // проверяю на файл users.php
if ($rus !== false){ // если файл users.php
    if ($usr[Root] == 0){ // и прав нет, то
        header ('Location: index.php');  // перенаправляю на index.php
        exit();
    }
}
session_start();
if (!isset($_SERVER['PHP_AUTH_USER']) || ($_GET['action'] === 'logout' AND isset($_SESSION)) )
{
    Header ("WWW-Authenticate: Basic realm=\"Admin Page\"");
    Header ("HTTP/1.0 401 Unauthorized");
    if ($_GET['action'] == 'logout')
    {
        session_destroy();
    }
    exit();
} else {
    if (empty($usr)){
        Header ("WWW-Authenticate: Basic realm=\"Admin Page\"");
        Header ("HTTP/1.0 401 Unauthorized");
        exit();
    }

    if (md5($_SERVER['PHP_AUTH_PW'])!= $usr['Pass'])
    {
       Header ("WWW-Authenticate: Basic realm=\"Admin Page\"");
       Header ("HTTP/1.0 401 Unauthorized");
       exit();
    }
    $_SESSION['action'] = 'logout';
}
?>
