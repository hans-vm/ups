<?php
/**
 * The class to parse the UPS Shipping Confirm API response.
 */
class UpsConfirmXmlHandler {
    public $digest;
    public $error;
    public $price;
    public $trackingNumber;

    private $currentTag;

    public function characterData($parser, $data) {
        switch ($this->currentTag) {
        case 'SHIPMENTIDENTIFICATIONNUMBER':
            $this->trackingNumber = $data;
            break;
        case 'MONETARYVALUE':
            $this->price = $data;
            break;
        case 'ERRORDESCRIPTION':
            $this->error = $data;
            break;
        case 'SHIPMENTDIGEST':
            $this->digest .= $data;
            break;
        }
    }

    public function endElement($parser, $name) {
        $this->currentTag = '';
    }

    public function startElement($parser, $name, $attrs) {
        $this->currentTag = $name;
    }
}
