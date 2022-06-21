<?php

declare(strict_types=1);

namespace ContaoEstateManager\PropertyNotifier\Controller\FrontendModule;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Contao\ModuleModel;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\User;
use ContaoEstateManager\PropertyNotifier\Model\PropertyNotifierModel;
use ContaoEstateManager\RealEstateTypeModel;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RRule\RRule;

/**
 * @FrontendModule(type=PropertyNotifierListController::TYPE, category="estatemanager")
 */
class PropertyNotifierListController extends AbstractFrontendModuleController
{
    /**
     * Frontend Module Type
     */
    public const TYPE = 'property_notifier_list';

    /**
     * Translator
     */
    private TranslatorInterface $translator;

    /**
     * Request
     */
    protected Request $request;

    /**
     * Request
     */
    protected ModuleModel $model;

    /**
     * User
     */
    protected ?User $user;

    /**
     * Create Frontend Module
     */
    public function __construct(TranslatorInterface $translator)
    {
        // Load language file
        Controller::loadLanguageFile('tl_property_notifier');
        Controller::loadLanguageFile('tl_real_estate_filter');

        $this->translator = $translator;
    }

    /**
     * Return the template
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $this->model = $model;
        $this->request = $request;

        // Get user instance
        $this->user = Controller::getContainer()->get('security.helper')->getUser();

        // Set template vars
        $template->list = $this->list();

        return new Response($template->parse());
    }

    /**
     * List all notifier items
     */
    protected function list(): array
    {
        if(!$objRecords = PropertyNotifierModel::findByMember($this->user))
        {
            return [];
        }

        $records = [];
        $editPage = $this->model->jumpTo ? $this->model->getRelated('jumpTo') : null;

        foreach ($objRecords as $record)
        {
            $template = new FrontendTemplate($this->model->notifierItemTpl ?: 'notifier_item_default');
            $template->setData($record->row());

            $template->intervalLabel = $this->translator->trans('tl_property_notifier.intervalLabel', [], 'contao_default');
            $template->editLabel = $this->translator->trans('tl_property_notifier.editLabel', [], 'contao_default');
            $template->deleteLabel = $this->translator->trans('tl_property_notifier.deleteLabel', [], 'contao_default');
            $template->createdAtLabel = $this->translator->trans('tl_property_notifier.createdAtLabel', [], 'contao_default');

            $template->humanReadableDate = date(Config::get('datimFormat'), (int) $record->tstamp);

            $properties = [];

            // Default property validating
            foreach (StringUtil::deserialize($record->properties, true) as $key => $value)
            {
                switch ($key)
                {
                    case 'marketing-type':
                        $properties[$key] = [
                            'label' => $this->translator->trans('tl_real_estate_filter.marketingType', [], 'contao_default'),
                            'value' => $this->translator->trans('tl_real_estate_filter.' . $value, [], 'contao_default')
                        ];
                        break;

                    case 'real-estate-type':
                        // Determine object type
                        $objType = RealEstateTypeModel::findById($value);

                        $properties[$key] = [
                            'label' => $this->translator->trans('tl_real_estate_filter.realEstateType', [], 'contao_default'),
                            'value' => $objType->title ?? ''
                        ];
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
                            'label' => $this->translator->trans('tl_real_estate_filter.' . $key, [], 'contao_default'),
                            'value' => $value
                        ];
                }
            }

            $template->properties = $properties;

            try{
                $template->humanReadableInterval = RRule::createFromRfcString($record->interval)->humanReadable([
                    'dtstart' => false,
                    'include_start' => false,
                    'include_until' => false
                ]);
            } catch (\Exception $e){
                $template->humanReadableInterval = $this->translator->trans('tl_property_notifier.NEVER', [], 'contao_default');
            }

            if($this->model->jumpTo)
            {
                /** @var PageModel $editPage */
                $template->editLink = $editPage->getAbsoluteUrl() . '?notifierId=' . $record->id;
            }

            // HOOK: add custom logic
            if (isset($GLOBALS['CEM_HOOKS']['beforeParseNotifierItemTemplate']) && \is_array($GLOBALS['CEM_HOOKS']['beforeParseNotifierItemTemplate']))
            {
                foreach ($GLOBALS['CEM_HOOKS']['beforeParseNotifierItemTemplate'] as $callback)
                {
                    System::importStatic($callback[0])->{$callback[1]}($template, $this->model);
                }
            }

            $records[] = $template->parse();
        }

        return $records;
    }

    /**
     * @deprecated If Contao only allows PHP 8 and above, this function is replaced with str_starts_with
     */
    protected function strStartsWith($haystack, $needle): bool
    {
        if (!function_exists('str_starts_with')) {
            return str_starts_with($haystack, $needle);
        }

        return strpos($haystack, $needle) === 0;
    }
}
