<?php

declare(strict_types=1);

namespace ContaoEstateManager\PropertyNotifier\Controller\FrontendModule;

use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\FrontendUser;
use Contao\Template;
use Contao\ModuleModel;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\User;
use ContaoEstateManager\PropertyNotifier\Model\PropertyNotifierModel;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(type=PropertyNotifierDeleteController::TYPE, category="estatemanager")
 */
class PropertyNotifierDeleteController extends AbstractFrontendModuleController
{
    /**
     * Frontend Module Type
     */
    public const TYPE = 'property_notifier_delete';

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
     * Flashbag
     */
    protected ?FlashBagInterface $flashBag;

    /**
     * Create Frontend Module
     */
    public function __construct(TranslatorInterface $translator)
    {
        // Load language file
        Controller::loadLanguageFile('tl_property_notifier');

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

        // Get flash bag
        $this->flashBag = $request->getSession()->getFlashBag();

        // Check whether a record is to be deleted
        $this->deleteAction();

        if ($request->getSession()->isStarted() && $this->flashBag->has(self::TYPE))
        {
            $arrMessages = $this->flashBag->get(self::TYPE);
            $template->message = $arrMessages[0];
        }

        return new Response($template->parse());
    }

    /**
     * Check whether a data record is to be deleted
     */
    protected function deleteAction()
    {
        if($hash = $this->request->get('act'))
        {
            if($objNotifier = PropertyNotifierModel::findOneByHash($hash))
            {
                PropertyNotifierModel::deleteById($objNotifier->id);

                $this->flashBag->set(self::TYPE, $this->translator->trans('MSC.property_notifier_delete', [], 'contao_default'));
            }
            else
            {
                // Add error message
                $this->flashBag->set(self::TYPE, $this->translator->trans('ERR.property_notifier_delete_no_module', [], 'contao_default'));
            }
        }
    }
}
