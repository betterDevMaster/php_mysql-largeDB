<?php
// Change setting in your mysql setting for importing the large data
// wait_timeout=600
// max_allowed_packet=64M


// Initialize All variables
$fp                 = fopen('ParentSupplierBrand_with_SubBrand_and_RevisionDate_OEM.txt', 'r');

// $dbHost             = '198.211.109.170:3306';
// $dbUser             = 'admin';
// $dbPass             = 'b2964dac51e8e368ad6037ba88a1c5767014440e2e761514';

$dbHost             = 'localhost';
$dbUser             = 'root';
$dbPass             = '';

$dbName             = 'products';
$tblBrands          = 'brands';
$GLOBALS['conn']    = '';
$GLOBALS['brands']  = array();


// Calling Func
expertFigure();
retriveArrayFromFile($fp);
dbInit($dbHost, $dbUser, $dbPass, $dbName);
brandsTblCreate($tblBrands);
ini_set('mysql.connect_timeout', 300);
ini_set('default_socket_timeout', 300); 
insertDataToBrandsTbl($tblBrands);
dbConnClose();

// Showing the result
showPrettyPrint($GLOBALS['brands']);

/////// Function body start : ////////
function expertFigure()
{
    echo "
        <figure role='img' aria-labelledby='cow-caption' style='text-align: center; margin-top: 30px;'>
        <pre>
        ___________________________
        < I am an expert in my field. >
        ---------------------------
                \   ^__^ 
                \  (oo)\_______
                    (__)\       )\/\
                        ||----w |
                        ||     ||
        </pre>
        <figcaption id='cow-caption'>
            A cow saying, 'I am an expert in my field.' 
        </figcaption>
        </figure>
        ";
}
function retriveArrayFromFile($fp)
{
    while (!feof($fp)) {
        $brand = array();
        $line = fgets($fp);

        //process line however you like
        $line = trim($line);

        //process to replace the doubleQuotation
        $subLine = explode('|', $line);
        if (!$subLine[0] || !$subLine[3] || !$subLine[5])
            continue;

        $brand[removeDoubleQuote($subLine[0])] = removeDoubleQuote($subLine[1]);
        $brand[removeDoubleQuote($subLine[3])] = removeDoubleQuote($subLine[4]);
        $brand[removeDoubleQuote($subLine[5])] = removeDoubleQuote($subLine[6]);

        array_push($GLOBALS['brands'], $brand);
    }
    echo 'brands Count : '.count($GLOBALS['brands']);
}
function removeDoubleQuote($str)
{
    return str_replace('"', '', $str);
}
function showPrettyPrint($a)
{
    echo '<pre>';
    print_r($a);
    echo '</pre>';
}
function dbInit($dbHost, $dbUser, $dbPass, $dbName)
{
    // Connect to MySQL
    $GLOBALS['conn'] = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
    if ($GLOBALS['conn']->connect_error) {
        die('Connection failed to Mysqli: ' . $GLOBALS['conn']->connect_error . '<br>');
    }

    // If database is not exist create one
    if (!mysqli_select_db($GLOBALS['conn'], $dbName)) {
        $sql = 'CREATE DATABASE ' . $dbName;
        if ($GLOBALS['conn']->query($sql) === TRUE) {
            echo 'Database created successfully <br>';
        } else {
            echo 'Error creating database: ' . $GLOBALS['conn']->error . '<br>';
        }
    }
}
function brandsTblCreate($tblBrands)
{
    $query = 'CREATE TABLE IF NOT EXISTS ' . $tblBrands . ' (
        id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),
        aaia   varchar(4) DEFAULT NULL,
        name   varchar(100) NOT NULL
    )';

    if ($GLOBALS['conn']->query($query) === TRUE) {
        echo `$tblBrands table created successfully <br>`;
    } else {
        echo `Error creating $tblBrands table: ` . $GLOBALS['conn']->error . '<br>';
    }
}
function insertDataToBrandsTbl($tblBrands)
{
    $sql = "INSERT INTO `$tblBrands` (`aaia`, `name`) VALUES(?,?)";
    // Run the batch insert statements
    if ($stmt = mysqli_prepare($GLOBALS['conn'], $sql)) {
        // execute it and all...
        foreach($GLOBALS['brands'] as $record) {
            // Loop through employee array  
            foreach($record as $recordKey => $element) {
                // echo $recordKey.'-----'.$element.'<br>';
                $stmt->bind_param('ss', $recordKey, $element);
                $stmt->execute();
            }  
        }
        echo 'Data inserted successfully <br>';
    } else {
        die("Error inserting to $tblBrands table: ". $GLOBALS['conn']->error);
    }
}
function dbConnClose() {
    mysqli_close($GLOBALS['conn']);
}
/////// Function body end : ////////

fclose($fp);
