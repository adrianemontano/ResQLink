<?php

namespace App\Services;

class IncidentSeverityService
{
    public function classify(int $affectedPopulation, float $impactRadius): string
    {
        $score = $this->populationScore($affectedPopulation)
            + $this->radiusScore($impactRadius);

        return match (true) {
            $score >= 6 => 'Critical',
            $score >= 4 => 'High',
            $score >= 2 => 'Moderate',
            default => 'Low',
        };
    }

    private function populationScore(int $population): int
    {
        return match (true) {
            $population >= 100 => 4,
            $population >= 50 => 3,
            $population >= 10 => 2,
            default => 0,
        };
    }

    private function radiusScore(float $radius): int
    {
        return match (true) {
            $radius >= 1000 => 4,
            $radius >= 500 => 3,
            $radius >= 100 => 2,
            default => 0,
        };
    }
}
