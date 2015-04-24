<?php
require_once('Address.class.php');
require_once('UpsApi.class.php');
require_once('UpsAcceptXmlHandler.class.php');
require_once('UpsConfirmXmlHandler.class.php');
require_once('UpsConstants.class.php');
require_once('UpsException.class.php');

// The directory where shipping labels are saved as PNG.
define('LABEL_DIRECTORY', 'labels');

/**
 * UPS Shipping API wrapper.
 */
class UpsShipping extends UpsApi {
    const URL_ACCEPT_DEMO = 'https://wwwcie.ups.com/ups.app/xml/ShipAccept';
    const URL_ACCEPT_LIVE = 'https://www.ups.com/ups.app/xml/ShipAccept';
    const URL_CONFIRM_DEMO = 'https://wwwcie.ups.com/ups.app/xml/ShipConfirm';
    const URL_CONFIRM_LIVE = 'https://www.ups.com/ups.app/xml/ShipConfirm';

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
     * The package weight in lbs. Default is 1 lbs.
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
     * Create a new UPS shipment.
     * Save the shipping label with a name of tracking number.
     * @return array An associative array of tracking number and total charge.
     * @throws UpsException on error.
     */
    public function ship() {
        // Get the UPS Access Request XML.
        $request = $this->getAccessRequest();

        // Compose the Shipment Confirm Request XML.
        $xml = new XMLWriter();
        // Use memory for string output.
        $xml->openMemory();
        $xml->startDocument();
            $xml->startElement('ShipmentConfirmRequest');
                $xml->writeAttribute('xml:lang', 'en-US');

                $xml->startElement('Request');
                    $xml->startElement('TransactionReference');
                        $xml->writeElement('CustomerContext', 'ShipConfirm');
                        $xml->writeElement('XpciVersion', '1.0001');
                    $xml->endElement();
                    $xml->writeElement('RequestAction', 'ShipConfirm');
                    $xml->writeElement('RequestOption', 'nonvalidate');
                $xml->endElement();

                $xml->startElement('LabelSpecification');
                    $xml->startElement('LabelPrintMethod');
                        $xml->writeElement('Code', 'GIF');
                    $xml->endElement();
                    $xml->writeElement('HTTPUserAgent', 'Mozilla/5.0');
                    $xml->startElement('LabelImageFormat');
                        $xml->writeElement('Code', 'GIF');
                    $xml->endElement();
                    $xml->startElement('LabelStockSize');
                        $xml->writeElement('Height', '4');
                        $xml->writeElement('Width', '6');
                    $xml->endElement();
                $xml->endElement();

                $xml->startElement('Shipment');
                    $xml->startElement('Shipper');
                        $xml->writeElement('Name', $this->fromAddress->getContact());
                        $xml->writeElement('ShipperNumber', $this->getShipperNumber());
                        $xml->startElement('Address');
                            $xml->writeElement('AddressLine1', $this->fromAddress->getAddress1());
                            $xml->writeElement('AddressLine2', $this->fromAddress->getAddress2());
                            $xml->writeElement('City', $this->fromAddress->getCity());
                            $xml->writeElement('StateProvinceCode', $this->fromAddress->getStateCode());
                            $xml->writeElement('PostalCode', $this->fromAddress->getPostalCode());
                            $xml->writeElement('CountryCode', $this->fromAddress->getCountryCode());
                        $xml->endElement();
                    $xml->endElement();
                    $xml->startElement('ShipFrom');
                        $this->getAddressXML($xml, $this->fromAddress);
                    $xml->endElement();
                    $xml->startElement('ShipTo');
                        $this->getAddressXML($xml, $this->shippingAddress);
                    $xml->endElement();
                    $xml->startElement('PaymentInformation');
                        $xml->startElement('Prepaid');
                            $xml->startElement('BillShipper');
                                $xml->writeElement('AccountNumber', $this->getShipperNumber());
                            $xml->endElement();
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
                            $xml->writeElement('Weight', number_format($this->weight, 2, '.', ''));
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

        // Call the Shipment Confirm API.
        $url = $this->isDemoMode() ? self::URL_CONFIRM_DEMO : self::URL_CONFIRM_LIVE;
        $response = $this->callApi($url, $request);

        // Parse the API response.
        $confirmHandler = new UpsConfirmXmlHandler();
        $this->parseResponse($response, $confirmHandler);
        $shipmentDigest = $confirmHandler->getShipmentDigest();

        // Compose the Shipment Accept Request XML.
        $xml->startDocument();
            $xml->startElement('ShipmentAcceptRequest');
                $xml->writeAttribute('xml:lang', 'en-US');
                $xml->startElement('Request');
                    $xml->startElement('TransactionReference');
                        $xml->writeElement('CustomerContext', 'ShipAccept');
                        $xml->writeElement('XpciVersion', '1.0001');
                    $xml->endElement();
                    $xml->writeElement('RequestAction', 'ShipAccept');
                    $xml->writeElement('RequestOption', '01');
                $xml->endElement();
                $xml->writeElement('ShipmentDigest', $shipmentDigest);
            $xml->endElement();
        $xml->endDocument();

        $request = $this->getAccessRequest() . $xml->outputMemory();

        // Call the Shipment Accept API.
        $url = $this->isDemoMode() ? self::URL_ACCEPT_DEMO : self::URL_ACCEPT_LIVE;
        $response = $this->callApi($url, $request);

        // Parse the acceptance response.
        $acceptHandler = new UpsAcceptXmlHandler();
        $this->parseResponse($response, $acceptHandler);

        // Get and save the shipping label as PNG.
        $label = $this->encodePng($acceptHandler->getLabel());
        if ($label !== false) {
            // Create a directory if not exists.
            if (!file_exists(LABEL_DIRECTORY)) {
                @mkdir(LABEL_DIRECTORY, 0777, true);
            }

            $fp = fopen(LABEL_DIRECTORY . '/' . $confirmHandler->getTrackingNumber() . '.png', 'w');
            if (!$fp) {
                throw new UpsException('Could not open file for write.');
            }

            $written = fwrite($fp, $label);
            if (!$written) {
                throw new UpsException('Could not save the label.');
            }
            fclose($fp);
        }

        return array(
            'trackingNumber' => $confirmHandler->getTrackingNumber(),
            'totalCharge' => $acceptHandler->getTotalCharge()
        );
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
     * Set the package weight in lbs.
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

    /**
     * Append the XML representation of @c Address to the given @c XMLWriter.
     * @param XMLWriter $xml The target XML writer.
     * @param Address $address
     * @return string
     */
    protected function getAddressXML(XMLWriter $xml, Address $address) {
        $xml->writeElement('CompanyName', $address->getContact());
        $xml->startElement('Address');
            $xml->writeElement('AddressLine1', $address->getAddress1());
            $xml->writeElement('AddressLine2', $address->getAddress2());
            $xml->writeElement('City', $address->getCity());
            $xml->writeElement('StateProvinceCode', $address->getStateCode());
            $xml->writeElement('PostalCode', $address->getPostalCode());
            $xml->writeElement('CountryCode', $address->getCountryCode());
        $xml->endElement();
    }

    /**
     * Convert GIF image data to PNG.
     * @param string $data The Base64 encoded GIF image data.
     * @return string|bool The Base64 decoded PNG data if successful, otherwise false.
     */
    protected function encodePng($data) {
        if (is_null($data)) {
            return false;
        }
        $data = base64_decode($data);
        if ($data === false) {
            return false;
        }

        $imageGif = @imagecreatefromstring($data);
        if (!$imageGif) {
            return false;
        }

        imageinterlace($imageGif, 0);
        ob_start();
        imagepng($imageGif, null, 9);
        $encoded = ob_get_contents();
        ob_end_clean();
        imagedestroy($imageGif);

        return $encoded;
    }
}
