<?php
require_once('Address.class.php');
require_once('UpsApi.class.php');
require_once('UpsConstants.class.php');
require_once('UpsException.class.php');
require_once('UpsRatingXmlHandler.class.php');

/**
 * UPS Rating API wrapper.
 */
class UpsRating extends UpsApi {
    const URL_RATING_DEMO = 'https://wwwcie.ups.com/ups.app/xml/Rate';
    const URL_RATING_LIVE = 'https://www.ups.com/ups.app/xml/Rate';

    /**
     * The UPS pickup type. One of the <tt>UpsConstants::PICKUP_TYPE_X</tt> constants. Default is Daily Pickup.
     * @var string
     */
    private $pickupType = UpsConstants::PICKUP_TYPE_DAILY_PICKUP;

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
     * The UPS package type. One of the <tt>UpsConstants::PACKAGE_TYPE_X</tt> constants.
     * @var string
     */
    private $packageType;

    /**
     * The UPS service type. One of the <tt>UpsConstants::TYPE_X</tt> constants.
     * @var string
     */
    private $serviceType;

    /**
     * The package weight. Default is 1 lbs.
     * @var float
     */
    private $weight = 1.0;

    /**
     * The package length.
     * @var integer
     */
    private $length = 0;

    /**
     * The package width.
     * @var integer
     */
    private $width = 0;

    /**
     * The package height.
     * @var integer
     */
    private $height = 0;

    /**
     * Get the shipping rate from UPS.
     * @return array An associative array with following keys:
     *  - {string} currency: The currency code.
     *  - {float} amount: The rate.
     * @throws UpsException on error.
     */
    public function getRate() {
        // Get the UPS Access Request XML.
        $request = $this->getAccessRequest();

        // Compose the Rating Service Selection Request XML.
        $xml = new XMLWriter();
        // Use memory for string output.
        $xml->openMemory();
        $xml->startDocument();
            $xml->startElement('RatingServiceSelectionRequest');
                $xml->writeAttribute('xml:lang', 'en-US');
                $xml->startElement('Request');
                    $xml->startElement('TransactionReference');
                        $xml->writeElement('CustomerContext', 'Rating and Service');
                        $xml->writeElement('XpciVersion', '1.0001');
                    $xml->endElement();
                    $xml->writeElement('RequestAction', 'Rate');
                    $xml->writeElement('RequestOption', 'Shop');
                $xml->endElement();
                $xml->startElement('PickupType');
                    $xml->writeElement('Code', $this->pickupType);
                $xml->endElement();
                $xml->startElement('Shipment');
                    $xml->startElement('Shipper');
                        $xml->startElement('Address');
                            $xml->writeElement('PostalCode', $this->fromAddress->getPostalCode());
                            $xml->writeElement('CountryCode', $this->fromAddress->getCountryCode());
                            if ($this->fromAddress->isResidential()) {
                                $xml->writeElement('ResidentialAddressIndicator');
                            }
                        $xml->endElement();
                        $xml->writeElement('ShipperNumber', $this->getShipperNumber());
                    $xml->endElement();
                    $xml->startElement('ShipTo');
                        $xml->startElement('Address');
                            $xml->writeElement('StateProvinceCode', $this->shippingAddress->getStateCode());
                            $xml->writeElement('PostalCode', $this->shippingAddress->getPostalCode());
                            $xml->writeElement('CountryCode', $this->shippingAddress->getCountryCode());
                            if ($this->shippingAddress->isResidential()) {
                                $xml->writeElement('ResidentialAddressIndicator');
                            }
                        $xml->endElement();
                    $xml->endElement();
                    $xml->startElement('ShipFrom');
                        $xml->startElement('Address');
                            $xml->writeElement('StateProvinceCode', $this->fromAddress->getStateCode());
                            $xml->writeElement('PostalCode', $this->fromAddress->getPostalCode());
                            $xml->writeElement('CountryCode', $this->fromAddress->getCountryCode());
                        $xml->endElement();
                    $xml->endElement();
                    $xml->startElement('Service');
                        $xml->writeElement('Code', $this->serviceType);
                    $xml->endElement();
                    $xml->startElement('Package');
                        $xml->startElement('PackagingType');
                            $xml->writeElement('Code', $this->packageType);
                        $xml->endElement();
                        $xml->startElement('PackageWeight');
                            $xml->startElement('UnitOfMeasurement');
                                $xml->writeElement('Code', $this->getWeightUnit());
                            $xml->endElement();
                            $xml->writeElement('Weight', $this->weight);
                        $xml->endElement();
                        // If any of the dimensions is unknown, don't send dimensions information.
                        if ($this->length && $this->width && $this->height) {
                            $xml->startElement('Dimensions');
                                $xml->startElement('UnitOfMeasurement');
                                    $xml->writeElement('Code', $this->getDimensionUnit());
                                $xml->endElement();
                                $xml->writeElement('Length', $this->length);
                                $xml->writeElement('Width', $this->width);
                                $xml->writeElement('Height', $this->height);
                            $xml->endElement();
                        }
                    $xml->endElement();
                $xml->endElement();
            $xml->endElement();
        $xml->endDocument();
        $request .= $xml->outputMemory();

        // Call the UPS Rating API.
        $url = $this->isDemoMode() ? self::URL_RATING_DEMO : self::URL_RATING_LIVE;
        $response = $this->callApi($url, $request);

        // Parse the API response.
        $handler = new UpsRatingXmlHandler();
        $this->parseResponse($response, $handler);
        return $handler->getRates();
    }

    /**
     * Set the UPS pickup type.
     * @param string $pickupType One of the <tt>UpsConstants::PICKUP_TYPE_X</tt> constants.
     */
    public function setPickupType($pickupType) {
        $this->pickupType = $pickupType;
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
     * Set the UPS service type.
     * @param string $serviceType One of the <tt>UpsConstants::TYPE_X</tt> constants.
     */
    public function setServiceType($serviceType) {
        $this->serviceType = $serviceType;
    }

    /**
     * Set the UPS package type.
     * @param string $packageType One of the <tt>UpsConstants::PACKAGE_TYPE_X</tt> constants.
     */
    public function setPackageType($packageType) {
        $this->packageType = $packageType;
    }

    /**
     * Set the package weight.
     * @param float $weight
     */
    public function setWeight($weight) {
        $this->weight = $weight;
    }

    /**
     * Set the dimension of package shipped.
     * @param int $length
     * @param int $width
     * @param int $height
     */
    public function setPackageDimension($length, $width, $height) {
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
    }
}
