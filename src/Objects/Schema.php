<?php

namespace Safronik\DBMigrator\Objects;

use Exception;
use Safronik\DBMigrator\Exceptions\DBMigratorException;
use Traversable;

class Schema implements \IteratorAggregate{
    
    /** @var Table[] */
    private array $tables;
    
    /**
     * @param Table[] $schema
     *
     * @throws Exception
     */
    public function __construct( array $schema = [] )
    {
        $this->checkInputArray( $schema, Table::class );
        $this->setTablesSchemas( $schema );
    }
    
    /**
     * @param array $input
     * @param $expected_type
     *
     * @return void
     * @throws DBMigratorException
     */
    private function checkInputArray( array $input, $expected_type ): void
    {
        array_walk(
            $input,
            static fn( $value ) => $value instanceof $expected_type
                || throw new DBMigratorException("Schema should receive $expected_type, " . $value::class . ' passed')
        );
    }
    
    /**
     * Set given schema
     * 
     * @param $tables_schemas
     *
     * @return void
     */
    private function setTablesSchemas( $tables_schemas ): void
    {
        foreach( $tables_schemas as $schema ){
            $this->tables[ $schema->getTableName() ] = $schema;
        }
    }
    
    public function isEmpty(): bool
    {
        return empty( $this->tables );
    }
    
    /**
     * @param string $table_name
     *
     * @return Table
     */
    public function getTableSchema( string $table_name ): Table
    {
        return $this->tables[ $table_name ];
    }

    public function getTableNames(): array
    {
        return array_keys( $this->tables );
    }
    
    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator( $this->tables );
    }
}