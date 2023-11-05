<?php
require 'vendor/autoload.php';
use CarParkAPI\CarPark; 
use CarParkAPI\Database; 
use PHPUnit\Framework\TestCase;

class CarParkTest extends TestCase
{
    private $db;
    private $carPark;

    protected function setUp(): void
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->carPark = new CarPark($this->db);
    }

    public function testCheckAvailability()
    {
        $availableSpaces = $this->carPark->checkAvailability('2023-11-01', '2023-11-10');
        $this->assertIsInt($availableSpaces);
    }

    public function testGetPricing()
    {
        $pricing = $this->carPark->getPricing('2023-11-01', '2023-11-10');
        
        //output $pricing in the console
        print_r($pricing);
        $this->assertIsArray($pricing);
        $this->assertArrayHasKey('weekday_price', $pricing);
        $this->assertArrayHasKey('weekend_price', $pricing);
        $this->assertArrayHasKey('weekday_count', $pricing);
        $this->assertArrayHasKey('weekend_count', $pricing);
        $this->assertArrayHasKey('total_price', $pricing);

    }

    public function testCreateBooking()
    {
        $bookingId = $this->carPark->createBooking('2023-11-01', '2023-11-10', 'ABC123');
        $this->assertIsInt($bookingId);
    }

    public function testCancelBooking()
    {
        $bookingId = $this->carPark->createBooking('2023-11-01', '2023-11-10', 'ABC123');
        $rowsAffected = $this->carPark->cancelBooking($bookingId);
        $this->assertEquals(1, $rowsAffected);
    }

    public function testAmendBooking()
    {
        $bookingId = $this->carPark->createBooking('2023-11-01', '2023-11-10', 'ABC123');
        $rowsAffected = $this->carPark->amendBooking($bookingId, '2023-11-02', '2023-11-09');
        $this->assertEquals(1, $rowsAffected);
    }

    protected function tearDown(): void
    {
        $this->db = null;
        $this->carPark = null;
    }
}
?>
