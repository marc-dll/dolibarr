<?php
/* Copyright (C) 2017		ATM Consulting			<support@atm-consulting.fr>
 * Copyright (C) 2017		Pierre-Henry Favre		<phf@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/expensereport/class/expensereport_ik.class.php
 *	\ingroup    expenseik
 *	\brief      File of class to manage expense ik
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/coreobject.class.php';

/**
 *	Class to manage inventories
 */
class ExpenseReportRule extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'expenserule';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'expensereport_rules';

	/**
	 * @var string Fieldname with ID of parent key if this field has a parent
	 */
	public $fk_element = 'fk_expense_rule';

	/**
	 * date start
	 * @var int|string
	 */
	public $dates;

	/**
	 * date end
	 * @var int|string
	 */
	public $datee;

	/**
	 * amount
	 * @var double
	 */
	public $amount;

	/**
	 * restrective
	 * @var int
	 */
	public $restrictive;

	/**
	 * rule for user
	 * @var int
	 */
	public $fk_user;

	/**
	 * rule for group
	 * @var int
	 */
	public $fk_usergroup;

	/**
	 * c_type_fees id
	 * @var int
	 */
	public $fk_c_type_fees;

	/**
	 * code type of expense report
	 * @var string
	 */
	public $code_expense_rules_type;

	/**
	 * rule for all
	 * @var int
	 */
	public $is_for_all;

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 1;


	/**
	 * Attribute object linked with database
	 * @var array
	 */
	public $fields = array(
		'rowid'=>array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-2, 'noteditable'=>1, 'notnull'=> 1, 'index'=>1, 'position'=>1, 'comment'=>'Id', 'css'=>'left'),
		'entity'=>array('type'=>'integer', 'label'=>'Entity', 'enabled'=>1, 'visible'=>0, 'notnull'=> 1, 'default'=>1, 'index'=>1, 'position'=>10),
		'dates'=>array('type'=>'date', 'label'=>'ExpenseReportDateStart', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'position'=>20),
		'datee'=>array('type'=>'date', 'label'=>'ExpenseReportDateEnd', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'position'=>25),
		'amount'=>array('type'=>'double(24,8)', 'label'=>'ExpenseReportLimitAmount', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'position'=>30),
		'restrictive'=>array('type'=>'integer', 'label'=>'ExpenseReportRestrictive', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'position'=>40),
		'is_for_all'=>array('type'=>'integer', 'label'=>'Everybody', 'enabled'=>1, 'visible'=>1, 'notnull'=>1, 'position'=>50),
		'fk_user'=>array('type'=>'integer:User:user/class/user.class.php', 'label'=>'User', 'enabled'=>1, 'visible'=>1, 'position'=>50),
		'fk_usergroup'=>array('type'=>'integer:Usergroup:user/class/usergroup.class.php', 'label'=>'Usergroup', 'enabled'=>1, 'visible'=>1, 'position'=>50),
		'fk_c_type_fees'=>array('type'=>'integer', 'label'=>'Type', 'enabled'=>1, 'visible'=>1, 'position'=>60),
		'code_expense_rules_type'=>array('type'=>'string', 'label'=>'Code', 'enabled'=>1, 'visible'=>1, 'position'=>70),
	);

	/**
	 *  Constructor
	 *
	 *  @param      DoliDB		$db      Database handler
	 */
	public function __construct(DoliDB &$db)
	{
        $this->db = $db;
	}

    /**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		return $this->fetchCommon($id, $ref);
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 * Return all rules or filtered by something
	 *
	 * @param int	     $fk_c_type_fees	type of expense
	 * @param integer	 $date			    date of expense
	 * @param int        $fk_user		    user of expense
	 * @return array                        Array with ExpenseReportRule
	 */
	public static function getAllRule($fk_c_type_fees = '', $date = '', $fk_user = '')
	{
		global $db;

		$rules = array();
		$sql = 'SELECT er.rowid';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'expensereport_rules er';
		$sql .= ' WHERE er.entity IN (0,'.getEntity('').')';
		if (!empty($fk_c_type_fees))
		{
			$sql .= ' AND er.fk_c_type_fees IN (-1, '.$fk_c_type_fees.')';
		}
		if (!empty($date))
		{
			$date = dol_print_date($date, '%Y-%m-%d');
			$sql .= ' AND er.dates <= \''.$date.'\'';
			$sql .= ' AND er.datee >= \''.$date.'\'';
		}
		if ($fk_user > 0)
		{
			$sql .= ' AND (er.is_for_all = 1';
			$sql .= ' OR er.fk_user = '.$fk_user;
			$sql .= ' OR er.fk_usergroup IN (SELECT ugu.fk_usergroup FROM '.MAIN_DB_PREFIX.'usergroup_user ugu WHERE ugu.fk_user = '.$fk_user.') )';
		}
		$sql .= ' ORDER BY er.is_for_all, er.fk_usergroup, er.fk_user';

		dol_syslog("ExpenseReportRule::getAllRule sql=".$sql);

		$resql = $db->query($sql);
		if ($resql)
		{
			while ($obj = $db->fetch_object($resql))
			{
				$rule = new ExpenseReportRule($db);
				if ($rule->fetch($obj->rowid) > 0) $rules[$rule->id] = $rule;
				else dol_print_error($db);
			}
		} else {
			dol_print_error($db);
		}

		return $rules;
	}

	/**
	 * Return the label of group for the current object
	 *
	 * @return string
	 */
	public function getGroupLabel()
	{
		include_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';

		if ($this->fk_usergroup > 0)
		{
			$group = new UserGroup($this->db);
			if ($group->fetch($this->fk_usergroup) > 0)
			{
				return $group->nom;
			} else {
				$this->error = $group->error;
				$this->errors[] = $this->error;
			}
		}

		return '';
	}

	/**
	 * Return the name of user for the current object
	 *
	 * @return string
	 */
	public function getUserName()
	{
		include_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';

		if ($this->fk_user > 0)
		{
			$u = new User($this->db);
			if ($u->fetch($this->fk_user) > 0)
			{
				return dolGetFirstLastname($u->firstname, $u->lastname);
			} else {
				$this->error = $u->error;
				$this->errors[] = $this->error;
			}
		}

		return '';
	}
}
