<?php

namespace srag\Plugins\UserDefaults\UDFCheck;

use ActiveRecord;
use ilObjUser;
use ilUserDefaultsPlugin;
use ilUserSearchOptions;
use srag\ActiveRecordConfig\ActiveRecordConfig;
use srag\DIC\DICTrait;
use ilUserDefinedFields;

/**
 * Class UDFCheck
 *
 * @package srag\Plugins\UserDefaults\UDFChec
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class UDFCheck extends ActiveRecord {

	use DICTrait;
	const TABLE_NAME = 'usr_def_checks';
	const PLUGIN_CLASS_NAME = ilUserDefaultsPlugin::class;
	const OP_EQUALS = 1;
	const OP_STARTS_WITH = 2;
	const OP_CONTAINS = 3;
	const OP_ENDS_WITH = 4;
	const OP_NOT_EQUALS = 5;
	const OP_NOT_STARTS_WITH = 6;
	const OP_NOT_CONTAINS = 7;
	const OP_NOT_ENDS_WITH = 8;
	const OP_IS_EMPTY = 9;
	const OP_NOT_IS_EMPTY = 10;
	const OP_REG_EX = 11;
	const STATUS_INACTIVE = 1;
	const STATUS_ACTIVE = 2;
	const CHECK_SPLIT = ' → ';

	const FIELD_CATEGORY_USR = 1;
	const FIELD_CATEGORY_UDF = 2;
	/**
	 * @var array|null
	 */
	protected static $all_definitions = NULL;
	/**
	 * @var array
	 */
	public static $operator_text_keys = array(
		self::OP_EQUALS => 'equals',
		self::OP_STARTS_WITH => 'starts_with',
		self::OP_CONTAINS => 'contains',
		self::OP_ENDS_WITH => 'ends_with',
		self::OP_NOT_EQUALS => 'not_equals',
		self::OP_NOT_STARTS_WITH => 'not_starts_with',
		self::OP_NOT_CONTAINS => 'not_contains',
		self::OP_NOT_ENDS_WITH => 'not_ends_with',
		self::OP_IS_EMPTY => 'is_empty',
		self::OP_NOT_IS_EMPTY => 'not_is_empty',
		self::OP_REG_EX => 'reg_ex',
	);


	/**
	 * @return string
	 */
	public function getConnectorContainerName() {
		return self::TABLE_NAME;
	}


	/**
	 * @return string
	 * @deprecated
	 */
	public static function returnDbTableName() {
		return self::TABLE_NAME;
	}


	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_sequence   true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $id = 0;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $parent_id = 0;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 *
	 */
	protected $field_key = 1;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 * @db_is_notnull  true
	 */
	protected $field_category = self::FIELD_CATEGORY_USR;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $check_value = '';
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $operator = self::OP_EQUALS;
	/**
	 * @var bool
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $negated = false;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $owner = 6;
	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 */
	protected $status = self::STATUS_ACTIVE;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        timestamp
	 * @db_is_notnull       true
	 */
	protected $create_date;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        timestamp
	 * @db_is_notnull       true
	 */
	protected $update_date;


	/**
	 * @param       $primary_key
	 * @param array $add_constructor_args
	 *
	 * @return UDFCheck
	 */
	public static function find($primary_key, array $add_constructor_args = array()) {
		return parent::find($primary_key, $add_constructor_args); // TODO: Change the autogenerated stub
	}


	public function update() {
		$this->setOwner(self::dic()->user()->getId());
		$this->setUpdateDate(time());
		parent::update();
	}


	public function create() {
		$this->setOwner(self::dic()->user()->getId());
		$this->setUpdateDate(time());
		$this->setCreateDate(time());
		parent::create();
	}


	/**
	 * @param string $check_value
	 */
	public function setCheckValue($check_value) {
		$this->check_value = $check_value;
	}


	/**
	 * @param string[] $check_values
	 */
	public function setCheckValues(array $check_values) {
		$this->check_value = implode(self::CHECK_SPLIT, array_map(function ($check_value) {
			return trim($check_value);
		}, $check_values));
	}


	/**
	 * @return string
	 */
	public function getCheckValue() {
		return $this->check_value;
	}


	/**
	 * @return string[]
	 */
	public function getCheckValues() {
		return array_map(function ($check_value) {
			return trim($check_value);
		}, explode(self::CHECK_SPLIT, $this->check_value));
	}


	/**
	 * @param string $field_key
	 */
	public function setFieldKey($field_key) {
		$this->field_key = $field_key;
	}


	/**
	 * @return string
	 */
	public function getFieldKey() {
		return $this->field_key;
	}


	/**
	 * @return string
	 */
	public function getFieldCategory() {
		return $this->field_category;
	}


	/**
	 * @param string $field_category
	 */
	public function setFieldCategory($field_category) {
		$this->field_category = $field_category;
	}

	/**
	 * @param int $operator
	 */
	public function setOperator($operator) {
		$this->operator = $operator;
	}


	/**
	 * @return int
	 */
	public function getOperator() {
		return $this->operator;
	}


	/**
	 * @param int $create_date
	 */
	public function setCreateDate($create_date) {
		$this->create_date = $create_date;
	}


	/**
	 * @return int
	 */
	public function getCreateDate() {
		return $this->create_date;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $owner
	 */
	public function setOwner($owner) {
		$this->owner = $owner;
	}


	/**
	 * @return int
	 */
	public function getOwner() {
		return $this->owner;
	}


	/**
	 * @param int $update_date
	 */
	public function setUpdateDate($update_date) {
		$this->update_date = $update_date;
	}


	/**
	 * @return int
	 */
	public function getUpdateDate() {
		return $this->update_date;
	}


	/**
	 * @param int $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}


	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}


	/**
	 * @param int $parent_id
	 */
	public function setParentId($parent_id) {
		$this->parent_id = $parent_id;
	}


	/**
	 * @return int
	 */
	public function getParentId() {
		return $this->parent_id;
	}


	/**
	 * @param $field_name
	 *
	 * @return mixed|null|string
	 */
	public function sleep($field_name) {
		switch ($field_name) {
			case 'create_date':
			case 'update_date':
				return date(ActiveRecordConfig::SQL_DATE_FORMAT, $this->{$field_name});
				break;
		}

		return NULL;
	}


	/**
	 * @param $field_name
	 * @param $field_value
	 *
	 * @return mixed|null
	 */
	public function wakeUp($field_name, $field_value) {
		switch ($field_name) {
			case 'create_date':
			case 'update_date':
				return strtotime($field_value);
				break;
		}

		return NULL;
	}


	/**
	 * @param ilObjUser $ilUser
	 *
	 * @return bool
	 */
	public function isValid(ilObjUser $ilUser) {


		switch($this->getFieldCategory()) {
			case self::FIELD_CATEGORY_USR:
				$values[$this->getFieldKey()] = $this->getUserFieldValue($ilUser,$this->getFieldKey());
				break;
			case self::FIELD_CATEGORY_UDF:
				$ilUser->readUserDefinedFields();
				$values = array_map(function ($value) {
					return trim($value);
				}, explode(self::CHECK_SPLIT, $ilUser->user_defined_data['f_' . $this->getFieldKey()]));
				break;
		}

		$check_values = $this->getCheckValues();

		foreach ($check_values as $key => $check_value) {
			$value = reset($values); //TODO

			if (!empty($value) && !empty($check_value)) {
				switch ($this->getOperator()) {
					case self::OP_EQUALS:
						$valid = ($value === $check_value);
						break;

					case self::OP_NOT_EQUALS:
						$valid = ($value !== $check_value);
						break;

					case self::OP_STARTS_WITH:
						$valid = (strpos($value, $check_value) === 0);
						break;

					case self::OP_NOT_STARTS_WITH:
						$valid = (strpos($value, $check_value) !== 0);
						break;

					case self::OP_ENDS_WITH:
						$valid = (strrpos($value, $check_value) === (strlen($value) - strlen($check_value)));
						break;

					case self::OP_NOT_ENDS_WITH:
						$valid = (strrpos($value, $check_value) !== (strlen($value) - strlen($check_value)));
						break;

					case self::OP_CONTAINS:
						$valid = (strpos($value, $check_value) !== false);
						break;

					case self::OP_NOT_CONTAINS:
						$valid = (strpos($value, $check_value) === false);
						break;

					case self::OP_IS_EMPTY:
						$valid = empty($value);
						break;

					case self::OP_NOT_IS_EMPTY:
						$valid = (!empty($value));
						break;

					case self::OP_REG_EX:
						// Fix RegExp
						if ($check_value[0] !== "/" && $check_value[strlen($check_value) - 1] !== "/") {
							$check_value = "/$check_value/";
						}
						$valid = (preg_match($check_value, $value) === 1);
						break;

					default:
						return false;
				}
			}

			if (!$valid) {
				break;
			}
		}

		$b = (!$this->isNegated() === $valid);

		return $b;
	}


	/**
	 * @param ilObjUser $user
	 * @param $field_name
	 */
	public function getUserFieldValue($user, $field_name) {

		switch($field_name) {
			case 'gender':
				return $user->getGender();
				break;
			case 'lastname':
				return $user->getLastname();
				break;
			case 'firstname':
				return $user->getFirstname();
				break;
			case 'login':
				return $user->getLogin();
				break;
			case 'title':
				return $user->getTitle();
				break;
			case 'institution':
				return $user->getInstitution();
				break;
			case 'department':
				return $user->getDepartment();
				break;
			case 'street':
				return $user->getStreet();
				break;
			case 'zipcode':
				return $user->getZipcode();
				break;
			case 'city':
				return $user->getCity();
				break;
			case 'country':
				return $user->getCountry();
				break;
			case 'sel_country':
				return $user->getSelectedCountry();
				break;
			case 'email':
				return $user->getEmail();
				break;
			case 'second_email':
				return $user->getSecondEmail();
				break;
			case 'hobby':
				return $user->getHobby();
				break;
			case 'org_units':
				return $user->getOrgUnitsRepresentation();
				break;
			case 'matriculation':
				return $user->getMatriculation();
				break;
			case 'interests_general':
				return $user->getGeneralInterestsAsText();
				break;
			case 'interests_help_offered':
				return $user->getOfferingHelpAsText();
			break;
			case 'interests_help_looking':
				return $user->getLookingForHelpAsText();
			break;
		}

		return '';

	}


	/**
	 * @return array
	 */
	public static function getAllDefinitions() {

		//require_once "./Services/User/classes/class.ilUserDefinedFields.php";

		if(!is_null(self::$all_definitions)) {
			return self::$all_definitions;
		}


		$usr_fields = array();
		foreach(ilUserSearchOptions::_getSearchableFieldsInfo(true) as $field) {
			$usr_field = array();

			if(!in_array($field['type'], array(FIELD_TYPE_TEXT,FIELD_TYPE_SELECT,FIELD_TYPE_MULTI))) {
				continue;
			}

			$usr_field["txt"] =  $field["lang"];
			$usr_field["field_category"] = self::FIELD_CATEGORY_USR;
			$usr_field["field_key"] = $field["db"];
			$usr_field["field_type"] = $field["type"];
			$usr_field["field_values"] = $field["values"];


			$usr_fields[] = $usr_field;
		}

		$udf_fields = array();
		$user_defined_fields = ilUserDefinedFields::_getInstance();
		foreach($user_defined_fields->getDefinitions() as $field) {
			$udf_field = array();



			if(!in_array($field['field_type'], array(UDF_TYPE_TEXT,UDF_TYPE_SELECT))) {
				continue;
			}

			$udf_field["txt"] =  $field["field_name"];
			$udf_field["field_category"] = self::FIELD_CATEGORY_UDF;
			$udf_field["field_key"] = $field["field_id"];
			$udf_field["field_type"] = $field["field_type"];
			$udf_field["field_values"]  = $field["field_values"];


			$udf_fields[] = $udf_field;
		}

		self::$all_definitions = array_merge($usr_fields,$udf_fields);


		return self::$all_definitions;
	}


	/**
	 * @param $id
	 *
	 * @return array
	 */
	public static function getDefinitionForId($id) {
		$definitions = self::getAllDefinitions();

		return $definitions[$id];
	}


	/**
	 * @return array
	 */
	public static function getDefinitionData() {
		/*return array_map(function (array $field) {
			return $field["txt"];
		}, self::getAllDefinitions());*/
		$return = array();
		foreach (self::getAllDefinitions() as $def) {
			$return[$def['field_key']] = $def['txt'];
		}

		return $return;
	}


	/**
	 * @param $udf_field_id
	 *
	 * @return array
	 */
	public static function getDefinitionValuesForKey($field_keyd) {
		$return = array();

		foreach (self::getAllDefinitions() as $def) {
			if ($def['field_key'] == $field_keyd) {


				switch($def['field_category']) {
					case self::FIELD_CATEGORY_USR:
						foreach ($def['field_values'] as $key => $val) {
							$return[$key] = $val;
						}
						break;
					case self::FIELD_CATEGORY_UDF:
						foreach ($def['field_values'] as $val) {
							$return[$val] = $val;
						}
						break;
				}


				return $return;
			}
		}

		return array();
	}


	/**
	 * @param $field_key
	 *
	 * @return int
	 */
	public static function getDefinitionTypeForKey($field_key) {



		foreach (self::getAllDefinitions() as $def) {
			if ($def['field_key'] == $field_key) {
				return $def['field_type'];
			}
		}

		return 0;
	}

	/**
	 * @param $field_key
	 *
	 * @return int
	 */
	public static function getDefinitionCategoryForKey($field_key) {


		foreach (self::getAllDefinitions() as $def) {
			if ($def['field_key'] == $field_key) {
				return $def['field_category'];
			}
		}

		return 0;
	}

	/**
	 * @param $field_key
	 * @param $field_category
	 *
	 * @return int
	 */
	public static function getDefinitionFieldTitleForKey($field_key) {


		foreach (self::getAllDefinitions() as $def) {
			if ($def['field_key'] == $field_key) {
				return $def['txt'];
			}
		}

		return 0;
	}






	/**
	 * @return boolean
	 */
	public function isNegated() {
		return $this->negated;
	}


	/**
	 * @param boolean $negated
	 */
	public function setNegated($negated) {
		$this->negated = $negated;
	}
}
