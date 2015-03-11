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
 * This interface defines the base functionality of the database access layer. 
 * All the functionality is based in the CRUD paradigma.
 * 
 * @author  Cyra Fredrich <cf048@hdm-stuttgart.de>
 *          Felix Schröder <fs087@hdm-stuttgart.de>
 *          Julian Weiß <jw083@hdm-stuttgart.de>
 *          Dennis Grewe <dg060@hdm-stuttgart.de>
 * @version 0.9 2015-03-11
 */
interface BasicAccess 
{
    
    /**
	 * This method enables the functionality to read a certain entry by the ID of the entry in the database.
     * 
     * @param string $table_name 	The name of the table to read a certain entry from it.
     * @param int $id 				The ID value of the entry.
     * @return 						The entry of the database as php data object.
     */
    public function selectById($table_name, $id);
    
    /**
	 * This method provides functionality to read certain data values from a certain table. For example,
	 * this method can be helpful for doing some join operations.
     * 
     * @param string $table_name 	The name of the table to read data from it.
     * @param int $key 				The name of the column
     * @param mixed $value 			The value which sould be filtered.
     * @param boolean $simple 		Describes if a simple SELECT statement should be used or if the start (*) operator
	 *								should be used in the statement.
     *                          	DEFAULT: TRUE
	 *								FALSE only tries to filter by the given value.
     * @return array				An array containing all the result objects.
     */
    public function selectByProperty($table_name, $key, $value, $simple = TRUE);
    
    /**
     * This method fetches all entries of a given table.
     * 
     * @param string $table_name 	The name of the table to read data from.
     * @return array 				An array containing all the result objects.
     */
    public function selectAll($table_name);
    
    /**
	 * This method inserts a new data object into the data base and creates a new entry.
	 * If the entry still exists, the method updates the entry in the database.
	 *
     * @param object $object_to_insert The object which should be inserted into the database.
     */
    public function insert($object_to_insert);
    
    /**
     * This method deletes a certain entry from a database table.
	 *
     * @param string $table_name	The name of the table to delete an entry from.
     * @param int $id				The ID of the entry.
     */
    public function deleteById($table_name, $id);
}

?>