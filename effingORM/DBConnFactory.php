<?php

use \MySqlAccessObject;
use \DBTypeNotSupportedException;

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
 * The DBConnFactory class provides methods to create a connection or release a connection
 * to / from a database. All functions provide a database handler object as return objects 
 * to perform additional ORM functionality.
 *
 * @author  Cyra Fredrich <cf048@hdm-stuttgart.de>
 *          Felix Schröder <fs087@hdm-stuttgart.de>
 *          Julian Weiß <jw083@hdm-stuttgart.de>
 *          Dennis Grewe <dg060@hdm-stuttgart.de>
 * @version 0.9 2015-03-11
 */
abstract class DBConnFactory 
{
	//-----------------------------------------------------
	// MEMBERS
	//-----------------------------------------------------
	
    private static $dbh = null;
    
	//-----------------------------------------------------
	// METHODS
	//-----------------------------------------------------
	
    /**
	 * This method creates a new connection to a certain database based by the given parameters. 
	 * The required connection options are configured in the DBConfiguration class. The method
	 * returns a handler object for further operations.
     * 
     * @param string $type  			The type of the DB (e.g.: 'mysql')
     * @param array() $driver_options	Options params to specify the conenction to the database more in detail.
	 *									These options are based on the PHP Data Object engine.
     * @return A database handler object.
     */
    public static function connect($type, $driver_options=array()) 
	{
        $type = strtolower($type);
        if ($type == 'mysql') 
		{
            self::$dbh = new MySqlAccessObject($driver_options);
        }
        else if ($type == 'oracle') 
		{
            printf('No implementation for oracle databases found!s');
        }
        else  
		{
            throw new DBTypeNotSupportedException("The type you declared ('{$type}') to connect to the database is not supported! " . 
                "Please enter correct database type.");
        }

        return self::$dbh;
    }
    
    /**
	 * The method returns an existing handler if a connection to a database still exists.
	 * The method returns FALSE if there is no existing connection yet.
     * 
     * @return  A database handler object, if a connection exists. Otherwise FALSE.
     */
    public static function getOpenConnection() 
	{
        if (!is_null(self::$dbh)) 
		{
            return self::$dbh;
        }
        else 
		{
            return FALSE;
        }
    }
    
    /**
     * The method releases explicit an open database connection if it exists. If this was
     * successful the method returns TRUE. If there is no database connection to release the
     * method returns FALSE.
     * 
     * @return boolean TRUE if the release process was successfull, otherwise FALSE
     */
    public static function release() 
	{
        $successfullRelease = FALSE;
        if (!is_null(self::$dbh)) 
		{
            self::$dbh->__destruct();
            $successfullRelease = TRUE;
        }
        
        return $successfullRelease;
    }
}

?>