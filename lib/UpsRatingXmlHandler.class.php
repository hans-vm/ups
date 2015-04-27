<?php
require_once('UpsException.class.php');

/**
 * The class to parse the UPS Rating API response.
 */
class UpsRatingXmlHandler {
    /**
     * The full xPath to the current element.
     * @var array
     */
    private $currentPath = array();

    /**
     * The list of shipping rates calculated for all UPS services. An array of an associative array with following keys:
     *  - {string} service: The code of UPS service type.
     *  - {string} currency: The currency code.
     *  - {float} amount: The rate.
     * @var array
     */
    private $rates = array();

    /**
     * The response status code. 1 for success, 0 for failure.
     * @var integer
     */
    private $statusCode = 0;

    /**
     * The description of error occured.
     * @var string
     */
    private $errorDescription = '';

    /**
     * XML character data handler.
     */
    public function characterData($parser, $data) {
        $tag = implode('/', $this->currentPath);
        switch ($tag) {
            case 'RATINGSERVICESELECTIONRESPONSE/RATEDSHIPMENT/SERVICE/CODE':
                $this->rates[] = array(
                    'service' => $data
                );
                break;
            case 'RATINGSERVICESELECTIONRESPONSE/RATEDSHIPMENT/TOTALCHARGES/CURRENCYCODE':
                $this->rates[count($this->rates) - 1]['currency'] = $data;
                break;
            case 'RATINGSERVICESELECTIONRESPONSE/RATEDSHIPMENT/TOTALCHARGES/MONETARYVALUE':
                $this->rates[count($this->rates) - 1]['amount'] = (float)$data;
                break;
            case 'RATINGSERVICESELECTIONRESPONSE/RESPONSE/RESPONSESTATUSCODE':
                $this->statusCode = (int)$data;
                break;
            case 'RATINGSERVICESELECTIONRESPONSE/RESPONSE/ERROR/ERRORDESCRIPTION':
                $this->errorDescription = $data;
                break;
        }
    }

    /**
     * XML start element handler.
     */
    public function startElement($parser, $name, $attrs) {
        array_push($this->currentPath, $name);
    }

    /**
     * XML end element handler.
     */
    public function endElement($parser, $name) {
        array_pop($this->currentPath);
    }

    /**
     * Retrieve the list of shipping rates for all UPS services.
     * @return array An array of an associative array with following keys:
     *  - {string} service: The code of UPS service type.
     *  - {string} currency: The currency code.
     *  - {float} amount: The rate.
     * @throws UpsException on error.
     */
    public function getRates() {
        if (!$this->statusCode) {
            throw new UpsException($this->errorDescription);
        }
        return $this->rates;
    }
}
