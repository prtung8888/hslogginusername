<?php

class AdminCustomersController extends AdminCustomersControllerCore
{


	public function __construct()
	{
		parent::__construct();

		$this->fields_list['username'] = array('title' => $this->l('Username'),'width' => 'auto');
	}

	public function renderForm()
	{
		if (!($obj = $this->loadObject(true)))
			return;

		$genders = Gender::getGenders();
		$list_genders = array();
		foreach ($genders as $key => $gender)
		{
			$list_genders[$key]['id'] = 'gender_'.$gender->id;
			$list_genders[$key]['value'] = $gender->id;
			$list_genders[$key]['label'] = $gender->name;
		}

		$years = Tools::dateYears();
		$months = Tools::dateMonths();
		$days = Tools::dateDays();

		$groups = Group::getGroups($this->default_form_language, true);
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Customer'),
				'icon' => 'icon-user'
			),
			'input' => array(
				array(
					'type' => 'radio',
					'label' => $this->l('Social title'),
					'name' => 'id_gender',
					'required' => false,
					'class' => 't',
					'values' => $list_genders
				),
				array(
					'type' => 'text',
					'label' => $this->l('First name'),
					'name' => 'firstname',
					'required' => true,
					'col' => '4',
					'hint' => $this->l('Invalid characters:').' 0-9!&lt;&gt;,;?=+()@#"°{}_$%:'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Last name'),
					'name' => 'lastname',
					'required' => true,
					'col' => '4',
					'hint' => $this->l('Invalid characters:').' 0-9!&lt;&gt;,;?=+()@#"°{}_$%:'
				),
				array(
				'type' => 'text',
				'label' => $this->l('Username:'),
				'name' => 'username',
				'col' => '4',
				'hint' => $this->l('Invalid characters:').' 0-9!&lt;&gt;,;?=+()@#"°{}_$%:'
				),
				array(
					'type' => 'text',
					'prefix' => '<i class="icon-envelope-o"></i>',
					'label' => $this->l('Email address'),
					'name' => 'email',
					'col' => '4',
					'required' => true,
					'autocomplete' => false
				),
				array(
					'type' => 'password',
					'label' => $this->l('Password'),
					'name' => 'passwd',
					'required' => ($obj->id ? false : true),
					'col' => '4',
					'hint' => ($obj->id ? $this->l('Leave this field blank if there\'s no change.') :
						sprintf($this->l('Password should be at least %s characters long.'), Validate::PASSWORD_LENGTH))
				),
				array(
					'type' => 'birthday',
					'label' => $this->l('Birthday'),
					'name' => 'birthday',
					'options' => array(
						'days' => $days,
						'months' => $months,
						'years' => $years
					)
				),
				array(
					'type' => 'switch',
					'label' => $this->l('Enabled'),
					'name' => 'active',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'hint' => $this->l('Enable or disable customer login.')
				),
				array(
					'type' => 'switch',
					'label' => $this->l('Newsletter'),
					'name' => 'newsletter',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'newsletter_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'newsletter_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'hint' => $this->l('This customer will receive your newsletter via email.')
				),
				array(
					'type' => 'switch',
					'label' => $this->l('Opt-in'),
					'name' => 'optin',
					'required' => false,
					'class' => 't',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'optin_on',
							'value' => 1,
							'label' => $this->l('Enabled')
						),
						array(
							'id' => 'optin_off',
							'value' => 0,
							'label' => $this->l('Disabled')
						)
					),
					'hint' => $this->l('This customer will receive your ads via email.')
				),
			)
		);

		// if we add a customer via fancybox (ajax), it's a customer and he doesn't need to be added to the visitor and guest groups
		if (Tools::isSubmit('addcustomer') && Tools::isSubmit('submitFormAjax'))
		{
			$visitor_group = Configuration::get('PS_UNIDENTIFIED_GROUP');
			$guest_group = Configuration::get('PS_GUEST_GROUP');
			foreach ($groups as $key => $g)
				if (in_array($g['id_group'], array($visitor_group, $guest_group)))
					unset($groups[$key]);
		}

		$this->fields_form['input'] = array_merge(
			$this->fields_form['input'],
			array(
				array(
					'type' => 'group',
					'label' => $this->l('Group access'),
					'name' => 'groupBox',
					'values' => $groups,
					'required' => true,
					'col' => '6',
					'hint' => $this->l('Select all the groups that you would like to apply to this customer.')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Default customer group'),
					'name' => 'id_default_group',
					'options' => array(
						'query' => $groups,
						'id' => 'id_group',
						'name' => 'name'
					),
					'col' => '4',
					'hint' => array(
						$this->l('This group will be the user\'s default group.'),
						$this->l('Only the discount for the selected group will be applied to this customer.')
					)
				)
			)
		);

		// if customer is a guest customer, password hasn't to be there
		if ($obj->id && ($obj->is_guest && $obj->id_default_group == Configuration::get('PS_GUEST_GROUP')))
		{
			foreach ($this->fields_form['input'] as $k => $field)
				if ($field['type'] == 'password')
					array_splice($this->fields_form['input'], $k, 1);
		}

		if (Configuration::get('PS_B2B_ENABLE'))
		{
			$risks = Risk::getRisks();

			$list_risks = array();
			foreach ($risks as $key => $risk)
			{
				$list_risks[$key]['id_risk'] = (int)$risk->id;
				$list_risks[$key]['name'] = $risk->name;
			}

			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('Company'),
				'name' => 'company'
			);
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('SIRET'),
				'name' => 'siret'
			);
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('APE'),
				'name' => 'ape'
			);
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('Website'),
				'name' => 'website'
			);
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('Allowed outstanding amount'),
				'name' => 'outstanding_allow_amount',
				'hint' => $this->l('Valid characters:').' 0-9',
				'suffix' => $this->context->currency->sign
			);
			$this->fields_form['input'][] = array(
				'type' => 'text',
				'label' => $this->l('Maximum number of payment days'),
				'name' => 'max_payment_days',
				'hint' => $this->l('Valid characters:').' 0-9'
			);
			$this->fields_form['input'][] = array(
				'type' => 'select',
				'label' => $this->l('Risk rating'),
				'name' => 'id_risk',
				'required' => false,
				'class' => 't',
				'options' => array(
					'query' => $list_risks,
					'id' => 'id_risk',
					'name' => 'name'
				),
			);
		}

		$this->fields_form['submit'] = array(
			'title' => $this->l('Save'),
		);

		$birthday = explode('-', $this->getFieldValue($obj, 'birthday'));

		$this->fields_value = array(
			'years' => $this->getFieldValue($obj, 'birthday') ? $birthday[0] : 0,
			'months' => $this->getFieldValue($obj, 'birthday') ? $birthday[1] : 0,
			'days' => $this->getFieldValue($obj, 'birthday') ? $birthday[2] : 0,
		);

		// Added values of object Group
		if (!Validate::isUnsignedId($obj->id))
			$customer_groups = array();
		else
			$customer_groups = $obj->getGroups();
		$customer_groups_ids = array();
		if (is_array($customer_groups))
			foreach ($customer_groups as $customer_group)
				$customer_groups_ids[] = $customer_group;

		// if empty $carrier_groups_ids : object creation : we set the default groups
		if (empty($customer_groups_ids))
		{
			$preselected = array(Configuration::get('PS_UNIDENTIFIED_GROUP'), Configuration::get('PS_GUEST_GROUP'), Configuration::get('PS_CUSTOMER_GROUP'));
			$customer_groups_ids = array_merge($customer_groups_ids, $preselected);
		}

		foreach ($groups as $group)
			$this->fields_value['groupBox_'.$group['id_group']] =
				Tools::getValue('groupBox_'.$group['id_group'], in_array($group['id_group'], $customer_groups_ids));

		return AdminController::renderForm();
	}
}

