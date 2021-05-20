<?php
/* Copyright (C) 2017 ATM Consulting      <contact@atm-consulting.fr>
 * Copyright (C) 2017 Pierre-Henry Favre  <phf@atm-consulting.fr>
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
 *       \file       htdocs/expensereport/ajax/ajaxprojet.php
 *       \ingroup    expensereport
 *       \brief      File to return Ajax response on third parties request
 */

if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1); // Disables token renewal
if (!defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (!defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if (!defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (!defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');
if (!defined('NOCSRFCHECK'))    define('NOCSRFCHECK', '1');

$res = 0;
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/expensereport/class/expensereport.class.php';
require_once DOL_DOCUMENT_ROOT.'/expensereport/class/expensereport_ik.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formexpensereport.class.php';

// Load translation files required by the page
$langs->loadlangs(array('errors', 'trips'));

/*
 * View
 */

top_httphead('application/json');


dol_syslog(join(',', $_POST));

$fk_c_exp_tax_cat = GETPOST('fk_c_exp_tax_cat');

if (empty($fk_c_exp_tax_cat) || $fk_c_exp_tax_cat < 0) {
    echo json_encode(array('error' => $langs->transnoentitiesnoconv('ErrorBadValueForParameter', $fk_c_exp_tax_cat, 'fk_c_exp_tax_cat')));

    $db->close();
    exit;
}

$formexpensereport = new FormExpenseReport($db);

$ranges = $formexpensereport->selectIkRange('', '', 0, true, $fk_c_exp_tax_cat, true);

if (empty($ranges)) {
    echo json_encode(array('error' => $langs->transnoentitiesnoconv('ErrorRecordNotFound'), 'ranges' => $ranges));

    $db->close();
    exit;
}

$ret = json_encode($ranges);

if (false === $ret) {
    echo json_encode(array('error' => $langs->transnoentitiesnoconv('ErrorCouldNotEncodeData') . ' : ' . json_last_error_msg()));

    $db->close();
    exit;
}


echo $ret;

$db->close();
