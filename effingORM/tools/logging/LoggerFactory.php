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
 * This class defines a factory class for any kind of logger initialization in this
 * ORM framework. Using this class creates a new instance of a logger which logs some
 * information about the database access layer directly into some logging files in the
 * file system.
 *
 * @author  Cyra Fredrich <cf048@hdm-stuttgart.de>
 *          Felix Schröder <fs087@hdm-stuttgart.de>
 *          Julian Weiß <jw083@hdm-stuttgart.de>
 *          Dennis Grewe <dg060@hdm-stuttgart.de>
 * @version 0.9 2015-03-11
 */
abstract class LoggerFactory 
{    
	//-----------------------------------------------------
	// METHODS
	//-----------------------------------------------------
	
    /**
	 * This method returns a new instance of a certain logger. Each log statement of this
	 * logger will be stored in a file on the file system seperately.
     * 
     * @param string $component_name 	The name of the logger (e.g. the name of the class or component)
     * @param array $option 			Some options of the logger. Depends on the implementation used by this factory.
     * @return 							A new instance of a logger class.
     */
    public static function getLogger($component_name, $option=array()) 
	{
        if (!empty($option)) 
		{
            // no other implementation of a logger yet
        }
        else 
		{
            return new Logger($component_name);
        }
    }
}

?>
