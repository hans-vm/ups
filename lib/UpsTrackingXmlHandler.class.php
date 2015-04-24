<?php
require_once('UpsException.class.php');

/**
 * The class to parse the UPS Tracking API response.
 */
class UpsTrackingXmlHandler {
    const NODE_NAME_ACTIVITY      = 'ACTIVITY';
    const NODE_NAME_ERROR         = 'ERROR';
    // The tracking response root node.
    const NODE_NAME_TRACKRESPONSE = 'TRACKRESPONSE';

    public $activity = array();
    public $activityCount = 0;
    public $charData = array();
    public $error = array();

    /**
     * Indicate if an error occurs.
     * @var boolean
     */
    private $errorOccurred = false;
    private $isTrackingResponse = false;

    private $currentTag;
    private $depth = 0;
    private $inActivity = false;
    private $inError;
    private $lastStatus = "NA";
    private $path = array();

    /**
     * XML character data handler.
     */
    public function characterData($parser, $data) {
        if ($this->inActivity) {
            $this->activity[$this->activityCount][implode("/", $this->path)] = $data;

            $xpath = implode('/', $this->path);

            if (($xpath == 'PACKAGE/ACTIVITY/STATUS/STATUSTYPE/DESCRIPTION') && ($this->lastStatus == "NA")) {
                $this->lastStatus = $data;
            }
        } else {
            if ($this->inError) {
                $this->error[$this->currentTag] = $data;
            }

            $this->charData[implode("/", $this->path)] = $data;
        }
    }

    /**
     * XML start element handler.
     */
    public function startElement($parser, $name, $attrs) {
        $this->currentTag = $name;
        if ($this->depth > 1) {
            array_push($this->path, $name);
        }

        switch ($name) {
        case self::NODE_NAME_ACTIVITY:
            $activity[] = array();
            $this->activityCount++;
            $this->inActivity = true;
            break;
        case self::NODE_NAME_ERROR:
            $this->inError = true;
            $this->errorOccurred = true;
            break;
        case self::NODE_NAME_TRACKRESPONSE:
            $this->isTrackingResponse = true;
            break;
        }

        $this->depth++;
    }

    /**
     * XML end element handler.
     */
    public function endElement($parser, $name) {
        $this->currentTag = '';

        if ($this->depth > 1) {
            array_pop($this->path);
        }

        switch ($name) {
        case self::NODE_NAME_ACTIVITY:
            $this->inActivity = false;
            break;
        case self::NODE_NAME_ERROR:
            $this->inError = false;
            break;
        }

        $this->depth--;
    }

    /**
     * Get the last tracking status.
     * @return string
     * @throws Exception on error.
     */
    public function getLastStatus() {
        if ($this->errorOccurred) {
            $errorMessage = 'Could not retrieve tracking information.';
            if ($this->isTrackingResponse) {
                $errorMessage = $this->error['ERRORDESCRIPTION'];
            }
            throw new UpsException($errorMessage);
        }
        return $this->lastStatus;
    }
}
