<?php

namespace Journey;

use PDO;
use Exception;
use SQLite3;

class DBLite
{
    private $db;

    private $config;


    /**
     * Construct our database object, and if necessary the database itself
     * @param array $config [description]
     */
    public function __construct($config = array())
    {
        // Load our configuration statically
        $this->config = static::config($config);


        // Check our storage paths
        if (!$this->config['storage'] || !is_dir($this->config['storage'])) {
            throw new DBLiteException('Cannot instantiate Journey\DBLite because the configuration does not have a storage path');
        }


        try {
            $location = $this->config['storage'] . DIRECTORY_SEPARATOR . $this->config['name'];
            
            // If we already have a database
            if (file_exists($location)) {
                if (!is_writable($location)) {
                    throw new DBLiteException('Unable to instantiate Journey\DBLite because the database file is not writable.');
                } else {
                    $this->db = new PDO('sqlite:' . $location);
                }


            // If we don't have a database, create one
            } else {
                if (is_writable(dirname($location))) {
                    $handle = new SQLite3($location);
                    $this->db = new PDO('sqlite:' . $location);
                    $this->install();
                } else {
                    throw new DBLiteException('Unable to create a database because the storage directory is not writable');
                }
            }

        // Catch any instantiation errors
        } catch (Exception $e) {
            throw new DBLiteException('Unable to connect to database: ' . $e->getMessage());
        }
    }



    /**
     * Create the database tables
     * @return none
     */
    public function install()
    {
        foreach ($this->config['tables'] as $table => $query) {
            try {
                $this->db->query($query);
            } catch (Exception $e) {
                throw new DBLiteException('Unable to create table: ' . $table . "; error: " . $e->getMessage());
            }
        }
    }



    /**
     * Statically configures the application
     * @return Array   array of configuration options
     */
    public static function config($options = array())
    {
        static $config;

        if (!$config) {
            $config = [
                'storage' => null,
                'name' => 'database.db',
                'tables' => array()
            ];
        }

        return array_replace_recursive($config, $options);
    }



    /**
     * Instance production factory
     * @param Array  $config  Configuration options to use for a new instance
     * @return DBLite         The active DBLite object (or a new one)
     */
    public static function factory($config = null)
    {
        static $instance;

        if (!$instance) {
            $instance = new self($config);
        }
        return $instance;
    }



    /**
     * Static mapping to methods (maps PDO methods directly)
     * @param String $method    Method name to pass to PDO
     * @param Array  $argyments Array of arguments to pass to the method
     */
    public static function __callStatic($method, $arguments)
    {
        $instance = DBLite::factory();
        return call_user_func_array([$instance, $method], $arguments);
    }



    /**
     * Auto-maps instance calls to PDO
     * @param  String $method    name of the method to call
     * @param  Array  $arguments array of arguments to pass
     * @return Mixed             return from PDO call
     */
    public function __call($method, $arguments)
    {
        if ($return = call_user_func_array([$this->db, $method], $arguments)) {
            return $return;
        } else {
             print_r($this->db->errorInfo());
        }
    }
}
