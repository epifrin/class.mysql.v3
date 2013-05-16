<?php
/**  
 *    ����� MySQLi ��� ������ � ����� ������ MySQL
 *    Для PHP5
 *    @version: 3.1.3
 *    @update: 01.08.2012
 *    @author: Yuriy Panas http://www.panas.net.ua
 */

class MySQLQuery3{
    var $database;
    var $last_sql = '';
    var $charset = '';

    // �������������� ����������
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
            echo "<p>���� ������ ����������, ������ ����� ����������!!!</p>";
            exit();
        }
    }
    // ������� ����������
    function __desctruct(){
        mysqli_close($this->database);
        return true;
    }
    
    // ��������� ������������ SQL-������
    function sql($sql){
        $this->last_sql = $sql;
        $query = mysqli_query($this->database, $sql);
        if ($query){
            if(strtoupper(substr($sql,0,6)) == 'INSERT'){
                return mysqli_insert_id($this->database);
            }
            return $query;
        }else{
             $this->db_error(mysqli_error($this->database),$sql, debug_backtrace());
             return false;
        }
    }
    
    //���������� ������ ������� � ���� ����������� �������
    function getarray($sql){
        $this->last_sql = $sql; 
        $query = mysqli_query($this->database, $sql);
        if (!$query) $this->db_error(mysqli_error($this->database),$sql, debug_backtrace());
        return mysqli_fetch_assoc($query);
    }
    // ����� �� getarray
    function ga($sql){
        return $this->getarray($sql);
    }
    
    //���������� ������ ������� � ���� ���������� �������
    function getmultiarray($sql){
        $this->last_sql = $sql; 
        $array = array();
        $query = mysqli_query($this->database, $sql);
        if (!$query) $this->db_error(mysqli_error($this->database),$sql, debug_backtrace());
        while($arr = mysqli_fetch_assoc($query)){
            $array[] = $arr;
        }
        return $array;
    }
    // ����� �� getmultiarray
    function gma($sql){
        return $this->getmultiarray($sql);
    }
    
    //�������� �������� ������ ����
    function getvalue($sql){
        $this->last_sql = $sql; 
        $value = '';
        $query = mysqli_query($this->database, $sql);
        if (!$query) $this->db_error(mysqli_error($this->database),$sql, debug_backtrace());
        list($value) = mysqli_fetch_row($query);
        return $value;
    }
    // ����� �� getvalue
    function gv($sql){
        return $this->getvalue($sql);
    }
    
    //���������� ������ ������ ������� ������� � ���� ����������� �������
    function getverticalarray($sql){
        $this->last_sql = $sql; 
        $array = array();
        $query = mysqli_query($this->database, $sql);
        if (!$query) $this->db_error(mysqli_error($this->database),$sql, debug_backtrace());
        while($arr = mysqli_fetch_row($query)){
            $array[] = $arr[0];
        }
        return $array;
    }
    // ����� �� getverticalarray
    function gva($sql){
        return $this->getverticalarray($sql);
    }
    
    // �������� ��������� ������ � �������� ������ ���� ���������� �������
    function getindexmultiarray($sql){
        $this->last_sql = $sql; 
        $array = array();
        $query = mysqli_query($this->database, $sql);
        if (!$query) $this->db_error(mysqli_error($this->database),$sql, debug_backtrace());
        while($arr = mysqli_fetch_assoc($query)){  
            $index = reset($arr); 
            $array[$index] = $arr;
        }
        return $array;
    }
    // ����� �� getverticalarray
    function gima($sql){
        return $this->getindexmultiarray($sql);
    }
    
    // �������� ����� ��������� SQL-������
    function get_lastsql(){
        return $this->last_sql;
    }
    
    // ��������
    function check_sql($text){
        $text = stripslashes($text);
        $text = mysqli_real_escape_string($this->database, $text);
        return $text;
    }

    function check_text($text){
        $text = str_replace('`','',$text);
        $text = htmlspecialchars(trim($this->check_sql($text)),ENT_NOQUOTES, $this->charset);
        return $text;
    }
    
    // �������� �������� ��� ���� date � mysql
    function check_date($date){
        if( preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d)$/",$date,$res)){
            return checkdate($res[2],$res[3],$res[1]);
        }else{ return false; };
    }
    
    // �������� �������� ��� ���� time � mysql
    function check_time($time){
        if( preg_match("/^([0-2]\d):[0-5]\d:[0-5]\d$/",$time,$res)){
            if($res[1] < 24) return true; else return false;
        }else{ return false; };
    }
    
    // �������� �������� ��� ���� datetime � mysql
    function check_datetime($datetime){
        if($this->check_date(substr($datetime,0,10)) && $this->check_time(substr($datetime,11))){
             return true;
         }else{
            return false;
        }
    }
    
    ######################## ��������� ####################################
    
    /* ��������� ����������
        type = int, text, float, date, datetime, time
        maxlen = �����
        empty = yes
        pk = yes
    */
    var $err_text = '';
    var $arr_struct = '';
    var $curr_table = '';
    
    function create_struct_from_t($table){
        $this->arr_struct = '';
        $arr_t_struct = $this->getmultiarray('SHOW COLUMNS FROM '.$table);
        //print_r($arr_t_struct);
        foreach($arr_t_struct AS $struct){

            $this->arr_struct[$struct['Field']]['if_no_param'] = 'asis';
            if($struct['Key'] == 'PRI') $this->arr_struct[$struct['Field']]['pk'] = 'yes'; 
             
            
            if(strpos($struct['Type'],'int') !== false){
                $this->arr_struct[$struct['Field']]['type'] = 'int';
            }elseif( (strpos($struct['Type'],'varchar') !== false) || (strpos($struct['Type'],'char') !== false)){
                $this->arr_struct[$struct['Field']]['type'] = 'text';
            }elseif( $struct['Type'] == 'text'){
                $this->arr_struct[$struct['Field']]['type'] = 'text';
            }elseif( (strpos($struct['Type'],'float') !== false) || (strpos($struct['Type'],'double') !== false) || (strpos($struct['Type'],'decimal') !== false)){    
                $this->arr_struct[$struct['Field']]['type'] = 'float';
            }elseif( $struct['Type'] == 'date'){
                $this->arr_struct[$struct['Field']]['type'] = 'date';
                $this->arr_struct[$struct['Field']]['if_no_param'] = 'insert_default'; 
            }elseif( $struct['Type'] == 'datetime'){
                $this->arr_struct[$struct['Field']]['type'] = 'datetime';
                $this->arr_struct[$struct['Field']]['if_no_param'] = 'insert_default'; 
            }elseif( $struct['Type'] == 'time'){
                $this->arr_struct[$struct['Field']]['type'] = 'time';
                $this->arr_struct[$struct['Field']]['if_no_param'] = 'insert_default'; 
            }
            
            if(!empty($struct['Default'])){ 
                $this->arr_struct[$struct['Field']]['if_no_param'] = 'insert_default';
                $this->arr_struct[$struct['Field']]['default'] = $struct['Default'];
            }
            if($struct['Default'] === '0'){
                $this->arr_struct[$struct['Field']]['if_no_param'] = 'insert_default';
                $this->arr_struct[$struct['Field']]['default'] = '0';
            }
            
            if($struct['Null'] == 'YES') $this->arr_struct[$struct['Field']]['empty'] = 'yes'; 
            //$this->arr_struct[$struct['Field']]['if_no_param'] = 'asis';
            //$this->arr_struct[$struct['Field']]['if_no_param'] = 'insert_default';
            //$this->arr_struct[$struct['Field']]['if_no_param'] = 'default';
        }
        return $this->arr_struct;
    }
    
    // �������� ���������� �� ���������
    function check_params($arr_params,$table=''){
        if($table && !$this->arr_struct){
            $this->create_struct_from_t($table);
        }
        if($this->arr_struct){
            $this->err_text = '';
            foreach($this->arr_struct AS $field => $struct){ //������ �� ���������
                if(isset($arr_params[$field])){  // �������� ����
                    if(!empty($arr_params[$field])){

                        if(($arr_params[$field] == 'NULL') && !empty($struct['empty'])){
                            //
                        }elseif($struct['type'] == 'int'){
                            if(is_numeric($arr_params[$field]) && (int)$arr_params[$field]){
                                $arr_params[$field] = (int)$arr_params[$field];
                            }else{
                                $this->err_text = '������������ �������� � ��������� "'.$field.'"<br>';
                            }
                        }elseif($struct['type'] == 'text'){
                            $arr_params[$field] = $this->check_text($arr_params[$field]);
                        }elseif(($struct['type'] == 'float') || ($struct['type'] == 'numeric')){
                            if(is_numeric($arr_params[$field]) && (float)$arr_params[$field]){
                                $arr_params[$field] = (float)$arr_params[$field];
                            }else{
                                $this->err_text = '������������ �������� � ��������� "'.$field.'"<br>';
                            }
                        }elseif($struct['type'] == 'date'){
                            if(!$this->check_date($arr_params[$field])){
                                $this->err_text = '������������ �������� � ��������� "'.$field.'"<br>';
                            }
                        }elseif($struct['type'] == 'time'){
                            if(!$this->check_time($arr_params[$field])){
                                $this->err_text = '������������ �������� � ��������� "'.$field.'"<br>';
                            }
                          }elseif($struct['type'] == 'datetime'){
                            if(!$this->check_datetime($arr_params[$field])){
                                $this->err_text = '������������ �������� � ��������� "'.$field.'"<br>';
                            }
                        }else{
                            // ����������� ���
                        }
                    }elseif(empty($struct['empty']) && empty($struct['pk'])){
                        $this->err_text = '�� ������ ��������� ��������� "'.$field.'"<br>';
                    }
                
                }elseif(empty($struct['empty']) && empty($struct['pk'])){
                    if($struct['if_no_param'] == 'insert_default' || $struct['if_no_param'] == 'default'){
                        if(!isset($struct['default']) && $struct['type'] != 'date' && $struct['type'] != 'datetime' && $struct['type'] != 'time' ) $this->err_text = '��� ��������� "'.$field.'" �� ������ �������� ��-���������<br>'; 
                    }else{
                        $this->err_text = '��� ��������� "'.$field.'"<br>';
                    }
                }
            }
            if($this->err_text){ 
                return false;
            }else{    
                return $arr_params; 
            }
        }else{
            return false;
        }
    }

    // ������ ����� ������ � �������
    // !!������ ������ ���� �������������� ���������!!
    function insert($arr_params,$table){
        if(!$this->arr_struct){
            $this->create_struct_from_t($table);
        }
        // ���� ��������� ������
        if($this->arr_struct){
            if($arr_params){
                $sql = 'INSERT INTO '.$table;
                $ins_field = ''; $ins_val = '';
                foreach($this->arr_struct AS $field => $struct){
                    if( !empty($arr_params[$field]) ){
                        $ins_field .= $field.',';
                        if(($struct['type'] == 'int') || ($struct['type'] == 'float') || ($arr_params[$field] == 'NULL')){
                            $ins_val .= $arr_params[$field].',';
                        }else{
                            $ins_val .= '"'.$arr_params[$field].'",';
                        }
                    }elseif( empty($struct['pk']) ){
                        if($struct['if_no_param'] == 'insert_default' || $struct['if_no_param'] == 'default'){
                            if(isset($struct['default'])){
                                $ins_field .= $field.',';
                                $ins_val .= '"'.$struct['default'].'",';
                            }else{
                                $ins_field .= $field.',';
                                if($struct['type'] == 'date') $ins_val .= '"'.date('Y-m-d').'",';
                                if($struct['type'] == 'datetime') $ins_val .= '"'.date('Y-m-d H:i:s').'",';
                                if($struct['type'] == 'time') $ins_val .= '"'.date('H:i:s').'",';
                            }
                        } 
                    }
                }
                $ins_field = substr($ins_field,0, -1);
                $ins_val = substr($ins_val,0, -1);
                $sql .= ' ('.$ins_field.') VALUES ('.$ins_val.')';
                //echo $sql;
                return $this->sql($sql);
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /** ���������� ������ �������
    * 
    * @param array $arr_params
    * @param string $table
    * @param string $where_field  // ����, ��� ������� WHERE 
    * @return bool
    */
    function update($arr_params, $table, $where_field){
        if(!$this->arr_struct){
            $this->create_struct_from_t($table);
        }
        // ���� ��������� ����������
        if($this->arr_struct){
            if($arr_params){
                $sql = 'UPDATE '.$table.' SET ';
                foreach($this->arr_struct AS $field => $struct){
                    if(empty($struct['pk']) && ( empty($struct['empty']) || isset($arr_params[$field]))){  
                        if( isset($arr_params[$field]) && (($struct['type'] == 'int') || ($struct['type'] == 'float'))){
                            $sql .= $field.' = '.$arr_params[$field].',';  
                        }elseif($arr_params[$field] == 'NULL'){
                            $sql .= $field.' = NULL,';
                        }elseif(empty($arr_params[$field]) && isset($struct['empty'])){
                            $sql .= $field.' = NULL,';
                        }elseif( empty($arr_params[$field]) && ( ($struct['if_no_param'] == 'insert_default' || $struct['if_no_param'] == 'default') && isset($struct['default']))){
                            $sql .= $field.' = '.$struct['default'].',';
                        }else{
                            $sql .= $field.' = "'.$arr_params[$field].'",';
                        }
                    }
                }
                $sql = substr($sql,0, -1);
                // ������� WHERE
                if($where_field && !empty($arr_params[$where_field]) && isset($this->arr_struct[$where_field])){
                    if(($this->arr_struct[$where_field]['type'] == 'int') || ($this->arr_struct[$where_field]['type'] == 'float')){
                        $sql .= ' WHERE '.$where_field.' = '.$arr_params[$where_field].'';
                    }else{
                        $sql .= ' WHERE '.$where_field.' = "'.$arr_params[$where_field].'"';
                    }
                    //echo $sql;
                    return $this->sql($sql);
                }else{
                    // ���������� ��������� ����
                }
            }else{
                return false;
            }
         }else{
            return false;
        }
    }

    // �������� ��������� ������� ��� php-����
    function get_struct_for_php(){
        $str_struct = false;
        if($this->arr_struct){
            $str_struct = 'array(';
            foreach($this->arr_struct AS $field => $struct){
                $str_struct .= "'".$field."'=>array('type'=>'".$struct['type']."'";
                if(isset($struct['empty'])) $str_struct .= ", 'empty'=>'yes'";
                if(isset($struct['pk'])) $str_struct .= ", 'pk'=>'yes'";
                $str_struct .= "),\n";
            }
            $str_struct = substr($str_struct, 0, -2).");";
        }
        return '<pre>'.$str_struct.'</pre>';
    }

    function set_struct($arr_struct){
        $this->arr_struct = '';
    }

    // �������� ����� ������ ��������
    function get_error(){
        return $this->err_text;
    }

    function gen_edit_tmp($arr_param,$input_class=''){
        if($this->arr_struct){
            $tmp = "<form action=\"\">\n<table>\n";
            foreach($this->arr_struct AS $field => $struct){
                  $tmp .= "<tr>\n\t<td></td>\n\t<td>";
                  $tmp .= '<input type="text" name="'.$field.'" value="{$'.$arr_param.'.'.$field.'}"';
                  if ($input_class) $tmp .= ' class="'.$input_class.'"';
                  $tmp .= "></td>\n</tr>\n";
              }
              $tmp .= "<tr>\n\t<td></td><td><input type=\"submit\" value=\"\"></td>\n</tr>\n";
              $tmp .= "</table>\n</form>";
              return '<pre>'.htmlspecialchars($tmp).'</pre>';
         }
    }
    
    //  gen_show_tmp('','','th1','td1');
    function gen_show_tmp($arr_from,$item,$th_class='',$td_class=''){
        if($this->arr_struct){
            $tmp = "<table>\n<tr>\n";
            foreach($this->arr_struct AS $field => $struct){
                  $tmp .= "\t<th";
                  if ($th_class) $tmp .= ' class="'.$th_class.'"';
                  $tmp .= "></th>\n";
              }
            $tmp .= "</tr>\n".'{foreach item='.$item.' from=$'.$arr_from."}\n<tr>\n";
            foreach($this->arr_struct AS $field => $struct){
                  $tmp .= "\t<td";
                  if ($td_class) $tmp .= ' class="'.$td_class.'"';
                  $tmp .= '>{$'.$item.'.'.$field.'}</td>'."\n";
              }
              $tmp .= "</tr>\n{/foreach}\n";
              $tmp .= '</table>';
              return '<pre>'.htmlspecialchars($tmp).'</pre>';
         }
    }
    
    // ��������� ������
    private function db_error($msg,$text_sql, $arr_debug){

        $err_msg = '������: '.$text_sql.'<br> ������: '.$msg.' <br>';
        $err_msg .= '������� ���������� ��������:<br>';
        foreach($arr_debug AS $debug){
            $err_msg .= '������: '.$debug['file'].', ������: '.$debug['line'].'<br>';
        }
        echo str_replace("\n",'<br>',$err_msg);
        /*$query = mysqli_query("SELECT value FROM settings WHERE var='admin_mail'");
        $mail_admin = @mysqli_result($query, 0, 'value');
        if (!empty($mail_admin)){
            if (!mail($mail_admin, "site: ������ MySQL", $err_msg, "Content-Type: text/plain; charset=windows-1251\r\nFrom: mysql@domain.com\r\n" . "Reply-To: mysql@domain.com\r\n" . "X-Mailer: PHP/" . phpversion()))
            {
                echo $err_msg;
            }
        }//*/
        exit("�������� ������ ��� ������ � ����� ������! ���������� � �������������� �����.");
    }
}

?>