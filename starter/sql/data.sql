-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 14, 2011 at 03:12 PM
-- Server version: 5.1.46
-- PHP Version: 5.3.5

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `energine-2.6`
--

-- --------------------------------------------------------

--
-- Table structure for table `apps_feedback`
--

DROP TABLE IF EXISTS `apps_feedback`;
CREATE TABLE IF NOT EXISTS `apps_feedback` (
  `feed_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feed_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `feed_email` varchar(200) NOT NULL DEFAULT '',
  `feed_author` varchar(250) DEFAULT NULL,
  `feed_text` text NOT NULL,
  PRIMARY KEY (`feed_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `apps_feedback`
--


-- --------------------------------------------------------

--
-- Table structure for table `apps_news`
--

DROP TABLE IF EXISTS `apps_news`;
CREATE TABLE IF NOT EXISTS `apps_news` (
  `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `smap_id` int(10) unsigned NOT NULL,
  `news_date` datetime NOT NULL,
  `news_segment` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`news_id`),
  KEY `smap_id` (`smap_id`),
  KEY `news_segment` (`news_segment`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `apps_news`
--


-- --------------------------------------------------------

--
-- Table structure for table `apps_news_translation`
--

DROP TABLE IF EXISTS `apps_news_translation`;
CREATE TABLE IF NOT EXISTS `apps_news_translation` (
  `news_id` int(10) unsigned NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `news_title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `news_announce_rtf` text COLLATE utf8_unicode_ci,
  `news_text_rtf` mediumtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`news_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `apps_news_translation`
--


-- --------------------------------------------------------

--
-- Table structure for table `apps_news_uploads`
--

DROP TABLE IF EXISTS `apps_news_uploads`;
CREATE TABLE IF NOT EXISTS `apps_news_uploads` (
  `news_id` int(10) unsigned NOT NULL,
  `upl_id` int(10) unsigned NOT NULL,
  `upl_order_num` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`news_id`,`upl_id`,`upl_order_num`),
  KEY `upl_id` (`upl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `apps_news_uploads`
--


-- --------------------------------------------------------

--
-- Table structure for table `image_photo_gallery`
--

DROP TABLE IF EXISTS `image_photo_gallery`;
CREATE TABLE IF NOT EXISTS `image_photo_gallery` (
  `pg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `smap_id` int(10) unsigned NOT NULL DEFAULT '0',
  `pg_thumb_img` varchar(250) NOT NULL DEFAULT '',
  `pg_photo_img` varchar(200) NOT NULL DEFAULT '',
  `pg_order_num` int(10) unsigned DEFAULT '1',
  PRIMARY KEY (`pg_id`),
  KEY `smap_id` (`smap_id`),
  KEY `pg_photo_img` (`pg_photo_img`),
  KEY `pg_order_num` (`pg_order_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `image_photo_gallery`
--


-- --------------------------------------------------------

--
-- Table structure for table `image_photo_gallery_translation`
--

DROP TABLE IF EXISTS `image_photo_gallery_translation`;
CREATE TABLE IF NOT EXISTS `image_photo_gallery_translation` (
  `pg_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(11) unsigned NOT NULL DEFAULT '0',
  `pg_title` varchar(250) DEFAULT NULL,
  `pg_text` text,
  PRIMARY KEY (`pg_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `image_photo_gallery_translation`
--


-- --------------------------------------------------------

--
-- Table structure for table `share_access_level`
--

DROP TABLE IF EXISTS `share_access_level`;
CREATE TABLE IF NOT EXISTS `share_access_level` (
  `smap_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `right_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`smap_id`,`group_id`,`right_id`),
  KEY `group_id` (`group_id`),
  KEY `right_id` (`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `share_access_level`
--

INSERT INTO `share_access_level` (`smap_id`, `group_id`, `right_id`) VALUES
(80, 1, 3),
(329, 1, 3),
(330, 1, 3),
(331, 1, 3),
(351, 1, 1),
(483, 1, 3),
(1254, 1, 3),
(1255, 1, 3),
(1256, 1, 3),
(80, 3, 1),
(329, 3, 1),
(330, 3, 1),
(331, 3, 1),
(351, 3, 1),
(1254, 3, 1),
(1255, 3, 1),
(1256, 3, 1),
(80, 4, 1),
(329, 4, 1),
(330, 4, 1),
(331, 4, 2),
(351, 4, 1),
(483, 4, 1),
(1254, 4, 1),
(1255, 4, 1),
(1256, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `share_languages`
--

DROP TABLE IF EXISTS `share_languages`;
CREATE TABLE IF NOT EXISTS `share_languages` (
  `lang_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lang_locale` char(30) NOT NULL,
  `lang_abbr` char(2) NOT NULL,
  `lang_name` char(20) NOT NULL,
  `lang_default` tinyint(1) NOT NULL DEFAULT '0',
  `lang_order_num` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`lang_id`),
  KEY `idx_abbr` (`lang_abbr`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `share_languages`
--

INSERT INTO `share_languages` (`lang_id`, `lang_locale`, `lang_abbr`, `lang_name`, `lang_default`, `lang_order_num`) VALUES
(1, 'ru_UA.UTF8', 'ru', 'Русский', 1, 1),
(2, 'uk_UA.UTF8', 'ua', 'Українська', 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `share_lang_tags`
--

DROP TABLE IF EXISTS `share_lang_tags`;
CREATE TABLE IF NOT EXISTS `share_lang_tags` (
  `ltag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ltag_name` varchar(70) NOT NULL DEFAULT '',
  PRIMARY KEY (`ltag_id`),
  UNIQUE KEY `ltag_name` (`ltag_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1134 ;

--
-- Dumping data for table `share_lang_tags`
--

INSERT INTO `share_lang_tags` (`ltag_id`, `ltag_name`) VALUES
(493, 'BTN_ACTIVATE'),
(43, 'BTN_ADD'),
(313, 'BTN_ADD_DIR'),
(446, 'BTN_ADD_FILE'),
(868, 'BTN_ADD_GALLERY'),
(742, 'BTN_ADD_GROUP'),
(426, 'BTN_ADD_NEWS'),
(57, 'BTN_ADD_PAGE'),
(495, 'BTN_ADD_PHOTO'),
(743, 'BTN_ADD_SHORTCUT'),
(464, 'BTN_ALIGN_CENTER'),
(465, 'BTN_ALIGN_JUSTIFY'),
(462, 'BTN_ALIGN_LEFT'),
(463, 'BTN_ALIGN_RIGHT'),
(327, 'BTN_APPLY_FILTER'),
(701, 'BTN_APPROVE'),
(457, 'BTN_BOLD'),
(347, 'BTN_CANCEL'),
(55, 'BTN_CHANGE'),
(134, 'BTN_CLOSE'),
(47, 'BTN_DELETE'),
(428, 'BTN_DELETE_NEWS'),
(59, 'BTN_DELETE_PAGE'),
(497, 'BTN_DELETE_PHOTO'),
(448, 'BTN_DEL_FILE'),
(323, 'BTN_DIV_EDITOR'),
(546, 'BTN_DOWN'),
(46, 'BTN_EDIT'),
(56, 'BTN_EDIT_MODE'),
(427, 'BTN_EDIT_NEWS'),
(58, 'BTN_EDIT_PAGE'),
(466, 'BTN_FILE_LIBRARY'),
(357, 'BTN_FILE_REPOSITORY'),
(214, 'BTN_GO'),
(1025, 'BTN_GOTONEWS'),
(459, 'BTN_HREF'),
(169, 'BTN_IMAGELIB'),
(171, 'BTN_IMAGE_MANAGER'),
(168, 'BTN_INSERT'),
(161, 'BTN_INSERT_IMAGE'),
(1121, 'BTN_INSERT_WIDGET'),
(458, 'BTN_ITALIC'),
(969, 'BTN_ITEMS'),
(489, 'BTN_LANG_EDITOR'),
(260, 'BTN_LOAD'),
(639, 'BTN_LOAD_FILE'),
(79, 'BTN_LOGIN'),
(15, 'BTN_LOGOUT'),
(251, 'BTN_MOVE_DOWN'),
(250, 'BTN_MOVE_UP'),
(461, 'BTN_OL'),
(312, 'BTN_OPEN'),
(525, 'BTN_PARAMS_LIST'),
(753, 'BTN_PREVIOUS_WEEK'),
(542, 'BTN_PRINT'),
(54, 'BTN_REGISTER'),
(314, 'BTN_RENAME'),
(1105, 'BTN_RESET_TEMPLATES'),
(45, 'BTN_RETURN_LIST'),
(491, 'BTN_ROLE_EDITOR'),
(42, 'BTN_SAVE'),
(330, 'BTN_SAVE_GO'),
(284, 'BTN_SEARCH'),
(237, 'BTN_SELECT'),
(269, 'BTN_SEND'),
(74, 'BTN_SET_RIGHTS'),
(62, 'BTN_SET_ROLE'),
(324, 'BTN_TMPL_EDITOR'),
(381, 'BTN_TRANS_EDITOR'),
(460, 'BTN_UL'),
(545, 'BTN_UP'),
(346, 'BTN_UPDATE'),
(490, 'BTN_USER_EDITOR'),
(157, 'BTN_VIEW'),
(60, 'BTN_VIEWMODESWITCHER'),
(61, 'BTN_VIEWSOURCE'),
(365, 'BTN_VIEW_PROFILE'),
(641, 'BTN_VOTE'),
(469, 'BTN_ZIP_UPLOAD'),
(865, 'CONTENT_ADMIN_CHILDS'),
(604, 'CONTENT_CHILDS'),
(841, 'CONTENT_FEEDBACK_FORM'),
(842, 'CONTENT_FEEDBACK_LIST'),
(605, 'CONTENT_FILE_REPOSITORY'),
(606, 'CONTENT_GOOGLE_SITEMAP'),
(607, 'CONTENT_LANGUAGES'),
(608, 'CONTENT_LOGIN'),
(609, 'CONTENT_MAP'),
(610, 'CONTENT_NEWS'),
(856, 'CONTENT_NEWS_CATEGORIES_EDITOR'),
(612, 'CONTENT_NEWS_REPOSITORY'),
(611, 'CONTENT_REGISTER'),
(613, 'CONTENT_RESTORE_PASSWORD'),
(614, 'CONTENT_ROLES'),
(618, 'CONTENT_SITES'),
(597, 'CONTENT_SITE_DIV_EDITOR'),
(858, 'CONTENT_TAG_EDITOR'),
(617, 'CONTENT_TEXTBLOCK'),
(619, 'CONTENT_TRANSLATIONS'),
(621, 'CONTENT_USERS'),
(620, 'CONTENT_USER_PROFILE'),
(848, 'CONTENT_VIDEO'),
(422, 'DUMMY_EMAIL'),
(170, 'ERR_403'),
(96, 'ERR_404'),
(376, 'ERR_BAD_LOGIN'),
(468, 'ERR_BAD_URL'),
(65, 'ERR_CANT_DELETE_YOURSELF'),
(383, 'ERR_CANT_MOVE'),
(263, 'ERR_DATABASE_ERROR'),
(339, 'ERR_DEFAULT_GROUP'),
(389, 'ERR_DEV_NO_DATA'),
(303, 'ERR_NOT_UNIQUE_DATA'),
(544, 'ERR_NO_DIV_NAME'),
(295, 'ERR_NO_U_NAME'),
(952, 'ERR_PWD_MISMATCH'),
(796, 'FIELD_ATTACHMENTS'),
(898, 'FIELD_BACK_TOP_RIGHT_IMG'),
(899, 'FIELD_BACK_TOP_RIGHT_INDENT'),
(904, 'FIELD_BACK_WRAPPER1_IMG'),
(905, 'FIELD_BACK_WRAPPER1_PROPS'),
(906, 'FIELD_BACK_WRAPPER2_IMG'),
(907, 'FIELD_BACK_WRAPPER2_PROPS'),
(1023, 'FIELD_BAN_DATE'),
(1014, 'FIELD_BAN_IP'),
(1016, 'FIELD_BAN_IP_END_DATE'),
(569, 'FIELD_BASKET_COUNT'),
(667, 'FIELD_CAST_ANNOTATION_RTF'),
(881, 'FIELD_CAST_IS_MAIN'),
(666, 'FIELD_CAST_NAME'),
(665, 'FIELD_CAST_START_DATE'),
(668, 'FIELD_CAST_TEXT_RTF'),
(774, 'FIELD_CATEGORY_CLOSED'),
(773, 'FIELD_CATEGORY_DESC'),
(775, 'FIELD_CATEGORY_ID'),
(772, 'FIELD_CATEGORY_NAME'),
(738, 'FIELD_CAT_NAME'),
(736, 'FIELD_CAT_SEGMENT'),
(372, 'FIELD_CHANGE_U_PASSWORD'),
(373, 'FIELD_CHANGE_U_PASSWORD2'),
(702, 'FIELD_COMMENT_APPROVED'),
(934, 'FIELD_COMMENT_COUNT'),
(704, 'FIELD_COMMENT_CREATED'),
(781, 'FIELD_COMMENT_ID'),
(705, 'FIELD_COMMENT_NAME'),
(779, 'FIELD_COMMENT_NUM'),
(707, 'FIELD_COMMENT_PARENT_ID'),
(766, 'FIELD_COPY_SITE_STRUCTURE'),
(514, 'FIELD_CURR_ABBR'),
(515, 'FIELD_CURR_FORMAT'),
(559, 'FIELD_CURR_IS_MAIN'),
(512, 'FIELD_CURR_NAME'),
(513, 'FIELD_CURR_RATE'),
(1024, 'FIELD_DELETE_BAN'),
(729, 'FIELD_DOW_0'),
(730, 'FIELD_DOW_1'),
(731, 'FIELD_DOW_2'),
(732, 'FIELD_DOW_3'),
(733, 'FIELD_DOW_4'),
(734, 'FIELD_DOW_5'),
(735, 'FIELD_DOW_6'),
(799, 'FIELD_EPISODE_ANNOTATION_RTF'),
(797, 'FIELD_EPISODE_DATE'),
(798, 'FIELD_EPISODE_NAME'),
(800, 'FIELD_EPISODE_TEXT_RTF'),
(699, 'FIELD_EXEC_ADDITIONAL_TEXT'),
(652, 'FIELD_EXEC_ANNOTATION_RTF'),
(647, 'FIELD_EXEC_NAME'),
(651, 'FIELD_EXEC_POST'),
(653, 'FIELD_EXEC_TEXT_RTF'),
(437, 'FIELD_FEED_AUTHOR'),
(442, 'FIELD_FEED_DATE'),
(438, 'FIELD_FEED_EMAIL'),
(1007, 'FIELD_FEED_PHONE'),
(436, 'FIELD_FEED_TEXT'),
(993, 'FIELD_FEED_TOPIC'),
(1006, 'FIELD_FEED_TYPE'),
(996, 'FIELD_FEED_TYPE_EMAIL'),
(995, 'FIELD_FEED_TYPE_NAME'),
(1077, 'FIELD_GRAVITY'),
(67, 'FIELD_GROUP_DEFAULT'),
(340, 'FIELD_GROUP_DEFAULT_RIGHTS'),
(63, 'FIELD_GROUP_ID'),
(66, 'FIELD_GROUP_NAME'),
(68, 'FIELD_GROUP_USER_DEFAULT'),
(1081, 'FIELD_ID'),
(172, 'FIELD_IMG_ALIGN'),
(167, 'FIELD_IMG_ALTTEXT'),
(159, 'FIELD_IMG_DESCRIPTION'),
(162, 'FIELD_IMG_FILENAME'),
(158, 'FIELD_IMG_FILENAME_IMG'),
(164, 'FIELD_IMG_HEIGHT'),
(165, 'FIELD_IMG_HSPACE'),
(500, 'FIELD_IMG_MARGIN_BOTTOM'),
(501, 'FIELD_IMG_MARGIN_LEFT'),
(502, 'FIELD_IMG_MARGIN_RIGHT'),
(503, 'FIELD_IMG_MARGIN_TOP'),
(160, 'FIELD_IMG_THUMBNAIL_IMG'),
(166, 'FIELD_IMG_VSPACE'),
(163, 'FIELD_IMG_WIDTH'),
(700, 'FIELD_JURY_ADDITIONAL_TEXT'),
(872, 'FIELD_JURY_ADDITIONAL_TITLE'),
(658, 'FIELD_JURY_ANNOTATION_RTF'),
(657, 'FIELD_JURY_NAME'),
(659, 'FIELD_JURY_TEXT_RTF'),
(69, 'FIELD_LANG_ABBR'),
(71, 'FIELD_LANG_DEFAULT'),
(70, 'FIELD_LANG_NAME'),
(73, 'FIELD_LANG_WIN_CODE'),
(1069, 'FIELD_LIMIT'),
(305, 'FIELD_LTAG_DESCRIPTION'),
(49, 'FIELD_LTAG_NAME'),
(14, 'FIELD_LTAG_VALUE'),
(109, 'FIELD_LTAG_VALUE_RTF'),
(793, 'FIELD_MINI_FIRST_BLOCK'),
(928, 'FIELD_MINI_ID'),
(792, 'FIELD_MINI_IS_HIGH'),
(920, 'FIELD_MINI_NAME'),
(791, 'FIELD_MINI_PHOTO_IMG'),
(794, 'FIELD_MINI_SECOND_BLOCK'),
(795, 'FIELD_MINI_THIRD_BLOCK'),
(790, 'FIELD_MINI_URL'),
(925, 'FIELD_MS_FIRST_BLOCK'),
(923, 'FIELD_MS_PHOTO_IMG'),
(926, 'FIELD_MS_SECOND_BLOCK'),
(927, 'FIELD_MS_THIRD_BLOCK'),
(924, 'FIELD_MS_URL'),
(689, 'FIELD_NEWS_ADDITIONAL_TITLE'),
(393, 'FIELD_NEWS_ANNOUNCE_RTF'),
(737, 'FIELD_NEWS_CATEGORIES'),
(392, 'FIELD_NEWS_DATE'),
(660, 'FIELD_NEWS_IS_DISABLED'),
(882, 'FIELD_NEWS_IS_HOT'),
(875, 'FIELD_NEWS_MAIN'),
(877, 'FIELD_NEWS_PROJ_MAIN'),
(874, 'FIELD_NEWS_PROJ_TOP'),
(686, 'FIELD_NEWS_PUBLISH_DATE'),
(625, 'FIELD_NEWS_SOURCE'),
(394, 'FIELD_NEWS_TEXT_RTF'),
(391, 'FIELD_NEWS_TITLE'),
(876, 'FIELD_NEWS_TOP'),
(367, 'FIELD_NEW_PASSWORD'),
(935, 'FIELD_NICK'),
(1053, 'FIELD_ORDER'),
(584, 'FIELD_ORDER_COMMENT'),
(581, 'FIELD_ORDER_CREATED'),
(585, 'FIELD_ORDER_DELIVERY_COMMENT'),
(583, 'FIELD_OS_ID'),
(519, 'FIELD_OS_NAME'),
(820, 'FIELD_PART_AGE'),
(821, 'FIELD_PART_ANNOTATION_RTF'),
(819, 'FIELD_PART_CITY'),
(824, 'FIELD_PART_FNAME'),
(823, 'FIELD_PART_INFO_RTF'),
(817, 'FIELD_PART_IS_DISABLED'),
(818, 'FIELD_PART_NAME'),
(822, 'FIELD_PART_TEXT_RTF'),
(867, 'FIELD_PG_DATE'),
(485, 'FIELD_PG_PHOTO_IMG'),
(483, 'FIELD_PG_TEXT'),
(498, 'FIELD_PG_THUMB_IMG'),
(484, 'FIELD_PG_TITLE'),
(1034, 'FIELD_PG_TOTAL_PHOTOS'),
(547, 'FIELD_PPV_VALUE'),
(526, 'FIELD_PP_NAME'),
(527, 'FIELD_PP_TYPE'),
(537, 'FIELD_PRODUCER_ID'),
(521, 'FIELD_PRODUCER_NAME'),
(522, 'FIELD_PRODUCER_SEGMENT'),
(534, 'FIELD_PRODUCT_CODE'),
(536, 'FIELD_PRODUCT_COUNT'),
(541, 'FIELD_PRODUCT_DESCRIPTION_RTF'),
(532, 'FIELD_PRODUCT_NAME'),
(535, 'FIELD_PRODUCT_PRICE'),
(533, 'FIELD_PRODUCT_SEGMENT'),
(540, 'FIELD_PRODUCT_SHORT_DESCRIPTION_RTF'),
(568, 'FIELD_PRODUCT_SUMM'),
(531, 'FIELD_PS_ID'),
(517, 'FIELD_PS_IS_DEFAULT'),
(516, 'FIELD_PS_NAME'),
(539, 'FIELD_PT_ID'),
(524, 'FIELD_PT_NAME'),
(1061, 'FIELD_RECORDSPERTAB'),
(265, 'FIELD_REMEMBER_LOGIN'),
(518, 'FIELD_RIGHT_ID'),
(745, 'FIELD_SHORTCUTS'),
(748, 'FIELD_SHORTCUT_NAME'),
(749, 'FIELD_SHORTCUT_URL'),
(750, 'FIELD_SHORTGROUP_ID'),
(744, 'FIELD_SHORTGROUP_NAME'),
(1049, 'FIELD_SITE'),
(592, 'FIELD_SITE_FOLDER'),
(588, 'FIELD_SITE_HOST'),
(602, 'FIELD_SITE_ID'),
(728, 'FIELD_SITE_IS_ACTIVE'),
(591, 'FIELD_SITE_IS_DEFAULT'),
(594, 'FIELD_SITE_META_DESCRIPTION'),
(593, 'FIELD_SITE_META_KEYWORDS'),
(590, 'FIELD_SITE_NAME'),
(765, 'FIELD_SITE_PORT'),
(587, 'FIELD_SITE_PROTOCOL'),
(589, 'FIELD_SITE_ROOT'),
(916, 'FIELD_SMAP_ADOCEAN_300_250'),
(917, 'FIELD_SMAP_ADOCEAN_728_90'),
(918, 'FIELD_SMAP_ADOCEAN_RICHMEDIA'),
(1009, 'FIELD_SMAP_ADOCEAN_VIDEO'),
(595, 'FIELD_SMAP_CONTENT'),
(127, 'FIELD_SMAP_DEFAULT'),
(130, 'FIELD_SMAP_DESCRIPTION_RTF'),
(384, 'FIELD_SMAP_HTML_TITLE'),
(236, 'FIELD_SMAP_ID'),
(128, 'FIELD_SMAP_IS_DISABLED'),
(126, 'FIELD_SMAP_IS_FINAL'),
(596, 'FIELD_SMAP_LAYOUT'),
(300, 'FIELD_SMAP_LINK_ID'),
(133, 'FIELD_SMAP_META_DESCRIPTION'),
(131, 'FIELD_SMAP_META_KEYWORDS'),
(129, 'FIELD_SMAP_NAME'),
(125, 'FIELD_SMAP_ORDER_NUM'),
(122, 'FIELD_SMAP_PID'),
(467, 'FIELD_SMAP_REDIRECT_URL'),
(123, 'FIELD_SMAP_SEGMENT'),
(698, 'FIELD_STAFF_ADDITIONAL_TEXT'),
(628, 'FIELD_STAFF_ANNOTATION_RTF'),
(627, 'FIELD_STAFF_POST'),
(643, 'FIELD_STAFF_PROJECTS_RTF'),
(629, 'FIELD_STAFF_TEXT_RTF'),
(626, 'FIELD_STAFF_TITLE'),
(972, 'FIELD_SVI_COUNT'),
(973, 'FIELD_SVI_COUNT_PERCENT'),
(974, 'FIELD_SVI_DESC_RTF'),
(966, 'FIELD_SV_IS_ACTIVE'),
(967, 'FIELD_SV_NAME'),
(1065, 'FIELD_TABCOUNT'),
(329, 'FIELD_TAGS'),
(703, 'FIELD_TARGET_ID'),
(1073, 'FIELD_TEMPLATE'),
(348, 'FIELD_TEXTBLOCK_SOURCE'),
(778, 'FIELD_THEME_CLOSED'),
(933, 'FIELD_THEME_COUNT'),
(780, 'FIELD_THEME_CREATED'),
(776, 'FIELD_THEME_NAME'),
(944, 'FIELD_THEME_NAME2'),
(777, 'FIELD_THEME_TEXT'),
(1057, 'FIELD_TITLE'),
(77, 'FIELD_TMPL_CONTENT'),
(494, 'FIELD_TMPL_ICON'),
(124, 'FIELD_TMPL_ID'),
(78, 'FIELD_TMPL_IS_SYSTEM'),
(76, 'FIELD_TMPL_LAYOUT'),
(75, 'FIELD_TMPL_NAME'),
(1129, 'FIELD_TOP'),
(677, 'FIELD_TVPROGRAM_ANNOTATION_RTF'),
(681, 'FIELD_TVPROGRAM_CAST'),
(680, 'FIELD_TVPROGRAM_DIRECTOR'),
(684, 'FIELD_TVPROGRAM_HOST'),
(716, 'FIELD_TVPROGRAM_ID'),
(676, 'FIELD_TVPROGRAM_NAME'),
(679, 'FIELD_TVPROGRAM_PRODUCTION'),
(682, 'FIELD_TVPROGRAM_REDIRECT_URL'),
(674, 'FIELD_TVPROGRAM_SEGMENT'),
(678, 'FIELD_TVPROGRAM_TEXT_RTF'),
(675, 'FIELD_TVPROGRAM_YEAR'),
(768, 'FIELD_TVPS_IS_GROUP'),
(769, 'FIELD_TVPS_IS_PREMIERE'),
(770, 'FIELD_TVPS_NAME'),
(767, 'FIELD_TVPS_URL'),
(715, 'FIELD_TVSLOT_ANNOTATION_RTF'),
(949, 'FIELD_TVSLOT_DATE'),
(713, 'FIELD_TVSLOT_DURATION'),
(950, 'FIELD_TVSLOT_IS_YESTERDAY'),
(712, 'FIELD_TVSLOT_NAME'),
(711, 'FIELD_TVSLOT_TIME'),
(673, 'FIELD_TVTYPE_ID'),
(670, 'FIELD_TVTYPE_NAME'),
(671, 'FIELD_TVTYPE_NAME_PLURAL'),
(669, 'FIELD_TVTYPE_SEGMENT'),
(672, 'FIELD_TVTYPE_SHORTNAME'),
(688, 'FIELD_UPL_DESCRIPTION'),
(444, 'FIELD_UPL_FILE'),
(336, 'FIELD_UPL_NAME'),
(397, 'FIELD_UPL_PATH'),
(687, 'FIELD_UPL_PUBLICATION_DATE'),
(910, 'FIELD_UPL_RESIZE_IMAGE'),
(1033, 'FIELD_UPL_VIEWS'),
(368, 'FIELD_U_ADDRESS'),
(697, 'FIELD_U_AVATAR_IMG'),
(487, 'FIELD_U_AVATAR_PRFILE'),
(695, 'FIELD_U_BIRTHDAY'),
(486, 'FIELD_U_FULLNAME'),
(506, 'FIELD_U_GROUP'),
(277, 'FIELD_U_ID'),
(492, 'FIELD_U_IS_ACTIVE'),
(694, 'FIELD_U_IS_MALE'),
(345, 'FIELD_U_MAIN_PHONE'),
(50, 'FIELD_U_NAME'),
(693, 'FIELD_U_NICK'),
(958, 'FIELD_U_NICK_2'),
(52, 'FIELD_U_PASSWORD'),
(53, 'FIELD_U_PASSWORD2'),
(696, 'FIELD_U_PLACE'),
(636, 'FIELD_VACANCY_ANNOTATION_RTF'),
(634, 'FIELD_VACANCY_CONTACT_EMAIL'),
(631, 'FIELD_VACANCY_DATE'),
(632, 'FIELD_VACANCY_END_DATE'),
(638, 'FIELD_VACANCY_INFO_RTF'),
(630, 'FIELD_VACANCY_IS_ACTIVE'),
(633, 'FIELD_VACANCY_NAME'),
(637, 'FIELD_VACANCY_TEXT_RTF'),
(635, 'FIELD_VACANCY_URL_SEGMENT'),
(722, 'FIELD_VOTE_ANNOTATION_RTF'),
(718, 'FIELD_VOTE_CREATED'),
(721, 'FIELD_VOTE_END_DATE'),
(725, 'FIELD_VOTE_ID'),
(717, 'FIELD_VOTE_NAME'),
(723, 'FIELD_VOTE_QUESTION_COUNTER'),
(724, 'FIELD_VOTE_QUESTION_TITLE'),
(720, 'FIELD_VOTE_START_DATE'),
(710, 'FIELD_WEEK_IS_ACTIVE'),
(709, 'FIELD_WEEK_NAME'),
(1117, 'FIELD_WIDGET_ICON_IMG'),
(1113, 'FIELD_WIDGET_NAME'),
(470, 'FIELD_ZIP_FILE'),
(601, 'LAYOUT_DEFAULT'),
(957, 'LAYOUT_DEFAULT_WITHOUT_SIDEBAR'),
(600, 'LAYOUT_EMPTY'),
(599, 'LAYOUT_GOOGLE_SITEMAP'),
(826, 'LAYOUT_MAIN'),
(825, 'LAYOUT_MAIN_PAGE'),
(598, 'LAYOUT_NE_ADMIN'),
(556, 'MSG_BAD_CURR_ABBR'),
(144, 'MSG_BAD_EMAIL_FORMAT'),
(146, 'MSG_BAD_FLOAT_FORMAT'),
(178, 'MSG_BAD_FORMAT'),
(145, 'MSG_BAD_PHONE_FORMAT'),
(179, 'MSG_BAD_USER_NAME_FORMAT'),
(154, 'MSG_CONFIRM_DELETE'),
(499, 'MSG_DELETE_FILE'),
(343, 'MSG_EMPTY_SEARCH_RESULT'),
(136, 'MSG_FIELD_IS_NOT_NULL'),
(783, 'MSG_FILE_IS_NOT_NULL'),
(784, 'MSG_LOAD_FILE'),
(447, 'MSG_NO_ATTACHED_FILES'),
(261, 'MSG_NO_FILE'),
(578, 'MSG_ORDER_FAILED'),
(296, 'MSG_PASSWORD_SENT'),
(374, 'MSG_PWD_MISMATCH'),
(424, 'MSG_REQUEST_SENT'),
(331, 'MSG_START_EDITING'),
(558, 'MSG_SWITCHER_TIP'),
(445, 'TAB_ATTACHED_FILES'),
(586, 'TAB_ORDER_DETAILS'),
(510, 'TAB_PAGE_RIGHTS'),
(1045, 'TAB_PARAMS'),
(538, 'TAB_PRODUCT_PARAMS'),
(706, 'TAB_STB_NEWS_COMMENT'),
(320, 'TXT_ACCESS_EDIT'),
(319, 'TXT_ACCESS_FULL'),
(321, 'TXT_ACCESS_READ'),
(456, 'TXT_ADDRESS'),
(549, 'TXT_ADD_MANUFACTURER'),
(1028, 'TXT_ADD_TO_FAVORITES'),
(508, 'TXT_AFTER_SAVE_ACTION'),
(953, 'TXT_AGE'),
(173, 'TXT_ALIGN_BOTTOM'),
(176, 'TXT_ALIGN_LEFT'),
(174, 'TXT_ALIGN_MIDDLE'),
(177, 'TXT_ALIGN_RIGHT'),
(175, 'TXT_ALIGN_TOP'),
(879, 'TXT_ALL'),
(760, 'TXT_ALL_CASTINGS'),
(762, 'TXT_ALL_GENRES'),
(564, 'TXT_ANOTHER_PRODUCTS'),
(912, 'TXT_ANSWER'),
(788, 'TXT_BACKEDITOR'),
(429, 'TXT_BACK_TO_LIST'),
(985, 'TXT_BAD_CAPTCHA'),
(302, 'TXT_BAD_SEGMENT_FORMAT'),
(1037, 'TXT_BAN'),
(1085, 'TXT_BANIP'),
(1015, 'TXT_BANIPEDITOR'),
(1017, 'TXT_BAN_FOR_DAY'),
(1021, 'TXT_BAN_FOR_EVER'),
(1019, 'TXT_BAN_FOR_MONTH'),
(1018, 'TXT_BAN_FOR_WEEK'),
(1020, 'TXT_BAN_FOR_YEAR'),
(566, 'TXT_BASKET_CONTENTS'),
(567, 'TXT_BASKET_EMPTY'),
(565, 'TXT_BASKET_SUMM'),
(570, 'TXT_BASKET_SUMM2'),
(932, 'TXT_BODY_FEEDBACK_ADMIN'),
(990, 'TXT_BODY_FEEDBACK_ARTISTS_ADMIN'),
(929, 'TXT_BODY_FEEDBACK_USER'),
(505, 'TXT_BODY_REGISTER'),
(294, 'TXT_BODY_RESTORE_PASSWORD'),
(989, 'TXT_BODY_STB_FEEDBACK_ADMIN'),
(813, 'TXT_CASTING_BLOCK'),
(759, 'TXT_CASTING_LABEL'),
(763, 'TXT_CAT_NEWS_ALL'),
(1093, 'TXT_CHANGED'),
(421, 'TXT_CLOSE_FIELD'),
(939, 'TXT_COMMENT_NUM'),
(432, 'TXT_CONTACTS'),
(419, 'TXT_CONTACTS_TITLE'),
(560, 'TXT_CURRENCY_RATE'),
(883, 'TXT_DAY_MAIN_NEWS'),
(943, 'TXT_DELETE'),
(82, 'TXT_DETAIL_INFO'),
(507, 'TXT_DIVISIONS'),
(238, 'TXT_DIVISION_EDITOR'),
(121, 'TXT_DIV_EDITOR'),
(205, 'TXT_DOWNLOAD_FILE'),
(942, 'TXT_EDIT'),
(112, 'TXT_EDIT_ITEM'),
(579, 'TXT_ENTER_CAPTCHA'),
(371, 'TXT_ENTER_PASSWORD'),
(64, 'TXT_ERRORS'),
(987, 'TXT_FEEDBACKARTISTSEDITOR'),
(1004, 'TXT_FEEDBACKCOMMONLIST'),
(443, 'TXT_FEEDBACKLIST'),
(1001, 'TXT_FEEDBACK_FROM_AUTHOR'),
(1002, 'TXT_FEEDBACK_FROM_EMAIL'),
(991, 'TXT_FEEDBACK_FROM_PHONE'),
(1000, 'TXT_FEEDBACK_FROM_TEXT'),
(440, 'TXT_FEEDBACK_SUCCESS_SEND'),
(691, 'TXT_FEMALE'),
(396, 'TXT_FILELIBRARY'),
(683, 'TXT_FILEREPO'),
(326, 'TXT_FILTER'),
(297, 'TXT_FORGOT_PWD'),
(771, 'TXT_FORUMCATEGORYEDITOR'),
(782, 'TXT_FORUMTHEMEEDITOR'),
(946, 'TXT_FORUM_COMMENT_COUNT'),
(911, 'TXT_FORUM_CREATE_THEME'),
(945, 'TXT_FORUM_LAST_COMMENT'),
(947, 'TXT_FORUM_THEME_COUNT'),
(739, 'TXT_FROM'),
(756, 'TXT_FULL_SCHEDULE'),
(914, 'TXT_GENDER'),
(761, 'TXT_GENRE_LABEL'),
(450, 'TXT_H1'),
(451, 'TXT_H2'),
(452, 'TXT_H3'),
(453, 'TXT_H4'),
(454, 'TXT_H5'),
(455, 'TXT_H6'),
(344, 'TXT_HOME'),
(938, 'TXT_HOT_FORUM_THEMES'),
(255, 'TXT_IMAGE_LIBRARY'),
(971, 'TXT_ITEMEDITOR'),
(279, 'TXT_LANGUAGE_EDITOR'),
(940, 'TXT_LAST_COMMENT_DATE'),
(936, 'TXT_LAST_FORUM_MESSAGES'),
(954, 'TXT_LOCATION'),
(580, 'TXT_LOGIN_ENGAGED'),
(624, 'TXT_MAIN_NEWS'),
(692, 'TXT_MALE'),
(919, 'TXT_MIEDITOR'),
(471, 'TXT_MONTH_1'),
(480, 'TXT_MONTH_10'),
(481, 'TXT_MONTH_11'),
(482, 'TXT_MONTH_12'),
(472, 'TXT_MONTH_2'),
(473, 'TXT_MONTH_3'),
(474, 'TXT_MONTH_4'),
(475, 'TXT_MONTH_5'),
(476, 'TXT_MONTH_6'),
(477, 'TXT_MONTH_7'),
(478, 'TXT_MONTH_8'),
(479, 'TXT_MONTH_9'),
(827, 'TXT_NEW_PHOTO'),
(812, 'TXT_NEW_VIDEO'),
(880, 'TXT_NOW'),
(948, 'TXT_NO_COMMENTS'),
(156, 'TXT_NO_RIGHTS'),
(420, 'TXT_OPEN_FIELD'),
(548, 'TXT_OR'),
(83, 'TXT_ORDER'),
(582, 'TXT_ORDERHISTORY'),
(576, 'TXT_ORDER_CLIENT_SUBJECT'),
(575, 'TXT_ORDER_MANAGER_SUBJECT'),
(574, 'TXT_ORDER_SEND'),
(216, 'TXT_PAGES'),
(529, 'TXT_PARAM_TYPE_INT'),
(528, 'TXT_PARAM_TYPE_STRING'),
(530, 'TXT_PARAM_TYPE_TEXT'),
(1010, 'TXT_PARTICIPANTS'),
(955, 'TXT_PARTICIPANT_IS_DISABLED'),
(830, 'TXT_PHOTO'),
(915, 'TXT_PLACE'),
(1030, 'TXT_POST_ON_FACEBOOK'),
(1032, 'TXT_POST_ON_LIVEJOURNAL'),
(1031, 'TXT_POST_ON_TWITTER'),
(1029, 'TXT_POST_ON_VKONTAKTE'),
(878, 'TXT_PREMIERE'),
(385, 'TXT_PREVIEW'),
(1027, 'TXT_PRINT'),
(520, 'TXT_PRODUCEREDITOR'),
(523, 'TXT_PRODUCTTYPEEDITOR'),
(561, 'TXT_PRODUCT_PARAMS'),
(562, 'TXT_PRODUCT_PRICE'),
(1109, 'TXT_PROJECT_NEWS'),
(81, 'TXT_PROPERTIES'),
(979, 'TXT_READ_ALL_CASTINGS'),
(758, 'TXT_READ_ALL_NEWS'),
(937, 'TXT_READ_ALL_THEMES'),
(965, 'TXT_READ_ALL_VACANCIES'),
(752, 'TXT_READ_MORE'),
(886, 'TXT_REGISTRATION'),
(135, 'TXT_REGISTRATION_SUCCESS'),
(690, 'TXT_REGISTRATION_TEXT'),
(439, 'TXT_REQUIRED_FIELDS'),
(449, 'TXT_RESET'),
(1089, 'TXT_RESET_CONTENT'),
(328, 'TXT_RESET_FILTER'),
(341, 'TXT_ROLE_DIV_RIGHTS'),
(274, 'TXT_ROLE_EDITOR'),
(488, 'TXT_ROLE_TEXT'),
(511, 'TXT_SEARCH_CATALOGUE'),
(978, 'TXT_SEARCH_ON_STB'),
(342, 'TXT_SEARCH_RESULT'),
(1026, 'TXT_SEND_EMAIL'),
(757, 'TXT_SHARE_BLOCK_TITLE'),
(143, 'TXT_SHIT_HAPPENS'),
(1012, 'TXT_SIMILAR_NEWS'),
(431, 'TXT_SITEMAP'),
(968, 'TXT_SPONSORVOTEEDITOR'),
(986, 'TXT_STBFEEDBACKLIST'),
(931, 'TXT_SUBJ_FEEDBACK_ADMIN'),
(1003, 'TXT_SUBJ_FEEDBACK_COMMON'),
(930, 'TXT_SUBJ_FEEDBACK_USER'),
(504, 'TXT_SUBJ_REGISTER'),
(293, 'TXT_SUBJ_RESTORE_PASSWORD'),
(988, 'TXT_SUBJ_STB_FEEDBACK_ADMIN'),
(80, 'TXT_SUCCESS'),
(186, 'TXT_TEMPLATE_EDITOR'),
(1097, 'TXT_TEXTBLOCK_SOURCE_EDITOR'),
(322, 'TXT_THUMB'),
(740, 'TXT_TO'),
(726, 'TXT_TODAY'),
(755, 'TXT_TODAY_ON_STB'),
(828, 'TXT_TOP_PHOTO'),
(785, 'TXT_TOP_VIDEO'),
(708, 'TXT_TVWEEKSCHEDULEEDITOR'),
(884, 'TXT_UNKNOWN'),
(1022, 'TXT_USER_BAN_EDITOR'),
(273, 'TXT_USER_EDITOR'),
(287, 'TXT_USER_GREETING'),
(304, 'TXT_USER_GROUPS'),
(286, 'TXT_USER_NAME'),
(325, 'TXT_USER_PROFILE_SAVED'),
(375, 'TXT_USER_PROFILE_WRONG_PWD'),
(441, 'TXT_USER_REGISTRED'),
(640, 'TXT_VACANCIES'),
(811, 'TXT_VACANCIES_BLOCK'),
(964, 'TXT_VACANCY_CONTACT_EMAIL'),
(963, 'TXT_VACANCY_END_DATE'),
(962, 'TXT_VACANCY_START_DATE'),
(871, 'TXT_VIDEO'),
(110, 'TXT_VIEW_ITEM'),
(1011, 'TXT_VIEW_VIDEO'),
(661, 'TXT_VIKNA_NEWS'),
(984, 'TXT_VOTE'),
(983, 'TXT_VOTES_IN_FAVOUR'),
(786, 'TXT_VOTE_TITLE'),
(873, 'TXT_WATCH'),
(764, 'TXT_WATCH_ALL'),
(829, 'TXT_WATCH_ALL_PHOTO'),
(787, 'TXT_WATCH_ALL_VIDEO'),
(1125, 'TXT_WIDGETEDITOR'),
(727, 'TXT_YESTERDAY'),
(642, 'TXT_YOU_ALREADY_VOTED');

-- --------------------------------------------------------

--
-- Table structure for table `share_lang_tags_translation`
--

DROP TABLE IF EXISTS `share_lang_tags_translation`;
CREATE TABLE IF NOT EXISTS `share_lang_tags_translation` (
  `ltag_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ltag_value_rtf` text NOT NULL,
  PRIMARY KEY (`ltag_id`,`lang_id`),
  KEY `FK_tranaslatelv_language` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `share_lang_tags_translation`
--

INSERT INTO `share_lang_tags_translation` (`ltag_id`, `lang_id`, `ltag_value_rtf`) VALUES
(14, 1, 'Значение'),
(14, 2, 'Значення'),
(15, 1, 'Выйти'),
(15, 2, 'Вийти'),
(42, 1, 'Сохранить'),
(42, 2, 'Зберегти'),
(43, 1, 'Добавить'),
(43, 2, 'Додати'),
(45, 1, 'вернуться к списку'),
(45, 2, 'повернутися до списку'),
(46, 1, 'Редактировать'),
(46, 2, 'Редагувати'),
(47, 1, 'Удалить'),
(47, 2, 'Видалити'),
(49, 1, 'Имя тега'),
(49, 2, 'Назва тега'),
(50, 1, 'Логин (Ваш e-mail)'),
(50, 2, 'Логін (Ваш e-mail)'),
(52, 1, 'Пароль'),
(52, 2, 'Пароль'),
(53, 1, 'Повторите пароль'),
(53, 2, 'Повторіть пароль'),
(54, 1, 'Зарегистрироваться'),
(54, 2, 'Зареєструватись'),
(55, 1, 'Сохранить изменения'),
(55, 2, 'Зберегти зміни'),
(56, 1, 'Режим редактирования'),
(56, 2, 'Режим редагування'),
(57, 1, 'Добавить страницу'),
(57, 2, 'Додати сторінку'),
(58, 1, 'Редактировать страницу'),
(58, 2, 'Редагувати сторінку'),
(59, 1, 'Удалить страницу'),
(59, 2, 'Видалити сторінку'),
(60, 1, 'Режим просмотра'),
(60, 2, 'Режим перегляду'),
(61, 1, 'Исходный код'),
(61, 2, 'Вихідний код'),
(62, 1, 'Назначить группы'),
(62, 2, 'Призначити групи'),
(63, 1, 'Группы'),
(63, 2, 'Групи'),
(64, 1, 'В процессе работы возникли ошибки'),
(64, 2, 'В процесі роботи виникли помилки'),
(65, 1, 'Невозможно удалить себя самого.'),
(65, 2, 'Неможливо видалити самого себе.'),
(66, 1, 'Имя группы'),
(66, 2, 'Назва групи'),
(67, 1, 'По умолчанию для гостей'),
(67, 2, 'За замовчанням для гостей'),
(68, 1, 'По умолчанию для пользователей'),
(68, 2, 'За замовчанням для користувачів'),
(69, 1, 'Аббревиатура языка'),
(69, 2, 'Абревіатура мови'),
(70, 1, 'Название языка'),
(70, 2, 'Назва мови'),
(71, 1, 'Язык по умолчанию'),
(71, 2, 'Мова за замовчанням'),
(73, 1, 'Локаль для Windows'),
(73, 2, 'Локаль для Windows'),
(74, 1, 'Назначить права'),
(74, 2, 'Призначити права'),
(75, 1, 'Название шаблона'),
(75, 2, 'Назва шаблону'),
(76, 1, 'Layout'),
(76, 2, 'Layout'),
(77, 1, 'Content'),
(77, 2, 'Content'),
(78, 1, 'Системный шаблон'),
(78, 2, 'Системний шаблон'),
(79, 1, 'Войти'),
(79, 2, 'Увійти'),
(80, 1, 'Регистрация прошла успешно'),
(80, 2, 'Реєстрація пройшла успішно'),
(81, 1, 'Свойства'),
(81, 2, 'Властивості'),
(82, 1, 'подробнее'),
(82, 2, 'детальніше'),
(83, 1, 'Заказать'),
(83, 2, 'Замовити'),
(96, 1, 'Ошибка 404: документ не найден.'),
(96, 2, 'Помилка 404: документ не знайдено.'),
(109, 1, 'Перевод'),
(109, 2, 'Переклад'),
(110, 1, 'Просмотр'),
(110, 2, 'Перегляд'),
(112, 1, 'Редактирование'),
(112, 2, 'Редактування'),
(121, 1, 'Редактор разделов'),
(121, 2, 'Редактор розділів'),
(122, 1, 'Родительский раздел'),
(122, 2, 'Батьківський розділ'),
(123, 1, 'Сегмент URI'),
(123, 2, 'Сегмент URI'),
(124, 1, 'Шаблон'),
(124, 2, 'Шаблон'),
(125, 1, 'Порядок следования'),
(125, 2, 'Порядок слідування'),
(126, 1, 'Конечный раздел'),
(126, 2, 'Кінцевий розділ'),
(127, 1, 'Раздел по умолчанию'),
(127, 2, 'Розділ за замовчанням'),
(128, 1, 'Отключен'),
(128, 2, 'Відключена'),
(129, 1, 'Название раздела'),
(129, 2, 'Назва розділу'),
(130, 1, 'Описание раздела'),
(130, 2, 'Опис розділу'),
(131, 1, 'Ключевые слова (meta keywords)'),
(131, 2, 'Ключові слова (meta keywords)'),
(133, 1, 'Мета-описание (meta description)'),
(133, 2, 'Мета-опис (meta description)'),
(134, 1, 'Закрыть'),
(134, 2, 'Закрити'),
(135, 1, 'Успешная регистрация'),
(135, 2, 'Успішна реєстрація'),
(136, 1, 'Поле не может быть пустым.'),
(136, 2, 'Поле не може бути порожнім.'),
(143, 1, 'При сохранении произошли ошибки'),
(143, 2, 'При збереженні сталися помилки'),
(144, 1, 'Неправильный формат e-mail.'),
(144, 2, 'Неправильний формат e-mail.'),
(145, 1, 'Неправильный формат телефонного номера. Он должен содержать только цифры, знак "-", или пробел.'),
(145, 2, 'Неправильний формат телефонного номера. Він повинен містити тільки цифри, знак "-", або пробіл.'),
(146, 1, 'Неправильный формат числа.'),
(146, 2, 'Неправильний формат числа.'),
(154, 1, 'Вы уверены, что хотите удалить запись? Восстановить данные потом будет невозможно.'),
(154, 2, 'Ви впевнені, що хочете видалити запис? Відновити дані потім буде неможливо.'),
(156, 1, 'Права отсутствуют'),
(156, 2, 'Права відсутні'),
(157, 1, 'Просмотреть'),
(157, 2, 'Продивитись'),
(158, 1, 'Изображение'),
(158, 2, 'Зображення'),
(159, 1, 'Описание изображения'),
(159, 2, 'Опис зображення'),
(160, 1, 'Маленькое изображение'),
(160, 2, 'Маленьке зображення'),
(161, 1, 'Вставить изображение'),
(161, 2, 'Вставити зображення'),
(162, 1, 'Имя файла'),
(162, 2, 'Назва файла'),
(163, 1, 'Ширина'),
(163, 2, 'Ширина'),
(164, 1, 'Высота'),
(164, 2, 'Висота'),
(165, 1, 'Горизонтальный отступ'),
(165, 2, 'Горизонтальний відступ'),
(166, 1, 'Вертикальный отступ'),
(166, 2, 'Вертикальний відступ'),
(167, 1, 'Альтернативный текст'),
(167, 2, 'Альтернативний текст'),
(168, 1, 'Вставить изображение'),
(168, 2, 'Вставити зображення'),
(169, 1, 'Библиотека изображений'),
(169, 2, 'Бібліотека зображень'),
(170, 1, 'У Вас недостаточно прав на просмотр этой страницы.'),
(170, 2, 'У Вас недостатньо прав для перегляду цієї сторінки.'),
(171, 1, 'Менеджер изображений'),
(171, 2, 'Менеджер зображень'),
(172, 1, 'Выравнивание'),
(172, 2, 'Вирівнювання'),
(173, 1, 'Внизу'),
(173, 2, 'Внизу'),
(174, 1, 'Посередине'),
(174, 2, 'Посередині'),
(175, 1, 'Вверху'),
(175, 2, 'Зверху'),
(176, 1, 'Слева'),
(176, 2, 'Зліва'),
(177, 1, 'Справа'),
(177, 2, 'Справа'),
(178, 1, 'Неправильный формат'),
(178, 2, 'Неправильний формат'),
(179, 1, 'Неправильный формат имени пользователя. В поле должны быть только латинские буквы и цифры.'),
(179, 2, 'Неправильний формат імені користувача. У полі повинні бути тільки латинські літери і цифри.'),
(186, 1, 'Редактор шаблонов'),
(186, 2, 'Редактор шаблонів'),
(205, 1, 'Загрузить в формате PDF'),
(205, 2, 'Завантажити в форматі PDF'),
(214, 1, 'Перейти'),
(214, 2, 'Перейти'),
(216, 1, 'Страницы'),
(216, 2, 'Сторінки'),
(236, 1, 'Категория'),
(236, 2, 'Категорія'),
(237, 1, 'Выбрать'),
(237, 2, 'Обрати'),
(238, 1, 'Список разделов'),
(238, 2, 'Список розділів'),
(250, 1, 'Поднять'),
(250, 2, 'Підняти'),
(251, 1, 'Опустить'),
(251, 2, 'Опустити'),
(255, 1, 'Библиотека изображений'),
(255, 2, 'Бібліотека зображень'),
(260, 1, 'Загрузить'),
(260, 2, 'Завантажити'),
(261, 1, 'Файл не выбран'),
(261, 2, 'Файл не обрано'),
(263, 1, 'Произошла ошибка при работе с базой данных.'),
(263, 2, 'Сталася помилка при роботі з базою даних.'),
(265, 1, 'Запомнить'),
(265, 2, 'Запам''ятати'),
(269, 1, 'Отправить'),
(269, 2, 'Відправити'),
(273, 1, 'Редактор пользователей'),
(273, 2, 'Редактор користувачів'),
(274, 1, 'Редактор ролей'),
(274, 2, 'Редактор ролей'),
(277, 1, 'Пользователь'),
(277, 2, 'Користувач'),
(279, 1, 'Редактор языков'),
(279, 2, 'Редактор мов'),
(284, 1, 'Искать'),
(284, 2, 'Шукати'),
(286, 1, 'Вы вошли в систему как'),
(286, 2, 'Ви увійшли до системи як'),
(287, 1, 'Приветствуем,'),
(287, 2, 'Вітаємо,'),
(293, 1, 'Восстановление пароля на сайте СТБ'),
(293, 2, 'Відновлення пароля на сайті СТБ'),
(294, 1, '<p>Здравствуйте.</p>\n<p>Вами был подан запрос на восстановление пароля.<br>\nВаш новый пароль: $password.</p>\n<p>В качестве имени пользователя используйте адрес электронной почты,\nна которое поступило это письмо.</p>\n<p>С уважением, STB.UA.</p>\n'),
(294, 2, '<p>Доброго дня.</p>\n<p>Вами було подано запит на відновлення пароля.<br>\nВаш новый пароль: $password.</p>\n<p>В якості імені користувача використовуйте адресу електронної пошти,\nна яку надійшов цей лист.</p>\n<p>З повагою, STB.UA</p>\n'),
(295, 1, 'Неправильное имя пользователя'),
(295, 2, 'Невірне ім''я користувача'),
(296, 1, 'На указанный вами адрес электронной почты был отправлен новый пароль.'),
(296, 2, 'На вказану вами адресу електронної пошти було відправлено новий пароль.'),
(297, 1, 'Забыли пароль?'),
(297, 2, 'Забули пароль?'),
(300, 1, 'Ссылка на раздел'),
(300, 2, 'Посилання на розділ'),
(302, 1, 'Неправильный формат сегмента URL'),
(302, 2, 'Неправильний формат сегмента URL'),
(303, 1, '<P><STRONG>Пользователь с такими данными уже существует.</STRONG></P>\n<P>Скорее всего вы уже зарегистрированы в нашем магазине. </P>\n<P>Вам необходимо авторизоваться , перейдя на форму авторизации. </P>\n<P>Если вы забыли свой пароль, воспользуйтесь формой восстановления пароля, расположенной на той же странице.</P>'),
(303, 2, '<P><STRONG>Користувач з такими даними уже існує.</STRONG></P>\n<P>Скоріш за все, ви вже зареєстровані у нашому магазині. </P>\n<P>Вам необхідно авторизуватися за допомогою форми авторизації. </P>\n<P>Якщо ви забули свій пароль, скористайтесь формою відновлення пароля, що знаходиться на тій же сторінці.</P>'),
(304, 1, 'Группы'),
(304, 2, 'Групи'),
(305, 1, 'Комментарий'),
(305, 2, 'Коментар'),
(312, 1, 'Открыть'),
(312, 2, 'Відкрити'),
(313, 1, 'Создать папку'),
(313, 2, 'Створити папку'),
(314, 1, 'Переименовать'),
(314, 2, 'Перейменувати'),
(319, 1, 'Полный доступ'),
(319, 2, 'Повний доступ'),
(320, 1, 'Редактирование'),
(320, 2, 'Редактування'),
(321, 1, 'Только чтение'),
(321, 2, 'Тільки перегляд'),
(322, 1, 'Маленькое изображение для: '),
(322, 2, 'Маленьке зображення для: '),
(323, 1, 'Структура сайта'),
(323, 2, 'Структура сайту'),
(324, 1, 'Шаблоны'),
(324, 2, 'Шаблони'),
(325, 1, '<p>Пользовательские настройки сохранены.</p>'),
(325, 2, '<p>Налаштування користувача збережено.</p>'),
(326, 1, 'Фильтр'),
(326, 2, 'Фільтр'),
(327, 1, 'Применить'),
(327, 2, 'Застосувати'),
(328, 1, 'Сбросить фильтр'),
(328, 2, 'Скинути фільтр'),
(329, 1, 'Теги'),
(329, 2, 'Теги'),
(330, 1, 'Сохранить и перейти к редактированию'),
(330, 2, 'Зберегти і перейти до редагування'),
(331, 1, 'Вы хотите перейти на новосозданную страницу?'),
(331, 2, 'Ви хочете перейти до нової сторінки?'),
(336, 1, 'Название'),
(336, 2, 'Назва'),
(339, 1, 'Нельзя удалить группу по умолчанию'),
(339, 2, 'Неможливо видалити групу за замовчанням'),
(340, 1, 'Права группы по умолчанию'),
(340, 2, 'Права групи за замовчанням'),
(341, 1, 'Права на разделы'),
(341, 2, 'Права на розділи'),
(342, 1, 'Результаты поиска'),
(342, 2, 'Результати пошуку'),
(343, 1, 'По вашему запросу товар не найден.'),
(343, 2, 'За вашим запитом товар не знайдено.'),
(344, 1, 'Главная'),
(344, 2, 'Головна'),
(345, 1, 'Телефон'),
(345, 2, 'Телефон'),
(346, 1, 'Сохранить'),
(346, 2, 'Зберегти'),
(347, 1, 'Отменить'),
(347, 2, 'Відмінити'),
(348, 1, 'Исходный код текстового блока'),
(348, 2, 'Вихідний код текстового блоку'),
(357, 1, 'Файловый репозиторий'),
(357, 2, 'Файловий репозиторій'),
(365, 1, 'Изменить персональные данные'),
(365, 2, 'Змінити персональні дані'),
(367, 1, 'Новый пароль'),
(367, 2, 'Новий пароль'),
(368, 1, 'Адрес'),
(368, 2, 'Адреса'),
(371, 1, 'Для сохранения необходимо ввести пароль'),
(371, 2, 'Для збереження необхідно ввести пароль'),
(372, 1, 'Новый пароль'),
(372, 2, 'Новий пароль'),
(373, 1, 'Подтвердите пароль'),
(373, 2, 'Підтвердіть пароль'),
(374, 1, 'Новый пароль и его подтверждение должны быть одинаковыми.'),
(374, 2, 'Новий пароль і його підтвердження повинні бути однакові.'),
(375, 1, 'Вы ввели неверный пароль'),
(375, 2, 'Ви ввели невірний пароль'),
(376, 1, 'Неверный логин или пароль'),
(376, 2, 'Невірний логін або пароль'),
(381, 1, 'Переводы'),
(381, 2, 'Переклади'),
(383, 1, 'Невозможно изменить порядок следования'),
(383, 2, 'Неможливо змінити порядок слідування'),
(384, 1, 'Альтернативный title страницы'),
(384, 2, 'Альтернативний title сторінки'),
(385, 1, 'Режим просмотра'),
(385, 2, 'Режим перегляду'),
(389, 1, 'Неправильные данные.'),
(389, 2, 'Неправильні дані.'),
(391, 1, 'Заголовок новости'),
(391, 2, 'Заголовок новини'),
(392, 1, 'Дата новости'),
(392, 2, 'Дата новини'),
(393, 1, 'Анонс новости '),
(393, 2, 'Анонс новини'),
(394, 1, 'Текст новости'),
(394, 2, 'Текст новини'),
(396, 1, 'Файловый репозиторий'),
(396, 2, 'Файловий репозиторій'),
(397, 1, 'Имя папки (сегмент URI)'),
(397, 2, 'Назва папки (сегмент URI)'),
(419, 1, 'Контакты'),
(419, 2, 'Контакти'),
(420, 1, 'Открыть поле'),
(420, 2, 'Відкрити поле'),
(421, 1, 'Скрыть поле'),
(421, 2, 'Сховати поле'),
(422, 1, '/^(([^()<>@,;:\\\\\\".\\[\\] ]+)|("[^"\\\\\\\\\\r]*"))((\\.[^()<>@,;:\\\\\\".\\[\\] ]+)|(\\."[^"\\\\\\\\\\r]*"))*@(([a-z0-9][a-z0-9\\-]+)*[a-z0-9]+\\.)+[a-z]{2,}$/i'),
(422, 2, '/^(([^()<>@,;:\\\\\\".\\[\\] ]+)|("[^"\\\\\\\\\\r]*"))((\\.[^()<>@,;:\\\\\\".\\[\\] ]+)|(\\."[^"\\\\\\\\\\r]*"))*@(([a-z0-9][a-z0-9\\-]+)*[a-z0-9]+\\.)+[a-z]{2,}$/i'),
(424, 1, 'Спасибо за внимание к нашим продуктам. Ваша заявка отправлена. В ближайшее время мы свяжемся с Вами.'),
(424, 2, 'Дякуємо за інтерес до наших продуктів. Ваше замовлення відправлено. Найближчим часом ми звяжемось з вами. '),
(426, 1, 'Добавить новость'),
(426, 2, 'Додати новину'),
(427, 1, 'Редактировать новость'),
(427, 2, 'Редагувати новину'),
(428, 1, 'Удалить новость'),
(428, 2, 'Видалити новину'),
(429, 1, 'вернуться к списку'),
(429, 2, 'повернутися до списку'),
(431, 1, 'Карта сайта'),
(431, 2, 'Карта сайту'),
(432, 1, 'Контакты'),
(432, 2, 'Контакти'),
(436, 1, 'Текст сообщения'),
(436, 2, 'Текст повідомлення'),
(437, 1, 'Автор сообщения'),
(437, 2, 'Автор повідомлення'),
(438, 1, 'Контактный e-mail'),
(438, 2, 'Контактний e-mail'),
(439, 1, '<span class="mark">*</span> - поля, обязательные для заполнения'),
(439, 2, '<span class="mark">*</span> - поля, обов''язкові для заповнення'),
(440, 1, 'Ваше сообщение успешно отправлено.'),
(440, 2, 'Ваше повідомлення успішно відправлено.'),
(441, 1, 'Поздравляем, Вы успешно зарегистрировались. На указанный Вами адрес\nэлектронной почты отправлено письмо с Вашим паролем.'),
(441, 2, 'Вітаємо, Ви вдало зареєструвалися. На вказану Вами електронну адресу\nвідправлено лист з Вашим паролем.'),
(442, 1, 'Дата сообщения'),
(442, 2, 'Дата повідомлення'),
(443, 1, 'Список сообщений'),
(443, 2, 'Список повідомлень'),
(444, 1, 'Файл'),
(444, 2, 'Файл'),
(445, 1, 'Дополнительные файлы'),
(445, 2, 'Додаткові файли'),
(446, 1, 'Вставить из репозитория'),
(446, 2, 'Вставити з репозиторію'),
(447, 1, 'Дополнительные файлы отсутствуют'),
(447, 2, 'Додаткові файли відсутні'),
(448, 1, 'Удалить'),
(448, 2, 'Видалити'),
(449, 1, 'Очистить форматирование'),
(449, 2, 'Очистити форматування'),
(450, 1, 'Заголовок 1'),
(450, 2, 'Заголовок 1'),
(451, 1, 'Заголовок 2'),
(451, 2, 'Заголовок 2'),
(452, 1, 'Заголовок 3'),
(452, 2, 'Заголовок 3'),
(453, 1, 'Заголовок 4'),
(453, 2, 'Заголовок 4'),
(454, 1, 'Заголовок 5'),
(454, 2, 'Заголовок 5'),
(455, 1, 'Заголовок 6'),
(455, 2, 'Заголовок 6'),
(456, 1, 'Адрес'),
(456, 2, 'Адреса'),
(457, 1, 'Полужирный шрифт'),
(457, 2, 'Напівжирний '),
(458, 1, 'Курсив'),
(458, 2, 'Курсив'),
(459, 1, 'Вставить ссылку'),
(459, 2, 'Вставити посилання'),
(460, 1, 'Ненумерованный список'),
(460, 2, 'Ненумерований список'),
(461, 1, 'Нумерованный список'),
(461, 2, 'Нумерований перелiк'),
(462, 1, 'Выравнивание по левому краю'),
(462, 2, 'Вирiвнювання злiва'),
(463, 1, 'Выравнивание по правому краю'),
(463, 2, 'Вирiвнювання справа'),
(464, 1, 'Выравнивание по центру'),
(464, 2, 'Вирiвнювання по центру'),
(465, 1, 'Выравнивание по ширине'),
(465, 2, 'Вирiвнювання по ширинi'),
(466, 1, 'Вставить ссылку на файл'),
(466, 2, 'Вставити посилання на файл'),
(467, 1, 'Перенаправлять по адресу'),
(467, 2, 'Перенаправляти за адресою'),
(468, 1, 'Неправильный формат УРЛ'),
(468, 2, 'Невiрний формат УРЛ'),
(469, 1, 'Загрузить архив'),
(469, 2, 'Завантажити архiв'),
(470, 1, 'Архив содержащий  файлы для загрузки'),
(470, 2, 'Архів, що містить файли для завантаження'),
(471, 1, 'января'),
(471, 2, 'сiчня'),
(472, 1, 'февраля'),
(472, 2, 'лютого'),
(473, 1, 'марта'),
(473, 2, 'березня'),
(474, 1, 'апреля'),
(474, 2, 'квiтня'),
(475, 1, 'мая'),
(475, 2, 'травня'),
(476, 1, 'июня'),
(476, 2, 'червня'),
(477, 1, 'июля'),
(477, 2, 'липня'),
(478, 1, 'августа'),
(478, 2, 'серпня'),
(479, 1, 'сентября'),
(479, 2, 'вересня'),
(480, 1, 'октября'),
(480, 2, 'жовтня'),
(481, 1, 'ноября'),
(481, 2, 'листопада'),
(482, 1, 'декабря'),
(482, 2, 'грудня'),
(483, 1, 'Описание'),
(483, 2, 'Опис'),
(484, 1, 'Название'),
(484, 2, 'Назва'),
(485, 1, 'Фотография'),
(485, 2, 'Фотографiя'),
(486, 1, 'Полное имя'),
(486, 2, 'Повне iм''я'),
(487, 1, 'Аватар'),
(487, 2, 'Аватар'),
(488, 1, 'Роль'),
(488, 2, 'Роль'),
(489, 1, 'Языки'),
(489, 2, 'Мови'),
(490, 1, 'Пользователи'),
(490, 2, 'Користувачі'),
(491, 1, 'Роли'),
(491, 2, 'Ролі'),
(492, 1, 'Активизирован'),
(492, 2, 'Активований'),
(493, 1, 'Активизировать'),
(493, 2, 'Активувати'),
(494, 1, 'Иконка'),
(494, 2, 'Іконка'),
(495, 1, 'Добавить фотографию'),
(495, 2, 'Додати фотографію'),
(497, 1, 'Удалить'),
(497, 2, 'Видалити'),
(498, 1, 'Маленькое изображение'),
(498, 2, 'Маленьке зображення'),
(499, 1, 'Удалить файл'),
(499, 2, 'Видалити'),
(500, 1, 'Отступ снизу'),
(500, 2, 'Відступ знизу'),
(501, 1, 'Отступ слева'),
(501, 2, 'Відступ зліва'),
(502, 1, 'Оступ справа'),
(502, 2, 'Відступ справа'),
(503, 1, 'Отступ сверху'),
(503, 2, 'Відступ зверху'),
(504, 1, 'Уведомление о регистрации на сайте СТБ'),
(504, 2, 'Повідомлення про реєстрацію на сайті СТБ'),
(505, 1, '<p>Здравствуйте, $name.<br>\nВы были зарегистрированы на сайте.</p>\n<p>Ваш логин: $login<br>\nПароль: $password</p>\n<p>С уважением, STB.UA.</p>\n'),
(505, 2, '<div>\n<p>Здравствуйте, $name.<br>\nВы были зарегистрированы на сайте.</p>\n<p>Ваш логин: $login<br>\nПароль: $password</p>\n</div>\n<p>З повагою, STB.UA</p>\n'),
(506, 1, 'Группы'),
(506, 2, 'Групи'),
(507, 1, '<img src="images/loading.gif" width="32" height="32"/>'),
(507, 2, '<img src="images/loading.gif" width="32" height="32"/>'),
(508, 1, 'и'),
(508, 2, 'та'),
(510, 1, 'Права на страницу'),
(510, 2, 'Права на сторінку'),
(511, 1, 'Поиск по каталогу'),
(511, 2, 'Пошук по каталогу'),
(512, 1, 'Валюта'),
(512, 2, 'Валюта'),
(513, 1, 'Курс'),
(513, 2, 'Курс'),
(514, 1, 'Аббревиатура'),
(514, 2, 'Абревіатура'),
(515, 1, 'Формат'),
(515, 2, 'Формат'),
(516, 1, 'Статус товара'),
(516, 2, 'Статус товару'),
(517, 1, 'Статус по-умолчанию'),
(517, 2, 'Статус за замовчанням'),
(518, 1, 'Уровень прав, необходимый для просмотра товаров с этим статусом'),
(518, 2, 'Рівень прав, необхідний для перегляду товарів з цим статусом'),
(519, 1, 'Статус заказа'),
(519, 2, 'Статус замовлення'),
(520, 1, 'Редактор производителей'),
(520, 2, 'Редактор виробників'),
(521, 1, 'Производитель'),
(521, 2, 'Виробник'),
(522, 1, 'Сегмент URI'),
(522, 2, 'Сегмент URI'),
(523, 1, 'Редактор типов товара'),
(523, 2, 'Редактор типів товару'),
(524, 1, 'Тип товара'),
(524, 2, 'Тип товару'),
(525, 1, 'Параметры'),
(525, 2, 'Параметри'),
(526, 1, 'Параметр'),
(526, 2, 'Параметр'),
(527, 1, 'Тип'),
(527, 2, 'Тип'),
(528, 1, 'Строка'),
(528, 2, 'Рядок'),
(529, 1, 'Число'),
(529, 2, 'Число'),
(530, 1, 'Текст'),
(530, 2, 'Текст'),
(531, 1, 'Статус'),
(531, 2, 'Статус'),
(532, 1, 'Товар'),
(532, 2, 'Товар'),
(533, 1, 'Сегмент URI'),
(533, 2, 'Сегмент URI'),
(534, 1, 'Код товара'),
(534, 2, 'Код товару'),
(535, 1, 'Цена'),
(535, 2, 'Ціна'),
(536, 1, 'Количество'),
(536, 2, 'Кількість'),
(537, 1, 'Производитель'),
(537, 2, 'Виробник'),
(538, 1, 'Параметры'),
(538, 2, 'Параметри'),
(539, 1, 'Тип товара'),
(539, 2, 'Тип товару'),
(540, 1, 'Краткое описание'),
(540, 2, 'Короткий опис'),
(541, 1, 'Полное описание'),
(541, 2, 'Повний опис'),
(542, 1, 'Печатать'),
(542, 2, 'Друкувати'),
(544, 1, 'Необходимо указать название раздела для всех не отключенных языковых версий'),
(544, 2, 'Необхідно вказати назву розділу для всіх активних мов'),
(545, 1, 'Поднять'),
(545, 2, 'Підняти'),
(546, 1, 'Опустить'),
(546, 2, 'Опустити'),
(547, 1, 'Значение параметра'),
(547, 2, 'Значення параметру'),
(548, 1, 'или'),
(548, 2, 'або'),
(549, 1, 'Создать производителя'),
(549, 2, 'Створити виробника'),
(556, 1, 'Аббревиатура валюты должна состоять из трех латинских букв'),
(556, 2, 'Абревіатура валюти повинна складатися з трьох латинських букв'),
(558, 1, 'Показать цены в'),
(558, 2, 'Відобразити ціни у'),
(559, 1, 'Базовая валюта'),
(559, 2, 'Базова валюта'),
(560, 1, 'Курс валют'),
(560, 2, 'Курс валют'),
(561, 1, 'Параметры'),
(561, 2, 'Параметри'),
(562, 1, 'Цена'),
(562, 2, 'Ціна'),
(564, 1, 'Другие товары этого производителя'),
(564, 2, 'Інші товари цього виробника'),
(565, 1, 'Итого'),
(565, 2, 'Всього'),
(566, 1, 'В корзине'),
(566, 2, 'Вміст кошику'),
(567, 1, 'Корзина пуста'),
(567, 2, 'Кошик порожній'),
(568, 1, 'Сумма'),
(568, 2, 'Сума'),
(569, 1, 'Количество'),
(569, 2, 'Кількість'),
(570, 1, 'Общая сумма'),
(570, 2, 'Загальна сума'),
(574, 1, 'Ваш заказ отправлен'),
(574, 2, 'Ваше замовлення відправлено'),
(575, 1, 'Сообщение системы электронного магазина'),
(575, 2, 'Повідомлення системи електронного магазину'),
(576, 1, 'Уведомление о оформлении заказа'),
(576, 2, 'Повідомлення про відправку замолення'),
(578, 1, 'Оформление заказа не увенчалось успехом Свяжитесь с администратором магазина для решения проблем'),
(578, 2, 'Оформлення замовлення зазнало невдачі'),
(579, 1, 'Пожалуйста, введите цифры изображенные на картинке ниже: '),
(579, 2, 'Будь-ласка, введіть цифри зображені на малюнку:'),
(580, 1, 'Этот электронный адрес уже используется. Пожалуйста, используйте другой\ne-mail.'),
(580, 2, 'Ця електронна адреса вже використовується. Будь-ласка, використайте\nінший e-mail.'),
(581, 1, 'Дата заказа'),
(581, 2, 'Дата замовлення'),
(582, 1, 'Список заказов'),
(582, 2, 'Список замовлень'),
(583, 1, 'Статус заказа'),
(583, 2, 'Статус замовлення'),
(584, 1, 'Комментарий администратора'),
(584, 2, 'Коментар адміністратора'),
(585, 1, 'Комментарий пользователя'),
(585, 2, 'Коментар замовника'),
(586, 1, 'Детали заказа'),
(586, 2, 'Деталі'),
(587, 1, 'Протокол'),
(587, 2, 'Протокол'),
(588, 1, 'Хост'),
(588, 2, 'Хост'),
(589, 1, 'Корневая папка'),
(589, 2, 'Коренева папка'),
(590, 1, 'Название сайта'),
(590, 2, 'Назва сайту'),
(591, 1, 'Базовый'),
(591, 2, 'Базовий'),
(592, 1, 'Вариант дизайна'),
(592, 2, 'Варіант'),
(593, 1, 'Мета keywords'),
(593, 2, 'Мета keywords'),
(594, 1, 'Мета описание'),
(594, 2, 'Мета опис'),
(595, 1, 'Содержимое страницы'),
(595, 2, 'Вміст сторінки'),
(596, 1, 'Макет страницы'),
(596, 2, 'Макет сторінки'),
(597, 1, 'Редактор разделов'),
(597, 2, 'Редактор розділів'),
(598, 1, 'Административная часть'),
(598, 2, 'Адміністративна частина'),
(599, 1, 'Google sitemap'),
(599, 2, 'Google sitemap'),
(600, 1, 'Пустой'),
(600, 2, 'Пустий'),
(601, 1, 'Базовый'),
(601, 2, 'Базовий'),
(602, 1, 'Проект'),
(602, 2, 'Проект'),
(604, 1, 'Список подразделов'),
(604, 2, 'Список підрозділів'),
(605, 1, 'Файловый репозиторий'),
(605, 2, 'Файловий репозиторій'),
(606, 1, 'Google sitemap'),
(606, 2, 'Google sitemap'),
(607, 1, 'Редактор языков'),
(607, 2, 'Редактор мов'),
(608, 1, 'Авторизация'),
(608, 2, 'Авторизація'),
(609, 1, 'Карта сайта'),
(609, 2, 'Карта сайту'),
(610, 1, 'Список новостей'),
(610, 2, 'Список новин'),
(611, 1, 'Регистрация'),
(611, 2, 'Реєстрація'),
(612, 1, 'Редактор новостей'),
(612, 2, 'Редактор новин'),
(613, 1, 'Восстановление пароля'),
(613, 2, 'Відновлення паролю'),
(614, 1, 'Редактор ролей'),
(614, 2, 'Редактор ролей'),
(617, 1, 'Текстовая страница'),
(617, 2, 'Текстова сторінка'),
(618, 1, 'Редактор сайтов'),
(618, 2, 'Редактор сайтів'),
(619, 1, 'Редактор переводов'),
(619, 2, 'Редактор перекладів'),
(620, 1, 'Профиль пользователя'),
(620, 2, 'Профіль користувача'),
(621, 1, 'Редактор пользователей'),
(621, 2, 'Редактор користувачів'),
(624, 1, 'Главные новости'),
(624, 2, 'Головні новини'),
(625, 1, 'Источник'),
(625, 2, 'Джерело'),
(626, 1, 'Имя сотрудника'),
(626, 2, 'Ім''я співробітника'),
(627, 1, 'Должность сотрудника'),
(627, 2, 'Посада співробітника'),
(628, 1, 'Краткий текст о сотруднике'),
(628, 2, 'Короткий текст про співробітника'),
(629, 1, 'Полный текст про сотрудника'),
(629, 2, 'Повний текст про співробітника'),
(630, 1, 'Вакансия открыта'),
(630, 2, 'Вакансія відкрита'),
(631, 1, 'Дата открытия вакансии'),
(631, 2, 'Дата відкриття вакансії'),
(632, 1, 'Дата закрытия вакансии'),
(632, 2, 'Дата закриття вакансії'),
(633, 1, 'Название вакансии'),
(633, 2, 'Назва вакансії'),
(634, 1, 'Контактный e-mail'),
(634, 2, 'Контактний e-mail'),
(635, 1, 'Сегмент URL вакансии'),
(635, 2, 'Сегмент URL вакансії'),
(636, 1, 'Краткое описание вакансии'),
(636, 2, 'Короткий опис вакансії'),
(637, 1, 'Полное описание вакансии'),
(637, 2, 'Повний опис вакансії'),
(638, 1, 'Дополнительная информация'),
(638, 2, 'Додаткова інформація'),
(639, 1, 'Загрузить файл'),
(639, 2, 'Завантажити файл'),
(640, 1, 'Вакансии'),
(640, 2, 'Вакансії'),
(641, 1, 'Проголосовать'),
(641, 2, 'Проголосувати'),
(642, 1, 'Вы уже проголосовали.'),
(642, 2, 'Ви вже проголосували.'),
(643, 1, 'Проекты'),
(643, 2, 'Проекти'),
(647, 1, 'Имя руководителя'),
(647, 2, 'Ім''я керівника'),
(651, 1, 'Должность руководителя'),
(651, 2, 'Посада керівника'),
(652, 1, 'Краткий текст о руководителе'),
(652, 2, 'Короткий текст про керівника'),
(653, 1, 'Полный текст про руководителя'),
(653, 2, 'Повний текст про керівника'),
(657, 1, 'Имя члена жюри'),
(657, 2, 'Ім''я члена жюрі'),
(658, 1, 'Краткий текст про члена жюри'),
(658, 2, 'Короткий текст про члена жюрі'),
(659, 1, 'Полный текст про члена жюри'),
(659, 2, 'Повний текст про члена жюрі'),
(660, 1, 'Скрыть версию'),
(660, 2, 'Сховати версію'),
(661, 1, 'Викна-новости'),
(661, 2, 'Вікна-новини'),
(665, 1, 'Дата начала кастинга'),
(665, 2, 'Дата початку кастингу'),
(666, 1, 'Название кастинга'),
(666, 2, 'Назва кастингу'),
(667, 1, 'Краткий текст о кастинге'),
(667, 2, 'Короткий текст про кастинг'),
(668, 1, 'Полный текст про кастинг'),
(668, 2, 'Повний текст про кастинг'),
(669, 1, 'Сегмент URI жанра'),
(669, 2, 'Сегмент URI жанру'),
(670, 1, 'Название жанра'),
(670, 2, 'Назва жанру'),
(671, 1, 'Название жанра (во множественном числе)'),
(671, 2, 'Назва жанру (в множині)'),
(672, 1, 'Аббревиатура жанра'),
(672, 2, 'Абревіатура жанру'),
(673, 1, 'Жанр передачи'),
(673, 2, 'Жанр передачі'),
(674, 1, 'Сегмент URI передачи'),
(674, 2, 'Сегмент URI передачі'),
(675, 1, 'Год выпуска'),
(675, 2, 'Рік випуску'),
(676, 1, 'Название передачи'),
(676, 2, 'Назва передачі'),
(677, 1, 'Краткий текст о передаче'),
(677, 2, 'Короткий текст про передачу'),
(678, 1, 'Полный текст про передачу'),
(678, 2, 'Повний текст про передачу'),
(679, 1, 'Производство'),
(679, 2, 'Виробництво'),
(680, 1, 'Режиссер'),
(680, 2, 'Режисер'),
(681, 1, 'В ролях'),
(681, 2, 'У ролях'),
(682, 1, 'Перенаправлять на страницу'),
(682, 2, 'Перенаправляти на сторінку'),
(683, 1, 'Файловый репозиторий'),
(683, 2, 'Файловий репозиторій'),
(684, 1, 'Ведущий'),
(684, 2, 'Ведучий'),
(686, 1, 'Дата публикации'),
(686, 2, 'Дата публікації'),
(687, 1, 'Дата публикации'),
(687, 2, 'Дата публікації'),
(688, 1, 'Описание'),
(688, 2, 'Опис'),
(689, 1, 'Подпись к картинке'),
(689, 2, 'Підпис до картинки'),
(690, 1, 'Введите Ваш логин (в качестве логина используется адрес Вашей\nэлектронной почты), желаемый никнейм (Ваше отображаемое имя) и полное\nимя. Система автоматически создаст пароль и отправит Вам по указанному\nэлектронному адресу.'),
(690, 2, 'Введіть Ваш логін (Ваша електронна адреса), бажаний нікнейм та повне\nім''я. Система автоматично створить пароль та надішле на вказану\nелектронну адресу.'),
(691, 1, 'Женский'),
(691, 2, 'Жіноча'),
(692, 1, 'Мужской'),
(692, 2, 'Чоловіча'),
(693, 1, 'Никнейм (Ваше отображаемое имя)'),
(693, 2, 'Нікнейм'),
(694, 1, 'Пол'),
(694, 2, 'Стать'),
(695, 1, 'Дата рождения'),
(695, 2, 'Дата народження'),
(696, 1, 'Из какого Вы города?'),
(696, 2, 'З якого Ви міста?'),
(697, 1, 'Аватар (100px х 100px)'),
(697, 2, 'Аватар (100px х 100px)'),
(698, 1, 'Текст под изображением'),
(698, 2, 'Текст під зображенням'),
(699, 1, 'Подпись под картинкой'),
(699, 2, 'Підпис під картинкою'),
(700, 1, 'Подпись под картинкой'),
(700, 2, 'Підпис під картинкою'),
(701, 1, 'Разрешить'),
(701, 2, 'Дозволити'),
(702, 1, 'Разрешено'),
(702, 2, 'Дозволено'),
(703, 1, 'Таргет'),
(703, 2, 'Таргет'),
(704, 1, 'Дата'),
(704, 2, 'Дата'),
(705, 1, 'Текст'),
(705, 2, 'Текст'),
(706, 1, 'Комментарии к новостям'),
(706, 2, 'Коментарі до новин'),
(707, 1, 'Родительский комментарий'),
(707, 2, 'Батьківський коментар'),
(708, 1, 'Программа'),
(708, 2, 'Програма'),
(709, 1, 'Период'),
(709, 2, 'Період'),
(710, 1, 'Активная'),
(710, 2, 'Активна'),
(711, 1, 'Время'),
(711, 2, 'Час'),
(712, 1, 'Название'),
(712, 2, 'Назва'),
(713, 1, 'Продолжительность'),
(713, 2, 'Тривалість'),
(715, 1, 'Аннотация'),
(715, 2, 'Анотація'),
(716, 1, 'Программа'),
(716, 2, 'Програма'),
(717, 1, 'Заголовок'),
(717, 2, 'Заголовок'),
(718, 1, 'Дата создания'),
(718, 2, 'Дата створення'),
(720, 1, 'Дата начала'),
(720, 2, 'Дата початку'),
(721, 1, 'Дата окончания'),
(721, 2, 'Дата закінчення'),
(722, 1, 'Текст'),
(722, 2, 'Текст'),
(723, 1, 'Количество голосов'),
(723, 2, 'Кількість голосів'),
(724, 1, 'Вариант ответа'),
(724, 2, 'Варіант відповіді'),
(725, 1, 'Вопрос'),
(725, 2, 'Питання'),
(726, 1, 'Сегодня'),
(726, 2, 'Сьогодні'),
(727, 1, 'Вчера'),
(727, 2, 'Вчора'),
(728, 1, 'Активный'),
(728, 2, 'Активний'),
(729, 1, 'Пн'),
(729, 2, 'Пн'),
(730, 1, 'Вт'),
(730, 2, 'Вт'),
(731, 1, 'Ср'),
(731, 2, 'Ср'),
(732, 1, 'Чт'),
(732, 2, 'Чт'),
(733, 1, 'Пт'),
(733, 2, 'Пт'),
(734, 1, 'Сб'),
(734, 2, 'Сб'),
(735, 1, 'Вс'),
(735, 2, 'Нд'),
(736, 1, 'Сегмент УРЛ'),
(736, 2, 'Сегмент УРЛ'),
(737, 1, 'Категории'),
(737, 2, 'Категорії'),
(738, 1, 'Название категории'),
(738, 2, 'Назва категорії'),
(739, 1, 'с'),
(739, 2, 'з'),
(740, 1, 'по'),
(740, 2, 'по'),
(742, 1, 'Добавить группу'),
(742, 2, 'Додати групу'),
(743, 1, 'Добавить ссылку'),
(743, 2, 'Додати посилання'),
(744, 1, 'Название группы'),
(744, 2, 'Назва групи'),
(745, 1, 'Элементы'),
(745, 2, 'Елементи'),
(748, 1, 'Название элемента'),
(748, 2, 'Назва елементу'),
(749, 1, 'УРЛ элемента'),
(749, 2, 'УРЛ елементу'),
(750, 1, 'Группа'),
(750, 2, 'Група'),
(752, 1, 'подробнее'),
(752, 2, 'детальніше'),
(753, 1, 'Предыдущая<br/>\r\nнеделя'),
(753, 2, 'Попередній<br/>\r\nтиждень'),
(755, 1, 'Сегодня на СТБ'),
(755, 2, 'Сьогодні на СТБ'),
(756, 1, 'Все расписание'),
(756, 2, 'Повний розклад'),
(757, 1, 'Будьте с нами'),
(757, 2, 'Будьте з нами'),
(758, 1, 'читать все новости'),
(758, 2, 'читати усі новини'),
(759, 1, 'Смотреть по проекту'),
(759, 2, 'Фільтрувати по проектах'),
(760, 1, 'Все проекты'),
(760, 2, 'Усі проекти'),
(761, 1, 'Фильтровать по жанрам'),
(761, 2, 'Фільтрувати за жанрами'),
(762, 1, 'Все жанры'),
(762, 2, 'Усі жанри'),
(763, 1, 'Все'),
(763, 2, 'Усі'),
(764, 1, 'смотреть все'),
(764, 2, 'дивитися все'),
(765, 1, 'Порт'),
(765, 2, 'Порт'),
(766, 1, 'Скопировать структуру с'),
(766, 2, 'Скопіювати структуру з'),
(767, 1, 'Адрес'),
(767, 2, 'Адреса'),
(768, 1, 'Группа'),
(768, 2, 'Група'),
(769, 1, 'Премьера'),
(769, 2, 'Прем''єра'),
(770, 1, 'Название'),
(770, 2, 'Назва'),
(771, 1, 'Категории форума'),
(771, 2, 'Категорії форуму'),
(772, 1, 'Название'),
(772, 2, 'Назва'),
(773, 1, 'Описание'),
(773, 2, 'Опис'),
(774, 1, 'Закрыта'),
(774, 2, 'Закрита'),
(775, 1, 'Категория'),
(775, 2, 'Категорія'),
(776, 1, 'Название'),
(776, 2, 'Назва'),
(777, 1, 'Текст'),
(777, 2, 'Текст'),
(778, 1, 'Закрыта'),
(778, 2, 'Закрита'),
(779, 1, 'Количество комментариев'),
(779, 2, 'Кількість коментарів'),
(780, 1, 'Дата создания'),
(780, 2, 'Дата створення'),
(781, 1, 'Комментарий'),
(781, 2, 'Коментар'),
(782, 1, 'Редактор тем'),
(782, 2, 'Редактор тем'),
(783, 1, 'Необходимо загрузить файл'),
(783, 2, 'Необхідно завантажити файл'),
(784, 1, 'Загрузить файл'),
(784, 2, 'Завантажити файл'),
(785, 1, 'Топ видео'),
(785, 2, 'Топ відео'),
(786, 1, 'Голосование'),
(786, 2, 'Опитування'),
(787, 1, 'смотреть все видео'),
(787, 2, 'дивитися все відео'),
(788, 1, 'Фоновое изображения'),
(788, 2, 'Фонове зображення'),
(790, 1, 'Адрес ссылки'),
(790, 2, 'Адреса посилання'),
(791, 1, 'Файл 996x316(высокая шапка),996x166(низкая шапка)'),
(791, 2, 'Файл 996x316(высокая шапка),996x166(низкая шапка)'),
(792, 1, 'Высокая'),
(792, 2, 'Висока'),
(793, 1, 'Первая строчка в шапке'),
(793, 2, 'Перший рядок'),
(794, 1, 'Вторая строчка'),
(794, 2, 'Другий рядок'),
(795, 1, 'Третья строчка'),
(795, 2, 'Третій рядок'),
(796, 1, 'Смотрите:'),
(796, 2, 'Дивіться:'),
(797, 1, 'Дата'),
(797, 2, 'Дата'),
(798, 1, 'Название'),
(798, 2, 'Назва'),
(799, 1, 'Краткий текст'),
(799, 2, 'Короткий текст'),
(800, 1, 'Текст'),
(800, 2, 'Текст'),
(811, 1, 'Вакансии'),
(811, 2, 'Вакансії'),
(812, 1, 'Новое видео'),
(812, 2, 'Нове відео'),
(813, 1, 'Кастинги'),
(813, 2, 'Кастінги'),
(817, 1, 'Участник выбыл'),
(817, 2, 'Учасник вибув'),
(818, 1, 'Имя'),
(818, 2, 'Ім''я'),
(819, 1, 'Город'),
(819, 2, 'Місто'),
(820, 1, 'Возраст'),
(820, 2, 'Вік'),
(821, 1, 'Аннотация'),
(821, 2, 'Анотація'),
(822, 1, 'Текст'),
(822, 2, 'Текст'),
(823, 1, 'Дополнительная информация'),
(823, 2, 'Додаткова інформація'),
(824, 1, 'Фамилия'),
(824, 2, 'Прізвище'),
(825, 1, 'Главная страница сайта СТБ'),
(825, 2, 'Головна сторінка сайту СТБ'),
(826, 1, 'Главная страница проекта'),
(826, 2, 'Головна сторінка проекту'),
(827, 1, 'Новое фото'),
(827, 2, 'Нове фото'),
(828, 1, 'Топ фото'),
(828, 2, 'Топ фото'),
(829, 1, 'смотреть все фото'),
(829, 2, 'дивитися усі фото'),
(830, 1, 'Фото'),
(830, 2, 'Фото'),
(841, 1, 'Форма обратной связи'),
(841, 2, 'Форма зворотнього зв''язку'),
(842, 1, 'Список обратной связи'),
(842, 2, 'Список зворотнього зв''язку'),
(848, 1, 'Видео'),
(848, 2, 'Відео'),
(856, 1, 'Редактор категорий новостей'),
(856, 2, 'Редактор категорій новин'),
(858, 1, 'Редактор тегов'),
(858, 2, 'Редактор тегів'),
(865, 1, 'Список дочерних разделов админчасти'),
(865, 2, 'Список дочірніх розділів адмінчастини'),
(867, 1, 'Дата'),
(867, 2, 'Дата'),
(868, 1, 'Добавить галерею'),
(868, 2, 'Додати фотогалерею'),
(871, 1, 'Видео проекта'),
(871, 2, 'Відео проекта'),
(872, 1, 'Подпись под картинкой'),
(872, 2, 'Подпись под картинкой'),
(873, 1, 'Смотреть'),
(873, 2, 'Дивитись'),
(874, 1, 'Топ проекта'),
(874, 2, 'Топ проекта'),
(875, 1, 'Выводить на главную страницу'),
(875, 2, 'Виводити на головну сторінку'),
(876, 1, 'Топ'),
(876, 2, 'Топ'),
(877, 1, 'Выводить на главную страницу проекта'),
(877, 2, 'Виводити на головну сторінку проекту'),
(878, 1, 'Премьера'),
(878, 2, 'Прем''єра'),
(879, 1, 'Все'),
(879, 2, 'Усі'),
(880, 1, 'Сейчас'),
(880, 2, 'Зараз'),
(881, 1, 'Выводить на главную'),
(881, 2, 'Виводити на головну'),
(882, 1, 'Главная новость дня'),
(882, 2, 'Головна новина дня'),
(883, 1, 'Главная новость дня'),
(883, 2, 'Головна новина дня'),
(884, 1, 'не указан'),
(884, 2, 'не вказаний'),
(886, 1, 'Регистрация'),
(886, 2, 'Реєстрація'),
(898, 1, 'Верхнее правое изображение'),
(898, 2, 'Верхнє праве зображення'),
(899, 1, 'Отступ верхнего правого изображения'),
(899, 2, 'Відступ верхнього правого зображення'),
(904, 1, 'Нижнее фоновое изображение'),
(904, 2, 'Нижнє фонове зображення'),
(905, 1, 'Свойства нижнего фонового изображения'),
(905, 2, 'Властивості нижнього фонового зображення'),
(906, 1, 'Верхнее фоновое изображение'),
(906, 2, 'Верхнє фонове зображення'),
(907, 1, 'Свойства верхнего фонового изображения'),
(907, 2, 'Властивості верхнього фонового зображення'),
(910, 1, 'Изменить размеры изображения до стандартных'),
(910, 2, 'Змінити розміри зображення до стандартних'),
(911, 1, 'Новая тема'),
(911, 2, 'Нова тема'),
(912, 1, 'Ответить'),
(912, 2, 'Відповісти'),
(914, 1, 'Пол'),
(914, 2, 'Стать'),
(915, 1, 'Откуда'),
(915, 2, 'Звідки'),
(916, 1, 'Код AdOcean баннера 300х250'),
(916, 2, 'Код AdOcean баннера 300х250'),
(917, 1, 'Код AdOcean баннера 728х90');
INSERT INTO `share_lang_tags_translation` (`ltag_id`, `lang_id`, `ltag_value_rtf`) VALUES
(917, 2, 'Код AdOcean баннера 728х90'),
(918, 1, 'Код RichMedia баннера AdOcean'),
(918, 2, 'Код RichMedia баннера AdOcean'),
(919, 1, 'Управление набором шапок'),
(919, 2, 'Управління набором шапрк'),
(920, 1, 'Название набора'),
(920, 2, 'Название набора'),
(923, 1, 'Изображение'),
(923, 2, 'Зображення'),
(924, 1, 'URL'),
(924, 2, 'URL'),
(925, 1, 'Первый блок'),
(925, 2, 'Перший блок'),
(926, 1, 'Второй блок'),
(926, 2, 'Другий блок'),
(927, 1, 'Третий блок'),
(927, 2, 'Третій блок'),
(928, 1, 'Набор шапок'),
(928, 2, 'Набір шапок'),
(929, 1, 'Здравствуйте, Вы оставили сообщение на сайте http://www.stb.ua.'),
(929, 2, 'Доброго дня, Ви залишили повідомлення на сайті http://www.stb.ua.'),
(930, 1, 'Сообщение с сайта СТБ'),
(930, 2, 'Повідомлення з сайту СТБ'),
(931, 1, 'Новое сообщение на сайте СТБ'),
(931, 2, 'Нове повідомлення на сайті СТБ'),
(932, 1, 'На сайте СТБ оставлено новое сообщение. Текст сообщения: $feed_text\nАвтор: $feed_author Email автора: $feed_email\n<p>С уважением, STB.UA.</p>\n'),
(932, 2, 'На сайті СТБ залишено нове повідомлення. Текст повідомлення: $feed_text\nАвтор: $feed_author Email автора: $feed_email\n<p>С уважением, STB.UA.</p>\n'),
(933, 1, 'Тем'),
(933, 2, 'Тем'),
(934, 1, 'Сообщений'),
(934, 2, 'Повідомлень'),
(935, 1, 'Последний комментарий'),
(935, 2, 'Останній коментар'),
(936, 1, 'Последние сообщения'),
(936, 2, 'Останні повідомлення'),
(937, 1, 'читать весь форум'),
(937, 2, 'читати весь форум'),
(938, 1, 'Горячие темы'),
(938, 2, 'Гарячі теми'),
(939, 1, 'Комментариев'),
(939, 2, 'Коментарів'),
(940, 1, 'последний'),
(940, 2, 'останній'),
(942, 1, 'Редактировать'),
(942, 2, 'Редагувати'),
(943, 1, 'Удалить'),
(943, 2, 'Видалити'),
(944, 1, 'Тема'),
(944, 2, 'Тема'),
(945, 1, 'Последний комментарий'),
(945, 2, 'Останній коментар'),
(946, 1, 'Комментариев'),
(946, 2, 'Коментарів'),
(947, 1, 'Тем'),
(947, 2, 'Тем'),
(948, 1, 'Комментарии отсутствуют'),
(948, 2, 'Коментарі відсутні'),
(949, 1, 'Дата вывода'),
(949, 2, 'Дата виводу'),
(950, 1, 'Предыдущий теледень'),
(950, 2, 'Попередній теледень'),
(952, 1, 'Пароли не совпадают'),
(952, 2, 'Паролі не співпадають'),
(953, 1, 'Возраст'),
(953, 2, 'Вік'),
(954, 1, 'Откуда'),
(954, 2, 'Звідки'),
(955, 1, 'Участник выбыл'),
(955, 2, 'Учасник вибув'),
(957, 1, 'Базовый без боковой колонки'),
(957, 2, 'Базовый без боковой колонки'),
(958, 1, 'Ник'),
(958, 2, 'Ник'),
(962, 1, 'Вакансия открыта'),
(962, 2, 'Вакансія відкрита'),
(963, 1, 'и действительна до'),
(963, 2, 'і дійсна до'),
(964, 1, 'Контактный e-mail'),
(964, 2, 'Контактний e-mail'),
(965, 1, 'смотреть все вакансии'),
(965, 2, 'дивитись всі вакансії'),
(966, 1, 'Активный'),
(966, 2, 'Активний'),
(967, 1, 'Название'),
(967, 2, 'Назва'),
(968, 1, 'Редактор конкурса'),
(968, 2, 'Редактор конкурсу'),
(969, 1, 'Конкурсанты'),
(969, 2, 'Конкурсанти'),
(971, 1, 'Редактор конкурсантов'),
(971, 2, 'Редактор конкурсантів'),
(972, 1, 'Количество'),
(972, 2, 'Кількість'),
(973, 1, 'Проценты'),
(973, 2, 'Проценти'),
(974, 1, 'Описание'),
(974, 2, 'Опис'),
(978, 1, 'Искать на СТБ'),
(978, 2, 'Шукати на СТБ'),
(979, 1, 'смотреть все кастинги'),
(979, 2, 'переглянути усі кастінги'),
(983, 1, 'Поддержали'),
(983, 2, 'Підтримали'),
(984, 1, 'Голосовать'),
(984, 2, 'Голосувати'),
(985, 1, 'Неверный код'),
(985, 2, 'Невірний код'),
(986, 1, 'Список сообщений с формы обратной связи'),
(986, 2, 'Список повідомлень з форми зворотнього зв''язку'),
(987, 1, 'Редактор заказов артистов'),
(987, 2, 'Редактор запитів артистів'),
(988, 1, 'Сообщение от пользователя с формы обратной связи'),
(988, 2, 'Повідомлення від користувача з форми зворотнього зв''язку'),
(989, 1, '<br>\n<br>\nEMAIL пользователя: $feed_email<br>\nАвтор: $feed_author<br>\nТелефон: $feed_phone<br>\n<br>\nСообщение: $feed_text'),
(989, 2, '<br>\n<br>\nEMAIL користувача: $feed_email<br>\nАвтор: $feed_author<br>\nТелефон: $feed_phone<br>\n<br>\nПовідомлення: $feed_text'),
(990, 1, '<br>\n<br>\nEMAIL пользователя: $feed_email<br>\nАвтор: $feed_author<br>\nТелефон: $feed_phone<br>\n<br>\nТема: $feed_topic<br>\n<br>\nСообщение: $feed_text'),
(990, 2, '<br>\n<br>\nEMAIL користувача: $feed_email<br>\nАвтор: $feed_author<br>\nТелефон: $feed_phone<br>\n<br>\nТема: $feed_topic<br>\n<br>\nПовідомлення: $feed_text'),
(991, 1, 'Телефон'),
(991, 2, 'Телефон'),
(993, 1, 'Тема'),
(993, 2, 'Тема'),
(995, 1, 'Название'),
(995, 2, 'Назва'),
(996, 1, 'Email'),
(996, 2, 'Email'),
(1000, 1, 'Сообщение'),
(1000, 2, 'Повідомлення'),
(1001, 1, 'Автор'),
(1001, 2, 'Автор'),
(1002, 1, 'EMAIL пользователя'),
(1002, 2, 'EMAIL користувача'),
(1003, 1, 'Сообщение с формы обратной связи'),
(1003, 2, 'Повідомлення з форми зворотнього зв''язку'),
(1004, 1, 'Список сообщений с формы обратной связи'),
(1004, 2, 'Список повідомлень з форми зворотнього зв''язку'),
(1006, 1, 'Тема сообщения'),
(1006, 2, 'Тема повідомлення'),
(1007, 1, 'Телефон'),
(1007, 2, 'Телефон'),
(1009, 1, 'AdOcean код для видео преролла'),
(1009, 2, 'AdOcean код для видео преролла'),
(1010, 1, 'Участники'),
(1010, 2, 'Учасники'),
(1011, 1, 'Видео'),
(1011, 2, 'Відео'),
(1012, 1, 'Похожие новости'),
(1012, 2, 'Схожі новини'),
(1014, 1, 'IP'),
(1014, 2, 'IP'),
(1015, 1, 'Редактор запрещенных IP'),
(1015, 2, 'Редактор заборонених IP'),
(1016, 1, 'Время бана'),
(1016, 2, 'Час бану'),
(1017, 1, 'на день'),
(1017, 2, 'на день'),
(1018, 1, 'на неделю'),
(1018, 2, 'на тиждень'),
(1019, 1, 'на месяц'),
(1019, 2, 'на місяць'),
(1020, 1, 'на год'),
(1020, 2, 'на рік'),
(1021, 1, 'навсегда'),
(1021, 2, 'назавжди'),
(1022, 1, 'Редактор бана пользователя'),
(1022, 2, 'Редактор бана користувача'),
(1023, 1, 'Время бана'),
(1023, 2, 'Час бану'),
(1024, 1, 'Удалить бан'),
(1024, 2, 'Видалити бан'),
(1025, 1, 'Перейти на страницу'),
(1025, 2, 'Перейти на сторінку'),
(1026, 1, 'Отправить по e-mail'),
(1026, 2, 'Надіслати e-mail'),
(1027, 1, 'Распечатать страницу'),
(1027, 2, 'Роздрукувати сторінку'),
(1028, 1, 'Добавить в избранное'),
(1028, 2, 'Додати у вибране'),
(1029, 1, 'ВКонтакт'),
(1029, 2, 'ВКонтакт'),
(1030, 1, 'В Facebook'),
(1030, 2, 'У Facebook'),
(1031, 1, 'Затвитить'),
(1031, 2, 'Затвітити'),
(1032, 1, 'Запостить в ЖЖ'),
(1032, 2, 'Запостити у ЖЖ'),
(1033, 1, 'просмотров'),
(1033, 2, 'переглядів'),
(1034, 1, 'фото'),
(1034, 2, 'фото'),
(1037, 1, 'Бан'),
(1037, 2, 'Бан'),
(1045, 1, 'Параметры'),
(1045, 2, 'Параметри'),
(1049, 1, 'Данные с сайта'),
(1049, 2, 'Дані з сайту'),
(1053, 1, 'Порядок сортировки'),
(1053, 2, 'Порядок сортування'),
(1057, 1, 'Заголовок блока'),
(1057, 2, 'Заголовок блоку'),
(1061, 1, 'Количество записей во вкладке'),
(1061, 2, 'Кількість записів у вкладці'),
(1065, 1, 'Количество вкладок'),
(1065, 2, 'Кількість вкладок'),
(1069, 1, 'Количество записей'),
(1069, 2, 'Кількість записів'),
(1073, 1, 'Шаблон вывода'),
(1073, 2, 'Шаблон виводу'),
(1077, 1, 'Маска вывода'),
(1077, 2, 'Маска виводу'),
(1081, 1, 'Идентификатор блока'),
(1081, 2, 'Ідентифікатор блоку'),
(1085, 1, 'Бан по IP'),
(1085, 2, 'Бан по IP'),
(1089, 1, 'Сбросить в дефолтное состояние'),
(1089, 2, 'Скинути у дефолтний стан'),
(1093, 1, 'Изменено'),
(1093, 2, 'Змінено'),
(1097, 1, 'Редактор исходного кода текстового блока'),
(1097, 2, 'Редактор вихідного коду текстового блоку'),
(1105, 1, 'Сбросить шаблоны'),
(1105, 2, 'Скинути шаблони'),
(1109, 1, 'Новости проекта'),
(1109, 2, 'Новини проекту'),
(1113, 1, 'Название блока'),
(1113, 2, 'Назва блоку'),
(1117, 1, 'Иконка блока'),
(1117, 2, 'Іконка блоку'),
(1121, 1, 'Вставить блок'),
(1121, 2, 'Вставити блок'),
(1125, 1, 'Редактор блоков'),
(1125, 2, 'Редактор блоків'),
(1129, 1, 'Только популярные'),
(1129, 2, 'Тільки популярні');

-- --------------------------------------------------------

--
-- Table structure for table `share_session`
--

DROP TABLE IF EXISTS `share_session`;
CREATE TABLE IF NOT EXISTS `share_session` (
  `session_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_native_id` char(40) NOT NULL,
  `session_last_impression` int(11) NOT NULL,
  `session_created` int(11) NOT NULL,
  `session_expires` int(11) NOT NULL,
  `session_ip` int(4) unsigned DEFAULT NULL,
  `session_user_agent` char(255) NOT NULL,
  `u_id` int(10) unsigned DEFAULT NULL,
  `session_data` varchar(5000) DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `session_native_id` (`session_native_id`),
  KEY `i_session_u_id` (`u_id`),
  KEY `i_session_ip` (`session_ip`),
  KEY `session_expires` (`session_expires`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `share_session`
--

INSERT INTO `share_session` (`session_id`, `session_native_id`, `session_last_impression`, `session_created`, `session_expires`, `session_ip`, `session_user_agent`, `u_id`, `session_data`) VALUES
(1, 'bb499b5543b258b21081695a6ffe071af0203341', 1295010575, 1294996194, 1295118575, 2130706433, '', 22, 'userID|i:22;news_id|a:1:{s:7:"smap_id";i:1255;}');

-- --------------------------------------------------------

--
-- Table structure for table `share_sitemap`
--

DROP TABLE IF EXISTS `share_sitemap`;
CREATE TABLE IF NOT EXISTS `share_sitemap` (
  `smap_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned NOT NULL,
  `smap_layout` char(200) NOT NULL,
  `smap_layout_xml` text NOT NULL,
  `smap_content` char(200) NOT NULL,
  `smap_content_xml` text NOT NULL,
  `smap_pid` int(10) unsigned DEFAULT NULL,
  `smap_segment` char(50) NOT NULL DEFAULT '',
  `smap_order_num` int(10) unsigned DEFAULT '1',
  `smap_redirect_url` char(250) DEFAULT NULL,
  PRIMARY KEY (`smap_id`),
  UNIQUE KEY `smap_pid` (`smap_pid`,`site_id`,`smap_segment`),
  KEY `site_id` (`site_id`),
  KEY `smap_order_num` (`smap_order_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1257 ;

--
-- Dumping data for table `share_sitemap`
--

INSERT INTO `share_sitemap` (`smap_id`, `site_id`, `smap_layout`, `smap_layout_xml`, `smap_content`, `smap_content_xml`, `smap_pid`, `smap_segment`, `smap_order_num`, `smap_redirect_url`) VALUES
(80, 1, 'default.layout.xml', '', 'main.content.xml', '', NULL, '', 224, NULL),
(329, 1, 'default.layout.xml', '', 'map.content.xml', '', 80, 'sitemap', 174, NULL),
(330, 1, 'default.layout.xml', '', 'default/restore_password.content.xml', '', 80, 'restore-password', 207, NULL),
(331, 1, 'default.layout.xml', '', 'default/register.content.xml', '', 80, 'registration', 205, NULL),
(351, 1, 'google_sitemap.layout.xml', '', 'google_sitemap.content.xml', '', 80, 'google-sitemap', 210, NULL),
(483, 1, 'default.layout.xml', '', 'default/user_profile.content.xml', '', 80, 'profile', 206, NULL),
(1254, 1, 'default.layout.xml', '', 'feedback_form.content.xml', '', 80, 'feedback', 209, NULL),
(1255, 1, 'default.layout.xml', '', 'news.content.xml', '', 80, 'feed', 1, NULL),
(1256, 1, 'default.layout.xml', '', 'textblock.content.xml', '\r\n', 80, 'text', 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `share_sitemap_tags`
--

DROP TABLE IF EXISTS `share_sitemap_tags`;
CREATE TABLE IF NOT EXISTS `share_sitemap_tags` (
  `smap_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`smap_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `share_sitemap_tags`
--

INSERT INTO `share_sitemap_tags` (`smap_id`, `tag_id`) VALUES
(1255, 1),
(1256, 1);

-- --------------------------------------------------------

--
-- Table structure for table `share_sitemap_translation`
--

DROP TABLE IF EXISTS `share_sitemap_translation`;
CREATE TABLE IF NOT EXISTS `share_sitemap_translation` (
  `smap_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `smap_name` varchar(200) DEFAULT NULL,
  `smap_description_rtf` text,
  `smap_html_title` varchar(250) DEFAULT NULL,
  `smap_meta_keywords` text,
  `smap_meta_description` text,
  `smap_is_disabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lang_id`,`smap_id`),
  KEY `sitemaplv_sitemap_FK` (`smap_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `share_sitemap_translation`
--

INSERT INTO `share_sitemap_translation` (`smap_id`, `lang_id`, `smap_name`, `smap_description_rtf`, `smap_html_title`, `smap_meta_keywords`, `smap_meta_description`, `smap_is_disabled`) VALUES
(80, 1, 'Главная страница', NULL, 'Телеканал СТБ, смотреть СТБ онлайн, программа СТБ, СТБ видео, телепрограмма СТБ, СТБ Украина', NULL, '<meta name=''yandex-verification'' content=''421f31c92020f4c9'' />', 0),
(329, 1, 'Карта сайта', NULL, NULL, NULL, NULL, 0),
(330, 1, 'Восстановление пароля', NULL, NULL, NULL, NULL, 0),
(331, 1, 'Регистрация', NULL, NULL, NULL, NULL, 0),
(351, 1, 'Google sitemap page', NULL, NULL, NULL, NULL, 0),
(483, 1, 'Профиль', NULL, NULL, NULL, NULL, 0),
(1254, 1, 'Обратная связь', NULL, NULL, NULL, NULL, 0),
(1255, 1, 'Новости', NULL, NULL, NULL, NULL, 0),
(1256, 1, 'Просто текстовая страница', NULL, NULL, NULL, NULL, 0),
(80, 2, 'Головна сторінка', NULL, 'Телеканал СТБ, дивитися СТБ онлайн, програма СТБ, СТБ відео, телепрограма СТБ, СТБ Україна', NULL, '<meta name=''yandex-verification'' content=''421f31c92020f4c9'' />', 0),
(329, 2, 'Карта сайту', NULL, NULL, NULL, NULL, 0),
(330, 2, 'Поновлення паролю', NULL, NULL, NULL, NULL, 0),
(331, 2, 'Реєстрація', NULL, NULL, NULL, NULL, 0),
(351, 2, 'Google sitemap page', NULL, NULL, NULL, NULL, 0),
(483, 2, 'Профіль', NULL, NULL, NULL, NULL, 0),
(1254, 2, 'Зворотній зв''язок', NULL, NULL, NULL, NULL, 0),
(1255, 2, 'Новини', NULL, NULL, NULL, NULL, 0),
(1256, 2, 'Просто текстова сторінка', NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `share_sitemap_uploads`
--

DROP TABLE IF EXISTS `share_sitemap_uploads`;
CREATE TABLE IF NOT EXISTS `share_sitemap_uploads` (
  `smap_id` int(10) unsigned NOT NULL,
  `upl_id` int(10) unsigned NOT NULL,
  `upl_order_num` int(10) unsigned NOT NULL,
  PRIMARY KEY (`smap_id`,`upl_id`,`upl_order_num`),
  KEY `upl_id` (`upl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `share_sitemap_uploads`
--


-- --------------------------------------------------------

--
-- Table structure for table `share_sites`
--

DROP TABLE IF EXISTS `share_sites`;
CREATE TABLE IF NOT EXISTS `share_sites` (
  `site_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site_is_active` tinyint(1) NOT NULL DEFAULT '1',
  `site_is_indexed` tinyint(1) NOT NULL DEFAULT '1',
  `site_protocol` char(5) NOT NULL DEFAULT 'http',
  `site_host` char(50) NOT NULL,
  `site_port` smallint(5) unsigned NOT NULL DEFAULT '80',
  `site_root` char(50) NOT NULL DEFAULT '/',
  `site_is_default` tinyint(1) NOT NULL DEFAULT '0',
  `site_folder` char(20) NOT NULL DEFAULT 'default',
  `site_order_num` int(10) unsigned DEFAULT '1',
  PRIMARY KEY (`site_id`),
  UNIQUE KEY `site_host` (`site_host`,`site_root`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `share_sites`
--

INSERT INTO `share_sites` (`site_id`, `site_is_active`, `site_is_indexed`, `site_protocol`, `site_host`, `site_port`, `site_root`, `site_is_default`, `site_folder`, `site_order_num`) VALUES
(1, 1, 1, 'http', 'energine-2.6.dev', 80, '/', 1, 'default', 1);

-- --------------------------------------------------------

--
-- Table structure for table `share_sites_tags`
--

DROP TABLE IF EXISTS `share_sites_tags`;
CREATE TABLE IF NOT EXISTS `share_sites_tags` (
  `site_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`site_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `share_sites_tags`
--


-- --------------------------------------------------------

--
-- Table structure for table `share_sites_translation`
--

DROP TABLE IF EXISTS `share_sites_translation`;
CREATE TABLE IF NOT EXISTS `share_sites_translation` (
  `site_id` int(11) unsigned NOT NULL,
  `lang_id` int(11) unsigned NOT NULL,
  `site_name` char(200) NOT NULL,
  `site_meta_keywords` text,
  `site_meta_description` text,
  PRIMARY KEY (`site_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `share_sites_translation`
--

INSERT INTO `share_sites_translation` (`site_id`, `lang_id`, `site_name`, `site_meta_keywords`, `site_meta_description`) VALUES
(1, 1, 'Демо версия Energine-2.6', '', NULL),
(1, 2, 'Демо версія Energine-2.6', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `share_tags`
--

DROP TABLE IF EXISTS `share_tags`;
CREATE TABLE IF NOT EXISTS `share_tags` (
  `tag_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag_name` char(100) NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag_name` (`tag_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `share_tags`
--

INSERT INTO `share_tags` (`tag_id`, `tag_name`) VALUES
(1, 'menu');

-- --------------------------------------------------------

--
-- Table structure for table `share_textblocks`
--

DROP TABLE IF EXISTS `share_textblocks`;
CREATE TABLE IF NOT EXISTS `share_textblocks` (
  `tb_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `smap_id` int(10) unsigned DEFAULT NULL,
  `tb_num` char(50) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tb_id`),
  UNIQUE KEY `smap_id` (`smap_id`,`tb_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `share_textblocks`
--

INSERT INTO `share_textblocks` (`tb_id`, `smap_id`, `tb_num`) VALUES
(1, NULL, 'FooterTextBlock'),
(2, 80, '1'),
(3, 80, '2'),
(4, 1255, '1'),
(5, 1256, '1');

-- --------------------------------------------------------

--
-- Table structure for table `share_textblocks_translation`
--

DROP TABLE IF EXISTS `share_textblocks_translation`;
CREATE TABLE IF NOT EXISTS `share_textblocks_translation` (
  `tb_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tb_content` text NOT NULL,
  UNIQUE KEY `tb_id` (`tb_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `share_textblocks_translation`
--

INSERT INTO `share_textblocks_translation` (`tb_id`, `lang_id`, `tb_content`) VALUES
(1, 1, 'Energine 2.6 <br>2011'),
(1, 2, 'sdsdsdsddsdssdsdfdfddf'),
(2, 1, '\n          <p>&nbsp;dfdfdfdfdfdfdghghhgghghjjjjjjjjjjjjjjjjjjjjjjjjgffggf</p><p>ghgghhgghfdfdfdfdfdfjjjjjjjjjjjjj66666666666666666666666fgfgfg</p>\n        '),
(2, 2, '\n          <p>saasassaas<br></p>\n        '),
(3, 1, '\n          <ul><li>&nbsp;<i>dffddfdfdfhghghjfgfgfgfg</i></li><li><i>fddfjfgfggf</i></li></ul>\n        '),
(3, 2, 'saasasasassaasasas'),
(4, 1, '\n          <p>&nbsp;</p>\n        '),
(5, 1, '\n          <p>&nbsp;На этой странице просто текст</p>\n        ');

-- --------------------------------------------------------

--
-- Table structure for table `share_uploads`
--

DROP TABLE IF EXISTS `share_uploads`;
CREATE TABLE IF NOT EXISTS `share_uploads` (
  `upl_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `upl_path` varchar(250) NOT NULL,
  `upl_name` varchar(250) NOT NULL DEFAULT '',
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
  PRIMARY KEY (`upl_id`),
  UNIQUE KEY `upl_path` (`upl_path`),
  KEY `upl_views` (`upl_views`),
  KEY `upl_is_ready` (`upl_is_ready`),
  KEY `upl_publication_date_index` (`upl_publication_date`),
  KEY `abc` (`upl_id`,`upl_is_ready`,`upl_views`),
  KEY `abcd` (`upl_id`,`upl_is_ready`,`upl_views`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `share_uploads`
--


--
-- Triggers `share_uploads`
--
DROP TRIGGER IF EXISTS `insert_before_share_uploads`;
DELIMITER //
CREATE TRIGGER `insert_before_share_uploads` BEFORE INSERT ON `share_uploads`
 FOR EACH ROW begin
  IF isnull(NEW.upl_publication_date) THEN
    SET NEW.upl_publication_date = now( ) ;
  end if;
end
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `share_uploads_tags`
--

DROP TABLE IF EXISTS `share_uploads_tags`;
CREATE TABLE IF NOT EXISTS `share_uploads_tags` (
  `upl_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`upl_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `share_uploads_tags`
--


-- --------------------------------------------------------

--
-- Table structure for table `share_widgets`
--

DROP TABLE IF EXISTS `share_widgets`;
CREATE TABLE IF NOT EXISTS `share_widgets` (
  `widget_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `widget_name` varchar(250) NOT NULL,
  `widget_xml` text NOT NULL,
  `widget_icon_img` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`widget_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `share_widgets`
--


-- --------------------------------------------------------

--
-- Table structure for table `user_ban_ips`
--

DROP TABLE IF EXISTS `user_ban_ips`;
CREATE TABLE IF NOT EXISTS `user_ban_ips` (
  `ban_ip_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ban_ip` int(4) unsigned NOT NULL,
  `ban_ip_end_date` date NOT NULL,
  PRIMARY KEY (`ban_ip_id`),
  UNIQUE KEY `i_ban_ip_uniq` (`ban_ip`),
  KEY `i_ban_ip_end_date` (`ban_ip_end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `user_ban_ips`
--


-- --------------------------------------------------------

--
-- Table structure for table `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE IF NOT EXISTS `user_groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` char(50) NOT NULL DEFAULT '',
  `group_default` tinyint(1) NOT NULL DEFAULT '0',
  `group_user_default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`),
  KEY `group_default` (`group_default`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `user_groups`
--

INSERT INTO `user_groups` (`group_id`, `group_name`, `group_default`, `group_user_default`) VALUES
(1, 'Администратор', 0, 0),
(3, 'Гость', 1, 0),
(4, 'Пользователь', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_group_rights`
--

DROP TABLE IF EXISTS `user_group_rights`;
CREATE TABLE IF NOT EXISTS `user_group_rights` (
  `right_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `right_name` char(20) NOT NULL DEFAULT '',
  `right_const` char(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`right_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `user_group_rights`
--

INSERT INTO `user_group_rights` (`right_id`, `right_name`, `right_const`) VALUES
(1, 'Read only', 'ACCESS_READ'),
(2, 'Edit', 'ACCESS_EDIT'),
(3, 'Full control', 'ACCESS_FULL');

-- --------------------------------------------------------

--
-- Table structure for table `user_users`
--

DROP TABLE IF EXISTS `user_users`;
CREATE TABLE IF NOT EXISTS `user_users` (
  `u_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `u_name` varchar(50) NOT NULL DEFAULT '',
  `u_password` varchar(40) NOT NULL DEFAULT '',
  `u_is_active` tinyint(1) NOT NULL DEFAULT '1',
  `u_fullname` varchar(250) NOT NULL,
  `u_nick` varchar(250) DEFAULT NULL,
  `u_avatar_img` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`u_id`),
  UNIQUE KEY `u_login` (`u_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `user_users`
--

INSERT INTO `user_users` (`u_id`, `u_name`, `u_password`, `u_is_active`, `u_fullname`, `u_nick`, `u_avatar_img`) VALUES
(22, 'demo@energine.org', '89e495e7941cf9e40e6980d14a16bf023ccd4c91', 1, 'Admin', 'Admins', 'uploads/avatars/12871413545331.jpg');

--
-- Triggers `user_users`
--
DROP TRIGGER IF EXISTS `insert_user_users`;
DELIMITER //
CREATE TRIGGER `insert_user_users` BEFORE INSERT ON `user_users`
 FOR EACH ROW BEGIN
    if length(trim(NEW.u_nick)) = 0 then
        set NEW.u_nick = NEW.u_fullname;
    end if;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `update_user_users`;
DELIMITER //
CREATE TRIGGER `update_user_users` BEFORE UPDATE ON `user_users`
 FOR EACH ROW BEGIN
    if length(trim(NEW.u_nick)) = 0 then
        set NEW.u_nick = NEW.u_fullname;
    end if;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_users_ban`
--

DROP TABLE IF EXISTS `user_users_ban`;
CREATE TABLE IF NOT EXISTS `user_users_ban` (
  `u_id` int(10) unsigned NOT NULL,
  `ban_date` date NOT NULL,
  KEY `u_id` (`u_id`),
  KEY `ban_date` (`ban_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_users_ban`
--


-- --------------------------------------------------------

--
-- Table structure for table `user_user_groups`
--

DROP TABLE IF EXISTS `user_user_groups`;
CREATE TABLE IF NOT EXISTS `user_user_groups` (
  `u_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`u_id`,`group_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_user_groups`
--

INSERT INTO `user_user_groups` (`u_id`, `group_id`) VALUES
(22, 1);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `apps_news`
--
ALTER TABLE `apps_news`
  ADD CONSTRAINT `apps_news_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `apps_news_translation`
--
ALTER TABLE `apps_news_translation`
  ADD CONSTRAINT `apps_news_translation_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `apps_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `apps_news_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `apps_news_uploads`
--
ALTER TABLE `apps_news_uploads`
  ADD CONSTRAINT `apps_news_uploads_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `apps_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `apps_news_uploads_ibfk_2` FOREIGN KEY (`upl_id`) REFERENCES `share_uploads` (`upl_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `image_photo_gallery`
--
ALTER TABLE `image_photo_gallery`
  ADD CONSTRAINT `image_photo_gallery_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `image_photo_gallery_ibfk_2` FOREIGN KEY (`pg_photo_img`) REFERENCES `share_uploads` (`upl_path`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `image_photo_gallery_translation`
--
ALTER TABLE `image_photo_gallery_translation`
  ADD CONSTRAINT `image_photo_gallery_translation_ibfk_1` FOREIGN KEY (`pg_id`) REFERENCES `image_photo_gallery` (`pg_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `image_photo_gallery_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Constraints for table `share_access_level`
--
ALTER TABLE `share_access_level`
  ADD CONSTRAINT `share_access_level_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `share_access_level_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `share_access_level_ibfk_3` FOREIGN KEY (`right_id`) REFERENCES `user_group_rights` (`right_id`) ON DELETE CASCADE;

--
-- Constraints for table `share_lang_tags_translation`
--
ALTER TABLE `share_lang_tags_translation`
  ADD CONSTRAINT `FK_Reference_6` FOREIGN KEY (`ltag_id`) REFERENCES `share_lang_tags` (`ltag_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_tranaslatelv_language` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Constraints for table `share_sitemap`
--
ALTER TABLE `share_sitemap`
  ADD CONSTRAINT `share_sitemap_ibfk_8` FOREIGN KEY (`smap_pid`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `share_sitemap_ibfk_9` FOREIGN KEY (`site_id`) REFERENCES `share_sites` (`site_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `share_sitemap_tags`
--
ALTER TABLE `share_sitemap_tags`
  ADD CONSTRAINT `share_sitemap_tags_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `share_sitemap_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `share_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `share_sitemap_translation`
--
ALTER TABLE `share_sitemap_translation`
  ADD CONSTRAINT `FK_sitemaplv_language` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_sitemaplv_sitemap` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE;

--
-- Constraints for table `share_sitemap_uploads`
--
ALTER TABLE `share_sitemap_uploads`
  ADD CONSTRAINT `share_sitemap_uploads_ibfk_3` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `share_sitemap_uploads_ibfk_4` FOREIGN KEY (`upl_id`) REFERENCES `share_uploads` (`upl_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `share_sites_tags`
--
ALTER TABLE `share_sites_tags`
  ADD CONSTRAINT `share_sites_tags_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `share_sites` (`site_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `share_sites_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `share_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `share_sites_translation`
--
ALTER TABLE `share_sites_translation`
  ADD CONSTRAINT `share_sites_translation_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `share_sites` (`site_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `share_sites_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `share_textblocks`
--
ALTER TABLE `share_textblocks`
  ADD CONSTRAINT `share_textblocks_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE;

--
-- Constraints for table `share_textblocks_translation`
--
ALTER TABLE `share_textblocks_translation`
  ADD CONSTRAINT `share_textblocks_translation_ibfk_1` FOREIGN KEY (`tb_id`) REFERENCES `share_textblocks` (`tb_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `share_textblocks_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Constraints for table `share_uploads_tags`
--
ALTER TABLE `share_uploads_tags`
  ADD CONSTRAINT `share_uploads_tags_ibfk_1` FOREIGN KEY (`upl_id`) REFERENCES `share_uploads` (`upl_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `share_uploads_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `share_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_user_groups`
--
ALTER TABLE `user_user_groups`
  ADD CONSTRAINT `user_user_groups_ibfk_3` FOREIGN KEY (`u_id`) REFERENCES `user_users` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_user_groups_ibfk_4` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
