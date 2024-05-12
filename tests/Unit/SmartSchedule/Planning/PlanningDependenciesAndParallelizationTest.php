<?php

declare(strict_types=1);

namespace DomainDrivers\Tests\Unit\SmartSchedule\Planning;

use DomainDrivers\SmartSchedule\Planning\DefiningDependencies\DependencyPair;
use DomainDrivers\SmartSchedule\Planning\DefiningDependencies\DependencyReason;
use DomainDrivers\SmartSchedule\Planning\DefiningDependencies\FilterableDependencyPairs;
use DomainDrivers\SmartSchedule\Planning\Parallelization\StageParallelization;
use DomainDrivers\SmartSchedule\Planning\Stage;
use DomainDrivers\SmartSchedule\Planning\StagesWithDependencies;
use Munus\Collection\Set;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

// to nie jest dobry test jednostkowy, mimo to chcę sobie zobaczyć jak to działa
#[CoversNothing]
class PlanningDependenciesAndParallelizationTest extends TestCase
{
    public function testNoDependencies(): void
    {
        $stage1 = Stage::of('Stage1');
        $stage2 = Stage::of('Stage2');

        $stages = StagesWithDependencies::assignDependencies(
            Set::of($stage1, $stage2),
            Set::empty()
        );

        self::assertEquals('Stage1, Stage2', (new StageParallelization())->of($stages)->print());
    }

    public function testAllPossible(): void
    {
        $stage1 = Stage::of('Stage1');
        $stage2 = Stage::of('Stage2');
        $stage3 = Stage::of('Stage3');
        $stage4 = Stage::of('Stage4');

        $dependencyPair1 = (new DependencyPair())
            ->stage($stage2)
            ->dependsOn($stage1)
            ->withReason(DependencyReason::LEGAL);

        $dependencyPair2 = (new DependencyPair())
            ->stage($stage3)
            ->dependsOn($stage1)
            ->withReason(DependencyReason::FINANCIAL);

        $dependencyPair3 = (new DependencyPair())
            ->stage($stage4)
            ->dependsOn($stage2)
            ->withReason(DependencyReason::SHARED_RESOURCE);

        $stages = StagesWithDependencies::assignDependencies(
            Set::of($stage1, $stage2, $stage3, $stage4),
            Set::of($dependencyPair1, $dependencyPair2, $dependencyPair3)
        );

        $sortedStages = (new StageParallelization())->of($stages);

        self::assertEquals('Stage1 | Stage2, Stage3 | Stage4', $sortedStages->print());
    }

    public function testCyclic(): void
    {
        $stage1 = Stage::of('Stage1');
        $stage2 = Stage::of('Stage2');
        $stage3 = Stage::of('Stage3');
        $stage4 = Stage::of('Stage4');

        $dependencyPair1 = (new DependencyPair())
            ->stage($stage2)
            ->dependsOn($stage1)
            ->withReason(DependencyReason::LEGAL);

        $dependencyPair2 = (new DependencyPair())
            ->stage($stage3)
            ->dependsOn($stage1)
            ->withReason(DependencyReason::FINANCIAL);

        $dependencyPair3 = (new DependencyPair())
            ->stage($stage4)
            ->dependsOn($stage2)
            ->withReason(DependencyReason::SHARED_RESOURCE);

        $dependencyPair4 = (new DependencyPair())
            ->stage($stage1)
            ->dependsOn($stage4);

        $stages = StagesWithDependencies::assignDependencies(
            Set::of($stage1, $stage2, $stage3, $stage4),
            Set::of($dependencyPair1, $dependencyPair2, $dependencyPair3, $dependencyPair4),
        );

        $sortedStages = (new StageParallelization())->of($stages);

        self::assertEquals('', $sortedStages->print());
    }

    public function testCyclicButFixedByWithOnlyLogicalAndSharedResource(): void
    {
        $stage1 = Stage::of('Stage1');
        $stage2 = Stage::of('Stage2');
        $stage3 = Stage::of('Stage3');
        $stage4 = Stage::of('Stage4');

        $dependencyPair1 = (new DependencyPair())
            ->stage($stage2)
            ->dependsOn($stage1)
            ->withReason(DependencyReason::LEGAL);

        $dependencyPair2 = (new DependencyPair())
            ->stage($stage3)
            ->dependsOn($stage1)
            ->withReason(DependencyReason::FINANCIAL);

        $dependencyPair3 = (new DependencyPair())
            ->stage($stage4)
            ->dependsOn($stage2)
            ->withReason(DependencyReason::SHARED_RESOURCE);

        $dependencyPair4 = (new DependencyPair())
            ->stage($stage1)
            ->dependsOn($stage4);

        $pairs = (new FilterableDependencyPairs(Set::of($dependencyPair1, $dependencyPair2, $dependencyPair3, $dependencyPair4)))
            ->filterByReasons(Set::of(DependencyReason::LOGICAL, DependencyReason::SHARED_RESOURCE));

        $stages = StagesWithDependencies::assignDependencies(
            Set::of($stage1, $stage2, $stage3, $stage4),
            $pairs,
        );

        $sortedStages = (new StageParallelization())->of($stages);

        self::assertEquals('Stage1, Stage2, Stage3 | Stage4', $sortedStages->print());
    }
}
