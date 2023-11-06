<?php
require 'vendor/autoload.php';
use CarParkAPI\CarPark; 
use CarParkAPI\Database; 
use CarParkAPI\Utils\DatabaseHandler;
use PHPUnit\Framework\TestCase;

class CarParkTest extends TestCase
{
    private $db;
    private $carPark;
    private $dbHandlerMock;

    protected function setUp(): void
    {
        $this->dbHandlerMock = $this->getMockBuilder(DatabaseHandler::class)
                                     ->disableOriginalConstructor()
                                     ->getMock();

      
        $this->dbHandlerMock->method('checkAvailability')
                            ->willReturn(1);
        $this->dbHandlerMock->method('getPricing')
                            ->willReturn(["weekday_count"=>8,"weekend_count"=>2,"weekday_price"=>"20.00","weekend_price"=>"25.00","total_price"=>210]);
        $this->dbHandlerMock->method('cancelBooking')
                            ->willReturn(["status"=>"success","message"=>"Booking cancelled successfully"]);

        $this->carPark = new CarPark($this->dbHandlerMock);

    }


    public function testCheckAvailability()
    {
          $result = $this->carPark->checkAvailability('2025-11-01', '2025-11-10');
          $this->assertEquals(9, $result["data"]['available_spaces']);
    }

    public function testGetPricing()
    {
        $result = $this->carPark->getPricing("2025-11-01","2025-11-10");
        $this->assertEquals(210, $result["data"]["pricing"]["total_price"]);
    }
    public function testCreateBooking()
    {

        $this->dbHandlerMock->method('insertBooking')
        ->willReturn( ["status"=>"success","message"=>"Booking created successfully","data"=>["booking_id"=>"34"]]);
        $result = $this->carPark->createBooking('2025-04-01', '2025-04-10', 'ABC123');
      
        $this->assertEquals('success', $result['status']);
    }

    public function testCancelBooking()
    {
        $this->dbHandlerMock->method('getBookingStatus')
        ->willReturn(['status' => 'success', 'data' => ['booking_status' => 'booked']]);
        $this->dbHandlerMock->method('cancelBooking')
        ->willReturn(['status' => 'success', 'message' => "Booking cancelled successfully"]); 

      
        $cancelResult = $this->carPark->cancelBooking(9);
        $this->assertEquals('success', $cancelResult['status']);
    }

    public function testAmendBooking()
    {
        $this->dbHandlerMock->method('checkAvailability')
        ->willReturn(2);
        $this->dbHandlerMock->method('updateBooking')
        ->willReturn(['status' => 'success', 'message' => "Booking amended successfully"]); 
        $amendResult = $this->carPark->amendBooking(23, '2025-04-02', '2025-04-07');
        $this->assertEquals('success', $amendResult['status']);
    }

    protected function tearDown(): void
    {
        $this->db = null;
        $this->carPark = null;


    }
}
?>
