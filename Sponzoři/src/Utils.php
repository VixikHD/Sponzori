<?php

declare(strict_types=1);

namespace vixikhd\sponzori;

use function array_map;

class Utils {
	/**
	 * @return int[]
	 */
	public static function toIntArray(array $array): array {
		return array_map(fn(int|string|float $value) => (int)$value, $array);
	}

	public static function pairHash(int $sponsorId, int $animalId): int {
		return $sponsorId << 8 | $animalId;
	}

	public static function getSponsorIdFromHash(int $hash): int {
		return $hash >> 8;
	}

	public static function getAnimalIdFromHash(int $hash): int {
		return ($hash & 0xff) << 8 >> 8;
	}
}
