<?php

declare(strict_types=1);

namespace DomainDrivers\SmartSchedule\Planning\DefiningDependencies;

enum DependencyReason: string
{
    case FINANCIAL = 'FINANCIAL';
    case LOGICAL = 'LOGICAL';
    case ARBITRARY = 'ARBITRARY';
    case LEGAL = 'LEGAL';
    case SHARED_RESOURCE = 'SHARED_RESOURCE';

    // ... could be somewhere in configuration/DB
    // ... maybe it should be a class with more properties, e.g. description
}
