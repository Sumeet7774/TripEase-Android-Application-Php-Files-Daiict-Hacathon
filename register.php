<?php

    require "connection.php";

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email_id = $_POST['email_id'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);


    $check_sql = "SELECT * FROM users WHERE email_id=?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s",$email_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) 
    {
        $response = array('status' => 'error', 'message' => 'User data already exists');
        echo json_encode($response);
    } 
    else 
    {
    
        $insert_sql = "INSERT INTO users (first_name, last_name, email_id, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssss", $first_name, $last_name, $email_id, $hashed_password);
    
        if($stmt->execute()) 
        {
            $response = array('status' => 'success', 'message' => 'registration successfull');
        } 
        else 
        {
            $response = array('status' => 'error', 'message' => 'registration failed', 'error' => $stmt->error);
        }
        echo json_encode($response);
    }

    $stmt->close();
    $conn->close();  
?>