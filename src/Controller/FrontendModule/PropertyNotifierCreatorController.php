<?php

declare(strict_types=1);

namespace ContaoEstateManager\PropertyNotifier\Controller\FrontendModule;

use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\FrontendUser;
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
     * Data
     */
    protected ParameterBag $data;

    /**
     * Create Frontend Module
     */
    public function __construct(TranslatorInterface $translator)
    {
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

        // Set template vars
        $template->intervals = [];
        $template->description = $this->model->pnText;
        $template->form = $this->form->generate();

        return new Response($template->parse());
    }

    /**
     * Create the notifier form
     */
    public function createNotifierForm(): void
    {
        // Load language file
        Controller::loadLanguageFile('tl_property_notifier');

        // Create form instance
        $this->form = new Form(self::TYPE, 'POST', fn($objHaste) => $this->request->get('FORM_SUBMIT') === self::TYPE);

        // Add form fields from given form id
        if($this->model->addForm)
        {
            $this->form->addFieldsFromFormGenerator($this->model->form, static function($strField, &$arrDca){
                // Set value
                $arrDca['value'] = $this->data->get($strField);

                // Skip submit fields
                return $arrDca['type'] !== 'submit';
            });
        }

        // Interval field
        $options = [];

        foreach (Controller::getContainer()->getParameter('property_notifier.intervals') as $interval)
        {
            $options[$interval['rule']] = $this->translator->trans('tl_property_notifier.' . $interval['key'], [], 'contao_default');
        }

        $this->form->addFormField('interval', [
            'label'         => ['!Benachrichtigung per E-Mail'],
            'inputType'     => 'radio',
            'value'         => '',
            'options'       => $options,
            'eval'          => [
                'mandatory'=>true,
                'multiple' => true
            ]
        ]);

        // Edit mode hidden field
        $this->form->addFormField('notifierId', [
            'inputType' => 'hidden',
            'value'     => $this->data->get('id')
        ]);

        // Submit button
        $this->form->addFormField('submit', [
            'label'     => '!Suche speichern',
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
            //$parameters = $this->form->fetchAll();

            // Set values from filter
            //$value = $filter->get($strField);

            // Create notifier object
            #$notifier = new PropertyNotifierModel();
            #$notifier->tstamp = time();
            #$notifier->properties = serialize($parameters);

            // Save notifier object
            #$notifier->save();
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
        if($notifierModel = $notifierId ? PropertyNotifierModel::findById($notifierId) : null)
        {
            // Check if the record belongs to the member
            if(!PropertyNotifierModel::isOwnerOfRecord($this->user, $notifierModel))
            {
                throw new AccessDeniedException('No rights to edit this record.');
            }

            // Retrieve data from an existing model and wrap it in a ParameterBag to handle data in the same way
            return new ParameterBag($notifierModel->row());
        }

        // Get data from session
        return SessionManager::getInstance();
    }
}
