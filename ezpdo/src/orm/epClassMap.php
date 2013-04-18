<?php

/**
 * $Id: epClassMap.php,v 1.1 2009/06/17 15:23:53 viktor Exp $
 * 
 * Copyright(c) 2005 by Oak Nauhygon. All rights reserved.
 * 
 * @author Oak Nauhygon <ezpdo4php@gmail.com>
 * @version $Revision: 1.1 $ $Date: 2009/06/17 15:23:53 $
 * @package ezpdo
 * @subpackage ezpdo.orm
 */

/**
 * need epContainer class 
 */
include_once(EP_SRC_BASE.'/epContainer.php');

/**
 * need epFieldMap class 
 */
include_once(EP_SRC_ORM.'/epFieldMap.php');

/**
 * The exception class of {@link epClassMap}
 * 
 * @author Oak Nauhygon <ezpdo4php@gmail.com>
 * @version $Revision: 1.1 $ $Date: 2009/06/17 15:23:53 $
 * @package ezpdo
 * @subpackage ezpdo.orm
 */
class epExceptionClassMap extends epException {
}

/**
 * Class of ezpdo class mapping info
 * 
 * The class keeps the ORM info for a class. It associates a class 
 * with a database (DSN) to which the class is going to be mapped 
 * to for persistence. The class contains the field mapping info 
 * {@link epFieldMap}. The class also keeps track of the path for 
 * the class for later autoloading. As a subclass of {@link 
 * epContainer}, it also maintains the inheritence relationship 
 * among classes. 
 * 
 * A class map is generated by the ezpdo compiler ({@link epClassCompiler}) 
 * through the factory interface, {@link epClassMapFactory}.
 * 
 * @author Oak Nauhygon <ezpdo4php@gmail.com>
 * @version $Revision: 1.1 $ $Date: 2009/06/17 15:23:53 $
 * @package ezpdo
 * @subpackage ezpdo.orm
 */
class epClassMap extends epContainer {
    
    /**
     * The compile time
     * @var integer
     */
    protected $compile_time = false;
    
    /**
     * The class path
     * @var string
     * @access protected
     */
    protected $class_file = '';
    
    /**
     * Associated database dsn
     * @var string
     * @access protected
     */
    protected $dsn = '';
    
    /**
     * The database table the class to be mapped
     * @var string
     * @access protected
     */
    protected $table = '';

    /**
     * The oid column for the class (defult to 'oid')
     * @var string
     * @access protected
     */
    protected $oid_column = 'oid';
    
    /**
     * Is the class abstract
     * @var boolean
     * @access protected
     */
    protected $abstract = false;
    
    /**
     * The class map of the parent class
     * @var epClassMap
     * @access protected
     */
    protected $parent_class_map = null;
    
    /**
     * All fields the class has
     * @var array of epFieldMap keyed by field name
     */
    protected $fields = array();
    
    /**
     * number of non-primitive fields
     * @var bool
     */
    protected $num_non_primitive = 0;
    
    /**
     * number of compoosed_of fields 
     * @var bool
     */
    protected $num_composed_of = 0;

    /**
     * Custom class tags
     * @var array
     * @access protected
     */
    protected $custom_tags = array();

    /**
     * Unique keys
     * of the form
     * array(
     *      keyname = array(
     *                      field,
     *                      field
     *                     ),
     *      keyname = array(
     *                      field
     *                     )
     *      )
     * @var array
     * @access protected
     */
    protected $unique_keys = array();

    /**
     * index keys
     * same form as uniques
     * @var array
     * @access protected
     */
    protected $index_keys = array();
    
    /**
     * Constructor
     * @param string name of the corresponding class
     */
    function __construct($name, $table = false) {
        
        parent::__construct($name);
        
        // by default use the class name as the db table name
        $this->setTable($table ? $table : $name);
        
        // set compile time only when not testing
        if (!defined('EP_TESTING_NOW')) {
            // record the compile time
            $this->setCompileTime();
        }
    }
    
    /**
     * Get the last compile time for the class
     * @return string 
     * @access public
     */
    public function getCompileTime() {
        return $this->compile_time;
    }
    
    /**
     * Set the last compile time now
     * @return string 
     * @access public
     */
    public function setCompileTime() {
        $this->compile_time = time();
    }
    
    /**
     * Get value of class_file 
     * @return string 
     * @access public
     */
    public function getClassFile() {
        return $this->class_file;
    }

    /**
     * Set value to class_file 
     * @param string
     * @return void
     * @access public
     */
    public function setClassFile($class_file) {
        $this->class_file = $class_file;
    }
     
    /**
     * Get oid column for the class 
     * @return string 
     * @access public
     */
    public function getOidColumn() {
        return $this->oid_column;
    }
     
    /**
     * Set oid column for the class 
     * @param string
     * @return boolean
     * @access public
     */
    public function setOidColumn($oid_column) {
        $this->oid_column = $oid_column;
    }
     
    /**
     * Get database table
     * @return string 
     * @access public
     */
    public function getTable() {
        return $this->table;
    }
    
    /**
     * Set database table name
     * @param string
     * @return void
     * @access public
     */
    public function setTable($table) {
        $this->table = $table;
    }
    
    /**
     * Get value of dsn 
     * @return string 
     * @access public
     */
    public function getDsn() {
        return $this->dsn;
    }
    
    /**
     * Set value to dsn 
     * @param string
     * @return bool
     * @access public
     */
    public function setDsn($dsn) {
        $this->dsn = $dsn;
    }

    /**
     * Get class tags
     * @return array (keyed by tag name)
     * @access public
     */
    public function getTags() {
        return $this->custom_tags;
    }

    /**
     * Set class tags
     * @param array $tags class tags
     * @return bool
     * @access public
     */
    public function setTags($tags) {
        return $this->custom_tags = $tags;
    }

    /**
     * Get unique keys
     * @param boolean $recursive (default to true)
     * @return array
     * @access public
     */
    public function getUniqueKeys($recursive = true) {
        
        // array to hold unique keys
        $unique_keys = array();
        
        // recursion
        if ($recursive && $parent = $this->getParent()) {
            if ($unique_keys_ = $parent->getUniqueKeys($recursive)) {
                $unique_keys = array_merge_recursive($unique_keys, $unique_keys_);
            }
        }
        
        // combine keys from parents
        return array_merge_recursive($unique_keys, $this->unique_keys);
    }

    /**
     * Set unique keys
     * @param array $keys unique keys
     * @return bool
     * @access public
     */
    public function setUniqueKeys($keys = array()) {
        return $this->unique_keys = $keys;
    }

    /**
     * Add unique keys
     * @param string $name unique name
     * @param string $key unique key
     * @return bool
     * @access public
     */
    public function addUniqueKey($name, $key) {
        if (isset($this->unique_keys[$name])) {
            $this->unique_keys[$name][] = $key;
        } else {
            $this->unique_keys[$name] = array($key);
        }
        return true;
    }

    /**
     * Get index keys
     * @param boolean $recursive (default to true)
     * @return array
     * @access public
     */
    public function getIndexKeys($recursive = true) {

        // array to hold index keys
        $index_keys = array();

        // recursion
        if ($recursive && $parent = $this->getParent()) {
            if ($index_keys_ = $parent->getIndexKeys($recursive)) {
                $index_keys = array_merge_recursive($index_keys, $index_keys_);
            }
        }
        
        // combine keys from parents
        return array_merge_recursive($index_keys, $this->index_keys);
    }

    /**
     * Set index keys
     * @param array $keys index keys
     * @return bool
     * @access public
     */
    public function setIndexKeys($keys = array()) {
        return $this->index_keys = $keys;
    }

    /**
     * Add index keys
     * @param string $name index name
     * @param string $key index key
     * @return bool
     * @access public
     */
    public function addIndexKey($name, $key) {
        if (isset($this->index_keys[$name])) {
            $this->index_keys[$name][] = $key;
        } else {
            $this->index_keys[$name] = array($key);
        }
        return true;
    }
    
    /**
     * Gets value of abstract
     * @return bool
     * @access public
     */
    public function isAbstract() {
        return $this->abstract;
    }
    
    /**
     * Set value to abstract
     * @param bool
     * @access public
     */
    public function setAbstract($abstract) {
        $this->abstract = $abstract;
    }
     
    /**
     * Get all fields
     * @return array (keyed by field name)
     * @access public
     */
    public function getAllFields() {
        $fields = $this->fields;
        if ($parent = $this->getParent()) {
            $fields = array_merge($parent->getAllFields(), $fields);
        }
        return $fields;
    }

    /**
     * Get field by name
     * @param string $fieldname field name
     * @return false|epFieldMap
     * @access public
     */
    public function &getField($fieldname) {

        // check to see if this class map has the field
        if (isset($this->fields) && isset($this->fields[$fieldname])) {
            return $this->fields[$fieldname];
        }

        // or let's try parent
        if ($parent = $this->getParent()) {
            if ($f  = & $parent->getField($fieldname)) {
                // return immediately if found
                return $f;
            }
        }
        
        // field not found
        return self::$false;
    } 

    /**
     * Get the base (root) field by name
     * @param string $fieldname field name
     * @return false|epFieldMap
     * @access public
     */
    public function &getBaseField($fieldname) {

        // try parent to find root
        if ($parent = $this->getParent()) {
            if ($f = & $parent->getBaseField($fieldname)) {
                return $f;
            }
        }

        // check to see if this class map has the field
        if (isset($this->fields) && isset($this->fields[$fieldname])) {
            return $this->fields[$fieldname];
        }
        
        // field not found
        return self::$false;
    } 

    /**
     * Get field by column name
     * @param string $colname field name
     * @return false|epFieldMap
     * @access public
     */
    public function &getFieldByColumnName($colname) {
        
        // check to see if this class map has the field with the given column name
        if ($this->fields) {
            foreach($this->fields as &$field) {
                if ($field->getColumnName() == $colname) {
                    return $field;
                }
            }
        }

        // or let's try parent
        if ($parent = $this->getParent()) {
            if ($f  = & $parent->getFieldByColumnName($fieldname)) {
                // return immediately if found
                return $f;
            }
        }
        
        // field not found
        return self::$false;
    } 

    /**
     * Add field
     * @param epFieldMap $field
     * @return void
     * @access public
     */
    public function addField(epFieldMap $field) {
        
        // check if field is non primitive
        if (!$field->isPrimitive()) {
            
            // increase the count of non primitive fields
            $this->num_non_primitive ++;
            
            // check if the field is composed_of
            if ($field->isComposedOf()) {
                $this->num_composed_of ++;
            }
        } 
        
        // set class map to field map for easy retrieval
        $field->setClassMap($this);

        $this->fields[$field->getName()] = & $field;
    } 

    /**
     * Remove field by name
     * @param string name
     * @return bool
     * @access public
     */
    public function removeField($fieldname) {
        
        if (empty($this->fields) || !isset($this->fields[$fieldname])) {
            return false;
        }
        
        // check if field is non primitive
        if ($this->fields[$fieldname]->isPrimitive()) {
            $this->num_non_primitive --;
        }
        
        // check if the field is composed_of
        if ($this->fields[$fieldname]->isComposedOf()) {
            $this->num_composed_of --;
        }
        
        unset($this->fields[$fieldname]);
        return true;
    } 

    /**
     * Remove all fields
     * @return void
     * @access public
     */
    public function removeAllFields( ) {
        $this->fields = array();
        $this->num_non_primitive = 0;
        $this->num_composed_of = 0;
    } 
    
    /**
     * Returns whether class has non-primitive type
     * @param boolean $recursive (default to true)
     * @return bool
     * @access public
     */
    public function hasNonPrimitive($recursive = true) {
        
        // if this class has non-primitive fields
        if ($this->num_non_primitive > 0) {
            return true;
        } 
        
        // if not recursive checking and this class has no non-primitive field
        if (!$recursive && $this->num_non_primitive <= 0) {
            return false;
        } 
        
        // recursion
        if ($recursive && $parent = $this->getParent()) {
            if ($parent->hasNonPrimitive($recursive)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Returns the non-primitive fields
     * @param boolean $recursive (default to true)
     * @return false|array
     * @access public
     */
    public function getNonPrimitive($recursive = true) { 
        
        $npfs = array();
        
        // if not recursive and no non-primitive in class
        if (!$recursive && $this->num_non_primitive <= 0) {
            return $npfs;
        }
        
        // recursion
        if ($recursive) {
            if ($parent = $this->getParent()) {
                $npfs = array_merge($parent->getNonPrimitive($recursive), $npfs);
            }
        }
        
        // collect non-primitive field of this class
        foreach($this->fields as $name => &$field) {
            if (!$field->isPrimitive()) {
                $npfs[$name] = $field;
            }
        }
        
        return $npfs;
    }

    /**
     * Returns whether class has composed_of fields
     * @param boolean $recursive (default to true)
     * @return boolean
     * @access public
     */
    public function hasComposedOf($recursive = true) {
        
        // if this class has composed_of fields
        if ($this->num_composed_of > 0) {
            return true;
        } 
        
        // if not recursive checking and this class has no composed_of field
        if (!$recursive && $this->num_composed_of <= 0) {
            return false;
        } 
        
        // recursion
        if ($recursive && $parent = $this->getParent()) {
            if ($parent->hasComposedOf($recursive)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Returns the composed_of fields 
     * @return false|array
     * @access public
     */
    public function getComposedOf($recursive = true) { 
        
        $cofs = array();
        
        // if not recursive and no composed_of in class
        if (!$recursive && $this->num_composed_of <= 0) {
            return $cofs;
        }
        
        // recursion
        if ($recursive) {
            if ($parent = $this->getParent()) {
                $cofs = array_merge($parent->getComposedOf($recursive), $cofs);
            }
        }
        
        // collect non-primitive field of this class
        foreach($this->fields as $name => &$field) {
            if (!$field->isPrimitive() && $field->isComposedOf()) {
                $cofs[$name] = $field;
            }
        }
        
        return $cofs;
    }

    /**
     * Returns an array of relationship fields of a class
     * @param string $rclass the name of the related class
     * @return array (key'ed by 'class name : field name')
     * @access public
     */
    public function getFieldsOfClass($rclass) {
        
        // array to hold potential inverses
        $fields = array();
        
        // collect non-primitive field of this class
        foreach($this->fields as $name => $field) {
            if (!$field->isPrimitive() && $field->getClass() == $rclass) {
                $fields[$field->getClassMap()->getName(). ':' . $name] = $field;
            }
        }
        
        // return all fields
        return $fields;
    }

    /**
     * Determine whether the class map should be recompiled
     * by looking at the last compile timestamp and class file 
     * modification time
     * @return bool
     */
    public function needRecompile() {
        
        // check for special case
        if ($this->compile_time === false || !$this->class_file) {
            return false;
        }

        // check if file exists
        if (!file_exists($this->class_file)) {
            return false;
        }

        // get file mtime
        if (!($mt = filemtime($this->class_file))) {
            // if error occurs, force recompile
            return true;
        }

        // recompile if class compiled earlier than the modification
        return $this->compile_time < $mt;
    }

}

/**
 * Exception class for {@link epClassMapFactory}
 * 
 * @author Oak Nauhygon <ezpdo4php@gmail.com>
 * @version $Revision: 1.1 $ $Date: 2009/06/17 15:23:53 $
 * @package ezpdo
 * @subpackage ezpdo.base 
 */
class epExceptionClassMapFactory extends epException {
}

/**
 * The factory class of ezpdo class mapping info. 
 * 
 * @author Oak Nauhygon <ezpdo4php@gmail.com>
 * @version $Revision: 1.1 $ $Date: 2009/06/17 15:23:53 $
 * @package ezpdo
 * @subpackage ezpdo.orm
 */
class epClassMapFactory implements epFactory, epSingleton, epValidateable {

    /**#@+
     * Used for return value to avoid reference notice in 5.0.x and up
     * @var bool
     */
    static public $false = false;
    static public $true = true;
    static public $null = null;
    /**#@-*/
    
    /**
     * class_maps created
     * @var array
     */
    private $class_maps = array();
    
    /**
     * Constructor
     */
    private function __construct() {
    }
    
    /**
     * Implements factory method {@link epFactory::make()}
     * @param string $class_name
     * @return epClassMap|null
     * @access public
     * @static
     */
    public function &make($class_name) {
        return $this->get($class_name, false); // false: no tracking
    }

    /**
     * Implement factory method {@link epFactory::track()}
     * @param string $class_name
     * @return epClassMap
     * @access public
     */
    public function &track() {
        $args = func_get_args();
        return $this->get($args[0], true); // true: tracking
    }
    
    /**
     * Either create a class map (if not tracking) or retrieve it from cache 
     * @param $class_name
     * @param bool tracking or not
     * @return epClassMap
     * @throws epExceptionClassMapFactory
     */
    private function & get($class_name, $tracking = false) {
        
        // check class name
        if (empty($class_name)) {
            throw new epExceptionClassMapFactory('Class name is empty');
            return self::$null;
        }
        
        // check if class map has been created
        if (isset($this->class_maps[$class_name])) {
            return $this->class_maps[$class_name];
        }
        
        // check if it's in tracking mode
        if ($tracking) {
            return self::$null;
        }
        
        // otherwise create
        $this->class_maps[$class_name] = new epClassMap($class_name);
        
        return $this->class_maps[$class_name]; 
    }
    
    /**
     * Implement factory method {@link epFactory::allMade()}
     * Return all class_maps made by factory
     * @return array
     * @access public
     */
    public function allMade() {
        return array_values($this->class_maps);
    }
    
    /**
     * Implement factory method {@link epFactory::removeAll()}
     * Remove all class_maps made 
     * @return void
     */
    public function removeAll() {
         $this->class_maps = array();
    }
    
    /**
     * Sort class maps by key (only for testing)
     * @return void
     * @access public
     */
    public function sort() {
        ksort($this->class_maps);
    }

    /**
     * Check if the class maps are valid
     * 
     * Implements the {@link epValidateable} interface
     * 
     * @param bool $recursive (unused)
     * @return true|string (error msg)
     */
    public function isValid($recursive) {
        
        // error messages 
        $errors = array();

        // check if the classes of the relational fields exists
        // fix bug #54 (http://www.ezpdo.net/bugs/index.php?do=details&id=54)
        if (true !== ($errors_ = $this->_validateRelationshipFields())) {
            $errors = array_merge($errors, $errors_);
        }

        // either return array of errors or true
        return $errors ? $errors : true;
    }
    
    /**
     * Validate that relation fields have their related classes compiled.
     * @return true|array of strings (error msgs)
     */
    protected function _validateRelationshipFields() {
        
        // array to keep errors
        $errors = array();

        // loop through the class maps
        foreach($this->class_maps as $class => $cm) {
            
            // get all non-primitive fields 
            // (false: non-recursive to avoid double checking)
            if (!($npfs = $cm->getNonPrimitive(false))) {
                continue;
            }

            // loop through relational field maps
            foreach($npfs as $fm) {
                // check the inverse of the field
                $errors = array_merge($errors, $this->_validateRelationshipField($fm, $class));
            }
        }

        // either return array of errors or true
        return $errors ? $errors : true;
    }

    /**
     * Validate related class and inverse on a field map 
     * @param epFieldMap $fm the field map to be checked 
     * @param string $class the name of the class that the field belongs to
     * @return array (errors)
     */
    protected function _validateRelationshipField(&$fm, $class) {
        
        // array to hold error messages 
        $errors = array();

        //
        // 1. check the opposite class for the field
        // 

        // string for class and field 
        $class_field = '[' . $class . '::' . $fm->getName() . ']';

        // does the relation field have the related class defined?
        if (!($rclass = $fm->getClass())) {
            // shouldn't happend
            $errors[] = $class_field . ' does not have opposite class specified';
            return $errors;
        }

        // does the related class exist?
        if (!isset($this->class_maps[$rclass])) {
            // alert if not
            $errors[] = 'Class [' . $rclass . '] for ' . $class_field . ' does not exist';
            return $errors;
        }

        //
        // 2. check inverse of the field
        // 

        // does this field have an inverse?
        if (!($inverse = $fm->getInverse())) {
            return $errors;
        }
        
        // get the related class map
        $rcm = $this->class_maps[$rclass];

        // get all fields point to the current class in the related class
        $rfields = $rcm->getFieldsOfClass($class);

        // 2.a. default inverse (that is, set to true)
        if (true === $inverse) {

            // the related class must have only one relationship var to the current class
            if (!$rfields) { 
                $errors[] = 'No inverse found for ' . $class_field;
            } 

            // more than one fields pointing to the current class
            else if (count($rfields) > 1) {
                $errors[] = 'Ambiguilty in the inverse of ' . $class_field;
            }
            
            // set up the inverses
            else {
                $rfms = array_values($rfields);
                $fm->setInverse($rfms[0]->getName());
                $rfms[0]->setInverse($fm->getName());
            }

            return $errors;
        } 

        // 2.b. inverse is specified

        // check if inverse exists
        if (!isset($rfields[$fm->getClass().':'.$inverse]) || !$rfields[$fm->getClass().':'.$inverse]) {
            $errors[] = 'Inverse of ' . $class_field . ' (' . $fm->getClass() . '::' . $inverse . ') does not exist';
            return $errors;
        }

        // get the field map for the inverse
        $rfm = $rfields[$fm->getClass().':'.$inverse];

        // set up the inverse on the other side -only if- inverse on the other side 
        // is -not- already set or set to default
        if (!($rinverse = $rfm->getInverse()) || $rinverse === true) {
            $rfm->setInverse($fm->getName());
            return $errors;
        }

        // if specified, check duality
        if ($class != $rfm->getClass() || $rinverse != $fm->getName()) {
            $errors[] = 'Inverse of [' . $rcm->getName() . '::' . $fm->getName() . '] is not specified as ' . $class_field;
        }

        return $errors;
    }

    /**
     * Switches DSN at runtime.
     * 
     * Working assumption the class hierarchy of the input classes 
     * of which you want to change DSN should be in one database 
     * (i.e. one DSN) as well as their relationship tables. 
     * 
     * If no class name is specified, all compiled classes will 
     * change their DSN to the new one. 
     * 
     * @param string $dsn The targeted DSN
     * @param array $classes The classes to change to the target DSNs
     * @return true
     */
    public function setDsn($dsn, $classes = false) {

        // make sure non-empty dsn
        if (!$dsn) {
            return false;
        }

        // if no class is specified
        if (!$classes) {
            foreach($this->class_maps as $class => &$cm) {
                $cm->setDsn($dsn);
            }
            return true;
        }

        // array to keep track of classes included
        $classes_done = array();
        
        // go through each class
        foreach($classes as $class) {
            
            // true: tracking only, no creation
            if (!($cm = & $this->get($class, true))) {
                continue;
            }

            // add this class if not already in array
            if (!in_array($cm->getName(), $classes_done)) {
                $cm->setDsn($dsn);
            }

            // get all its children
            if (!($children = $cm->getChildren())) {
                continue;
            }

            // include all its children
            foreach($children as &$child) {

                // add this class if not already in array
                if (!in_array($child->getName(), $classes_done)) {
                    $child->setDsn($dsn);
                }
            }
        }

        return true;
    }

    /**
     * Returns all relation fields that involves the given class
     * @param string $class
     * @return array
     */
    public function getRelationFields($class) {
        
        $fields = array();

        // get the class map and loop thru all ancestors
        $cm = $this->get($class);
        while ($cm) {
            
            // loop through the class maps
            foreach($this->class_maps as $class => $cm_) {
                if ($fields_ = $cm_->getFieldsOfClass($cm->getName())) {
                    $fields = array_merge($fields, $fields_);
                }
            }

            // get all non primitive fields of this class
            // not recursive as we already are doing recursion
            if ($fields_ = $cm->getNonPrimitive(false)) {
                $fields = array_merge($fields, $fields_);
            }

            // get parent of the current class
            $cm = $cm->getParent();
        }

        return $fields;
    }
    
    /**
     * Serialize the singleton factory
     * @param bool $sort whether to sort class maps by name before serialization
     * @return false|string
     */
    static public function serialize($sort = true) {
        
        // get the class map factory
        $cmf = & epClassMapFactory::instance();
        
        // need to sort the class maps
        if ($sort) {
            $cmf->sort();
        }
        
        // serialize the factory
        return serialize($cmf);
    }
    
    /**
     * Unserialize the singleton factory
     * Make instance() consistent
     * @param string serialized data
     * @return null|epClassMapFactory
     */
    static public function &unserialize($scmf) {
        
        // sanity check
        if (!$scmf) {
            return self::$null;
        }
        
        // serialize 
        if (!($cmf = unserialize($scmf))) {
            return self::$null;
        }
        
        self::$instance = & $cmf;
        
        return self::$instance;
    }
    
    /**
     * Implements {@link epSingleton} interface
     * @return epClassMapFactory
     * @access public
     */
    static public function &instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    /**
     * Implement {@link epSingleton} interface
     * Forcefully destroy old instance (only used for tests). 
     * After reset(), {@link instance()} returns a new instance.
     */
    static public function destroy() {
        self::$instance = null;
    }

    /**
     * epClassMapFactory instance
     */
    static private $instance; 
} 

?>
