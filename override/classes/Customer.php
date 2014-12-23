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
	public $username;

	public function __construct($id = null)
	{
		$this->id_default_group = (int)Configuration::get('PS_CUSTOMER_GROUP');
		self::$definition['fields']['username'] = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 32);
		parent::__construct($id);
	}

	public static function searchByName($query)
	{
		$sql = 'SELECT *
				FROM `'._DB_PREFIX_.'customer`
				WHERE (
						`email` LIKE \'%'.pSQL($query).'%\'
						OR `id_customer` LIKE \'%'.pSQL($query).'%\'
						OR `lastname` LIKE \'%'.pSQL($query).'%\'
						OR `firstname` LIKE \'%'.pSQL($query).'%\'
						OR `username` LIKE \'%'.pSQL($query).'%\'
					)'.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	}
}
