<?php
function toUrlRewriting($str)
{
	$str = utf8_decode($str);
	$tofind = "�����������������������������������������������������";
	$replac = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn";		
	$str = strtolower(strtr($str, $tofind, $replac));
	$str = preg_replace('/[^A-Za-z0-9]/', '-', $str);
	$str = preg_replace('/-{2,}/', '-', $str);
	$str = $str[0]=='-'?substr($str, 1, strlen($str)):$str;
	$str = $str[strlen($str)-1]=='-'?substr($str, 0, strlen($str)-1):$str;
	$str = substr($str, 0, 250);
	return $str;
}
?>