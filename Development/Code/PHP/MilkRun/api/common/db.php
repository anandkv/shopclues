<?php
date_default_timezone_set('Asia/Calcutta');
$createddate = date('Y-m-d H:i:s');

function db_get_array($sql)
{
    $result = db_query($sql);
    $num = mysql_numrows($result);
    $output = Array();

    while ($data = mysql_fetch_assoc($result)) {
        array_push($output, $data);
    }
    return $output;
}

function db_get_row($sql)
{
    $result = db_query($sql);
    return mysql_fetch_assoc($result);
}

function connectDB()
{
    mysql_connect("localhost", "cabbeein_admin", "test123");
    mysql_select_db("cabbeein_milkrun");
}

function disconnect()
{
    mysql_close();
}

function db_query($sql)
{
    connectDB();
    $result = mysql_query($sql) or die(mysql_error());
    disconnect();
    return $result;
}

function executeCURL($url, $data)
{
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    $file_contents = curl_exec($curl_handle);
    curl_close($curl_handle);
    return $file_contents;
}

?>