<?php
/* Copyright (C) 2012-2013  Charles-Fr BENKE		<charles.fr@benke.fr>
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
 * or see https://www.gnu.org/
 */

/**
 * \file       htdocs/core/class/html.formexpensereport.class.php
 * \ingroup    core
 * \brief      File of class with all html predefined components
 */

/**
 *	Class to manage generation of HTML components for contract module
 */
class FormExpenseReport
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';


	/**
	 * Constructor
	 *
	 * @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 *    Retourne la liste deroulante des differents etats d'une note de frais.
	 *    Les valeurs de la liste sont les id de la table c_expensereport_statuts
	 *
	 *    @param    int     $selected       preselect status
	 *    @param    string  $htmlname       Name of HTML select
	 *    @param    int     $useempty       1=Add empty line
	 *    @param    int     $useshortlabel  Use short labels
	 *    @return   string                  HTML select with status
	 */
	public function selectExpensereportStatus($selected = '', $htmlname = 'fk_statut', $useempty = 1, $useshortlabel = 0)
	{
		global $langs;

		$tmpep = new ExpenseReport($this->db);

		print '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'">';
		if ($useempty) print '<option value="-1">&nbsp;</option>';
		$arrayoflabels = $tmpep->statuts;
		if ($useshortlabel) $arrayoflabels = $tmpep->statuts_short;
		foreach ($arrayoflabels as $key => $val)
		{
			if ($selected != '' && $selected == $key)
			{
				print '<option value="'.$key.'" selected>';
			} else {
				print '<option value="'.$key.'">';
			}
			print $langs->trans($val);
			print '</option>';
		}
		print '</select>';
		print ajax_combobox($htmlname);
	}

	/**
	 *  Return list of types of notes with select value = id
	 *
	 *  @param      int     $selected       Preselected type
	 *  @param      string  $htmlname       Name of field in form
	 *  @param      int     $showempty      Add an empty field
	 *  @param      int     $active         1=Active only, 0=Unactive only, -1=All
	 *  @return     string                  Select html
	 */
	public function selectTypeExpenseReport($selected = '', $htmlname = 'type', $showempty = 0, $active = 1)
	{
		// phpcs:enable
		global $langs, $user;
		$langs->load("trips");

		$out = '';

		$out .= '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'">';
		if ($showempty)
		{
			$out .= '<option value="-1"';
			if ($selected == -1) $out .= ' selected';
			$out .= '>&nbsp;</option>';
		}

		$sql = "SELECT c.id, c.code, c.label as type FROM ".MAIN_DB_PREFIX."c_type_fees as c";
		if ($active >= 0) $sql .= " WHERE c.active = ".$active;
		$sql .= " ORDER BY c.label ASC";
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;

			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
				$out .= '<option value="'.$obj->id.'"';
				if ($obj->code == $selected || $obj->id == $selected) $out .= ' selected';
				$out .= '>';
				if ($obj->code != $langs->trans($obj->code)) $out .= $langs->trans($obj->code);
				else $out .= $langs->trans($obj->type);
				$i++;
			}
		}
		$out .= '</select>';
		$out .= ajax_combobox($htmlname);

		return $out;
	}

    public function selectIkRange($selected = '', $htmlname = 'fk_c_exp_tax_range', $showempty = 0, $onlyactive = true, $fk_c_exp_tax_cat = 0, $returnarray = false)
    {
		global $langs;

		$langs->load('trips');

        $outArray = array();

		$sql = "SELECT r.rowid, c.label, r.range_ik, ik.coef, ik.ikoffset, r.fk_c_exp_tax_cat";
        $sql .= " FROM ".MAIN_DB_PREFIX."expensereport_ik ik";
        $sql .= " INNER JOIN ".MAIN_DB_PREFIX."c_exp_tax_range r ON r.rowid = ik.fk_range";
        $sql .= " INNER JOIN ".MAIN_DB_PREFIX."c_exp_tax_cat c ON c.rowid = ik.fk_c_exp_tax_cat AND c.rowid = r.fk_c_exp_tax_cat";
        $sql .= " WHERE 1 = 1";

		if ($onlyactive) {
            $sql .= " AND ik.active != 0 AND r.active != 0 AND c.active != 0";
        }

        if ($fk_c_exp_tax_cat > 0) {
            $sql .= " AND c.rowid = ".intval($fk_c_exp_tax_cat);
        }

		$sql .= " ORDER BY c.rowid ASC, r.range_ik ASC";

		$resql = $this->db->query($sql);

        $ranges = array();

		if ($resql) {
			$num = $this->db->num_rows($resql);

            for ($i = 0; $i < $num; $i++) {
				$obj = $this->db->fetch_object($resql);

                $ranges[] = $obj;
			}

            $this->db->free($resql);
		}

		$out = '<select class="flat minwidth75imp maxwidth150" name="'.$htmlname.'" id="'.$htmlname.'">';

        if ($showempty) {
            if ($returnarray) {
                $outArray[-1] = '&nbsp;';
            } else {
                $out .= '<option value="-1"';

                if ($selected == -1) {
                    $out .= ' selected';
                }

                $out .= '>&nbsp;</option>';
            }
        }

        foreach ($ranges as $i => $range) {
            $label = '';

            if ($fk_c_exp_tax_cat <= 0) {
                $label .= $langs->trans($range->label) . ' - ';
            }

            if (isset($ranges[$i + 1]) && $ranges[$i + 1]->fk_c_exp_tax_cat == $range->fk_c_exp_tax_cat) {
                $label .= $langs->trans('expenseReportRangeFromTo', $range->range_ik, $ranges[$i + 1]->range_ik);
            } else {
                $label .= $langs->trans('expenseReportRangeMoreThan', $range->range_ik);
            }

            if ($returnarray) {
                $outArray[$range->rowid] = array(
                    'label' => $label,
                    'data' => array(
                        'offset' => $range->ikoffset,
                        'coef' => $range->coef
                    )
                );
            } else {
                $out .= '<option value="'.$range->rowid.'"';
                $out .= ' data-offset="'.$range->ikoffset.'"';
                $out .= ' data-coef="'.$range->coef.'"';

                if ($range->rowid == $selected) {
                    $out .= ' selected';
                }

                $out .= '>'.$label.'</option>';
            }
        }

        if ($returnarray) {
            return $outArray;
        }

		$out .= '</select>';
		$out .= ajax_combobox($htmlname);

		return $out;
    }

    /**
     * @param int $fk_c_exp_tax_range Id of mileage range
     * @return string
     */
    public function getIkRangeLabel($fk_c_exp_tax_range)
    {
        global $langs;

        $sqlStart = "SELECT r.rowid, c.label, r.range_ik, r.fk_c_exp_tax_cat";
        $sqlStart .= " FROM ".MAIN_DB_PREFIX."c_exp_tax_range r";
        $sqlStart .= " INNER JOIN ".MAIN_DB_PREFIX."c_exp_tax_cat c ON c.rowid = r.fk_c_exp_tax_cat";
        $sqlStart .= " WHERE r.rowid = ".intval($fk_c_exp_tax_range);

        $resqlStart = $this->db->query($sqlStart);

        if (! $resqlStart) {
            $this->error = $this->db->lasterror;
            return '';
        }

        if ($this->db->num_rows($resqlStart) == 0) {
            $this->error = $langs->trans('ErrorRecordNotFound');
            $this->db->free($resqlStart);
            return '';
        }

        $objStart = $this->db->fetch_object($resqlStart);

        $this->db->free($resqlStart);

        $sqlEnd = "SELECT r.rowid, c.label, r.range_ik, r.fk_c_exp_tax_cat";
        $sqlEnd .= " FROM ".MAIN_DB_PREFIX."c_exp_tax_range r";
        $sqlEnd .= " INNER JOIN ".MAIN_DB_PREFIX."c_exp_tax_cat c ON c.rowid = r.fk_c_exp_tax_cat";
        $sqlEnd .= " WHERE r.fk_c_exp_tax_cat = ".intval($objStart->fk_c_exp_tax_cat);
        $sqlEnd .= " AND r.range_ik > ".floatval($objStart->range_ik);
        $sqlEnd .= " ORDER BY r.range_ik ASC";
        $sqlEnd .= " LIMIT 1";

        $resqlEnd = $this->db->query($sqlEnd);

        if (! $resqlEnd) {
            $this->error = $this->db->lasterror;
            return '';
        }

        if ($this->db->num_rows($resqlEnd) == 0) {
            $out = $langs->trans('expenseReportRangeMoreThan', $objStart->range_ik);
        } else {
            $objEnd = $this->db->fetch_object($resqlEnd);

            $out = $langs->trans('expenseReportRangeFromTo', $objStart->range_ik, $objEnd->range_ik);;
        }

        $this->db->free($resqlEnd);

        return $out;
    }
}
