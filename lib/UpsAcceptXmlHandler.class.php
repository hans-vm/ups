<?php
/**
 * The class to parse the UPS Shipping Accept API response.
 */
class UpsAcceptXmlHandler {
    public $error;
    public $label;

    private $totalCharge = 0;

    private $currentTag;
    private $depth = 0;
    private $path = array();

    // === Public Methods === //

    public function characterData($parser, $data) {
        $xpath = implode('/', $this->path);
        switch ($xpath) {
        case 'SHIPMENTCHARGES/TOTALCHARGES/MONETARYVALUE':
            $this->totalCharge = $data;
            break;
        case 'ERROR/ERRORDESCRIPTION':
            $this->error = $data;
            break;
        case 'PACKAGERESULTS/LABELIMAGE/GRAPHICIMAGE':
            $this->label = $data;
            break;
        }
    }

    public function endElement($parser, $name) {
        $this->currentTag = '';

        if ($this->depth > 1) {
            array_pop($this->path);
        }

        $this->depth--;
    }

    /**
     * Get the total charge of shipments.
     * @return float
     */
    public function getTotalCharge() {
        return (float)$this->totalCharge;
    }

    public function startElement($parser, $name, $attrs) {
        $this->currentTag = $name;

        if ($this->depth > 1) {
            array_push($this->path, $name);
        }

        $this->depth++;
    }
}
