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

# class defines all report controller functions
class ReportController extends Controller {
	var $seLIst;
	var $showAll = false;

	# func to get keyword report summary
	function __getKeywordSearchReport($keywordId, $limit=1){
		$positionInfo = array();
		
		if(empty($this->seLIst)){
			$seController = New SearchEngineController();
			$this->seLIst = $seController->__getAllSearchEngines();
		}
		
		foreach($this->seLIst as $seInfo){
			$sql = "select min(rank) as rank 
					from searchresults 
					where keyword_id=$keywordId and searchengine_id=".$seInfo['id']."
					group by time 
					order by time DESC
					limit 0, ".($limit+1);
			$reportList = $this->db->select($sql);
			$reportList = array_reverse($reportList);
			
			$prevRank = 0;
			$i = 0;
			foreach ($reportList as $key => $repInfo) {
				$rankDiff = '';
				if ($i > 0) {
					$rankDiff = $prevRank - $repInfo['rank'];
					if ($rankDiff > 0) {
						$rankDiff = "<font class='green'>($rankDiff)</font>";
					}elseif ($rankDiff < 0) {
						$rankDiff = "<font class='red'>($rankDiff)</font>";
					}
				}
				$positionInfo[$seInfo['id']]['rank_diff'] = empty ($rankDiff) ? '' : $rankDiff;
				$positionInfo[$seInfo['id']]['rank'] = $repInfo['rank'];
				$prevRank = $repInfo['rank'];
				$i++;
			}			
		}
				
		return $positionInfo;
	}
	

	# func to show keyword report summary
	function showKeywordReportSummary($searchInfo = '') {
		
		$userId = isLoggedIn();
		$exportVersion = false;
		switch($searchInfo['doc_type']){
						
			case "export":
				$exportVersion = true;
				$exportContent = "";
				break;
			
			case "print":
				$this->set('printVersion', true);
				break;
		}
		
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsitesWithActiveKeywords($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = isset($searchInfo['website_id']) ? $searchInfo['website_id'] : $websiteList[0]['id'];
		$this->set('websiteId', $websiteId);
		
		$seController = New SearchEngineController();
		$this->seLIst = $seController->__getAllSearchEngines();
		$this->set('seList', $this->seLIst);
		
		$keywordController = New KeywordController();
		$list = $keywordController->__getAllKeywords($userId, $websiteId, true);
		foreach($list as $keywordInfo){
			$positionInfo = $this->__getKeywordSearchReport($keywordInfo['id']);
			$keywordInfo['position_info'] = $positionInfo;
			$keywordList[] = $keywordInfo;
		}		

		if ($exportVersion) {
			$spText = $_SESSION['text'];
			$exportContent .= createExportContent( array('', $this->spTextTools['Keyword Position Summary'], ''));
			$exportContent .= createExportContent( array());
			$headList = array($spText['common']['Website'], $spText['common']['Keyword']);
			foreach ($this->seLIst as $seInfo) $headList[] = $seInfo['domain'];
			$exportContent .= createExportContent( $headList);
			foreach ($keywordList as $listInfo) {
				$positionInfo = $listInfo['position_info'];
				$valueList = array($listInfo['weburl'], $listInfo['name']);
				foreach ($this->seLIst as $index => $seInfo){
					$rank = empty($positionInfo[$seInfo['id']]['rank']) ? '-' : $positionInfo[$seInfo['id']]['rank'];
					$rankDiff = empty($positionInfo[$seInfo['id']]['rank_diff']) ? '' : $positionInfo[$seInfo['id']]['rank_diff'];
					$valueList[] = $rank. strip_tags($rankDiff);
				}
				$exportContent .= createExportContent( $valueList);
			}
			exportToCsv('keyword_report_summary', $exportContent);
		} else {
			$this->set('list', $keywordList);
			$this->render('report/reportsummary');	
		}		
	}
	
	# func to show reports
	function showReports($searchInfo = '') {
		
		$userId = isLoggedIn();
		if (!empty ($searchInfo['from_time'])) {
			$fromTime = strtotime($searchInfo['from_time'] . ' 00:00:00');
		} else {
			$fromTime = mktime(0, 0, 0, date('m'), date('d') - 30, date('Y'));
		}
		if (!empty ($searchInfo['to_time'])) {
			$toTime = strtotime($searchInfo['to_time'] . ' 23:59:59');
		} else {
			$toTime = mktime();
		}
		$this->set('fromTime', date('Y-m-d', $fromTime));
		$this->set('toTime', date('Y-m-d', $toTime));
		
		$keywordController = New KeywordController();
		if(!empty($searchInfo['keyword_id']) && !empty($searchInfo['rep'])){
			
			$searchInfo['keyword_id'] = intval($searchInfo['keyword_id']);
			$keywordInfo = $keywordController->__getKeywordInfo($searchInfo['keyword_id']);
			$searchInfo['website_id'] = $keywordInfo['website_id'];
		}

		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsitesWithActiveKeywords($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);

		$keywordList = $keywordController->__getAllKeywords($userId, $websiteId, true);
		$this->set('keywordList', $keywordList);
		$keywordId = empty ($searchInfo['keyword_id']) ? $keywordList[0]['id'] : $searchInfo['keyword_id'];
		$this->set('keywordId', $keywordId);

		$seController = New SearchEngineController();
		$seList = $seController->__getAllSearchEngines();
		$this->set('seList', $seList);
		$seId = empty ($searchInfo['se_id']) ? $seList[0]['id'] : intval($searchInfo['se_id']);
		$this->set('seId', $seId);
		$this->set('seInfo', $seController->__getsearchEngineInfo($seId));

		$conditions = empty ($keywordId) ? "" : " and s.keyword_id=$keywordId";
		$conditions .= empty ($seId) ? "" : " and s.searchengine_id=$seId";
		$sql = "select s.*,sd.url,sd.title,sd.description 
								from searchresults s,searchresultdetails sd 
								where s.id=sd.searchresult_id 
								and time>= $fromTime and time<=$toTime $conditions  
								order by s.time";
		$repList = $this->db->select($sql);

		$reportList = array ();
		foreach ($repList as $repInfo) {
			$var = 'se' . $seId . $repInfo['keyword_id'] . $repInfo['time'];
			if (empty ($reportList[$var])) {
				$reportList[$var] = $repInfo;
			} else {
				if ($repInfo['rank'] < $reportList[$var]['rank']) {
					$reportList[$var] = $repInfo;
				}
			}

		}

		$prevRank = 0;
		$i = 0;
		foreach ($reportList as $key => $repInfo) {
			$rankDiff = '';
			if ($i > 0) {
				$rankDiff = $prevRank - $repInfo['rank'];
				if ($rankDiff > 0) {
					$rankDiff = "<font class='green'>($rankDiff)</font>";
				}
				elseif ($rankDiff < 0) {
					$rankDiff = "<font class='red'>($rankDiff)</font>";
				}
			}
			$reportList[$key]['rank_diff'] = empty ($rankDiff) ? '' : $rankDiff;
			$prevRank = $repInfo['rank'];
			$i++;
		}

		$this->set('list', array_reverse($reportList, true));
		$this->render('report/report');
	}

	# func to show reports in a time
	function showTimeReport($searchInfo = '') {
		
		$fromTime = $searchInfo['time'];
		$toTime = $fromTime + (3600 * 24);
		$keywordId = intval($searchInfo['keyId']);
		$seId = intval($searchInfo['seId']);
		$seController = New SearchEngineController();
		$this->set('seInfo', $seController->__getsearchEngineInfo($seId));

		$conditions = empty ($keywordId) ? "" : " and s.keyword_id=$keywordId";
		$conditions .= empty ($seId) ? "" : " and s.searchengine_id=$seId";
		$sql = "select s.*,sd.url,sd.title,sd.description 
								from searchresults s,searchresultdetails sd 
								where s.id=sd.searchresult_id 
								and time>= $fromTime and time<$toTime $conditions  
								order by s.rank";
		$reportList = $this->db->select($sql);
		$this->set('list', $reportList);
		$this->render('report/timereport');
	}

	# func to show graphical reports
	function showGraphicalReports($searchInfo = '') {		
		
		$userId = isLoggedIn();
		if (!empty ($searchInfo['from_time'])) {
			$fromTime = strtotime($searchInfo['from_time'] . ' 00:00:00');
		} else {			
			$fromTime = mktime(0, 0, 0, date('m'), date('d') - 30, date('Y'));
		}
		if (!empty ($searchInfo['to_time'])) {
			$toTime = strtotime($searchInfo['to_time'] . ' 23:59:59');
		} else {
			$toTime = mktime();
		}
		$this->set('fromTime', date('Y-m-d', $fromTime));
		$this->set('toTime', date('Y-m-d', $toTime));

		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsitesWithActiveKeywords($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);

		$keywordController = New KeywordController();
		$keywordList = $keywordController->__getAllKeywords($userId, $websiteId, true);
		$this->set('keywordList', $keywordList);
		$keywordId = empty ($searchInfo['keyword_id']) ? $keywordList[0]['id'] : intval($searchInfo['keyword_id']);
		$this->set('keywordId', $keywordId);

		$seController = New SearchEngineController();
		$seList = $seController->__getAllSearchEngines();
		$this->set('seList', $seList);
		$seId = empty ($searchInfo['se_id']) ? '' : intval($searchInfo['se_id']);
		$this->set('seId', $seId);
		$this->set('seNull', true);		
		$this->set('graphUrl', "graphical-reports.php?sec=graph&fromTime=$fromTime&toTime=$toTime&keywordId=$keywordId&seId=$seId");
		
		$this->render('report/graphicalreport');
	}
	
	# function to show an message in graph when no records exist
	function showMessageAsImage($msg='', $width=700, $height=30, $red=233, $green=14, $blue=91) {	    
		
		$im = imagecreate($width, $height);		
        $bgColor = imagecolorallocate($im, 245, 248, 250);
        $textColor = imagecolorallocate($im, 233, 14, 91);
        imagestring($im, 5, 260, 5,  $msg, $textColor);
        imagepng($im);
        imagedestroy($im);
        exit;
	}

	# function to show graph
	function showGraph($searchInfo = '') {
		
		$conditions = empty ($searchInfo['keywordId']) ? "" : " and s.keyword_id=".intval($searchInfo['keywordId']);
		$conditions .= empty ($searchInfo['seId']) ? "" : " and s.searchengine_id=".intval($searchInfo['seId']);
		$sql = "select s.*,se.domain 
					from searchresults s,searchengines se  
					where s.searchengine_id=se.id 
					and time>= ".intval($searchInfo['fromTime'])." and time<".intval($searchInfo['toTime'])." $conditions  
					order by s.time";
		$repList = $this->db->select($sql);		
		$reportList = array ();
		$seList = array();
		foreach ($repList as $repInfo) {
			$var = $repInfo['searchengine_id'] . $repInfo['keyword_id'] . $repInfo['time'];
			if (empty ($reportList[$var])) {
				$reportList[$var] = $repInfo;
			} else {
				if ($repInfo['rank'] < $reportList[$var]['rank']) {
					$reportList[$var] = $repInfo;
				}
			}
			
			
			if(empty($seList[$repInfo['searchengine_id']])){
				$seList[$repInfo['searchengine_id']] = $repInfo['domain'];
			}
		}
		asort($seList);	
		
		$dataList = array();
		$maxValue = 0;
		foreach($reportList as $repInfo){
			$seId = $repInfo['searchengine_id'];
			$dataList[$repInfo['time']][$seId] = $repInfo['rank'];
			$maxValue = ($repInfo['rank'] > $maxValue) ? $repInfo['rank'] : $maxValue;
		}
		
		// check whether the records are available for drawing graph
		if(empty($dataList) || empty($maxValue)) {
		    $this->showMessageAsImage($_SESSION['text']['common']['No Records Found']."!");		    
		}
		
		# Dataset definition
		$dataSet = new pData;
		foreach($dataList as $dataInfo){
			$i = 1;	
			foreach($seList as $seId => $seVal){
				$val = empty($dataInfo[$seId]) ? 0 : $dataInfo[$seId];
				$dataSet->AddPoint($val, "Serie".$i++);				  
			}
		}
		
		$i = 1;
		foreach($seList as $seDomain){
			$dataSet->AddSerie("Serie$i");
			$dataSet->SetSerieName($seDomain, "Serie$i");
			$i++;
		}
		
		$serieCount = count($seList) + 1;
		$dataSet->AddPoint(array_keys($dataList), "Serie$serieCount");
		$dataSet->SetAbsciseLabelSerie("Serie$serieCount");
		
		$dataSet->SetXAxisName($_SESSION['text']['common']["Date"]);		
		$dataSet->SetYAxisName($_SESSION['text']['common']["Rank"]);
		$dataSet->SetXAxisFormat("date");		

		# Initialise the graph
		$chart = new pChart(720, 520);
		$chart->setFixedScale(0, $maxValue );		
		$chart->setFontProperties("fonts/tahoma.ttf", 8);
		$chart->setGraphArea(85, 30, 670, 425);
		$chart->drawFilledRoundedRectangle(7, 7, 713, 513, 5, 240, 240, 240);
		$chart->drawRoundedRectangle(5, 5, 715, 515, 5, 230, 230, 230);

		$chart->drawGraphArea(255, 255, 255, TRUE);
		$chart->drawScale($dataSet->GetData(), $dataSet->GetDataDescription(), SCALE_NORMAL, 150, 150, 150, TRUE, 90, 2);
		$chart->drawGrid(4, TRUE, 230, 230, 230, 50);

		# Draw the 0 line   
		$chart->setFontProperties("fonts/tahoma.ttf", 6);
		$chart->drawTreshold(0, 143, 55, 72, TRUE, TRUE);

		# Draw the line graph
		$chart->drawLineGraph($dataSet->GetData(), $dataSet->GetDataDescription());
		$chart->drawPlotGraph($dataSet->GetData(), $dataSet->GetDataDescription(), 3, 2, 255, 255, 255);
		
		$j = 1;
		$chart->setFontProperties("fonts/tahoma.ttf", 10);
		foreach($seList as $seDomain){
			$chart->writeValues($dataSet->GetData(), $dataSet->GetDataDescription(), "Serie".$j++);
		}

		# Finish the graph
		$chart->setFontProperties("fonts/tahoma.ttf", 8);
		$chart->drawLegend(90, 35, $dataSet->GetDataDescription(), 255, 255, 255);
		$chart->setFontProperties("fonts/tahoma.ttf", 10);
		$chart->drawTitle(60, 22, $this->spTextKeyword["Keyword Position Report"], 50, 50, 50, 585);
		$chart->stroke();
	}
	
	# func to show genearte reports interface
	function showGenerateReports($searchInfo = '') {
		
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? '' : $searchInfo['website_id'];
		$this->set('websiteId', $websiteId);

		$keywordController = New KeywordController();
		$keywordList = $keywordController->__getAllKeywords($userId, $websiteId, true);
		$this->set('keywordList', $keywordList);
		$this->set('keyNull', true);
		$keywordId = empty ($searchInfo['keyword_id']) ? '' : $searchInfo['keyword_id'];
		$this->set('keywordId', $keywordId);		

		$seController = New SearchEngineController();
		$seList = $seController->__getAllSearchEngines();
		$this->set('seList', $seList);
		$seId = empty ($searchInfo['se_id']) ? '' : $searchInfo['se_id'];
		$this->set('seId', $seId);
		$this->set('seNull', true);
		$this->set('seStyle', 170);		
				
		$this->render('report/generatereport');
	}
	
	# func to generate reports
	function generateReports( $searchInfo='' ) {		
		$userId = isLoggedIn();
		$keywordId = empty ($searchInfo['keyword_id']) ? '' : intval($searchInfo['keyword_id']);
		$websiteId = empty ($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);
		$seId = empty ($searchInfo['se_id']) ? '' : intval($searchInfo['se_id']);
		
		$seController = New SearchEngineController();
		$this->seList = $seController->__getAllCrawlFormatedSearchEngines();
		
		$sql = "select k.*,w.url from keywords k,websites w where k.website_id=w.id and k.status=1";
		if(!empty($userId) && !isAdmin()) $sql .= " and w.user_id=$userId";
		if(!empty($websiteId)) $sql .= " and k.website_id=$websiteId";
		if(!empty($keywordId)) $sql .= " and k.id=$keywordId";
		$sql .= " order by k.name";
		$keywordList = $this->db->select($sql);		
		
		if(count($keywordList) <= 0){
			echo "<p class='note error'>".$_SESSION['text']['common']['No Keywords Found']."</p>";
			exit;
		}
		
		# loop through each keyword			
		foreach ( $keywordList as $keywordInfo ) {
			$this->seFound = 0;
			$crawlResult = $this->crawlKeyword($keywordInfo, $seId);
			foreach($crawlResult as $sengineId => $matchList){
				if($matchList['status']){
					foreach($matchList['matched'] as $i => $matchInfo){
						$remove = ($i == 0) ? true : false;						
						$matchInfo['se_id'] = $sengineId;						
						$matchInfo['keyword_id'] = $keywordInfo['id'];
						$this->saveMatchedKeywordInfo($matchInfo, $remove);
					}
					echo "<p class='note notesuccess'>".$this->spTextKeyword['Successfully crawled keyword']." <b>{$keywordInfo['name']}</b> ".$this->spTextKeyword['results from']." <b>".$this->seList[$sengineId]['domain']."</b>.....</p>";
				}else{
					echo "<p class='note notefailed'>".$this->spTextKeyword['Crawling keyword']." <b>{$keywordInfo['name']}</b> ".$this->spTextKeyword['results from']." <b>".$this->seList[$sengineId]['domain']."</b> ".$_SESSION['text']['common']['failed']."......</p>";
				}
			}
			if(empty($this->seFound)){
				echo "<p class='note notefailed'>".$_SESSION['text']['common']['Keyword']." <b>{$keywordInfo['name']}</b> ".$this->spTextKeyword['not assigned to required search engines']."........</p>";
			}
			sleep(SP_CRAWL_DELAY);
		}	
	}
	
	# function to format pagecontent
	function formatPageContent($seInfoId, $pageContent) {
	    if (!empty($this->seList[$seInfoId]['from_pattern']) && $this->seList[$seInfoId]['to_pattern']) {
	        $pattern = $this->seList[$seInfoId]['from_pattern']."(.*)".$this->seList[$seInfoId]['to_pattern'];
	        if (preg_match("/$pattern/is", $pageContent, $matches)) {
	            if (!empty($matches[1])) {
	                $pageContent = $matches[1];
	            }
	        }
	    }
	    return $pageContent;	    
	}
	
	# func to crawl keyword
	function crawlKeyword( $keywordInfo, $seId='' ) {
		$crawlResult = array();
		$websiteUrl = formatUrl($keywordInfo['url']);
		if(empty($websiteUrl)) return $crawlResult;
		if(empty($keywordInfo['name'])) return $crawlResult;	
		
		$seList = explode(':', $keywordInfo['searchengines']);
		foreach($seList as $seInfoId){
			if(!empty($seId) && ($seInfoId != $seId)) continue;
			$this->seFound = 1;
			$searchUrl = str_replace('[--keyword--]', urlencode(stripslashes($keywordInfo['name'])), $this->seList[$seInfoId]['url']);
			$searchUrl = str_replace('[--lang--]', $keywordInfo['lang_code'], $searchUrl);
			$searchUrl = str_replace('[--country--]', $keywordInfo['country_code'], $searchUrl);
			$seUrl = str_replace('[--start--]', $this->seList[$seInfoId]['start'], $searchUrl);
			
			if(!empty($this->seList[$seInfoId]['cookie_send'])){
				$this->seList[$seInfoId]['cookie_send'] = str_replace('[--lang--]', $keywordInfo['lang_code'], $this->seList[$seInfoId]['cookie_send']);
				$this->spider->_CURLOPT_COOKIE = $this->seList[$seInfoId]['cookie_send'];				
			}
			
			$result = $this->spider->getContent($seUrl);
			$pageContent = $this->formatPageContent($seInfoId, $result['page']);
			
			$seStart = $this->seList[$seInfoId]['start'] + $this->seList[$seInfoId]['start_offset'];
			while(empty($result['error']) && ($seStart < $this->seList[$seInfoId]['max_results']) ){
				$seUrl = str_replace('[--start--]', $seStart, $searchUrl);
				$result = $this->spider->getContent($seUrl);
				$pageContent .= $this->formatPageContent($seInfoId, $result['page']);
				$seStart += $this->seList[$seInfoId]['start_offset'];
				sleep(SP_CRAWL_DELAY);
			}

			# to check whether utf8 conversion needed
			if(!empty($this->seList[$seInfoId]['encoding'])){
				$pageContent = mb_convert_encoding($pageContent, "UTF-8", $this->seList[$seInfoId]['encoding']);
			}
			
			$crawlStatus = 0;
			if(empty($result['error'])){		
				if(preg_match_all($this->seList[$seInfoId]['regex'], $pageContent, $matches)){
					$urlList = $matches[$this->seList[$seInfoId]['url_index']];
					$crawlResult[$seInfoId]['matched'] = array();
					foreach($urlList as $i => $url){
						$url = strip_tags($url);
						$url = str_replace(array('http%3a', 'https%3a'), array('http:', 'https:'), $url);
						if(!preg_match('/^http:\/\/|^https:\/\//i', $url)) continue;
						if($this->showAll || stristr($url, $websiteUrl)){

							if($this->showAll && stristr($url, $websiteUrl)){
								$matchInfo['found'] = 1; 
							}else{
								$matchInfo['found'] = 0;
							}
							$matchInfo['url'] = $url;
							$matchInfo['title'] = strip_tags($matches[$this->seList[$seInfoId]['title_index']][$i]);
							$matchInfo['description'] = strip_tags($matches[$this->seList[$seInfoId]['description_index']][$i]);
							$matchInfo['rank'] = $i + 1;
							$crawlResult[$seInfoId]['matched'][] = $matchInfo;
						}							
					}
					$crawlStatus = 1;
				}else{
					if(SP_DEBUG){
						echo "<p class='note' style='text-align:left;'>Error occured while parsing $seUrl ".formatErrorMsg("Regex not matched <br>\n")."</p>";
					}
				}	
			}else{
				if(SP_DEBUG){
					echo "<p class='note' style='text-align:left;'>Error occured while crawling $seUrl ".formatErrorMsg($result['errmsg']."<br>\n")."</p>";
				}
			}			
			$crawlResult[$seInfoId]['status'] = $crawlStatus;			
			sleep(SP_CRAWL_DELAY);
		}
		return  $crawlResult;
	}
	
	# func to save the report
	function saveMatchedKeywordInfo($matchInfo, $remove=false) {
		$time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		
		if($remove){
			$sql = "select id from searchresults where keyword_id={$matchInfo['keyword_id']} and searchengine_id={$matchInfo['se_id']} and time=$time";
			$recordList = $this->db->select($sql);
		
			if(count($recordList) > 0){
				foreach($recordList as $recordInfo){
					$sql = "delete from searchresultdetails where searchresult_id=".$recordInfo['id'];
					$this->db->query($sql);
				}
				
				$sql = "delete from searchresults where keyword_id={$matchInfo['keyword_id']} and searchengine_id={$matchInfo['se_id']} and time=$time";
				$this->db->query($sql);
			}
		}
		
		$sql = "insert into searchresults(keyword_id,searchengine_id,rank,time)
				values({$matchInfo['keyword_id']},{$matchInfo['se_id']},{$matchInfo['rank']},$time)";
		$this->db->query($sql);
		
		$recordId = $this->db->getMaxId('searchresults');		
		$sql = "insert into searchresultdetails(searchresult_id,url,title,description)
				values($recordId,'{$matchInfo['url']}','".addslashes($matchInfo['title'])."','".addslashes($matchInfo['description'])."')";
		$this->db->query($sql); 
	}
	
	# func to check keyword rank
	function quickRankChecker() {
		
		$seController = New SearchEngineController();
		$seList = $seController->__getAllSearchEngines();
		$this->set('seList', $seList);
		$this->set('seStyle', 230);		
		$seId = empty ($searchInfo['se_id']) ? '' : $searchInfo['se_id'];
		$this->set('seId', $seId);
		
		$langController = New LanguageController();
		$this->set('langNull', true);
		$this->set('langStyle', 230);			
		$this->set('langList', $langController->__getAllLanguages());
		
		$countryController = New CountryController();
		$this->set('countryList', $countryController->__getAllCountries());
		$this->set('countryNull', true);		
		$this->set('countryStyle', 230);
		
		$this->render('report/quickrankchecker');
	}
	
	# func to show quick rank report
	function showQuickRankChecker($keywordInfo='') {
		
		$keywordInfo['searchengines'] = $keywordInfo['se_id'];
		$this->showAll = $keywordInfo['show_all'];
		
		$seController = New SearchEngineController();
		$this->seList = $seController->__getAllCrawlFormatedSearchEngines();
		
		$crawlResult = $this->crawlKeyword($keywordInfo);
		
		$resultList = array();
		if(!empty($crawlResult[$keywordInfo['se_id']]['status'])){
			$resultList = $crawlResult[$keywordInfo['se_id']]['matched'];
		}
		$this->set('list', $resultList);
		
		$this->render('report/showquickrankchecker');
	}
}
?>