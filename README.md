DBLite
------

# Why

Small serverless databases can be extremely useful in micro frameworks. SQLite (3) provides an excellent storage engine for these databases, and PDO offers a painless way to query and work with SQL result sets. DBLite simply combines the two in a easy to use wrapper for quick configuration. 

# Usage

### Installation

To add DBLite to your project, just use composer:

    composer require journeygroup/dblite @dev-master


### Configuration

DBLite only requires 1 configuration option (its not called *lite* for nothing). These are all the possible configuration options:

```php
$config = [
    'storage' => 'path/to/storage/dir',  # Required storage path
    'name' => 'your-database.db',        # (optional) database name
    'tables' => [                        # (optional) tables to create
        'table_one' => 'CREATE TABLE table_one ...' # (optional) CREATE TABLE sql statement
    ]
];
```

To use them, simply instantiate the database class:

```php
$db = new Journey\DBLite($config);
```

*or*

```php
// Configure once for your application
Journey\DBLite::config($config);

// Access methods statically
Journey\DBLite::query('SELECT * FROM mytable');
```

**When DBLite is instantiated, it will check for a the presence a database, if it doesn't exist, it will automatically create the database and add the tables in the configuration file.**

### Querying

Any call to a [PDO method](http://php.net/manual/en/book.pdo.php) is valid, and methods can be called statically or on an instance of DBLite. When called statically, only configuration options of the _first instantiated_ database will be used.

```php

use Journey\DBLite;

# Example configuration
DBLite::config([
    'storage' => './storage',
    'tables' => parse_ini_file('./tables.ini')
]);

# Example Prepared Insert
DBLite::prepare('INSERT INTO mytable (first, last) VALUES(?, ?)')
    ->execute(['Journey', 'Group']);

# Example Query Statement
foreach (DBLite::query('SELECT * FROM mytable') as $row) {
    var_dump($row);
}
```
