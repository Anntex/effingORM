<?php

use \AbstractAccessObject;
use \BasicAccess;
use \StringUtil;
use \DBConfiguration;
use \ReflectionUtil;
use \LoggerFactory;
use \ModelUtil;

/* The MIT License (MIT)

Copyright (c) 2015 Dennis Grewe, Felix Schröder, Julian Weiß, Cyra Fredrich

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE. */

/**
 * This class defines a database access component for a specific SQL dialect, MySQL. By using the 
 * configuration class, an object of this class creates a connection to a MySQL database and sets up
 * all required options to be able to process data between your business logic and a MySQL database.
 * This class provides concrete implementations to perform CRUD operations to this kind of database.
 *
 * @author  Cyra Fredrich <cf048@hdm-stuttgart.de>
 *          Felix Schröder <fs087@hdm-stuttgart.de>
 *          Julian Weiß <jw083@hdm-stuttgart.de>
 *          Dennis Grewe <dg060@hdm-stuttgart.de>
 * @version 0.9 2015-03-11
 */
class MySqlAccessObject extends AbstractAccessObject implements BasicAccess 
{
	//-----------------------------------------------------
	// MEMBERS
	//-----------------------------------------------------
    
    /**
     * @var PDO the database handler object
     */
    private $dbh = null;
    /**
     * @var logger instance of a logger used in this class to log statements and messages.
     */
    private $log = null;
    /**
     * @var string name of the database you want to access to.
     */
    private $db_name = null;
    
	//-----------------------------------------------------
	// CONSTRUCTOR
	//-----------------------------------------------------	
    
	/**
     * Constructs a new instance of this access object.
     * 
     * @param array $options 	The options for this access object:
	 *							* host: the name of the host (ip:port)
	 *							* dbname: the name of the db
	 *							If no options are provided the values from the configuration class will be used
     */
    public function __construct($options = array()) 
	{
        try 
		{
            $this->dbh = new PDO("mysql:host=" . DBConfiguration::$HOST . 
                    ";dbname=" . DBConfiguration::$DB_NAME, 
                    DBConfiguration::$USER, 
                    DBConfiguration::$PW, $options);
            $this->dbh->exec("SET CHARACTER SET utf8");
            $this->db_name = DBConfiguration::$DB_NAME;
            $this->log = LoggerFactory::getLogger(__CLASS__);
        } 
		catch (PDOException $exc) 
		{
            echo $exc->getTraceAsString();
            die();
        }
    }

	//-----------------------------------------------------
	// DESTRUCTOR
	//-----------------------------------------------------
	
    /**
     * Destructs an existing database access object.
     */
    public function __destruct() 
	{
        if (!is_null($this->dbh)) {
            $this->dbh = null;
        }
    }

	//-----------------------------------------------------
	// METHODS
	//-----------------------------------------------------
	
    /**
	 * This method stores a new table entry or updates the values if the entry exists.
	 * The method returns TRUE if the operation was successful, otherwise FALSE.
     * 
     * @param string $table_name 		The name of the table to store the data into.
     * @param object $object_to_insert 	The data object which provides the data to store.
     * @return boolean  				FALSE if:
	 *										* the object is no object 
	 *										* or no definition was found.
     */
    public function insert($object_to_insert) 
	{
        $successfull_insert = FALSE;
        $insert_query = "";
        
        if (is_object($object_to_insert)) 
		{
            $statement = $this->build_generic_insert_statement($this->db_name, $object_to_insert);
            
            foreach ($statement as $key => $value) 
			{
                $insert_query = $insert_query . $value;
            }
            
            $successfull_insert = $this->perform_query_in_transaction($this->dbh, $insert_query);
        }
        
        return $successfull_insert;
    }

    /**
     * This method reads all entries of the given table and maps each entiry of the table into a corresponding data object at runtime.
	 * TODO: At the present time no resolve of FK dependencies.
     * 
     * @param string $table_name 	The name of the table to read all entries from.
     * @return array 				An array containing all fetched entries as data objects.
     */
    public function selectAll($table_name) 
	{
        $result_statement = FALSE;
        
        // check if the data object still exists in the runtime definition
        if (class_exists($table_name)) 
		{
                $select_query = "SELECT * FROM `{$this->db_name}`.`{$table_name}`";
                $stmt = $this->dbh->prepare($select_query);
                $this->log->log($select_query, 1);
                $stmt->execute();
                $result_statement = $stmt->fetchAll(PDO::FETCH_CLASS, StringUtil::first_char_to_upper_case($table_name));
        }
        
        return $result_statement;
    }

    /**
	 * This method provides functionality to read a certain data entry from a specific table in the database.
	 * The method returns the values mapped directly to the correct php data object.
     * 
     * @param string $table_name 	The name of the table to read the correct entry.
     * @param int $id 				The ID of the data row entry to read from table.
     * @return object 				The existing data entry as mapped object.
     */
    public function selectById($table_name, $id) 
	{
        $result_statement = "";
        
        // testen ob model ueberhaupt im projekt existiert
        if (class_exists($table_name)) 
		{
            $target_name = StringUtil::first_char_to_upper_case($table_name);
            $target_class = new $target_name();
            
            if (ModelUtil::testDomainObjektForForeignKeys($target_class)) 
			{
                
                $select_query = "SELECT " . $this->build_generic_select_all_AS_statement($table_name, $target_class) . " FROM `{$this->db_name}`.`{$table_name}`" . 
                        $this->build_generic_inner_join_satement($this->db_name, $target_class) . 
                        " WHERE `{$this->db_name}`.`{$table_name}`.`id`={$id}";
                $stmt = $this->dbh->prepare($select_query);
                $this->log->log($select_query, 1);
                $stmt->execute();
                $result_statement = ModelUtil::transform_fetch_result_to_model_object($target_class, $stmt->fetchAll());
            }
            else 
			{
                $select_query = "SELECT " . $this->build_generic_select_all_statement($table_name, $target_class) . 
                                " FROM `{$this->db_name}`.`{$table_name}` WHERE `{$this->db_name}`.`{$table_name}`.`id`={$id}";
                $stmt = $this->dbh->prepare($select_query);
                $this->log->log($select_query, 1);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_CLASS, StringUtil::first_char_to_upper_case($table_name));
                $result_statement = $result[0];
            }
        }
        
        return $result_statement;
    }  
    
    /**
	 * This method enables the possibility to read certain data values from a table depending on the given member name OR value
	 * of the corresponding data object definition.
     * 
     * @param string $table_name 	The name of the table to read data from.
     * @param int $key 				The name of the column to read data from.
     * @param mixed $value 			The filtered value.
     * @param boolean $simple 		Describes if the function should use the start (*) operator in the query or not.
     *                          	DEFAULT: TRUE
     * @return array				An array containing the fetched results.
     */
    public function selectByProperty($table_name, $key, $value, $simple = TRUE) 
	{
        $result = FALSE;
        
        if (class_exists($table_name)) 
		{
            if ($simple) 
			{
                $select_query = "SELECT * FROM `{$this->db_name}`.`{$table_name}` WHERE `{$table_name}`.`{$key}`={$value}";
                $stmt = $this->dbh->prepare($select_query);
                $this->log->log($select_query, 1);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_CLASS, StringUtil::first_char_to_upper_case($table_name));
                
            }
            else 
			{
                $target_name = StringUtil::first_char_to_upper_case($table_name);
                $target_class = new $target_name();

                $select_query = "SELECT " . $this->build_generic_select_all_AS_statement($table_name, $target_class) . 
                        " FROM `{$this->db_name}`.`{$table_name}`" . 
                        $this->build_generic_inner_join_satement($this->db_name, $target_class) . 
                        " WHERE `{$this->db_name}`.`{$table_name}`.`{$key}`= {$value}";
                $stmt = $this->dbh->prepare($select_query);
                $this->log->log($select_query, 1);
                $stmt->execute();
                $result = ModelUtil::transform_fetch_result_to_model_object($target_class, $stmt->fetchAll());
            }
        }
        
        return $result;
    } 
    
    /**
	 * This method contains the logic to delete an entry from a certain table in the database. This is
	 * done by providing the unique identifier of the table entry, which is provided in the entity object.
	 * The method returns TRUE, if the operation was successful. Otherwise FALSE.
     * 
     * @param string $table_name 	The name of the table
     * @param int $id 				ID of the entry which should be deleted.
     * @return boolean 				TRUE if the delete operation was successful, otherwise FALSE.
     */
    public function deleteById($table_name, $id) 
	{
        $delete_query = "DELETE FROM `{$this->db_name}`.`{$table_name}` WHERE id={$id}";
        printf($delete_query . "<br/>");
        $stmt = $this->dbh->prepare($delete_query);
        return $stmt->execute();
    }
}

?>