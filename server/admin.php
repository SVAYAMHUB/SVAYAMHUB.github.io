<?php
// Define the path to the admin JSON file
$adminJsonFile = "/var/www/html/Attendance/server/admin.json";

// Check if the admin JSON file exists
if (!file_exists($adminJsonFile)) {
    die("Error: Admin JSON file not found.");
}

// Read the admin JSON file
$adminData = json_decode(file_get_contents($adminJsonFile), true);

// Function to validate user role based on admin data
function validateUserRole($name, $rollNo, $department, $post)
{
    global $adminData;

    // Trim input values to remove extra whitespaces
    $name = trim($name);
    $rollNo = trim($rollNo);
    $department = trim($department);
    $post = trim($post);

    foreach ($adminData as $admin) {
        // Make the comparison case-insensitive
        if (
            strcasecmp($admin['name'], $name) === 0 &&
            strcasecmp($admin['roll_no'], $rollNo) === 0 &&
            strcasecmp($admin['department'], $department) === 0 &&
            strcasecmp($admin['post'], $post) === 0
        ) {
            return true;
        }
    }

    return false;
}

// Function to get the attendance file path based on the date
function getAttendanceFilePath($date)
{
    return "/var/www/html/Attendance/server/attendance_$date.json";
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST["name"];
    $rollNo = $_POST["roll_no"];
    $department = $_POST["department"];
    $post = $_POST["post"];
    $date = $_POST["date"];

    // Validate user role
    if (validateUserRole($name, $rollNo, $department, $post)) {
        // Get attendance file path
        $attendanceFilePath = getAttendanceFilePath($date);

        // Check if the attendance file exists
        if (file_exists($attendanceFilePath)) {
            // Set the appropriate headers for JSON download
            header('Content-Type: application/json');
            header("Content-Disposition: attachment; filename=attendance_$date.json");

            // Read the attendance JSON file and output its contents
            readfile($attendanceFilePath);
        } else {
            echo "Error: Attendance file not found for the specified date.";
        }
    } else {
        echo "Error: Invalid user details. Check name, roll number, department, and post.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Download Attendance</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('assets/admin_bg.jpeg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
        }

        .container {
            background-color: rgba(100, 255, 255, 0.0);
            padding: -10px;
            border-radius: 5px;
            box-shadow: 10px 10px 10px rgba(0, 0, 0, 0.1);
        }

        .center-button {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title text-center">Download Attendance</h1>
                        
                        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <div class="form-group">
                                <label for="name">Name:</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>

                            <div class="form-group">
                                <label for="roll_no">Roll No:</label>
                                <input type="text" class="form-control" id="roll_no" name="roll_no" required>
                            </div>

                            <div class="form-group">
                                <label for="department">Department:</label>
                                <select class="form-control" id="department" name="department" required>
                                    <option value="None">None</option>
                                    <option value="SSD">Sustainable Social Development</option>
                                    <option value="GC">Green Campus</option>
                                    <option value="EO">Educational Outreach</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="post">Post:</label>
                                <select class="form-control" id="post" name="post" required>
                                    <option value="AA">AA</option>
                                    <option value="Head">Head</option>
                                    <!-- Add other post options as needed -->
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="date">Date:</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>

                            <div class="center-button">
                                <button type="submit" class="btn btn-primary">Download Attendance</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>


