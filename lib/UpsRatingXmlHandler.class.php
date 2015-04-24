<?php
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
     */
    public function getRate() {
        return $this->rate;
    }
}
