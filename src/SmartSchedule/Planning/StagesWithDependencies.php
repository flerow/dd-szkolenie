<?php

declare(strict_types=1);

namespace DomainDrivers\SmartSchedule\Planning;

use DomainDrivers\SmartSchedule\Planning\DefiningDependencies\DependencyPair;
use Munus\Collection\Set;

class StagesWithDependencies
{
    /**
     * @param Set<Stage>          $stages
     * @param Set<DependencyPair> $pairs
     *
     * @return Set<Stage>
     */
    public static function assignDependencies(Set $stages, Set $pairs): Set
    {
        return $stages->map(function (Stage $stage) use ($pairs) {
            $filteredPairs = $pairs->filter(function (DependencyPair $pair) use ($stage) {
                return $pair->getDependent() === $stage;
            });

            if ($filteredPairs->isEmpty()) {
                return $stage;
            }

            $newStage = $filteredPairs->map(function (DependencyPair $pair) use ($stage) {
                return $stage->dependsOn($pair->getDependency());
            })->reduce(function ($carry, $newStage) {
                return $newStage;
            });

            return $newStage;
        });
    }
}
