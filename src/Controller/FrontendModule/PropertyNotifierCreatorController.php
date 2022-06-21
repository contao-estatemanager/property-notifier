<?php

declare(strict_types=1);

namespace ContaoEstateManager\PropertyNotifier\Controller\FrontendModule;

use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\FrontendUser;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Contao\ModuleModel;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\User;
use ContaoEstateManager\PropertyNotifier\Model\PropertyNotifierModel;
use ContaoEstateManager\SessionManager;
use Haste\Form\Form;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(type=PropertyNotifierCreatorController::TYPE, category="estatemanager")
 */
class PropertyNotifierCreatorController extends AbstractFrontendModuleController
{
    /**
     * Frontend Module Type
     */
    public const TYPE = 'property_notifier_create';

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
     * Form
     */
    protected Form $form;

    /**
     * User
     */
    protected ?User $user;

    /**
     * Notifier Model
     */
    protected ?PropertyNotifierModel $notifier;

    /**
     * Data
     */
    protected $data;

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

        // Get data
        $this->data = $this->getData();

        // Create and validate form
        $this->createNotifierForm();
        $this->validateNotifierForm();

        // Message
        $flashBag = $request->getSession()->getFlashBag();

        if ($request->getSession()->isStarted() && $flashBag->has(self::TYPE))
        {
            $arrMessages = $flashBag->get(self::TYPE);
            $template->message = $arrMessages[0];
        }

        // Set template vars
        $template->description = $this->model->pnText;
        $template->form = $this->form->generate();

        return new Response($template->parse());
    }

    /**
     * Create the notifier form
     */
    public function createNotifierForm(): void
    {
        // Create form instance
        $this->form = new Form(self::TYPE, 'POST', fn($objHaste) => $this->request->get('FORM_SUBMIT') === self::TYPE);

        // Add form fields from given form id
        if($this->model->addForm)
        {
            $this->form->addFieldsFromFormGenerator($this->model->form, function($strField, &$arrDca) {
                // Set value
                $arrDca['value'] = $this->data->get($strField);

                // Skip submit fields
                return $arrDca['type'] !== 'submit';
            });
        }

        // Create hidden fields with all data from session (not in edit mode)
        if(!$this->model->excludeHiddenFields && !$this->notifier)
        {
            foreach ($this->data->all() as $key => $value)
            {
                // Skip system fields
                if(!in_array($key, ['FORM_SUBMIT', 'REQUEST_TOKEN']) && !$this->form->hasFormField($key))
                {
                    $this->form->addFormField($key, [
                        'inputType' => 'hidden',
                        'value'     => $value
                    ]);
                }
            }
        }

        // Create email field is no user is logged in
        if(!$this->user)
        {
            $this->form->addFormField('email', [
                'label'         => ['!E-Mail'],
                'inputType'     => 'text',
                'eval'          => [
                    'mandatory' => true,
                    'rgxp'      => 'email'
                ]
            ]);
        }

        // Interval field
        $options = [];

        foreach (Controller::getContainer()->getParameter('property_notifier.intervals') as $interval)
        {
            $options[urlencode($interval['rule'])] = $this->translator->trans('tl_property_notifier.' . $interval['key'], [], 'contao_default');
        }

        $this->form->addFormField('interval', [
            'label'         => [$this->translator->trans('tl_property_notifier.createIntervalLegend', [], 'contao_default')],
            'value'         => urlencode($this->data->get('interval')) ?: array_key_first($options),
            'inputType'     => 'radio',
            'options'       => $options,
            'eval'          => [
                'mandatory'=>true
            ]
        ]);

        // Submit button
        $this->form->addFormField('submit', [
            'label'     => $this->translator->trans('tl_property_notifier.createSaveLabel', [], 'contao_default'),
            'inputType' => 'submit'
        ]);
    }

    /**
     * Validate the notifier form
     */
    public function validateNotifierForm(): void
    {
        if($this->form->validate())
        {
            // Get submitted data
            $formData = array_filter($this->form->fetchAll(function($strName) {
                if(in_array($strName, ['FORM_SUBMIT', 'REQUEST_TOKEN', 'interval', 'email', 'submit']))
                {
                    return null;
                }

                return $this->request->get($strName);
            }));

            // Create or edit notifier object
            if($this->notifier)
            {
                $notifier = $this->notifier;

                // Merge form data
                $formData = array_merge(
                    StringUtil::deserialize($this->notifier->properties, true),
                    $formData
                );

                $notifier->properties = $formData;
            }
            else
            {
                $notifier = new PropertyNotifierModel();
            }

            $notifier->properties = serialize($formData);
            $notifier->tstamp = time();
            $notifier->interval = urldecode($this->request->get('interval', ''));
            $notifier->email = $this->request->get('email') ?? ($notifier->email ?? '');
            $notifier->member = $this->user->id ?? 0;

            // Create hash to check if an identical entry already exists
            $hash = hash('sha256', $notifier->properties);

            // Check if the hash already exists, and we are not in edit mode
            if(!$this->notifier && PropertyNotifierModel::findByMemberAndHash($this->user, $hash))
            {
                $this->request->getSession()->getFlashBag()->set(self::TYPE, $this->translator->trans('ERR.property_notifier_create', [], 'contao_default'));

                // Reload
                throw new RedirectResponseException($this->request->getUri(), 303);
            }

            $notifier->hash = $hash;

            // Save notifier object
            $notifier->save();

            // Success message if we are in edit mode
            if($this->notifier)
            {
                $this->request->getSession()->getFlashBag()->set(self::TYPE, $this->translator->trans('MSC.property_notifier_edit', [], 'contao_default'));
            }
            else
            {
                $this->request->getSession()->getFlashBag()->set(self::TYPE, $this->translator->trans('MSC.property_notifier_create', [], 'contao_default'));
            }

            // Redirect
            if(($objTarget = $this->model->getRelated('jumpTo')) instanceof PageModel)
            {
                /** @var PageModel $objTarget */
                throw new RedirectResponseException($objTarget->getAbsoluteUrl(), 303);
            }

            // Reload
            throw new RedirectResponseException($this->request->getUri(), 303);
        }
    }

    /**
     * Returns all data - Depending on entering the module, it is decided whether you are in edit or create mode.
     */
    private function getData()
    {
        // Check if an ID was passed to edit the record
        $notifierId = $this->request->get('notifierId');

        // Get the model when an ID has been passed
        if($this->notifier = $notifierId ? PropertyNotifierModel::findById($notifierId) : null)
        {
            // Check if the record belongs to the member
            if(!PropertyNotifierModel::isOwnerOfRecord($this->user, $this->notifier))
            {
                throw new AccessDeniedException('No rights to edit this record.');
            }

            // Retrieve data from an existing model and wrap it in a ParameterBag to handle data in the same way
            return new ParameterBag($this->notifier->row());
        }

        // Get data from session
        return SessionManager::getInstance();
    }
}
