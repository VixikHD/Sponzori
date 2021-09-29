<?php

declare(strict_types=1);

namespace vixikhd\sponzori;

use function defined;
use const ENABLE_DEBUG;

trait Debug {
	public function debug(string $message): void {
		if(!defined("ENABLE_DEBUG")) {
			return;
		}
		if(!ENABLE_DEBUG) {
			return;
		}

		echo "[Debug] $message\n";
	}
}
