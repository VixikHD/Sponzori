<?php

declare(strict_types=1);

namespace vixikhd\sponzori\data;

class Sponsor {
	public function __construct(
		private string $name
	) {}

	public function getName(): string {
		return $this->name;
	}
}
