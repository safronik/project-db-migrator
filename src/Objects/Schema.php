<?php

namespace Safronik\DBMigrator\Objects;

use Exception;
use Safronik\DBMigrator\Exceptions\DBMigratorException;
use Traversable;

class Schema implements \IteratorAggregate{
    
    /** @var Table[] */
    private array $schemas;
    
    /**
     * @param Table[] $schema
     *
     * @throws Exception
     */
    public function __construct( array $schema = [] )
    {
        $this->checkInputArray( $schema, Table::class );
        $this->setSchema( $schema );
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
    
    /**
     * Set given schema
     *
     * @param $schemas
     *
     * @return void
     */
    private function setSchema( $schemas ): void
    {
        foreach( $schemas as $schema ){
            $this->schemas[ $schema->getTableName() ] = $schema;
        }
    }
    
    /**
     * @param string $table_name
     *
     * @return Table
     */
    public function getTableSchema( $table_name ): Table
    {
        return $this->schemas[ $table_name ];
    }
    
    /**
     * @return string[]
     */
    public function getTableNames(): array
    {
        return array_keys( $this->schemas );
    }
    
    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator( $this->schemas );
    }
}