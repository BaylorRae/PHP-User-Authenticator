<?php

// Handles all password encryption
class UserCrypt {
  
  public static function prepare_password($password) {
    // salt
    $salt = sha1(join('', array(time(), rand())));
    
    // hash (includes the salt)
    $hash = self::encrypt_password($password, $salt);
    
    return array('salt' => $salt, 'hash' => $hash);
  }
  
  public static function encrypt_password($password, $salt) {
    return sha1(join('', array($password, $salt)));
  }
  
}

// Handles all user managing
class User {
  
  protected $user_data = array();
  protected $user_roles = null;
  public static $validiator_class = 'UserValidator';
  public static $errors = array();
  
  function __construct(array $info = null) {
    if( !empty($info) )
      $this->user_data = $info;
  }
  
  public function __get($name) {
    return (empty($this->user_data[$name])) ? null : $this->user_data[$name];
  }
  
  public function __set($name, $value) {
    $this->user_data[$name] = $value;
  }
  
  public static function __callStatic($func, $args) {
    if( preg_match('/^find_by_(\w+)/', $func, $matches) ) {
      $field = $matches[1];
      return self::find($field, $args[0]);
    }
  }
  
  public static function instance() {
    static $instance = null;
    if( $instance === null )
      $instance = new User;
    
    return $instance;
  }
  
  public function set_password($password) {
    // user salt, user password hash
    $data = UserCrypt::prepare_password($password);
    $this->password_hash = $data['hash'];
    $this->password_salt = $data['salt'];
  }
  
  public function save() {
    $return_value = false;
    
    // only allow specific fields
    $proper_fields = array('first_name', 'last_name', 'password', 'email_address', 'username');
    array_walk($this->user_data, function($value, $field) use($proper_fields) {
      if( !in_array($field, $proper_fields) )
        die('Oh no something went wrong. Please go back and try again.');
    });
    
    // Check if the validator class exists
    if( class_exists(User::$validiator_class) ) {
      $self = $this;
      $class = User::$validiator_class;
      $validators = get_class_vars($class);
      
      array_walk($validators, function($fields, $type) use($self) {
        $self->validate($type, $fields);
      });
      
      // had some errors
      if( count(User::$errors) )
        return false; // stop the function
      
    }
    
    $this->set_password($this->password);
    unset($this->user_data['password']);
        
    // Insert the row
    $mysql = MysqlDB::instance();
    $mysql->insert('users', $this->user_data);
    
    if( $row = $mysql->last_row ) {
      $this->user_data = (array) $row[0];
      $return_value = true;
    }
    
    return $return_value;
  }
  
  private static function find($field, $value) {
    $mysql = MysqlDB::instance();
    $return_value = false;
    
    $mysql->where($field, $value);
    $result = $mysql->get('users');
    
    if( !empty($result[0]) ) {
      $user = User::instance();
      $user->user_data = (array) $result[0];
      
      $return_value = $user;
    }
    
    return $return_value;
  }
  
  public static function authenticate($username, $password) {
    $return_value = false;
    
    if( ($user = User::find_by_username($username)) && // check if the username is found in the database
      
        // Make sure the user password matches the password_hash
        ($user->password_hash == UserCrypt::encrypt_password($password, $user->password_salt))
      ) {
      
      $return_value = $user;
            
    }else {
      self::add_error('invalid_login', 'username');
    }
    
    return $return_value;
  }

  public function validate($type, $fields) {
    $fields = (!is_array($fields)) ? array($fields) : $fields;
        
    foreach( $fields as $field ) {
      $field_value = $this->$field;
      
      switch ($type) {
        case 'uniqueness' :
        case 'uniqueness_of' :
          if( !empty($field_value) ) {
            $mysql = MysqlDB::instance();
            $mysql->where($field, $field_value);
            $rows = $mysql->get('users');

            if( count($rows) )
              User::add_error('uniqueness_of', $field);
          }
        break;
        
        case 'presence' :
        case 'presence_of' :
          if( empty($field_value) )
            User::add_error('presence_of', $field);
        break;
        
        default :
          if( is_callable(User::$validiator_class . '::validate') ) {
            $class = User::$validiator_class;
            $class::validate($type, $field, $field_value);
          }
        break;
      }
    }
  }
  
  public static function add_error($type, $field) {
    self::$errors[$type][$field] = true;
  }
  
  public function generate_login_hash() {
    $login_hash = UserCrypt::prepare_password($this->password_salt);
    $login_hash = $login_hash['salt'];
    
    $mysql = MysqlDB::instance();
    $mysql->where('id', $this->id);
    if( $mysql->update('users', array('login_hash' => $login_hash)) ) {
      return $login_hash;
    }else {
      // todo: show some type of error
    }
  }
  
  public function full_name() {
    return join(array($this->first_name, $this->last_name), ' ');
  }

  public function can($role_name) {
    if( $this->user_roles === null ) {
      $mysql = MysqlDB::instance();
      $mysql->select('roles.name');
      $mysql->left_join('roles', 'role_id = roles.id');
      $mysql->where('user_id', $this->id);
      
      $roles = $mysql->get('user_roles');
      
      foreach( $roles as $role ) {
        $this->user_roles[$role->name] = 1;
      }
    }
    
    return isset($this->user_roles[$role_name]);
  }
}