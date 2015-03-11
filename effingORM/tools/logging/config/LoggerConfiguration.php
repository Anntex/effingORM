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
 * This class provides some configuration options of the framework logger. For example, 
 * this includes the file path of the logging files, etc. This class in just a possible
 * implementation and can be extended to support much more different options.
 *
 * @author  Cyra Fredrich <cf048@hdm-stuttgart.de>
 *          Felix Schröder <fs087@hdm-stuttgart.de>
 *          Julian Weiß <jw083@hdm-stuttgart.de>
 *          Dennis Grewe <dg060@hdm-stuttgart.de>
 * @version 0.9 2015-03-11
 */
abstract class LoggerConfiguration 
{
	//-----------------------------------------------------
	// MEMBERS
	//-----------------------------------------------------
    
    protected static $DEFAULT_FILE_NAME = null;

	//-----------------------------------------------------
	// METHODS
	//-----------------------------------------------------
    
	/**
	 * @return	By default, this method returns the root directory of the application server
	 *			for logging information. In a live system this directory should not be root.
	 */
    public static function defaultFileDir() 
	{
        if (is_null(self::$DEFAULT_FILE_NAME)) 
		{
            self::$DEFAULT_FILE_NAME = $_SERVER['DOCUMENT_ROOT'];
        }
        return self::$DEFAULT_FILE_NAME;
    }
}

?>
