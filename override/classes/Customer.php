<?php
/**
 * Logging in with username
 *
 * @author    PrestashopExtensions.com
 * @copyright PrestashopExtensions.com
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class Customer extends CustomerCore
{
	/**
	 * @var string
	 */
	public $username;

	/**
	 * Support username
	 * @see parent::__construct()
	 */
	public function __construct($id = null)
	{
		$this->id_default_group = (int)Configuration::get('PS_CUSTOMER_GROUP');
		self::$definition['fields']['username'] = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 32);
		parent::__construct($id);
	}
}
