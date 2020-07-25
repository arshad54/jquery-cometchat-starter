<?php
session_start();
 
if (isset($_SESSION["authenticated"])) {
    header("location: chat.php");
    exit;
}

// Include config file
require_once "connection.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";
 
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            
                            // Store data in session variables
                            $_SESSION["authenticated"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            echo "Logged in successfully";
                            
                            // Redirect user to welcome page
                            header("location: chat.php");
                        } else {
                            // Display an error message if password is not valid
                            $password_err = "The password you entered is not valid.";
                        }
                    }
                } else {
                    // Display an error message if username doesn't exist
                    $username_err = "No account found with that username.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JQUERY / PHP CHAT APPLICATION</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div>
        <div class="login-page">
            <div class="login">
                <div class="login-container">
                    <div class="login-form-column">
                        <form class="auth_form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                            method="post">
                            <h3>Welcome!</h3>

                            <div class="form-wrapper">
                                <label for="username">Username</label><br>
                                <input type="text" name="username" id="username" placeholder="Enter your username"
                                    class="form-control" required value="<?php echo $username; ?>" />
                                <span class="help-block"><?php echo $username_err; ?></span>
                            </div>

                            <div class="form-wrapper">
                                <label for="password">Password</label><br>
                                <input type="password" name="password" id="reg_password"
                                    placeholder="Enter your password" class="form-control"
                                    value="<?php echo $password; ?>" required />
                                <span class="help-block"><?php echo $password_err; ?></span>
                            </div>

                            <button type="submit">LOG IN </button>
                        </form>

                        <div>
                            <p> Don't have an account? </p>
                            <a href="register.php"> Register </a>
                        </div>
                    </div>
                    <div class="login-image-column">
                        <div class="image-holder">
                            <img src="../assets/login-illustration.svg" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>