<?php
/**
 * The class for UPS constants.
 */
class UpsConstants {
    // UPS package types.
    const PACKAGE_TYPE_10KG_BOX = '25';
    const PACKAGE_TYPE_25KG_BOX = '24';
    const PACKAGE_TYPE_EXPRESS_BOX = '21';
    const PACKAGE_TYPE_EXPRESS_BOX_LARGE = '2c';
    const PACKAGE_TYPE_EXPRESS_BOX_MEDIUM = '2b';
    const PACKAGE_TYPE_EXPRESS_BOX_SMALL = '2a';
    const PACKAGE_TYPE_EXPRESS_PAK = '04';
    const PACKAGE_TYPE_LETTER = '01';
    const PACKAGE_TYPE_TUBE = '03';
    const PACKAGE_TYPE_YOUR_PACKAGING = '02';

    // UPS service types.
    const TYPE_GROUND = '03';
    const TYPE_NEXT_DAY_AIR = '01';
    const TYPE_NEXT_DAY_AIR_EARLY_AM = '14';
    const TYPE_NEXT_DAY_AIR_SAVER = '13';
    const TYPE_2ND_DAY_AIR = '02';
    const TYPE_2ND_DAY_AIR_AM = '59';
    const TYPE_3DAY_SELECT = '12';
}
