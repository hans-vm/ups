<?php
/**
 * The class to parse the UPS Time In Transit API response.
 */
class UpsTimeInTransitXmlHandler {
    private $currentTag = array();

    private $service = array();

    public function characterData($parser, $data) {
        $tag = implode('/', $this->currentTag);
        switch ($tag) {
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

    public function endElement($parser, $name) {
        array_pop($this->currentTag);
    }

    public function startElement($parser, $name, $attrs) {
        array_push($this->currentTag, $name);
    }

    public function getService() {
        return $this->service;
    }
}
