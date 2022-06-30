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
use ContaoEstateManager\Filter;
use ContaoEstateManager\PropertyNotifier\Model\PropertyNotifierModel;
use ContaoEstateManager\PropertyNotifier\PropertyNotifier;
use ContaoEstateManager\RealEstateTypeModel;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
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
    protected TranslatorInterface $translator;

    /**
     * Translator
     */
    protected PropertyNotifier $propertyNotifier;

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
     * Flashbag
     */
    protected ?FlashBagInterface $flashBag;

    /**
     * Create Frontend Module
     */
    public function __construct(TranslatorInterface $translator, PropertyNotifier $propertyNotifier)
    {
        // Load language file
        Controller::loadLanguageFile('tl_property_notifier');
        Controller::loadLanguageFile('tl_real_estate_filter');

        $this->translator = $translator;
        $this->propertyNotifier = $propertyNotifier;
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

        // Get flash bag
        $this->flashBag = $request->getSession()->getFlashBag();

        // Check whether a record is to be deleted
        $this->deleteAction();

        // Set template vars
        $template->list = $this->list();

        if ($request->getSession()->isStarted() && $this->flashBag->has(self::TYPE))
        {
            $arrMessages = $this->flashBag->get(self::TYPE);
            $template->message = $arrMessages[0];
        }

        return new Response($template->parse());
    }

    /**
     * List all notifier items
     */
    protected function list(): array
    {
        if(!$objRecords = PropertyNotifierModel::findByMember($this->user))
        {
            $this->flashBag->set(self::TYPE, $this->translator->trans('MSC.property_notifier_list_empty', [], 'contao_default'));
            return [];
        }

        global $objPage;

        $records = [];
        $editPage = $this->model->jumpTo ? $this->model->getRelated('jumpTo') : null;

        foreach ($objRecords as $record)
        {
            $template = new FrontendTemplate($this->model->notifierItemTpl ?: 'notifier_item_default');
            $template->setData($record->row());

            $template->intervalLabel = $this->translator->trans('tl_property_notifier.intervalLabel', [], 'contao_default');
            $template->editLabel = $this->translator->trans('tl_property_notifier.editLabel', [], 'contao_default');
            $template->createdAtLabel = $this->translator->trans('tl_property_notifier.createdAtLabel', [], 'contao_default');

            $template->deleteLabel = $this->translator->trans('tl_property_notifier.deleteLabel', [], 'contao_default');
            $template->deleteSafetyQuestion = $this->translator->trans('tl_property_notifier.deleteSafetyQuestion', [], 'contao_default');
            $template->deleteLink = $objPage->getAbsoluteUrl() . '?act=delete&notifierId=' . $record->id;

            $template->humanReadableDate = date(Config::get('datimFormat'), (int) $record->tstamp);
            $template->properties = $this->propertyNotifier->parseNotifierProperties($record->properties);

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
     * Check whether a data record is to be deleted
     */
    protected function deleteAction()
    {
        global $objPage;

        if('delete' === $this->request->get('act') && $id = $this->request->get('notifierId'))
        {
            if($objNotifier = PropertyNotifierModel::findById($id))
            {
                if(PropertyNotifierModel::isOwnerOfRecord($this->user, $objNotifier))
                {
                    PropertyNotifierModel::deleteById($id);

                    $this->flashBag->set(self::TYPE, $this->translator->trans('MSC.property_notifier_list_delete', [], 'contao_default'));

                    // Reload
                    throw new RedirectResponseException($objPage->getAbsoluteUrl(), 303);
                }
                else
                {
                    $this->flashBag->set(self::TYPE, $this->translator->trans('ERR.property_notifier_list_delete_not_authorized', [], 'contao_default'));
                }
            }
            else
            {
                // Add error message
                $this->flashBag->set(self::TYPE, $this->translator->trans('ERR.property_notifier_list_no_module', [], 'contao_default'));
            }
        }
    }
}
