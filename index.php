

<?php
session_start();
date_default_timezone_set('Asia/Manila');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "revised";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



if(isset($_POST['submit'])){
    $email = $_POST['email'];
    $pass = $_POST['password'];
    
    // Prepare and execute the SQL statement to fetch user details
    $stmt = $conn->prepare("SELECT * FROM `admin_login` WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $pass);
    $stmt->execute();
    $result = $stmt->get_result();
 
    // Check if the user exists
    if($result->num_rows == 1){
        $row = $result->fetch_assoc();
        
        // Update user status, time_joined, and date_joined
        $stmt = $conn->prepare("UPDATE admin_login SET user_status = 'online', time_joined = NOW(), date_joined = CURDATE() WHERE ID = ?");
        $stmt->bind_param("i", $row['ID']);
        $stmt->execute();
        
        // Insert values into the activity table
        $stmt = $conn->prepare("INSERT INTO activity (ID, time_loged) VALUES (?, NOW())");
        $stmt->bind_param("i", $row['ID']);
        $stmt->execute();
        
        // Store user details in session variables
        $_SESSION['user_id'] = $row['ID'];
        $_SESSION['user_name'] = $row['name']; // Store user's name
        $_SESSION['role'] = $row['role'];      // Store user's role
        
        // Debugging line (you can comment it out later)
        var_dump($row['role']); 

        // Redirect based on role
        if ($row['role'] == 'HR Manager') {
            header('Location: Dashtwo.php'); // Redirect to HR Manager dashboard
            exit(); // Stop further code execution
        } else if ($row['role'] == 'HR Analyst') {
            header('Location: Dash.php'); // Redirect to HR Analyst dashboard
            exit(); // Stop further code execution
        } else {
            header('Location: index2.php'); // Redirect to default dashboard
            exit(); // Stop further code execution
        }
    } else {
        $message[] = 'Incorrect email or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign in || Sign up from</title>
   
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
		@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');

	
        * {
            padding: 0px;
            margin: 0px;
            box-sizing: border-box;
        }

        :root {
            --linear-grad: linear-gradient(to right, #458B95, #05647A);
            --grad-clr1: #458B95;
            --grad-clr2: #05647A;
        }

        body {
            height: 100vh;
            background: #E5F2F0;
            display: grid;
            place-content: center;
            font-family: 'Poppins', sans-serif;
        }
        
        .container {
            position: relative;
            width: 850px;
            height: 500px;
            background-color: white; 
            box-shadow: 25px 30px 55px #5557; 
            border-radius: 13px;
            overflow: hidden;
        }
        
        .form-container {
            position: absolute;
            width: 60%;
            height: 100%;
            padding: 0px 40px;
            transition: all 0.6s ease-in-out;
        }
        
        .sign-up-container {
            opacity: 0;
            z-index: 1;
        }
        
        .login-container {
            z-index: 2;    
        }
        
        form {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0px 50px;
        }
        
        h1 {
            color: var(--grad-clr1);
        }
        
        .overlay h1 span {
            font-size: 30px;
            color: white;
        }
        
        .social-container {
            margin-top: 20px 0px;
        }
        .social-container a {
            border: 1px solid #DDD;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0px 5px;
            height: 40px;
            width: 40px;
        }
        
        span {
            font-size: 12px;
        }
        
        .infield {
            position: relative;
            margin: 8px 0px;
            width: 100%;
        }
        
        input {
            width: 100%;
            padding: 12px 8px;
            background-color: #f3f3f3;
            border: none;
            outline: none;
        }
        
        label {
            position: absolute;
            left: 50%;
            top 100%;
            width: 0%;
            height: 2px;
        }
        
        input:focus ~ label {
            width: 100%;
        }
        
        a {
            color: #333;
            font-size: 14px;
            text-decoration: none;
            margin: 15px 0px;
        }
        
        a.forgot {
            padding-bottom: 3px;
            border-bottom: 2px solid #EEE;
        }
        
        a.over {
            color: white;
        }
        
        .logo img {
            position: absolute;
            width: 90px;
            margin-top:0px;
            left: 10px;
            top: 0;
        }
        
        .-btn {
            border-radius: 20px;
            border: 1px solid var(--grad-clr1);
            background: var(--grad-clr2);
            color: #FFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .form-container button {
            margin-top: 17px;
            transition: 80ms ease-in;
        }
        
        .form-container button:hover {
            background: #FFF;
            color: var(--grad-clr1);
        }
        
        .overlay-container {
            position: absolute;
            top: 0;
            left: 60%;
            width: 40%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 9;
        }
        
        #overlayBtn {
            cursor: pointer;
            position: absolute;
            left: 50%;
            top: 304px;
            transform: translateX(-50%);
            width: 143.67px;
            height: 40px;
            border: 1px solid #FFF;
            background: transparent;
            border-radius: 20px;;
        }
        .over-btn {
        
        }
        
        .overlay {
            position: relative;
           
            color: #FFF;
            left: -150%;
            height: 100%;
            width: 250%;
            transition: transform 0.6s ease-in-out;
        }
        .overlay img {
            position: absolute;
            width: 100%;
			height: 100%;
            display: none; /* Hide all images initially */
        }
        .overlay img.active {
            display: block; /* Display the active image */
        }
        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0px 40px;
            text-align: center;
            height: 100%;
            width: 340px;
            transition: 0.6s ease-in-out;
        }
        
        .overlay-left {
            right: 60%;
            transform: translateX(-12%);
        }
        
        .overlay-right {
            right: 0;
            transform: translateX(0%);
        }
        
        .overlay-panel h1 {
            color: 3FFF;
        }
        
        p {
            font-size: 14px;
            font-weight: 300;
            line-height: 20px;
            letter-spacing: 0.5px;
            margin: 25px 0px 35px;
        }
        
        .overlay-panel button {
            border: none;
            background-color: transparent;
        }
        
        .right-panel-active .overlay-container {
            transform: translateX(-150%);
        }
        
        .right-panel-active .overlay {
            transform: translateX(50%);
        }
        
        .right-panel-active .overlay-left {
            transform: translateX(25%);
        }
        
        .right-panel-active .overlay-right {
            transform: translateX(35%);
        }
        .right-panel-active .login-container {
            transform: translateX(20%);
            opacity: 0;
        }
        
        .right-panel-active .sign-up-container {
            transform: translateX(66.7%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
        }
        
        @keyframes show {
            0%, 50% {
                opacity: 0;
                z-index: 1;
            }
            50.1%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }
        
        .btnScaled {
            animation: scaleBtn 0.6s;
        }
        @keyframes {
            0% {
                width: 143.67px;
            }
            50% {
                width: 250px;
            }
            100% {
                width: 143.67px;
            }
        }

         .footer {
            position: absolute;
            bottom: 10px;
            right: 20px;
            font-size: 12px;
            color: #333;
        }
    </style>
	
</head>

<body>
	<div class="logo"><img src="Pic/newlogo.png"></img></div>
    <div class="container" id="container">
        
        <div class="form-container login-container">
            <form action="" method="post" >
                <h1>Login</h1>

                <?php
      if(isset($message)){
         foreach($message as $message){
            echo '<div class="message">'.$message.'</div>';
         }
      }
      ?>


                
                <div class="infield">
                    <input type="email" placeholder="Username" name="email" autofocus="true"/>
                    <label></label>
                </div>
                <div class="infield">
                    <input type="password" placeholder="Password" name="password"/>
                    <label></label>
                </div>

                <a href="forgot_password.php">Forgot Password?</a>
                
                <p>
                <input class="-btn" type="submit" name="submit" value="Login">
				</p>
            </form>
        </div>
		
        <div class="overlay-container" id="overlayCon">
            <div class="overlay">
                <img src="Pic/img1.jfif" class="active"></img>
                <img src="Pic/img2.jfif"></img>
                <img src="Pic/img3.jpg"></img>
            </div>
        </div>

    </div>

    <footer class="footer">
        Developed by: Villarante, Melo, Jarina, Santiago, Medina, and San Diego
    </footer>









    <script>


      const slides = document.querySelectorAll('.overlay img');
let currentSlide = 0;

// Function to show slide
function showSlide(index) {
    slides.forEach((slide, i) => {
        if (i === index) {
            slide.classList.add('active');
        } else {
            slide.classList.remove('active');
        }
    });
}

// Function to switch slides automatically
function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
}

// Automatically switch slides every 3 seconds
setInterval(nextSlide, 2000);





		const container = document.getElementById('container');
		const overlayCon = document.getElementById('overlayCon');
		const overlayBtn = document.getElementById('overlayBtn');
		
		overlayBtn.addEventListener('click',()=>{
			container.classList.toggle('right-panel-active');
			
			overlayBtn.classList.remove('btnScaled');
			window.requestAnimation(()=>{
				overlayBtn.classList.add('btnScaled');
			});
		});
    </script>
    
	

</body>
</html>