#!/usr/bin/php -q
<?php

echo "\033[31m ---> Updating Contributions \r\n\033[0m";

$destination = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autogen');

$sources = [
	'ads' => realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'ads.conf'),
	'shock-sites' => realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'shock-sites.conf'),
	'windows-telemetry' => realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'windows-telemetry.conf')
];

$replacements = [
	'"  {' => '" { ',
	'type master; file' => 'type master; notify no; file',
	'";}; ' => '"; };',
	'	" { ' => '" { ',
	'	' => ' ',
	'_' => '-'
];

$clean = [];

foreach ($sources as $type => $source) {
	echo "\033[31m ---> Updating: $type.conf \r\n\033[0m";

	$content = file_get_contents($source);

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
}

$file = implode("\r\n", $clean);
@file_put_contents($destination . DIRECTORY_SEPARATOR . 'other-autogen.conf', $file);
