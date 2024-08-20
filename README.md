<h1 align="center">safronik/db-migrator</h1>
<p align="center">
    <strong>Check and change the existing SQL-schema and alters it to according requested schema</strong>
</p>

# About

This lib helps to migrate to the given schema from the current schema.

# Installation

The preferred method of installation is via Composer. Run the following
command to install the package and add it as a requirement to your project's
`composer.json`:

```bash
composer require safronik/db-migrator
```
or just download files or clone repository (in this case you should bother about autoloader)

# Usage

### Restrictions

You need to do few things before usage.

- Firstly, you should implement `DBMigratorGatewayInterface` to create the gateway.
```php
namespace Safronik\DBMigrator;

use Safronik\DBMigrator\Objects\Table;

interface DBMigratorGatewayInterface
{
    public function isTableExists( $table ): bool;
    public function createTable( Table $table ): bool;
    public function getTablesNames(): array;
    public function getTableColumns( string $table ): array;
    public function getTableIndexes( string $table ): array;
    public function getTableConstraints( string $table ): array;
    public function alterTable( $table, array $columns = [], array $indexes = [], array $constraints = [] ): bool;
    public function dropTable( $table ): bool;
}
```
Like this:
```php
class DBMigratorGatewayInterfaceImplementation implements \Safronik\DBMigrator\DBMigratorGatewayInterface
{
    // Your implementation of the interface
}
```

- Secondly, you should make schema provider or just provide object `Schemas` to migrator object. You can use `self::getCurrentSchemas()` to get current schemas.

```php
$migrator = new DBMigrator( $migrator_gateway );
$schemas  = $migrator->getCurrentSchemas();
```

Anyway, here is an example of the manual Schemas object creation:

```php
$schemas = new Schemas([
    new Table(
        'example_table',
        
        // Columns 
        [
            new Column([
                'field'   => 'id',      // Required
                'type'    => 'INT(11)', // Required
                'null'    => 'no',  
                'default' => ''   
                'extra'   => 'AUTO INCREMENT',
                'comment' => 'Primary key',
            ]),            
            new Column([
                'field'   => 'value_field', // Required
                'type'    => 'TEXT',        // Required
                'null'    => 'yes',  
                'default' => 'null',   
                'extra'   => '',
                'comment' => 'Desc',
            ]),            
            // ...
        ],
        
        // Indexes (optional)
        [
            new Index([
                'key_name' => 'PRIMARY', // Required
                'columns'  => ['id'],    // Required
                'unique'   => true,
                'type'     => 'BTREE',
                'comment'  => 'Primary key',
            ]),
            // ...    
        ],
        
        // Constraints (optional)
        [
            new Constraint([
                'name'             => 'Example constraint', // Required
                'column'           => 'id',                 // Required
                'reference_table'  => 'examples2',          // Required
                'reference_column' => 'example_id',         // Required
                'on_update'        => '',
                'on_delete'        => '',
            ]),
            // ...    
        ],
    )
]);
```

And finally after all that you can proceed:

```php
$migrator = new DBMigrator( 
    new DBMigratorGatewayInterfaceImplementation() 
);

$migrator
    ->setSchemas( $schemas )
    ->compareWithCurrentStructure()
    ->actualizeSchema();
```

Also, you can drop existing schema:

```php
$migrator = new DBMigrator( 
    new DBMigratorGatewayInterfaceImplementation() 
);

$migrator
    ->setSchemas( $migrator->getCurrentSchemas() )
    ->dropSchema();
```