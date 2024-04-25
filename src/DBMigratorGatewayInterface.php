<?php

namespace Safronik\DBMigrator;

use Safronik\DBMigrator\Objects\Table;

interface DBMigratorGatewayInterface
{
    // Is
    public function isTableExists( $table ): bool;
    
    // Create
    public function createTable( Table $table ): bool;
    
    // Read
    public function getTablesNames(): array;
    public function getTableColumns( string $table ): array;
    public function getTableIndexes( string $table ): array;
    public function getTableConstraints( string $table ): array;
    
    // Update
    public function alterTable( $table, array $columns = [], array $indexes = [], array $constraints = [] ): bool;
    
    // Delete
    public function dropTable( $table ): bool;
}