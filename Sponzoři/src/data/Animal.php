<?php

declare(strict_types=1);

namespace vixikhd\sponzori\data;

class Animal {
	public function __construct(
		private string $name
	) {}

	public function getName(): string {
		return $this->name;
	}
}
