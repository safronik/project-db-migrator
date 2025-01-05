<?php

namespace Safronik\DBMigrator;

use Exception;
use Safronik\DBMigrator\Analyzers\ColumnsAnalyzer;
use Safronik\DBMigrator\Analyzers\ConstraintAnalyzer;
use Safronik\DBMigrator\Analyzers\IndexAnalyzer;
use Safronik\DBMigrator\Exceptions\DBMigratorException;
use Safronik\DBMigrator\Objects\Column;
use Safronik\DBMigrator\Objects\Constraint;
use Safronik\DBMigrator\Objects\Index;
use Safronik\DBMigrator\Objects\Schema;
use Safronik\DBMigrator\Objects\Table;

/**
 * Database Migrator
 * 
 * Allows to create|update database structure by given schema
 * The schema should be provided by side provider
 *
 * @author  Roman safronov
 * @version 1.0.0
 */
class DBMigrator
{
	private DBMigratorGatewayInterface $gateway;
	private Schema $schema;
    
    private array $not_existing_tables;
    private array $existing_tables;
    private array $tables_to_update;
    
    private bool $is_analyzed = false;
    
    public function __construct( DBMigratorGatewayInterface $gateway )
	{
        $this->gateway      = $gateway;
	}
    
    public function setSchema( Schema $schema ): static
    {
        $this->schema      = $schema;
        $this->is_analyzed = false;
        
        return $this;
    }
    
    /**
     * Get schemas for all database tables
     *
     * @return Schema
     * @throws Exception
     */
    public function getCurrentSchemas(): Schema
    {
        $tables_data = [];
        
        foreach( $this->gateway->getTablesNames() as $table_name ){
            $tables_data[] = $this->getCurrentTableSchema( $table_name );
        }
        
        return new Schema( $tables_data );
    }
    
    public function compareWithCurrentStructure(): static
    {
        $this->schema
            || throw new DBMigratorException('No schema defined to analyze. Use self::setSchema() to define it.');
        
        if( $this->is_analyzed ){
            return $this;
        }
        
        $this->checkTablesForExistence();
        
        $this->tables_to_update = [];
        foreach( $this->existing_tables as $existing_table ){
            
            $current_schema = $this->getCurrentTableSchema( $existing_table );
            
            $columns_analyzer = new ColumnsAnalyzer(
                $current_schema->getColumns(),
                $this->schema->getTableSchema( $existing_table )->getColumns()
            );
            
            $indexes_analyzer = new IndexAnalyzer(
                $current_schema->getIndexes(),
                $this->schema->getTableSchema( $existing_table )->getIndexes()
            );
            
            $constraints_analyzer = new ConstraintAnalyzer(
                $current_schema->getConstraints(),
                $this->schema->getTableSchema( $existing_table )->getConstraints()
            );
            
            if( $columns_analyzer->changes_required ){
                
                $this->tables_to_update[ $existing_table ] = [
                    'columns' => [
                        'create' => $columns_analyzer->columns_to_create,
                        'update' => $columns_analyzer->columns_to_change,
                        'delete' => $columns_analyzer->columns_to_delete,
                    ],
                    'indexes' => [
                        'create' => $indexes_analyzer->indexes_to_create,
                        'update' => $indexes_analyzer->indexes_to_change,
                        'delete' => $indexes_analyzer->indexes_to_delete,
                    ],
                    'constraints' => [
                        'create' => $constraints_analyzer->constraints_to_create,
                        'update' => $constraints_analyzer->constraints_to_change,
                        'delete' => $constraints_analyzer->constraints_to_delete,
                    ],
                ];
            }
        }
        
        $this->is_analyzed = true;
        
        return $this;
    }

    /**
     * @throws DBMigratorException
     */
    public function actualizeSchema(): void
    {
        $this->schema
            || throw new DBMigratorException('No schema defined to analyze. Use DBMigrator::setSchema() to define it.');
        
        $this->compareWithCurrentStructure();
        
        $this->createTables( $this->not_existing_tables );
        $this->updateTables( $this->tables_to_update );
	}

    /**
     * @throws DBMigratorException
     */
    public function dropSchema(): bool
    {
        $this->schema
            || throw new DBMigratorException( 'No schema defined to analyze. Use DBMigrator::setSchema() to define it.' );

        $out = true;

        $this->existing_tables = $this->schema->getTableNames();
        usort(
            $this->existing_tables,
            static fn( $a, $b ) => strlen( $b ) - strlen( $a )
        );

        foreach( $this->existing_tables as $scheme_table_name ){
            $out = $out && $this->gateway->dropTable( $scheme_table_name );
        }

        return $out;
    }
    
    private function checkTablesForExistence(): void
    {
        foreach ( $this->schema->getTableNames() as $table_name ){
			
	        $production_table_name = $table_name;
            
            $this->gateway->isTableExists( $production_table_name )
                && $this->existing_tables[] = $table_name;

            ! $this->gateway->isTableExists( $production_table_name )
                && $this->not_existing_tables[] = $table_name;
        }

        $this->existing_tables     = array_unique($this->existing_tables ?? [] );
        $this->not_existing_tables = array_unique($this->not_existing_tables ?? [] );
    }
    
    /**
     * Iteratively creates tables
     */
    private function createTables( array $tables_to_create ): bool
    {
        // Filter out created tables one by one
        return (bool) array_filter(
            $tables_to_create,
            fn( $table_to_create ) =>
                $this->gateway->createTable(
                    $this->schema->getTableSchema( $table_to_create )
                )
        );
    }
    
    /**
     * Iteratively updates tables
     */
    private function updateTables( $tables_to_update ): bool
    {
        // Filter out updated tables one by one
        return (bool) array_filter(
            $tables_to_update,
            fn( $table_data, $table_name ) =>
                $this->gateway->alterTable(
                    $table_name,
                    $table_data['columns'],
                    $table_data['indexes'],
                    $table_data['constraints'],
                ),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @throws DBMigratorException
     */
    private function getCurrentTableSchema( mixed $table_name ): Table
    {
        return new Table(
            $table_name,
            array_map(
                static fn( $data ) => new Column( $data ),
                $this->gateway->getTableColumns( $table_name )
            ),
            Index::createBulkFromSQLResponse(
                $this->gateway->getTableIndexes( $table_name )
            ),
            Constraint::createBulkFromSQLResponse(
                $this->gateway->getTableConstraints( $table_name )
            ),
        );
    }
    
    public function getExistingTables(): array
    {
        return $this->existing_tables;
    }

    public function getNotExistingTables(): array
    {
        return $this->not_existing_tables;
    }
    
    public function getTablesToUpdate(): array
    {
        return $this->tables_to_update;
    }
    
    public function getTablesNames(): array
    {
        return $this->gateway->getTablesNames();
    }
}