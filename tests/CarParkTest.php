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

    /**
     * @dataProvider availabilityDataProvider
     */
    public function testCheckAvailability($from, $to, $expected)
    {
        $result = $this->carPark->checkAvailability($from, $to);
        $this->assertEquals($expected, $result['data']['available_spaces']);
    }

    public function availabilityDataProvider()
    {
        return [
            ['2023-11-01', '2023-11-10', 0]
           
        ];
    }

    /**
     * @dataProvider pricingDataProvider
     */
    public function testGetPricing($from, $to, $expected)
    {
        $result = $this->carPark->getPricing($from, $to)['data'];
        $this->assertEquals($expected, $result);
    }

    public function pricingDataProvider()
    {
        return [
            ['2023-11-01', '2023-11-10', [
                'weekday_count' => 7,
                'weekend_count' => 3,
                'weekday_price' => 10,
                'weekend_price' => 15,
                'total_price' => 105
            ]],
     
        ];
    }

    public function testCreateBooking()
    {
        $result = $this->carPark->createBooking('2023-11-01', '2023-11-10', 'ABC123');
        $this->assertEquals('success', $result['status']);
    }

    public function testCancelBooking()
    {
        $createResult = $this->carPark->createBooking('2023-11-01', '2023-11-10', 'ABC123');
        $bookingId = $createResult['data']['booking_id'];
        $cancelResult = $this->carPark->cancelBooking($bookingId);
        $this->assertEquals('success', $cancelResult['status']);
    }

    public function testAmendBooking()
    {
        $createResult = $this->carPark->createBooking('2023-11-01', '2023-11-10', 'ABC123');
        $bookingId = $createResult['data']['booking_id'];
        $amendResult = $this->carPark->amendBooking($bookingId, '2023-11-02', '2023-11-09');
        $this->assertEquals('success', $amendResult['status']);
    }

    protected function tearDown(): void
    {
        $this->db = null;
        $this->carPark = null;


    }
}
?>
