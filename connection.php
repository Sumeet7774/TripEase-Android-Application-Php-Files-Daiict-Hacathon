<?php

    $server_name = "localhost";
    $username = "root";
    $password = "";
    $database_name = "trip_ease";
    $port = "3307";

    $conn = mysqli_connect($server_name, $username, $password, $database_name, $port);

    if($conn)
    {
        echo "connection successfull";
    }
    else
    {
        echo "connection unsuccessfull";
    }

?>