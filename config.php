<?php
/*
 * config.php
 * ----------
 * Keeps ALL database settings in one place and gives a helper
 * function db() that returns ONE shared PDO connection.
 */

/* OPTIONAL: show errors while debugging (remove in production) */
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

/* --------------------------------------------------------------
   1)  EDIT THESE FOUR CONSTANTS TO MATCH YOUR SERVER
   -------------------------------------------------------------- */
define('DB_HOST', 'mysql01');        // usually "localhost"
define('DB_NAME', '8153-boatlog');   // your database
define('DB_USER', '8153-k1jcf');     // your MySQL username
define('DB_PASS', 'l4AwiyHZ-0');     // your MySQL password

/**
 * db()
 * ----
 * Returns ONE shared PDO object.
 * static $pdo stores the connection between calls, so we only
 * connect once (good for performance).
 */
function db(): PDO
{
    static $pdo;          // keeps the connection between calls

    if ($pdo === null) {  // create connection only the first time
        /* DSN string: tells PDO where the DB is and that we want
           utf8mb4 as internal charset */
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        /* Build the PDO object */
        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                /* NEW LINE ? forces the connection to use utf8mb4
                   even on older MySQL/MariaDB versions */
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",

                /* Throw exceptions on SQL errors (easier debugging) */
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                /* Every fetch() returns an associative array
                   (column => value) by default */
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    return $pdo; // same PDO object every time
}
