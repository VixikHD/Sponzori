<?php

/**
 * Generátor k řešení úlohy Sponzoři (1. kolo, 8. ročník FIKSu)
 */

declare(strict_types=1);

const SPONSOR_COUNT = 200;
const ANIMAL_COUNT = 100;
const TARGET_FILE = "input.txt";

$symbols = "abcdefghijklmnopqrstuvwxyz";

$sponsors = [];
for($i = 0; $i < SPONSOR_COUNT; ++$i) {
	do {
		$name = "";
		for($j = 0, $k = mt_rand(7, 15); $j < $k; ++$j) {
			$name .= $symbols[mt_rand(0, strlen($symbols) - 1)];
		}
		$name = ucfirst($name);
	} while(in_array($name, $sponsors));

	$sponsors[] = $name;
}

$animals = [];
for($i = 0; $i < ANIMAL_COUNT; ++$i) {
	do {
		$name = "";
		for($j = 0, $k = mt_rand(7, 15); $j < $k; ++$j) {
			$name .= $symbols[mt_rand(0, strlen($symbols) - 1)];
		}
	} while(in_array($name, $animals));

	$animals[] = $name;
}

uasort($animals, fn() => mt_rand(-1, 1));

$output = ANIMAL_COUNT . " " . SPONSOR_COUNT . PHP_EOL;
foreach ($animals as $id => $name) {
	$output .= $id . " " . $name . PHP_EOL;
}

foreach ($sponsors as $sponsor) {
	$j = mt_rand(1, mt_rand(1, mt_rand(1, mt_rand(1, ANIMAL_COUNT))));
	$output .= "$sponsor $j ";
	$targets = [];
	for($i = 0; $i < $j; ++$i) {
		$targets[] = (string)mt_rand(0, ANIMAL_COUNT - mt_rand(1, intdiv(ANIMAL_COUNT, mt_rand(1, 10))));
	}

	$output .= implode(" ", $targets) . PHP_EOL;
}

file_put_contents(TARGET_FILE, $output);
