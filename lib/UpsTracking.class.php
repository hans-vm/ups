<?php
require_once('Address.class.php');
require_once('UpsApi.class.php');
require_once('UpsConstants.class.php');
require_once('UpsTrackingXmlHandler.class.php');

/**
 * UPS Tracking API wrapper.
 */
class UpsTracking extends UpsApi {
    const URL_TRACK_DEMO = 'https://wwwcie.ups.com/ups.app/xml/Track';
    const URL_TRACK_LIVE = 'https://www.ups.com/ups.app/xml/Track';

    /**
     * Request tracking information.
     * @param string $trackingNumber The shipment tracking number.
     * @return string The last tracking status.
     */
    public function track($trackingNumber) {
        // Get the UPS Access Request XML.
        $request = $this->getAccessRequest();

        // Compose TrackRequest XML document.
        $xml = new XMLWriter();
        // Use memory for string output.
        $xml->openMemory();
        $xml->startDocument();
            $xml->startElement('TrackRequest');
                $xml->writeAttribute('xml:lang', 'en-US');
                $xml->startElement('Request');
                    $xml->startElement('TransactionReference');
                        $xml->writeElement('CustomerContext', 'Package Tracking');
                        $xml->writeElement('XpciVersion', '1.0001');
                    $xml->endElement();
                    $xml->writeElement('RequestAction', 'Track');
                    $xml->writeElement('RequestOption', 'none');
                $xml->endElement();
                $xml->writeElement('TrackingNumber', $trackingNumber);
            $xml->endElement();
        $xml->endDocument();

        $request .= $xml->outputMemory();

        // Call the UPS Tracking API.
        $url = $this->isDemoMode() ? self::URL_TRACK_DEMO : self::URL_TRACK_LIVE;
        try {
            $result = $this->callApi($url, $request);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }

        $handler = new UpsTrackingXmlHandler();
        $this->parseResponse($result, $handler);

        if ($handler->errorOccurred) {
            $errorMessage = 'Could not retrieve tracking information.';
            if ($handler->isTrackingResponse) {
                $errorMessage = $handler->error['ERRORDESCRIPTION'];
            }
            error_log($errorMessage);
            return false;
        }

        return $handler->getLastStatus();
    }
}
