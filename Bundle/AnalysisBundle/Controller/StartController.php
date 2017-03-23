<?php

namespace Gold\Bundle\AnalysisBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StartController extends Controller {

    private $maxAvaliableNbpPeriode = 1;
    private $startAmountOfManey = 600000;
    private $nbpApiUrl = 'http://api.nbp.pl/api/cenyzlota/';

    /**
     * Analyze gold price and render result
     * @param int $years
     * @return view
     */
    public function analyzeAction($years) {
        $yearsAmount = (int) $years;
        $goldPriseHistory = $this->getGoldPriseHistory($yearsAmount);
        $transactionDiary = $this->makeGreateExchanges($goldPriseHistory);
        return $this->render('GoldAnalysisBundle:Default:result.html.twig', [
            'transactions'  => $transactionDiary        
        ]);
    }
    /**
     * Prepear transctions data 
     * @param array $goldPriseHistory
     * @return array
     */
    public function makeGreateExchanges($goldPriseHistory) {
        $transactionDiary = [];
        $i = 0;
        $moneyAmount = $this->startAmountOfManey;
        $goldAmount = 0;
        $extremes = $this->findRightTransactionDates($goldPriseHistory);

        foreach ($extremes as $date => $price) {
            if (0 === $i % 2) {
                //buy
                $goldAmount = $moneyAmount / $price;
                $moneyAmount = 0;
                $transactionDiary[] = ['date' => $date, 'operationType' => 'buy', 'goldPrice' => $price, 'goldAmount' => $goldAmount, 'moneyAmount' => $moneyAmount, 'earnd' => $moneyAmount - $this->startAmountOfManey
                ];
            } else {
                //sell
                $moneyAmount = $goldAmount * $price;
                $goldAmount = 0;
                $transactionDiary[] = ['date' => $date, 'operationType' => 'sell', 'goldPrice' => $price, 'goldAmount' => 0, 'moneyAmount' => $goldAmount * $price, 'earnd' => $moneyAmount - $this->startAmountOfManey
                ];
            }
            $i++;
        }
        return $transactionDiary;
    }
    /**
     * Finding best moments to sell and buy gold
     * @param array $prices
     * @return array
     */
    private function findRightTransactionDates($prices) {
        $peaks = array();
        $valleys = array();

        $price = array_values($prices);
        $dates = array_keys($prices);

        for ($i = 1; $i < count($prices) - 1; $i++) {
            ($price[$i] > $price[$i - 1] && $price[$i] > $price[$i + 1]) ? $peaks[$dates[$i]] = $price[$i] :
                            ($price[$i] < $price[$i - 1] && $price[$i] < $price[$i + 1]) ? $valleys[$dates[$i]] = $price[$i] : false;
        }

        $transactionDates = array_merge($peaks, $valleys);
        ksort($transactionDates);
        $sortPrices = array_values($transactionDates);
        if ($sortPrices[0] > $sortPrices[1]) {
            array_shift($transactionDates);
        }
        $organizedTransactionDates = $this->organizeTransactionDates($transactionDates);
        

        return $organizedTransactionDates;
    }
    /**
     * Removes the extrems causing transaction problems
     * @param type $transactionDates
     * @return type
     */
    public function organizeTransactionDates($transactionDates) {
        $modify = true;
        $dates = array_keys($transactionDates);
        while ($modify) {
            $i = 0;
            $modify = false;
            foreach ($transactionDates as $date => $price) {
                if ((0 == $i%2)) {
                    //kupuj
                    //echo $i . ' ';
                    if ($transactionDates[$dates[$i + 1]] <= $price) {
                        unset($transactionDates[$dates[$i + 1]]);
                        unset($dates[$i + 1]);
                        $dates = array_values($dates);
                        $modify = true;
                        //echo 'break ';
                        break;
                    }
                } else {
                    //sprzedaj
                    //echo $i . ' ';
                    if ($transactionDates[$dates[$i - 1]] >= $price) {
                        unset($transactionDates[$dates[$i - 1]]);
                        unset($dates[$i - 1]);
                        $dates = array_values($dates);
                        $modify = true;
                        //echo 'break ';
                        break;
                    }
                }
                
                $i++;
            }
            if ($i == count($transactionDates)-1) {
                $modify = false;
            }
        }
        return $transactionDates;
    }
    
    /**
     * Get date for start of analyse
     * @param int $years
     * @return date
     */
    public function getDateAction($years) {
        return date('Y-m-d', strtotime('-' . $years . ' year'));
    }
    
    /**
     * Merging data from API
     * @param int $years
     * @return array
     */
    public function getGoldPriseHistory($years) {
        $collectedData = [];
        for ($i = 0; $i < $years; $i++) {
            $startDate = $this->getDateAction($years - $i);
            $endDate = date('Y-m-d', strtotime($this->getDateAction($years - $i - 1) . '-1 day'));
            $datesPeriod = ['start' => $startDate, 'end' => $endDate];
            $apiDataResultArray = $this->collectDataFromApi($datesPeriod);
            $collectedData = array_merge($collectedData, $apiDataResultArray);
        }
        return $collectedData;
    }
    
    /**
     * Collect Data from nbp API
     * @param array $datesPeriod
     * @return array
     */
    public function collectDataFromApi($datesPeriod) {
        $apiResponse = $this->callNbpApi($datesPeriod);
        $data = json_decode($apiResponse, true);
        if (empty($data)) {
            return [];
        }
        $dates = array_map(function($data) {
            return $data['data'];
        }, $data);
        $prises = array_map(function($data) {
            return $data['cena'];
        }, $data);
        $dataCombined = array_combine($dates, $prises);

        return $dataCombined;
    }
    
    /**
     * Call MBP API for single response
     * @param array $datesPeriod
     * @return object
     */
    public function callNbpApi($datesPeriod) {
        $url = $this->nbpApiUrl . '/' . $datesPeriod['start'] . '/' . $datesPeriod['end'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        return curl_exec($ch);
    }

}
