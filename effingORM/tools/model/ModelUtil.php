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
 * This util class provides functionality for accessing and collecting some 
 * information of an database entity object represented as php model. One of 
 * the key functionality is the detection of foreign key definition in the entity
 * model.
 *
 * @author  Cyra Fredrich <cf048@hdm-stuttgart.de>
 *          Felix Schröder <fs087@hdm-stuttgart.de>
 *          Julian Weiß <jw083@hdm-stuttgart.de>
 *          Dennis Grewe <dg060@hdm-stuttgart.de>
 * @version 0.9 2015-03-11
 */
abstract class ModelUtil 
{
	//-----------------------------------------------------
	// MEMBERS
	//-----------------------------------------------------
    
    private static $FK_ID = "_id";
    
	//-----------------------------------------------------
	// METHODS
	//-----------------------------------------------------
	
    /**
	 * This method tests a certain entity having some foreign key definitions. In this case,
	 * the method checks the naming of the member field whether one has the suffix '_id'.
     * 
     * @param object $object 	The entity object which should be tested.
     * @return boolean 			TRUE, if there are any definitions, otherwise FALSE
     */
    public static function testDomainObjektForForeignKeys($object) 
	{
        $result = FALSE;
        $object_properties = ReflectionUtil::collect_all_class_properties($object);
        
        foreach ($object_properties as $key => $value) 
		{
            if ((strpos($key, self::$FK_ID) !== FALSE)) 
			{
                $result = TRUE;
            }
        }
        
        return $result;
    }
    
    /**
     * This method tests a certain member field to be a FK field definition.
     * 
     * @param string $property_name The name of the member which should be tested as string.
     * @return boolean 				TRUE, if the member is a FK definition, otherwise FALSE.
     */
    public static function test_property_for_foreign_key($property_name) 
	{
        $result = FALSE;
        
        if ((strpos($property_name, self::$FK_ID) !== FALSE)) 
		{
            $result = TRUE;
        }
        
        return $result;
    }
    
    /**
	 * This method iterates over all member names of an object and detects foreign key definitions.
     * 
     * @param object $object The model object, which should be tested for foreign key definitions
     * @return array 		 Returns an array containing all FK definitions in it.
     */
    public static function collectAllForeignKeysOfAnEntity($object) 
	{
        $result = array();
        $object_properties = ReflectionUtil::collect_all_class_properties($object);
        
        $log = LoggerFactory::getLogger("ModelUtil");
        $log->log("Number of member field of the object: " . get_class($object) . ": " . count($object_properties));
        
        foreach ($object_properties as $key => $value) 
		{
            if (strpos($key, self::$FK_ID) !== FALSE) 
			{
                $result[$key] = $value;
            }
        }
        
        $log->log("Following FK definitions found: " . count($result));
        
        return $result;
    }
	
    public static function transform_fetch_result_to_model_object($target_class, array $fetch_result) 
	{
        $target_class_properties = ReflectionUtil::collect_all_class_properties($target_class);
        
        foreach ($target_class_properties as $key => $value) 
		{
            $target_class_lower_case = strtolower(get_class($target_class));
            if (ModelUtil::test_property_for_foreign_key($key)) 
			{
                $fk_name = explode("_", $key);
                $recursive_target_class_name = StringUtil::first_char_to_upper_case($fk_name[0]);
                $new_target_class = new $recursive_target_class_name();
                $target_class->__set($key, self::transform_fetch_result_to_model_object($new_target_class, $fetch_result));
            }
            else 
			{
                $fetch_result_key = "{$target_class_lower_case}_{$key}";
                $target_class->__set($key, $fetch_result[0][$fetch_result_key]);
            }
        }
        
        return $target_class;
    }
}

?>
