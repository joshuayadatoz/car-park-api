<?php

require 'vendor/autoload.php';
use CarParkAPI\CarPark; 
use CarParkAPI\Database; 

$database = new Database();
$db = $database->getConnection();

$carPark = new CarPark($db);

$action = $_GET['action'];

//http://localhost/car-park-api/index.php?action=getPricing&from=2023-11-01&to=2023-11-10
//http://localhost/car-park-api/index.php?action=createBooking&from=2023-11-01&to=2023-11-10&car_plate=ABC123
//http://localhost/car-park-api/index.php?action=cancelBooking&booking_id=24
//http://localhost/car-park-api/index.php?action=amendBooking&booking_id=10&new_from=2023-11-02&new_to=2023-11-09
//http://localhost/car-park-api/index.php?action=checkAvailability&from=2023-11-01&to=2023-11-10
//http://localhost/car-park-api/index.php?action=checkBookingStatus&booking_id=8
//http://localhost/car-park-api/index.php?action=dailyAvailability&date=2023-11-01
//http://localhost/car-park-api/index.php?action=dailyAvailabilityByRange&from=2023-11-01&to=2023-11-10




try {
    
    if ($action == 'dailyAvailabilityByRange') {
        $from = $_GET['from'];
        $to = $_GET['to'];
        $result = $carPark->dailyAvailabilityByRange($from, $to);
    }
    if ($action == 'dailyAvailability') {
        $date = $_GET['date'];
        $result = $carPark->dailyAvailability($date);
    }
    if ($action == 'checkBookingStatus') {
        $booking_id = $_GET['booking_id'];
        $result = $carPark->checkBookingStatus($booking_id);
    }
    if ($action == 'getPricing') {
        $from = $_GET['from'];
        $to = $_GET['to'];
        $result = $carPark->getPricing($from, $to);
    }
    if ($action == 'createBooking') {
        $from = $_GET['from'];
        $to = $_GET['to'];
        $car_plate = $_GET['car_plate'];
        $result = $carPark->createBooking($from, $to, $car_plate);
    }
    if ($action == 'cancelBooking') {
        $booking_id = $_GET['booking_id'];
        $result = $carPark->cancelBooking($booking_id);
    }
    if ($action == 'amendBooking') {
        $booking_id = $_GET['booking_id'];
        $new_from = $_GET['new_from'];
        $new_to = $_GET['new_to'];
        $result = $carPark->amendBooking($booking_id, $new_from, $new_to);
    }
    if ($action == 'checkAvailability') {
        $from = $_GET['from'];
        $to = $_GET['to'];
        $result = $carPark->checkAvailability($from, $to);
    }
    echo json_encode($result);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'An error occurred']);
}
?>
