<?php
namespace CarParkAPI;


class CarPark {
    private $conn;
    private $max_capacity = 10;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    public function checkAvailability($from, $to) {
        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $from) || !preg_match('/\d{4}-\d{2}-\d{2}/', $to)) {
            throw new \Exception('Invalid date format');
        }
        
        $query = "SELECT *
            FROM bookings
            WHERE (
                (date_from BETWEEN :from AND :to) OR
                (date_to BETWEEN :from AND :to) OR
                (date_from <= :from AND date_to >= :to)
            ) AND booking_status = 'booked'
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':from' => $from, ':to' => $to]);
        $occupied_spaces = $stmt->rowCount();
        $available_spaces = $this->max_capacity - $occupied_spaces;
        //if available_spaces is less than 0, we return 0 (this is  here because of the test data)
        if ($available_spaces < 0) {
            $available_spaces = 0;
        }
        return $this->formatResponse('success', 'Availability retrieved successfully', ['available_spaces' => $available_spaces]);

    }

    public function dailyAvailabilityByRange($from, $to) {
        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $from) || !preg_match('/\d{4}-\d{2}-\d{2}/', $to)) {
            return $this->handleError('Invalid date format');
        }
        
        $startDate = new \DateTime($from);
        $endDate = new \DateTime($to);
        $availability = [];  // Array to hold availability data for each date in the range
        
        for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $dateFormatted = $date->format('Y-m-d');
            $query = "
                SELECT COUNT(*) as booked_count 
                FROM bookings 
                WHERE 
                    (:date BETWEEN date_from AND date_to) AND 
                    booking_status = 'booked'
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':date' => $dateFormatted]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $booked_count = $row['booked_count'];
            $available_spaces = $this->max_capacity - $booked_count;
            $availability[$dateFormatted] = $available_spaces;
        }
        
        return $this->formatResponse('success', 'Availability retrieved successfully', ['availability' => $availability]);
    }
    
    
    //Useful to check the availability of the car park for a given date
    public function dailyAvailability($date) {
        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
            return $this->handleError('Invalid date format');
        }

        $query = "SELECT * FROM bookings WHERE 
                  (:date BETWEEN date_from AND date_to) AND 
                  booking_status = 'booked'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':date' => $date]);  
        $occupiedSpaces = $stmt->rowCount();  
    
        // Calculate the number of available spaces by subtracting occupied spaces from max capacity
        $availableSpaces = $this->max_capacity - $occupiedSpaces;
    
        // Return the available spaces count for the specified date in an associative array
        return $this->formatResponse('success', 'Availability retrieved successfully', ['date' => $date, 'available_spaces' => $availableSpaces]);
    }
    
    public function getPricing($date_from, $date_to) {
        $startDate = new \DateTime($date_from);
        $endDate = new \DateTime($date_to);
        $weekdayCount = 0;
        $weekendCount = 0;
    
        // Count weekdays and weekends
        for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
            if ($date->format('N') >= 6) {  // 6 = Saturday, 7 = Sunday
                $weekendCount++;
            } else {
                $weekdayCount++;
            }
        }
    
        // Determine season
        $season = (intval($startDate->format('m')) < 6) ? 'winter' : 'summer';
    
        // Prepare the query to fetch prices
        $query = "SELECT day_type, price FROM pricing WHERE season = :season";
        $stmt = $this->conn->prepare($query);

        // Execute the query with the season parameter
        $stmt->execute([':season' => $season]);

        // Fetch the results and organize them into an associative array
        $prices = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $prices[$row['day_type']] = $row['price'];
        }
    
        // Calculate the total price
        $totalPrice = ($weekdayCount * $prices['weekday']) + ($weekendCount * $prices['weekend']);
    
        // Create the result object
        $result = [
            'weekday_count' => $weekdayCount,
            'weekend_count' => $weekendCount,
            'weekday_price' => $prices['weekday'],
            'weekend_price' => $prices['weekend'],
            'total_price' => $totalPrice
        ];
    
        return $this->formatResponse('success', 'Pricing retrieved successfully', $result);
    }
    

    public function createBooking($date_from, $date_to, $car_plate) {
        $availability = $this->checkAvailability($date_from, $date_to)["data"]["available_spaces"];
        if ($availability <= 0) {
            return $this->handleError('No available parking spaces for the selected dates');
        }
        

        $price = $this->getPricing($date_from, $date_to)["data"]['total_price'];
        

        //we have default values for booking_status. We don't need to pass it as a parameter
        //booking_status is set to 'booked' by default
        $query = "INSERT INTO bookings (date_from, date_to, car_plate, price) VALUES (:date_from, :date_to, :car_plate, :price)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':date_from' => $date_from, ':date_to' => $date_to, ':car_plate' => $car_plate, ':price' => $price]);
        
        //check if the booking was created successfully
        if ($stmt->rowCount() <= 0) {
            return $this->handleError('An error occurred while creating the booking');
        }else{
            return $this->formatResponse('success', 'Booking created successfully');
        }
    }

    public function cancelBooking($booking_id) {
        //check if the booking exists and the status is 'booked' using the checkBookingStatus() function
        if ($this->checkBookingStatus($booking_id)["message"] != 'booked') {
            return $this->handleError('Booking does not exist or has already been cancelled');
        }

        $query = "UPDATE bookings SET booking_status = 'cancelled' WHERE id = :booking_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':booking_id' => $booking_id]);
        //check if the booking was cancelled successfully
        if ($stmt->rowCount() <= 0) {
            return $this->handleError('An error occurred while cancelling the booking'); 
        }else{
            return $this->formatResponse('success', 'Booking cancelled successfully');
        }

    }

    //create a function to check booking status
    public function checkBookingStatus($booking_id) {
        $query = "SELECT booking_status FROM bookings WHERE id = :booking_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':booking_id' => $booking_id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($stmt->rowCount() <= 0) {
            return $this->handleError('Booking does not exist');
        }
        return $this->formatResponse('success', $row['booking_status']);

    }
    public function amendBooking($booking_id, $new_date_from, $new_date_to) {

        //we assume checkAvailability() and getPricing() are already called before this function to inform the user of the new price and availability
        //but just to be safe, we will call checkAvailability() again to make sure the new dates are available while amending the booking
        $availability = $this->checkAvailability($new_date_from, $new_date_to)["data"]["available_spaces"];
        if ($availability <= 0) {
            return $this->handleError('No available parking spaces for the selected dates');
        }

        $price = $this->getPricing($new_date_from, $new_date_to)["data"]['total_price'];
        $query = "UPDATE bookings SET date_from = :new_date_from, date_to = :new_date_to, price = :price WHERE id = :booking_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':new_date_from' => $new_date_from, ':new_date_to' => $new_date_to, ':price' => $price, ':booking_id' => $booking_id]);

        //check if the booking was amended successfully
        if ($stmt->rowCount() <= 0) {
            return $this->handleError('An error occurred while amending the booking');
        }else{
            return $this->formatResponse('success', 'Booking amended successfully');
        }

    }

    public function formatResponse($status, $message, $data = null) {
        $return = ['status' => $status, 'message' => $message];
        if ($data) {
            $return['data'] = $data;
        }
        return $return;
    }
    public function handleError($message, $code = 400) {
        return $this->formatResponse('error', $message);
    }
    
}
?>
