<?php

use \BasicLogger;

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
 * This class defines one possible logger implementation of the framework. The following
 * class only provides some basic functionality, such as writing formatted information to the file
 * system. This information can be helpful in the future, for example for debug purposes.
 *
 * @author  Cyra Fredrich <cf048@hdm-stuttgart.de>
 *          Felix Schröder <fs087@hdm-stuttgart.de>
 *          Julian Weiß <jw083@hdm-stuttgart.de>
 *          Dennis Grewe <dg060@hdm-stuttgart.de>
 * @version 0.9 2015-03-11
 */
class Logger implements BasicLogger 
{
	//-----------------------------------------------------
	// MEMBERS
	//-----------------------------------------------------
	
    private $component_name = null;
    private $file_name		= null;
    private $file_dir 		= null;
    
	//-----------------------------------------------------
	// CONSTRUCTOR
	//-----------------------------------------------------
	
    /**
     * This constructor creates a new instance of a logger used by this framework.
     * 
     * @param string $component_name 	The name of the component (e.g.: class, component, method).
     * @param array $option 			The options of the logger.
	 *									* file_name:	the name of the logging file in the file system. 
	 *									* file_dir:		the logging directory, based on the root directory.
     */
    public function __construct($component_name, $option = array()) 
	{
        /* check the given options, if there are no one specified use the default values */
        if (!empty($option)) 
		{
            $this->file_name = isset($option["file_name"]) ? $option["file_name"] : "/log.txt";
            $this->file_dir = isset($option["file_dir"]) ? $option["file_dir"] : "/log";
        }
        else 
		{
            $this->file_dir = LoggerConfiguration::defaultFileDir() . "/log";
            $this->file_name = "/log.txt";
        }
        $this->component_name = $component_name;
    }
	
	//-----------------------------------------------------
	// METHODS
	//-----------------------------------------------------

    /**
     * This method writes a certain message into a log file in the file system.
     * 
     * @param string $message 	The message which should be written into the file
     * @param int $level 		The level of the message which indicates the priority of this message.
     *  							* 1: INFO - simple message | low prio (DEFAULT)
     *  							* 2: ERROR - error message | mid prio
     *  							* 3: FATAL - fatal message | high prio
     */
    public function log($message, $level = 1) 
	{
        if (!file_exists($this->file_dir)) 
		{
            mkdir($this->file_dir);
        }
		
        $log_file_handler = fopen($this->file_dir . $this->file_name, 'a');   // open file, just containing write permissions
        $formatted_message = "[" . date("Y/m/d h:i:s", time()) . "] - " 
                . $this->returnLogLevel($level) . " - " 
                . "Component: {$this->component_name} - "
                . $message . PHP_EOL;
        fwrite($log_file_handler, $formatted_message);      // write message into file
        fclose($log_file_handler);                          // close the file
    }
    
    private function returnLogLevel($level) 
	{
        $return_statement = "";
        switch ($level) {
            case 1:
                $return_statement = " INFO ";
                break;
            case 2:
                $return_statement = " ERROR ";
                break;
            case 3:
                $return_statement = " FATAL ";
                break;
            default:
                break;
        }
        return $return_statement;
    }
}

?>
