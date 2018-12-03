#!/usr/bin/php -q
<?php

echo "\033[31m ---> Downloading Sources \r\n\033[0m";

$destination = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autogen');

$ip = '0.0.0.0';

$sources = [
	'base' => 'http://pgl.yoyo.org/adservers/serverlist.php?hostformat=bindconfig&showintro=0&mimetype=plaintext&zonefilename=/etc/bind/nullzonefile.txt',
	'malware' => 'http://mirror1.malwaredomains.com/files/spywaredomains.zones',
	'ads' => realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'ads.conf'),
	'shock-sites' => realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'shock-sites.conf'),
	'windows-telemetry' => realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'windows-telemetry.conf')
];

$replacements = [
	'zone "' => ''
];

$hosts = [];

foreach ($sources as $type => $source) {
	echo "\033[31m ---> Processing: $type \r\n\033[0m";

	$content = file_get_contents($source);
	$lines = explode("\n", $content);

	foreach ($lines as $entry) {
		$firts_char = substr($entry, 0, 1);

		if ($firts_char !== '/' && $firts_char !== '#' && !empty($entry)) {
			foreach ($replacements as $find => $replace) {
				$entry = str_replace($find, $replace, $entry);
			}

			$parts = explode('"', $entry, 2);
			$entry = isset($parts[0]) ? $parts[0] : $entry;

			$id = md5(strtolower(trim($entry)));
			$hosts[$id] = $ip . ' ' . trim($entry);
		}
	}
}

$file = implode("\r\n", $hosts);
@file_put_contents($destination . DIRECTORY_SEPARATOR . 'autogen.hosts', $file);
