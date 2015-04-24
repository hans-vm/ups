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

    // UPS service types for international shipments.
    const TYPE_STANDARD = '11';
    const TYPE_WORLDWIDE_EXPRESS = '07';
    const TYPE_WORLDWIDE_EXPRESS_PLUS = '54';
    const TYPE_WORLDWIDE_EXPEDITED = '08';

    // UPS pickup types.
    const PICKUP_TYPE_DAILY_PICKUP = '01';
    const PICKUP_TYPE_CUSTOMER_COUNTER = '03';
    const PICKUP_TYPE_ONE_TIME_PICKUP = '06';
    const PICKUP_TYPE_ON_CALL_AIR = '07';
    const PICKUP_TYPE_LETTER_CENTER = '19';
    const PICKUP_TYPE_AIR_SERVICE_CENTER = '20';

    // Unit of measurement for package weight.
    const WEIGHT_UNIT_LBS = 'LBS';
    const WEIGHT_UNIT_KGS = 'KGS';

    // Unit of measurement for package dimension.
    const DIMENSION_UNIT_INCH = 'IN';
    const DIMENSION_UNIT_CENTIMETER = 'CM';
}
