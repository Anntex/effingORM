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
 * This class provides some util methods to perform some reflection operations on
 * data objects. This is required to get access to some of the member values at runtime.
 * For further details about reflection, see: http://en.wikipedia.org/wiki/Reflection_(computer_programming)
 *
 * @author  Cyra Fredrich <cf048@hdm-stuttgart.de>
 *          Felix Schröder <fs087@hdm-stuttgart.de>
 *          Julian Weiß <jw083@hdm-stuttgart.de>
 *          Dennis Grewe <dg060@hdm-stuttgart.de>
 * @version 0.9 2015-03-11
 */
abstract class ReflectionUtil 
{
	//-----------------------------------------------------
	// METHODS
	//-----------------------------------------------------
	
    /**
	 * This method reads all member names of a given object by using the reflection mechanism of PHP.
	 * All member names will be returned as an array.
     * 
     * @param object $object	The object which contains the member names.
     * @return array    		Returns a array which contains all member names as KEYS and their values as VALUES.
     */
    public static function collect_all_class_properties($object) 
	{
        $result_array = array();
        /* create a new reflection class of the object to access the member names for mapping these as fields
		 * into the database */
        $reflect = new ReflectionClass($object);
        $properties = $reflect->getProperties();

        /* get all member names of a given object */
        foreach ($properties as $prop) 
		{
            $propertyName = $prop->getName();
            $result_array[$propertyName] = self::read_value_of_a_private_class_member($reflect, $object, $propertyName);
        }

        return $result_array;
    }
    
    /**
	 * This method reads the value of a certain member variable. This includes the values of 
	 * private members --> TODO: better to use getter and setter methods instead of reflection
     * 
     * @param ReflectionClass $reflection_class 	The reflection class of the desired object.
     * @param object $object_to_read_property 		The object which contains the member
     * @param string $property_name 				The name of the member
     */
    public static function read_value_of_a_private_class_member($reflection_class, $object_to_read_property, $property_name) 
	{
        $property = $reflection_class->getProperty($property_name);
        $property->setAccessible(true);                                 // if visibility is private, set accessibility == true
        return $property->getValue($object_to_read_property);
    }
}

?>
