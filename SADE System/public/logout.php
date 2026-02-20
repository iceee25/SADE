<?php
session_start();
session_destroy();
header('Location: schedule-management.php');
exit;
?>
