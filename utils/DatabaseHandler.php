<?php

namespace CarParkAPI\Utils;

class DatabaseHandler {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    public function beginTransaction() {
        if (!$this->conn->inTransaction()) {
            $this->conn->beginTransaction();
        }
    }
    public function rollBack() {
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }
    }
    public function commit() {
        if ($this->conn->inTransaction()) {
            $this->conn->commit();
        }
    }
    public function checkAvailability($from, $to) {
        $query = "SELECT *
                  FROM bookings
                  WHERE (
                      (date_from BETWEEN :from AND :to) OR
                      (date_to BETWEEN :from AND :to) OR
                      (date_from <= :from AND date_to >= :to)
                  ) AND booking_status = 'booked'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':from' => $from, ':to' => $to]);
        return $stmt->rowCount();
    }

    public function getDailyAvailability($date) {

        $query = "SELECT * FROM bookings WHERE 
                  (:date BETWEEN date_from AND date_to) AND 
                  booking_status = 'booked'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':date' => $date]);  
        $occupiedSpaces = $stmt->rowCount();  

        return $occupiedSpaces;
    }

    public function insertBooking($date_from, $date_to, $car_plate, $price) {
        try {
            $this->beginTransaction();
            $query = "INSERT INTO bookings (date_from, date_to, car_plate, price, booking_status) VALUES (:date_from, :date_to, :car_plate, :price, 'booked')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':date_from' => $date_from, ':date_to' => $date_to, ':car_plate' => $car_plate, ':price' => $price]);
            $bookingId = $this->conn->lastInsertId();
            $this->commit();
            return $bookingId;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function cancelBooking($booking_id) {
        try {
            $this->beginTransaction();
            $query = "UPDATE bookings SET booking_status = 'cancelled' WHERE id = :booking_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':booking_id' => $booking_id]);
            $affectedRows = $stmt->rowCount();
            $this->commit();
            return $affectedRows;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function updateBooking($booking_id, $new_date_from, $new_date_to, $price) {
        try {
            $this->beginTransaction();
            $query = "UPDATE bookings SET date_from = :new_date_from, date_to = :new_date_to, price = :price WHERE id = :booking_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':new_date_from' => $new_date_from, ':new_date_to' => $new_date_to, ':price' => $price, ':booking_id' => $booking_id]);
            $affectedRows = $stmt->rowCount();
            $this->commit();
            return $affectedRows;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function getBookingStatus($booking_id) {
        $query = "SELECT booking_status FROM bookings WHERE id = :booking_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':booking_id' => $booking_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
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
        $season = (intval($startDate->format('m')) > 3 && intval($startDate->format('m')) < 9) ? 'summer' : 'winter';
        
        // Prepare the query to fetch prices
        $query = "SELECT day_type, price FROM pricing WHERE season = :season";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':season' => $season]);
        
        // Fetch the results and organize them into an associative array
        $prices = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $prices[$row['day_type']] = $row['price'];
        }
        
        // Calculate the total price
        $totalPrice = ($weekdayCount * $prices['weekday']) + ($weekendCount * $prices['weekend']);
        
        return [
            'weekday_count' => $weekdayCount,
            'weekend_count' => $weekendCount,
            'weekday_price' => $prices['weekday'],
            'weekend_price' => $prices['weekend'],
            'total_price' => $totalPrice
        ];
    }

 
}

?>
