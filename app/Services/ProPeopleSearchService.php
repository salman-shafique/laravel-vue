<?php
namespace App\Services;

use  App\Services\CurlService;

class ProPeopleSearchService
{

    protected $baseURL = 'https://propeoplesearch.com/';
    protected $formURL = 'https://propeoplesearch.com/ld/people/default/processing';
    protected $resultsURL = 'https://propeoplesearch.com/ld/people/default/loading-results';

    public $curlService;
    public $query = ['firstName' => '', 'lastName' => '', 'city' => '', 'state' => '', '_token' => ''];
    public $cookie = null;

    public function __construct(CurlService $curlService)
    {
        $this->curlService = $curlService;

    }

    /**
     * Load main page of the website
     * @return array
     */
    public function getUsers($firstName, $lastName)
    {
        $this->query['firstName'] = $firstName;
        $this->query['lastName'] = $lastName;
        return $this->loadSearchPage();
    }

    /**
     * Load main page of the website
     * @return array
     */
    public function loadSearchPage()
    {
        $html = $this->curlService->get($this->baseURL);
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        // Get the form csrf token
        $list = $xpath->query('//*[@id="mainForm"]/input[1]');
        foreach ($list as $li) {
            if (!empty($li->getAttribute('value'))) {
                $this->query['_token'] = $li->getAttribute('value');
            };
        }
        if (empty($this->query['_token'])) {
            return ['error' => true, 'message' => 'Token not found'];
        };
        return $this->submitForm();
    }

    public function submitForm()
    {
        $headers = $this->curlService->responseHeaders;
        $this->curlService->addHeader('cookie:' . explode(';', $headers['Set-Cookie'])[0] . ';' . explode(';', $headers['set-cookie'])[0]);
        $this->curlService->post($this->formURL, $this->query);
        $headers = $this->curlService->responseHeaders;
        /**
         * @var  $curl CurlService
         */
        $curl = $this->curlService->getInstance();
        $curl->addHeader('cookie:' . explode(';', $headers['Set-Cookie'])[0] . ';' . explode(';', $headers['set-cookie'])[0]);
        $resultsHtml = $curl->get($this->resultsURL);
        return $this->parseData($resultsHtml);
    }

    /**
     * Extracts the person information from the html
     * @return array
     */
    public function parseData($html)
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $items = $xpath->query('//*[@id="app"]/div/div[2]/div/div/ul/li');
        $counter = 0;
        $peopleData = [];
        foreach ($items as $item) {
            // skip the table headers
            if ($counter == 0) {
                ++$counter;
                continue;
            }
            ++$counter;
            $peopleData[] = [
                'first_name' => $item->getAttribute('data-firstname'),
                'last_name' => $item->getAttribute('data-lastname'),
                'age' => $item->getAttribute('data-age'),
                'address' => $item->getAttribute('data-location'),
            ];
        }
        return $peopleData;
    }

    /**
     * Adds scoring
     */
    public function addScores($persons)
    {
        return $persons->map(function ($item, $key) {
            $item['score'] = $this->calculateScore($item);
            $item['full_name'] = $item['first_name'] . ' ' . $item['last_name'];
            return $item;
        });
    }

    /**
     * calculates score based on age and address
     */
    public function calculateScore($person)
    {
        $ageScore = 0;
        if ($person['age'] >= 20 && $person['age'] <= 30) {
            $ageScore = 3;
        } else if ($person['age'] >= 31 && $person['age'] <= 40) {
            $ageScore = 5;
        } elseif ($person['age'] >= 41 && $person['age'] <= 50) {
            $ageScore = 7;
        } elseif ($person['age'] >= 51 && $person['age'] <= 60) {
            $ageScore = 10;
        } else {
            $ageScore = 1;
        }
        $addressScore = 0;
        if (strpos(strtolower($person['address']), 'po box')) {
            $addressScore = 1;
        }
        if (strpos(strtolower($person['address']), 'street')) {
            $addressScore += 2;
        }
        if (strpos(strtolower($person['address']), 'st')) {
            $addressScore += 3;
        }
        return ($ageScore + $addressScore) / 2;
    }
}

?>
