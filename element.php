<?php
/* Copyright (C) 2001-2004  Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2020  Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010  Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2012-2016  Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2015-2021  Alexandre Spangaro   <aspangaro@open-dsi.fr>
 * Copyright (C) 2015       Marcos García        <marcosgdf@gmail.com>
 * Copyright (C) 2016       Josep Lluís Amador   <joseplluis@lliuretic.cat>
 * Copyright (C) 2021       Gauthier VERDOL      <gauthier.verdol@atm-consulting.fr>
 * Copyright (C) 2021       Noé Cendrier         <noe.cendrier@altairis.fr>
 * Copyright (C) 2023       Jean-Michel Bain
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
 *      \file       htdocs/projet/element.php
 *      \ingroup    projet
 *		\brief      Page of project referrers
 */

/*
TODO :
- afficher nombre > 0 de factures et autres non associés à un projet
- options filtre affichage :
	- projets et catégories cachés
	- certains tags (OR / AND / XOR)
	- date début
	- date fin
- mémoriser options affichage
- possibiliter de 'cacher' un ou plusieurs projets (ajouter un champ ou une table)
- regroupement projets cachés sur une seule ligne dans la catégorie
- ne pas afficher projets cachés, ni catégories ne comportant que des projets cachés
*/

// Load Dolibarr environment
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
if (isModEnabled('stock')) {
	require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
}
if (isModEnabled("propal")) {
	require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
}
if (isModEnabled('facture')) {
	require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
	require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture-rec.class.php';
}
if (isModEnabled('commande')) {
	require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
}
if (isModEnabled('supplier_proposal')) {
	require_once DOL_DOCUMENT_ROOT.'/supplier_proposal/class/supplier_proposal.class.php';
}
if ((isModEnabled("fournisseur") && empty($conf->global->MAIN_USE_NEW_SUPPLIERMOD)) || isModEnabled("supplier_invoice")) {
	require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
}
if ((isModEnabled("fournisseur") && empty($conf->global->MAIN_USE_NEW_SUPPLIERMOD)) || isModEnabled("supplier_order")) {
	require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
}
if (isModEnabled('contrat')) {
	require_once DOL_DOCUMENT_ROOT.'/contrat/class/contrat.class.php';
}
if (isModEnabled('ficheinter')) {
	require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';
}
if (isModEnabled("expedition")) {
	require_once DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php';
}
if (isModEnabled('deplacement')) {
	require_once DOL_DOCUMENT_ROOT.'/compta/deplacement/class/deplacement.class.php';
}
if (isModEnabled('expensereport')) {
	require_once DOL_DOCUMENT_ROOT.'/expensereport/class/expensereport.class.php';
}
if (isModEnabled('agenda')) {
	require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
}
if (isModEnabled('don')) {
	require_once DOL_DOCUMENT_ROOT.'/don/class/don.class.php';
}
if (!empty($conf->loan->enabled)) {
	require_once DOL_DOCUMENT_ROOT.'/loan/class/loan.class.php';
	require_once DOL_DOCUMENT_ROOT.'/loan/class/loanschedule.class.php';
}
if (isModEnabled('stock')) {
	require_once DOL_DOCUMENT_ROOT.'/product/stock/class/mouvementstock.class.php';
}
if (isModEnabled('tax')) {
	require_once DOL_DOCUMENT_ROOT.'/compta/sociales/class/chargesociales.class.php';
}
if (isModEnabled("banque")) {
	require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/paymentvarious.class.php';
}
if (!empty($conf->salaries->enabled)) {
	require_once DOL_DOCUMENT_ROOT.'/salaries/class/salary.class.php';
}
if (isModEnabled('categorie')) {
	require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
}
if (isModEnabled('mrp')) {
	require_once DOL_DOCUMENT_ROOT.'/mrp/class/mo.class.php';
}

// Load translation files required by the page
$langs->loadLangs(array('projects', 'companies', 'suppliers', 'compta'));
if (isModEnabled('facture')) {
	$langs->load("bills");
}
if (isModEnabled('commande')) {
	$langs->load("orders");
}
if (isModEnabled("propal")) {
	$langs->load("propal");
}
if (isModEnabled('ficheinter')) {
	$langs->load("interventions");
}
if (isModEnabled('deplacement')) {
	$langs->load("trips");
}
if (isModEnabled('expensereport')) {
	$langs->load("trips");
}
if (isModEnabled('don')) {
	$langs->load("donations");
}
if (!empty($conf->loan->enabled)) {
	$langs->load("loan");
}
if (!empty($conf->salaries->enabled)) {
	$langs->load("salaries");
}
if (isModEnabled('mrp')) {
	$langs->load("mrp");
}
if (isModEnabled('eventorganization')) {
	$langs->load("eventorganization");
}

$form = new Form($db);

// llxHeader("", $langs->trans("RprjT_Title"));

// // print title specific icon, title, and help text
// print load_fiche_titre( $form->textwithpicto($langs->trans("RprjT_Title"), $langs->trans("RprjT_HelpMain")), '', 'projectanalytic.png@projectanalytic' );

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$datesrfc = GETPOST('datesrfc');
$dateerfc = GETPOST('dateerfc');
$dates = dol_mktime(0, 0, 0, GETPOST('datesmonth'), GETPOST('datesday'), GETPOST('datesyear'));
$datee = dol_mktime(23, 59, 59, GETPOST('dateemonth'), GETPOST('dateeday'), GETPOST('dateeyear'));
if (empty($dates) && !empty($datesrfc)) {
	$dates = dol_stringtotime($datesrfc);
}
if (empty($datee) && !empty($dateerfc)) {
	$datee = dol_stringtotime($dateerfc);
}
if (!GETPOSTISSET('datesrfc') && !GETPOSTISSET('datesday') && !empty($conf->global->PROJECT_LINKED_ELEMENT_DEFAULT_FILTER_YEAR)) {
	$new = dol_now();
	$tmp = dol_getdate($new);
	//$datee=$now
	//$dates=dol_time_plus_duree($datee, -1, 'y');
	$dates = dol_get_first_day($tmp['year'], 1);
}

// set projects list
$object = new Project($db);
$projectsListId = array();
if (!empty($user->rights->projet->all->lire)) {
	$projectsListId = $object->getProjectsAuthorizedForUser($user, 2, 0, $socid);
} else {
	$projectsListId = $object->getProjectsAuthorizedForUser($user, 0, 1, $socid);
}

// -- Init projects list step --
// xdebug_break();

// make arrays for all projects and all categories
$projectsData = [];
$categoriesData = [];

$globalBalanceCumul = 0;

// projects list loop
foreach ($projectsListId as $projectsListIdKey => $projectsListIdValue) {
	
	// get project id
	$id = $projectsListIdKey;
	
	// read it
	$object = new Project($db);
	include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once
	if (!empty($conf->global->PROJECT_ALLOW_COMMENT_ON_PROJECT) && method_exists($object, 'fetchComments') && empty($object->comments)) {
		$object->fetchComments();
	}
	
	// get ref
	$projectRef = $object->ref;
	// make data array for current project
	$projectsData[$projectRef] = [];
	$projectsData[$projectRef]["categories"] = [];

	// get current project tags
	$cat = new Categorie($db);
	$categoriesIds = $cat->containing($id, Categorie::TYPE_PROJECT, $mode = 'id');
	// not any category associated ?
	if (count($categoriesIds) == 0) {
		// so category with id = -1 means project without tag/category
		$categoriesIds = [''];
	}
	
	// build up data
	foreach ($categoriesIds as $categoryId) {
		$categLabel = '';
		$categBackgroundColor = '';
		$categNomUrl = '';
		if ($categoryId != '') {
			$cat->fetch($categoryId);
			$categBackgroundColor = $cat->color;
			$categLabel = $cat->label;
			//
			$type = Categorie::TYPE_PROJECT;
			$nosearch = GETPOST('nosearch', 'int');
			$moreparam = ($nosearch ? '&nosearch=1' : '');
			$categNomUrl = $cat->getNomUrl(1, '', 60, '&backtolist='.urlencode($_SERVER["PHP_SELF"].'?type='.$type.$moreparam));
		}
		// first occurence of current category ?
		if (!in_array($categLabel, array_keys($categoriesData))) {
			$categoriesData[$categLabel] = [];
			$categoriesData[$categLabel]['projects'] = [];
			$categoriesData[$categLabel]['categBackgroundColor'] = $categBackgroundColor;
			$categoriesData[$categLabel]['categNomUrl'] = $categNomUrl;
			$categoriesData[$categLabel]['projects'] = [];
			$categoriesData[$categLabel]['balanceCumul'] = 0;
			$categoriesData[$categLabel]['keptBalanceCumul'] = 0;
			$categoriesData[$categLabel]['sales'] = 0;
		}
		// keep cross reference of projects/categories
		array_push($categoriesData[$categLabel]['projects'],$projectRef);
		$projectsData[$projectRef]["categories"][$categLabel] = [];
		
	}

	// start of project processing
	$mine = GETPOST('mode') == 'mine' ? 1 : 0;

	// thirdparty
	$morehtmlref = '<div class="refidno">';
	// Title
	$morehtmlref .= $object->title;
	// Thirdparty
	if (!empty($object->thirdparty->id) && $object->thirdparty->id > 0) {
		$morehtmlref .= '<br>'.$object->thirdparty->getNomUrl(1, 'project');
	}
	$morehtmlref .= '</div>';
	$projectsData[$projectRef]["thirdparty"] = $morehtmlref;
	// other data
	$projectsData[$projectRef]["title"] = $object->title;
	$projectsData[$projectRef]["projectNomUrl"] = $object->getNomUrl(1, (!empty(GETPOST('search_usage_event_organization', 'int'))?'eventorganization':''));

	// Security check
	$socid = $object->socid;
	//if ($user->socid > 0) $socid = $user->socid;    // For external user, no check is done on company because readability is managed by public status of project and assignement.
	//$result = restrictedArea($user, 'projet', $object->id, 'projet&project');

	$hookmanager->initHooks(array('projectOverview'));


	/*
	*	View
	*/
	$title = $langs->trans('ProjectReferers').' - '.$object->ref.' '.$object->name;
	if (!empty($conf->global->MAIN_HTML_TITLE) && preg_match('/projectnameonly/', $conf->global->MAIN_HTML_TITLE) && $object->name) {
		$title = $object->ref.' '.$object->name.' - '.$langs->trans('ProjectReferers');
	}

	$help_url = 'EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos|DE:Modul_Projekte';

	$userstatic = new User($db);

	// To verify role of users
	$userAccess = $object->restrictedProjectArea($user);

	/*
	* Referers types
	*/
	$listofreferent = array(
	'entrepot'=>array(
		'name'=>"Warehouse",
		'title'=>"ListWarehouseAssociatedProject",
		'class'=>'Entrepot',
		'table'=>'entrepot',
		'datefieldname'=>'date_entrepot',
		'urlnew'=>DOL_URL_ROOT.'/product/stock/card.php?action=create&projectid='.$id.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'entrepot',
		'buttonnew'=>'AddWarehouse',
		'project_field'=>'fk_project',
		'testnew'=>$user->hasRight('stock', 'creer'),
		'test'=>!empty($conf->stock->enabled) && $user->hasRight('stock', 'lire') && !empty($conf->global->WAREHOUSE_ASK_WAREHOUSE_DURING_PROJECT)),
	'propal'=>array(
		'name'=>"Proposals",
		'title'=>"ListProposalsAssociatedProject",
		'class'=>'Propal',
		'table'=>'propal',
		'datefieldname'=>'datep',
		'urlnew'=>DOL_URL_ROOT.'/comm/propal/card.php?action=create&origin=project&originid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'propal',
		'buttonnew'=>'AddProp',
		'testnew'=>$user->hasRight('propal', 'creer'),
		'test'=>!empty($conf->propal->enabled) && $user->hasRight('propal', 'lire')),
	'order'=>array(
		'name'=>"CustomersOrders",
		'title'=>"ListOrdersAssociatedProject",
		'class'=>'Commande',
		'table'=>'commande',
		'datefieldname'=>'date_commande',
		'urlnew'=>DOL_URL_ROOT.'/commande/card.php?action=create&projectid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'orders',
		'buttonnew'=>'CreateOrder',
		'testnew'=>$user->hasRight('commande', 'creer'),
		'test'=>!empty($conf->commande->enabled) && $user->hasRight('commande', 'lire')),
	'invoice'=>array(
		'name'=>"CustomersInvoices",
		'title'=>"ListInvoicesAssociatedProject",
		'class'=>'Facture',
		'margin'=>'add',
		'table'=>'facture',
		'datefieldname'=>'datef',
		'urlnew'=>DOL_URL_ROOT.'/compta/facture/card.php?action=create&projectid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'bills',
		'buttonnew'=>'CreateBill',
		'testnew'=>$user->hasRight('facture', 'creer'),
		'test'=>!empty($conf->facture->enabled) && $user->hasRight('facture', 'lire')),
	'invoice_predefined'=>array(
		'name'=>"PredefinedInvoices",
		'title'=>"ListPredefinedInvoicesAssociatedProject",
		'class'=>'FactureRec',
		'table'=>'facture_rec',
		'datefieldname'=>'datec',
		'urlnew'=>DOL_URL_ROOT.'/compta/facture/card.php?action=create&projectid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'bills',
		'buttonnew'=>'CreateBill',
		'testnew'=>$user->hasRight('facture', 'creer'),
		'test'=>!empty($conf->facture->enabled) && $user->hasRight('facture', 'lire')),
	'proposal_supplier'=>array(
		'name'=>"SuppliersProposals",
		'title'=>"ListSupplierProposalsAssociatedProject",
		'class'=>'SupplierProposal',
		'table'=>'supplier_proposal',
		'datefieldname'=>'date_valid',
		'urlnew'=>DOL_URL_ROOT.'/supplier_proposal/card.php?action=create&projectid='.$id.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id), // No socid parameter here, the socid is often the customer and we create a supplier object
		'lang'=>'supplier_proposal',
		'buttonnew'=>'AddSupplierProposal',
		'testnew'=>$user->hasRight('supplier_proposal', 'creer'),
		'test'=>!empty($conf->supplier_proposal->enabled) && $user->hasRight('supplier_proposal', 'lire')),
	'order_supplier'=>array(
		'name'=>"SuppliersOrders",
		'title'=>"ListSupplierOrdersAssociatedProject",
		'class'=>'CommandeFournisseur',
		'table'=>'commande_fournisseur',
		'datefieldname'=>'date_commande',
		'urlnew'=>DOL_URL_ROOT.'/fourn/commande/card.php?action=create&projectid='.$id.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id), // No socid parameter here, the socid is often the customer and we create a supplier object
		'lang'=>'suppliers',
		'buttonnew'=>'AddSupplierOrder',
		'testnew'=>$user->hasRight('fournisseur', 'commande', 'creer') || $user->hasRight('supplier_order', 'creer'),
		'test'=>!empty($conf->supplier_order->enabled) && $user->hasRight('fournisseur', 'commande', 'lire') || $user->hasRight('supplier_order', 'lire')),
	'invoice_supplier'=>array(
		'name'=>"BillsSuppliers",
		'title'=>"ListSupplierInvoicesAssociatedProject",
		'class'=>'FactureFournisseur',
		'margin'=>'minus',
		'table'=>'facture_fourn',
		'datefieldname'=>'datef',
		'urlnew'=>DOL_URL_ROOT.'/fourn/facture/card.php?action=create&projectid='.$id.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id), // No socid parameter here, the socid is often the customer and we create a supplier object
		'lang'=>'suppliers',
		'buttonnew'=>'AddSupplierInvoice',
		'testnew'=>$user->hasRight('fournisseur', 'facture', 'creer') || $user->hasRight('supplier_invoice', 'creer'),
		'test'=>!empty($conf->supplier_invoice->enabled) && $user->hasRight('fournisseur', 'facture', 'lire') || $user->hasRight('supplier_invoice', 'lire')),
	'contract'=>array(
		'name'=>"Contracts",
		'title'=>"ListContractAssociatedProject",
		'class'=>'Contrat',
		'table'=>'contrat',
		'datefieldname'=>'date_contrat',
		'urlnew'=>DOL_URL_ROOT.'/contrat/card.php?action=create&projectid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'contracts',
		'buttonnew'=>'AddContract',
		'testnew'=>$user->hasRight('contrat', 'creer'),
		'test'=>!empty($conf->contrat->enabled) && $user->hasRight('contrat', 'lire')),
	'intervention'=>array(
		'name'=>"Interventions",
		'title'=>"ListFichinterAssociatedProject",
		'class'=>'Fichinter',
		'table'=>'fichinter',
		'datefieldname'=>'date_valid',
		'disableamount'=>0,
		'margin'=>'minus',
		'urlnew'=>DOL_URL_ROOT.'/fichinter/card.php?action=create&origin=project&originid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'interventions',
		'buttonnew'=>'AddIntervention',
		'testnew'=>$user->hasRight('ficheinter', 'creer'),
		'test'=>!empty($conf->ficheinter->enabled) && $user->hasRight('ficheinter', 'lire')),
	'shipping'=>array(
		'name'=>"Shippings",
		'title'=>"ListShippingAssociatedProject",
		'class'=>'Expedition',
		'table'=>'expedition',
		'datefieldname'=>'date_valid',
		'urlnew'=>DOL_URL_ROOT.'/expedition/card.php?action=create&origin=project&originid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'sendings',
		'buttonnew'=>'CreateShipment',
		'testnew'=>0,
		'test'=>$conf->expedition->enabled && $user->rights->expedition->lire),
	'mrp'=>array(
		'name'=>"MO",
		'title'=>"ListMOAssociatedProject",
		'class'=>'Mo',
		'table'=>'mrp_mo',
		'datefieldname'=>'date_valid',
		'urlnew'=>DOL_URL_ROOT.'/mrp/mo_card.php?action=create&origin=project&originid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'mrp',
		'buttonnew'=>'CreateMO',
		'testnew'=>$user->hasRight('mrp', 'write'),
		'project_field'=>'fk_project',
		'test'=>!empty($conf->mrp->enabled) && $user->hasRight('mrp', 'read')),
	'trip'=>array(
		'name'=>"TripsAndExpenses",
		'title'=>"ListExpenseReportsAssociatedProject",
		'class'=>'Deplacement',
		'table'=>'deplacement',
		'datefieldname'=>'dated',
		'margin'=>'minus',
		'disableamount'=>1,
		'urlnew'=>DOL_URL_ROOT.'/deplacement/card.php?action=create&projectid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'trips',
		'buttonnew'=>'AddTrip',
		'testnew'=>$user->hasRight('deplacement', 'creer'),
		'test'=>!empty($conf->deplacement->enabled) && $user->hasRight('deplacement', 'lire')),
	'expensereport'=>array(
		'name'=>"ExpenseReports",
		'title'=>"ListExpenseReportsAssociatedProject",
		'class'=>'ExpenseReportLine',
		'table'=>'expensereport_det',
		'datefieldname'=>'date',
		'margin'=>'minus',
		'disableamount'=>0,
		'urlnew'=>DOL_URL_ROOT.'/expensereport/card.php?action=create&projectid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'trips',
		'buttonnew'=>'AddTrip',
		'testnew'=>$user->hasRight('expensereport', 'creer'),
		'test'=>!empty($conf->expensereport->enabled) && $user->hasRight('expensereport', 'lire')),
	'donation'=>array(
		'name'=>"Donation",
		'title'=>"ListDonationsAssociatedProject",
		'class'=>'Don',
		'margin'=>'add',
		'table'=>'don',
		'datefieldname'=>'datedon',
		'disableamount'=>0,
		'urlnew'=>DOL_URL_ROOT.'/don/card.php?action=create&projectid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'donations',
		'buttonnew'=>'AddDonation',
		'testnew'=>$user->hasRight('don', 'creer'),
		'test'=>!empty($conf->don->enabled) && $user->hasRight('don', 'lire')),
	'loan'=>array(
		'name'=>"Loan",
		'title'=>"ListLoanAssociatedProject",
		'class'=>'Loan',
		'margin'=>'add',
		'table'=>'loan',
		'datefieldname'=>'datestart',
		'disableamount'=>0,
		'urlnew'=>DOL_URL_ROOT.'/loan/card.php?action=create&projectid='.$id.'&socid='.$socid.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'loan',
		'buttonnew'=>'AddLoan',
		'testnew'=>$user->hasRight('loan', 'write'),
		'test'=>!empty($conf->loan->enabled) && $user->hasRight('loan', 'read')),
	'chargesociales'=>array(
		'name'=>"SocialContribution",
		'title'=>"ListSocialContributionAssociatedProject",
		'class'=>'ChargeSociales',
		'margin'=>'minus',
		'table'=>'chargesociales',
		'datefieldname'=>'date_ech',
		'disableamount'=>0,
		'urlnew'=>DOL_URL_ROOT.'/compta/sociales/card.php?action=create&projectid='.$id.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'compta',
		'buttonnew'=>'AddSocialContribution',
		'testnew'=>$user->hasRight('tax', 'charges', 'lire'),
		'test'=>!empty($conf->tax->enabled) && $user->hasRight('tax', 'charges', 'lire')),
	'project_task'=>array(
		'name'=>"TaskTimeSpent",
		'title'=>"ListTaskTimeUserProject",
		'class'=>'Task',
		'margin'=>'minus',
		'table'=>'projet_task',
		'datefieldname'=>'task_date',
		'disableamount'=>0,
		'urlnew'=>DOL_URL_ROOT.'/projet/tasks/time.php?withproject=1&action=createtime&projectid='.$id.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'buttonnew'=>'AddTimeSpent',
		'testnew'=>$user->hasRight('project', 'creer'),
		'test'=>!empty($conf->project->enabled) && $user->hasRight('projet', 'lire') && !empty($conf->global->PROJECT_HIDE_TASKS)),
	'stock_mouvement'=>array(
		'name'=>"MouvementStockAssociated",
		'title'=>"ListMouvementStockProject",
		'class'=>'MouvementStock',
		'margin'=>'minus',
		'table'=>'stock_mouvement',
		'datefieldname'=>'datem',
		'disableamount'=>0,
		'test'=>!empty($conf->stock->enabled) && $user->hasRight('stock', 'mouvement', 'lire') && !empty($conf->global->STOCK_MOVEMENT_INTO_PROJECT_OVERVIEW)),
	'salaries'=>array(
		'name'=>"Salaries",
		'title'=>"ListSalariesAssociatedProject",
		'class'=>'Salary',
		'table'=>'salary',
		'datefieldname'=>'datesp',
		'margin'=>'minus',
		'disableamount'=>0,
		'urlnew'=>DOL_URL_ROOT.'/salaries/card.php?action=create&projectid='.$id.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'salaries',
		'buttonnew'=>'AddSalary',
		'testnew'=>$user->hasRight('salaries', 'write'),
		'test'=>!empty($conf->salaries->enabled) && $user->hasRight('salaries', 'read')),
	'variouspayment'=>array(
		'name'=>"VariousPayments",
		'title'=>"ListVariousPaymentsAssociatedProject",
		'class'=>'PaymentVarious',
		'table'=>'payment_various',
		'datefieldname'=>'datev',
		'margin'=>'minus',
		'disableamount'=>0,
		'urlnew'=>DOL_URL_ROOT.'/compta/bank/various_payment/card.php?action=create&projectid='.$id.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$id),
		'lang'=>'banks',
		'buttonnew'=>'AddVariousPayment',
		'testnew'=>$user->hasRight('banque', 'modifier'),
		'test'=>!empty($conf->banque->enabled) && $user->hasRight('banque', 'lire') && !empty($conf->global->BANK_USE_OLD_VARIOUS_PAYMENT)),
	);

	// Change rules for profit/benefit calculation
	if (!empty($conf->global->PROJECT_ELEMENTS_FOR_PLUS_MARGIN)) {
		foreach ($listofreferent as $key => $element) {
			if ($listofreferent[$key]['margin'] == 'add') {
				unset($listofreferent[$key]['margin']);
			}
		}
		$newelementforplusmargin = explode(',', $conf->global->PROJECT_ELEMENTS_FOR_PLUS_MARGIN);
		foreach ($newelementforplusmargin as $value) {
			$listofreferent[trim($value)]['margin'] = 'add';
		}
	}
	if (!empty($conf->global->PROJECT_ELEMENTS_FOR_MINUS_MARGIN)) {
		foreach ($listofreferent as $key => $element) {
			if ($listofreferent[$key]['margin'] == 'minus') {
				unset($listofreferent[$key]['margin']);
			}
		}
		$newelementforminusmargin = explode(',', $conf->global->PROJECT_ELEMENTS_FOR_MINUS_MARGIN);
		foreach ($newelementforminusmargin as $value) {
			$listofreferent[trim($value)]['margin'] = 'minus';
		}
	}



	$parameters = array('listofreferent'=>$listofreferent);
	$resHook = $hookmanager->executeHooks('completeListOfReferent', $parameters, $object, $action);

	if (!empty($hookmanager->resArray)) {
		$listofreferent = array_merge($listofreferent, $hookmanager->resArray);
	}

	$elementuser = new User($db);

	// Show balance for whole project

	$langs->loadLangs(array("suppliers", "bills", "orders", "proposals", "margins"));

	if (isModEnabled('stock')) {
		$langs->load('stocks');
	}
	
	$tooltiponprofit = $langs->trans("RprjT_ProfitIsCalculatedWith")."<br>\n";
	$tooltiponprofitplus = $tooltiponprofitminus = '';
	foreach ($listofreferent as $key => $value) {
		$name = $langs->trans($value['name']);
		$qualified = $value['test'];
		$margin = $value['margin'];
		if ($qualified && isset($margin)) {		// If this element must be included into profit calculation ($margin is 'minus' or 'add')
			if ($margin == 'add') {
				$tooltiponprofitplus .= ' &gt; '.$name." (+)<br>\n";
			}
			if ($margin == 'minus') {
				$tooltiponprofitminus .= ' &gt; '.$name." (-)<br>\n";
			}
		}
	}
	$tooltiponprofit .= $tooltiponprofitplus;
	$tooltiponprofit .= $tooltiponprofitminus;

	$total_revenue_ht = 0;
	$balance_ht = 0;
	$balance_ttc = 0;
	$sales = 0;

	foreach ($listofreferent as $key => $value) {
		$parameters = array(
			'total_revenue_ht' =>& $total_revenue_ht,
			'balance_ht' =>& $balance_ht,
			'balance_ttc' =>& $balance_ttc,
			'key' => $key,
			'value' =>& $value,
			'dates' => $dates,
			'datee' => $datee
		);

		$name = $langs->trans($value['name']);
		$title = $value['title'];
		$classname = $value['class'];
		$tablename = $value['table'];
		$datefieldname = $value['datefieldname'];
		$qualified = $value['test'];
		$margin = $value['margin'];
		$project_field = $value['project_field'];
		if ($qualified && isset($margin)) {		// If this element must be included into profit calculation ($margin is 'minus' or 'add')
			$element = new $classname($db);

			$elementarray = $object->get_element_list($key, $tablename, $datefieldname, $dates, $datee, !empty($project_field) ? $project_field : 'fk_projet');

			if (is_array($elementarray) && count($elementarray) > 0) {
				$total_ht = 0;
				$total_ttc = 0;

				$num = count($elementarray);
				for ($i = 0; $i < $num; $i++) {
					$tmp = explode('_', $elementarray[$i]);
					$idofelement = $tmp[0];
					$idofelementuser = $tmp[1];

					$element->fetch($idofelement);
					if ($idofelementuser) {
						$elementuser->fetch($idofelementuser);
					}

					// Define if record must be used for total or not
					$qualifiedfortotal = true;
					if ($key == 'invoice') {
						if (!empty($element->close_code) && $element->close_code == 'replaced') {
							$qualifiedfortotal = false; // Replacement invoice, do not include into total
						}
						if (!empty($conf->global->FACTURE_DEPOSITS_ARE_JUST_PAYMENTS) && $element->type == Facture::TYPE_DEPOSIT) {
							$qualifiedfortotal = false; // If hidden option to use deposits as payment (deprecated, not recommended to use this), deposits are not included
						}
					}
					if ($key == 'propal') {
						if ($element->status != Propal::STATUS_SIGNED && $element->status != Propal::STATUS_BILLED) {
							$qualifiedfortotal = false; // Only signed proposal must not be included in total
						}
					}

					if ($tablename != 'expensereport_det' && method_exists($element, 'fetch_thirdparty')) {
						$element->fetch_thirdparty();
					}

					// Define $total_ht_by_line
					if ($tablename == 'don' || $tablename == 'chargesociales' || $tablename == 'payment_various' || $tablename == 'salary') {
						$total_ht_by_line = $element->amount;
					} elseif ($tablename == 'fichinter') {
						$total_ht_by_line = $element->getAmount();
					} elseif ($tablename == 'stock_mouvement') {
						$total_ht_by_line = $element->price * abs($element->qty);
					} elseif ($tablename == 'projet_task') {
						if ($idofelementuser) {
							$tmp = $element->getSumOfAmount($elementuser, $dates, $datee);
							$total_ht_by_line = price2num($tmp['amount'], 'MT');
						} else {
							$tmp = $element->getSumOfAmount('', $dates, $datee);
							$total_ht_by_line = price2num($tmp['amount'], 'MT');
						}
					} elseif ($key == 'loan') {
						if ((empty($dates) && empty($datee)) || (intval($dates) <= $element->datestart && intval($datee) >= $element->dateend)) {
							// Get total loan
							$total_ht_by_line = -$element->capital;
						} else {
							// Get loan schedule according to date filter
							$total_ht_by_line = 0;
							$loanScheduleStatic = new LoanSchedule($element->db);
							$loanScheduleStatic->fetchAll($element->id);
							if (!empty($loanScheduleStatic->lines)) {
								foreach ($loanScheduleStatic->lines as $loanSchedule) {
									/**
									 * @var $loanSchedule LoanSchedule
									 */
									if (($loanSchedule->datep >= $dates && $loanSchedule->datep <= $datee) // dates filter is defined
										|| !empty($dates) && empty($datee) && $loanSchedule->datep >= $dates && $loanSchedule->datep <= dol_now()
										|| empty($dates) && !empty($datee) && $loanSchedule->datep <= $datee
									) {
										$total_ht_by_line -= $loanSchedule->amount_capital;
									}
								}
							}
						}
					} else {
						$total_ht_by_line = $element->total_ht;
					}

					// Define $total_ttc_by_line
					if ($tablename == 'don' || $tablename == 'chargesociales' || $tablename == 'payment_various' || $tablename == 'salary') {
						$total_ttc_by_line = $element->amount;
					} elseif ($tablename == 'fichinter') {
						$total_ttc_by_line = $element->getAmount();
					} elseif ($tablename == 'stock_mouvement') {
						$total_ttc_by_line = $element->price * abs($element->qty);
					} elseif ($tablename == 'projet_task') {
						$defaultvat = get_default_tva($mysoc, $mysoc);
						$total_ttc_by_line = price2num($total_ht_by_line * (1 + ($defaultvat / 100)), 'MT');
					} elseif ($key == 'loan') {
							$total_ttc_by_line = $total_ht_by_line; // For loan there is actually no taxe managed in Dolibarr
					} else {
						$total_ttc_by_line = $element->total_ttc;
					}

					// Change sign of $total_ht_by_line and $total_ttc_by_line for some cases
					if ($tablename == 'payment_various') {
						if ($element->sens == 1) {
							$total_ht_by_line = -$total_ht_by_line;
							$total_ttc_by_line = -$total_ttc_by_line;
						}
					}

					// Add total if we have to
					if ($qualifiedfortotal) {
						$total_ht = $total_ht + $total_ht_by_line;
						$total_ttc = $total_ttc + $total_ttc_by_line;
					}

					// Sales cumulation
					if ($parameters['value']['name'] == "CustomersInvoices") {
						if (!empty($conf->global->FACTURE_TVAOPTION)) {
							$sales += $total_ht_by_line;
						} else {
							$sales += $total_ttc_by_line;
						}
					}

				}

				// Each element with at least one line is output
				$qualifiedforfinalprofit = true;
				if ($key == 'intervention' && empty($conf->global->PROJECT_INCLUDE_INTERVENTION_AMOUNT_IN_PROFIT)) {
					$qualifiedforfinalprofit = false;
				}
				//var_dump($key.' '.$qualifiedforfinalprofit);

				// Calculate margin
				if ($qualifiedforfinalprofit) {
					if ($margin == 'add') {
						$total_revenue_ht += $total_ht;
					}

					if ($margin != "add") {	// Revert sign
						$total_ht = -$total_ht;
						$total_ttc = -$total_ttc;
					}

					$balance_ht += $total_ht;
					$balance_ttc += $total_ttc;
				}

			}
		}
	}

	// cumulations
	if (!empty($conf->global->FACTURE_TVAOPTION)) {
		$projectsData[$projectRef]['balance'] = $balance_ht;
	} else {
		$projectsData[$projectRef]['balance'] = $balance_ttc;
	}
	// global sales
	$categoriesData[$categLabel]['sales'] += $sales;
	$projectsData[$projectRef]['sales'] = $sales;
	$globalSales += $sales;
	// global balance
	$globalBalanceCumul += $projectsData[$projectRef]['balance'];

}

// -- Processing step --
// xdebug_break();

// loop on projects
ksort($projectsData);
foreach ($projectsData as $projectRef => $projectData) {

	// loop on categories from current project

	if (empty($conf->global->PROJECTANALYTIC_CA)) {
		// profits divided equally among the tags
		
		$nbCateg = count($projectData['categories']);
		$repPerc = 100 / $nbCateg;
		$part = round( $projectData['balance'] * $repPerc / 100, 2);
		
		// loop on current project categories
		foreach ($projectData['categories'] as $category => $projectCategories) {
			// project category cumulation
			$projectsData[$projectRef]['categories'][$category]['repPerc'] = $repPerc;
			$projectsData[$projectRef]['categories'][$category]['balance'] = $part;
			// category cumulation
			$categoriesData[$category]['balanceCumul'] += $projectData['balance'];
			$categoriesData[$category]['keptBalanceCumul'] += $part;
		}

	} else {
		// profits distribution based on the project's total sales
		
		$nbCateg = count($projectData['categories']);
		
		// categories sales cumulation relative to current project
		$projectSalesCumul = 0;
		foreach ($projectData['categories'] as $category => $projectCategories) {
			$projectSalesCumul += $categoriesData[$category]['sales'];
		}

		// loop on current project categories
		foreach ($projectData['categories'] as $category => $projectCategories) {
			// sales part of current category relative to categories concerned by current project
			if ($categoriesData[$category]['sales'] == 0) {
				$repPerc = 0;
			} else {
				$repPerc = 100 / $projectSalesCumul * $categoriesData[$category]['sales'];
			}
			if ($nbCateg <= 1) {
				$repPerc = 100;
			}
			// project category cumulation
			$projectsData[$projectRef]['categories'][$category]['repPerc'] = $repPerc;
			$part = round( $projectData['balance'] * $repPerc / 100, 2);
			$projectsData[$projectRef]['categories'][$category]['balance'] = $part;
			// category cumulation
			$categoriesData[$category]['balanceCumul'] += $projectData['balance'];
			$categoriesData[$category]['keptBalanceCumul'] += $part;
		}

	}
		
	// rest management
	$roundedBalance = 0;
	$max = 0;
	$maxCateg = '';
	foreach ($projectData['categories'] as $category => $projectCategories) {
		$currentBalance = $projectsData[$projectRef]['categories'][$category]['balance'];
		$w = intval($currentBalance);
		if ($w < 0) {
			$w = $w * -1;
		}
		if ($w > $max) {
			$max = $currentBalance;
			$maxCateg = $category;
		}
		$roundedBalance += $currentBalance;
	}
	$rest = $projectData['balance'] - $roundedBalance;
	$projectsData[$projectRef]['categories'][$maxCateg]['balance'] += $rest;

}
$nbProjectsWithoutCateg = count($categoriesData['']['projects']);
ksort($categoriesData);

// -- Templates definitions step --
// xdebug_break();

$tplSource = '<!-- group main -->
<div>
    <div class="fichecenter">
        <div class="underbanner clearboth"></div>
    </div>
	<!-- empty form title -->
    <table class="centpercent notopnoleftnoright table-fiche-title"></table>
    <!-- --><!-- group category -->
	<table class="centpercent notopnoleftnoright table-fiche-title">
    <tr>
		$renderCategoryLink
    </tr>
	</table>
	<table class="noborder centpercent">
        <tbody>
            <tr class="liste_titre">
                <th class="wrapcolumntitle liste_titre_sel" title="Réf.">
                    <a class="reposition" href="/projet/list.php?sortfield=p.ref&amp;sortorder=desc&amp;begin=&amp;contextpage=projectlist">$RprjT_RefPrj_Lib</a>
                </th>
                <th class="wrapcolumntitle liste_titre" title="$RprjT_Tiers_Lib">
                    <a class="reposition" href="/projet/list.php?sortfield=s.nom&amp;sortorder=asc&amp;begin=&amp;contextpage=projectlist">$RprjT_ProjTiers_Lib</a>
                </th>
                <th class="wrapcolumntitle center liste_titre">$RprjT_BalanceVAT_Lib</th>
                <th class="wrapcolumntitle center liste_titre">$RprjT_Repartition_Lib</th>
                <th class="wrapcolumntitle center liste_titre">$RprjT_HeldVAT_Lib</th>
            </tr>
			<!-- --><!-- group projet -->
            <tr class="oddeven">
                <td class="nowraponall">$projectNomUrl</td>
                <td class="left">$thirdparty</td>
                <td class="right">$balance</td>
                <td class="right">$repPerc %</td>
                <td class="right">$keptBalance</td>
            </tr>
            <!--end group projet --><!-- -->
			$renderCategoryCumulContent
        </tbody>
    </table>
    <!--end group category --><!-- -->
</div>
<!--end group main -->';
// split the main template up
$wParts = explode('<!-- -->',$tplSource);
// build up the dependancies
$templateMain = array_shift($wParts) . '$renderCategoriesContent' . array_pop($wParts);
$templateCategoryMain = array_shift($wParts) . '$renderCategoryContent' . array_pop($wParts);
$templateProject = array_shift($wParts) . array_pop($wParts);

//
$templateHelpTip = '<!-- help tip -->
	<td class="right">$globalBalanceCumul</td>
	<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px;" title="$textHelpTip">
    	<span class="fas fa-info-circle  em088 opacityhigh" style=" vertical-align: middle; cursor: help"></span>
	</span>';
$templateWithoutCategory = '<!-- projects without category -->
	<span class="vmenu">$RprjT_PrjsWithoutCateg</span>
	<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px;" title="$RprjT_HelpPrjWithoutCateg.<br>$tooltiponprofit">
    	<span class="fas fa-info-circle  em088 opacityhigh" style=" vertical-align: middle; cursor: help"></span>
	</span>
	<span class="opacitymedium colorblack paddingleft">($projectsCount $RprjT_Prjs_Lib)</span>';
$templateWithCategory = '<!-- projects with category -->
	<td>
		<span class="noborderoncategories" style="background: #$categBackgroundColor;">$categNomUrl</span>
		<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px;" title="$tooltiponprofit">
			<span class="fas fa-info-circle  em088 opacityhigh" style=" vertical-align: middle; cursor: help"></span>
		</span>
		<span class="opacitymedium colorblack paddingleft">($projectsCount $RprjT_Prjs_Lib)</span>
	</td>
	<td class="right" width="20px;">
		<a href="/categories/viewcat.php?id=$tagId&amp;type=project&amp;backtolist=%2Fcategories%2Findex.php%3Ftype%3Dproject">
			<span class="fas fa-eye valignmiddle" style="" title="$RprjT_View_Lib"></span>
		</a>
	</td>';
$templateCategoryCumul = '<!-- group category cumulation -->
	<tr class="liste_total">
		<td class="right" colspan="2">$RprjT_Cumulation_Lib</td>
		<td class="right">$balanceCumul</td>
		<td></td>
		<td class="right">$keptBalanceCumul</td>
	</tr>
	<!-- end group category cumulation -->';

// build up the translated texts array
$tplVars = [];
foreach (['RprjT_HelpMain',
	'RprjT_HelpPrjWithoutCateg','RprjT_RefPrj_Lib', 'RprjT_LibPrj_Lib','RprjT_HelpLibPrj_Lib',
	'RprjT_Tiers_Lib','RprjT_ProjTiers_Lib',
	'RprjT_Repartition_Lib',
	'RprjT_Cumulation_Lib','RprjT_View_Lib'] as $txt) {
	$tplVars['$'.$txt] = $langs->trans($txt);
	}
if (!empty($conf->global->FACTURE_TVAOPTION)) {
	$tplVars['$RprjT_BalanceVAT_Lib'] = $langs->trans('RprjT_BalanceVATfree_Lib');
	$tplVars['$RprjT_HeldVAT_Lib'] = $langs->trans('RprjT_HeldVATfree_Lib');
	
} else {
	$tplVars['$RprjT_BalanceVAT_Lib'] = $langs->trans('RprjT_BalanceVATincluded_Lib');
	$tplVars['$RprjT_HeldVAT_Lib'] = $langs->trans('RprjT_HeldVATincluded_Lib');
}
if ($nbProjectsWithoutCateg <= 1) {
	$tplVars['$RprjT_PrjsWithoutCateg'] = $langs->trans('RprjT_PrjWithoutCateg');
} else {
	$tplVars['$RprjT_PrjsWithoutCateg'] = $langs->trans('RprjT_PrjsWithoutCateg');
}
// $tooltiponprofit comes from anterior projects list loop
$tplVars['$tooltiponprofit'] = $tooltiponprofit;
//
$textHelpTip = $tplVars['$RprjT_HelpMain'] . '<br>' . $tooltiponprofit;

$title1 = $langs->trans("RprjT_Title");
llxHeader("", $title1);

$title2 = strtr(' '.$langs->trans("RprjT_Main"), [
	'$globalBalanceCumul'=> number_format($globalBalanceCumul,2,',',' '),
	'$globalSales'=> number_format($globalSales,2,',',' '),
]);

// print title specific icon, title, and help text
print load_fiche_titre( $form->textwithpicto( $title1 . $title2, $langs->trans("RprjT_HelpMain")), '', 'projectanalytic.png@projectanalytic' );


// -- Rendering step --
//xdebug_break();

$renderCategoriesContent = '';
// loop on categories
foreach($categoriesData as $categKey => $categData) {

	$renderCategoryContent = '';
	$nbProjects = count($categData["projects"]);
	$tplVars['$projectsCount'] = $nbProjects;
	if ($nbProjects <= 1) {
		$tplVars['$RprjT_Prjs_Lib'] = $langs->trans('RprjT_Prj_Lib');
	} else {
		$tplVars['$RprjT_Prjs_Lib'] = $langs->trans('RprjT_Prjs_Lib');
	}

	// loop on projects of this category
	foreach ($categData["projects"] as $projectInd => $projectRef) {
		$projectData = $projectsData[$projectRef];
		// get current project data on template vars data
		$tplVars['$ref'] = $projectRef;
		$tplVars['$title'] = htmlspecialchars($projectData['title'],ENT_QUOTES);
		$tplVars['$balance'] = number_format($projectData['balance'],2,',',' ');
		$tplVars['$repPerc'] = number_format($projectData['categories'][$categKey]['repPerc'],2,',',' ');
		$tplVars['$keptBalance'] = number_format($projectData['categories'][$categKey]['balance'],2,',',' ');
		$tplVars['$projectNomUrl'] = $projectData['projectNomUrl'];
		$tplVars['$thirdparty'] = $projectData['thirdparty'];
		// render current project for current category
		$renderCategoryContent = $renderCategoryContent . strtr($templateProject, $tplVars);
	}

	// projects without category ?
	if ($categKey == "") {
		$renderCategoryLink = strtr($templateWithoutCategory, $tplVars);
	} else {
		$tplVars['$tagLabel'] = $categData['categLabel'];
		$tplVars['$tagId'] = $categData['tagId'];
		$tplVars['$categBackgroundColor'] = $categData['categBackgroundColor'];
		$tplVars['$categNomUrl'] = $categData['categNomUrl'];
		$renderCategoryLink = strtr($templateWithCategory, $tplVars);
	}
	$tplVars['$renderCategoryLink'] = $renderCategoryLink;

	// get vars for current category interpolation
	foreach (array("balanceCumul","keptBalanceCumul") as $wkey) {
		$tplVars['$' . $wkey] = number_format($categData[$wkey],2,',',' ');
	}
	// render current category
	$tplVars['$renderCategoryContent'] = $renderCategoryContent;
	$tplVars['$renderCategoryCumulContent'] = strtr($templateCategoryCumul, $tplVars);
	$renderCategoryContent = strtr($templateCategoryMain, $tplVars);

	// keep on rendering all categories
	$renderCategoriesContent = $renderCategoriesContent . $renderCategoryContent;
}

// global rendering
$tplVars['$renderCategoriesContent'] = $renderCategoriesContent;
print strtr($templateMain, $tplVars);

// End of page
llxFooter();
$db->close();

/**
 * Return if we should do a group by customer with sub-total
 *
 * @param 	string	$tablename		Name of table
 * @return	boolean					True to tell to make a group by sub-total
 */
function canApplySubtotalOn($tablename)
{
	global $conf;

	if (empty($conf->global->PROJECT_ADD_SUBTOTAL_LINES)) {
		return false;
	}
	return in_array($tablename, array('facture_fourn', 'commande_fournisseur'));
}

/**
 * sortElementsByClientName
 *
 * @param 	array		$elementarray	Element array
 * @return	array						Element array sorted
 */
function sortElementsByClientName($elementarray)
{
	global $db, $classname;

	$element = new $classname($db);

	$clientname = array();
	foreach ($elementarray as $key => $id) {	// id = id of object
		if (empty($clientname[$id])) {
			$element->fetch($id);
			$element->fetch_thirdparty();

			$clientname[$id] = $element->thirdparty->name;
		}
	}

	//var_dump($clientname);
	asort($clientname); // sort on name

	$elementarray = array();
	foreach ($clientname as $id => $name) {
		$elementarray[] = $id;
	}

	return $elementarray;
}
