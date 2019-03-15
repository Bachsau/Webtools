<?php header('Content-Type: text/plain');echo isset($_GET['hostname'])?gethostbyaddr($_SERVER['REMOTE_ADDR']):$_SERVER['REMOTE_ADDR'];
