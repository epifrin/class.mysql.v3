mysql.class
===========

###PHP class for work with MySQL database


Connect to database and create object

    require_once '/path/to/mysql.class.php';
    $Query = MySqlClass::connect('localhost','dbname','dbuser','dbpass','utf8');

Simple sql query

    $Query->sql('INSERT INTO tablename VALUES ("value1","value2")');
    ->query() alias of sql()

Get an array of the first row of the table

    $array = $Query->getarray('SELECT * FROM tablename');
    ->ga() alias of getarray()

Get an multiple array of records of the table

    $array = $Query->getmultiarray('SELECT * FROM tablename');
    ->gma() is alias of getmultiarray()

Get a value of first field of first record

	$value = $Query->getvalue('SELECT * FROM tablename');
	->gv() is alias of getvalue()
	
Get an array of values of first column

    $array = $Query->getverticalarray('SELECT * FROM tablename');
    ->gva() is alias of getverticalarray()

Get index array with keys from values first field

    $array = $Query->getindexmultiarray('SELECT * FROM tablename');
    ->gima() is alias of getindexmultiarray()

Get text of last SQL query

    $sqltext = $Query->getLastQuery();

Additional functions

    //incoming text is processed using functions: stripslashes, mysql_real_escape_string
    $Query->check_sql($string)
    
    //incoming text is processed using functions: stripslashes, mysql_real_escape_string, htmlspecialchars
    $Query->check_text($string)
    
    Example
    $Query->sql('INSERT INTO tablename VALUES ("'.$Query->check_text($_POST['param1']).'", "'.$Query->check_text($_POST['param2']).'")');
    
    $Query->check_date($date) // example right date 2013-05-15
    $Query->check_time($time) // example right time 13:05:45
    $Query->check_datetime($datetime) // example right datetime 2013-05-15 13:05:45

Error message

If you set email for object property mailForError, you will recieve error message

    $Query->mailForError = 'my_mail@domain.com';

Also, you can set alternative error handler function

Example

    $Query->functionErrorName = 'mysqlErrorFunctionName'; // set name of error handler function
	
	/**
    * error handler
    * 
    * @param string $msg Mysql error text
    * @param string $sql SQL query
    * @param array $arrDebug Debug backtrace
    */
	function mysqlErrorFunctionName($msg, $sql, $arrDebug){
		$errMsg = '<b>MySQL Error:</b><br>
                SQL select: '.$sql.'<br> 
                Error: '.$msg.'<br>';
		$errMsg .= 'Stack trace:<br>';
		foreach($arrDebug AS $debug){
			$errMsg .= 'File: <b>'.$debug['file'].'</b>, line: <b>'.$debug['line'].'</b><br>';
		}
		die($errMsg);
    }

If you use alternative error handler function, you cannot use property "mailForError"