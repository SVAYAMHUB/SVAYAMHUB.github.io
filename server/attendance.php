<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the path to the JSON file
$jsonFile = "/var/www/html/Attendance/server/student_data.json";
date_default_timezone_set('Asia/Kolkata');
// Get the current date in the desired format (e.g., yyyy-mm-dd)
$currentDate = date("Y-m-d");
$time_stamp = date("H:i:s");
// Define the path to the JSON file with the current date in the name
$attendanceFileName = '/var/www/html/Attendance/server/attendance_' . $currentDate . '.json';

// Initialize a flag to indicate whether data has been saved
$dataSaved = false;
$message = '';
$mail = "web.nssiitbombay@gmail.com";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $name = isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : '';
    $roll_no = isset($_POST["roll_no"]) ? htmlspecialchars($_POST["roll_no"]) : '';
    $department = isset($_POST["department"]) ? htmlspecialchars($_POST["department"]) : '';
    $phone_number = isset($_POST["phone_number"]) ? htmlspecialchars($_POST["phone_number"]) : '';
    $geolocation = isset($_POST["geolocation"]) ? htmlspecialchars($_POST["geolocation"]) : ''; // Sanitize user input

    // Read existing data from the JSON file
    $attendanceRecords = [];
    if (file_exists($attendanceFileName)) {
        $jsonData = file_get_contents($attendanceFileName);
        $attendanceRecords = json_decode($jsonData, true);
    } 
    else {
        file_put_contents($attendanceFileName, "[]");
    }

    $attendenceentryExists = false;
    foreach ($attendanceRecords as $entry) {
        if (
            strcasecmp($entry['name'], $name) === 0 &&
            strcasecmp($entry['roll_no'], $roll_no) === 0 &&
            strcasecmp($entry['department'], $department) === 0
        ) {
            $attendenceentryExists = true;
            break;
        }
    }

    $existingData = [];
    if (file_exists($jsonFile)) {
        $jsonData = file_get_contents($jsonFile);
        $existingData = json_decode($jsonData, true);
    }

    // Check if name, roll_no, or phone_number already exists in any existing entry (case-insensitive comparison)
    $entryExists = false;
    foreach ($existingData as $entry) {
        if (
            strcasecmp($entry['name'], $name) === 0 &&
            strcasecmp($entry['roll_no'], $roll_no) === 0 &&
            strcasecmp($entry['phone_number'], $phone_number) === 0 &&
            strcasecmp($entry['department'], $department) === 0
        ) {
            $entryExists = true;
            break;
        }
    }

    function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance;
    }

    if ($attendenceentryExists) {
        $message = "Error: Attendance already marked\n";
    } else {
        if (!$entryExists) {
            $message = "\n !! Please Complete Registration !! \n You are not registered.\n Enter Correct details .  Contact :- AA";
        } else {
            $currentDate = date("Y-m-d");
            $aaDateFileName = "/var/www/html/Attendance/server/AA_$currentDate.json";

            $aaDateData = json_decode(file_get_contents($aaDateFileName), true);

            if ($aaDateData && isset($aaDateData['location'])) {
                $locationParts = explode(",", str_replace(["Latitude: ", "Longitude: "], "", $aaDateData['location']));

                $aaDateLatitude = floatval(trim($locationParts[0]));
                $aaDateLongitude = floatval(trim($locationParts[1]));

                $geolocationArray = explode(",", $geolocation);

                if (count($geolocationArray) >= 2) {
                    $currentLatitude = floatval($geolocationArray[0]);
                    $currentLongitude = floatval($geolocationArray[1]);

                    $distance = haversineDistance($currentLatitude, $currentLongitude, $aaDateLatitude, $aaDateLongitude);

                    echo "Current Latitude: " . $currentLatitude . "<br>";
                    echo "Current Longitude: " . $currentLongitude . "<br>";
                    echo "Distance: " . $distance . " meters<br>";

                    if ($distance > 100) {
                        $studentData = [
                            "name" => $name,
                            "roll_no" => $roll_no,
                            "department" => $department,
                            "Timestamp" => $time_stamp,
                            "geolocation" => $geolocation
                        ];

                        $attendanceRecords[] = $studentData;

                        $jsonData = json_encode($attendanceRecords, JSON_PRETTY_PRINT);

                        if (file_put_contents($attendanceFileName, $jsonData) !== false) {
                            $dataSaved = true;
                        } else {
                            $message = "Error: Unable to Process @ $mail";
                            error_log("Error: Unable to save data to $attendanceFileName");
                        }
                    } else {
                        $message = "Error: You are too far from the expected location. Distance: $distance meters.";
                    }
                } else {
                    $message = "Error: Invalid geolocation format.";
                }
            } else {
                $message = "Error: Unable to retrieve geolocation.\n Ask AA to Fill form first.";
            }
        }
    }
} 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('assets/attendance_bg.jpeg');
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
                        <h1 class="card-title text-center">Attendance</h1>

                        <?php if ($dataSaved) : ?>
                            <div class="alert alert-success" role="alert">
                                Your Attendance has been marked successfully.
                            </div>
                        <?php elseif (!empty($message)) : ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $message; ?>
                            </div>
                        <?php else : ?>
                            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
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
                                    <label for="phone_number">Phone Number:</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                                </div>

                                <div class="form-group">
                                    <label for="location">Location:</label>
                                    <input type="text" class="form-control" id="location" name="location" required readonly>
                                </div>

                                <!-- Add this hidden input field for geolocation -->
                                <input type="hidden" id="geolocation" name="geolocation" value="">

                                <div class="center-button">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Add this script block at the end of your HTML body -->
    <script>
        // Function to get geolocation
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        // Function to update the hidden input field with geolocation data
        function showPosition(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            const geolocationData = `Latitude: ${latitude}, Longitude: ${longitude}`;
            document.getElementById("geolocation").value = geolocationData;
        }

        // Call getLocation function when the page loads
        document.addEventListener("DOMContentLoaded", function () {
            getLocation();
        });
    </script>
</body>
</html>