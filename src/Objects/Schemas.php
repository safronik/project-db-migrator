<?php

namespace Safronik\DBMigrator\Objects;

use Exception;
use Safronik\DBMigrator\Exceptions\DBMigratorException;
use Traversable;

class Schemas implements \IteratorAggregate{
    
    /** @var Table[] */
    private array $schemas;
    
    /**
     * @param Table[] $schemas
     *
     * @throws Exception
     */
    public function __construct( array $schemas )
    {
        $this->checkInputArray( $schemas, Table::class );
        
        foreach( $schemas as $schema ){ $this->schemas[ $schema->getTableName() ] = $schema; }
    }
    
    /**
     * @param $input
     * @param $expected_type
     *
     * @return void
     * @throws DBMigratorException
     */
    private function checkInputArray( $input, $expected_type ): void
    {
        array_walk(
            $input,
            static fn( $value ) => $value instanceof $expected_type || throw new DBMigratorException("Schema should get $expected_type as param param")
        );
    }
    
    public function getTableSchema( $table_name ): Table
    {
        return $this->schemas[ $table_name ];
    }
    
    public function getTableNames(): array
    {
        return array_keys( $this->schemas );
    }
    
    /**
     * @return Traversable|array
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator( $this->schemas );
    }
}