-- MySQL dump 10.13  Distrib 5.6.10, for solaris11 (x86_64)
--
-- Host: dbhost    Database: andy_starter
-- ------------------------------------------------------
-- Server version	5.6.10-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `apps_branding`
--

DROP TABLE IF EXISTS `apps_branding`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_branding` (
  `brand_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(100) NOT NULL,
  `brand_main_img` varchar(255) DEFAULT NULL,
  `brand_bgcolor` varchar(100) DEFAULT NULL,
  `brand_css_rule` mediumtext,
  `brand_min_height` int(11) DEFAULT NULL,
  `brand_layout_cclass` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`brand_id`),
  KEY `bs_id` (`brand_layout_cclass`),
  KEY `brand_main_img` (`brand_main_img`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_feed`
--

DROP TABLE IF EXISTS `apps_feed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_feed` (
  `tf_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `smap_id` int(10) unsigned NOT NULL,
  `tf_date` datetime NOT NULL,
  `tf_order_num` int(10) unsigned DEFAULT '1',
  PRIMARY KEY (`tf_id`),
  KEY `smap_id` (`smap_id`),
  KEY `tf_order_num` (`tf_order_num`),
  KEY `tf_date` (`tf_date`),
  CONSTRAINT `apps_feed_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_feed_tags`
--

DROP TABLE IF EXISTS `apps_feed_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_feed_tags` (
  `tf_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tf_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `apps_feed_tags_ibfk_1` FOREIGN KEY (`tf_id`) REFERENCES `apps_feed` (`tf_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `apps_feed_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `share_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_feed_translation`
--

DROP TABLE IF EXISTS `apps_feed_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_feed_translation` (
  `tf_id` int(10) unsigned NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `tf_name` varchar(256) NOT NULL,
  `tf_annotation_rtf` text NOT NULL,
  `tf_text_rtf` text NOT NULL,
  PRIMARY KEY (`tf_id`,`lang_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `apps_feed_translation_ibfk_1` FOREIGN KEY (`tf_id`) REFERENCES `apps_feed` (`tf_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `apps_feed_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_feed_uploads`
--

DROP TABLE IF EXISTS `apps_feed_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_feed_uploads` (
  `tf_id` int(10) unsigned NOT NULL,
  `upl_id` int(10) unsigned NOT NULL,
  `upl_order_num` int(10) unsigned DEFAULT '1',
  PRIMARY KEY (`tf_id`,`upl_id`),
  KEY `upl_order_num` (`upl_order_num`),
  KEY `upl_id` (`upl_id`),
  CONSTRAINT `apps_feed_uploads_ibfk_1` FOREIGN KEY (`tf_id`) REFERENCES `apps_feed` (`tf_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `apps_feed_uploads_ibfk_2` FOREIGN KEY (`upl_id`) REFERENCES `share_uploads` (`upl_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_feedback`
--

DROP TABLE IF EXISTS `apps_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_feedback` (
  `feed_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feed_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rcp_id` int(10) unsigned NOT NULL DEFAULT '0',
  `feed_email` varchar(200) NOT NULL DEFAULT '',
  `feed_phone` varchar(10) DEFAULT NULL,
  `feed_author` varchar(250) NOT NULL,
  `feed_theme` varchar(250) NOT NULL,
  `feed_text` text NOT NULL,
  PRIMARY KEY (`feed_id`),
  KEY `rcp_id` (`rcp_id`),
  CONSTRAINT `apps_feedback_ibfk_1` FOREIGN KEY (`rcp_id`) REFERENCES `apps_feedback_recipient` (`rcp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_feedback_recipient`
--

DROP TABLE IF EXISTS `apps_feedback_recipient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_feedback_recipient` (
  `rcp_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rcp_recipients` varchar(300) NOT NULL,
  `rcp_order_num` int(10) unsigned DEFAULT '1',
  PRIMARY KEY (`rcp_id`),
  KEY `rcp_order_num` (`rcp_order_num`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_feedback_recipient_translation`
--

DROP TABLE IF EXISTS `apps_feedback_recipient_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_feedback_recipient_translation` (
  `rcp_id` int(10) unsigned NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `rcp_name` varchar(255) NOT NULL,
  PRIMARY KEY (`rcp_id`,`lang_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `apps_feedback_recipient_translation_ibfk_1` FOREIGN KEY (`rcp_id`) REFERENCES `apps_feedback_recipient` (`rcp_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `apps_feedback_recipient_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_news`
--

DROP TABLE IF EXISTS `apps_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_news` (
  `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `smap_id` int(10) unsigned NOT NULL,
  `news_is_active` tinyint(1) NOT NULL DEFAULT '1',
  `news_is_top` tinyint(1) NOT NULL,
  `news_date` datetime NOT NULL,
  `news_segment` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `news_show_image` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`news_id`),
  KEY `smap_id` (`smap_id`),
  KEY `news_segment` (`news_segment`),
  KEY `news_is_active` (`news_is_active`),
  KEY `news_show_image` (`news_show_image`),
  KEY `news_is_top` (`news_is_top`),
  CONSTRAINT `apps_news_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_news_tags`
--

DROP TABLE IF EXISTS `apps_news_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_news_tags` (
  `news_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`news_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `apps_news_tags_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `apps_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `apps_news_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `share_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_news_translation`
--

DROP TABLE IF EXISTS `apps_news_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_news_translation` (
  `news_id` int(10) unsigned NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `news_title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `news_announce_rtf` text COLLATE utf8_unicode_ci,
  `news_text_rtf` mediumtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`news_id`,`lang_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `apps_news_translation_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `apps_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `apps_news_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_news_uploads`
--

DROP TABLE IF EXISTS `apps_news_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_news_uploads` (
  `news_id` int(10) unsigned NOT NULL,
  `upl_id` int(10) unsigned NOT NULL,
  `upl_order_num` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`news_id`,`upl_id`,`upl_order_num`),
  KEY `upl_id` (`upl_id`),
  CONSTRAINT `apps_news_uploads_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `apps_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `apps_news_uploads_ibfk_2` FOREIGN KEY (`upl_id`) REFERENCES `share_uploads` (`upl_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_vote`
--

DROP TABLE IF EXISTS `apps_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_vote` (
  `vote_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vote_date` datetime NOT NULL,
  `vote_is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`vote_id`),
  KEY `vote_is_active` (`vote_is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_vote_question`
--

DROP TABLE IF EXISTS `apps_vote_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_vote_question` (
  `vote_question_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vote_id` int(10) unsigned DEFAULT NULL,
  `vote_question_counter` int(10) unsigned DEFAULT '0',
  `vote_question_order_num` int(10) unsigned DEFAULT '1',
  PRIMARY KEY (`vote_question_id`),
  KEY `vote_question_order_num` (`vote_question_order_num`),
  KEY `vote_question_counter` (`vote_question_counter`),
  KEY `vote_id` (`vote_id`),
  CONSTRAINT `apps_vote_question_ibfk_1` FOREIGN KEY (`vote_id`) REFERENCES `apps_vote` (`vote_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_vote_question_translation`
--

DROP TABLE IF EXISTS `apps_vote_question_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_vote_question_translation` (
  `vote_question_id` int(10) unsigned NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `vote_question_title` varchar(250) NOT NULL,
  PRIMARY KEY (`vote_question_id`,`lang_id`),
  KEY `apps_vote_question_translation_ibfk_1` (`lang_id`),
  CONSTRAINT `apps_vote_question_translation_ibfk_1` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `apps_vote_question_translation_ibfk_2` FOREIGN KEY (`vote_question_id`) REFERENCES `apps_vote_question` (`vote_question_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `apps_vote_translation`
--

DROP TABLE IF EXISTS `apps_vote_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apps_vote_translation` (
  `vote_id` int(10) unsigned NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `vote_name` varchar(250) NOT NULL,
  PRIMARY KEY (`vote_id`,`lang_id`),
  KEY `apps_vote_translation_ibfk_2` (`lang_id`),
  CONSTRAINT `apps_vote_translation_ibfk_1` FOREIGN KEY (`vote_id`) REFERENCES `apps_vote` (`vote_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `apps_vote_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form_5`
--

DROP TABLE IF EXISTS `form_5`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_5` (
  `pk_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `form_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `form_5_field_2` int(11) unsigned NOT NULL,
  PRIMARY KEY (`pk_id`),
  KEY `form_date` (`form_date`),
  KEY `form_5_field_2` (`form_5_field_2`),
  CONSTRAINT `form_5_ibfk_1` FOREIGN KEY (`form_5_field_2`) REFERENCES `form_5_field_2` (`fk_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form_5_field_2`
--

DROP TABLE IF EXISTS `form_5_field_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_5_field_2` (
  `fk_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_order_num` int(10) unsigned DEFAULT '1',
  PRIMARY KEY (`fk_id`),
  KEY `fk_order_num` (`fk_order_num`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form_5_field_2_translation`
--

DROP TABLE IF EXISTS `form_5_field_2_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_5_field_2_translation` (
  `fk_id` int(11) unsigned NOT NULL,
  `lang_id` int(11) unsigned NOT NULL,
  `fk_name` varchar(255) NOT NULL,
  PRIMARY KEY (`fk_id`,`lang_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `form_5_field_2_translation_ibfk_1` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `form_5_field_2_translation_ibfk_2` FOREIGN KEY (`fk_id`) REFERENCES `form_5_field_2` (`fk_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `frm_forms`
--

DROP TABLE IF EXISTS `frm_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frm_forms` (
  `form_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `form_creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `form_is_active` tinyint(1) NOT NULL DEFAULT '1',
  `form_email_adresses` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`form_id`),
  KEY `form_creation_date` (`form_creation_date`),
  KEY `form_is_active` (`form_is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `frm_forms_translation`
--

DROP TABLE IF EXISTS `frm_forms_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `frm_forms_translation` (
  `form_id` int(10) unsigned NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `form_name` varchar(250) NOT NULL,
  `form_annotation_rtf` text NOT NULL,
  `form_post_annotation_rtf` text,
  PRIMARY KEY (`form_id`,`lang_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `frm_forms_translation_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `frm_forms` (`form_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `frm_forms_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_access_level`
--

DROP TABLE IF EXISTS `share_access_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_access_level` (
  `smap_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `right_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`smap_id`,`group_id`,`right_id`),
  KEY `group_id` (`group_id`),
  KEY `right_id` (`right_id`),
  CONSTRAINT `share_access_level_ibfk_4` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `share_access_level_ibfk_5` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `share_access_level_ibfk_6` FOREIGN KEY (`right_id`) REFERENCES `user_group_rights` (`right_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_domain2site`
--

DROP TABLE IF EXISTS `share_domain2site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_domain2site` (
  `domain_id` int(10) unsigned NOT NULL,
  `site_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`domain_id`,`site_id`),
  KEY `site_id` (`site_id`),
  CONSTRAINT `share_domain2site_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `share_domains` (`domain_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `share_domain2site_ibfk_2` FOREIGN KEY (`site_id`) REFERENCES `share_sites` (`site_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_domains`
--

DROP TABLE IF EXISTS `share_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_domains` (
  `domain_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `domain_protocol` char(5) NOT NULL DEFAULT 'http',
  `domain_port` mediumint(8) unsigned NOT NULL DEFAULT '80',
  `domain_host` varchar(255) NOT NULL,
  `domain_root` varchar(255) NOT NULL,
  PRIMARY KEY (`domain_id`),
  UNIQUE KEY `domain_protocol` (`domain_protocol`,`domain_host`,`domain_port`,`domain_root`)
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_lang_tags`
--

DROP TABLE IF EXISTS `share_lang_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_lang_tags` (
  `ltag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ltag_name` varchar(70) NOT NULL DEFAULT '',
  PRIMARY KEY (`ltag_id`),
  UNIQUE KEY `ltag_name` (`ltag_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2012 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_lang_tags_translation`
--

DROP TABLE IF EXISTS `share_lang_tags_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_lang_tags_translation` (
  `ltag_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ltag_value_rtf` text NOT NULL,
  PRIMARY KEY (`ltag_id`,`lang_id`),
  KEY `FK_tranaslatelv_language` (`lang_id`),
  CONSTRAINT `FK_Reference_6` FOREIGN KEY (`ltag_id`) REFERENCES `share_lang_tags` (`ltag_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_tranaslatelv_language` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_languages`
--

DROP TABLE IF EXISTS `share_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_languages` (
  `lang_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lang_locale` char(30) NOT NULL,
  `lang_abbr` char(2) NOT NULL,
  `lang_name` char(20) NOT NULL,
  `lang_default` tinyint(1) NOT NULL DEFAULT '0',
  `lang_order_num` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`lang_id`),
  KEY `idx_abbr` (`lang_abbr`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_session`
--

DROP TABLE IF EXISTS `share_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_session` (
  `session_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_native_id` char(40) NOT NULL,
  `session_last_impression` int(11) NOT NULL,
  `session_created` int(11) NOT NULL,
  `session_expires` int(11) NOT NULL,
  `session_ip` int(11) unsigned DEFAULT NULL,
  `session_user_agent` char(255) DEFAULT NULL,
  `u_id` int(10) unsigned DEFAULT NULL,
  `session_data` varchar(5000) DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `session_native_id` (`session_native_id`),
  KEY `i_session_u_id` (`u_id`),
  KEY `i_session_ip` (`session_ip`),
  KEY `session_expires` (`session_expires`)
) ENGINE=MEMORY AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_sitemap`
--

DROP TABLE IF EXISTS `share_sitemap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_sitemap` (
  `smap_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned NOT NULL,
  `brand_id` int(11) unsigned DEFAULT NULL,
  `smap_layout` char(200) NOT NULL,
  `smap_layout_xml` text,
  `smap_content` char(200) NOT NULL,
  `smap_content_xml` text,
  `smap_pid` int(10) unsigned DEFAULT NULL,
  `smap_segment` char(50) NOT NULL DEFAULT '',
  `smap_order_num` int(10) unsigned DEFAULT '1',
  `smap_redirect_url` char(250) DEFAULT NULL,
  `smap_meta_robots` text,
  PRIMARY KEY (`smap_id`),
  UNIQUE KEY `smap_pid` (`smap_pid`,`site_id`,`smap_segment`),
  KEY `site_id` (`site_id`),
  KEY `smap_order_num` (`smap_order_num`),
  KEY `brand_id` (`brand_id`),
  CONSTRAINT `share_sitemap_ibfk_11` FOREIGN KEY (`brand_id`) REFERENCES `apps_branding` (`brand_id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `share_sitemap_ibfk_8` FOREIGN KEY (`smap_pid`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `share_sitemap_ibfk_9` FOREIGN KEY (`site_id`) REFERENCES `share_sites` (`site_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3641 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_sitemap_tags`
--

DROP TABLE IF EXISTS `share_sitemap_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_sitemap_tags` (
  `smap_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`smap_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `share_sitemap_tags_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `share_sitemap_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `share_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_sitemap_translation`
--

DROP TABLE IF EXISTS `share_sitemap_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_sitemap_translation` (
  `smap_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `smap_name` varchar(200) DEFAULT NULL,
  `smap_description_rtf` text,
  `smap_html_title` varchar(250) DEFAULT NULL,
  `smap_meta_keywords` text,
  `smap_meta_description` text,
  `smap_is_disabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lang_id`,`smap_id`),
  KEY `sitemaplv_sitemap_FK` (`smap_id`),
  CONSTRAINT `share_sitemap_translation_ibfk_1` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `share_sitemap_translation_ibfk_2` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_sitemap_uploads`
--

DROP TABLE IF EXISTS `share_sitemap_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_sitemap_uploads` (
  `smap_id` int(10) unsigned NOT NULL,
  `upl_id` int(10) unsigned NOT NULL,
  `upl_order_num` int(10) unsigned NOT NULL,
  PRIMARY KEY (`smap_id`,`upl_id`,`upl_order_num`),
  KEY `upl_id` (`upl_id`),
  CONSTRAINT `share_sitemap_uploads_ibfk_3` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `share_sitemap_uploads_ibfk_4` FOREIGN KEY (`upl_id`) REFERENCES `share_uploads` (`upl_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_sites`
--

DROP TABLE IF EXISTS `share_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_sites` (
  `site_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site_is_active` tinyint(1) NOT NULL DEFAULT '1',
  `site_is_indexed` tinyint(1) NOT NULL DEFAULT '1',
  `site_is_default` tinyint(1) NOT NULL DEFAULT '0',
  `site_folder` char(20) NOT NULL DEFAULT 'default',
  `site_order_num` int(10) unsigned DEFAULT '1',
  `site_meta_robots` text,
  `site_ga_code` text,
  PRIMARY KEY (`site_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_sites_tags`
--

DROP TABLE IF EXISTS `share_sites_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_sites_tags` (
  `site_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`site_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `share_sites_tags_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `share_sites` (`site_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `share_sites_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `share_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_sites_translation`
--

DROP TABLE IF EXISTS `share_sites_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_sites_translation` (
  `site_id` int(11) unsigned NOT NULL,
  `lang_id` int(11) unsigned NOT NULL,
  `site_name` char(200) NOT NULL,
  `site_meta_keywords` text,
  `site_meta_description` text,
  PRIMARY KEY (`site_id`,`lang_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `share_sites_translation_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `share_sites` (`site_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `share_sites_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_tags`
--

DROP TABLE IF EXISTS `share_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_tags` (
  `tag_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag_name` char(100) NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag_name` (`tag_name`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_textblocks`
--

DROP TABLE IF EXISTS `share_textblocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_textblocks` (
  `tb_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `smap_id` int(10) unsigned DEFAULT NULL,
  `tb_num` char(50) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tb_id`),
  UNIQUE KEY `smap_id` (`smap_id`,`tb_num`),
  CONSTRAINT `share_textblocks_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_textblocks_translation`
--

DROP TABLE IF EXISTS `share_textblocks_translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_textblocks_translation` (
  `tb_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tb_content` text NOT NULL,
  UNIQUE KEY `tb_id` (`tb_id`,`lang_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `share_textblocks_translation_ibfk_1` FOREIGN KEY (`tb_id`) REFERENCES `share_textblocks` (`tb_id`) ON DELETE CASCADE,
  CONSTRAINT `share_textblocks_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_uploads`
--

DROP TABLE IF EXISTS `share_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_uploads` (
  `upl_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `upl_pid` int(10) unsigned DEFAULT NULL COMMENT 'родительский идентификатор',
  `upl_childs_count` int(10) unsigned DEFAULT NULL COMMENT 'количество наследников, 0 - могут быть[но сейчас нет, пустая папка ], NULL - не может быть вообще',
  `upl_path` varchar(250) NOT NULL COMMENT 'уникальный полный путь к файлу',
  `upl_filename` varchar(255) NOT NULL COMMENT 'реальное имя файла',
  `upl_name` varchar(250) NOT NULL COMMENT 'имя файла с расширением, с этим именем файл отдается при скачивании',
  `upl_title` varchar(250) NOT NULL DEFAULT '' COMMENT 'то как файл выводится в репозитории и в alt-ах',
  `upl_description` text,
  `upl_publication_date` date DEFAULT NULL,
  `upl_data` text,
  `upl_views` bigint(20) NOT NULL DEFAULT '0',
  `upl_internal_type` char(20) DEFAULT NULL,
  `upl_mime_type` char(50) DEFAULT NULL,
  `upl_width` int(10) unsigned DEFAULT NULL,
  `upl_height` int(10) unsigned DEFAULT NULL,
  `upl_is_ready` tinyint(1) DEFAULT '1',
  `upl_duration` time DEFAULT NULL,
  `upl_is_active` int(10) unsigned DEFAULT '1',
  PRIMARY KEY (`upl_id`),
  UNIQUE KEY `upl_path` (`upl_path`),
  KEY `upl_views` (`upl_views`),
  KEY `upl_is_ready` (`upl_is_ready`),
  KEY `upl_publication_date_index` (`upl_publication_date`),
  KEY `abc` (`upl_id`,`upl_is_ready`,`upl_views`),
  KEY `upl_pid` (`upl_pid`),
  KEY `upl_childs_count` (`upl_childs_count`),
  KEY `upl_filename` (`upl_filename`),
  KEY `upl_is_active` (`upl_is_active`),
  CONSTRAINT `share_uploads_ibfk_1` FOREIGN KEY (`upl_pid`) REFERENCES `share_uploads` (`upl_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_uploads_tags`
--

DROP TABLE IF EXISTS `share_uploads_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_uploads_tags` (
  `upl_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`upl_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `share_uploads_tags_ibfk_1` FOREIGN KEY (`upl_id`) REFERENCES `share_uploads` (`upl_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `share_uploads_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `share_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `share_widgets`
--

DROP TABLE IF EXISTS `share_widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_widgets` (
  `widget_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `widget_name` varchar(250) NOT NULL,
  `widget_xml` text NOT NULL,
  `widget_icon_img` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`widget_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `test_feed`
--

DROP TABLE IF EXISTS `test_feed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_feed` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_ban_ips`
--

DROP TABLE IF EXISTS `user_ban_ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_ban_ips` (
  `ban_ip_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ban_ip` int(4) unsigned NOT NULL,
  `ban_ip_end_date` date NOT NULL,
  PRIMARY KEY (`ban_ip_id`),
  UNIQUE KEY `i_ban_ip_uniq` (`ban_ip`),
  KEY `i_ban_ip_end_date` (`ban_ip_end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_fb_storage`
--

DROP TABLE IF EXISTS `user_fb_storage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_fb_storage` (
  `var_name` char(50) NOT NULL,
  `var_value` varchar(255) NOT NULL,
  PRIMARY KEY (`var_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_group_rights`
--

DROP TABLE IF EXISTS `user_group_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group_rights` (
  `right_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `right_name` char(20) NOT NULL DEFAULT '',
  `right_const` char(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`right_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` char(50) NOT NULL DEFAULT '',
  `group_default` tinyint(1) NOT NULL DEFAULT '0',
  `group_user_default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`),
  KEY `group_default` (`group_default`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_user_groups`
--

DROP TABLE IF EXISTS `user_user_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_user_groups` (
  `u_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`u_id`,`group_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `user_user_groups_ibfk_3` FOREIGN KEY (`u_id`) REFERENCES `user_users` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_user_groups_ibfk_4` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_users`
--

DROP TABLE IF EXISTS `user_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_users` (
  `u_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `u_fbid` char(25) DEFAULT NULL,
  `u_vkid` char(25) DEFAULT NULL,
  `u_name` varchar(50) NOT NULL DEFAULT '',
  `u_phone` varchar(100) DEFAULT NULL,
  `u_password` varchar(40) NOT NULL DEFAULT '',
  `u_is_active` tinyint(1) NOT NULL DEFAULT '1',
  `u_fullname` varchar(250) NOT NULL,
  `u_country` varchar(255) DEFAULT NULL,
  `u_city` varchar(255) DEFAULT NULL,
  `u_company` varchar(255) DEFAULT NULL,
  `u_position` varchar(255) DEFAULT NULL,
  `u_nick` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`u_id`),
  UNIQUE KEY `u_login` (`u_name`),
  UNIQUE KEY `u_fbid` (`u_fbid`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_users_ban`
--

DROP TABLE IF EXISTS `user_users_ban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_users_ban` (
  `u_id` int(10) unsigned NOT NULL,
  `ban_date` date NOT NULL,
  KEY `u_id` (`u_id`),
  KEY `ban_date` (`ban_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-04-19 18:10:53
