<?php
/**
 * 	Copyright 2008,2009, 2010
 *  This file is part of mod_GMap.
 *
 *  mod_GMap is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  mod_GMap is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with mod_GMap.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Created on Dec , 2010
 * @author James Hansen(Kermode Bear Software)
 *
 */
jimport('joomla.html.parameter.element');
class GElement extends JElement{
	
	static function getParameters($mod = 'mod_GMap'){
		static $params;
		$file = JPATH_SITE . 'modules/' .DS . $mod . DS . $mod . '.xml';	
		if(!is_object($params) ){

			$app = JFactory::getApplication();
//			$id = $app->getUserState('com_modules.edit.module.id');
			$id = JRequest::getInt('id');
			if($id){	
				$db = JFactory::getDBO();
				$sql = 'select * from #__modules where ';
				$sql .= 'id = ' . $id;
				$db->setQuery($sql);
				$module = $db->loadObject();
				$params = new JParameter($module->params, $file);
			}else{
				$params = new JParameter(null, $file);
			}
			
		}
		
		return $params;
	}
}