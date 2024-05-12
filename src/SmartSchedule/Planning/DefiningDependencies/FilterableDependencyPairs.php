<?php

namespace DomainDrivers\SmartSchedule\Planning\DefiningDependencies;

use Munus\Collection\Set;

class FilterableDependencyPairs
{
    /**
     * @var Set<DependencyPair>
     */
    private Set $pairs;

    /**
     * @param Set<DependencyPair> $pairs
     */
    public function __construct(Set $pairs)
    {
        $this->pairs = $pairs;
    }

    /**
     * @param Set<DependencyReason> $reasons
     * @return Set<DependencyPair>
     */
    public function filterByReasons(Set $reasons): Set
    {
        return $this->pairs->filter(function (DependencyPair $pair) use ($reasons) {
            return $reasons->exists(function (DependencyReason $reason) use ($pair) {
                return $pair->getReason() === $reason;
            });
        });
    }
}