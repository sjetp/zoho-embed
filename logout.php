<?php include('session.php'); ?>
<?php
session_destroy();
echo "<script>alert('Logout Successfully'); location.replace('login.php');</script>";
?>