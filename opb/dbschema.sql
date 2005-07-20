-- phpMyAdmin SQL Dump
-- version 2.6.2-pl1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost:3306
-- Czas wygenerowania: 15 Lip 2005, 10:19
-- Wersja serwera: 4.1.11
-- Wersja PHP: 5.0.4
-- 
-- Baza danych: `openpb`
-- 

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_forum_perms`
-- 

CREATE TABLE `opb_forum_perms` (
  `gid` int(10) unsigned NOT NULL default '0',
  `forum_id` int(10) unsigned NOT NULL default '0',
  `perms` text,
  KEY `gid` (`gid`,`forum_id`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `opb_forum_perms`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_forums`
-- 

CREATE TABLE `opb_forums` (
  `id` int(10) NOT NULL auto_increment,
  `last_topic_id` int(10) unsigned NOT NULL default '0',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `last_poster_id` int(11) unsigned NOT NULL default '0',
  `last_poster_name` varchar(32) NOT NULL default '',
  `last_post_id` int(10) unsigned NOT NULL default '0',
  `title` varchar(45) default NULL,
  `description` text,
  `forum_order` smallint(8) unsigned default NULL,
  `sort_key` tinyint(2) unsigned default NULL,
  `sort_order` tinyint(2) unsigned default NULL,
  `topics` int(10) unsigned NOT NULL default '0',
  `posts` int(10) unsigned NOT NULL default '0',
  `rules_title` varchar(64) default NULL,
  `rules_text` text,
  `access_pass` varchar(40) default NULL,
  `show_rules` tinyint(1) unsigned default NULL,
  `flat_topics` tinyint(1) default '0',
  `use_html` tinyint(1) unsigned default NULL,
  `use_bbcode` tinyint(1) unsigned default NULL,
  `preview_posts` tinyint(1) unsigned default NULL,
  `allow_poll` tinyint(1) unsigned default NULL,
  `use_emots` tinyint(1) unsigned default NULL,
  `forum_status` tinyint(2) unsigned default NULL,
  `tree_left` int(8) default NULL,
  `tree_right` int(8) default NULL,
  PRIMARY KEY  (`id`),
  KEY `order` (`forum_order`),
  KEY `left` (`tree_left`),
  KEY `right` (`tree_right`)
) TYPE=MyISAM AUTO_INCREMENT=13 ;

-- 
-- Zrzut danych tabeli `opb_forums`
-- 

INSERT INTO `opb_forums` VALUES (1, 0, 0, 0, '', 0, 'Organizacja', NULL, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 1, 4);
INSERT INTO `opb_forums` VALUES (2, 0, 0, 0, '', 0, 'Webmastering', NULL, 1, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 5, 18);
INSERT INTO `opb_forums` VALUES (3, 0, 0, 0, '', 0, 'Inne', NULL, 2, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 0, 19, 24);
INSERT INTO `opb_forums` VALUES (4, 4, 1, 0, 'gfhfgh', 0, 'Forum organizacyjne', NULL, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1, 2, 3);
INSERT INTO `opb_forums` VALUES (5, 2, 2, 0, 'Zyx', 0, 'HTML', NULL, 3, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1, 10, 11);
INSERT INTO `opb_forums` VALUES (6, 0, 2, 0, '', 0, 'JavaScript', NULL, 4, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1, 12, 13);
INSERT INTO `opb_forums` VALUES (7, 0, 3, 0, '', 0, 'Hydepark', NULL, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1, 20, 21);
INSERT INTO `opb_forums` VALUES (8, 0, 3, 0, '', 0, '?¹mietnik', NULL, 1, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 1, 22, 23);
INSERT INTO `opb_forums` VALUES (9, 0, 2, 0, '', 0, 'Forum A', NULL, 6, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 16, 17);
INSERT INTO `opb_forums` VALUES (10, 0, 2, 0, '', 0, 'Forum B', NULL, 1, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 6, 7);
INSERT INTO `opb_forums` VALUES (11, 0, 2, 0, '', 0, 'Forum C', NULL, 5, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 14, 15);
INSERT INTO `opb_forums` VALUES (12, 0, 2, 0, '', 0, 'Forum D', NULL, 2, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 8, 9);

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_groups`
-- 

CREATE TABLE `opb_groups` (
  `gid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(28) default NULL,
  `type` smallint(4) NOT NULL default '0',
  `icon` varchar(16) NOT NULL default '',
  `description` text NOT NULL,
  `access` text,
  PRIMARY KEY  (`gid`)
) TYPE=MyISAM AUTO_INCREMENT=3 ;

-- 
-- Zrzut danych tabeli `opb_groups`
-- 

INSERT INTO `opb_groups` VALUES (1, 'Administrator', 6, '', '', NULL);
INSERT INTO `opb_groups` VALUES (2, 'Banned', 0, '', '', NULL);

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_messengers`
-- 

CREATE TABLE `opb_messengers` (
  `id` smallint(8) unsigned NOT NULL auto_increment,
  `name` varchar(32) default NULL,
  `icon` varchar(32) default NULL,
  `status_url` varchar(64) default NULL,
  `list_order` smallint(6) unsigned default NULL,
  `value_type` enum('n','i','a') default NULL,
  PRIMARY KEY  (`id`),
  KEY `order` (`list_order`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `opb_messengers`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_poll_votes`
-- 

CREATE TABLE `opb_poll_votes` (
  `uid` int(11) unsigned NOT NULL default '0',
  `pid` int(10) unsigned NOT NULL default '0',
  `choice` smallint(8) unsigned default NULL,
  KEY `uid` (`uid`,`pid`)
) TYPE=InnoDB;

-- 
-- Zrzut danych tabeli `opb_poll_votes`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_polls`
-- 

CREATE TABLE `opb_polls` (
  `pid` int(10) unsigned NOT NULL auto_increment,
  `starter_id` int(11) unsigned NOT NULL default '0',
  `topic_id` int(8) NOT NULL default '0',
  `start_date` int(10) unsigned default NULL,
  `question` varchar(255) default NULL,
  `choices` text,
  PRIMARY KEY  (`pid`),
  KEY `topic_id` (`topic_id`)
) TYPE=InnoDB AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `opb_polls`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_posts`
-- 

CREATE TABLE `opb_posts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `author_id` int(11) unsigned NOT NULL default '0',
  `topic` int(10) unsigned NOT NULL default '0',
  `title` varchar(64) default NULL,
  `body` text,
  `post_date` int(10) unsigned default NULL,
  `post_author` varchar(32) default NULL,
  `ip_address` varchar(16) default NULL,
  `use_signatures` tinyint(1) unsigned default NULL,
  `use_emots` tinyint(1) unsigned default NULL,
  `use_html` tinyint(1) unsigned default NULL,
  `use_bbcode` tinyint(1) unsigned default NULL,
  `edit_time` int(10) unsigned default NULL,
  `post_icon` smallint(5) unsigned default NULL,
  `tree_left` int(10) unsigned default NULL,
  `tree_right` int(10) unsigned default NULL,
  `parent_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `left` (`tree_left`),
  KEY `right` (`tree_right`),
  KEY `parent` (`parent_id`)
) TYPE=MyISAM AUTO_INCREMENT=7 ;

-- 
-- Zrzut danych tabeli `opb_posts`
-- 

INSERT INTO `opb_posts` VALUES (1, 0, 1, 'Temat próbny', 'pdpsd lol wymiatam z tymi tematami', NULL, 'TheZyxist', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
INSERT INTO `opb_posts` VALUES (2, 0, 1, 'Hajba', 'R?bane lasy deszczowe zapad?y si? w cie?. Z?otodajny las zapowiada dzie?.', NULL, 'TheZyxist', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
INSERT INTO `opb_posts` VALUES (3, 0, 1, 'Hajba', 'R?bane lasy deszczowe zapad?y si? w cie?. Z?otodajny las zapowiada dzie?.', NULL, 'TheZyxist', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
INSERT INTO `opb_posts` VALUES (4, 0, 2, 'Rozporek', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec commodo, nulla nec rutrum lobortis, nulla nisl feugiat purus, ut volutpat pede metus vestibulum ligula. Quisque vulputate turpis at enim. Suspendisse tempor congue lacus. Donec lectus elit, congue eu, tincidunt congue, hendrerit quis, orci. Duis nonummy. Morbi odio augue, aliquet sit amet, rutrum id, ultricies ac, est. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Etiam luctus diam in elit. Phasellus ligula lacus, gravida a, iaculis a, tempus in, dui. Integer congue neque tincidunt augue. Donec mi. Sed leo mauris, tempus id, rutrum eu, pulvinar vitae, leo. Pellentesque nec urna. Integer fringilla, lorem non rutrum nonummy, felis massa iaculis nibh, et euismod ipsum mauris vel dui. Nam nec ipsum. Nulla facilisi. \r\n\r\nCurabitur ornare dui non risus. Morbi dui magna, tempus eu, laoreet vel, porttitor a, metus. Curabitur commodo orci ut mauris. Ut scelerisque fermentum metus. Vestibulum in orci. Phasellus arcu orci, semper id, malesuada quis, placerat in, metus. Fusce quis urna a magna placerat fringilla. Sed semper placerat nisl. In hac habitasse platea dictumst. Ut at sapien. Etiam rhoncus turpis a orci.', NULL, 'Zyx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
INSERT INTO `opb_posts` VALUES (5, 0, 3, 'Maglownica', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec commodo, nulla nec rutrum lobortis, nulla nisl feugiat purus, ut volutpat pede metus vestibulum ligula. Quisque vulputate turpis at enim. Suspendisse tempor congue lacus. Donec lectus elit, congue eu, tincidunt congue, hendrerit quis, orci. Duis nonummy. Morbi odio augue, aliquet sit amet, rutrum id, ultricies ac, est. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Etiam luctus diam in elit. Phasellus ligula lacus, gravida a, iaculis a, tempus in, dui. Integer congue neque tincidunt augue. Donec mi. Sed leo mauris, tempus id, rutrum eu, pulvinar vitae, leo. Pellentesque nec urna. Integer fringilla, lorem non rutrum nonummy, felis massa iaculis nibh, et euismod ipsum mauris vel dui. Nam nec ipsum. Nulla facilisi. \r\n\r\nCurabitur ornare dui non risus. Morbi dui magna, tempus eu, laoreet vel, porttitor a, metus. Curabitur commodo orci ut mauris. Ut scelerisque fermentum metus. Vestibulum in orci. Phasellus arcu orci, semper id, malesuada quis, placerat in, metus. Fusce quis urna a magna placerat fringilla. Sed semper placerat nisl. In hac habitasse platea dictumst. Ut at sapien. Etiam rhoncus turpis a orci.', NULL, 'sdfsdf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
INSERT INTO `opb_posts` VALUES (6, 0, 4, 'Suszarka', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec commodo, nulla nec rutrum lobortis, nulla nisl feugiat purus, ut volutpat pede metus vestibulum ligula. Quisque vulputate turpis at enim. Suspendisse tempor congue lacus. Donec lectus elit, congue eu, tincidunt congue, hendrerit quis, orci. Duis nonummy. Morbi odio augue, aliquet sit amet, rutrum id, ultricies ac, est. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Etiam luctus diam in elit. Phasellus ligula lacus, gravida a, iaculis a, tempus in, dui. Integer congue neque tincidunt augue. Donec mi. Sed leo mauris, tempus id, rutrum eu, pulvinar vitae, leo. Pellentesque nec urna. Integer fringilla, lorem non rutrum nonummy, felis massa iaculis nibh, et euismod ipsum mauris vel dui. Nam nec ipsum. Nulla facilisi. \r\n\r\nCurabitur ornare dui non risus. Morbi dui magna, tempus eu, laoreet vel, porttitor a, metus. Curabitur commodo orci ut mauris. Ut scelerisque fermentum metus. Vestibulum in orci. Phasellus arcu orci, semper id, malesuada quis, placerat in, metus. Fusce quis urna a magna placerat fringilla. Sed semper placerat nisl. In hac habitasse platea dictumst. Ut at sapien. Etiam rhoncus turpis a orci.', 1117871453, 'gfhfgh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_session`
-- 

CREATE TABLE `opb_session` (
  `session_id` varchar(32) NOT NULL default '',
  `in_forum` int(10) unsigned NOT NULL default '0',
  `in_topic` int(10) unsigned NOT NULL default '0',
  `session_user_id` int(11) unsigned NOT NULL default '0',
  `session_time` int(8) unsigned default NULL,
  `session_ip` varchar(15) default NULL,
  `session_browser` varchar(64) default NULL,
  `session_user` varchar(50) default NULL,
  `in_module` smallint(3) unsigned default NULL,
  `os` smallint(3) unsigned default NULL,
  `location` varchar(40) default NULL,
  `user_agent` varchar(255) default NULL,
  `session_type` smallint(1) unsigned default NULL,
  `referer` varchar(255) default NULL,
  `type` smallint(1) unsigned default NULL,
  KEY `time_sort` (`session_time`)
) TYPE=HEAP;

-- 
-- Zrzut danych tabeli `opb_session`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_topics`
-- 

CREATE TABLE `opb_topics` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `poll` int(10) unsigned NOT NULL default '0',
  `forum_id` int(10) unsigned NOT NULL default '0',
  `author_id` int(11) unsigned NOT NULL default '0',
  `last_poster_id` int(11) unsigned NOT NULL default '0',
  `last_post_id` int(10) unsigned NOT NULL default '0',
  `first_post_id` int(8) NOT NULL default '0',
  `title` varchar(64) default NULL,
  `description` varchar(64) default NULL,
  `imp_status` tinyint(2) unsigned default NULL,
  `moved_to` int(10) unsigned default NULL,
  `posts` int(10) unsigned default NULL,
  `views` int(10) unsigned default NULL,
  `starter_name` varchar(32) default NULL,
  `last_poster_name` varchar(32) default NULL,
  PRIMARY KEY  (`id`),
  KEY `first_post_id` (`first_post_id`)
) TYPE=MyISAM AUTO_INCREMENT=5 ;

-- 
-- Zrzut danych tabeli `opb_topics`
-- 

INSERT INTO `opb_topics` VALUES (1, 0, 4, 0, 0, 3, 1, 'Temat próbny', 'wajha', NULL, NULL, NULL, NULL, 'TheZyxist', 'TheZyxist');
INSERT INTO `opb_topics` VALUES (2, 0, 5, 0, 0, 4, 4, 'Rozporek', 'modrzew', NULL, NULL, NULL, NULL, 'Zyx', 'Zyx');
INSERT INTO `opb_topics` VALUES (3, 0, 4, 0, 0, 5, 5, 'Maglownica', 'sdfsdf', NULL, NULL, NULL, NULL, 'sdfsdf', 'sdfsdf');
INSERT INTO `opb_topics` VALUES (4, 0, 4, 0, 0, 6, 6, 'Suszarka', 'sdfsdfsdf', NULL, NULL, NULL, NULL, 'gfhfgh', 'gfhfgh');

-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_topics_read`
-- 

CREATE TABLE `opb_topics_read` (
  `uid` int(11) unsigned NOT NULL default '0',
  `tid` int(10) unsigned NOT NULL default '0',
  `date` int(8) unsigned default NULL,
  KEY `uid` (`uid`,`tid`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `opb_topics_read`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_user_param_names`
-- 

CREATE TABLE `opb_user_param_names` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `list_order` mediumint(6) unsigned NOT NULL default '0',
  `name_source` tinyint(1) unsigned default NULL,
  `name_value` varchar(32) default NULL,
  `description_value` varchar(32) default NULL,
  `value_type` smallint(4) unsigned default NULL,
  `default_value` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `order` (`list_order`)
) TYPE=InnoDB AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `opb_user_param_names`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_user_param_values`
-- 

CREATE TABLE `opb_user_param_values` (
  `param_id` mediumint(6) unsigned NOT NULL default '0',
  `user_id` int(11) unsigned NOT NULL default '0',
  `value` varchar(255) default NULL,
  KEY `param_id` (`param_id`,`user_id`)
) TYPE=InnoDB;

-- 
-- Zrzut danych tabeli `opb_user_param_values`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_user_profile`
-- 

CREATE TABLE `opb_user_profile` (
  `uid` int(10) unsigned NOT NULL auto_increment,
  `last_modification` int(10) unsigned default NULL,
  `avatar_location` varchar(255) default NULL,
  `avatar_type` tinyint(4) unsigned default NULL,
  `signature` text,
  `notes` text,
  `about` text,
  `messengers` text,
  `website` varchar(255) default NULL,
  `location` varchar(255) default NULL,
  `hobbys` varchar(255) default NULL,
  PRIMARY KEY  (`uid`)
) TYPE=InnoDB AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `opb_user_profile`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_users`
-- 

CREATE TABLE `opb_users` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `user_profile_uid` int(10) unsigned NOT NULL default '0',
  `nick` varchar(10) default NULL,
  `pass` varchar(40) default NULL,
  `email` varchar(64) NOT NULL default '',
  `joined` int(10) unsigned default NULL,
  `title` varchar(32) default NULL,
  `posts` int(10) unsigned default NULL,
  `language` mediumint(8) unsigned default NULL,
  `skin` mediumint(8) unsigned default NULL,
  `date_format` varchar(12) default NULL,
  `last_visit` int(10) unsigned default NULL,
  `last_activity` int(10) unsigned default NULL,
  `login_key` varchar(40) default NULL,
  `messengers` text,
  `new_pass` varchar(40) default NULL,
  `active` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `posts` (`posts`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Zrzut danych tabeli `opb_users`
-- 


-- --------------------------------------------------------

-- 
-- Struktura tabeli dla  `opb_users_to_groups`
-- 

CREATE TABLE `opb_users_to_groups` (
  `gid` int(10) unsigned NOT NULL default '0',
  `uid` int(11) unsigned NOT NULL default '0',
  `priority` tinyint(1) NOT NULL default '0',
  KEY `gid` (`gid`,`uid`)
) TYPE=MyISAM;

-- 
-- Zrzut danych tabeli `opb_users_to_groups`
-- 
