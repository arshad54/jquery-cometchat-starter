<?php
// Include config file
require_once "connection.php";
 
// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";
 
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have atleast 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
         
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);
            
            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to login page
                header("location: index.php");
            } else {
                echo "Something went wrong. Please try again later.";
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
                            <h2>Create an account</h2>

                            <div class="form-wrapper">
                                <label for="username">Username</label><br>
                                <input type="text" name="username" id="reg_username" placeholder="Enter your username"
                                    class="form-control" value="<?php echo $username; ?>" required />
                                <span class="help-block"><?php echo $username_err; ?></span>
                            </div>

                            <div class="form-wrapper">
                                <label for="password">Password</label><br>
                                <input type="password" name="password" id="reg_password"
                                    placeholder="Enter your password" class="form-control"
                                    value="<?php echo $password; ?>" required />
                                <span class="help-block"><?php echo $password_err; ?></span>
                            </div>

                            <div class="form-wrapper">
                                <label for="confirm_password">Confirm Password</label><br>
                                <input type="password" name="confirm_password" id="confirm_password"
                                    placeholder="Re-enter password" class="form-control" value="<?php echo $confirm_password; ?> required />
                                    <span class=" help-block"><?php echo $confirm_password_err; ?></span>
                            </div>
                            <button type=" submit">REGISTER </button>
                        </form>

                        <div>
                            <p> Already have an account ? </p>
                            <a href="index.php"> Login </a>
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


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>


</body>

</html>