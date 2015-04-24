<?php
/**
 * The address class.
 */
class Address {
    private $stateCode;
    private $postalCode;
    private $countryCode;

    /**
     * Indicate if the address is a residential location. Default is @c false.
     * @var boolean
     */
    private $isResidential = false;

    public function getStateCode() {
        return $this->stateCode;
    }

    public function getPostalCode() {
        return $this->postalCode;
    }

    public function getCountryCode() {
        return $this->countryCode;
    }

    public function isResidential() {
        return $this->isResidential;
    }

    public function setStateCode($stateCode) {
        $this->stateCode = $stateCode;
    }

    public function setPostalCode($postalCode){
        $this->postalCode = $postalCode;
    }

    public function setCountryCode($countryCode){
        $this->countryCode = $countryCode;
    }

    public function setResidentialIndicator($isResidential) {
        $this->isResidential = $isResidential;
    }
}
