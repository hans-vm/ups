<?php
require_once('UpsException.class.php');

/**
 * The class to parse the UPS Shipping Accept API response.
 */
class UpsAcceptXmlHandler {
    /**
     * The full xPath to the current element.
     * @var array
     */
    private $currentPath = array();

    /**
     * Base64 encoded shipping label.
     * @var string
     */
    private $label = '';

    /**
     * The total charge. An associative array with following keys:
     *  - {string} currency: The currency code.
     *  - {float} amount: The charge amount.
     * @var array
     */
    private $totalCharge = array();

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
        case 'SHIPMENTACCEPTRESPONSE/SHIPMENTRESULTS/SHIPMENTCHARGES/TOTALCHARGES/CURRENCYCODE':
            $this->totalCharge['currency'] = $data;
            break;
        case 'SHIPMENTACCEPTRESPONSE/SHIPMENTRESULTS/SHIPMENTCHARGES/TOTALCHARGES/MONETARYVALUE':
            $this->totalCharge['amount'] = (float)$data;
            break;
        case 'SHIPMENTACCEPTRESPONSE/SHIPMENTRESULTS/PACKAGERESULTS/LABELIMAGE/GRAPHICIMAGE':
            $this->label = $data;
            break;
        case 'SHIPMENTACCEPTRESPONSE/RESPONSE/RESPONSESTATUSCODE':
            $this->statusCode = (int)$data;
            break;
        case 'SHIPMENTACCEPTRESPONSE/RESPONSE/ERROR/ERRORDESCRIPTION':
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
     * Get the total charge of shipment.
     * @return array An associative array with following keys:
     *  - {string} currency: The currency code.
     *  - {float} amount: The charge amount.
     * @throws UpsException on error.
     */
    public function getTotalCharge() {
        if (!$this->statusCode) {
            throw new UpsException($this->errorDescription);
        }
        return $this->totalCharge;
    }

    /**
     * Retrieve the shipping label.
     * @return string
     * @throws UpsException on error.
     */
    public function getLabel() {
        if (!$this->statusCode) {
            throw new UpsException($this->errorDescription);
        }
        return $this->label;
    }
}
