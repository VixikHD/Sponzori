<?php /** @noinspection PhpDefineCanBeReplacedWithConstInspection */

declare(strict_types=1);

namespace vixikhd\sponzori {

	use ErrorException;
	use function array_key_exists;
	use function array_map;
	use function array_search;
	use function array_shift;
	use function count;
	use function current;
	use function define;
	use function explode;
	use function file_exists;
	use function fwrite;
	use function implode;
	use function in_array;
	use function is_numeric;
	use function is_resource;
	use function memory_get_usage;
	use function microtime;
	use function round;
	use function set_error_handler;
	use function sort;
	use function stream_get_line;
	use function yaml_emit_file;
	use function yaml_parse_file;
	use const CHECK_SOLUTION;
	use const ENABLE_DEBUG;
	use const GENERATE_RANDOM_INPUT;
	use const OUTPUT_LINE_ENDING;
	use const OUTPUT_STREAM;
	use const STATS;
	use const STDOUT;

	/**
	 * Konfigurace
	 */

	// Odkomentujte řádky níže pro zadání inputu skrze stdin
//	define("INPUT_STREAM", STDIN);
	define("OUTPUT_STREAM", STDOUT);

	// Odkomentujte řádky níže pro zadání inputu skrze textový soubor
	define("INPUT_STREAM", fopen("input.txt", "r"));
//	define("OUTPUT_STREAM", fopen("output.txt", "w"));

	// Problémy s windowsem
	define("INPUT_LINE_ENDING", PHP_EOL);
	define("OUTPUT_LINE_ENDING", PHP_EOL);

	// Debug...
	define("ENABLE_DEBUG", false);
	define("CHECK_SOLUTION", true); // Checky jdou do stdoutu
	define("STATS", true); // Staty jdou do stdoutu
	define("GENERATE_RANDOM_INPUT", true);

	/**
	 * Inicializace
	 */

	if(GENERATE_RANDOM_INPUT) {
		require "generator.php";
	}

	if(!is_resource(INPUT_STREAM)) {
		throw new ErrorException("Invalid input stream");
	}
	if(!is_resource(OUTPUT_STREAM)) {
		throw new ErrorException("Invalid output stream");
	}

	// Z warningu se mohou stát errory -> snazší nalezení chyb
	set_error_handler(fn(int $errno, string $errstr, string $errfile, int $errline, ?array $errcontext = []) => throw new ErrorException($errstr, 0, $errno, $errfile, $errline));

	function readLine(): string {
		return stream_get_line(INPUT_STREAM, 0xfff, INPUT_LINE_ENDING);
	}

	function toIntArray(array $array): array {
		return [(int)$array[0], (int)$array[1]];
	}

	function toTypeSafeArray(array $array): array {
		return array_map(fn(string $val) => (is_numeric($val) ? (int)$val : $val), $array); // tohle by šlo optimalizovat
	}

	$startTime = microtime(true);
	$startMemoryUsage = memory_get_usage();

	/**
	 * Načtení dat
	 */
	[$animalCount, $sponsorCount] = toIntArray(explode(" ", readLine()));

	/** @var array<int, string> $animals */
	$animals = [];
	/** @var array<int, string> $sponsors */
	$sponsors = [];

	/** @var int[] $pairs */
	$pairs = [];

	for($i = 0; $i < $animalCount; ++$i) {
		[$id, $name] = toTypeSafeArray(explode(" ", readLine()));

		$animals[$id] = $name;
	}

	for($i = 0; $i < $sponsorCount; ++$i) {
		$lineData = toTypeSafeArray(explode(" ", readLine()));

		$name = array_shift($lineData);
		$targetAnimalCount = array_shift($lineData);
		if(count($lineData) != $targetAnimalCount) {
			throw new ErrorException("Invalid input given");
		}

		$sponsors[$i] = $name;

		foreach($lineData as $id) {
			$pairs[$i << 8 | $id] = [$i, $id];
		}
	}

	/** @var array<int, array<int, int>> $sponsorsByAnimals */
	$sponsorsByAnimals = [];
	/** @var array<int, array<int, int>> $animalsBySponsors */
	$animalsBySponsors = [];

	foreach($pairs as $hash => [$sponsorId, $animalId]) {
		$sponsorsByAnimals[$animalId][$sponsorId] = $sponsorId;
		$animalsBySponsors[$sponsorId][$animalId] = $animalId;
	}

	$maxMemoryUsage = memory_get_usage();

	/**
	 * Ta složitá část
	 */

	/** @var list<array{0: int $sponsorId, 1: int $animalId}> $solution */
	$solution = [];

	$debugProgress = ENABLE_DEBUG ? function() use (&$animalsBySponsors, &$sponsorsByAnimals, &$solution, $animals, $sponsors): void {
		echo "\n\n\nSolution:\n";
		foreach($solution as [$sponsorId, $animalId]) {
			echo "$animals[$animalId] $sponsors[$sponsorId]\n";
		}

		echo "\nsponsors by animals:\n";
		foreach($sponsorsByAnimals as $animalId => $sponsorIds) {
			echo "$animals[$animalId] => [" . implode(", ", array_map(fn(int $id) => $sponsors[$id], $sponsorIds)) . "]\n";
		}

		echo "\nanimals by sponsors:\n";
		foreach($animalsBySponsors as $sponsorId => $animalIds) {
			echo "$sponsors[$sponsorId] => [" . implode(", ", array_map(fn(int $id) => $animals[$id], $animalIds)) . "]\n";
		}
	} : fn() => null;

	do {
		$debugProgress();
		foreach($sponsorsByAnimals as $animalId => $sponsorIds) {
			if(count($sponsorIds) == 1) {
				$solution[] = [$sponsorId = current($sponsorIds), $animalId];

				unset($sponsorsByAnimals[$animalId]);
				foreach($animalsBySponsors[$sponsorId] as $animalId) {
					if(!isset($sponsorsByAnimals[$animalId][$sponsorId])) {
						continue;
					}

					unset($sponsorsByAnimals[$animalId][$sponsorId]);
					if(count($sponsorsByAnimals[$animalId]) == 0) {
						unset($sponsorsByAnimals[$animalId]);
					}
				}
				unset($animalsBySponsors[$sponsorId]);
				continue 2;
			}
		}
		foreach($animalsBySponsors as $sponsorId => $animalIds) {
			if(count($animalIds) == 1) {
				$solution[] = [$sponsorId, $animalId = current($animalIds)];

				unset($animalsBySponsors[$sponsorId]);
				foreach($sponsorsByAnimals[$animalId] as $sponsorId) {
					if(!isset($animalsBySponsors[$sponsorId][$animalId])) {
						continue;
					}
					unset($animalsBySponsors[$sponsorId][$animalId]);
					if(count($animalsBySponsors[$sponsorId]) == 0) {
						unset($animalsBySponsors[$sponsorId]);
					}
				}

				unset($sponsorsByAnimals[$animalId]);
				continue 2;
			}
		}
		foreach($animalsBySponsors as $sponsorId => $animalIds) {
			$solution[] = [$sponsorId, $animalId = current($animalIds)];


			foreach($sponsorsByAnimals[$animalId] as $anotherSponsorId) {
				if(!isset($animalsBySponsors[$anotherSponsorId][$animalId])) {
					continue;
				}
				unset($animalsBySponsors[$anotherSponsorId][$animalId]);
				if(count($animalsBySponsors[$anotherSponsorId]) == 0) {
					unset($animalsBySponsors[$anotherSponsorId]);
				}
			}
			foreach($animalIds as $anotherAnimalId) {
				if(!isset($sponsorsByAnimals[$anotherAnimalId][$sponsorId])) {
					continue;
				}
				unset($sponsorsByAnimals[$anotherAnimalId][$sponsorId]);
				if(count($sponsorsByAnimals[$anotherAnimalId]) == 0) {
					unset($sponsorsByAnimals[$anotherAnimalId]);
				}
			}

			unset($animalsBySponsors[$sponsorId]);
			unset($sponsorsByAnimals[$animalId]);
			continue 2;
		}
	} while(0 != count($animalsBySponsors));

	$merged = count($animals) == count($solution);

	/**
	 * Vypsání outputu
	 */

	$translatedSolution = array_map(fn(array $ids) => "{$animals[$ids[1]]} {$sponsors[$ids[0]]}", $solution);
	sort($translatedSolution);
	fwrite(OUTPUT_STREAM, ($merged ? "Ano" : "Ne") . OUTPUT_LINE_ENDING);
	foreach($translatedSolution as $line) {
		fwrite(OUTPUT_STREAM, $line . OUTPUT_LINE_ENDING);
	}

	/**
	 * Bordel
	 */
	if(!STATS) {
		goto check;
	}

	$executionTime = round(microtime(true) - $startTime, 4);
	$stats = [];
	if(file_exists("stats.yml")) {
		$stats = yaml_parse_file("stats.yml");
	}

//	$pairCount = count($pairs);
	$data = $stats[$index = "$animalCount:$sponsorCount"/*.":$pairCount"*/] ?? [];
	$data[] = $executionTime;
	sort($data);

	$stats[$index] = $data;
	yaml_emit_file("stats.yml", $stats);

	$pos = array_search($executionTime, $data) + 1;

	$averageTime = 0;
	foreach($data as $val) {
		$averageTime += $val;
	}

	$averageTime = round($averageTime / count($data), 4);

	echo "\n\n";
	echo "Current test is on #$pos position in comparison to other ones with similar input.\n";
	echo "Similar attempts: " . (count($data) - 1) . "\n";
	echo "Execution time: " . $executionTime . "s\n";
	echo "Average execution time: " . $averageTime . "s\n";
	echo "Maximum memory usage: " . round((($maxMemoryUsage - $startMemoryUsage) / (1024 ** 2)), 4) . "MB / " . round($startMemoryUsage / (1024 * 2), 4) . "MB\n";

	check:
	if(!CHECK_SOLUTION) {
		return;
	}

	echo "\n";

	$checkedSponsors = $checkedAnimals = [];
	foreach($solution as [$sponsorId, $animalId]) {
		if(in_array($sponsorId, $checkedSponsors)) {
			echo "Check failed (Solution contains duplicate sponsor $sponsors[$sponsorId])\n";
			return;
		}
		if(in_array($animalId, $checkedAnimals)) {
			echo "Check failed (Solution contains duplicate animal $animals[$animalId]\n)";
			return;
		}
		if(!array_key_exists($sponsorId << 8 | $animalId, $pairs)) {
			echo "Check failed (Input does not allow to merge $animals[$animalId] with $sponsors[$sponsorId])\n";
		}
		$checkedSponsors[] = $sponsorId;
		$checkedAnimals[] = $animalId;
	}

	if($merged) {
		echo "Check success\n";
		return;
	}

	echo "Check success (solution is probably right)\n";

	// TODO - Königův teorém
}




