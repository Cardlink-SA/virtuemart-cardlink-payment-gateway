<?php
/**
* @package     Joomla
* @author      Cardlink https://www.cardlink.gr
* @copyright   Copyright (c) 2022 CARDLINK. All rights reserved.
* @license     GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
* @Developer   Cardlink
*/

//ini_set('display_errors','On');
//error_reporting(1);
defined('_JEXEC') or die('Restricted access');
$oldVersion=JFolder::exists(JPATH_VM_ADMINISTRATOR.'/elements');
if(version_compare(JVERSION,'2.5','ge') && !$oldVersion)
{
	jimport('joomla.form.formfield');
	class JFormFieldLiveupdate extends JFormField
	{
		protected $type = 'Liveupdate';
		protected $downloadURL = 'https://web-expert.gr/clients/dl.php?type=d&id=116';
		protected $productURL = 'https://www.web-expert.gr/en/joomla-extensions/item/104-joomla-extension-live-updater-plugin';
		protected $langPrefix = 'JLIB';
		
		public function getLabel()
		{
			if($this->getAttribute('langPrefix'))
			{
				$this->langPrefix=$this->getAttribute('langPrefix');
			}
			
			$this->element['label'] = $this->element['label'] ? (string) $this->element['label'] : $this->langPrefix.'_'.JString::strtoupper($this->element['name']);
			$this->description = $this->description ? (string) $this->description : $this->langPrefix.'_'.JString::strtoupper($this->element['name']).'_DESC';
			return parent::getLabel();
		}

		protected function getInput()
		{
			$app = JFactory::getApplication();
				
			$doc =JFactory::getDocument();
			$doc->addStyleDeclaration('
				div#liveupdate{background:#EBEBEB;min-height:50px;border-radius:5px;padding:5px;width:90%;margin:0 2px;}
				div#liveupdate input{width:98%;}
				div#liveupdate-install{color:red;font-weight:bold;}
				div#liveupdate-enable{color:orange;font-weight:bold;}
				div#liveupdate-activated{color:green;font-weight:bold;}
				div#liveupdate span{font-size:10px;}
			');
			
			if(version_compare(JVERSION,'3.0','lt'))
			{
				//bootstrap buttons
				$doc->addStyleDeclaration('
					.btn{border-image:none;border-radius:4px;border-style:solid;border-width:1px;box-shadow:0 1px 0 rgba(255,255,255,.2) inset,0 1px 2px rgba(0,0,0,.05);cursor:pointer;display:inline-block;font-size:13px;line-height:18px;margin-bottom:0;padding:4px 12px;text-align:center;vertical-align:middle}
					.btn-success{background-color:#409740;background-image:linear-gradient(to bottom,#46a546,#378137);background-repeat:repeat-x;border-color:#378137 #378137 #204b20;color:#fff;text-shadow:0 -1px 0 rgba(0,0,0,.25)}
				');	
			}
			if($this->getAttribute('langPrefix'))
			{
				$this->langPrefix=$this->getAttribute('langPrefix');
			}
			
			$url = preg_replace('/&mode=(install|enable)/i','',JFactory::getURI()->toString());
			$mode=$app->input->getCMD('mode','');
			if(!empty($mode))
			{
				if($mode=='install')
				{
					$lang = JFactory::getLanguage();
					$lang->load('com_installer',JPATH_ADMINISTRATOR,$lang->getTag(), true);
					$app->input->set('installtype','url');
					$app->input->set('install_url',$this->downloadURL);
					JLoader::import('joomla.application.component.model');
					version_compare(JVERSION,'3.0','ge')? JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_installer/models'):JModel::addIncludePath (JPATH_ADMINISTRATOR.'/components/com_installer/models');				
					$model = version_compare(JVERSION,'3.0','ge')? JModelLegacy::getInstance('Install','InstallerModel') : JModel::getInstance('Install','InstallerModel');
					if($model!==false && $model->install())
					{
						$app->redirect($url);
					}
				}
				elseif($mode=='enable')
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$fields = array($db->quoteName('enabled').' = 1');
					
					$conditions = array(
						$db->quoteName('element') . ' = ' . $db->quote('jextupdater'), 
						$db->quoteName('type') . ' = ' . $db->quote('plugin')
					);
					
					$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
					$db->setQuery($query);   
					$rs=version_compare(JVERSION,'3.0','ge')? $db->execute() : $db->query();
					if($rs) $app->redirect($url);
				}
			}
			
			jimport('joomla.filesystem.file');
			jimport('joomla.plugin.helper');
			
			$html=array();
			if(!JFile::exists(JPATH_PLUGINS.'/system/jextupdater/jextupdater.php'))
			{
				$content='<div id="liveupdate-install"><p>'.JText::_($this->langPrefix.'_INSTALL_PLGJEXTUPDATER').' <a class="btn btn-success" href="'.$url.'&mode=install'.'">'.JText::_('JLIB_INSTALLER_INSTALL').'</a> <span>[<a target="_blank" href="'.$this->productURL.'">Plugin Page</a>]</span></p></div>';
			}
			elseif(!JPluginHelper::isEnabled('system','jextupdater'))
			{
				$content='<div id="liveupdate-enable"><p>'.JText::_($this->langPrefix.'_ENABLE_PLGJEXTUPDATER').' <a class="btn btn-success" href="'.$url.'&mode=enable'.'">'.JText::_($this->langPrefix.'_ENABLE_PLGJEXTUPDATER_BUTTON').'</a> <span>[<a target="_blank" href="'.$this->productURL.'">Plugin Page</a>]</span></p></div>';
			}
			else
			{
				$content='<div id="liveupdate-activated"><p>'.JText::_($this->langPrefix.'_ACTIVATED_PLGJEXTUPDATER').'</a> <span>[<a target="_blank" href="'.$this->productURL.'">Plugin Page</a>]</span></p></div>';
			}
			
			$html[]='<div id="liveupdate"><input placeholder="'.JText::_($this->description).'" class="' . $this->getAttribute('class') . '" type="text" name="'.$this->name.'" value="'.preg_replace("/[^a-zA-Z0-9_-]/",'',$this->value).'" />'.$content.'</div>';
			return implode("\n", $html);
		}
		
		public function getAttribute($attr_name, $default = null)
		{
		  return !empty($this->element[$attr_name])? $this->element[$attr_name]: $default;
		}
	}
}
else if(version_compare(JVERSION,'2.5','ge') && $oldVersion)
{
	jimport('joomla.html.parameter.element');
    class JElementLiveupdate extends JElement
	{
		var	$_name = 'liveupdate';
		function fetchElement($name, $value, &$node, $control_name)
		{
			$html=array();
			$html[]='<div id="liveupdate"><input placeholder="" type="text" name="'.$control_name.'['.$name. ']" value="'.preg_replace("/[^a-zA-Z0-9_-]/",'',$value).'" /><div>Live Extension update supported only for Virtuemart 3.x</div></div>';
			return implode("\n", $html);
		}
	}
}
else
{
	jimport('joomla.html.parameter.element');
    class JElementLiveupdate extends JElement
	{
		var	$_name = 'liveupdate';
		function fetchElement($name, $value, &$node, $control_name)
		{
			$html=array();
			$html[]='<div id="liveupdate"><input placeholder="" type="text" name="'.$control_name.'['.$name. ']" value="'.preg_replace("/[^a-zA-Z0-9_-]/",'',$value).'" /><div>Live Extension update supported only for Joomla 2.5+</div></div>';
			return implode("\n", $html);
		}
	}
}