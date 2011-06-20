<?php

class MysqlDB {
  // Instance of the class
  protected static $__instance = null;
  
  private $mysql;
  private $current_table;
  private $type;
  private $query;
  private $limit;
  private $where = array();
  private $paramTypeList;
  private $insertData;
  private $join = null;
  private $fields = '*';
  
  public  $last_row = null;
  
  private function __construct($host, $user, $pass, $name) {
    $this->mysql = new mysqli($host, $user, $pass, $name);
  }
  
  public static function connect($host, $user, $pass, $name) {
    if( self::$__instance === null ) {
      // Initialize a new mysql connection
      $c = __CLASS__;
      self::$__instance = new $c($host, $user, $pass, $name);
      
      return self::$__instance;
    }
  }
  
  public static function instance() {
    // The user needs to run MysqlWrapper::connect();
    if( self::$__instance === null )
      trigger_error('Please connect to a database using <code>' . __CLASS__ . '::connect($host, $user, $pass, $name);</code>', E_USER_ERROR);
    
    return self::$__instance;
  }
  
  protected function reset() {
    $this->where = array();
    $this->paramTypeList = '';
    $this->insertData = array();
  }
    
  protected function setDefaults($table, $limit) {
    $this->current_table = $table;
    $this->limit = (is_integer($limit)) ? $limit : 0;
  }
  
  public function get($table, $limit = 0) {
    $this->type = 'read';
    $this->setDefaults($table, $limit);
    $this->query = "SELECT {$this->fields} FROM $table";
    
    $stmt = $this->buildQuery();
    $stmt->execute();
    
    $this->reset();        
    return $this->bindResults($stmt);
  }
  
  public function where($col, $value) {
    $this->where[$col] = $value;
  }
  
  public function insert($table, array $content) {
    $this->type = 'insert';
    $this->setDefaults($table, 0);
    $this->insertData = $content;
    $this->query = "INSERT INTO $table";
    
    $stmt = $this->buildQuery();
    $stmt->execute();
                    
    if( $stmt->affected_rows ) {
      $this->reset();
      
      $this->where('id', $stmt->insert_id);
      $this->last_row = $this->get($table);
      
      return true;
    }
  }
  
  public function update($table, array $content) {
    if( count($this->where) == 0 )
      trigger_error('You must supply where when updating', E_USER_ERROR);
    
    $this->type = 'update';
    $this->setDefaults($table, 0);
    $this->insertData = $content;
    $this->query = "UPDATE $table SET";
    
    $stmt = $this->buildQuery();
    $stmt->execute();
    
    if( $stmt->affected_rows ) {
      $this->reset();
      return true;
    }    
  }
  
  public function delete($table) {
    if( count($this->where) == 0 )
      trigger_error('You must supply where when deleting', E_USER_ERROR);
      
    $this->type = 'delete';
    $this->setDefaults($table, 0);
    $this->query = "DELETE FROM $table";
    
    $stmt = $this->buildQuery();
    $stmt->execute();
    
    if( $stmt->affected_rows ) {
      $this->reset();
      return true;
    }
  }
  
  public function left_join($table, $conditions) {
    $this->join = " LEFT JOIN `$table` ON ($conditions)";
  }
  
  public function right_join($table, $conditions) {
    $this->join = " RIGHT JOIN `$table` ON ($conditions)";
  }
  
  public function select($fields) {
    $this->fields = $fields;
  }
  
  protected function buildQuery() {
    
    // Updating Row?
    if( $this->type == 'update' && count($this->insertData) ) {
      foreach( $this->insertData as $col => $value ) {
        $this->paramTypeList .= $this->getType($value);
        
        $this->query .= ' ' . $col . '= ?, ';
      }
      $this->query = rtrim($this->query, ', ');
    }
    
    // Check for where filters
    if( in_array($this->type, array('read', 'update', 'delete')) && count($this->where) ) {
      if( $this->join !== null )
        $this->query .= $this->join;
      
      $this->query .= " WHERE";
      
      // Store all the values found
      $values = array();
      foreach( $this->where as $col => $value ) {
        $this->paramTypeList .= $this->getType($value);
        $this->query .= ' ' . $col . '=? AND ';
        array_push($values, $value);
      }
      $this->query = rtrim($this->query, ' AND ');
    }
    
    // Inserting Row?
    if( $this->type == 'insert' && count($this->insertData) ) {
      $keys = array_keys($this->insertData);
      $vals = array_values($this->insertData);
      $num = count($keys);
      
      foreach( $vals as $key => $value ) {
        $this->paramTypeList .= $this->getType($value);
        $vals[$key] = "'$value'";
      }
      
      $this->query .= ' (' . join($keys, ', ') . ')';
      $this->query .= ' VALUES(';      
      while ($num !== 0) {
        ($num !== 1) ? $this->query .= '?, ' : $this->query .= '?)';
        $num--;
      }
    }
    
    // Check if we need to limit the rows
    if( $this->type == 'read' && $this->limit !== 0 ) {
      $this->query .= " LIMIT $this->limit";
    }
    
    $stmt = $this->prepareQuery();
    
    // Check if we're inserting (to bind params)
    if( in_array($this->type, array('insert', 'update')) && count($this->insertData) ) {
      $args = array();
      $args[] = $this->paramTypeList;
      foreach( $this->insertData as $prop => $val ) {
         $args[] = &$this->insertData[$prop];
      }
      
      // Add where filters
      if( count($this->where) ) {
        $args = array_merge($args, $values);
      }

      call_user_func_array(array($stmt, 'bind_param'), $this->refValues($args));
    }
    
    // Bind the parameters
    if( in_array($this->type, array('read', 'delete')) && count($this->where) ) {
      $values = array_merge(array($this->paramTypeList), $values);
      
      call_user_func_array(array($stmt, 'bind_param'), $this->refValues($values));
    }
                
    return $stmt;
  }
  
  protected function prepareQuery() {
    if( !$stmt = $this->mysql->prepare($this->query))
      trigger_error('Could not prepare query', E_USER_ERROR);
    return $stmt;
  }
  
  protected function bindResults($stmt) {
    $params = array();
    $results = array();
    
    $meta = $stmt->result_metadata();
    
    while( $field = $meta->fetch_field() ) {
      $params[] = &$row[$field->name];
    }
    
    call_user_func_array(array($stmt, 'bind_result'), $params);
    
    while( $stmt->fetch() ) {
      $x = array();
      foreach( $row as $key => $value ) {
        $x[$key] = $value;
      }
      $results[] = (object) $x;
    }
    
    return $results;
  }
  
  protected function getType($item) {
    switch (gettype($item)) {
      case 'string':
        return 's';
      break;

      case 'integer':
        return 'i';
      break;

      case 'blob':
        return 'b';
      break;

      case 'double':
        return 'd';
      break;
    }
  }
  
  /**
   * Turn an value array into a reference array
   * Requires PHP 5.3+
   *
   * @param array $arr 
   * @return reference version of array
   * @link http://www.php.net/manual/en/mysqli-stmt.bind-param.php#100879
   */
  protected function refValues($arr) {
    if (strnatcmp(phpversion(),'5.3') >= 0) {
      $refs = array();
      foreach($arr as $key => $value)
        $refs[$key] = &$arr[$key];
      return $refs;
    }
    return $arr;
  }
  
}