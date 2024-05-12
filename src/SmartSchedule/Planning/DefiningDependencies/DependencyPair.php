<?php

namespace DomainDrivers\SmartSchedule\Planning\DefiningDependencies;

use DomainDrivers\SmartSchedule\Planning\Stage;

//powinno być niemutowalne, statyczna metoda wytwórcza itp, nie chce mi sie
class DependencyPair
{
    private Stage $dependent;
    private Stage $dependency;
    private DependencyReason $reason;

    public function __construct()
    {
        $this->reason = DependencyReason::ARBITRARY; // default reason
    }

    public function stage(Stage $from): self
    {
        $this->dependent = $from;

        return $this;
    }

    public function dependsOn(Stage $to): self
    {
        $this->dependency = $to;

        return $this;
    }

    public function withReason(DependencyReason $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getDependent(): Stage
    {
        return $this->dependent;
    }

    public function getDependency(): Stage
    {
        return $this->dependency;
    }

    public function getReason(): DependencyReason
    {
        return $this->reason;
    }
}