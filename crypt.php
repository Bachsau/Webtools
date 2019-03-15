<?php

header('Content-type: text/plain;');

if (isset($_GET['crypt']))
{
	if (isset($_GET['salt']))
	{
		$salt = $_GET['salt'];
	}
	else
	{
		$random = mt_rand(); 
		$rand64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"; 
		$salt = substr($rand64,$random%64,1).substr($rand64,($random/64)%64,1);
	}
	echo crypt($_GET['crypt'], $salt);
}
elseif (isset($_GET['md5']))
{
	echo md5($_GET['md5']);
}
elseif (isset($_GET['sha1']))
{
	echo sha1($_GET['sha1']);
}
elseif (isset($_GET['crc32']))
{
	echo crc32($_GET['crc32']);
}
elseif (isset($_GET['base64']))
{
	echo base64_encode($_GET['base64']);
}
elseif (isset($_GET['unbase64']))
{
	echo base64_decode($_GET['unbase64']);
}
else
{
	echo 'Nothing to encrypt!';
}
