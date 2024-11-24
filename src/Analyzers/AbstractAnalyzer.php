<?php

namespace Safronik\DBMigrator\Analyzers;

use Safronik\DBMigrator\Exceptions\DBMigratorException;

abstract class AbstractAnalyzer{
    
    protected function checkInputArray( $input, $expected_type ): void
    {
        array_walk(
            $input,
            static fn( $value ) => $value instanceof $expected_type
                || throw new DBMigratorException("Schema should get $expected_type as param")
        );
    }

    abstract protected function compare(): void;
    
}