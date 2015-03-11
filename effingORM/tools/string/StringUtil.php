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
 * This class provides util methods for string manipulation.
 *
 * @author  Cyra Fredrich <cf048@hdm-stuttgart.de>
 *          Felix Schröder <fs087@hdm-stuttgart.de>
 *          Julian Weiß <jw083@hdm-stuttgart.de>
 *          Dennis Grewe <dg060@hdm-stuttgart.de>
 * @version 0.9 2015-03-1
 */
abstract class StringUtil 
{
    //-----------------------------------------------------
	// METHODS
	//-----------------------------------------------------
	
    /**
	 * The method transforms the first letter of a string into 
	 * upper case.
     * 
     * @param string $param The string value 
     * @return string 		The transformed string.
     */
    public static function first_char_to_upper_case($param) 
	{
        return ucwords($param);
    }
    
    /**
     * This method converts names of member variables into public conventions of database naming. 
	 * For example.
     * 
     *  * createDate            --> create_date
     *  * customerToAccountId   --> customer_to_account_id
     * 
     * @param string $param The string value which should be transformed by this method.
     * @return string 		The transformed string value.
     */
    public static function transform_name_to_db_convetion($param) 
	{
        $replacedUpperCaseLettersToUnderscore = preg_replace('/\B([A-Z])/', '_$1', $param);
        $transforemdResult = strtolower($replacedUpperCaseLettersToUnderscore);
		
        return $transforemdResult;
    }
    
    /**
	 * This method replaces all characters of a given string based on the rules of the given array.
     * 
     * @param string $string			The string which should be changed.
     * @param array $replacementRules 	The array containing the mapping rules (Key = the value to replace, Value = replacement)
     */
    public static function replaceStrings($string, array $replacementRules) 
	{
        if (!is_null($replacementRules) && !empty($replacementRules)) 
		{
            foreach ($replacementRules as $key => $replacement) 
			{
                $toReplace = $key;
                if (!is_null($replacement)) 
				{
                    $string = str_replace($toReplace, $replacement, $string);
                }
                else 
				{
                    $string = str_replace($string, "", $string);    // if null -> set ""
                }
            }
        }
        
        return $string;
    }
}

?>