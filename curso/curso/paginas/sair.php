<?php
session_start();
session_destroy();                 // apaga o login da memória
header("Location: ../index.php");
exit;