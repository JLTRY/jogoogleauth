<?php
/**
 * Installer File
 * Performs an install / update of hello
 *
 */

defined('_JEXEC') or die;


jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
define('ROOT', dirname(__FILE__));
class com_jgoogleInstallerScript
{
	
	function getextensionid($type, $element)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('a.extension_id'))->from($db->quoteName('#__extensions', 'a'))->where($db->quoteName('a.type').' = '.$db->quote($type))			->where($db->quoteName('a.element').' = '.$db->quote($element));
		$db->setQuery($query);
		$db->execute();
		if($db->getNumRows()){
			return $db->loadResult();
		}
		return false;
	}
	
	/**
	 * Method to install the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function install($parent) 
	{		
		if (JFolder::exists(ROOT . '/protostar/html'))
		{
			if (!self::copy_from_folder('/protostar/html', '/templates/protostar/html/', 1))
			{
				echo '<p>error with install</p>';
				return 0;
			}
			else
			{
				echo '<p>OK with install</p>';
				echo '<p>' . ROOT . '/images</p>';
			}
		}
		else
		{
			echo '<p>' . ROOT . '/images' .'does not exists</p>' ;
		}
				
		echo '<p>The component has been installed to version' . $parent->get('manifest')->version . '</p>';
	}
	
	
	/**
	 * Method to uninstall the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		echo '<p>The component has been uninstalled</p>';
	}
 
	/**
	 * Method to update the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function update($parent) 
	{
		
		$parent->getParent()->setSchemaVersion($parent->get('manifest')->update->schemas, $this->getextensionid('component', 'com_jgoogle'));
		echo '<p>The module has been updated to version ' . $parent->get('manifest')->version . '</p>';
	}
 
	/**
	 * Method to run before an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
		echo '<p>Anything here happens before the installation/update/uninstallation of the module</p>';
	}
 
	/**
	 * Method to run after an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
		echo '<p>Anything here happens after the installation/update/uninstallation of the module</p>';
	}
	
	
	/**
	 * Copies all files from install folder
	 */
	private function copy_from_folder($folder, $dest, $force = 0)
	{
		if (!is_dir(ROOT . $dest))
		{
			return 0;
		}

		// Copy files
		$folders = JFolder::folders(ROOT  . $folder);

		$success = 1;

		foreach ($folders as $subfolder)
		{
			$dest = JPATH_ROOT .  $dest . '/' . $subfolder;
			//echo '<p>copy' . $folder . '/' . $subfolder . ':to:' .  $dest .'</p>';
			if (!self::folder_copy(ROOT . '/' . $folder . '/' . $subfolder, $dest, $force))
			{
				$success = 0;
			}
		}

		return $success;
	}

	/**
	 * Copy a folder
	 */
	private function folder_copy($src, $dest, $force = 0)
	{
		// Initialize variables
		jimport('joomla.client.helper');
		
		// Eliminate trailing directory separators, if any
		$src = rtrim(str_replace('\\', '/', $src), '/');
		$dest = rtrim(str_replace('\\', '/', $dest), '/');

		if (!JFolder::exists($src))
		{
			return 0;
		}

		$success = 1;

		// Make sure the destination exists
		if (!JFolder::exists($dest) && !self::folder_create($dest))
		{
			$folder = str_replace(JPATH_ROOT, '', $dest);
			JFactory::getApplication()->enqueueMessage(JText::sprintf(JText::_('NNI_FAILED_TO_CREATE_DIRECTORY'), $folder), 'error');
			$success = 0;
		}

		if (!($dh = @opendir($src)))
		{
			return 0;
		}

		$folders = array();
		$files = array();
		while (($file = readdir($dh)) !== false)
		{
			if ($file != '.' && $file != '..')
			{
				$file_src = $src . '/' . $file;
				switch (filetype($file_src))
				{
					case 'dir':
						$folders[] = $file;
						break;
					case 'file':
						$files[] = $file;
						break;
				}
			}
		}
		sort($folders);
		sort($files);
		$expl = explode('/', $src);
		$curr_folder = array_pop($expl);
		// Walk through the directory recursing into folders
		foreach ($folders as $folder)
		{
			$folder_src = $src . '/' . $folder;
			$folder_dest = $dest . '/' . $folder;
			if (!($curr_folder == 'language' && !JFolder::exists($folder_dest)))
			{
				if (!self::folder_copy($folder_src, $folder_dest, $force))
				{
					$success = 0;
				}
			}
		}
		foreach ($files as $file)
		{
			$file_src = $src . '/' . $file;
			$file_dest = $dest . '/' . $file;
			if ($force || !JFile::exists($file_dest))
			{
				if (!@copy($file_src, $file_dest))
				{
					$file_path = str_replace(JPATH_ROOT, '', $file_dest);
					JFactory::getApplication()->enqueueMessage(JText::sprintf(JText::_('NNI_ERROR_SAVING_FILE'), $file_path), 'error');
					$success = 0;
				}
				else
				{
				  echo "<br>copy:" . $file_dest;	
				}
			}			
		}

		return $success;
	}

	/**
	 * Create a folder
	 */
	private function folder_create($path = '', $mode = 0755)
	{
		// Initialize variables
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Check if dir already exists
		if (JFolder::exists($path))
		{
			return true;
		}

		// Check for safe mode
		if ($ftpOptions['enabled'] == 1)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = JFTP::getInstance(
				$ftpOptions['host'], $ftpOptions['port'], array(),
				$ftpOptions['user'], $ftpOptions['pass']
			);

			// Translate path to FTP path
			$path = JPath::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $path), '/');
			$ret = $ftp->mkdir($path);
			$ftp->chmod($path, $mode);
		}
		else
		{
			// We need to get and explode the open_basedir paths
			$obd = ini_get('open_basedir');

			// If open_basedir is set we need to get the open_basedir that the path is in
			if ($obd != null)
			{
				if (JPATH_ISWIN)
				{
					$obdSeparator = ";";
				}
				else
				{
					$obdSeparator = ":";
				}
				// Create the array of open_basedir paths
				$obdArray = explode($obdSeparator, $obd);
				$inBaseDir = false;
				// Iterate through open_basedir paths looking for a match
				foreach ($obdArray as $test)
				{
					$test = JPath::clean($test);
					if (strpos($path, $test) === 0)
					{
						$inBaseDir = true;
						break;
					}
				}
				if ($inBaseDir == false)
				{
					// Return false for JFolder::create because the path to be created is not in open_basedir
					JError::raiseWarning(
						'SOME_ERROR_CODE',
						'JFolder::create: ' . JText::_('NNI_PATH_NOT_IN_OPEN_BASEDIR_PATHS')
					);
					return false;
				}
			}

			// First set umask
			$origmask = @umask(0);

			// Create the path
			if (!$ret = @mkdir($path, $mode))
			{
				@umask($origmask);
				return false;
			}

			// Reset umask
			@umask($origmask);
		}

		return $ret;
	}
}

