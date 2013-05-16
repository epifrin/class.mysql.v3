mysql.class
===========

##PHP class for MySQL


Connect to database and create object

    require 'mysql.class.php';
    $Query = MySqlClass::connect('localhost','dbname','dbuser','dbpass','utf8');


Simple sql-select

    $Query->sql('INSERT INTO tablename VALUES ("value1","value2")');
    ->query() alias of sql()

Get array of first record

    $array = $Query->getarray('SELECT * FROM tablename');
    ->ga() alias of getarray()

Get multiline array of sql select

    $array = $Query->getmultiarray('SELECT * FROM tablename');
    ->gma() is alias of getmultiarray()