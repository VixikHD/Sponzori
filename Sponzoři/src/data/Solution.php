<?php

declare(strict_types=1);

namespace vixikhd\sponzori\data;

class Solution {
	/** @var int[] */
	private array $pairs = [];

	public function addPair(int $pairHash): void {
		$this->pairs[] = $pairHash;
	}

	/**
	 * @return int[]
	 */
	public function getPairs(): array {
		return $this->pairs;
	}
}
