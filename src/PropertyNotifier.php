<?php

namespace ContaoEstateManager\PropertyNotifier;

use Contao\Config;
use Contao\FrontendTemplate;
use Contao\MemberModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use ContaoEstateManager\EstateManager\EstateManager\PropertyFragment\PropertyFragmentBuilder;
use ContaoEstateManager\EstateManager\EstateManager\PropertyFragment\Provider\ExpressionPropertyFragmentProvider;
use ContaoEstateManager\Filter;
use ContaoEstateManager\PropertyNotifier\Cron\PropertyNotifierCron;
use ContaoEstateManager\PropertyNotifier\Model\PropertyNotifierModel;
use ContaoEstateManager\RealEstate;
use ContaoEstateManager\RealEstateModel;
use ContaoEstateManager\RealEstateType;
use ContaoEstateManager\RealEstateTypeModel;
use RRule\RRule;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;

class PropertyNotifier
{
    /**
     * Check properties to be notified
     */
    public function getPropertiesToNotify(): ?array
    {
        $collection = null;

        // Find all real estate records that have been added since the last cron run
        $objRealEstates = RealEstateModel::findBy([
            'published=?',
            'dateAdded>=?'
        ], [1, PropertyNotifierCron::getLastRun()]);

        // Start time
        $time = time();

        if(null !== $objRealEstates && $objNotifiers = PropertyNotifierModel::findAll())
        {
            foreach ($objNotifiers as $objNotifier)
            {
                if(!$this->shouldRunNotifier($objNotifier))
                {
                    continue;
                }

                if($matches = $this->match($objRealEstates, $objNotifier))
                {
                    $collection[] = [
                        $objNotifier,
                        $matches
                    ];
                }
            }
        }

        // Update last cron run time
        PropertyNotifierCron::setLastRun($time);

        return $collection;
    }

    /**
     * Check if a notifier record should be executed
     */
    public function shouldRunNotifier($objNotifier): bool
    {
        $time    = time();
        $dtStart = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
        $until   = strtotime("+2 day", $dtStart);

        try{
            // Get RRules parts from string
            $parts = RRule::parseRfcString($objNotifier->interval);

            // Set start date
            $parts['dtstart'] =  date("Y-m-d H:m:i", $dtStart);

            // Set end date
            $parts['until'] = date("Y-m-d H:m:i", $until);

            // Create RRule object
            $rrule = new RRule($parts);
        } catch (\Exception $e){
            return false;
        }

        return count($rrule->getOccurrencesBetween((int) $objNotifier->sentOn, $time)) >= 1;
    }

    /**
     * Check if notifier and real estates match
     */
    public function match($objRealEstates, $objNotifier): ?array
    {
        $realEstates = null;
        $notifierObjType = null;
        $properties =  StringUtil::deserialize($objNotifier->properties, true);

        // Create expression language object and necessary methods
        $expressionLanguage = new ExpressionLanguage();
        $expressionLanguage->addFunction(ExpressionFunction::fromPhp('strpos'));
        $expressionLanguage->addFunction(ExpressionFunction::fromPhp('strlen'));
        $expressionLanguage->addFunction(ExpressionFunction::fromPhp('substr_compare'));

        // Create parameter bag
        $notifierProperties = new ParameterBag($properties);

        // Get the object type if one was selected
        if($objTypeId = $notifierProperties->get(Filter::PROPERTY_TYPE_KEY))
        {
            /** @var RealEstateTypeModel $realEstateType */
            $notifierObjType = RealEstateType::getInstance()->getTypeById($objTypeId);
        }

        // HOOK: add custom expression language logic
        if (isset($GLOBALS['CEM_HOOKS']['beforeMatchPropertyNotifier']) && \is_array($GLOBALS['CEM_HOOKS']['beforeMatchPropertyNotifier']))
        {
            foreach ($GLOBALS['CEM_HOOKS']['beforeMatchPropertyNotifier'] as $callback)
            {
                System::importStatic($callback[0])->{$callback[1]}($notifierProperties, $notifierObjType, $expressionLanguage);
            }
        }

        // Max amount of properties
        $max  = Config::get('propertyNotifierMaxCount') ?: null;
        $count = 0;

        // Generating fragments for the compliance check
        $fragment = new PropertyFragmentBuilder($notifierProperties, new ExpressionPropertyFragmentProvider());
        $fragment->setObjType($notifierObjType);
        $fragment->applyMultiple([
            PropertyFragmentBuilder::FRAGMENT_BASIC,
            PropertyFragmentBuilder::FRAGMENT_COUNTRY,
            PropertyFragmentBuilder::FRAGMENT_LANGUAGE,
            PropertyFragmentBuilder::FRAGMENT_LOCATION,
            PropertyFragmentBuilder::FRAGMENT_PRICE,
            PropertyFragmentBuilder::FRAGMENT_AREA,
            PropertyFragmentBuilder::FRAGMENT_PERIOD,
            PropertyFragmentBuilder::FRAGMENT_ROOM
        ]);

        $expressionRows = $fragment->generate();

        // Check if the notifier data match with a real estate
        foreach ($objRealEstates as $objRealEstate)
        {
            /** @var RealEstateTypeModel $realEstateType */
            $objType = RealEstateType::getInstance()->getTypeByRealEstate($objRealEstate);

            // Skip properties that cannot be assigned to a unique object type
            if(null === $objType)
            {
                continue;
            }

            foreach ($expressionRows as $row)
            {
                if(!$expressionLanguage->evaluate($row, $objRealEstate->row()))
                {
                    continue 2;
                }
            }

            // Property matches the search object and is included in the collection
            $realEstates[] = $objRealEstate;

            if($max && ++$count === $max)
            {
                break;
            }
        }

        return $realEstates;
    }

    /**
     * Parse notifier properties
     */
    public function parseNotifierProperties($properties)
    {
        /** @var TranslatorInterface $translator */
        $translator = System::getContainer()->get('translator');
        $properties = StringUtil::deserialize($properties, true);

        foreach ($properties as $key => $value)
        {
            switch ($key)
            {
                case Filter::MARKETING_TYPE_KEY:
                    $properties[$key] = [
                        'label' => $translator->trans('tl_real_estate_filter.marketingType', [], 'contao_default'),
                        'value' => $translator->trans('tl_real_estate_filter.' . $value, [], 'contao_default')
                    ];
                    break;

                case Filter::PROPERTY_TYPE_KEY:
                    // Determine object type
                    if($objType = RealEstateTypeModel::findById($value))
                    {
                        $properties[$key] = [
                            'label' => $translator->trans('tl_real_estate_filter.realEstateType', [], 'contao_default'),
                            'value' => $objType->title ?? ''
                        ];
                    }
                    break;

                default:

                    if($this->strStartsWith($key, 'price'))
                    {
                        $value = number_format((float) $value, 0, Config::get('numberFormatDecimals'), Config::get('numberFormatThousands')) . ' &' . Config::get('defaultCurrency') . ';';
                    }

                    if($this->strStartsWith($key, 'area'))
                    {
                        $value = number_format((float) $value, 2, Config::get('numberFormatDecimals'), Config::get('numberFormatThousands')) . ' m²';
                    }

                    $properties[$key] = [
                        'label' => $translator->trans('tl_real_estate_filter.' . $key, [], 'contao_default'),
                        'value' => $value
                    ];
            }
        }

        // HOOK: add custom logic
        if (isset($GLOBALS['CEM_HOOKS']['parseNotifierProperties']) && \is_array($GLOBALS['CEM_HOOKS']['parseNotifierProperties']))
        {
            foreach ($GLOBALS['CEM_HOOKS']['parseNotifierProperties'] as $callback)
            {
                System::importStatic($callback[0])->{$callback[1]}($properties);
            }
        }

        return $properties;
    }

    /**
     * Return simple token
     */
    public function getSimpleToken(PropertyNotifierModel $objNotifier, ?array $arrRealEstates = null)
    {
        /** @var TranslatorInterface $translator */
        $translator = System::getContainer()->get('translator');

        // Parse notifier properties
        $properties = $this->parseNotifierProperties($objNotifier->properties);

        // Default token
        $token = [
            'notifier_created_date'     => date(Config::get('dateFormat'), $objNotifier->tstamp),
            'notifier_created_time'     => date(Config::get('timeFormat'), $objNotifier->tstamp),
            'notifier_properties_raw'   => implode("\n", array_map(fn($props) => $props['label'] . ': ' . $props['value'], $properties)),
            'notifier_properties_html'  => implode("<br/>", array_map(fn($props) => $props['label'] . ': ' . $props['value'], $properties)),
            'notifier_list_link'        => '',
            'notifier_delete_link'      => ''
        ];

        // Set list link
        if($objResultListPage = PageModel::findById(Config::get('propertyNotifierRealEstatePage')))
        {
            $token['notifier_list_link'] = $objResultListPage->getAbsoluteUrl();
        }

        // Set delete link
        if($objDeletePage = PageModel::findById(Config::get('propertyNotifierDeletePage')))
        {
            $token['notifier_delete_link'] = $objDeletePage->getAbsoluteUrl() . '?act=' . $objNotifier->hash;
        }

        // Set human-readable interval string
        try{
            $token['notifier_interval'] = RRule::createFromRfcString($objNotifier->interval)->humanReadable([
                'dtstart' => false,
                'include_start' => false,
                'include_until' => false
            ]);
        } catch (\Exception $e){
            $token['notifier_interval'] = $translator->trans('tl_property_notifier.NEVER', [], 'contao_default');
        }

        // Set member data
        if(!$member = $objNotifier->getRelated('member'))
        {
            if(!$member = MemberModel::findByEmail($objNotifier->email))
            {
                // If no member can be determined, a member record with the email address
                // is simulated to always be accessible via `member_email`
                $member = new MemberModel();
                $member->email = $objNotifier->email;
            }
        }

        if(null !== $member)
        {
            $arrMember = $member->row();

            $token = array_merge(
                $token,
                array_combine(
                    array_map(fn($key) => 'member_' . $key, array_keys($arrMember)),
                    array_values($arrMember)
                )
            );
        }

        // Set real estates
        if(null !== $arrRealEstates)
        {
            $template = new FrontendTemplate('nc_real_estates');
            $template->exposePage = PageModel::findById(Config::get('propertyNotifierExposePage'));
            $template->realEstates = array_map(fn($re) => new RealEstate($re), $arrRealEstates);

            $token['real_estates'] = $template->parse();
        }

        // HOOK: add custom logic
        if (isset($GLOBALS['CEM_HOOKS']['propertyNotifierSimpleToken']) && \is_array($GLOBALS['CEM_HOOKS']['propertyNotifierSimpleToken']))
        {
            foreach ($GLOBALS['CEM_HOOKS']['propertyNotifierSimpleToken'] as $callback)
            {
                System::importStatic($callback[0])->{$callback[1]}($token, $objNotifier, $arrRealEstates);
            }
        }

        return $token;
    }

    /**
     * @deprecated If Contao only allows PHP 8 and above, this function is replaced with str_starts_with
     */
    public function strStartsWith($haystack, $needle): bool
    {
        if (!function_exists('str_starts_with')) {
            return str_starts_with($haystack, $needle);
        }

        return strpos($haystack, $needle) === 0;
    }
}
