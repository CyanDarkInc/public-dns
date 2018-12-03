#!/usr/bin/php -q
<?php

echo "\033[31m ---> Updating Sources \r\n\033[0m";

$destination = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autogen');

$sources = [
	'base' => 'http://pgl.yoyo.org/adservers/serverlist.php?hostformat=bindconfig&showintro=0&mimetype=plaintext&zonefilename=/etc/bind/nullzonefile.txt',
	'malware' => 'http://mirror1.malwaredomains.com/files/spywaredomains.zones'
];

$replacements = [
	'/etc/namedb/blockeddomain.hosts' => '/etc/named/zones/db.nullroute',
	'/etc/bind/nullzonefile.txt' => '/etc/named/zones/db.nullroute',
	'"  {' => '" { ',
	'type master; file' => 'type master; notify no; file',
	'";}; ' => '"; };',
	'	" { ' => '" { ',
	'	' => ' ',
	'_' => '-'
];

foreach ($sources as $type => $source) {
	echo "\033[31m ---> Updating: $type-autogen.conf \r\n\033[0m";

	$content = file_get_contents($source);
	$clean = [];
	$lines = explode("\n", $content);

	foreach ($lines as $entry) {
		if (strlen($entry) < 150) {
			$firts_char = substr($entry, 0, 1);

			if ($firts_char !== '/' && $firts_char !== '#' && !empty($entry) && !(strpos($entry, '_') !== false)) {
				foreach ($replacements as $find => $replace) {
					$entry = str_replace($find, $replace, $entry);
				}

				$id = md5(strtolower(trim($entry)));
				$clean[$id] = trim($entry);
			}
		}
	}

	$file = implode("\r\n", $clean);
	@file_put_contents($destination . DIRECTORY_SEPARATOR . $type . '-autogen.conf', $file);
}