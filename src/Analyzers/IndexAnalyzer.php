<?php

namespace Safronik\DBMigrator\Analyzers;

use Safronik\DBMigrator\Objects\Index;

class IndexAnalyzer extends AbstractAnalyzer
{
    /** @var Index[] */
    private $current_indexs;
    
    /** @var Index[] */
    private $requested_indexs;
    
    public array $indexes_to_create;
    public array $indexes_to_delete;
    public array $indexes_to_change;
	
    public $changes_required;
    
    public function __construct( $current_indexes, $requested_indexes )
    {
        $this->checkInputArray( $current_indexes,   Index::class );
        $this->checkInputArray( $requested_indexes, Index::class );
        
        $this->current_indexs   = $current_indexes;
        $this->requested_indexs = $requested_indexes;
        
        $this->compare();
        
        $this->changes_required = $this->indexes_to_change || $this->indexes_to_create || $this->indexes_to_delete;
    }
    
    protected function compare(): void
    {
        $this->indexes_to_create = array_diff(
            array_keys($this->requested_indexs ),
            array_keys($this->current_indexs )
        );
		
        $this->indexes_to_delete = array_diff(
            array_keys( $this->current_indexs ),
            array_keys($this->requested_indexs )
        );
        
        foreach ( $this->requested_indexs as $requested_index_name => $requested_index ) {
            foreach ( $this->current_indexs as $current_index_name => $current_index ) {
                
                if(
                    $requested_index_name === $current_index_name &&
                    (string)$requested_index !== (string)$current_index
                ){
                    $this->indexes_to_change[] = $current_index_name;
                }
            }
        }
        
        $this->indexes_to_change = array_unique($this->indexes_to_change ?? [] );
        
        
    }
}
