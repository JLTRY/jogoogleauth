<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Log\Log;

class com_jogoogleauthInstallerScript implements InstallerScriptInterface
{

    /**
     * Minimum Joomla release supported
     *
     * @var string
     */
    protected string $minimum_joomla_release = "4.2.0";

    public function install(InstallerAdapter $adapter): bool
    {
        //$this->updateMenuLink();
        return true;
    }
    
    public function uninstall(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        //$this->updateMenuLink();
        return true;
    }

    private function updateMenuLink()
    {
        Log::Add("updateMenuLink start", Log::WARNING, "install");
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->update('#__menu')
              ->set('link=' . $db->quote('index.php?option=com_config&view=component&component=com_jogoogleauth'))
              ->where('title LIKE ' . $db->quote('COM_JOGOOGLEAUTH_SETTINGS_MENU'));
        $db->setQuery($query);
        $result = $db->execute();

        if ($result) {
            $err = "Query executed successfully!";
        } else {
            $err = "Error: " . $db->getErrorMsg();
        }
        // Get the full SQL query as a string
        $fullQuery = (string) $query;
        Log::Add("updateMenuLink was done" . $fullQuery, Log::WARNING, "install");
        Log::Add("updateMenuLink result" . $err, Log::WARNING, "install");
    }
    
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        return true;
    }
    
    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        $this->updateMenuLink();
        return true;
    }
}