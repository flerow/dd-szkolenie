<?php

namespace DomainDrivers\SmartSchedule\Planning\DefiningDependencies;

use Munus\Collection\Set;

//should be more sophisticated, reasons priorities to be defined in configuration or in another place as a rule engine
//idk if works when in multiple cycles there are same dependencies and removing it from one cycle will cause another cycle to be removed
//when same priority should promote removing dependencies appearing in more cycles
//should write tests to ensure it works
//surely this is not a decider, should change the name
class DependenciesToOmitDecider
{
    private $reasonPriority = [
        DependencyReason::FINANCIAL->name => 1,
        DependencyReason::SHARED_RESOURCE->name => 2,
        DependencyReason::LEGAL->name => 3,
        DependencyReason::LOGICAL->name => 4,
        DependencyReason::ARBITRARY->name => 5,
    ];

    public function decide(Set $cycles): Set
    {
        $toRemove = Set::empty();

        foreach ($cycles as $cycle) {
            // Convert the Set to an array
            $cycleArray = $cycle->iterator()->toArray();

            usort($cycleArray, function (DependencyPair $a, DependencyPair $b) {
                return $this->reasonPriority[$a->getReason()->name] <=> $this->reasonPriority[$b->getReason()->name];
            });

            // Convert the array back to a Set
            $sortedCycle = Set::ofAll($cycleArray);

            foreach ($sortedCycle as $dependencyPair) {
                if (!$toRemove->contains($dependencyPair)) {
                    $toRemove = $toRemove->add($dependencyPair);
                    break;
                }
            }
        }

        return $toRemove;
    }
}