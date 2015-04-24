<?php
/**
 * The address class.
 */
class Address {
    /**
     * The full name of contact person.
     * @var string
     */
    private $contact;
    private $address1;
    private $address2 = '';
    private $city;
    private $stateCode;
    private $postalCode;
    private $countryCode;

    /**
     * Indicate if the address is a residential location. Default is @c false.
     * @var boolean
     */
    private $isResidential = false;

    public function getContact() {
        return $this->contact;
    }

    public function getAddress1() {
        return $this->address1;
    }

    public function getAddress2() {
        return $this->address2;
    }

    public function getCity() {
        return $this->city;
    }

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

    public function setContact($contact) {
        $this->contact = $contact;
    }

    public function setAddress1($address1) {
        $this->address1 = $address1;
    }

    public function setAddress2($address2) {
        $this->address2 = $address2;
    }

    public function setCity($city) {
        $this->city = $city;
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
