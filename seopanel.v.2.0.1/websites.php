<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.in)  	   *
 *   sendtogeo@gmail.com   												   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/

include_once("includes/sp-load.php");
checkLoggedIn();
include_once(SP_CTRLPATH."/website.ctrl.php");
include_once(SP_CTRLPATH."/keyword.ctrl.php");
$controller = New WebsiteController();
$controller->view->menu = 'seotools';
$controller->layout = 'ajax';
$controller->set('sectionHead', 'Websites');

if($_SERVER['REQUEST_METHOD'] == 'POST'){

	switch($_POST['sec']){
		case "create":
			$controller->createWebsite($_POST);
			break;
			
		case "update":
			$controller->updateWebsite($_POST);
			break;
	}

}else{
	switch($_GET['sec']){
		
		case "Activate":
			$controller->__changeStatus($_GET['websiteId'], 1);			
			$controller->listWebsites($_GET);
			break;
		
		case "Inactivate":
			$controller->__changeStatus($_GET['websiteId'], 0);
			$controller->listWebsites($_GET);
			break;
		
		case "delete":
			$controller->__deleteWebsite($_GET['websiteId']);
			$controller->listWebsites($_GET);
			break;
		
		case "edit":
			$controller->editWebsite($_GET['websiteId']);
			break;		
		
		case "new":
			$controller->newWebsite($_GET);
			break;
		
		case "crawlmeta":
			$controller->crawlMetaData(urldecode($_GET['url']));
			break;			
			
		default:
			$controller->listWebsites($_GET);
			break;
	}
}

?>