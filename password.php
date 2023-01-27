<?php

if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {
	$SECURE_URL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	header('Location: ' . $SECURE_URL, true, 301);
	header('Content-Type: text/html; charset=UTF-8');
	echo "<!DOCTYPE html>\n";
	echo "<html dir=\"ltr\" lang=\"en\">\n";
	echo "\t<head>\n";
	echo "\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
	echo "\t\t<title>Encryption required</title>\n";
	echo "\t</head>\n";
	echo "\t<body>\n";
	echo "\t\t<p>This tool requires an <a href=\"${SECURE_URL}\">encrypted connection</a>.</p>\n";
	echo "\t</body>\n";
	echo "</html>\n";
	exit;
}

if (isset($_GET['source'])) {
	header('Content-Type: text/html; charset=UTF-8');
	highlight_file(__FILE__);
	exit;
}
else {
	header('Content-Type: text/plain; charset=UTF-8');
	header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	header('Expires: 0');
	header('Pragma: no-cache');
}

# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
# FOUND SOMETHING INSECURE? PLEASE REPORT!
# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

$length = (isset($_GET['length']) && ctype_digit($_GET['length'])) ? intval($_GET['length']) : 16;

if ($length < 1 || $length > 9999) {
	echo "\xe2\x9a\xa0\n";
	exit();
}

if (isset($_GET['idiot'])) {
	$uletters = str_split('ABCDEFGHJKLMNPQRSTUVWXYZ');
	$lletters = str_split('abcdefghijkmnopqrstuvwxyz');
	$digits = str_split('23456789');
}
else {
	$uletters = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
	$lletters = str_split('abcdefghijklmnopqrstuvwxyz');
	$digits = str_split('0123456789');
}
if (isset($_GET['upper'])) {
	$letters = &$uletters;
}
elseif (isset($_GET['lower'])) {
	$letters = &$lletters;
}
else {
	$letters = array_merge($uletters, $lletters);
}
$num_digits = max(1, round($length * 0.2));

if (!isset($_GET['anum'])) {
	if (isset($_GET['idiot'])) {
		$signs = str_split('!#$%()*+,-./:;=@[]_{|}');
	}
	else {
		$signs = str_split('!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~');
	}
	$num_signs = max(1, round($length * 0.25));
}
else {
	$num_signs = 0;
}

// Seed RNG
srand();

// Vary digit and sign count
$vary = floor($length / 16);
if ($vary) {
	$num_digits += rand(-$vary, $vary);
	if ($num_signs) {
		$num_signs += rand(-$vary, $vary);
	}
}

// Build password
$result = array();
if ($length > $num_digits) {
	for ($i = 0; $i < $num_digits; $i++) {
		$result[] = $digits[array_rand($digits)];
	}
	$length -= $num_digits;
}
if ($length > $num_signs) {
	for ($i = 0; $i < $num_signs; $i++) {
		$result[] = $signs[array_rand($signs)];
	}
	$length -= $num_signs;
}
for ($i = 0; $i < $length; $i++) {
	$result[] = $letters[array_rand($letters)];
}

srand();
shuffle($result);

echo implode($result);
echo "\n";
