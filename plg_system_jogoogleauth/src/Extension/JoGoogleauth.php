<?php
namespace JLTRY\Plugin\System\JOGoogleAuth\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

class JoGoogleauth extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepareForm' => 'onContentPrepareForm',
        ];
    }

    public function onContentPrepareForm(PrepareFormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        // Vérifiez que le formulaire est celui d'un module
        if (!($form instanceof Form) || $form->getName() !== 'com_modules.module') {
            return;
        }
        // Vérifiez que le module est bien "mod_jocoaching"
        if (isset($data->module) && $data->module === 'mod_jogoogleauth') {
            $lang = Factory::getLanguage();
            // Récupérer la langue courante (ex: fr-FR, en-GB, etc.)
            $currentLang = $lang->getTag();

            // Charger les langues du composant et de Joomla pour la langue courante
            $lang->load('com_users', JPATH_ADMINISTRATOR, $currentLang, true);
            $lang->load('joomla', JPATH_ADMINISTRATOR, $currentLang, true);
        }
    }
}