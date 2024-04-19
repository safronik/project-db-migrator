<?php

namespace Safronik\DBMigrator\Objects;

use Safronik\DBMigrator\Exceptions\DBMigratorException;

class Constraint implements \Stringable{
    
    // Required
    private string $name;             // Constraint name
    private string $column;           // Column in the current table
    private string $reference_table;
    private string $reference_column;
    
    // Optional
    private string $on_update = 'NO ACTION';
    private string $on_delete = 'NO ACTION';
    
    public function __construct( $data )
    {
        $data = array_change_key_case( $data, CASE_LOWER );
        
        isset( $data['name'] )
            || throw new DBMigratorException('No constraint name given');
        
        isset( $data['column'] )
            || throw new DBMigratorException('No constraint column given');
        
        isset( $data['reference_table'] )
            || throw new DBMigratorException('No constraint reference table given');
        
        isset( $data['reference_column'] )
            || throw new DBMigratorException('No constraint reference column given');
        
        $this->name             = $data['name'];
        $this->column           = $data['column'];
        $this->reference_table  = $data['reference_table'];
        $this->reference_column = $data['reference_column'];
        $this->on_update        = $data['on_update'] ?? $this->on_update;
        $this->on_delete        = $data['on_delete'] ?? $this->on_delete;
    }
    
    public static function createBulkFromSQLResponse( $input ): array
    {
        $constraints = [];
        foreach( $input as $input_constraint ){
            
            $input_constraint = array_change_key_case( $input_constraint, CASE_LOWER );
            
            $constraints[ $input_constraint['constraint_name'] ]['name']             = $input_constraint['constraint_name'];
            $constraints[ $input_constraint['constraint_name'] ]['column']           = $input_constraint['column_name'];
            $constraints[ $input_constraint['constraint_name'] ]['reference_table']  = $input_constraint['referenced_table_name'];
            $constraints[ $input_constraint['constraint_name'] ]['reference_column'] = $input_constraint['referenced_column_name'];
            
            $constraints[ $input_constraint['constraint_name'] ]['on_update']        = $input_constraint['on_update'] ?? null;
            $constraints[ $input_constraint['constraint_name'] ]['on_delete']        = $input_constraint['on_delete'] ?? null;
        }
        
        foreach( $constraints as &$item ){
            $item = new self( $item );
        }
        
        return $constraints;
    }
    
    public function __toString(): string
    {
        return "CONSTRAINT `$this->name` FOREIGN KEY (`$this->column`) REFERENCES `$this->reference_table` (`$this->reference_column`) ON UPDATE $this->on_update ON DELETE $this->on_delete";
    }
    
    public function getName(): string
    {
        return $this->name;
    }
}