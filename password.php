<?php

header('Content-Type: text/plain; charset=ASCII');
header('Expires: 0');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

if (isset($_GET['source'])) {
	header('Content-Type: text/html; charset=UTF-8');
	highlight_file(__FILE__);
	exit();
}


# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
# FOUND SOMETHING INSECURE? PLEASE REPORT!
# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

$length = (isset($_GET['length']) && ctype_digit($_GET['length']) && ($_GET['length'] > 0)) ? intval($_GET['length']) : 16;

if ($length > 9999){
	echo "Du spinnst wohl.";
	exit(0);
}

$count = array();
$result = '';

// Zeichenarten festlegen:
// Buchstaben kommen doppelt so oft vor, wie Zahlen und Sonderzeichen
$ascii = array
(
	str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'),
	str_split('0123456789')
);

// Sonderzeichen nur hinzufügen, wenn erlaubt
if (!isset($_GET['anum']))
{
	$ascii[] = str_split('!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~');
}

// Zeichenarten zählen
$ascii_count = count($ascii);

// Zeichen mischen und zählen
for ($i = 0; $i < $ascii_count; $i++)
{
	srand();
	shuffle($ascii[$i]);
	$count[$i] = count($ascii[$i]);
}

// Passwort generieren
for ($i = 0; $i < $length; $i++)
{
	// Eine Zeichenart wählen
	mt_srand();
	$chartype = mt_rand(0, ($ascii_count - 1) * 2);
	// mt_srand();
	if ($chartype >= $ascii_count) $chartype = 0; // mt_rand(0, 1);

	// Ein Zeichen wählen
	mt_srand();
	$rand    = mt_rand(0, $count[$chartype] - 1);
	$result .= $ascii[$chartype][$rand];
}

// Groß-/Kleinschreibung
if (isset($_GET['upper']))
{
	$result = strtoupper($result);
}
elseif (isset($_GET['lower']))
{
	$result = strtolower($result);
}

// Passwort ausgeben
echo $result;
