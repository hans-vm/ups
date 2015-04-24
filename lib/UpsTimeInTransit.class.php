<?php
require_once('Address.class.php');
require_once('UpsApi.class.php');
require_once('UpsConstants.class.php');
require_once('UpsException.class.php');
require_once('UpsTimeInTransitXmlHandler.class.php');

/**
 * UPS Time In Transit API wrapper.
 */
class UpsTimeInTransit extends UpsApi {
    const URL_TIME_IN_TRANSIT_DEMO = 'https://wwwcie.ups.com/ups.app/xml/TimeInTransit';
    const URL_TIME_IN_TRANSIT_LIVE = 'https://onlinetools.ups.com/ups.app/xml/TimeInTransit';

    /**
     * The from address.
     * @var Address
     */
    private $fromAddress;

    /**
     * The shipping address.
     * @var Address
     */
    private $shippingAddress;

    /**
     * The package weight in lbs. Default is 1 lbs.
     * @var float
     */
    private $weight = 1.0;

    /**
     * The UNIX timestamp of pickup date.
     * @var int
     */
    private $pickupDate;

    /**
     * Retrieve the delivery information.
     * @return array An array of service summary.
     */
    public function getDeliveryInformation() {
        // Get the UPS Access Request XML.
        $request = $this->getAccessRequest();

        // Compose TimeInTransitRequest XML document.
        $xml = new XMLWriter();
        // Use memory for string output.
        $xml->openMemory();

        $xml->startDocument();
            $xml->startElement('TimeInTransitRequest');
                $xml->writeAttribute('xml:lang', 'en-US');
                $xml->startElement('Request');
                    $xml->startElement('TransactionReference');
                        $xml->writeElement('CustomerContext', 'TNT_D Origin Country Code');
                        $xml->writeElement('XpciVersion', '1.0002');
                    $xml->endElement();
                    $xml->writeElement('RequestAction', 'TimeInTransit');
                $xml->endElement();
                $xml->startElement('TransitFrom');
                    $xml->startElement('AddressArtifactFormat');
                        $xml->writeElement('PoliticalDivision2', $this->fromAddress->getCity());
                        $xml->writeElement('PoliticalDivision1', $this->fromAddress->getStateCode());
                        $xml->writeElement('CountryCode', $this->fromAddress->getCountryCode());
                        $xml->writeElement('PostcodePrimaryLow', $this->fromAddress->getPostalCode());
                    $xml->endElement();
                $xml->endElement();
                $xml->startElement('TransitTo');
                    $xml->startElement('AddressArtifactFormat');
                        $xml->writeElement('PoliticalDivision2', $this->shippingAddress->getCity());
                        $xml->writeElement('PoliticalDivision1', $this->shippingAddress->getStateCode());
                        $xml->writeElement('CountryCode', $this->shippingAddress->getCountryCode());
                        $xml->writeElement('PostcodePrimaryLow', $this->shippingAddress->getPostalCode());
                        $xml->writeElement('PostcodePrimaryHigh', $this->shippingAddress->getPostalCode());
                        if ($this->shippingAddress->isResidential()) {
                            $xml->writeElement('ResidentialAddressIndicator');
                        }
                    $xml->endElement();
                $xml->endElement();
                $xml->startElement('ShipmentWeight');
                    $xml->startElement('UnitOfMeasurement');
                        $xml->writeElement('Code', 'LBS');
                    $xml->endElement();
                    $xml->writeElement('Weight', $this->weight);
                $xml->endElement();
                $xml->writeElement('PickupDate', date('Y', $this->pickupDate) . date('m', $this->pickupDate) . date('d', $this->pickupDate));
            $xml->endElement();
        $xml->endDocument();

        $request .= $xml->outputMemory();

        // Call the UPS Tracking API.
        $url = $this->isDemoMode() ? self::URL_TIME_IN_TRANSIT_DEMO : self::URL_TIME_IN_TRANSIT_LIVE;
        $response = $this->callApi($url, $request);

        // Parse the API response.
        $handler = new UpsTimeInTransitXmlHandler();
        $this->parseResponse($response, $handler);
        return $handler->getService();
    }

    /**
     * Set the from address.
     * @param Address $address
     */
    public function setFromAddress($address) {
        $this->fromAddress = $address;
    }

    /**
     * Set the shipping address.
     * @param Address $address
     */
    public function setShippingAddress($address) {
        $this->shippingAddress = $address;
    }

    /**
     * Set the package weight in lbs.
     * @param float $weight
     */
    public function setWeight($weight) {
        $this->weight = $weight;
    }

    /**
     * Set the pickup date.
     * @param int $pickupDate The UNIX timestamp of pickup date.
     */
    public function setPickupDate($pickupDate) {
        $this->pickupDate = $pickupDate;
    }
}
