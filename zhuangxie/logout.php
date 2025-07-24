<?php
include 'includes/auth.php';
session_destroy();
redirect('index.php');
?>
