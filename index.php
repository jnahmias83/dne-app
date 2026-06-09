<?php
session_start();
if(empty($_SESSION['id_user'])){
    header('Location:login.php');
    exit;
}
?><!DOCTYPE html>
<html>
<head><meta charset="utf-8">
<noscript><meta http-equiv="refresh" content="0;url=projects.php"></noscript>
</head>
<body>
<script>
try {
    let last = localStorage.getItem('dne_last_url');
    if(last && last.indexOf('meetings.php') !== -1){
        window.location.href = last;
    } else {
        window.location.href = 'projects.php';
    }
} catch(e) {
    window.location.href = 'projects.php';
}
</script>
</body>
</html>
