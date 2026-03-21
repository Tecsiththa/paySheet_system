<?php
session_start();

// Session destroy
session_unset();
session_destroy();

// Redirect to login
header("Location: login.php");
exit();
?>