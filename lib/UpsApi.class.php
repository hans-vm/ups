<?php
/**
 * The base class for UPS API wrappers.
 */
abstract class UpsApi {
    /**
     * UPS Access License Number.
     * @var string
     */
    private $accessLicenseNumber;

    /**
     * UPS user ID.
     * @var string
     */
    private $userId;

    /**
     * UPS password.
     * @var string
     */
    private $password;

    /**
     * UPS Shipper Number.
     * @var string
     */
    private $shipperNumber;

    /**
     * The UPS demo mode. Default is @c false.
     * @var boolean
     */
    private $isDemo = false;

    /**
     * Set the UPS account details.
     * @param string $accessLicenseNumber
     * @param string $userId
     * @param string $password
     * @param string $shipperNumber
     */
    public function setUpsAccount($accessLicenseNumber, $userId, $password, $shipperNumber) {
        $this->accessLicenseNumber = $accessLicenseNumber;
        $this->userId = $userId;
        $this->password = $password;
        $this->shipperNumber = $shipperNumber;
    }

    /**
     * Get the demo mode.
     * @return boolean
     */
    public function isDemoMode() {
        return $this->isDemo;
    }

    /**
     * Set the demo mode.
     * @param boolean $isDemo Default is @c true.
     */
    public function setDemoMode($isDemo = true) {
        $this->isDemo = $isDemo;
    }

    /**
     * Get the UPS Shipper Number.
     * @return string
     */
    protected function getShipperNumber() {
        return $this->shipperNumber;
    }

    /**
     * Generate UPS AccessRequest XML document.
     * @return string The request XML.
     */
    protected function getAccessRequest() {
        $xml = new XMLWriter();
        // Use memory for string output.
        $xml->openMemory();
        $xml->startDocument();
        $xml->startElement('AccessRequest');
                $xml->writeAttribute('xml:lang', 'en-US');
                $xml->writeElement('AccessLicenseNumber', $this->accessLicenseNumber);
                $xml->writeElement('UserId', $this->userId);
                $xml->writeElement('Password', $this->password);
            $xml->endElement();
        $xml->endDocument();
        return $xml->outputMemory();
    }

    /**
     * Call UPS API.
     * @param string $url
     * @param string $request XML for API request.
     * @return string The response XML.
     * @throws Exception on error.
     */
    protected function callApi($url, $request) {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            // In debug mode, also track the request headers, so that we can include them in the debug information.
            CURLINFO_HEADER_OUT => $this->isDemoMode(),

            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_TIMEOUT => 60,

            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => dirname(__FILE__) . '/cacert.pem',
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_HTTPHEADER => array(
                'Content-Length: ' . strlen($request)
            )
        );

        $ch = curl_init();
        if (!$ch) {
            throw new Exception('Could not initiate the cURL instance.');
        }

        if (!curl_setopt_array($ch, $options)) {
            throw new Exception('Could not set the cURL options.');
        }
        $result = curl_exec($ch);
        if ($result === false) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);

        // Split a raw HTTP response string into headers and data.
        $length = mb_strlen($result);
        $start = 0;
        while (mb_substr($result, $start, 7) == 'HTTP/1.') {
            // Find the position just after the end of the headers (where the separator starts).
            $separatorStart = mb_strpos($result, "\r\n\r\n", $start);
            // Nothing after the headers?
            if ($separatorStart === false) {
                $separatorStart = $length;
            }
            // Skip the separator characters.
            $start = $separatorStart + 4;
        }

        // Extract the data portion.
        $data = ($start > 0) ? mb_substr($result, $start) : $result;

        return $data;
    }

    /**
     * Parse API response.
     * @param string $response
     * @param object $xmlHandler One of @c UpsAcceptXmlHandler, @c UpsConfirmXmlHandler or @c UpsTrackingXmlHandler.
     */
    protected function parseResponse($response, $xmlHandler) {
        // Initialize parser.
        $xmlParser = xml_parser_create();

        // Set callback functions.
        xml_set_object($xmlParser, $xmlHandler);
        xml_set_element_handler($xmlParser, 'startElement', 'endElement');
        xml_set_character_data_handler($xmlParser, 'characterData');

        xml_parse($xmlParser, $response);

        // Clean up.
        xml_parser_free($xmlParser);
    }
}
