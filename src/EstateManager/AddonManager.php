<?php

namespace ContaoEstateManager\PropertyNotifier\EstateManager;

use Contao\Config;
use Contao\Environment;
use ContaoEstateManager\EstateManager;

class AddonManager
{
    /**
     * Bundle name
     * @var string
     */
    public static $bundle = 'EstateManagerPropertyNotifier';

    /**
     * Package
     * @var string
     */
    public static $package = 'contao-estatemanager/property-notifier';

    /**
     * Addon config key
     * @var string
     */
    public static $key  = 'addon_property_notifier_license';

    /**
     * Is initialized
     * @var boolean
     */
    public static $initialized  = false;

    /**
     * Is valid
     * @var boolean
     */
    public static $valid  = false;

    /**
     * Licenses
     * @var array
     */
    private static $licenses = [
        'b5db0620b68110e6adcc10c0deb19e62',
        'fada906ac482df85f2e59e017d2bc682',
        'd813a3ee68af2e98f764aa86b671d2dc',
        '87348ed1d90a07a6fddd432bf882bcfc',
        '9c5102c6a2d9013ca7d3a31562a73314',
        '465662570fbec62663f48e861e045459',
        '7c67f7dba6fa7819933d0a6bc19f56fe',
        'fb81a76e994a308310ae196dbca9498f',
        'f301b219782824964504b2a100e781f3',
        '6525617af7c09e43ef141356a220bc6c',
        'c946e04534d0811f44eef38286e03674',
        '8d7b4ebd695ec255afb54c82ab002607',
        'ce3b665a2fc232d46a4209429683a9fc',
        'aa4f8abca7355b23bd917afba3bdcb46',
        '24fb32725e49b76e2e19b6f8b1f0ccee',
        '993d7955a374f13ba44bde609e152cdf',
        '1b6713f20615e2d59bdff03efa9c240c',
        'a07c39ee9d484a601d0ffd5a10994535',
        '6a842293da8cfa1dcccbc4f325f92960',
        '7494f76a53d488e88667227d980bd85a',
        '000b8f85ea4271c2f4c26642ab7007a9',
        '65928cdc3861ee2eccf7ab121032858c',
        '5d6b67ed8fe5aba63d064f8dcb2cf299',
        '846f9036559765a860c5dbe8883c702b',
        'fe60f475dfe1fafbef5c346e47adff43',
        '92a3fae35cca2fd922fedfc01640ecbd',
        '056dc745f96b7cf4680695d6d73b0ea5',
        '740c14ce92dedef47f14015cce17a41f',
        'd44fa1d1e8d43fd71c2a2d731e27052e',
        'caa33152443fe955388589c60a2fdede',
        'a7c6f0ef3a073e2acc2c7e9444742b40',
        '6ccca07bf63ad71c868c13a97c226a5c',
        '764d577987140a64a9242efb4e6a7686',
        'cb33bd78ceb710a940caa58fea74eeb6',
        '19f40fd4b5bc83e0a5f7686c9cb42cad',
        '83185c3bc285f58f2a36b1ddd5f041d0',
        '3d09668094574d700c2e045e802724a9',
        '41520d436bb04585e7612ea76b2931c4',
        'db269c71ab2413825c3090cef58c6631',
        '4cfc82fac876d4ede3475afd97e83d6c',
        '0ac7f6d8e5cffaae3f105b05382a2070',
        '210c18c566e304434c0fb9ee69b43bdb',
        '86f9c88a70e1cadd8c515adfb69ee2a8',
        '3c3234ee606cd6d2ea1508a0dbecabfd',
        'f8e21f41a6c74e51129b1d47df8f0c30',
        '0cfc5d65f29e592d80c9d3314a361575',
        'ebeb91f9deed279e2dc1ea42e0a59fff',
        'b74cb0561d0a9248758ebb0f21c2080c',
        'be5a13e63a3bd043d8ad1a67718da2ad',
        'ca2b2efe75830ae38fa700d1dfa8cbe2'
    ];

    public static function getLicenses()
    {
        return static::$licenses;
    }

    public static function valid()
    {
        if (false !== strpos(Environment::get('requestUri'), '/contao/install'))
        {
            return true;
        }

        if (false === static::$initialized)
        {
            static::$valid = EstateManager::checkLicenses(Config::get(static::$key), static::$licenses, static::$key);
            static::$initialized = true;
        }

        return static::$valid;
    }
}
