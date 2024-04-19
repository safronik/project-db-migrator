<?php

namespace Safronik\DBMigrator\Analyzers;

use Safronik\DBMigrator\Objects\Constraint;

class ConstraintAnalyzer extends AbstractAnalyzer
{
    /** @var Constraint[] */
    private $current;
    
    /** @var Constraint[] */
    private $requested;
    
    public array $constraints_to_create;
    public array $constraints_to_delete;
    public array $constraints_to_change;
	
    public $changes_required;
    
    public function __construct( $current_constraints, $requested_constraints )
    {
        $this->checkInputArray( $current_constraints,   Constraint::class );
        $this->checkInputArray( $requested_constraints, Constraint::class );
        
        $this->current   = $current_constraints;
        $this->requested = $requested_constraints;
        
        $this->compare();
        
        $this->changes_required = $this->constraints_to_change || $this->constraints_to_create || $this->constraints_to_delete;
    }
    
    protected function compare(): void
    {
        $this->constraints_to_create = array_diff(
            array_keys($this->requested ),
            array_keys($this->current )
        );
		
        $this->constraints_to_delete = array_diff(
            array_keys( $this->current ),
            array_keys($this->requested )
        );
        
        foreach ( $this->requested as $requested_name => $requested ) {
            foreach ( $this->current as $current_name => $current ) {
                
                if(
                    $requested_name === $current_name &&
                    (string)$requested !== (string)$current
                ){
                    $this->constraints_to_change[] = $current_name;
                }
            }
        }
        
        $this->constraints_to_change = array_unique( $this->constraints_to_change ?? [] );
        
        
    }
}
