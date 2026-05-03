<?php
session_start();
session_destroy();
setcookie('form_values', '', time() - 3600, '/');
header('Location: index.php');
exit;