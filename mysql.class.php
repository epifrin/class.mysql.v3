<?php
/**  
 * PHP class for work with MySQL database
 * Work with library mysql and mysqli
 * For PHP 5
 * 
 * @version: 1.0
 * @update: 01.08.2012
 * @
 * @author: Yuriy Panas http://www.panas.net.ua
 */

abstract class MySqlAbstract{
    protected $database;
    protected $lastQuery = '';
    protected $charset = '';
    public $mailForError = '';
    public $functionErrorName = '';
    
    public abstract function sql($sql);
    
    public function query($sql){
        return $this->sql($sql);
    }
    
    public abstract function getarray($sql);
    
    public function ga($sql){
        return $this->getarray($sql);
    }
    
    public abstract function getmultiarray($sql);
    
    public function gma($sql){
        return $this->getmultiarray($sql);
    }
    
    public abstract function getvalue($sql);
    
    public function gv($sql){
        return $this->getvalue($sql);
    }
    
    public abstract function getverticalarray($sql);
    
    public function gva($sql){
        return $this->getverticalarray($sql);
    }
    
    public abstract function getindexmultiarray($sql);
    
    public function gima($sql){
        return $this->getindexmultiarray($sql);
    }
    
    /**
    * return text of last SQL select
    * 
    */
    public function getLastQuery(){
        return $this->lastQuery;
    }
    
    /* Validators */
    
    public abstract function check_sql($text);
    
    /**
    * incoming text is processed using functions: stripslashes, mysql_real_escape_string, htmlspecialchars
    * 
    * @param string $text
    * @return string
    */
    public function check_text($text){
        $text = str_replace('`','',$text);
        $text = htmlspecialchars(trim($this->check_sql($text)), ENT_NOQUOTES, $this->charset);
        return $text;
    }
    
    /**
    * check date format
    * 
    * @param string $date
    * @return bool
    */
    public function check_date($date){
        if( preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d)$/",$date,$res)){
            return checkdate($res[2],$res[3],$res[1]);
        }else{ return false; };
    }
    
    /**
    * check time format
    * 
    * @param string $time
    * @return bool
    */
    public function check_time($time){
        if( preg_match("/^([0-2]\d):[0-5]\d:[0-5]\d$/",$time,$res)){
            if($res[1] < 24) return true; else return false;
        }else{ return false; };
    }
    
    /**
    * check datetime format
    * 
    * @param string $datetime
    * @return bool
    */
    public function check_datetime($datetime){
        if($this->check_date(substr($datetime,0,10)) && $this->check_time(substr($datetime,11))){
            return true;
        }else{
            return false;
        }
    }
    
    /**
    * error handler
    * 
    * @param string $msg Mysql error text
    * @param string $sql SQL query
    * @param array $arrDebug Debug backtrace
    */
    protected function error($msg, $sql, $arrDebug){
        if(!empty($this->functionErrorName) && function_exists($this->functionErrorName)){
            // Call alternative error handler function
            $functionErrorName = $this->functionErrorName;
            $functionErrorName($msg, $sql, $arrDebug);
        
        }else{
            
            $errMsg = '<b>MySQL Error:</b><br>
                        SQL select: '.$sql.'<br> 
                        Error: '.$msg.'<br>';
            $errMsg .= 'Stack trace:<br>';
            foreach($arrDebug AS $debug){
                $errMsg .= 'File: <b>'.$debug['file'].'</b>, line: <b>'.$debug['line'].'</b><br>';
            }
            // send mail about error
            if(!empty($this->mailForError)){
                $headers .= "MIME-Version: 1.0;\r\n";
                $headers .= "Content-Type: text/html; charset=windows-1251\r\n";
                $headers .= "Content-Transfer-Encoding: base64\r\n";
                $headers .= "\r\n";
                @mail($this->mailForError, 'MySQL Error', chunk_split(base64_encode($errMsg)), $headers);
            }
            die($errMsg);
        }
    }
}

class MySqlILibrary extends MySqlAbstract {
    
    function __construct($dblocation, $dbname, $dbuser, $dbpass, $charset = ''){
        $dbcon = mysqli_connect($dblocation, $dbuser, $dbpass, $dbname);
        if ($dbcon){
            $this->database = $dbcon;
            if($charset){
                $this->charset = $charset;
                $this->sql('SET character_set_client="'.$this->charset.'"'); 
                $this->sql('SET character_set_results="'.$this->charset.'"'); 
                $this->sql('SET character_set_connection="'.$this->charset.'"');
                $this->sql('SET collation_connection="'.$this->charset.'_general_ci"');
            }

        }else{
            trigger_error('Unable to connect to database', E_USER_ERROR);
            die();
        }
    }
    
    function __desctruct(){
        mysqli_close($this->database);
        return true;
    }
    
    public function sql($sql){
        $this->lastQuery = $sql;
        $query = mysqli_query($this->database, $sql);
        if ($query){
            if(strtoupper(substr($sql,0,6)) == 'INSERT'){
                return mysqli_insert_id($this->database);
            }
            return $query;
        }else{
             $this->error(mysqli_error($this->database),$sql, debug_backtrace());
             return false;
        }
    }
    
    /**
    * return php array first record of table
    * 
    * @param string $sql SQL SELECT
    * @return assoc array
    */
    public function getarray($sql){
        $this->lastQuery = $sql; 
        $query = mysqli_query($this->database, $sql);
        if (!$query) $this->error(mysqli_error($this->database), $sql, debug_backtrace());
        return mysqli_fetch_assoc($query);
    }
    
    /**
    * return multiple array records of table
    * 
    * @param string $sql SQL select
    */
    public function getmultiarray($sql){
        $this->lastQuery = $sql; 
        $array = array();
        $query = mysqli_query($this->database, $sql);
        if (!$query) $this->error(mysqli_error($this->database), $sql, debug_backtrace());
        while($arr = mysqli_fetch_assoc($query)){
            $array[] = $arr;
        }
        return $array;
    }
    
    /**
    * return value of first field of first record
    * 
    * @param string $sql
    */
    public function getvalue($sql){
        $this->lastQuery = $sql; 
        $value = '';
        $query = mysqli_query($this->database, $sql);
        if (!$query) $this->error(mysqli_error($this->database), $sql, debug_backtrace());
        list($value) = mysqli_fetch_row($query);
        return $value;
    }
    
    /**
    * return array of values first field
    * 
    * @param string $sql
    */
    public function getverticalarray($sql){
        $this->lastQuery = $sql; 
        $array = array();
        $query = mysqli_query($this->database, $sql);
        if (!$query) $this->error(mysqli_error($this->database), $sql, debug_backtrace());
        while($arr = mysqli_fetch_row($query)){
            $array[] = $arr[0];
        }
        return $array;
    }
    
    public function getindexmultiarray($sql){
        $this->lastQuery = $sql; 
        $array = array();
        $query = mysqli_query($this->database, $sql);
        if (!$query) $this->error(mysqli_error($this->database), $sql, debug_backtrace());
        while($arr = mysqli_fetch_assoc($query)){  
            $index = reset($arr); 
            $array[$index] = $arr;
        }
        return $array;
    }
    
    /**
    * incoming text is processed using functions: stripslashes, mysqli_real_escape_string
    * 
    * @param string $text
    * @return string
    */
    public function check_sql($text){
        $text = stripslashes($text);
        $text = mysqli_real_escape_string($this->database, $text);
        return $text;
    }
}

class MySqlLibrary extends MySqlAbstract {
    
    function __construct($dblocation, $dbname, $dbuser, $dbpass, $charset = ''){
        $dbcon = mysql_connect($dblocation, $dbuser, $dbpass);
        if ($dbcon){
            $this->database = $dbcon; 
            mysql_select_db($dbname, $this->database); 
            if($charset){
                $this->charset = $charset;
                $this->sql('SET character_set_client="'.$this->charset.'"'); 
                $this->sql('SET character_set_results="'.$this->charset.'"'); 
                $this->sql('SET character_set_connection="'.$this->charset.'"');
                $this->sql('SET collation_connection="'.$this->charset.'_general_ci"');
            }
        }else{
            trigger_error('Unable to connect to database', E_USER_ERROR);
            die();
        }
    }
    
    function __desctruct(){
        mysql_close($this->database);
        return true;
    }
    
    function sql($sql){
        $this->lastQuery = $sql;
        $query = mysql_query($sql,$this->database);
        if ($query){
            if(strtoupper(substr($sql,0,6)) == 'INSERT'){
                return mysql_insert_id($this->database);
            }
            return $query;
        }else{
             $this->error(mysql_error(), $sql, debug_backtrace());
             return false;
        }
    }
    
    /**
    * return php array first record of table
    * 
    * @param string $sql SQL select
    * @return assoc array
    */
    function getarray($sql){
        $this->lastQuery = $sql; 
        $query = mysql_query($sql, $this->database);
        if (!$query) $this->error(mysql_error(), $sql, debug_backtrace());
        return mysql_fetch_assoc($query);
    }
    
    /**
    * return multiple array records of table
    * 
    * @param string $sql SQL select
    */
    function getmultiarray($sql){
        $this->lastQuery = $sql; 
        $array = array();
        $query = mysql_query($sql,$this->database);
        if (!$query) $this->error(mysql_error(), $sql, debug_backtrace());
        while($arr = mysql_fetch_assoc($query)){
            $array[] = $arr;
        }
        return $array;
    }
    
    /**
    * return value of first field of first record
    * 
    * @param string $sql
    */
    function getvalue($sql){
        $this->lastQuery = $sql; 
        $value = '';
        $query = mysql_query($sql,$this->database);
        if (!$query) $this->error(mysql_error(), $sql, debug_backtrace());
        list($value) = mysql_fetch_row($query);
        return $value;
    }
    
    /**
    * return array of values first field
    * 
    * @param string $sql
    */
    function getverticalarray($sql){
        $this->lastQuery = $sql; 
        $array = array();
        $query = mysql_query($sql,$this->database);
        if (!$query) $this->error(mysql_error(), $sql, debug_backtrace());
        while($arr = mysql_fetch_row($query)){
            $array[] = $arr[0];
        }
        return $array;
    }
    
    public function getindexmultiarray($sql){
        $this->lastQuery = $sql; 
        $array = array();
        $query = mysql_query($sql, $this->database);
        if (!$query) $this->db_error(mysql_error(), $sql, debug_backtrace()); 
        while($arr = mysql_fetch_assoc($query)){  
            $index = reset($arr); 
            $array[$index] = $arr;
        }
        return $array;
    }
    
    /**
    * incoming text is processed using functions: stripslashes, mysql_real_escape_string
    * 
    * @param string $text
    * @return string
    */
    function check_sql($text){
        $text = stripslashes($text);
        $text = mysql_real_escape_string($text, $this->database);
        return $text;
    }
}

class MySqlClass {
    
    private function __construct(){}
    
    /**
    * connect to database
    * 
    * @param string $dblocation
    * @param string $dbname
    * @param string $dbuser
    * @param string $dbpass
    * @param string $charset
    * @return object
    */
    public function connect($dblocation, $dbname, $dbuser, $dbpass, $charset = ''){
        if(function_exists('mysqli_query')){
            return new MySqlILibrary($dblocation, $dbname, $dbuser, $dbpass, $charset);
        }else{
            return new MySqlLibrary($dblocation, $dbname, $dbuser, $dbpass, $charset);
        }
    }
}

?>