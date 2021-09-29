<?php

/**
 * Rešení úlohy Sponzoři (1. kolo, 8. ročník FIKSu)
 */

declare(strict_types=1);

use vixikhd\sponzori\data\Animal;
use vixikhd\sponzori\data\Pair;
use vixikhd\sponzori\data\Sponsor;
use vixikhd\sponzori\TaskSolver;
use vixikhd\sponzori\Utils;

/**
 * Začátek konfigurace programu
 */

// Odkomentujte řádky níže pro zadání inputu skrze stdin
//define("INPUT_STREAM", STDIN);
//define("OUTPUT_STREAM", STDOUT);

// Odkomentujte řádky níže pro zadání inputu skrze textový soubor
define("INPUT_STREAM", fopen("input.txt", "r"));
define("OUTPUT_STREAM", fopen("output.txt", "w"));

// V některých případech v phpčku blbne nová řádka a je potřeba použít místo PHP_EOL \n nebo naopak
define("INPUT_LINE_ENDING", PHP_EOL);
define("OUTPUT_LINE_ENDING", PHP_EOL);

// Debug...
define("ENABLE_DEBUG", false);
define("RESOLVE_INFO_FILE", true);

/**
 * Konec konfigurace algoritmu
 */

if(!is_resource(INPUT_STREAM)) {
	throw new ErrorException("Invalid input stream");
}

if(!is_resource(OUTPUT_STREAM)) {
	throw new ErrorException("Invalid output stream");
}

spl_autoload_register(function (string $class): void {
	include "src" . DIRECTORY_SEPARATOR . str_replace("vixikhd\\sponzori\\", "", $class) . ".php";
});

/**
 * Funkce sloužící k načtení dat a vytvoření třídy, která bude úlohu řešit
 */
function loadSolver(): TaskSolver {
	$readLine = fn() => stream_get_line(INPUT_STREAM, 0xfff, INPUT_LINE_ENDING);

	$animals = $sponsors = $pairs = [];

	[$animalCount, $sponsorCount] = Utils::toIntArray(explode(" ", $readLine()));
	for($i = 0; $i < $animalCount; ++$i) {
		[$id, $name] = explode(" ", $readLine());
		$id = (int)$id;

		$animals[$id] = new Animal($name);
	}
	for($i = 0; $i < $sponsorCount; ++$i) {
		$line = explode(" ", $readLine());
		$name = array_shift($line);

		$sponsors[$i] = new Sponsor($name);

		$count = array_shift($line);
		if((int)$count != ($invalidCount = count($line))) {
			throw new ErrorException("Invalid input (Expected $count animals instead of $invalidCount for sponsor $name)");
		}

		foreach (Utils::toIntArray($line) as $pairedAnimalId) {
			$pairs[Utils::pairHash($i, $pairedAnimalId)] = new Pair($i, $pairedAnimalId);
		}
	}

	return new TaskSolver($animals, $sponsors, $pairs);
}

function displayOutput(TaskSolver $solver): void {
	// Ano / Ne
	fwrite(OUTPUT_STREAM, (count($solver->getAnimals()) == count($solver->getSolution()->getPairs()) ? "Ano" : "Ne") . OUTPUT_LINE_ENDING);

	// Spolupráce
	$pairs = array_combine(
		keys: array_map(fn(int $hash) => $solver->getSponsors()[Utils::getSponsorIdFromHash($hash)]->getName(), $solver->getSolution()->getPairs()),
		values: array_map(fn(int $hash) => $solver->getAnimals()[Utils::getAnimalIdFromHash($hash)]->getName(), $solver->getSolution()->getPairs())
	);
	asort($pairs);

	foreach ($pairs as $sponsor => $animal) {
		fwrite(OUTPUT_STREAM, "$animal $sponsor" . OUTPUT_LINE_ENDING);
	}
}

$startTime = microtime(true);
$solver = loadSolver();
$solver->debug("Loaded TaskSolver in " . ($loadTime = round(microtime(true) - $startTime, 3)) . " seconds! Trying to solve the task...");

$startTime = microtime(true);
$solver->run();
$solver->debug("Task solved in " . ($resolveTime = round(microtime(true) - $startTime, 3)) . " seconds!");

displayOutput($solver);

if(defined("RESOLVE_INFO_FILE") && RESOLVE_INFO_FILE) {
	yaml_emit_file("resolve_info.yml", [
		"load-time" => $loadTime,
		"resolve-time" => $resolveTime,
		"sponsor-count" => count($solver->getSponsors()),
		"animal-count" => count($solver->getAnimals())
	]);
}

fclose(INPUT_STREAM);
fclose(OUTPUT_STREAM);
