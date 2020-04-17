<?php
/**
 * String formated attributes handling class
 *
 * @package 	Core
 * @author		Sylvain Frigui <sf@hands.agency>
 * @access		public
 * @link		http://grandcentral.fr
 */
class attrSirtrevor extends _attrs
{
/**
 * Turn nicknames into links
 *
 * @return	string	la définition mysql
 * @access	public
 */
 public function convert_links($txt)
 {
	 // print'<pre>';print_r($txt);print'</pre>';
	 $from = [];
	 $to_replace = [];
	 $pattern = '/<a href=\"([^\"]*)\">.*<\/a>/iU';
	 preg_match_all($pattern, $txt, $matches, PREG_SET_ORDER);
	 foreach ($matches as $link)
	 {
		 $from[] = $link[1];
		 $url = html_entity_decode($link[1]);
		 // url déjà dans le texte
		 if (filter_var($url, FILTER_VALIDATE_URL))
		 {
			 $to_replace[] = [
				 'url' => $url,
				 'type' => 'external'
			 ];
		 }
		 // lien Grand Central
		 elseif (mb_strstr($url, '['))
		 {
			 $tmp = explode('_', str_replace(array('[', ']'), '', $url));
			 $to_replace[] = [
				 'url' => str_replace(DOMAIN_URL, '', i($tmp[0], $tmp[1])['url']->__tostring()),
				 'type' => 'internal'
			 ];
		 }
		 else {
			 $to_replace[] = [
				 'url' => $url,
				 'type' => 'external'
			 ];
		 }
	 }
	 $to = array_column($to_replace, 'url');
	 $links = str_replace($from, $to, $txt);
	 $to = $from = [];
	 foreach ($to_replace as $link)
	 {
		 if ($link['type'] == 'internal')
		 {
			 $from[] = '<a href="'.$link['url'];
			 $to[] = '<a class="jslink" href="'.$link['url'];
		 }
	 }
	 $links = str_replace($from, $to, $links);
 //	retour
	 return $links;
 }
/**
 * Set array attribute
 *
 * @param	string	la variable
 * @return	string	une string
 * @access	public
 */
	public function set($data)
	{
		$this->data = (string) $data;
		return $this;
	}
	/**
	 * xxxx
	 *
	 * @param	string	la variable
	 * @return	string	une string
	 * @access	public
	 */
		public function cut($length, $add = '...')
		{
			$d = $this->data;
			$text = json_decode($this->data)->data[0]->data->text;
			if (mb_strlen($text) > $length)
			{
			//	On coupe
				$string = mb_substr($text, 0, $length);
			//	On enlève le dernier mot
				$string = mb_substr($string, 0, -(mb_strlen(mb_strrchr($string, ' '))));
			//	On ajoute les ...
				$string .= $add;
			}
			else
			{
				$string = $text;
			}
			return $string;
		}

/**
 * Definition mysql
 *
 * @return	string	la définition mysql
 * @access	public
 */
	public function mysql_definition()
	{
	//	definition
		$definition = '`'.$this->params['key'].'` mediumtext CHARACTER SET '.database::charset.' COLLATE '.database::collation.' NOT NULL';
	//	retour
		return $definition;
	}
/**
 * Get array attribute
 *
 * @param	string	la variable
 * @return	string	une string
 * @access	public
 */
	public function __toString()
	{
		$return = '';
		$json = json_decode($this->get(), true);

		if (isset($json['data']))
		{
			foreach ($json['data'] as $block)
			{
				$return .= app('sirtrevor', 'block/'.$block['type'], array('attr' => $this,'block' => $block), 'site', 'html')->__toString();
			}
		}
		return $return;
	}
/**
 * Default field attributes for Array
 *
 * @param	string	la variable
 * @return	string	une string
 * @access	public
 */
	public static function get_properties()
	{
	//	Start with the default for all properties
		$params = parent::get_properties();
	//	Somes specifics for this attr
	/*
		$params['blockTypes'] = array();
		$params['defaultType'] = array();
		$params['blockLimit'] = array();
		$params['blockTypeLimits'] = array();
		$params['onEditorRender'] = array();
	*/
	//	Return
		return $params;
	}

/**
* Ajout d'espace insécable dans le texte
*
* @param	string	la texte à formatter
* @return	string	une texte formaté
* @access	public
*/
	public function format_text($txt)
	{
		$errors = array(" .", " ,",
						" :", " ;", " !", " ?", " »",
						"« ",
						"( ","[ ",
						" )"," ]",
						" -", "- "
						);
		$solutions = array(".", ",",
						"&nbsp:", "&nbsp;", "&nbsp!", "&nbsp?", "&nbsp»",
						"«&nbsp",
						"(","[",
						")","]",
						"-", "-"
						);

		$no_space_before_and_space_justify_after = array(".",",");
		// Remplace " ." par "."
		$space_insecable_before_and_space_justify_after = array(":",";","!","?","»");
		// Remplace " :" par "$nbsp:"
		$space_justify_before_and_space_insecable_after = array("«");
		// Remplace "« " par "«$nbsp"
		$space_justify_before_and_no_space_after = array("(","[");
		// Remplace "( " par "("
		$no_space_before_and_space_justify_after = array(")","]");
		// Remplace " )" par ")"
		$no_space_before_and_after = array("-");
		// Remplace " -" par "-"
		// Remplace "- " par "-"

		return str_replace($errors, $solutions, $txt);
	}
}
?>
