<?php

/**
 * Logging in with username
 *
 * @author    PrestashopExtensions.com
 * @copyright PrestashopExtensions.com
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_'))
	exit;


class HsLogginUsername extends Module
{

	public function __construct()
	{
		$this->name						 = 'hslogginusername';
		$this->version					 = '1.1';
		$this->tab						 = 'front_office_features';
		$this->displayName				 = $this->l('Logging in with username');
		$this->author					 = 'PrestashopExtensions.com';
		parent::__construct();
		$this->description				 = $this->l('Want your clients logging in with their username? Install this right now!');
		$this->confirmUninstall			 = $this->l('Do you want to uninstall').' '.$this->displayName.'?';
	}

	public function install()
	{
		return parent::install() &&
				$this->installTables() &&
				$this->registerHook('actionBeforeAuthentication') &&
				$this->registerHook('displayCustomerAccountForm') &&
				$this->registerHook('actionBeforeSubmitAccount')
				;
	}

	protected function installTables()
	{
		if (!$this->does_column_exist('username', 'customer'))
		{
			$sql_add_username	 = 'ALTER TABLE `'._DB_PREFIX_.'customer` ADD COLUMN `username` VARCHAR(64) NULL AFTER `email`';
			return Db::getInstance()->query($sql_add_username);
		}
		return true;
	}


	/**
	 * Map username to email before logging in
	 */
	public function hookActionBeforeAuthentication()
	{
		$username_or_email = trim(Tools::getValue('email'));
		if (!empty($username_or_email))
		{
			$sql_check_username = 'SELECT `email` FROM `'._DB_PREFIX_.'customer` WHERE `username` LIKE "'.$username_or_email.'" OR `email` LIKE "'.$username_or_email.'"';
			$email = Db::getInstance()->getValue($sql_check_username);
			if (empty($email))
				$this->context->controller->errors[] = Tools::displayError('Authentication failed.');
			else
				$_POST['email'] = $email;// It's good to go, let's hand over to Prestashop, it will take care the rest of the process.
		}
	}

	public function hookDisplayCustomerAccountForm()
	{
		return $this->display(__FILE__, 'display_customer_account_form.tpl');
	}

	/**
	 * Validate username before moving forward to create a new customer account
	 */
	public function hookActionBeforeSubmitAccount()
	{
		if ($this->usernameExists(Tools::getValue('username')))
			$this->context->controller->errors[] = Tools::displayError('Your username has been taken.', false);
	}

	protected function does_column_exist($column_name, $table_name) {
		$sql = 'SHOW COLUMNS FROM `'._DB_PREFIX_.''.$table_name.'` LIKE "'.$column_name.'"';
		return (bool) Db::getInstance()->executeS($sql);
	}

	/**
	 * Check if username is already registered in database
	 *
	 * @param string $username e-mail
	 * @return boolean
	 */
	protected function usernameExists($username)
	{
		$sql = 'SELECT `username`
				FROM `'._DB_PREFIX_.'customer`
				WHERE `username` = \''.pSQL($username).'\'
					'.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);
		return (boolean) Db::getInstance()->getValue($sql);
	}
}