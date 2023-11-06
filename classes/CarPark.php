<?php
namespace CarParkAPI;

use CarParkAPI\Utils\DatabaseHandler;

class CarPark {
    private $dbHandler;
    private $max_capacity = 10;

    public function __construct(DatabaseHandler $dbHandler) {
        $this->dbHandler = $dbHandler;
    }

    public function checkAvailability($from, $to) {
        $dateCheckResult = $this->checkDates([$from, $to]);
        if ($dateCheckResult !== true) {
            return $dateCheckResult; 
        }
        $occupied_spaces = $this->dbHandler->checkAvailability($from, $to);
        $available_spaces = $this->max_capacity - $occupied_spaces;
        //if available_spaces is less than 0, we return 0 (this is  here because of the test data)
        if ($available_spaces < 0) {
            $available_spaces = 0;
        }
        return $this->formatResponse('success', 'Availability retrieved successfully', ['available_spaces' => $available_spaces]);
    }

    public function dailyAvailabilityByRange($from, $to) {
        try {
            $dateCheckResult = $this->checkDates([$from, $to]);
            if ($dateCheckResult !== true) {
                return $dateCheckResult; 
            }
            
            $startDate = new \DateTime($from);
            $endDate = new \DateTime($to);
            $availability = [];

            for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
                $dateFormatted = $date->format('Y-m-d');
                $dailyBookings = $this->dbHandler->getDailyAvailability($dateFormatted)["data"]["available_space"];
                $availableSpaces = $this->max_capacity - $dailyBookings;
                $availability[$dateFormatted] = max($availableSpaces, 0); // Ensure available spaces doesn't go below 0
            }

            return $this->formatResponse('success', 'Daily availability retrieved successfully', ['availability' => $availability]);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function dailyAvailability($date) {
        $dateCheckResult = $this->checkDates([$date]);
        if ($dateCheckResult !== true) {
            return $dateCheckResult; 
        }
        $occupied_spaces = $this->dbHandler->getDailyAvailability($date);
        return $this->formatResponse('success', 'Available spaces for requested day', ['available_space' => $this->max_capacity - $occupied_spaces]);
    }

    public function getPricing($date_from, $date_to) {
        $dateCheckResult = $this->checkDates([$date_from, $date_to]);
        if ($dateCheckResult !== true) {
            return $dateCheckResult; 
        }
        $pricing = $this->dbHandler->getPricing($date_from, $date_to);
        return $this->formatResponse('success', 'Pricing retrieved successfully', ['pricing' => $pricing]);
    }

    public function createBooking($date_from, $date_to, $car_plate) {
        $dateCheckResult = $this->checkDates([$date_from, $date_to]);
        if ($dateCheckResult !== true) {
            return $dateCheckResult; 
        }
        $this->dbHandler->beginTransaction();
        try {
            $availability = $this->checkAvailability($date_from, $date_to);
            if ($availability['data']['available_spaces'] <= 0) {
                $this->dbHandler->rollBack();
                return $this->handleError('No available parking spaces for the selected dates');
            }

            $priceDetails = $this->getPricing($date_from, $date_to);
            $booking_id = $this->dbHandler->insertBooking($date_from, $date_to, $car_plate, $priceDetails["data"]["pricing"]['total_price']);

            $this->dbHandler->commit();
            return $this->formatResponse('success', 'Booking created successfully', ['booking_id' => $booking_id]);
        } catch (\Exception $e) {
            $this->dbHandler->rollBack();
            return $this->handleError($e->getMessage());
        }
    }

    public function cancelBooking($booking_id) {
        $this->dbHandler->beginTransaction();
        try {
            $bookingStatus = $this->dbHandler->getBookingStatus($booking_id);
            if ($bookingStatus === false || $bookingStatus["data"]['booking_status'] !== 'booked') {
                $this->dbHandler->rollBack();
                return $this->handleError('Booking does not exist or has already been cancelled');
            }

            $this->dbHandler->cancelBooking($booking_id);
            $this->dbHandler->commit();
            return $this->formatResponse('success', 'Booking cancelled successfully');
        } catch (\Exception $e) {
            $this->dbHandler->rollBack();
            return $this->handleError($e->getMessage());
        }
    }

    public function checkBookingStatus($booking_id) {
        $status = $this->dbHandler->getBookingStatus($booking_id);
        return $this->formatResponse('success', $status);
    }

    public function amendBooking($booking_id, $new_date_from, $new_date_to) {
        $dateCheckResult = $this->checkDates([$new_date_from, $new_date_to]);
        if ($dateCheckResult !== true) {
            return $dateCheckResult; 
        }
        $this->dbHandler->beginTransaction();
        try {
            $availability = $this->checkAvailability($new_date_from, $new_date_to);
            if ($availability['data']['available_spaces'] <= 0) {
                $this->dbHandler->rollBack();
                return $this->handleError('No available parking spaces for the selected dates');
            }

            $priceDetails = $this->getPricing($new_date_from, $new_date_to);
            $this->dbHandler->updateBooking($booking_id, $new_date_from, $new_date_to, $priceDetails["data"]["pricing"]['total_price']);

            $this->dbHandler->commit();
            return $this->formatResponse('success', 'Booking amended successfully');
        } catch (\Exception $e) {
            $this->dbHandler->rollBack();
            return $this->handleError($e->getMessage());
        }
    }

    private function handleError($message) {
        return $this->formatResponse('error', $message);
    }

    private function formatResponse($status, $message, $data = null) {
        $response = ['status' => $status, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        return $response;
    }
    private function checkDates(array $dates) {
        //check date formats and if the dates are not in past. if so, fail
        $currentDate = new \DateTime(); 

        foreach ($dates as $date) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->handleError('Invalid date format');
            }

            $dateToCheck = new \DateTime($date);
            if ($dateToCheck < $currentDate) {
          
                return $this->handleError('Date is in the past');
            }
        }

        return true;
    }
}
?>
