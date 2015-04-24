<?php
require_once('UpsException.class.php');

/**
 * The class to parse the UPS Shipping Confirm API response.
 */
class UpsConfirmXmlHandler {
    /**
     * The current tag name.
     * @var string
     */
    private $currentTag = '';

    /**
     * Encoded shipment parameters required to be passed in the accept phase.
     * @var string
     */
    private $digest = '';

    /**
     * Returned UPS shipment ID number.
     * @var string
     */
    private $trackingNumber = '';

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
        switch ($this->currentTag) {
            case 'SHIPMENTIDENTIFICATIONNUMBER':
                $this->trackingNumber = $data;
                break;
            case 'RESPONSESTATUSCODE':
                $this->statusCode = (int)$data;
                break;
            case 'ERRORDESCRIPTION':
                $this->errorDescription = $data;
                break;
            case 'SHIPMENTDIGEST':
                $this->digest .= $data;
                break;
        }
    }

    /**
     * XML start element handler.
     */
    public function startElement($parser, $name, $attrs) {
        $this->currentTag = $name;
    }

    /**
     * XML end element handler.
     */
    public function endElement($parser, $name) {
        $this->currentTag = '';
    }

    /**
     * Retrieve the encoded shipment parameters required to be passed in the accept phase.
     * @return string
     * @throws UpsException on error.
     */
    public function getShipmentDigest() {
        if (!$this->statusCode) {
            throw new UpsException($this->errorDescription);
        }
        return $this->digest;
    }

    /**
     * Get the UPS tracking number.
     * @return string
     * @throws UpsException on error.
     */
    public function getTrackingNumber() {
        if (!$this->statusCode) {
            throw new UpsException($this->errorDescription);
        }
        return $this->trackingNumber;
    }
}
