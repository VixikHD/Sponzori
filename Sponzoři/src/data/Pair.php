<?php

declare(strict_types=1);

namespace vixikhd\sponzori\data;

class Pair {
	public function __construct(
		private int $sponsorId,
		private int $animalId
	) {}

	public function getSponsorId(): int {
		return $this->sponsorId;
	}

	public function getAnimalId(): int {
		return $this->animalId;
	}
}
