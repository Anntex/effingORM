<?php

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
 * This class defines some basic implementation which should be supported by every SQL database.
 * This functionality is in depended of the underlying SQL dialect used by the database. Additionally,
 * this class supports some methods to build generic database queries independent of a certain
 * entity definition.
 *
 * @author  Cyra Fredrich <cf048@hdm-stuttgart.de>
 *          Felix Schröder <fs087@hdm-stuttgart.de>
 *          Julian Weiß <jw083@hdm-stuttgart.de>
 *          Dennis Grewe <dg060@hdm-stuttgart.de>
 * @version 0.9 2015-03-11
 */
abstract class AbstractAccessObject 
{
    //-----------------------------------------------------
	// MEMBERS
	//-----------------------------------------------------
	
    private $INSERT_INTO 	= "INSERT IGNORE INTO ";
    private $SELECT 		= "SELECT ";
    private $FROM 			= " FROM ";
    private $WHERE 			= " WHERE ";
    private $AND 			= " AND ";
    
    /**
     * This method sets up a transaction between the framework and the database. This is
	 * required to support consistency based on the ACID paradigm.
	 *
     * @param type $dbh		The database handler object.
     * @param type $query	The query which should be processed by the 
     */
    protected function perform_query_in_transaction(\PDO $dbh, $query) 
	{
        $success = FALSE;
        
        try 
		{
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $dbh->beginTransaction();
            $dbh->exec($query);
            LoggerFactory::getLogger(__CLASS__)->log($query, 1);
            $dbh->commit();
            $success = TRUE;
        } 
		catch (Exception $e) 
		{
            $dbh->rollBack();
            LoggerFactory::getLogger(__CLASS__)->log("Failed insert into query:" . $e->getMessage(), 1);
        }
        
        return $success;
    }
    
	/**
	 * This method builds an INSERT statement for a certain database depending on the data object definition.
	 *
	 * @param type $db_name			The name of the database.
	 * @param type $object			The object definition and the data which should be inserted into the database.
	 * @param type $result_array	Just for resolving dependencies between other objects recursively.
	 */
    protected function build_generic_insert_statement($db_name, $object, $result_array = array(), $j = 0) 
	{
        $prop_value_array = ReflectionUtil::collect_all_class_properties($object);
        $loop_variable = 0;
        $insert_query = $this->INSERT_INTO . "`{$db_name}`.`" . strtolower(get_class($object)) . "` (";
        $insert_value_query = " VALUES (";
        
        foreach ($prop_value_array as $prop_name => $prop_value) 
		{
            $recursive_flag = FALSE;
            
            if ((strpos($prop_name, "_id") !== FALSE) && is_object($prop_value)) 
			{
                $result_array = $this->build_generic_insert_statement($db_name, $prop_value, $result_array, $j++);
                $recursive_flag = TRUE;
            }
            else 
			{
                if (is_null($prop_value)) 
				{ 
                    $prop_value = "NULL";
                }
                if (($loop_variable < count($prop_value_array) - 1)) 
				{
                    $insert_query = $insert_query . "`" . $prop_name . "`, ";
                    $insert_value_query = $insert_value_query . $this->create_insert_value_statement_depending_on_value_type($prop_value) . ", ";
                }
                else {
                    $insert_query = $insert_query . "`" . $prop_name . "`)";
                    $insert_value_query = $insert_value_query . $this->create_insert_value_statement_depending_on_value_type($prop_value) . ");";
                }
                $loop_variable++;
            }
            
            if ($recursive_flag) 
			{
                $insert_query = $insert_query . "`" . $prop_name . "`, ";
                if ($loop_variable < count($prop_value_array) - 1) 
				{
                    $insert_value_query = $insert_value_query . $this->build_select_statement_from_object($prop_name, $prop_value) . ", ";
                }
                else 
				{
                    $insert_value_query = $insert_value_query . $this->build_select_statement_from_object($prop_name, $prop_value);
                }
                $loop_variable++;
            }
        }
        
        // build the statement together and pass it to the database handler object to perform the query
        $insert_query = $insert_query . $insert_value_query;
        $result_array[$j] = $insert_query;
        
        return $result_array;
    }
    
    /**
	 * This method provides functionality to build INNER JOIN statements. This is required to resolve / create
	 * FK dependencies between multiple database tables.
     * 
     * @param string $db_name 		The name of the database
     * @param object $class 		The entity class definition to collect the FK information.
     * @return 						String of the generated INNER JOIN statement.
     */
    protected function build_generic_inner_join_satement($db_name, $class) 
	{
        $result_query = "";
        $FK_properties = ModelUtil::collectAllForeignKeysOfAnEntity($class);
        $class_name = strtolower(get_class($class));
        
        if (!empty($FK_properties)) 
		{
            foreach ($FK_properties as $key => $value) 
			{
                $fk_name = explode("_", $key);
                $targetTable = strtolower($fk_name[0]);
                $join_query = " INNER JOIN `{$db_name}`.`";                               // INNER JOIN QUERY
                $join_query = $join_query . "{$targetTable}` ON ";                        // TARGET TABLE
                $join_query = $join_query . "`{$db_name}`.`{$targetTable}`.`id` = ";      // solve FK 
                $result_query = $result_query . $join_query . "`{$db_name}`.`{$class_name}`.`{$targetTable}_id` ";    // call target table
            }
        }
        
        return $result_query;
    }
    
    /**
	 * This method generates a SELECT statement for all properties of the data object.
     * 
     * @param string $table_name 	The name of the table to create the statement for.
     * @param object $object 		The domain object definition to access the table schema.
     * @return 						The generated SELECT statement ready to be processed as query of a database.
     */
    protected function build_generic_select_all_statement($table_name, $object) 
	{
        $statement = "";
        $object_properties = ReflectionUtil::collect_all_class_properties($object);

        if (!empty($object_properties)) 
		{
            $array = ReflectionUtil::collect_all_class_properties($object);
            $loop_variable = 0;
            foreach ($array as $key => $value) 
			{
                if (ModelUtil::test_property_for_foreign_key($key)) 
				{
                    $foreign_key_class_name = StringUtil::first_char_to_upper_case(spliti("_", $key));
                    $foreign_key_class = new $foreign_key_class_name();
                    $statement = $statement . $this->build_generic_select_all_statement(strtolower($foreign_key_class), $foreign_key_class);
                }
                else 
				{
                    $statement = $statement . " `{$table_name}`.`" . "{$key}`";
                }
                
                // we need to separate the different properties. if there are other
                if ($loop_variable < count($array) - 1) 
				{
                    $statement = $statement . ", ";
                }
                $loop_variable++;
            }
        }
        else 
		{
            $statement = " * ";
        }
        
        return $statement;
    }
    
    /**
	 * This method generates a SELECT ALL statement of all columns and entries of a certain database table.
     * 
     * @param string $table_name 	The name of the table
     * @param object $object 		The entity object to access the table schema.
     * @return 						The generated string of the SELECT ALL statement.
     */
    protected function build_generic_select_all_AS_statement($table_name, $object) 
	{
        $statement = "";
        $object_properties = ReflectionUtil::collect_all_class_properties($object);

        if (!empty($object_properties)) 
		{
            $array = ReflectionUtil::collect_all_class_properties($object);
            $loop_variable = 0;
            foreach ($array as $key => $value) 
			{
                if (ModelUtil::test_property_for_foreign_key($key)) 
				{
                    $fk_name = explode("_", $key);
                    $foreign_key_class_name = StringUtil::first_char_to_upper_case($fk_name[0]);
                    $foreign_key_class = new $foreign_key_class_name();
                    $statement = $statement . $this->build_generic_select_all_AS_statement(strtolower($foreign_key_class_name), $foreign_key_class);
                }
                else 
				{
                    $statement = $statement . " `{$table_name}`.`" . "{$key}` AS {$table_name}_{$key}";
                }
                
                // we need to separate the different properties. if there are other
                if ($loop_variable < count($array) - 1 && $key !== '') {
                    $statement = $statement . ", ";
                }
                $loop_variable++;
            }
        }
        
        return $statement;
    }
    
    /**
	 * This method converts the given php member data types into the correct query definition in SQL.
	 * Depending on the given data type, SQL requires some ' symbols. Regarding numbers, there are
	 * no ' symbols allowed. For example:
     * 
     *  * string "Hallo"    --> 'Hallo'
     *  * int 10            --> 10 NOT '10'
     * 
     * @param mixed $param 		The value which should be converted into the correct query rules.
     * @return mixed 			The formatted value as string.
     */
    protected function create_insert_value_statement_depending_on_value_type($param) 
	{
        if ($param != "NULL") 
		{
            $param = "'" . $param . "'";
        }
		
        return $param;
    }
    
    private function build_select_statement_from_object($property_name, $object) 
	{
        $return_insert_select = FALSE;
        $loop_var = 0;
		
        if (is_object($object)) 
		{
            $prop_name_splitted = explode("_", $property_name);
            $return_insert_select = "({$this->SELECT}". $prop_name_splitted[1] . "{$this->FROM}" . $prop_name_splitted[0] . "{$this->WHERE}";
            $object_props = ReflectionUtil::collect_all_class_properties($object);
			
            foreach ($object_props as $prop => $value) 
			{
                if (!is_null($value) && !empty($value)) 
				{
                    if ($loop_var > 1) 
					{
                        if ($loop_var < count($object_props) - 1) 
						{
                            $return_insert_select = $return_insert_select . "{$this->AND}" . '`' . $prop . '` = ' . $this->create_insert_value_statement_depending_on_value_type($value);
                        }
                        else 
						{
                            $return_insert_select = $return_insert_select . "{$this->AND}" . '`' . $prop . '` = ' . $this->create_insert_value_statement_depending_on_value_type($value);
                        }
                    }
                    else 
					{
                        if ($loop_var < count($object_props) - 1) 
						{
                            $return_insert_select = $return_insert_select . '`' . $prop . '` = ' . $this->create_insert_value_statement_depending_on_value_type($value);
                        }
                        else 
						{
                            $return_insert_select = $return_insert_select . '`' . $prop . '` = ' . $this->create_insert_value_statement_depending_on_value_type($value);
                        }
                    }
                }
				
                $loop_var++;
            }
			
            $return_insert_select = $return_insert_select. ")";
        }
        
        return $return_insert_select;
    }
}

?>
