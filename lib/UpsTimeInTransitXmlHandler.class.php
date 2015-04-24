<?php
require_once('UpsException.class.php');

/**
 * The class to parse the UPS Time In Transit API response.
 */
class UpsTimeInTransitXmlHandler {
    /**
     * The full xPath to the current element.
     * @var array
     */
    private $currentPath = array();

    /**
     * The list of services available for delivery.
     * @var array
     */
    private $service = array();

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
            case 'TIMEINTRANSITRESPONSE/RESPONSE/RESPONSESTATUSCODE':
                $this->statusCode = (int)$data;
                break;
            case 'TIMEINTRANSITRESPONSE/RESPONSE/ERROR/ERRORDESCRIPTION':
                $this->errorDescription = $data;
                break;
            case 'TIMEINTRANSITRESPONSE/TRANSITRESPONSE/SERVICESUMMARY/SERVICE/CODE':
                $this->service[] = array(
                    'code' => $data
                );
                break;
            case 'TIMEINTRANSITRESPONSE/TRANSITRESPONSE/SERVICESUMMARY/SERVICE/DESCRIPTION':
                $this->service[count($this->service) - 1]['description'] = $data;
                break;
            case 'TIMEINTRANSITRESPONSE/TRANSITRESPONSE/SERVICESUMMARY/GUARANTEED/CODE':
                $this->service[count($this->service) - 1]['guaranteed'] = $data === 'Y';
                break;
            case 'TIMEINTRANSITRESPONSE/TRANSITRESPONSE/SERVICESUMMARY/ESTIMATEDARRIVAL/BUSINESSTRANSITDAYS':
                $this->service[count($this->service) - 1]['days'] = $data;
                break;
            case 'TIMEINTRANSITRESPONSE/TRANSITRESPONSE/SERVICESUMMARY/ESTIMATEDARRIVAL/TIME':
                $this->service[count($this->service) - 1]['time'] = $data;
                break;
            case 'TIMEINTRANSITRESPONSE/TRANSITRESPONSE/SERVICESUMMARY/ESTIMATEDARRIVAL/PICKUPDATE':
                $this->service[count($this->service) - 1]['pickup-date'] = $data;
                break;
            case 'TIMEINTRANSITRESPONSE/TRANSITRESPONSE/SERVICESUMMARY/ESTIMATEDARRIVAL/PICKUPTIME':
                $this->service[count($this->service) - 1]['pickup-time'] = $data;
                break;
            case 'TIMEINTRANSITRESPONSE/TRANSITRESPONSE/SERVICESUMMARY/ESTIMATEDARRIVAL/DATE':
                $this->service[count($this->service) - 1]['date'] = $data;
                break;
            case 'TIMEINTRANSITRESPONSE/TRANSITRESPONSE/SERVICESUMMARY/ESTIMATEDARRIVAL/DAYOFWEEK':
                $this->service[count($this->service) - 1]['day-of-week'] = $data;
                break;
            case 'TIMEINTRANSITRESPONSE/TRANSITRESPONSE/SERVICESUMMARY/ESTIMATEDARRIVAL/CUSTOMERCENTERCUTOFF':
                $this->service[count($this->service) - 1]['customer-service'] = $data;
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
     * Get the list of services available for delivery.
     * @return array
     * @throws UpsException on error.
     */
    public function getService() {
        if (!$this->statusCode) {
            throw new UpsException($this->errorDescription);
        }
        return $this->service;
    }
}
