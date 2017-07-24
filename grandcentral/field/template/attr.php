<?php
/**
 * Description: This is the description of the document.
 * You can add as many lines as you want.
 * Remember you're not coding for yourself. The world needs your doc.
 * Example usage:
 * <pre>
 * if (Example_Class::example()) {
 *    echo "I am an example.";
 * }
 * </pre>
 *
 * @author		Michaël V. Dandrieux <@mvdandrieux>
 * @author		Sylvain Frigui <sf@hands.agency>
 * @copyright	Copyright © 2004-2015, Hands
 * @license		http://grandcentral.fr/license MIT License
 * @access		public
 * @link		http://grandcentral.fr
 */
/********************************************************************************************/
//	Some binds
/********************************************************************************************/
	$_APP->bind_css('css/addable.css');
	$_APP->bind_css('css/attr.css');
	$_APP->bind_script('js/addable.js');
	$_APP->bind_script('js/attr.js');
	$_APP->bind_code('script', '$(\'li[data-type="attr"]\').addable();');

/********************************************************************************************/
//	Some vars
/********************************************************************************************/
//	For easier access
	$_FIELD = $_PARAM['field'];

//	The data from the DB
	$data = '';
//	The add buttons
	$addbuttons = '';
//	The html templates for jQuery
	$template = array();
//	Hide everything except
	$donthide = array('key', 'type');

/********************************************************************************************/
//	Set defaults
/********************************************************************************************/
//	Get the list of available attr
	$available = registry::get_class('attr');
//	Get the properties for each attr
	foreach ($available as $attr) $fields[mb_substr(mb_strtolower($attr), 4)] = $attr::get_properties();
	// echo "<pre>";print_r($fields);echo "</pre>";exit;
/********************************************************************************************/
//	The list of add buttons
/********************************************************************************************/
	foreach ($available as $field) $addbuttons .= '<li><button type="button" data-type="'.mb_substr(mb_strtolower($field), 4).'" data-feathericon="&#xe114"> '.mb_substr(mb_strtolower($field), 4).'</button></li>';

/********************************************************************************************/
//	Print the data from the Database
/********************************************************************************************/
	$values = $_FIELD->get_value();
	foreach ((array) $values as $key => $value)
	{
		$li = '';
		foreach ($fields[$value['type']] as $param)
		{
		//	Field
			$class = 'field'.ucfirst($param['type']);
			$field = new $class($_FIELD->get_name().'['.$key.']['.$param['name'].']', $param);
			if (isset($value[$param['name']])) $field->set_value($value[$param['name']]);
		//	LI
			$hideRows = (in_array($param['name'], $donthide)) ? null : 'style="display:none;"';
			$li .= '<li data-type="'.$field->get_type().'" data-key="'.$param['name'].'" '.$hideRows.'>'.$field.'</li>';
		}

		$data .= '<li><span class="handle" data-feathericon="&#xe026"></span><ol>'.$li.'</ol><button type="button" class="delete"></button></li>';
	}
/********************************************************************************************/
//	Now we can build the templates used when creating new fields
/********************************************************************************************/
//	It's a template, these fields MUST be disabled otherwise they get through the POST (appending will enable them, don't worry)
	foreach ($fields as $name => $field)
	{
		foreach ($field as $key => $param) $fields[$name][$key]['disabled'] = true;
	}

	foreach ($fields as $key => $fieldtype)
	{
		$li = '';
		foreach ($fieldtype as $param)
		{
		//	Field
			if ($param['name'] == 'type') $param['value'] = $key;
			$class = 'field'.ucfirst($param['type']);
			$field = new $class($_FIELD->get_name().'[]['.$param['name'].']', $param);
		//	Li
			$li .= '<li data-type="'.$field->get_type().'" data-key="'.$param['name'].'"></span>'.$field.'</li>';
		}
	//	We store them in a <pre> tag, so that the addable.js plugin can retrieve them
		$html = '<li style="display:none;"><span class="handle" data-feathericon="&#xe026"></span><ol>'.$li.'</ol><button type="button" class="delete"></button></li>';
		$template[$key] = $html;
	}
?>
