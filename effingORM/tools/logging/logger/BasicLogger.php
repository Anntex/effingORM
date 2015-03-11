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
 * This interface defines the basic functionality of each logger implementation used in this framework.
 * Each logger should implement this interface to be able to exchange the logger during runtime.
 * 
 * @author  Cyra Fredrich <cf048@hdm-stuttgart.de>
 *          Felix Schröder <fs087@hdm-stuttgart.de>
 *          Julian Weiß <jw083@hdm-stuttgart.de>
 *          Dennis Grewe <dg060@hdm-stuttgart.de>
 * @version 0.9 2015-03-11
 */
interface BasicLogger 
{
	//-----------------------------------------------------
	// METHODS
	//-----------------------------------------------------
	
    /**
     * This method sends a message to the storage logic of the logger implementation.
	 * The priority of the message is indicated by the given log level (default: level 1).
     * 
     * @param string $message 	The message as string.
     * @param int $level 		The log level which indicates the priority of the message.
	 *							The following levels will be supported in this version:
     *  							* 1: low prio (DEFAULT)
     *  							* 2: mid prio
     *  							* 3: high prio
     */
    public function log($message, $level = 1);
}

?>