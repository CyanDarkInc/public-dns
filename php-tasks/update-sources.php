#!/usr/bin/php -q
<?php

echo "\033[31m ---> Updating Sources \r\n\033[0m";

$destination = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autogen');

$sources = [
	'base' => 'http://pgl.yoyo.org/adservers/serverlist.php?hostformat=bindconfig&showintro=0&mimetype=plaintext&zonefilename=/etc/bind/nullzonefile.txt',
    'malware' => 'http://mirror1.malwaredomains.com/files/spywaredomains.zones',
    'oisd' => 'https://raw.githubusercontent.com/sjhgvr/oisd/main/unbound_basic.txt'
];

$replacements = [
	'/etc/namedb/blockeddomain.hosts' => '/etc/named/zones/db.nullroute',
	'/etc/bind/nullzonefile.txt' => '/etc/named/zones/db.nullroute',
    'always_null' => '{ type master; notify no; file "/etc/named/zones/db.nullroute"; };',
    '." ' => '" ',
    '"  {' => '" { ',
	'type master; file' => 'type master; notify no; file',
	'";}; ' => '"; };',
	'	" { ' => '" { ',
	'	' => ' ',
	'_' => '-',
    'local-zone: ' => 'zone ',
    'server:' => ''
];

$max_elements = 32000;
$current_elements = 0;
$zones = [];
foreach ($sources as $type => $source) {
	echo "\033[31m ---> Updating: $type \r\n\033[0m";

	$content = file_get_contents($source);
	$lines = explode("\n", $content);

	foreach ($lines as $entry) {
        if ($current_elements >= $max_elements) {
            continue;
        }

		if (strlen($entry) < 150) {
            foreach ($replacements as $find => $replace) {
                $entry = str_replace($find, $replace, $entry);
            }

            $firts_char = substr($entry, 0, 1);
			if ($firts_char !== '/' && $firts_char !== '#' && !empty($entry)) {
				$id = md5(strtolower(trim($entry)));
                $zones[$id] = trim($entry);

                $current_elements++;
			}
		}
	}
}

$file = implode("\r\n", $zones);
try {
    file_put_contents($destination . DIRECTORY_SEPARATOR . 'base-autogen.conf', $file);
} catch (Exception $e) {
    print_r($e);
}
