<?php

namespace Safronik\DBMigrator\Objects;

use Safronik\DBMigrator\Exceptions\DBMigratorException;

class Table{

    private string $table_name;
    
    /** @var Column[] */
    private array $columns;
    
    /** @var Index[] */
    private array $indexes = [];
    
    /** @var Constraint[] */
    private array $constraints = [];
    
    /**
     * @param string     $table
     * @param Column[]      $columns
     * @param Index[]|null $indexes
     * @param Constraint[]|null $constraints
     *
     * @throws DBMigratorException
     */
    public function __construct( string $table, array $columns, array $indexes = [], array $constraints = [] )
    {
        $this->checkInputArray( $columns,     Column::class );
        $this->checkInputArray( $indexes,     Index::class );
        $this->checkInputArray( $constraints, Constraint::class );
        
        $this->table_name  = $table;
        
        // Crutch
        foreach( $columns     as $column     ){ $this->columns[     $column->getField()    ] = $column; }
        foreach( $indexes     as $index      ){ $this->indexes[     $index->getKeyName()   ] = $index; }
        foreach( $constraints as $constraint ){ $this->constraints[ $constraint->getName() ] = $constraint; }
    }
    
    private function checkInputArray( $input, $expected_type ): void
    {
        array_walk(
            $input,
            static fn( $value ) =>
                $value instanceof $expected_type
                || throw new DBMigratorException("Schema should get $expected_type as param")
        );
    }
    
    public function getTableName(): string
    {
        return $this->table_name;
    }
    
    public function getColumns(): array
    {
        return $this->columns;
    }
    
    public function getIndexes(): array
    {
        return $this->indexes;
    }
    
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}