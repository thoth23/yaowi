SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `yaowi`
--
CREATE DATABASE `yaowi` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `yaowi`;

-- --------------------------------------------------------

--
-- Table structure for table `ya_authcodes`
--

CREATE TABLE IF NOT EXISTS `ya_authcodes` (
  `uuid` varchar(36) NOT NULL,
  `authcode` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Table structure for table `ya_users`
--

CREATE TABLE IF NOT EXISTS `ya_users` (
  `uuid` varchar(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `real_firstname` varchar(255) NOT NULL,
  `real_lastname` varchar(255) NOT NULL,
  `user_dob` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `admin` int(1) NOT NULL default '0',
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `userip` varchar(16) NOT NULL,
  `active` tinyint(4) NOT NULL default '0',
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Yaowi user table';


--
-- Table structure for table `ya_wiki_archive`
--

CREATE TABLE IF NOT EXISTS `ya_wiki_archive` (
  `page_id` bigint(20) NOT NULL auto_increment,
  `page_path` varchar(255) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `page_text` longtext NOT NULL,
  `page_counter` bigint(20) NOT NULL default '0',
  `page_touched` int(11) NOT NULL,
  `page_is_redirect` tinyint(4) NOT NULL default '0',
  `page_is_protected` tinyint(4) NOT NULL default '0',
  `page_last_edited` int(11) NOT NULL,
  `page_last_editor` varchar(36) NOT NULL,
  `page_edit_comment` text NOT NULL,
  `page_active` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`page_id`),
  UNIQUE KEY `page_path` (`page_path`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Yaowi Wiki Pages' AUTO_INCREMENT=1 ;


--
-- Table structure for table `ya_wiki_pages`
--

CREATE TABLE IF NOT EXISTS `ya_wiki_pages` (
  `page_id` bigint(20) NOT NULL auto_increment,
  `page_path` varchar(255) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `page_text` longtext NOT NULL,
  `page_counter` bigint(20) NOT NULL default '0',
  `page_touched` int(11) NOT NULL,
  `page_is_redirect` tinyint(4) NOT NULL default '0',
  `page_is_protected` tinyint(4) NOT NULL default '0',
  `page_last_edited` int(11) NOT NULL,
  `page_last_editor` varchar(36) NOT NULL,
  `page_edit_comment` text NOT NULL,
  `page_active` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`page_id`),
  UNIQUE KEY `page_path` (`page_path`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Yaowi Wiki Pages' AUTO_INCREMENT=1 ;



