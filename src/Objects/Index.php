<?php

namespace Safronik\DBMigrator\Objects;

use Safronik\DBMigrator\Exceptions\DBMigratorException;

class Index implements \Stringable{
    
    // Required
    private string $key_name;          // Primary|Index
    private array  $columns;           // Sequence is important
    
    // Optional
    private bool   $unique  = false;   // Is index unique
    private string $type    = 'BTREE'; // Index technology BTREE|RTREE|HASH
    private string $comment = '';      // Column comment
    
    public function __construct( $data )
    {
        $data = array_change_key_case( $data, CASE_LOWER );
        
        isset( $data['key_name'] )
            || throw new DBMigratorException('No index name given');
        
        isset( $data['columns'] )
            || throw new DBMigratorException('No index columns given');
        
        $this->key_name = $data['key_name'];
        $this->columns  = $data['columns'];
        $this->unique   = $data['unique']  ?? $this->unique;
        $this->type     = $data['type']    ?? $this->type;
        $this->comment  = $data['comment'] ?? $this->comment;
    }
    
    /**
     * @param $input
     *
     * @return self[]
     * @throws DBMigratorException
     */
    public static function createBulkFromSQLResponse( $input ): array
    {
        $output = [];
        foreach( $input as $input_index ){
            
            $input_index = array_change_key_case( $input_index, CASE_LOWER );
            
            $output[ $input_index['key_name'] ]['key_name'] = $input_index['key_name'];
            $output[ $input_index['key_name'] ]['unique']   ??= ! $input_index['non_unique'];
            $output[ $input_index['key_name'] ]['type']     ??= $input_index['index_type'];
            $output[ $input_index['key_name'] ]['comment']  ??= $input_index['index_comment'];
            
            $output[ $input_index['key_name'] ]['columns'][ $input_index['seq_in_index'] ] = $input_index['column_name'];
        }
        
        foreach( $output as &$item ){
            $item = new self( $item );
        }
        
        return $output;
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        // Crutch because of SQL is gone crazy and forget about standards
        
        if( $this->key_name === 'PRIMARY' ){
            $name = 'PRIMARY KEY';
        }elseif( $this->unique === true ){
            $name = "UNIQUE INDEX `$this->key_name`";
        }else{
            $name = "INDEX `$this->key_name`";
        }
        
        $columns = '(`' . implode( '`,`', $this->columns ) . '`)';
        
        return "$name $columns USING $this->type COMMENT '$this->comment'";
    }
    
    public function getKeyName(): string
    {
        return $this->key_name;
    }
}