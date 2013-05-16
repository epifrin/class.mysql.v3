mysql.class
===========

PHP class for MySQL


# Connect to database and create object
$Query = MySqlClass::connect('localhost','dbname','dbuser','dbpass','utf8');

# Simple sql-select
$Query->sql('INSERT INTO tablename VALUES ("value1","value2")');
