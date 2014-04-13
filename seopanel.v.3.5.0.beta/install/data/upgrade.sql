--
-- Seo Panel 3.5.0 changes
--
UPDATE `searchengines` SET `regex` = '<li.*?<h3><a.*?RU=(.*?)\\/.*?>(.*?)<\\/a><\\/h3>.*?<div.*?>(.*?)<\\/div>' WHERE url like '%yahoo%';

--
-- Table structure for table `crawl_log`
--

CREATE TABLE IF NOT EXISTS `crawl_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `crawl_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'other',
  `ref_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `crawl_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `crawl_referer` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `crawl_cookie` text COLLATE utf8_unicode_ci NOT NULL,
  `crawl_post_fields` text COLLATE utf8_unicode_ci NOT NULL,
  `crawl_useragent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `crawl_status` tinyint(4) NOT NULL DEFAULT '1',
  `proxy_id` int(11) unsigned NOT NULL,
  `log_message` text COLLATE utf8_unicode_ci NOT NULL,
  `crawl_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `crawl_status` (`crawl_status`),
  KEY `crawl_type` (`crawl_type`),
  KEY `ref_id` (`ref_id`),
  KEY `proxy_id` (`proxy_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `timezone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timezone_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `timezone_label` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=91 ;

INSERT INTO `timezone` (`id`, `timezone_name`, `timezone_label`) VALUES
(1, 'Pacific/Midway', '(GMT-11:00) Midway Island, Samoa'),
(2, 'America/Adak', '(GMT-10:00) Hawaii-Aleutian'),
(3, 'Etc/GMT+10', '(GMT-10:00) Hawaii'),
(4, 'Pacific/Marquesas', '(GMT-09:30) Marquesas Islands'),
(5, 'Pacific/Gambier', '(GMT-09:00) Gambier Islands'),
(6, 'America/Anchorage', '(GMT-09:00) Alaska'),
(7, 'America/Ensenada', '(GMT-08:00) Tijuana, Baja California'),
(8, 'Etc/GMT+8', '(GMT-08:00) Pitcairn Islands'),
(9, 'America/Los_Angeles', '(GMT-08:00) Pacific Time (US & Canada)'),
(10, 'America/Denver', '(GMT-07:00) Mountain Time (US & Canada)'),
(11, 'America/Chihuahua', '(GMT-07:00) Chihuahua, La Paz, Mazatlan'),
(12, 'America/Dawson_Creek', '(GMT-07:00) Arizona'),
(13, 'America/Belize', '(GMT-06:00) Saskatchewan, Central America'),
(14, 'America/Cancun', '(GMT-06:00) Guadalajara, Mexico City, Monterrey'),
(15, 'Chile/EasterIsland', '(GMT-06:00) Easter Island'),
(16, 'America/Chicago', '(GMT-06:00) Central Time (US & Canada)'),
(17, 'America/New_York', '(GMT-05:00) Eastern Time (US & Canada)'),
(18, 'America/Havana', '(GMT-05:00) Cuba'),
(19, 'America/Bogota', '(GMT-05:00) Bogota, Lima, Quito, Rio Branco'),
(20, 'America/Caracas', '(GMT-04:30) Caracas'),
(21, 'America/Santiago', '(GMT-04:00) Santiago'),
(22, 'America/La_Paz', '(GMT-04:00) La Paz'),
(23, 'Atlantic/Stanley', '(GMT-04:00) Faukland Islands'),
(24, 'America/Campo_Grande', '(GMT-04:00) Brazil'),
(25, 'America/Goose_Bay', '(GMT-04:00) Atlantic Time (Goose Bay)'),
(26, 'America/Glace_Bay', '(GMT-04:00) Atlantic Time (Canada)'),
(27, 'America/St_Johns', '(GMT-03:30) Newfoundland'),
(28, 'America/Araguaina', '(GMT-03:00) UTC-3'),
(29, 'America/Montevideo', '(GMT-03:00) Montevideo'),
(30, 'America/Miquelon', '(GMT-03:00) Miquelon, St. Pierre'),
(31, 'America/Godthab', '(GMT-03:00) Greenland'),
(32, 'America/Argentina/Buenos_Aires', '(GMT-03:00) Buenos Aires'),
(33, 'America/Sao_Paulo', '(GMT-03:00) Brasilia'),
(34, 'America/Noronha', '(GMT-02:00) Mid-Atlantic'),
(35, 'Atlantic/Cape_Verde', '(GMT-01:00) Cape Verde Is.'),
(36, 'Atlantic/Azores', '(GMT-01:00) Azores'),
(37, 'Europe/Belfast', '(GMT) Greenwich Mean Time : Belfast'),
(38, 'Europe/Dublin', '(GMT) Greenwich Mean Time : Dublin'),
(39, 'Europe/Lisbon', '(GMT) Greenwich Mean Time : Lisbon'),
(40, 'Europe/London', '(GMT) Greenwich Mean Time : London'),
(41, 'Africa/Abidjan', '(GMT) Monrovia, Reykjavik'),
(42, 'Europe/Amsterdam', '(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna'),
(43, 'Europe/Belgrade', '(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague'),
(44, 'Europe/Brussels', '(GMT+01:00) Brussels, Copenhagen, Madrid, Paris'),
(45, 'Africa/Algiers', '(GMT+01:00) West Central Africa'),
(46, 'Africa/Windhoek', '(GMT+01:00) Windhoek'),
(47, 'Asia/Beirut', '(GMT+02:00) Beirut'),
(48, 'Africa/Cairo', '(GMT+02:00) Cairo'),
(49, 'Asia/Gaza', '(GMT+02:00) Gaza'),
(50, 'Africa/Blantyre', '(GMT+02:00) Harare, Pretoria'),
(51, 'Asia/Jerusalem', '(GMT+02:00) Jerusalem'),
(52, 'Europe/Minsk', '(GMT+02:00) Minsk'),
(53, 'Asia/Damascus', '(GMT+02:00) Syria'),
(54, 'Europe/Moscow', '(GMT+03:00) Moscow, St. Petersburg, Volgograd'),
(55, 'Africa/Addis_Ababa', '(GMT+03:00) Nairobi'),
(56, 'Asia/Tehran', '(GMT+03:30) Tehran'),
(57, 'Asia/Dubai', '(GMT+04:00) Abu Dhabi, Muscat'),
(58, 'Asia/Yerevan', '(GMT+04:00) Yerevan'),
(59, 'Asia/Kabul', '(GMT+04:30) Kabul'),
(60, 'Asia/Yekaterinburg', '(GMT+05:00) Ekaterinburg'),
(61, 'Asia/Tashkent', '(GMT+05:00) Tashkent'),
(62, 'Asia/Kolkata', '(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi'),
(63, 'Asia/Katmandu', '(GMT+05:45) Kathmandu'),
(64, 'Asia/Dhaka', '(GMT+06:00) Astana, Dhaka'),
(65, 'Asia/Novosibirsk', '(GMT+06:00) Novosibirsk'),
(66, 'Asia/Rangoon', '(GMT+06:30) Yangon (Rangoon)'),
(67, 'Asia/Bangkok', '(GMT+07:00) Bangkok, Hanoi, Jakarta'),
(68, 'Asia/Krasnoyarsk', '(GMT+07:00) Krasnoyarsk'),
(69, 'Asia/Hong_Kong', '(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi'),
(70, 'Asia/Irkutsk', '(GMT+08:00) Irkutsk, Ulaan Bataar'),
(71, 'Australia/Perth', '(GMT+08:00) Perth'),
(72, 'Australia/Eucla', '(GMT+08:45) Eucla'),
(73, 'Asia/Tokyo', '(GMT+09:00) Osaka, Sapporo, Tokyo'),
(74, 'Asia/Seoul', '(GMT+09:00) Seoul'),
(75, 'Asia/Yakutsk', '(GMT+09:00) Yakutsk'),
(76, 'Australia/Adelaide', '(GMT+09:30) Adelaide'),
(77, 'Australia/Darwin', '(GMT+09:30) Darwin'),
(78, 'Australia/Brisbane', '(GMT+10:00) Brisbane'),
(79, 'Australia/Hobart', '(GMT+10:00) Hobart'),
(80, 'Asia/Vladivostok', '(GMT+10:00) Vladivostok'),
(81, 'Australia/Lord_Howe', '(GMT+10:30) Lord Howe Island'),
(82, 'Etc/GMT-11', '(GMT+11:00) Solomon Is., New Caledonia'),
(83, 'Asia/Magadan', '(GMT+11:00) Magadan'),
(84, 'Pacific/Norfolk', '(GMT+11:30) Norfolk Island'),
(85, 'Asia/Anadyr', '(GMT+12:00) Anadyr, Kamchatka'),
(86, 'Pacific/Auckland', '(GMT+12:00) Auckland, Wellington'),
(87, 'Etc/GMT-12', '(GMT+12:00) Fiji, Kamchatka, Marshall Is.'),
(88, 'Pacific/Chatham', '(GMT+12:45) Chatham Islands'),
(89, 'Pacific/Tongatapu', '(GMT+13:00) Nuku''alofa'),
(90, 'Pacific/Kiritimati', '(GMT+14:00) Kiritimati');

---
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'panel', 'Log Manager', 'Log Manager');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'panel', 'Crawl Log Manager', 'Crawl Log Manager');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'panel', 'Proxy Perfomance', 'Proxy Perfomance');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'label', 'Success', 'Success');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'label', 'Fail', 'Fail');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'label', 'Reference', 'Reference');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'label', 'Subject', 'Subject');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'label', 'Cookie', 'Cookie');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'label', 'User agent', 'User agent');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'label', 'Referer', 'Referer');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'label', 'Order By', 'Order By');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'log', 'Clear All Logs', 'Clear All Logs');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'log', 'Crawl Log Details', 'Crawl Log Details');
INSERT INTO `texts` (`lang_code`, `category`, `label`, `content`) VALUES ('en', 'log', 'Post Fields', 'Post Fields');