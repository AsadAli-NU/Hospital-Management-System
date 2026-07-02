<?php
// ============================================================
// IVOR PAINE MEMORIAL HOSPITAL
// Database Connection — SQL Server via sqlsrv driver
// ============================================================

$serverName = "REHANS-PC\\SQLEXPRESS";
$connectionOptions = [
    "Database"             => "IvorPaineHospital",
    "Uid"                  => "sa",
    "PWD"                  => "hooper123",
    "CharacterSet"         => "UTF-8",
    "TrustServerCertificate" => true,
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    $errors = sqlsrv_errors();
    http_response_code(500);
    die(json_encode([
        'error' => 'Database connection failed.',
        'details' => $errors
    ]));
}

/**
 * Execute a query and return all rows as associative array.
 */
function db_query($conn, $sql, $params = []) {
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        return ['error' => sqlsrv_errors()];
    }
    $rows = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Convert DateTime objects to strings
        foreach ($row as $k => $v) {
            if ($v instanceof DateTime) {
                $row[$k] = $v->format('Y-m-d');
            }
        }
        $rows[] = $row;
    }
    sqlsrv_free_stmt($stmt);
    return $rows;
}

/**
 * Execute a single-row query (returns one row or null).
 */
function db_query_one($conn, $sql, $params = []) {
    $rows = db_query($conn, $sql, $params);
    if (isset($rows['error'])) return $rows;
    return count($rows) > 0 ? $rows[0] : null;
}
?>
