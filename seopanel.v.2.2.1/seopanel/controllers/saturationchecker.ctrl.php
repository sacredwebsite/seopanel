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

# class defines all backlink controller functions
class SaturationCheckerController extends Controller{
	var $url;
	var $colList = array('google' => 'google', 'yahoo' => 'yahoo', 'msn' => 'msn');
	
	function showSaturationChecker() {
		
		$this->render('saturationchecker/showsaturationchecker');
	}
	
	function findSearchEngineSaturation($searchInfo) {
		$urlList = explode("\n", $searchInfo['website_urls']);
		$list = array();
		foreach ($urlList as $url) {
			if(!preg_match('/\w+/', $url)) continue;
			if(!stristr($url, 'http://')) $url = "http://".$url;
			$list[] = $url;
		}

		$this->set('list', $list);
		$this->render('saturationchecker/findsearchenginesaturation');
	}
	
	function printSearchEngineSaturation($saturationInfo){
		$this->url = $saturationInfo['url'];
		print $this->__getSaturationRank($saturationInfo['engine']);
	}
	
	function __getSaturationRank ($engine) {
		
		switch ($engine) {
			
			#google
			case 'google':
				$url = 'http://www.google.com/search?q=site%3A' . urlencode($this->url);			
				$v = $this->spider->getContent($url);
				$v = empty($v['page']) ? '' :  $v['page'];				
				
				if(preg_match('/about ([0-9\,]+) results/si', $v, $r)){					
				}elseif(preg_match('/<div id=resultStats>([0-9\,]+) results/si', $v, $r)){					
				}elseif(preg_match('/([0-9\,]+) results/si', $v, $r)){					
				}elseif(preg_match('/about <b>([0-9\,]+)<\/b> from/si', $v, $r)){					
				}
								
				$rank = ($r[1]) ? str_replace(',', '', $r[1]) : 0;
				if(empty($rank)){
					preg_match('/of <b>([0-9\,]+)<\/b>/si', $v, $r);
					$rank = ($r[1]) ? str_replace(',', '', $r[1]) : 0;
				}
				return $rank;
				break;
				
			#yahoo
			case 'yahoo':
				$url = 'http://siteexplorer.search.yahoo.com/advsearch?p=' . urlencode($this->url);
				$v = $this->spider->getContent($url);
				$v = empty($v['page']) ? '' :  $v['page'];
				preg_match('/Pages \(([0-9\,]+)\)/si', $v, $r);
				return ($r[1]) ? str_replace(',', '', $r[1]) : 0;
				break;
				
			#msn
			case 'msn':
				$url = 'http://www.bing.com/search?q=site%3A' . urlencode($this->url);
				$v = $this->spider->getContent($url);
				$v = empty($v['page']) ? '' :  $v['page'];				
				preg_match('/of ([0-9\,]+) results/si', $v, $r);
				return ($r[1]) ? str_replace(',', '', $r[1]) : 0;
				break;
		}
		
		return 0;
	}
	
	# func to show genearte reports interface
	function showGenerateReports($searchInfo = '') {
				
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
						
		$this->render('saturationchecker/generatereport');
	}
	
	# func to generate reports
	function generateReports( $searchInfo='' ) {
				
		$userId = isLoggedIn();		
		$websiteId = empty ($searchInfo['website_id']) ? '' : intval($searchInfo['website_id']);
		
		$sql = "select id,url from websites where status=1";
		if(!empty($userId) && !isAdmin()) $sql .= " and user_id=$userId";
		if(!empty($websiteId)) $sql .= " and id=$websiteId";
		$sql .= " order by name";
		$websiteList = $this->db->select($sql);		
		
		if(count($websiteList) <= 0){
			echo "<p class='note'>".$_SESSION['text']['common']['nowebsites']."!</p>";
			exit;
		}
		
		# loop through each websites			
		foreach ( $websiteList as $websiteInfo ) {
			$this->url = $websiteUrl = addHttpToUrl($websiteInfo['url']);			
			foreach ($this->colList as $col => $dbCol) {
				$websiteInfo[$col] = $this->__getSaturationRank($col);
			}
			
			$this->saveRankResults($websiteInfo, true);			
			echo "<p class='note notesuccess'>".$this->spTextSat['Saved Search Engine Saturation results of']." <b>$websiteUrl</b>.....</p>";
		}	
	}
	
	# function to save rank details
	function saveRankResults($matchInfo, $remove=false) {
		$time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		
		if($remove){				
			$sql = "delete from saturationresults where website_id={$matchInfo['id']} and result_time=$time";
			$this->db->query($sql);
		}
		
		$sql = "insert into saturationresults(website_id,google,yahoo,msn,result_time)
				values({$matchInfo['id']},{$matchInfo['google']},{$matchInfo['yahoo']},{$matchInfo['msn']},$time)";
		$this->db->query($sql);
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

		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$websiteId = empty ($searchInfo['website_id']) ? $websiteList[0]['id'] : intval($searchInfo['website_id']);
		$this->set('websiteId', $websiteId);
		
		$conditions = empty ($websiteId) ? "" : " and s.website_id=$websiteId";		
		$sql = "select s.* ,w.name
								from saturationresults s,websites w 
								where s.website_id=w.id 
								and result_time>= $fromTime and result_time<=$toTime $conditions  
								order by result_time";
		$reportList = $this->db->select($sql);
		
		$i = 0;
		$colList = $this->colList;
		foreach ($colList as $col => $dbCol) {
			$prevRank[$col] = 0;
		}
		
		# loop throgh rank
		foreach ($reportList as $key => $repInfo) {
			foreach ($colList as $col => $dbCol) {
				$rankDiff[$col] = '';
			}			
			
			foreach ($colList as $col => $dbCol) {
				if ($i > 0) {
					$rankDiff[$col] = ($prevRank[$col] - $repInfo[$dbCol]) * -1;
					if ($rankDiff[$col] > 0) {
						$rankDiff[$col] = "<font class='green'>($rankDiff[$col])</font>";
					}elseif ($rankDiff[$col] < 0) {
						$rankDiff[$col] = "<font class='red'>($rankDiff[$col])</font>";
					}
				}
				$reportList[$key]['rank_diff_'.$col] = empty ($rankDiff[$col]) ? '' : $rankDiff[$col];
			}
			
			foreach ($colList as $col => $dbCol) {
				$prevRank[$col] = $repInfo[$dbCol];
			}
			
			$i++;
		}

		$this->set('list', array_reverse($reportList, true));
		$this->render('saturationchecker/saturationreport');
	}
	
	# func to get reports of saturation of a website
	function __getWebsiteSaturationReport($websiteId, $limit=1) {
				
		$sql = "select s.* ,w.name
								from saturationresults s,websites w 
								where s.website_id=w.id 
								 and s.website_id=$websiteId  
								order by result_time DESC
								Limit 0, ".($limit+1);
		$reportList = $this->db->select($sql);
		$reportList = array_reverse($reportList);
		
		$i = 0;
		$colList = $this->colList;
		foreach ($colList as $col => $dbCol) {
			$prevRank[$col] = 0;
		}
		
		# loop throgh rank
		foreach ($reportList as $key => $repInfo) {
			foreach ($colList as $col => $dbCol) {
				$rankDiff[$col] = '';
			}			
			
			foreach ($colList as $col => $dbCol) {
				if ($i > 0) {
					$rankDiff[$col] = ($prevRank[$col] - $repInfo[$dbCol]) * -1;
					if ($rankDiff[$col] > 0) {
						$rankDiff[$col] = "<font class='green'>($rankDiff[$col])</font>";
					}elseif ($rankDiff[$col] < 0) {
						$rankDiff[$col] = "<font class='red'>($rankDiff[$col])</font>";
					}
				}
				$reportList[$key]['rank_diff_'.$col] = empty ($rankDiff[$col]) ? '' : $rankDiff[$col];
			}
			
			foreach ($colList as $col => $dbCol) {
				$prevRank[$col] = $repInfo[$dbCol];
			}
			
			$i++;
		}

		$reportList = array_reverse(array_slice($reportList, count($reportList) - $limit));
		return $reportList;
	}
	
}
?>