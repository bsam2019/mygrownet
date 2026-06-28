<?php

namespace App\Domain\Ubumi\Services;

use App\Domain\Ubumi\Entities\Person;
use App\Domain\Ubumi\ValueObjects\PersonName;

/**
 * Duplicate Detection Service
 * 
 * Detects potential duplicate person records using similarity algorithms
 */
class DuplicateDetectionService
{
    /**
     * Calculate similarity score between two persons
     * 
     * @return float Score between 0 and 1
     */
    public function calculateSimilarity(Person $person1, Person $person2): float
    {
        $score = 0.0;

        // Name similarity (40% weight)
        $nameScore = $this->calculateNameSimilarity(
            $person1->getName(),
            $person2->getName()
        );
        $score += $nameScore * 0.4;

        // Age similarity (30% weight)
        $age1 = $person1->getAge();
        $age2 = $person2->getAge();
        
        if ($age1 !== null && $age2 !== null) {
            $ageDiff = abs($age1 - $age2);
            $ageScore = max(0, 1 - ($ageDiff / 10));
            $score += $ageScore * 0.3;
        }

        // Gender match (10% weight)
        if ($person1->getGender() && $person2->getGender()) {
            if ($person1->getGender() === $person2->getGender()) {
                $score += 0.1;
            }
        }

        // Photo similarity (20% weight) - placeholder for future implementation
        // This would use image comparison algorithms
        if ($person1->getPhotoUrl() && $person2->getPhotoUrl()) {
            // TODO: Implement photo similarity
            $score += 0.0;
        }

        return $score;
    }

    /**
     * Calculate name similarity using Levenshtein distance and phonetic matching
     */
    private function calculateNameSimilarity(PersonName $name1, PersonName $name2): float
    {
        $str1 = $this->normalizeName($name1->toString());
        $str2 = $this->normalizeName($name2->toString());

        // Levenshtein distance
        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) {
            return 1.0;
        }

        $distance = levenshtein($str1, $str2);
        $levScore = 1 - ($distance / $maxLen);

        // Phonetic matching (Soundex)
        $soundex1 = soundex($str1);
        $soundex2 = soundex($str2);
        $phoneticScore = ($soundex1 === $soundex2) ? 1.0 : 0.0;

        // Combined score (60% Levenshtein, 40% phonetic)
        return ($levScore * 0.6) + ($phoneticScore * 0.4);
    }

    /**
     * Normalize name for comparison
     */
    private function normalizeName(string $name): string
    {
        // Convert to lowercase
        $name = strtolower($name);
        
        // Remove extra whitespace
        $name = preg_replace('/\s+/', ' ', $name);
        
        // Trim
        $name = trim($name);
        
        return $name;
    }

    /**
     * Check if similarity score indicates potential duplicate
     */
    public function isPotentialDuplicate(float $similarityScore): bool
    {
        return $similarityScore >= 0.6;
    }

    /**
     * Get confidence level description
     */
    public function getConfidenceLevel(float $similarityScore): string
    {
        if ($similarityScore >= 0.8) {
            return 'very_likely';
        } elseif ($similarityScore >= 0.6) {
            return 'possibly';
        } else {
            return 'unlikely';
        }
    }
}
