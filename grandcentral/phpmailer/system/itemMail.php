<?php
/**
 * The generic item of Grand Central
 *
 * @author	Sylvain Frigui <sf@hands.agency>
 * @access	public
 * @link		http://grandcentral.fr
 */
class itemMail extends _items
{
/**
 * Class constructor (Don't forget it is an abstract class)
 *
 * @param	mixed  une id, une clé ou un tableau array('id' => 2)
 * @param	string  admin ou site (environnement courant par défaut)
 * @access	public
 */
	public function __construct($env = env)
	{
		$env = 'site';
		parent::__construct($env);
	}

	public function replace_text_with_data($datas)
	{
		$msg = (string) $this['content'];
		preg_match_all("/\[([a-zA-Z0-9=&]+)\]/", $msg, $results, PREG_SET_ORDER);
		foreach ((array) $results as $result)
		{
			if (isset($datas[$result[1]]))
			{
				$msg = str_replace($result[0], $datas[$result[1]], $msg);
			}
		}
		print'<pre>';print_r($msg);print'</pre>';
		return $msg;
	}
}
?>
