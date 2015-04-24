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
     * The shipping rate calculated. An associative array with following keys:
     *  - {string} currency: The currency code.
     *  - {float} amount: The rate.
     * @var array
     */
    private $rate = array();

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
            case 'RATINGSERVICESELECTIONRESPONSE/RATEDSHIPMENT/TOTALCHARGES/CURRENCYCODE':
                $this->rate['currency'] = $data;
                break;
            case 'RATINGSERVICESELECTIONRESPONSE/RATEDSHIPMENT/TOTALCHARGES/MONETARYVALUE':
                $this->rate['amount'] = (float)$data;
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
     * Retrieve the shipping rate.
     * @return array An associative array with following keys:
     *  - {string} currency: The currency code.
     *  - {float} amount: The rate.
     * @throws UpsException on error.
     */
    public function getRate() {
        if (!$this->statusCode) {
            throw new UpsException($this->errorDescription);
        }
        return $this->rate;
    }
}
