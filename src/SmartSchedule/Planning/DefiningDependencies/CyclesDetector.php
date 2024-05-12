<?php

namespace DomainDrivers\SmartSchedule\Planning\DefiningDependencies;

use Munus\Collection\Set;

class CyclesDetector
{
    public function detect(Set $dependencyPairs): Set
    {
        // Prepare the edges and edgeObjects arrays
        $edges = [];
        $edgeObjects = [];
        foreach ($dependencyPairs as $pair) {
            $dependent = spl_object_id($pair->getDependent());
            $dependency = spl_object_id($pair->getDependency());
            if (!isset($edges[$dependent])) {
                $edges[$dependent] = [];
            }
            $edges[$dependent][] = $dependency;
            $edgeObjects[$dependent][$dependency] = $pair;
        }

        // Run the Tarjan algorithm
        $tarjan = new TarjanCyclesAsEdgesLists();
        $cyclesAsEdges = $tarjan->tarjanSCCs($edges, $edgeObjects);

        // Convert the cycles from arrays to Sets
        $cycles = Set::empty();
        foreach ($cyclesAsEdges as $cycleAsEdges) {
            $cycle = Set::empty();
            foreach ($cycleAsEdges as $edge) {
                $cycle = $cycle->add($edge);
            }
            $cycles = $cycles->add($cycle);
        }
        return $cycles;
    }
}