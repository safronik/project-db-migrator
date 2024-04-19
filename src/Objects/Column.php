<?php

namespace Safronik\DBMigrator\Objects;

use Safronik\DBMigrator\Exceptions\DBMigratorException;

class Column implements \Stringable{
    
    // Required
    private string $field; // Column name
    private string $type;  // Column type
    
    // Optional
    private string $null    = 'yes'; // Is null allowed
    private string $default = '';    // Default value
    private string $extra   = '';    // AUTO_INCREMENT | ...
    private string $comment = '';    // Column comment
    
    public function __construct( $data )
    {
        $data = array_change_key_case( $data, CASE_LOWER );
        
        isset( $data['field'] )
            || throw new DBMigratorException('No field name given');
        
        isset( $data['type'] )
            || throw new DBMigratorException('No field type given');
        
        $this->field   = $data['field'];
        $this->type    = $data['type'];
        $this->null    = $data['null']    ?? $this->null;
        $this->default = $data['default'] ?? $this->default;
        $this->extra   = $data['extra']   ?? $this->extra;
        $this->comment = $data['comment'] ?? $this->comment;
        
        $this->field   = strtolower( $this->field );
        $this->type    = strtolower( $this->type );
        $this->null    = strtolower( $this->null );
        $this->default = strtolower( $this->default );
        $this->extra   = strtolower( $this->extra );
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        $default = $this->default ? "DEFAULT '$this->default'" : '';
        
        $null    = $this->null === 'yes' ? 'NULL' : 'NOT NULL';
        $default = ! $default && $null === 'NULL' ? "DEFAULT NULL" : '';
        
        return "`$this->field` $this->type $null $default $this->extra $this->comment";
    }
    
    public function getField(): string
    {
        return $this->field;
    }
}