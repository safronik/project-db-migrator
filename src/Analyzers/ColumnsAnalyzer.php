<?php

namespace Safronik\DBMigrator\Analyzers;

use Safronik\DBMigrator\Objects\Column;

class ColumnsAnalyzer extends AbstractAnalyzer
{
    /** @var Column[] */
    private $current_columns;
    
    /** @var Column[] */
    private $requested_columns;
    
    public array $columns_to_create = [];
    public array $columns_to_delete = [];
    public array $columns_to_change = [];
	
    public $changes_required;
    
    public function __construct( $current_columns, $requested_columns )
    {
        $this->checkInputArray( $current_columns,   Column::class );
        $this->checkInputArray( $requested_columns, Column::class );
        
        $this->current_columns   = $current_columns;
        $this->requested_columns = $requested_columns;
        
        $this->compare();
        
        $this->changes_required = $this->columns_to_change || $this->columns_to_create || $this->columns_to_delete;
    }
    
    /**
     * Create columns and drop excess columns
     */
    protected function compare(): void
    {
        
        $columns_names_to_create = array_diff(
            array_keys($this->requested_columns ),
            array_keys($this->current_columns )
        );
        foreach( $this->requested_columns as $column_name => $column ){
            in_array( $column_name, $columns_names_to_create, true )
                && $this->columns_to_create[] = $column;
        }
        
        $column_names_to_delete = array_diff(
            array_keys( $this->current_columns ),
            array_keys($this->requested_columns )
        );
        foreach( $this->current_columns as $column_name => $column ){
            in_array( $column_name, $column_names_to_delete, true )
                && $this->columns_to_delete[] = $column;
        }
        
        foreach ( $this->requested_columns as $requested_column_name => $requested_column ) {
            foreach ( $this->current_columns as $current_column_name => $current_column ) {
                
                if(
                    $requested_column_name === $current_column_name &&
                    (string)$requested_column !== (string)$current_column
                ){
                    $this->columns_to_change[] = $this->requested_columns[ $current_column_name ];
                }
            }
        }
        
        $this->columns_to_change = array_unique($this->columns_to_change ?? [] );
    }
}
