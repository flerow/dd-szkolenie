<?php

declare(strict_types=1);

namespace DomainDrivers\SmartSchedule\Planning\Parallelization;

use DomainDrivers\SmartSchedule\Planning\Stage;
use Munus\Collection\Set;

final class StageParallelization
{
    /**
     * @var Set<string>
     */
    private Set $visited;

    /**
     * @var Set<Stage>
     */
    private Set $sortedStages;

    /**
     * @var Set<string>
     */
    private Set $processing;

    /**
     * @param Set<Stage> $stages
     */
    public function of(Set $stages): ParallelStagesList
    {
        $this->visited = Set::empty();
        $this->processing = Set::empty();
        $this->sortedStages = Set::empty();

        foreach ($stages as $stage) {
            if (!$this->visited->contains($stage->name())) {
                $hasCycle = $this->topologicalSort($stage);
                if ($hasCycle) {
                    // Cycle detected, return empty list
                    return ParallelStagesList::empty();
                }
            }
        }

        /** @var Set<Stage> $currentGroup */
        $currentGroup = Set::empty();
        $parallelStagesList = ParallelStagesList::empty();

        foreach ($this->sortedStages as $stage) {
            /** @var Stage $stage */
            /** @var Set<Stage> $currentGroup */
            if (!$currentGroup->isEmpty() && $this->isDependentOn($stage, $currentGroup)) {
                /** @var Set<Stage> $currentGroup */
                $parallelStagesList = $parallelStagesList->add(new ParallelStages($currentGroup));
                $currentGroup = Set::empty();
            }

            $currentGroup = $currentGroup->add($stage);
        }

        if (!$currentGroup->isEmpty()) {
            /** @var Set<Stage> $currentGroup */
            $parallelStagesList = $parallelStagesList->add(new ParallelStages($currentGroup));
        }

        return $parallelStagesList;
    }

    /**
     * @param Set<Stage> $group
     */
    private function isDependentOn(Stage $stage, Set $group): bool
    {
        foreach ($group as $groupStage) {
            if ($stage->dependencies()->contains($groupStage)) {
                return true;
            }
        }

        return false;
    }

    private function topologicalSort(Stage $stage): bool
    {
        $this->visited = $this->visited->add($stage->name());
        $this->processing = $this->processing->add($stage->name());

        foreach ($stage->dependencies() as $dep) {
            if ($this->processing->contains($dep->name())) {
                // Cycle detected
                return true;
            }

            if (!$this->visited->contains($dep->name())) {
                $hasCycle = $this->topologicalSort($dep);
                if ($hasCycle) {
                    // Cycle detected
                    return true;
                }
            }
        }

        $this->sortedStages = $this->sortedStages->add($stage);
        $this->processing = $this->processing->remove($stage->name());

        return false;
    }
}
