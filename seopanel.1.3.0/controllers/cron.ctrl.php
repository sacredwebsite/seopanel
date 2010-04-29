<?php


/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seofreetools.net)  	   *
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

# class defines all cron controller functions
class CronController extends Controller {
	var $cronList;			// the array includes all tools avialable for cron
	var $repTools;			// the array includes all tools avialable for report generation
	var $debug = true;		// to show debug message or not
	var $layout = 'ajax';   // ajax layout or not 
	
	
	# function to load all tools required for report generation 
	function loadReportGenerationTools($includeList=array()){
		$sql = "select * from seotools where status=1 and reportgen=1";
		if(count($includeList) > 0) $sql .= " and id in (".implode(',', $includeList).")";
		$this->repTools = $this->db->select($sql);
	}
	
	# function to load all tools required for cron job
	function loadCronJobTools($includeList=array()){
		$sql = "select * from seotools where status=1 and cron=1";
		if(count($includeList) > 0) $sql .= " and id in (".implode(',', $includeList).")";
		$this->cronList = $this->db->select($sql);
	}
	
	
	# function to show report generation manager
	function showReportGenerationManager(){
		$this->set('sectionHead', 'Report Generation Manager');
		$userId = isLoggedIn();
		
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$this->set('websiteNull', true);
		
		$this->loadReportGenerationTools();
		$this->set('repTools', $this->repTools);
		
		$this->render('report/reportgenerationmanager');
	}	
	
	# common report generation function
	function executeReportGenerationScript($info='') {
		
		if(count($info['repTools']) <= 0){
			showErrorMsg('Please select atleast one Seo Tools!');
		}
		
		$websiteCtrler = New WebsiteController();
		if(!empty($info['website_id'])){
			$allWebsiteList[] = $websiteCtrler->__getWebsiteInfo($info['website_id']);
		}else{
			$userCtrler = New UserController();
			$userList = $userCtrler->__getAllUsers();		
			$allWebsiteList = array();
			foreach($userList as $userInfo){
				
				$websiteList = $websiteCtrler->__getAllWebsites($userInfo['id']);			
				foreach($websiteList as $websiteInfo){
					$allWebsiteList[] = $websiteInfo;				
				}
			}	
		}

		if(count($allWebsiteList) <= 0){
			showErrorMsg('No websites found!');
		}
		
		$this->set('allWebsiteList', $allWebsiteList);
		$this->set('repTools', implode(':', $info['repTools']));
		$this->render('report/reportgenerator');
	}
	
	# common cron execute function
	function executeCron($includeList=array()) {
		
		$this->loadCronJobTools($includeList);
		
		$userCtrler = New UserController();
		$userList = $userCtrler->__getAllUsers();
		$websiteCtrler = New WebsiteController();
		foreach($userList as $userInfo){
			
			$websiteList = $websiteCtrler->__getAllWebsites($userInfo['id']);
			foreach($websiteList as $websiteInfo){
				
				$this->websiteInfo = $websiteInfo;
				$this->routeCronJob($websiteInfo['id'], '', true);
			}
		}		
	}
	
	# function to route the cronjobs to different methods
	function routeCronJob($websiteId, $repTools='', $cron=false){		
		
		if(empty($this->websiteInfo)){
			$websiteCtrler = New WebsiteController();
			$this->websiteInfo = $websiteCtrler->__getWebsiteInfo($websiteId);
		}
		
		if($cron){			
			if(empty($this->cronList)){
				$this->loadCronJobTools();
			}
			$seoTools = $this->cronList;	
		}else{			
			$this->loadReportGenerationTools(explode(':', $repTools));
			$seoTools = $this->repTools;
		}
		
		foreach ($seoTools as $cronInfo) {
			switch($cronInfo['url_section']){
				
				case "keyword-position-checker":
					$this->keywordPositionCheckerCron($websiteId);
					break;
					
				case "rank-checker":
					$this->rankCheckerCron($websiteId);
					break;
					
				case "backlink-checker":
					$this->backlinkCheckerCron($websiteId);
					break;
					
				case "saturation-checker":
					$this->saturationCheckerCron($websiteId);
					break;
			}
		}
	}
	
	# func to generate search engine saturation reports from cron
	function saturationCheckerCron($websiteId){
		
		include_once(SP_CTRLPATH."/saturationchecker.ctrl.php");
		$this->debugMsg("Starting Search engine saturation Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		$saturationCtrler = New SaturationCheckerController();
		$websiteInfo = $this->websiteInfo;
		$saturationCtrler->url = $websiteUrl = addHttpToUrl($websiteInfo['url']);			
		foreach ($saturationCtrler->colList as $col => $dbCol) {
			$websiteInfo[$col] = $saturationCtrler->__getSaturationRank($col);
		}
			
		$saturationCtrler->saveRankResults($websiteInfo, true);			
		echo "Saved Search Engine Saturation results of <b>$websiteUrl</b>.....</br>\n";
		
	}
	
	
	# func to generate backlink reports from cron
	function backlinkCheckerCron($websiteId){
		
		include_once(SP_CTRLPATH."/backlink.ctrl.php");
		$this->debugMsg("Starting Backlik Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		$backlinkCtrler = New BacklinkController();
		$websiteInfo = $this->websiteInfo;
		$backlinkCtrler->url = $websiteUrl = addHttpToUrl($websiteInfo['url']);			
		foreach ($backlinkCtrler->colList as $col => $dbCol) {
			$websiteInfo[$col] = $backlinkCtrler->__getBacklinks($col);
		}
		
		$backlinkCtrler->saveRankResults($websiteInfo, true);			
		echo "Saved backlink results of <b>$websiteUrl</b>.....</br>\n";
		
	}
	
	
	# func to generate rank reports from cron
	function rankCheckerCron($websiteId){
		
		include_once(SP_CTRLPATH."/rank.ctrl.php");
		$this->debugMsg("Starting Rank Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		$rankCtrler = New RankController();
		$websiteInfo = $this->websiteInfo;
		$websiteUrl = addHttpToUrl($websiteInfo['url']);
		$websiteInfo['googlePagerank'] = $rankCtrler->__getGooglePageRank($websiteUrl);
		$websiteInfo['alexaRank'] = $rankCtrler->__getAlexaRank($websiteUrl);
			
		$rankCtrler->saveRankResults($websiteInfo, true);			
		$this->debugMsg("Saved rank results of <b>$websiteUrl</b>.....<br>\n");
		
	}
	
	# func to find the keyword position checker
	function keywordPositionCheckerCron($websiteId){
		
		include_once(SP_CTRLPATH."/searchengine.ctrl.php");
		include_once(SP_CTRLPATH."/report.ctrl.php");
		
		$reportController = New ReportController();
		
		$seController = New SearchEngineController();
		$reportController->seList = $seController->__getAllCrawlFormatedSearchEngines();
		
		$sql = "select k.*,w.url from keywords k,websites w where k.website_id=w.id and w.id=$websiteId and k.status=1";		
		$sql .= " order by k.name";
		$keywordList = $reportController->db->select($sql);		
		
		$this->debugMsg("Starting keyword position checker cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		# loop through each keyword			
		foreach ( $keywordList as $keywordInfo ) {
			$reportController->seFound = 0;
			$crawlResult = $reportController->crawlKeyword($keywordInfo);
			foreach($crawlResult as $sengineId => $matchList){
				if($matchList['status']){
					foreach($matchList['matched'] as $i => $matchInfo){
						$remove = ($i == 0) ? true : false;						
						$matchInfo['se_id'] = $sengineId;						
						$matchInfo['keyword_id'] = $keywordInfo['id'];
						$reportController->saveMatchedKeywordInfo($matchInfo, $remove);
					}
					$this->debugMsg("Successfully crawled keyword <b>{$keywordInfo['name']}</b> results from ".$reportController->seList[$sengineId]['domain'].".....<br>\n");
				}else{
					$this->debugMsg("Crawling keyword </b>{$keywordInfo['name']}</b> results from ".$reportController->seList[$sengineId]['domain']." failed......<br>\n");
				}
			}
			if(empty($reportController->seFound)){
				$this->debugMsg("Keyword <b>{$keywordInfo['name']}</b> not assigned to required search engines........\n");
			}
			sleep(SP_CRAWL_DELAY);
		}
	}
	
	# func to show debug messages
	function debugMsg($msg=''){
		
		if($this->debug == true) print $msg;
	}
}
?>