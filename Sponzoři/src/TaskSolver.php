<?php

declare(strict_types=1);

namespace vixikhd\sponzori;

use vixikhd\sponzori\data\Animal;
use vixikhd\sponzori\data\Pair;
use vixikhd\sponzori\data\Solution;
use vixikhd\sponzori\data\Sponsor;
use function array_filter;
use function array_key_first;
use function array_map;
use function asort;
use function count;
use function current;

class TaskSolver {
	use Debug;

	private Solution $solution;

	public function __construct(
		/** @var array<int, Animal> */
		private array $animals = [],
		/** @var array<int, Sponsor> */
		private array $sponsors = [],
		/** @var Pair[] */
		private array $pairs = []
	) {}

	public function run(): void {
		$this->solution = new Solution();

		$recalculateSponsorData = function(?array &$animalsBySponsors, ?array &$sponsorsSortedByAnimalCount): void {
			/** @var array<int, int> $animalsBySponsors */
			$animalsBySponsors = [];
			foreach ($this->pairs as $pair) {
				$animalsBySponsors[$pair->getSponsorId()][] = $pair->getAnimalId();
			}

			/** @var array<int, int> $sponsorsSortedByAnimalCount */
			$sponsorsSortedByAnimalCount = array_map(fn(array $array) => count($array), $animalsBySponsors);
			asort($sponsorsSortedByAnimalCount);
		};

		$recalculateAnimalData = function(?array &$sponsorsByAnimals, ?array &$animalsSortedBySponsorCount): void {
			/** @var array<int, int> $sponsorsByAnimals */
			$sponsorsByAnimals = [];
			foreach ($this->pairs as $pair) {
				$sponsorsByAnimals[$pair->getAnimalId()][] = $pair->getSponsorId();
			}

			/** @var array<int, int> $animalsSortedBySponsorCount */
			$animalsSortedBySponsorCount = array_map(fn(array $array) => count($array), $sponsorsByAnimals);
			asort($animalsSortedBySponsorCount);
		};

		do {
			$recalculateSponsorData($animalsBySponsors, $sponsorsSortedByAnimalCount);

			/** @var int $nextSponsor */
			$nextSponsor = array_key_first($sponsorsSortedByAnimalCount);
			if($sponsorsSortedByAnimalCount[$nextSponsor] == 1) {
				$this->solution->addPair(Utils::pairHash(
					sponsorId: $nextSponsor,
					animalId: $animalId = current($animalsBySponsors[$nextSponsor])
				));

				$this->pairs = array_filter($this->pairs, function(Pair $pair) use ($nextSponsor, $animalId): bool {
					if($pair->getAnimalId() == $animalId) {
						return false;
					}
					if($pair->getSponsorId() == $nextSponsor)  {
						return false;
					}

					return true;
				});
				$this->debug("Found a new pair using method #1 (Sponsor=" . $this->sponsors[$nextSponsor]->getName() . ";Animal=" . $this->animals[$animalId]->getName() . ")");
				continue;
			}

			$recalculateAnimalData($sponsorsByAnimals, $animalsSortedBySponsorCount);

			/** @var int $nextAnimal */
			$nextAnimal = array_key_first($animalsSortedBySponsorCount);
			if($animalsSortedBySponsorCount[$nextAnimal] == 1) {
				$this->solution->addPair(Utils::pairHash(
					sponsorId: $sponsorId = current($sponsorsByAnimals[$nextAnimal]),
					animalId: $nextAnimal
				));

				$this->pairs = array_filter($this->pairs, fn(Pair $pair) => $pair->getSponsorId() != $sponsorId && $pair->getAnimalId() != $nextAnimal);
				$this->debug("Found a new pair using method #2 (Sponsor=" . $this->sponsors[$sponsorId]->getName() . ";Animal=" . $this->animals[$nextAnimal]->getName() . ")");
				continue;
			}

			// Každé zvíře má alespoň 2 potenciální sponzory, stejně tak každý sponzor si může vybrat 2 zvířata
			// Tudíž můžeme zvolit jakoukoliv dvojci, tak, abychom mohli dál pokračovat
			$this->solution->addPair(
				Utils::pairHash(
					sponsorId: $nextSponsor,
					animalId: $nextAnimal
			));

			$this->pairs = array_filter($this->pairs, fn(Pair $pair) => $pair->getSponsorId() != $nextSponsor && $pair->getAnimalId() != $nextAnimal);
			$this->debug("Found a new pair using method #3 (Sponsor=" . $this->sponsors[$nextSponsor]->getName() . ";Animal=" . $this->animals[$nextAnimal]->getName() . ")");
		} while(count($this->pairs) != 0 && count($this->solution->getPairs()) != count($this->animals));
	}

	/**
	 * @return Animal[]
	 */
	public function getAnimals(): array {
		return $this->animals;
	}

	/**
	 * @return Sponsor[]
	 */
	public function getSponsors(): array {
		return $this->sponsors;
	}

	public function getSolution(): Solution {
		return $this->solution;
	}
}
