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


/**
 *	Class to manage inventories
 */
class ExpenseReportIk extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'expenseik';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'expensereport_ik';

	/**
	 * @var string Field with ID of parent key if this field has a parent
	 */
	public $fk_element = 'fk_expense_ik';

	/**
	 * c_exp_tax_cat Id
	 * @var int
	 */
	public $fk_c_exp_tax_cat;

	/**
	 * c_exp_tax_range id
	 * @var int
	 */
	public $fk_range;

	/**
	 * Coef
	 * @var double
	 */
	public $coef;

	/**
	 * Offset
	 * @var double
	 */
	public $ikoffset;

    /**
     * Expense report vehicle range
     * @var type stdClass
     */
    public $range = null;

    /**
     * Attribute object linked with database
     * @var array
     */
    public $fields = array(
        'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>1, 'noteditable'=>1, 'notnull'=>1, 'index'=>1, 'position'=>1, 'comment'=>'Id', 'css'=>'left'),
        'fk_c_exp_tax_cat' => array('type'=>'sellist:c_exp_tax_cat:label:rowid::active=1', 'label'=>'VehicleCategory', 'enabled'=>1, 'visible'=>1, 'default'=>0, 'notnull'=>1, 'position'=>10),
        'fk_range' => array('type'=>'sellist:c_exp_tax_range:label:rowid:fk_c_exp_tax_cat:active=1', 'label'=>'RangeIk', 'enabled'=>1, 'visible'=>1, 'default'=>0, 'notnull'=>1, 'position'=>20),
        'coef' => array('type'=>'double', 'label'=>'expenseReportCoef', 'enabled'=>1, 'visible'=>1, 'default'=>0, 'notnull'=>1, 'position'=>30),
        'ikoffset' => array('type'=>'double', 'label'=>'expenseReportOffset', 'enabled'=>1, 'visible'=>1, 'default'=>0, 'notnull'=>1, 'position'=>40),
        'active' => array('type'=>'integer', 'label'=>'Active', 'enabled'=>1, 'visible'=>1, 'default'=>1, 'notnull'=>1, 'position'=>50),
        'datec' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'visible'=>-2, 'default'=>null, 'position'=>500),
        'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-2, 'notnull'=> 0, 'position'=>501)
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
		$res = $this->fetchCommon($id, $ref);

        if ($res <= 0) {
            return $res;
        }

        $resRange = $this->fetchTaxRange();

        if ($resRange < 0) {
            return $resRange;
        }

        return $res;
	}

    /**
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetchTaxRange()
    {
        if ($this->fk_range <= 0) {
            $this->range = null;
            return 0;
        }

        $sql = 'SELECT rowid as id, fk_c_exp_tax_cat, range_ik, active';
        $sql .= ' FROM '.MAIN_DB_PREFIX.'c_exp_tax_range';
        $sql .= ' WHERE rowid = '.intval($this->fk_range);

        $resql = $this->db->query($sql);

        if (! $resql) {
            $this->error = $this->db->lasterror;
            return -1;
        }

        $this->range = $this->db->fetch_object($resql);

        $this->db->free($resql);

        return 1;
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
	 * Return an array of ranges for a category
	 *
	 * @param int	$fk_c_exp_tax_cat	category id
	 * @param int	$active				active
	 * @return array
	 */
	public static function getRangesByCategory($fk_c_exp_tax_cat, $active = 1)
	{
		global $db;

		$ranges = array();

		$sql = 'SELECT eik.rowid FROM '.MAIN_DB_PREFIX.'expensereport_ik eik';
		$sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'c_exp_tax_range r ON r.rowid = eik.fk_range';
		if ($active) $sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'c_exp_tax_cat c ON (r.fk_c_exp_tax_cat = c.rowid)';
		$sql .= ' WHERE r.fk_c_exp_tax_cat = '.$fk_c_exp_tax_cat;
		if ($active) $sql .= ' AND r.active = 1 AND c.active = 1';
		$sql .= ' ORDER BY r.range_ik';

		dol_syslog(get_called_class().'::getRangesByCategory sql='.$sql, LOG_DEBUG);
		$resql = $db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);
			if ($num > 0)
			{
				while ($obj = $db->fetch_object($resql))
				{
					$object = new ExpenseReportIk($db);
					$object->fetch($obj->rowid);

					$ranges[] = $object;
				}
			}
		} else {
			dol_print_error($db);
		}

		return $ranges;
	}

	/**
	 * Return an array of ranges grouped by category
	 *
	 * @return array
	 */
	public static function getAllRanges()
	{
		global $db;

		$ranges = array();

		$sql = ' SELECT r.rowid, r.fk_c_exp_tax_cat, r.range_ik, c.label, i.rowid as fk_expense_ik, r.active as range_active, c.active as cat_active';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'c_exp_tax_range r';
		$sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'c_exp_tax_cat c ON (r.fk_c_exp_tax_cat = c.rowid)';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'expensereport_ik i ON (r.rowid = i.fk_range)';
		$sql .= ' WHERE r.entity IN (0, '.getEntity('').')';
		$sql .= ' ORDER BY r.fk_c_exp_tax_cat, r.range_ik';

		dol_syslog(get_called_class().'::getAllRanges sql='.$sql, LOG_DEBUG);
		$resql = $db->query($sql);
		if ($resql)
		{
			while ($obj = $db->fetch_object($resql))
			{
				$ik = new ExpenseReportIk($db);
				if ($obj->fk_expense_ik > 0) $ik->fetch($obj->fk_expense_ik);
				$obj->ik = $ik;

				if (!isset($ranges[$obj->fk_c_exp_tax_cat])) $ranges[$obj->fk_c_exp_tax_cat] = array('label' => $obj->label, 'active' => $obj->cat_active, 'ranges' => array());
				$ranges[$obj->fk_c_exp_tax_cat]['ranges'][] = $obj;
			}
		} else {
			dol_print_error($db);
		}

		return $ranges;
	}
}
