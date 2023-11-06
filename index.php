<?php

require 'vendor/autoload.php';
use CarParkAPI\CarPark; 
use CarParkAPI\Database; 
use CarParkAPI\Utils\InputValidator;
use CarParkAPI\Utils\DatabaseHandler;

$database = new Database();
$db = $database->getConnection();
$dbHandler = new DatabaseHandler($db);



//http://localhost/car-park-api/index.php?action=getPricing&from=2023-11-01&to=2023-11-10
//http://localhost/car-park-api/index.php?action=createBooking&from=2023-11-01&to=2023-11-10&car_plate=ABC123
//http://localhost/car-park-api/index.php?action=cancelBooking&booking_id=29
//http://localhost/car-park-api/index.php?action=amendBooking&booking_id=10&new_from=2023-11-02&new_to=2023-11-09
//http://localhost/car-park-api/index.php?action=checkAvailability&from=2023-11-01&to=2023-11-10
//http://localhost/car-park-api/index.php?action=checkBookingStatus&booking_id=8
//http://localhost/car-park-api/index.php?action=dailyAvailability&date=2023-11-01
//http://localhost/car-park-api/index.php?action=dailyAvailabilityByRange&from=2023-11-01&to=2023-11-10

$action = isset($_GET['action']) ? InputValidator::sanitize($_GET['action'], 'string') : null;

if ($action) {
    // Instantiate the CarPark object
    $carPark = new CarPark($dbHandler);
    try {
        
        if ($action == 'dailyAvailabilityByRange') {
            $from = InputValidator::sanitize($_GET['from'], 'date');
            $to = InputValidator::sanitize($_GET['to'], 'date');
            $result = $carPark->dailyAvailabilityByRange($from, $to);
        }
        if ($action == 'dailyAvailability') {
            $date = InputValidator::sanitize($_GET['date'], 'date');
            $result = $carPark->dailyAvailability($date);
        }
        if ($action == 'checkBookingStatus') {
            $booking_id = InputValidator::sanitize($_GET['booking_id'], 'int');
            $result = $carPark->checkBookingStatus($booking_id);
        }
        if ($action == 'getPricing') {
            $from = InputValidator::sanitize($_GET['from'], 'date');
            $to = InputValidator::sanitize($_GET['to'], 'date');
            $result = $carPark->getPricing($from, $to);
        }
        if ($action == 'createBooking') {
            $from = InputValidator::sanitize($_GET['from'], 'date');
            $to = InputValidator::sanitize($_GET['to'], 'date');
            $car_plate = $_GET['car_plate'];
            $result = $carPark->createBooking($from, $to, $car_plate);
        }
        if ($action == 'cancelBooking') {
            $booking_id = InputValidator::sanitize($_GET['booking_id'], 'int');
            $result = $carPark->cancelBooking($booking_id);
        }
        if ($action == 'amendBooking') {
            $booking_id = InputValidator::sanitize($_GET['booking_id'], 'int') ;
            $new_from = InputValidator::sanitize($_GET['new_from'], 'date');
            $new_to = InputValidator::sanitize($_GET['new_to'], 'date');
            $result = $carPark->amendBooking($booking_id, $new_from, $new_to);
        }
        if ($action == 'checkAvailability') {
            $from = InputValidator::sanitize($_GET['from'], 'date');
            $to = InputValidator::sanitize($_GET['to'], 'date');
            $result = $carPark->checkAvailability($from, $to);
        }
        echo json_encode($result);

    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['error' => $e->getMessage()]);
    }
}

?>
