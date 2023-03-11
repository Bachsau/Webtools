<?php
declare(strict_types=1);

header('Content-Type: text/plain; charset=US-ASCII');
header('Cache-Control: no-store, max-age=0');
header('X-Robots-Tag: noindex');

if (array_key_exists('hostname', $_GET) && $_GET['hostname'] != '0') {
	echo gethostbyaddr($_SERVER['REMOTE_ADDR']) . "\n";
}
else {
	echo $_SERVER['REMOTE_ADDR'] . "\n";
}
