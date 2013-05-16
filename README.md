mysql.class
===========

##PHP class for MySQL


Connect to database and create object

    require 'mysql.class.php';
    $Query = MySqlClass::connect('localhost','dbname','dbuser','dbpass','utf8');


Simple sql query

    $Query->sql('INSERT INTO tablename VALUES ("value1","value2")');
    ->query() alias of sql()

Get array of first record

    $array = $Query->getarray('SELECT * FROM tablename');
    ->ga() alias of getarray()

Get multiline array of sql query

    $array = $Query->getmultiarray('SELECT * FROM tablename');
    ->gma() is alias of getmultiarray()

Get array values of first field

    $array = $Query->getverticalarray('SELECT * FROM tablename');
    ->gva() is alias of getverticalarray()

Get index array with keys from values first field

    $array = $Query->getindexmultiarray('SELECT * FROM tablename');
    ->gima() is alias of getindexmultiarray()

Get text of last SQL query

    $sqltext = $Query->getLastQuery();

