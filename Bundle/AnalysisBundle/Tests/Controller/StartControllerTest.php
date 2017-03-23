<?php

namespace Gold\Bundle\AnalysisBundle\Tests\Controller;

//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Gold\Bundle\AnalysisBundle\Controller\StartController;
use PHPUnit\Framework\TestCase;

class StartControllerTest extends TestCase
{
    /*public function testGetDateAction()
    {
        $analizer = new StartController();
        $result = $analizer->makeGreateExchanges(1);

        // assert that your calculator added the numbers correctly!
        //$this->assertEquals(42, $result);
    }*/
    
    public function testCallNbpApi()
    {
        $datesPeriod['start'] = '2017-02-20'; 
        $datesPeriod['end'] = '2017-02-21';
        $controller = new StartController();
        $result = $controller->callNbpApi($datesPeriod);
        $this->assertEquals('[{"data":"2017-02-20","cena":162.49},{"data":"2017-02-21","cena":161.87}]', $result);
    }
    
    public function testCollectDataFromApi() {
        $datesPeriod['start'] = '2017-02-20'; 
        $datesPeriod['end'] = '2017-02-21';
        $resultArray = array (
                '2017-02-20' => 162.49,
                '2017-02-21' => 161.87,
            );
        $controller = new StartController();
        $result = $controller->CollectDataFromApi($datesPeriod);
        $this->assertEquals($resultArray, $result);
    }
    
    public function testGetGoldPriseHistory() {
        $controller = new StartController();
        $result = $controller->getGoldPriseHistory(1);
        $this->assertInternalType('array', $result);
    }
    
    public function testGetDateAction() {
        $controller = new StartController();
        $result = $controller->getDateAction(1);
        $this->assertInternalType('string', $result);
    }

    public function testOrganizeTransactionDates() {
        $data = Array (
            '2017-02-20' => 200,
            '2017-02-21' => 300,
            '2017-02-22' => 50,
            '2017-02-23' => 100,
            '2017-02-24' => 50,
            '2017-02-25' => 100,
            '2017-02-26' => 200,
            '2017-02-27' => 10,
            '2017-02-28' => 5,
            '2017-02-28' => 2,
            '2017-02-28' => 300,
        );
        $controller = new StartController();
        $result = $controller->organizeTransactionDates($data);
        //print_r ($result);
        $this->assertInternalType('array', $result);
        $this->assertEquals(1300, array_sum($result));
    }
    
    
    
    
}
