-- phpMyAdmin SQL Dump
-- version 2.11.5
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Апр 10 2008 г., 18:20
-- Версия сервера: 5.0.51
-- Версия PHP: 5.2.5

SET FOREIGN_KEY_CHECKS=0;

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

SET AUTOCOMMIT=0;
START TRANSACTION;

--
-- База данных: `energine2_demo`
--

-- --------------------------------------------------------

--
-- Структура таблицы `image_photo_gallery`
--

CREATE TABLE IF NOT EXISTS `image_photo_gallery` (
  `pg_id` int(10) unsigned NOT NULL auto_increment,
  `smap_id` int(10) unsigned NOT NULL default '0',
  `pg_thumb_img` varchar(250) NOT NULL default '',
  `pg_photo_img` varchar(200) NOT NULL default '',
  `pg_order_num` int(10) unsigned default '1',
  PRIMARY KEY  (`pg_id`),
  KEY `smap_id` (`smap_id`),
  KEY `pg_photo_img` (`pg_photo_img`),
  KEY `pg_order_num` (`pg_order_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Дамп данных таблицы `image_photo_gallery`
--

INSERT INTO `image_photo_gallery` (`pg_id`, `smap_id`, `pg_thumb_img`, `pg_photo_img`, `pg_order_num`) VALUES
(3, 334, 'uploads/public/gallery-2/1207230416.jpg', 'uploads/public/gallery-2/1207230392.jpg', 8),
(4, 334, 'uploads/public/gallery-2/1207230584.jpg', 'uploads/public/gallery-2/1207230560.jpg', 7),
(5, 334, 'uploads/public/gallery-2/1207230622.jpg', 'uploads/public/gallery-2/1207230602.jpg', 6),
(6, 334, 'uploads/public/gallery-2/1207230656.jpg', 'uploads/public/gallery-2/1207230637.jpg', 5),
(8, 333, 'uploads/public/gallery-1/1207235036.jpg', 'uploads/public/gallery-1/1207235019.jpg', 3),
(9, 333, 'uploads/public/gallery-1/1207235073.jpg', 'uploads/public/gallery-1/1207235056.jpg', 2),
(10, 333, 'uploads/public/gallery-1/1207235111.jpg', 'uploads/public/gallery-1/1207235092.jpg', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `image_photo_gallery_translation`
--

CREATE TABLE IF NOT EXISTS `image_photo_gallery_translation` (
  `pg_id` int(11) unsigned NOT NULL default '0',
  `lang_id` int(11) unsigned NOT NULL default '0',
  `pg_title` varchar(250) default NULL,
  `pg_text` text,
  PRIMARY KEY  (`pg_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `image_photo_gallery_translation`
--

INSERT INTO `image_photo_gallery_translation` (`pg_id`, `lang_id`, `pg_title`, `pg_text`) VALUES
(3, 1, 'Название фотографии', NULL),
(3, 2, 'Название фотографии', NULL),
(3, 3, 'Название фотографии', NULL),
(4, 1, 'Фотокамера Canon EOS', NULL),
(4, 2, 'Фотокамера Canon EOS', NULL),
(4, 3, 'Фотокамера Canon EOS', NULL),
(5, 1, 'ЖК монитор Samsung', NULL),
(5, 2, 'ЖК монитор Samsung', NULL),
(5, 3, 'ЖК монитор Samsung', NULL),
(6, 1, 'Фотокамера Olympus', NULL),
(6, 2, 'Фотокамера Olympus', NULL),
(6, 3, 'Фотокамера Olympus', NULL),
(8, 1, 'Кран', NULL),
(8, 2, 'Кран', NULL),
(8, 3, 'Кран', NULL),
(9, 1, 'Самосвал', NULL),
(9, 2, 'Самосвал', NULL),
(9, 3, 'Самосвал', NULL),
(10, 1, 'Эвакуатор', NULL),
(10, 2, 'Эвакуатор', NULL),
(10, 3, 'Эвакуатор', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `share_access_level`
--

CREATE TABLE IF NOT EXISTS `share_access_level` (
  `smap_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  `right_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`smap_id`,`group_id`,`right_id`),
  KEY `group_id` (`group_id`),
  KEY `right_id` (`right_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `share_access_level`
--

INSERT INTO `share_access_level` (`smap_id`, `group_id`, `right_id`) VALUES
(7, 1, 3),
(8, 1, 3),
(9, 1, 3),
(73, 1, 3),
(80, 1, 3),
(156, 1, 3),
(158, 1, 3),
(161, 1, 3),
(288, 1, 3),
(292, 1, 3),
(324, 1, 3),
(327, 1, 3),
(329, 1, 3),
(330, 1, 3),
(331, 1, 3),
(332, 1, 3),
(333, 1, 3),
(334, 1, 3),
(335, 1, 3),
(336, 1, 3),
(337, 1, 3),
(338, 1, 3),
(339, 1, 3),
(341, 1, 3),
(342, 1, 3),
(343, 1, 3),
(344, 1, 3),
(345, 1, 3),
(346, 1, 3),
(348, 1, 3),
(349, 1, 3),
(350, 1, 3),
(351, 1, 3),
(352, 1, 3),
(353, 1, 3),
(354, 1, 3),
(355, 1, 3),
(357, 1, 3),
(358, 1, 3),
(359, 1, 3),
(360, 1, 3),
(361, 1, 3),
(80, 3, 1),
(161, 3, 1),
(288, 3, 1),
(324, 3, 1),
(327, 3, 1),
(329, 3, 1),
(330, 3, 1),
(331, 3, 1),
(332, 3, 1),
(333, 3, 1),
(334, 3, 1),
(336, 3, 1),
(338, 3, 1),
(339, 3, 1),
(341, 3, 1),
(344, 3, 1),
(345, 3, 1),
(350, 3, 1),
(351, 3, 1),
(352, 3, 1),
(353, 3, 1),
(354, 3, 1),
(355, 3, 1),
(357, 3, 1),
(358, 3, 1),
(359, 3, 1),
(360, 3, 1),
(361, 3, 1),
(80, 4, 1),
(161, 4, 1),
(288, 4, 1),
(324, 4, 1),
(327, 4, 1),
(329, 4, 1),
(330, 4, 1),
(331, 4, 1),
(332, 4, 1),
(333, 4, 1),
(334, 4, 1),
(335, 4, 1),
(336, 4, 1),
(338, 4, 1),
(339, 4, 1),
(341, 4, 1),
(344, 4, 1),
(345, 4, 1),
(348, 4, 1),
(349, 4, 1),
(350, 4, 1),
(351, 4, 1),
(352, 4, 1),
(353, 4, 1),
(354, 4, 1),
(355, 4, 1),
(357, 4, 1),
(358, 4, 1),
(359, 4, 1),
(360, 4, 1),
(361, 4, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `share_feedback`
--

CREATE TABLE IF NOT EXISTS `share_feedback` (
  `feed_id` int(10) unsigned NOT NULL auto_increment,
  `feed_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `feed_email` varchar(200) NOT NULL default '',
  `feed_author` varchar(250) default NULL,
  `feed_text` text NOT NULL,
  PRIMARY KEY  (`feed_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `share_feedback`
--

INSERT INTO `share_feedback` (`feed_id`, `feed_date`, `feed_email`, `feed_author`, `feed_text`) VALUES
(1, '2008-04-04 13:57:35', 'd.pavka@gmail.com', NULL, 'Оппа'),
(2, '2008-04-04 17:08:45', 'test@test.test', 'test', 'test'),
(3, '2008-04-04 17:14:10', 'test@test.test', 'test', 'test');

-- --------------------------------------------------------

--
-- Структура таблицы `share_languages`
--

CREATE TABLE IF NOT EXISTS `share_languages` (
  `lang_id` int(10) unsigned NOT NULL auto_increment,
  `lang_abbr` varchar(2) NOT NULL default '',
  `lang_name` varchar(20) NOT NULL default '',
  `lang_default` tinyint(1) NOT NULL default '0',
  `lang_order_num` int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (`lang_id`),
  KEY `idx_abbr` (`lang_abbr`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `share_languages`
--

INSERT INTO `share_languages` (`lang_id`, `lang_abbr`, `lang_name`, `lang_default`, `lang_order_num`) VALUES
(1, 'ru', 'Русский', 1, 1),
(2, 'ua', 'Українська', 0, 2),
(3, 'en', 'English', 0, 3);

-- --------------------------------------------------------

--
-- Структура таблицы `share_lang_tags`
--

CREATE TABLE IF NOT EXISTS `share_lang_tags` (
  `ltag_id` int(10) unsigned NOT NULL auto_increment,
  `ltag_name` varchar(70) NOT NULL default '',
  `ltag_description` text,
  PRIMARY KEY  (`ltag_id`),
  UNIQUE KEY `ltag_name` (`ltag_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=445 ;

--
-- Дамп данных таблицы `share_lang_tags`
--

INSERT INTO `share_lang_tags` (`ltag_id`, `ltag_name`, `ltag_description`) VALUES
(14, 'FIELD_LTAG_VALUE', NULL),
(15, 'BTN_LOGOUT', NULL),
(42, 'BTN_SAVE', NULL),
(43, 'BTN_ADD', NULL),
(45, 'BTN_RETURN_LIST', NULL),
(46, 'BTN_EDIT', NULL),
(47, 'BTN_DELETE', NULL),
(49, 'FIELD_LTAG_NAME', NULL),
(50, 'FIELD_U_NAME', NULL),
(52, 'FIELD_U_PASSWORD', NULL),
(53, 'FIELD_U_PASSWORD2', NULL),
(54, 'BTN_REGISTER', NULL),
(55, 'BTN_CHANGE', NULL),
(56, 'BTN_EDIT_MODE', NULL),
(57, 'BTN_ADD_PAGE', NULL),
(58, 'BTN_EDIT_PAGE', NULL),
(59, 'BTN_DELETE_PAGE', NULL),
(60, 'BTN_VIEWMODESWITCHER', NULL),
(61, 'BTN_VIEWSOURCE', NULL),
(62, 'BTN_SET_ROLE', NULL),
(63, 'FIELD_GROUP_ID', NULL),
(64, 'TXT_ERRORS', NULL),
(65, 'ERR_CANT_DELETE_YOURSELF', NULL),
(66, 'FIELD_GROUP_NAME', NULL),
(67, 'FIELD_GROUP_DEFAULT', NULL),
(68, 'FIELD_GROUP_USER_DEFAULT', NULL),
(69, 'FIELD_LANG_ABBR', NULL),
(70, 'FIELD_LANG_NAME', NULL),
(71, 'FIELD_LANG_DEFAULT', NULL),
(72, 'FIELD_LANG_LOCALE', NULL),
(73, 'FIELD_LANG_WIN_CODE', NULL),
(74, 'BTN_SET_RIGHTS', NULL),
(75, 'FIELD_TMPL_NAME', NULL),
(76, 'FIELD_TMPL_LAYOUT', NULL),
(77, 'FIELD_TMPL_CONTENT', NULL),
(78, 'FIELD_TMPL_IS_SYSTEM', NULL),
(79, 'BTN_LOGIN', NULL),
(80, 'TXT_SUCCESS', NULL),
(81, 'TXT_PROPERTIES', NULL),
(82, 'TXT_DETAIL_INFO', NULL),
(83, 'TXT_ORDER', NULL),
(96, 'ERR_404', NULL),
(109, 'FIELD_LTAG_VALUE_RTF', NULL),
(110, 'TXT_VIEW_ITEM', NULL),
(112, 'TXT_EDIT_ITEM', NULL),
(115, 'FIELD_CG_DESCRIPTION_RTF', NULL),
(121, 'TXT_DIV_EDITOR', NULL),
(122, 'FIELD_SMAP_PID', NULL),
(123, 'FIELD_SMAP_SEGMENT', NULL),
(124, 'FIELD_TMPL_ID', NULL),
(125, 'FIELD_SMAP_ORDER_NUM', NULL),
(126, 'FIELD_SMAP_IS_FINAL', NULL),
(127, 'FIELD_SMAP_DEFAULT', NULL),
(128, 'FIELD_SMAP_IS_DISABLED', NULL),
(129, 'FIELD_SMAP_NAME', NULL),
(130, 'FIELD_SMAP_DESCRIPTION_RTF', NULL),
(131, 'FIELD_SMAP_META_KEYWORDS', NULL),
(133, 'FIELD_SMAP_META_DESCRIPTION', NULL),
(134, 'BTN_CLOSE', NULL),
(135, 'TXT_REGISTRATION_SUCCESS', NULL),
(136, 'MSG_FIELD_IS_NOT_NULL', NULL),
(143, 'TXT_SHIT_HAPPENS', NULL),
(144, 'MSG_BAD_EMAIL_FORMAT', NULL),
(145, 'MSG_BAD_PHONE_FORMAT', NULL),
(146, 'MSG_BAD_FLOAT_FORMAT', NULL),
(152, 'FIELD_TOTAL_COST', NULL),
(154, 'MSG_CONFIRM_DELETE', NULL),
(156, 'TXT_NO_RIGHTS', NULL),
(157, 'BTN_VIEW', NULL),
(158, 'FIELD_IMG_FILENAME_IMG', NULL),
(159, 'FIELD_IMG_DESCRIPTION', NULL),
(160, 'FIELD_IMG_THUMBNAIL_IMG', NULL),
(161, 'BTN_INSERT_IMAGE', NULL),
(162, 'FIELD_IMG_FILENAME', NULL),
(163, 'FIELD_IMG_WIDTH', NULL),
(164, 'FIELD_IMG_HEIGHT', NULL),
(165, 'FIELD_IMG_HSPACE', NULL),
(166, 'FIELD_IMG_VSPACE', NULL),
(167, 'FIELD_IMG_ALTTEXT', NULL),
(168, 'BTN_INSERT', NULL),
(169, 'BTN_IMAGELIB', NULL),
(170, 'ERR_403', NULL),
(171, 'BTN_IMAGE_MANAGER', NULL),
(172, 'FIELD_IMG_ALIGN', NULL),
(173, 'TXT_ALIGN_BOTTOM', NULL),
(174, 'TXT_ALIGN_MIDDLE', NULL),
(175, 'TXT_ALIGN_TOP', NULL),
(176, 'TXT_ALIGN_LEFT', NULL),
(177, 'TXT_ALIGN_RIGHT', NULL),
(178, 'MSG_BAD_FORMAT', NULL),
(179, 'MSG_BAD_USER_NAME_FORMAT', NULL),
(186, 'TXT_TEMPLATE_EDITOR', NULL),
(190, 'BTN_GO_TO_ADMIN', NULL),
(205, 'TXT_DOWNLOAD_FILE', NULL),
(207, 'FIELD_PRODUCT_ID', NULL),
(208, 'FIELD_PRODUCT_PRICE', NULL),
(209, 'FIELD_BASKET_COUNT', NULL),
(210, 'FIELD_PRODUCT_SUMM', NULL),
(211, 'TXT_BASKET_SUMM', NULL),
(212, 'TXT_ANOTHER_PRODUCTS', NULL),
(213, 'TXT_PRODUCT_PRICE', NULL),
(214, 'BTN_GO', NULL),
(215, 'BTN_ADD_TO_BASKET', NULL),
(216, 'TXT_PAGES', NULL),
(217, 'FIELD_PRODUCT_DESCRIPTION_RTF', NULL),
(218, 'BTN_RECOUNT', NULL),
(219, 'BTN_ORDER', NULL),
(220, 'TXT_PRODUCTTYPEEDITOR', NULL),
(221, 'BTN_PARAMS_LIST', NULL),
(222, 'FIELD_PT_NAME', NULL),
(223, 'FIELD_PP_NAME', NULL),
(224, 'FIELD_PP_TYPE', NULL),
(225, 'TXT_PARAM_TYPE_STRING', NULL),
(226, 'TXT_PARAM_TYPE_INT', NULL),
(227, 'TXT_PARAM_TYPE_TEXT', NULL),
(228, 'TXT_PARAM_TYPE_BOOL', NULL),
(229, 'TXT_PARAM_TYPE_FLOAT', NULL),
(230, 'TXT_PRODUCEREDITOR', NULL),
(231, 'FIELD_PRODUCER_NAME', NULL),
(232, 'FIELD_PRODUCER_SEGMENT', NULL),
(233, 'FIELD_PRODUCT_IS_AVAILABLE', NULL),
(234, 'FIELD_PRODUCT_NAME', NULL),
(235, 'BTN_SHOW_PARAMS', NULL),
(236, 'FIELD_SMAP_ID', NULL),
(237, 'BTN_SELECT', NULL),
(238, 'TXT_DIVISION_EDITOR', NULL),
(239, 'FIELD_PRODUCT_CODE', NULL),
(240, 'FIELD_PRODUCT_COUNT', NULL),
(241, 'FIELD_PT_ID', NULL),
(242, 'FIELD_PRODUCER_ID', NULL),
(243, 'FIELD_PRODUCT_SEGMENT', NULL),
(244, 'FIELD_PRODUCT_PHOTO_IMG', NULL),
(245, 'FIELD_PRODUCT_SHORT_DESCRIPTION_RTF', NULL),
(246, 'FIELD_PPV_VALUE', NULL),
(247, 'FIELD_PP_ID', NULL),
(248, 'TXT_BASKET_EMPTY', NULL),
(249, 'TXT_PRODUCT_PARAMS', NULL),
(250, 'BTN_MOVE_UP', NULL),
(251, 'BTN_MOVE_DOWN', NULL),
(252, 'TXT_ORDER_CLIENT_SUBJECT', NULL),
(253, 'TXT_ORDER_CLIENT_MAIL_BODY', NULL),
(255, 'TXT_IMAGE_LIBRARY', NULL),
(256, 'TXT_ORDER_SEND', NULL),
(257, 'TXT_LOAD_PRICE', NULL),
(258, 'FIELD_FILE_NAME', NULL),
(259, 'FIELD_LOAD_TYPE', NULL),
(260, 'BTN_LOAD', NULL),
(261, 'MSG_NO_FILE', NULL),
(262, 'MSG_IMPORT_FAILED', NULL),
(263, 'ERR_DATABASE_ERROR', NULL),
(264, 'MSG_IMPORT_SUCCESS', NULL),
(265, 'FIELD_REMEMBER_LOGIN', NULL),
(266, 'FIELD_ORDER_ADDRESS', NULL),
(267, 'FIELD_u_contact_person', NULL),
(268, 'FIELD_u_delivery_comment', NULL),
(269, 'BTN_SEND', NULL),
(270, 'TXT_ORDER_FORM', NULL),
(271, 'FIELD_OS_PRIORITY', NULL),
(272, 'FIELD_OS_NAME', NULL),
(273, 'TXT_USER_EDITOR', NULL),
(274, 'TXT_ROLE_EDITOR', NULL),
(275, 'TXT_RIGHTS', NULL),
(276, 'FIELD_ORDER_CREATED', NULL),
(277, 'FIELD_U_ID', NULL),
(278, 'TXT_ORDERHISTORY', NULL),
(279, 'TXT_LANGUAGE_EDITOR', NULL),
(280, 'BTN_VIEW_DETAILS', NULL),
(281, 'FIELD_OS_ID', NULL),
(282, 'FIELD_ORDER_COMMENT', NULL),
(283, 'TXT_ORDERDETAILS', NULL),
(284, 'BTN_SEARCH', NULL),
(285, 'TXT_SEARCH_CATALOGUE', NULL),
(286, 'TXT_USER_NAME', NULL),
(287, 'TXT_USER_GREETING', NULL),
(288, 'TXT_BASKET_CONTENTS', NULL),
(289, 'TXT_ORDER_NEW_CLIENT_MAIL_BODY', NULL),
(290, 'FIELD_DSCNT_NAME', NULL),
(291, 'FIELD_DSCNT_PERCENT', NULL),
(292, 'TXT_DISCOUNTSEDITOR', NULL),
(293, 'TXT_SUBJ_RESTORE_PASSWORD', NULL),
(294, 'TXT_BODY_RESTORE_PASSWORD', NULL),
(295, 'ERR_NO_U_NAME', NULL),
(296, 'MSG_PASSWORD_SENT', NULL),
(297, 'TXT_FORGOT_PWD', NULL),
(298, 'TXT_DISCOUNT', NULL),
(299, 'FIELD_PRODUCT_PRICE_WITH_DISCOUNT', NULL),
(300, 'FIELD_SMAP_LINK_ID', NULL),
(301, 'TXT_BASKET_SUMM_WITH_DISCOUNT', NULL),
(302, 'TXT_BAD_SEGMENT_FORMAT', NULL),
(303, 'ERR_NOT_UNIQUE_DATA', NULL),
(304, 'TXT_USER_GROUPS', NULL),
(305, 'FIELD_LTAG_DESCRIPTION', NULL),
(306, 'FIELD_CURR_NAME', NULL),
(307, 'FIELD_CURR_RATE', NULL),
(308, 'FIELD_CURR_ABBR', NULL),
(309, 'FIELD_CURR_IS_MAIN', NULL),
(310, 'FIELD_CURR_FORMAT', NULL),
(312, 'BTN_OPEN', NULL),
(313, 'BTN_ADD_DIR', NULL),
(314, 'BTN_RENAME', NULL),
(315, 'FIELD_CURR_ID', NULL),
(319, 'TXT_ACCESS_FULL', NULL),
(320, 'TXT_ACCESS_EDIT', NULL),
(321, 'TXT_ACCESS_READ', NULL),
(322, 'TXT_THUMB', NULL),
(323, 'BTN_DIV_EDITOR', NULL),
(324, 'BTN_TMPL_EDITOR', NULL),
(325, 'TXT_USER_PROFILE_SAVED', NULL),
(326, 'TXT_FILTER', NULL),
(327, 'BTN_APPLY_FILTER', NULL),
(328, 'TXT_RESET_FILTER', NULL),
(329, 'FIELD_TAGS', NULL),
(330, 'BTN_SAVE_GO', NULL),
(331, 'MSG_START_EDITING', NULL),
(332, 'BTN_GO_BASKET', NULL),
(333, 'TXT_BASKET_SUMM2', NULL),
(334, 'TXT_REM_SUBJ', NULL),
(336, 'FIELD_UPL_NAME', NULL),
(339, 'ERR_DEFAULT_GROUP', NULL),
(340, 'FIELD_GROUP_DEFAULT_RIGHTS', NULL),
(341, 'TXT_ROLE_DIV_RIGHTS', NULL),
(342, 'TXT_SEARCH_RESULT', NULL),
(343, 'MSG_EMPTY_SEARCH_RESULT', NULL),
(344, 'TXT_HOME', NULL),
(345, 'FIELD_u_main_phone', NULL),
(346, 'BTN_UPDATE', NULL),
(347, 'BTN_CANCEL', NULL),
(348, 'FIELD_TEXTBLOCK_SOURCE', NULL),
(349, 'FIELD_u_company_name', NULL),
(350, 'FIELD_u_company_address', NULL),
(351, 'FIELD_u_company_fis_address', NULL),
(352, 'FIELD_u_company_nds_number', NULL),
(353, 'FIELD_u_company_tax_number', NULL),
(354, 'FIELD_u_company_fax', NULL),
(355, 'FIELD_u_get_order_by_email', NULL),
(356, 'FIELD_u_get_order_by_fax', NULL),
(357, 'BTN_FILE_REPOSITORY', NULL),
(358, 'MSG_ORDER_FAILED', NULL),
(359, 'FIELD_ps_id', NULL),
(360, 'FIELD_ps_name', NULL),
(361, 'FIELD_ps_is_default', NULL),
(362, 'FIELD_ps_is_visible', NULL),
(364, 'TXT_ORDER_DELIMITER', NULL),
(365, 'BTN_VIEW_PROFILE', NULL),
(366, 'FIELD_order_detail', NULL),
(367, 'FIELD_NEW_PASSWORD', NULL),
(368, 'FIELD_u_address', NULL),
(370, 'TXT_GENERAL_INFORMATION', NULL),
(371, 'TXT_ENTER_PASSWORD', NULL),
(372, 'FIELD_CHANGE_U_PASSWORD', NULL),
(373, 'FIELD_CHANGE_U_PASSWORD2', NULL),
(374, 'MSG_PWD_MISMATCH', NULL),
(375, 'TXT_USER_PROFILE_WRONG_PWD', NULL),
(376, 'ERR_BAD_LOGIN', NULL),
(377, 'FIELD_message', NULL),
(378, 'TXT_ORDER_MANAGER_MAIL_BODY', NULL),
(379, 'FIELD_order_delivery_comment', NULL),
(381, 'BTN_TRANS_EDITOR', NULL),
(382, 'TXT_DIVISIONS', NULL),
(383, 'ERR_CANT_MOVE', NULL),
(384, 'FIELD_smap_html_title', NULL),
(385, 'TXT_PREVIEW', NULL),
(386, 'MSG_SWITCHER_TIP', NULL),
(387, 'TXT_CURRENCY_RATE', NULL),
(388, 'FIELD_u_phone', NULL),
(389, 'ERR_DEV_NO_DATA', NULL),
(390, 'ERR_BASKET_IS_EMPTY', NULL),
(391, 'FIELD_news_title', NULL),
(392, 'FIELD_news_date', NULL),
(393, 'FIELD_news_announce_rtf', NULL),
(394, 'FIELD_news_text_rtf', NULL),
(395, 'MSG_WRONG_DATETIME_FORMAT', NULL),
(396, 'TXT_FILELIBRARY', NULL),
(397, 'FIELD_upl_path', NULL),
(398, 'FIELD_U_GROUP', NULL),
(399, 'TXT_ROLE_TEXT', NULL),
(400, 'TXT_CHILD_DIVISIONS', NULL),
(401, 'TXT_LOGIN_FORM', NULL),
(402, 'TXT_ORDER_TITLE', NULL),
(403, 'TXT_CONSUMER_INFO', NULL),
(404, 'TXT_ORDER_INFO', NULL),
(405, 'FIELD_ORGANIZATION_NAME', NULL),
(406, 'FIELD_CONTACT_PERSON', NULL),
(407, 'FIELD_CONTACT_PERSON_POSITION', NULL),
(408, 'FIELD_CONTACT_PHONE', NULL),
(409, 'FIELD_CONTACT_EMAIL', NULL),
(410, 'FIELD_CONTACT_ADDRESS', NULL),
(411, 'FIELD_WEBSITE', NULL),
(412, 'FIELD_PRODUCT_CATEGORY', NULL),
(413, 'FIELD_MODEL', NULL),
(414, 'FIELD_PRODUCT_NUMBER', NULL),
(415, 'FIELD_ADDITIONAL_EQUIPMENT', NULL),
(416, 'FIELD_FIELD_OF_USE', NULL),
(417, 'FIELD_COMMENTS', NULL),
(418, 'TXT_CATALOGUE_LINK', NULL),
(419, 'TXT_CONTACTS_TITLE', NULL),
(420, 'TXT_OPEN_FIELD', NULL),
(421, 'TXT_CLOSE_FIELD', NULL),
(422, 'DUMMY_EMAIL', NULL),
(424, 'MSG_REQUEST_SENT', NULL),
(426, 'BTN_ADD_NEWS', NULL),
(427, 'BTN_EDIT_NEWS', NULL),
(428, 'BTN_DELETE_NEWS', NULL),
(429, 'TXT_BACK_TO_LIST', NULL),
(430, 'TXT_PARTNERS_TITLE', NULL),
(431, 'TXT_SITEMAP', NULL),
(432, 'TXT_CONTACTS', NULL),
(433, 'BTN_ADD_PHOTO', NULL),
(434, 'BTN_EDIT_PHOTO', NULL),
(435, 'BTN_DELETE_PHOTO', NULL),
(436, 'FIELD_feed_text', NULL),
(437, 'FIELD_feed_author', NULL),
(438, 'FIELD_feed_email', NULL),
(439, 'TXT_REQUIRED_FIELDS', NULL),
(440, 'TXT_FEEDBACK_SUCCESS_SEND', NULL),
(441, 'TXT_USER_REGISTRED', NULL),
(442, 'FIELD_feed_date', NULL),
(443, 'TXT_FEEDBACKLIST', NULL),
(444, 'FIELD_UPL_FILE', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `share_lang_tags_translation`
--

CREATE TABLE IF NOT EXISTS `share_lang_tags_translation` (
  `ltag_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `ltag_value_rtf` text NOT NULL,
  PRIMARY KEY  (`ltag_id`,`lang_id`),
  KEY `FK_tranaslatelv_language` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `share_lang_tags_translation`
--

INSERT INTO `share_lang_tags_translation` (`ltag_id`, `lang_id`, `ltag_value_rtf`) VALUES
(14, 1, 'Значение'),
(14, 2, 'Значення'),
(14, 3, 'Value'),
(15, 1, 'Выйти'),
(15, 2, 'Вийти'),
(15, 3, 'Exit'),
(42, 1, 'Сохранить'),
(42, 2, 'Зберегти'),
(42, 3, 'Save'),
(43, 1, 'Добавить'),
(43, 2, 'Додати'),
(43, 3, 'Add'),
(45, 1, 'Просмотреть список'),
(45, 2, 'Продивитися список'),
(45, 3, 'View list'),
(46, 1, 'Редактировать'),
(46, 2, 'Редагувати'),
(46, 3, 'Edit'),
(47, 1, 'Удалить'),
(47, 2, 'Видалити'),
(47, 3, 'Delete'),
(49, 1, 'Имя тега'),
(49, 2, 'Назва тега'),
(49, 3, 'Tag name'),
(50, 1, 'Логин (e-mail)'),
(50, 2, 'Логін (e-mail)'),
(50, 3, 'Login (e-mail)'),
(52, 1, 'Пароль'),
(52, 2, 'Пароль'),
(52, 3, 'Password'),
(53, 1, 'Повторите пароль'),
(53, 2, 'Повторіть пароль'),
(53, 3, 'Repeat password'),
(54, 1, 'Зарегистрироваться'),
(54, 2, 'Зареєструватись'),
(54, 3, 'Register'),
(55, 1, 'Сохранить изменения'),
(55, 2, 'Зберегти зміни'),
(55, 3, 'Save changes'),
(56, 1, 'Режим редактирования'),
(56, 2, 'Режим редагування'),
(56, 3, 'Edit mode'),
(57, 1, 'Добавить страницу'),
(57, 2, 'Додати сторінку'),
(57, 3, 'Add page'),
(58, 1, 'Редактировать страницу'),
(58, 2, 'Редагувати сторінку'),
(58, 3, 'Edit page'),
(59, 1, 'Удалить страницу'),
(59, 2, 'Видалити сторінку'),
(59, 3, 'Delete page'),
(60, 1, 'Режим просмотра'),
(60, 2, 'Режим перегляду'),
(60, 3, 'View mode'),
(61, 1, 'Исходный код'),
(61, 2, 'Вихідний код'),
(61, 3, 'View source'),
(62, 1, 'Назначить группы'),
(62, 2, 'Призначити групи'),
(62, 3, 'Set roles'),
(63, 1, 'Группы'),
(63, 2, 'Групи'),
(63, 3, 'Groups'),
(64, 1, 'В процессе работы возникли ошибки'),
(64, 2, 'В процесі роботи виникли помилки'),
(64, 3, 'The errors occurred'),
(65, 1, 'Невозможно удалить себя самого.'),
(65, 2, 'Неможливо видалити самого себе.'),
(65, 3, 'You cannot delete yourself.'),
(66, 1, 'Имя группы'),
(66, 2, 'Назва групи'),
(66, 3, 'Group name'),
(67, 1, 'По умолчанию для гостей'),
(67, 2, 'За замовчанням для гостей'),
(67, 3, 'Default for guests'),
(68, 1, 'По умолчанию для пользователей'),
(68, 2, 'За замовчанням для користувачів'),
(68, 3, 'Default for users'),
(69, 1, 'Аббревиатура языка'),
(69, 2, 'Абревіатура мови'),
(69, 3, 'Language abbreviation'),
(70, 1, 'Название языка'),
(70, 2, 'Назва мови'),
(70, 3, 'Language name'),
(71, 1, 'Язык по умолчанию'),
(71, 2, 'Мова за замовчанням'),
(71, 3, 'Default language'),
(72, 1, 'Локаль для *NIX'),
(72, 2, 'Локаль для *NIX'),
(72, 3, 'Local for *NIX'),
(73, 1, 'Локаль для Windows'),
(73, 2, 'Локаль для Windows'),
(73, 3, 'Local for Windows'),
(74, 1, 'Назначить права'),
(74, 2, 'Призначити права'),
(74, 3, 'Set rights'),
(75, 1, 'Название шаблона'),
(75, 2, 'Назва шаблону'),
(75, 3, 'Template name'),
(76, 1, 'Layout'),
(76, 2, 'Layout'),
(76, 3, 'Layout'),
(77, 1, 'Content'),
(77, 2, 'Content'),
(77, 3, 'Content'),
(78, 1, 'Системный шаблон'),
(78, 2, 'Системний шаблон'),
(78, 3, 'System template'),
(79, 1, 'Войти'),
(79, 2, 'Увійти'),
(79, 3, 'Enter'),
(80, 1, 'Регистрация прошла успешно'),
(80, 2, 'Реєстрація пройшла успішно'),
(80, 3, 'Registration was successful'),
(81, 1, 'Свойства'),
(81, 2, 'Властивості'),
(81, 3, 'Properties'),
(82, 1, 'Подробнее'),
(82, 2, 'Детальніше'),
(82, 3, 'Details'),
(83, 1, 'Заказать'),
(83, 2, 'Замовити'),
(83, 3, 'Order'),
(96, 1, 'Ошибка 404: документ не найден.'),
(96, 2, 'Помилка 404: документ не знайдено.'),
(96, 3, 'Error 404: document not found.'),
(109, 1, 'Перевод'),
(109, 2, 'Переклад'),
(109, 3, 'Translation'),
(110, 1, 'Просмотр'),
(110, 2, 'Перегляд'),
(110, 3, 'View'),
(112, 1, 'Редактирование'),
(112, 2, 'Редактування'),
(112, 3, 'Edit'),
(115, 1, 'Описание раздела'),
(115, 2, 'Опис розділу'),
(115, 3, 'Division description'),
(121, 1, 'Редактор разделов'),
(121, 2, 'Редактор розділів'),
(121, 3, 'Divisions editor'),
(122, 1, 'Родительский раздел'),
(122, 2, 'Батьківський розділ'),
(122, 3, 'Parent division'),
(123, 1, 'Сегмент URI'),
(123, 2, 'Сегмент URI'),
(123, 3, 'URI segment'),
(124, 1, 'Шаблон'),
(124, 2, 'Шаблон'),
(124, 3, 'Template'),
(125, 1, 'Порядок следования'),
(125, 2, 'Порядок слідування'),
(125, 3, 'Consecution'),
(126, 1, 'Конечный раздел'),
(126, 2, 'Кінцевий розділ'),
(126, 3, 'Final division'),
(127, 1, 'Раздел по умолчанию'),
(127, 2, 'Розділ за замовчанням'),
(127, 3, 'Default division'),
(128, 1, 'Отключен'),
(128, 2, 'Відключена'),
(128, 3, 'Off'),
(129, 1, 'Название раздела'),
(129, 2, 'Назва розділу'),
(129, 3, 'Division name'),
(130, 1, 'Описание раздела'),
(130, 2, 'Опис розділу'),
(130, 3, 'Division description'),
(131, 1, 'Ключевые слова (meta keywords)'),
(131, 2, 'Ключові слова (meta keywords)'),
(131, 3, 'Meta keywords'),
(133, 1, 'Мета-описание (meta description)'),
(133, 2, 'Мета-опис (meta description)'),
(133, 3, 'Meta description'),
(134, 1, 'Закрыть'),
(134, 2, 'Закрити'),
(134, 3, 'Close'),
(135, 1, 'Успешная регистрация'),
(135, 2, 'Успішна реєстрація'),
(135, 3, 'Successful registration'),
(136, 1, 'Поле не может быть пустым.'),
(136, 2, 'Поле не може бути порожнім.'),
(136, 3, 'Field cannot be empty.'),
(143, 1, 'При сохранении произошли ошибки'),
(143, 2, 'При збереженні сталися помилки'),
(143, 3, 'Errors occurred while saving'),
(144, 1, 'Неправильный формат e-mail.'),
(144, 2, 'Неправильний формат e-mail.'),
(144, 3, 'Incorrect e-mail.'),
(145, 1, 'Неправильный формат телефонного номера. Он должен содержать только цифры, знак "-", или пробел.'),
(145, 2, 'Неправильний формат телефонного номера. Він повинен містити тільки цифри, знак "-", або пробіл.'),
(145, 3, 'Incorrect format of phone number.'),
(146, 1, 'Неправильный формат числа.'),
(146, 2, 'Неправильний формат числа.'),
(146, 3, 'Incorrect number format.'),
(152, 1, 'Всего на сумму'),
(152, 2, 'Всього на суму'),
(152, 3, 'Total cost'),
(154, 1, 'Вы уверены, что хотите удалить запись? Восстановить данные потом будет невозможно.'),
(154, 2, 'Ви впевнені, що хочете видалити запис? Відновити дані потім буде неможливо.'),
(154, 3, 'Are you sure you want to delete the record? You would not be able to restore data.'),
(156, 1, 'Права отсутствуют'),
(156, 2, 'Права відсутні'),
(156, 3, 'No rights'),
(157, 1, 'Просмотреть'),
(157, 2, 'Продивитись'),
(157, 3, 'View'),
(158, 1, 'Изображение'),
(158, 2, 'Зображення'),
(158, 3, 'Image'),
(159, 1, 'Описание изображения'),
(159, 2, 'Опис зображення'),
(159, 3, 'Image description'),
(160, 1, 'Маленькое изображение'),
(160, 2, 'Маленьке зображення'),
(160, 3, 'Small image'),
(161, 1, 'Вставить изображение'),
(161, 2, 'Вставити зображення'),
(161, 3, 'Insert image'),
(162, 1, 'Имя файла'),
(162, 2, 'Назва файла'),
(162, 3, 'File name'),
(163, 1, 'Ширина'),
(163, 2, 'Ширина'),
(163, 3, 'Width'),
(164, 1, 'Высота'),
(164, 2, 'Висота'),
(164, 3, 'Height'),
(165, 1, 'Горизонтальный отступ'),
(165, 2, 'Горизонтальний відступ'),
(165, 3, 'Horizontal indent'),
(166, 1, 'Вертикальный отступ'),
(166, 2, 'Вертикальний відступ'),
(166, 3, 'Vertical indent'),
(167, 1, 'Альтернативный текст'),
(167, 2, 'Альтернативний текст'),
(167, 3, 'Alternative text'),
(168, 1, 'Вставить изображение'),
(168, 2, 'Вставити зображення'),
(168, 3, 'Insert image'),
(169, 1, 'Библиотека изображений'),
(169, 2, 'Бібліотека зображень'),
(169, 3, 'Image library'),
(170, 1, 'У Вас недостаточно прав на просмотр этой страницы.'),
(170, 2, 'У Вас недостатньо прав для перегляду цієї сторінки.'),
(170, 3, 'Access allowed only for registered users.'),
(171, 1, 'Менеджер изображений'),
(171, 2, 'Менеджер зображень'),
(171, 3, 'Image manager'),
(172, 1, 'Выравнивание'),
(172, 2, 'Вирівнювання'),
(172, 3, 'Align'),
(173, 1, 'Внизу'),
(173, 2, 'Внизу'),
(173, 3, 'Bottom'),
(174, 1, 'Посередине'),
(174, 2, 'Посередині'),
(174, 3, 'Center'),
(175, 1, 'Вверху'),
(175, 2, 'Зверху'),
(175, 3, 'Top'),
(176, 1, 'Слева'),
(176, 2, 'Зліва'),
(176, 3, 'Left'),
(177, 1, 'Справа'),
(177, 2, 'Справа'),
(177, 3, 'Right'),
(178, 1, 'Неправильный формат'),
(178, 2, 'Неправильний формат'),
(178, 3, 'Incorrect format'),
(179, 1, 'Неправильный формат имени пользователя. В поле должны быть только латинские буквы и цифры.'),
(179, 2, 'Неправильний формат імені користувача. У полі повинні бути тільки латинські літери і цифри.'),
(179, 3, 'Incorrect format of user name. There are have to be only latin letters and figures in this field.'),
(186, 1, 'Редактор шаблонов'),
(186, 2, 'Редактор шаблонів'),
(186, 3, 'Template editor'),
(190, 1, 'Перейти в админчасть'),
(190, 2, 'Перейти в адмінчастину'),
(190, 3, 'Go to admin'),
(205, 1, 'Загрузить в формате PDF'),
(205, 2, 'Завантажити в форматі PDF'),
(205, 3, 'Load in PDF format'),
(207, 1, 'Наименование товара'),
(207, 2, 'Найменування товару'),
(207, 3, 'Product name'),
(208, 1, 'Цена'),
(208, 2, 'Ціна'),
(208, 3, 'Price'),
(209, 1, 'Количество'),
(209, 2, 'Кількість'),
(209, 3, 'Quantity'),
(210, 1, 'Всего'),
(210, 2, 'Всього'),
(210, 3, 'Sum'),
(211, 1, 'Итого'),
(211, 2, 'Всього'),
(211, 3, 'Total'),
(212, 1, 'Другие товары этого производителя'),
(212, 2, 'Інші товари цього виробника'),
(212, 3, 'Other goods of this producer '),
(213, 1, 'Цена'),
(213, 2, 'Ціна'),
(213, 3, 'Price'),
(214, 1, 'Перейти'),
(214, 2, 'Перейти'),
(214, 3, 'Go'),
(215, 1, 'Добавить в корзину'),
(215, 2, 'Додати у корзину'),
(215, 3, 'Add to cart'),
(216, 1, 'Страницы'),
(216, 2, 'Сторінки'),
(216, 3, 'Pages'),
(217, 1, 'Описание'),
(217, 2, 'Опис'),
(217, 3, 'Description'),
(218, 1, 'Пересчитать'),
(218, 2, 'Перерахувати'),
(218, 3, 'Recount'),
(219, 1, 'Оформить заказ'),
(219, 2, 'Оформити замовлення'),
(219, 3, 'Make order'),
(220, 1, 'Редактор типов товаров'),
(220, 2, 'Редактор типів товару'),
(220, 3, 'Product type editor'),
(221, 1, 'Параметры товара'),
(221, 2, 'Параметри товару'),
(221, 3, 'Product parameters'),
(222, 1, 'Название типа'),
(222, 2, 'Назва типу'),
(222, 3, 'Type name'),
(223, 1, 'Название параметра'),
(223, 2, 'Назва параметра'),
(223, 3, 'Parameter name'),
(224, 1, 'Тип параметра'),
(224, 2, 'Тип параметра'),
(224, 3, 'Parameter type'),
(225, 1, 'Строка'),
(225, 2, 'Строка'),
(225, 3, 'String'),
(226, 1, 'Целое число'),
(226, 2, 'Ціле число'),
(226, 3, 'Integer'),
(227, 1, 'Текст'),
(227, 2, 'Текст'),
(227, 3, 'Text'),
(228, 1, 'Есть/Нет'),
(228, 2, 'Є/Ні'),
(228, 3, 'Yes/No'),
(229, 1, 'Дробное число'),
(229, 2, 'Дробове число'),
(229, 3, 'Broken number'),
(230, 1, 'Редактор производителей'),
(230, 2, 'Редактор виробників'),
(230, 3, 'Producer editor'),
(231, 1, 'Название производителя'),
(231, 2, 'Назва виробника'),
(231, 3, 'Producer name'),
(232, 1, 'Сегмент URI'),
(232, 2, 'Сегмент URI'),
(232, 3, 'URI segment'),
(233, 1, 'Доступен'),
(233, 2, 'Доступний'),
(233, 3, 'Available'),
(234, 1, 'Название'),
(234, 2, 'Назва'),
(234, 3, 'Name'),
(235, 1, 'Редактировать параметры'),
(235, 2, 'Редагувати параметри'),
(235, 3, 'Edit parameters'),
(236, 1, 'Категория'),
(236, 2, 'Категорія'),
(236, 3, 'Category'),
(237, 1, 'Выбрать'),
(237, 2, 'Обрати'),
(237, 3, 'Choose'),
(238, 1, 'Список разделов'),
(238, 2, 'Список розділів'),
(238, 3, 'Divisions list'),
(239, 1, 'Код товара'),
(239, 2, 'Код товару'),
(239, 3, 'Product code'),
(240, 1, 'Количество на складе'),
(240, 2, 'Кількість на складі'),
(240, 3, 'Storage on hand'),
(241, 1, 'Тип товара'),
(241, 2, 'Тип товару'),
(241, 3, 'Product type'),
(242, 1, 'Производитель'),
(242, 2, 'Виробник'),
(242, 3, 'Producer'),
(243, 1, 'Сегмент URI'),
(243, 2, 'Сегмент URI'),
(243, 3, 'URI segment'),
(244, 1, 'Фотография'),
(244, 2, 'Фотографія'),
(244, 3, 'Photograph'),
(245, 1, 'Краткое описание'),
(245, 2, 'Короткий опис'),
(245, 3, 'Short description'),
(246, 1, 'Значение параметра'),
(246, 2, 'Значення параметра'),
(246, 3, 'Parameter value'),
(247, 1, 'Название параметра'),
(247, 2, 'Назва параметра'),
(247, 3, 'Parameter name'),
(248, 1, 'Ваша корзина пуста.'),
(248, 2, 'Ваша корзина порожня.'),
(248, 3, 'Your cart is empty.'),
(249, 1, 'Параметры товара'),
(249, 2, 'Параметри товару'),
(249, 3, 'Product parameters'),
(250, 1, 'Поднять'),
(250, 2, 'Підняти'),
(250, 3, 'Up'),
(251, 1, 'Опустить'),
(251, 2, 'Опустити'),
(251, 3, 'Down'),
(252, 1, 'Заказ от интернет-магазина ENERGINE'),
(252, 2, 'Замовлення від інтернет-магазину ENERGINE'),
(252, 3, 'Order from internet shop ENERGINE'),
(253, 1, 'Здравствуйте, <br/>\n<P>Вами был сделан заказ в интернет магазине.</P>\n<P>Информация о заказе</P>\n<ul>\n<li>Номер заявки: <strong>%s</strong></li>\n<li>Заказчик: <a href="mailto:%s">%s</a></li>\n<li>Телефон: %s</li>\n<li>Адрес: %s</li>\n<li>Комментарий: %s</li>\n</ul>\n<P>Перечень заказанных Вами товаров</P>\n%s\n\n<P>В ближайшее время наш менеджер свяжется с Вами.</P>\n<P>Спасибо за покупку.</P>\n<P>Администрация интернет магазина</P>'),
(253, 2, 'Доброго дня, <br/>\n<P>Вами було зроблено замовлення в інтернет-магазині.</P>\n<P>Інформація про замовлення</P>\n<ul>\n<li>Номер заявки: <strong>%s</strong></li>\n<li>Замовник: <a href="mailto:%s">%s</a></li>\n<li>Телефон: %s</li>\n<li>Адреса: %s</li>\n<li>Коментар: %s</li>\n</ul>\n<P>Перелік замовлених Вами товарів</P>\n%s\n\n<P>В найближчий час наш менеджер зв''яжеться з Вами.</P>\n<P>Дякуємо за покупку.</P>\n<P>Администрація інтернет-магазину</P>'),
(253, 3, '<P>Order information</P>\n<ul>\n<li>Number: <strong>%s</strong></li>\n<li>Customer: <a href="mailto:%s">%s</a></li>\n<li>Phone: %s</li>\n<li>Address: %s</li>\n<li>Comment: %s</li>\n</ul>\n<P>List of goods</P>\n%s'),
(255, 1, 'Библиотека изображений'),
(255, 2, 'Бібліотека зображень'),
(255, 3, 'Images library'),
(256, 1, '<p><strong>Спасибо за покупку, Ваш заказ отправлен.</strong></p>\n<p>На ваш электронный адрес отправлено письмо с описанием последующих действий.</p>'),
(256, 2, '<p><strong>Дякуємо за купівлю, Ваше замовлення відправлено.</strong></p>\n<p>На вашу електронну адресу відправлено лист із описом наступних дій.</p>'),
(256, 3, '<p><strong>Thank''s, you order is sent.</strong></p>\n<p>The message with instructions is sent on you contact e-mail address.</p>'),
(257, 1, 'Импорт прайс-листа'),
(257, 2, 'Імпорт прайс-листа'),
(257, 3, 'Price list import'),
(258, 1, 'Файл в фомате .csv, содержащий прайс-лист'),
(258, 2, 'Файл у фоматі .csv, що містить прайс-лист'),
(258, 3, 'File in .csv format which contains price list'),
(259, 1, 'Файл содержит полное описание'),
(259, 2, 'Файл містить повний опис'),
(259, 3, 'File contains full description'),
(260, 1, 'Загрузить'),
(260, 2, 'Завантажити'),
(260, 3, 'Load'),
(261, 1, 'Файл не выбран'),
(261, 2, 'Файл не обрано'),
(261, 3, 'File is not chosen'),
(262, 1, 'Импорт не удался'),
(262, 2, 'Невдалий імпорт'),
(262, 3, 'Unsuccessful import'),
(263, 1, 'Произошла ошибка при работе с базой данных.'),
(263, 2, 'Сталася помилка при роботі з базою даних.'),
(263, 3, 'An error occurred while working with database.'),
(264, 1, 'Импортирование завершилось успешно'),
(264, 2, 'Імпортування завершилося вдало'),
(264, 3, 'Import completed successfully'),
(265, 1, 'Запомнить'),
(265, 2, 'Запам''ятати'),
(265, 3, 'Remember'),
(266, 1, 'Адрес доставки'),
(266, 2, 'Адреса доставки'),
(266, 3, 'Delivery address'),
(267, 1, 'Контактное лицо'),
(267, 2, 'Контактна особа'),
(267, 3, 'Contact person'),
(268, 1, 'Комментарий'),
(268, 2, 'Коментар'),
(268, 3, 'Comment'),
(269, 1, 'Отправить'),
(269, 2, 'Відправити'),
(269, 3, 'Send'),
(270, 1, 'Контактная информация'),
(270, 2, 'Контактна інформація'),
(270, 3, 'Contact info'),
(271, 1, 'Порядок следования'),
(271, 2, 'Порядок слідування'),
(271, 3, 'Consecution'),
(272, 1, 'Статус'),
(272, 2, 'Статус'),
(272, 3, 'Status'),
(273, 1, 'Редактор пользователей'),
(273, 2, 'Редактор користувачів'),
(273, 3, 'User editor'),
(274, 1, 'Редактор ролей'),
(274, 2, 'Редактор ролей'),
(274, 3, 'Role editor'),
(275, 1, 'Права на страницу'),
(275, 2, 'Права на сторінку'),
(275, 3, 'Rights for page'),
(276, 1, 'Дата заказа'),
(276, 2, 'Дата замовлення'),
(276, 3, 'Date of an order'),
(277, 1, 'Пользователь'),
(277, 2, 'Користувач'),
(277, 3, 'User'),
(278, 1, 'История заказов'),
(278, 2, 'Історія замовлень'),
(278, 3, 'Order history'),
(279, 1, 'Редактор языков'),
(279, 2, 'Редактор мов'),
(279, 3, 'Language editor'),
(280, 1, 'Посмотреть детали'),
(280, 2, 'Продивитись деталі'),
(280, 3, 'View details'),
(281, 1, 'Статус заказа'),
(281, 2, 'Статус замовлення'),
(281, 3, 'Order status'),
(282, 1, 'Комментарий менеджера'),
(282, 2, 'Коментар менеджера'),
(282, 3, 'Manager''s comment'),
(283, 1, 'Детали заказа'),
(283, 2, 'Деталі замовлення'),
(283, 3, 'Order details'),
(284, 1, 'Искать'),
(284, 2, 'Шукати'),
(284, 3, 'Search'),
(285, 1, 'Поиск по каталогу'),
(285, 2, 'Пошук по каталогу'),
(285, 3, 'Search in catalogue '),
(286, 1, 'Вы вошли в систему как'),
(286, 2, 'Ви увійшли до системи як'),
(286, 3, 'You have authorized as'),
(287, 1, 'Здравствуйте!'),
(287, 2, 'Доброго дня!'),
(287, 3, 'Hello!'),
(288, 1, 'Содержимое вашей корзины'),
(288, 2, 'Вміст вашої корзини'),
(288, 3, 'Your cart contents'),
(289, 1, 'Здравствуйте, <br/>\n<P>Вами был&nbsp;сделан заказ в интернет-магазине</P>\n<UL>\n<LI>Логин: %s</LI>\n<LI>Пароль: %s</LI></UL>\n<P>Информация о заказе</P>\n<ul>\n<li>Номер заявки: <strong>%s</strong></li>\n<li>Заказчик: <a href="mailto:%s">%s</a></li>\n<li>Телефон: %s</li>\n<li>Адрес: %s</li>\n<li>Комментарий: %s</li>\n</ul>\n<P>Перечень заказанных Вами товаров</P>\n%s\n\n<P>В ближайшее время наш менеджер свяжется с Вами.</P>\n<P>Спасибо за покупку.</P>\n<P>Администрация интернет-магазина</P>'),
(289, 2, 'Доброго дня, <br/>\n<P>Вами було зроблено замовлення в інтернет-магазині</P>\n<UL>\n<LI>Логін: %s</LI>\n<LI>Пароль: %s</LI></UL>\n<P>Інформация про замовлення</P>\n<ul>\n<li>Номер замовлення: <strong>%s</strong></li>\n<li>Замовник: <a href="mailto:%s">%s</a></li>\n<li>Телефон: %s</li>\n<li>Адреса: %s</li>\n<li>Коментар: %s</li>\n</ul>\n<P>Перелік замовлених Вами товарів</P>\n%s\n\n<P>Найближчим часом наш менеджер зв''яжеться з Вами.</P>\n<P>Дякуємо за покупку.</P>\n<P>Адміністрація інтернет-магазина</P>'),
(289, 3, 'Hello, <br/>\n<P>You have made an order in internet shop.</P>\n<UL>\n<LI>Login: %s</LI>\n<LI>Password: %s</LI></UL>\n<P>Information about order</P>\n<ul>\n<li>Number: <strong>%s</strong></li>\n<li>Customer: <a href="mailto:%s">%s</a></li>\n<li>Phone: %s</li>\n<li>Address: %s</li>\n<li>Comment: %s</li>\n</ul>\n<P>Goods list</P>\n%s'),
(290, 1, 'Название скидки'),
(290, 2, 'Назва знижки'),
(290, 3, 'Discount name'),
(291, 1, 'Процент скидки'),
(291, 2, 'Процент знижки'),
(291, 3, 'Discount percent'),
(292, 1, 'Редактор скидок'),
(292, 2, 'Редактор знижок'),
(292, 3, 'Discounts editor'),
(293, 1, 'Восстановление пароля'),
(293, 2, 'Відновлення пароля'),
(293, 3, 'Restore password'),
(294, 1, '<P>Здравствуйте. </P>\n<P>Вами был подан запрос на восстановление пароля.<BR>Ваш новый пароль: %s.</P>\n<P>В качестве имени пользователя используйте адрес электронной почты, на которое поступило это письмо.</P>\n<P>С уважением, разработчики Energine.</P>'),
(294, 2, '<P>Доброго дня. </P>\n<P>Вами було подано запит на відновлення пароля.<BR>Ваш новый пароль: %s.</P>\n<P>В якості імені користувача використовуйте адресу електронної пошти, на яку надійшов цей лист.</P>\n<P>С повагою, розробники Energine.</P>'),
(294, 3, '<P>Hello.</P>\n<P>You have sent an order to restore password.<BR>Your new password is: %s.</P>\n<P>Use e-mail address on which you have received this message as login.</P>'),
(295, 1, 'Неправильное имя пользователя'),
(295, 2, 'Невірне ім''я користувача'),
(295, 3, 'Incorrect user name'),
(296, 1, 'На указанный вами адрес электронной почты был отправлен новый пароль.'),
(296, 2, 'На вказану вами адресу електронної пошти було відправлено новий пароль.'),
(296, 3, 'New password was send on your e-mail address.'),
(297, 1, 'Забыли пароль?'),
(297, 2, 'Забули пароль?'),
(297, 3, 'Forgot password?'),
(298, 1, 'Ваша скидка'),
(298, 2, 'Ваша знижка'),
(298, 3, 'Your discount'),
(299, 1, 'Цена со скидкой'),
(299, 2, 'Ціна зі знижкою'),
(299, 3, 'Price with discount'),
(300, 1, 'Ссылка на раздел'),
(300, 2, 'Посилання на розділ'),
(300, 3, 'Link on division'),
(301, 1, 'Итого с учетом скидки'),
(301, 2, 'Всього з урахуванням знижки'),
(301, 3, 'Total cost with discount'),
(302, 1, 'Неправильный формат сегмента URL'),
(302, 2, 'Неправильний формат сегмента URL'),
(302, 3, 'Incorrect format of URL segment'),
(303, 1, '<P><STRONG>Пользователь с такими данными уже существует.</STRONG></P>\n<P>Скорее всего вы уже зарегистрированы в нашем магазине. </P>\n<P>Вам необходимо авторизоваться , перейдя на форму авторизации. </P>\n<P>Если вы забыли свой пароль, воспользуйтесь формой восстановления пароля, расположенной на той же странице.</P>'),
(303, 2, '<P><STRONG>Користувач з такими даними уже існує.</STRONG></P>\n<P>Скоріш за все, ви вже зареєстровані у нашому магазині. </P>\n<P>Вам необхідно авторизуватися за допомогою форми авторизації. </P>\n<P>Якщо ви забули свій пароль, скористайтесь формою відновлення пароля, що знаходиться на тій же сторінці.</P>'),
(303, 3, '<P><STRONG>User with such data already exists.</STRONG></P>\n<P>Probably you have registered yet in our shop.</P>'),
(304, 1, 'Группы'),
(304, 2, 'Групи'),
(304, 3, 'Groups'),
(305, 1, 'Комментарий'),
(305, 2, 'Коментар'),
(305, 3, 'Comment'),
(306, 1, 'Название валюты'),
(306, 2, 'Назва валюти'),
(306, 3, 'Currency name'),
(307, 1, 'Курс валюты по отношению к базовой'),
(307, 2, 'Курс валюти стосовно базової'),
(307, 3, 'Currency rate to base'),
(308, 1, 'Стандартная аббревиатура'),
(308, 2, 'Стандартна абревіатура'),
(308, 3, 'Standard abbreviation'),
(309, 1, 'Валюта по умолчанию'),
(309, 2, 'Валюта за замовчанням'),
(309, 3, 'Default currency'),
(310, 1, 'Формат'),
(310, 2, 'Формат'),
(310, 3, 'Format'),
(312, 1, 'Открыть'),
(312, 2, 'Відкрити'),
(312, 3, 'Open'),
(313, 1, 'Создать папку'),
(313, 2, 'Створити папку'),
(313, 3, 'Add folder'),
(314, 1, 'Переименовать'),
(314, 2, 'Перейменувати'),
(314, 3, 'Rename'),
(315, 1, 'Валюта'),
(315, 2, 'Валюта'),
(315, 3, 'Currency'),
(319, 1, 'Полный доступ'),
(319, 2, 'Повний доступ'),
(319, 3, 'Full access'),
(320, 1, 'Редактирование'),
(320, 2, 'Редактування'),
(320, 3, 'Editing'),
(321, 1, 'Только чтение'),
(321, 2, 'Тільки перегляд'),
(321, 3, 'Read only'),
(322, 1, 'Маленькое изображение для: '),
(322, 2, 'Маленьке зображення для: '),
(322, 3, 'Small image for:'),
(323, 1, 'Структура сайта'),
(323, 2, 'Структура сайту'),
(323, 3, 'Site structure'),
(324, 1, 'Шаблоны'),
(324, 2, 'Шаблони'),
(324, 3, 'Templates'),
(325, 1, '<p>Пользовательские настройки сохранены.</p>'),
(325, 2, '<p>Налаштування користувача збережено.</p>'),
(325, 3, '<p>User settings are saved.</p>'),
(326, 1, 'Фильтр'),
(326, 2, 'Фільтр'),
(326, 3, 'Filter'),
(327, 1, 'Применить'),
(327, 2, 'Застосувати'),
(327, 3, 'Apply'),
(328, 1, 'Сбросить фильтр'),
(328, 2, 'Скинути фільтр'),
(328, 3, 'Reset filter'),
(329, 1, 'Теги'),
(329, 2, 'Теги'),
(329, 3, 'Tags'),
(330, 1, 'Сохранить и перейти к редактированию'),
(330, 2, 'Зберегти і перейти до редагування'),
(330, 3, 'Save and go to edit'),
(331, 1, 'Вы хотите перейти на новосозданную страницу?'),
(331, 2, 'Ви хочете перейти до нової сторінки?'),
(331, 3, 'Do you want to move to the new page?'),
(332, 1, 'Перейти к корзине'),
(332, 2, 'Перейти до корзини'),
(332, 3, 'Go to cart'),
(333, 1, 'Всего на сумму'),
(333, 2, 'Всього на суму'),
(333, 3, 'Total cost'),
(334, 1, 'Тема сообщения'),
(334, 2, 'Тема повідомлення'),
(334, 3, 'Message theme'),
(336, 1, 'Название'),
(336, 2, 'Назва'),
(336, 3, 'Name'),
(339, 1, 'Нельзя удалить группу по умолчанию'),
(339, 2, 'Неможливо видалити групу за замовчанням'),
(339, 3, 'You cannot delete default group '),
(340, 1, 'Права группы по умолчанию'),
(340, 2, 'Права групи за замовчанням'),
(340, 3, 'Default group rights'),
(341, 1, 'Права на разделы'),
(341, 2, 'Права на розділи'),
(341, 3, 'Rights for divisions'),
(342, 1, 'Результаты поиска'),
(342, 2, 'Результати пошуку'),
(342, 3, 'Search results'),
(343, 1, 'По вашему запросу товар не найден.'),
(343, 2, 'За вашим запитом товар не знайдено.'),
(343, 3, 'Goods not found.'),
(344, 1, 'Главная'),
(344, 2, 'Головна'),
(344, 3, 'Main page'),
(345, 1, 'Телефон'),
(345, 2, 'Телефон'),
(345, 3, 'Phone'),
(346, 1, 'Сохранить'),
(346, 2, 'Зберегти'),
(346, 3, 'Save'),
(347, 1, 'Отменить'),
(347, 2, 'Відмінити'),
(347, 3, 'Cancel'),
(348, 1, 'Исходный код текстового блока'),
(348, 2, 'Вихідний код текстового блоку'),
(348, 3, 'Source code of the text block'),
(349, 1, 'Название организации'),
(349, 2, 'Назва організації'),
(349, 3, 'Company name'),
(350, 1, 'Юридический адрес'),
(350, 2, 'Юридична адреса'),
(350, 3, 'Legal address'),
(351, 1, 'Физический адрес'),
(351, 2, 'Фізична адреса'),
(351, 3, 'Physical address'),
(352, 1, 'Номер свид. плат. НДС'),
(352, 2, 'Номер свід. платн. ПДВ'),
(352, 3, 'Number of VAT payer''s certificate'),
(353, 1, 'Инд. налоговый номер'),
(353, 2, 'Індивідуальний податковий номер'),
(353, 3, 'Taxpayer Identification Number'),
(354, 1, 'Номер факса'),
(354, 2, 'Номер факса'),
(354, 3, 'Fax number'),
(355, 1, 'Получить счет на e-mail'),
(355, 2, 'Отримати рахунок на e-mail'),
(355, 3, 'Get invoice by e-mail'),
(356, 1, 'Получить счет по факсу'),
(356, 2, 'Отримати рахунок факсом'),
(356, 3, 'Get invoice by e-mail'),
(357, 1, 'Файловый репозиторий'),
(357, 2, 'Файловий репозиторій'),
(357, 3, 'File repository'),
(358, 1, '<p class="error">При оформлении заказа произошла ошибка.</p>'),
(358, 2, '<p class="error">При оформленні замовлення відбулася помилка.</p>'),
(358, 3, '<p class="error">An error occurred while sending order.</p>'),
(359, 1, 'Статус'),
(359, 2, 'Статус'),
(359, 3, 'Status'),
(360, 1, 'Название статуса'),
(360, 2, 'Назва статусу'),
(360, 3, 'Status name'),
(361, 1, 'По умолчанию'),
(361, 2, 'За замовчанням'),
(361, 3, 'Default'),
(362, 1, 'Статус видимый'),
(362, 2, 'Статус видимий'),
(362, 3, 'Visible'),
(364, 1, 'Данные для юридических лиц (будут использованы для выписки счета)'),
(364, 2, 'Дані для юридичних осіб (будуть використані для виписування рахунку)'),
(364, 3, 'Information for juridical persons (will use to issue an invoice)'),
(365, 1, 'Изменить персональные данные'),
(365, 2, 'Змінити персональні дані'),
(365, 3, 'Change personal data'),
(366, 1, 'Детали заказа'),
(366, 2, 'Деталі замовлення'),
(366, 3, 'Order details'),
(367, 1, 'Новый пароль'),
(367, 2, 'Новий пароль'),
(367, 3, 'New password'),
(368, 1, 'Адрес'),
(368, 2, 'Адреса'),
(368, 3, 'Address'),
(370, 1, 'Общая информация'),
(370, 2, 'Загальна інформація'),
(370, 3, 'General information'),
(371, 1, 'Для сохранения необходимо ввести пароль'),
(371, 2, 'Для збереження необхідно ввести пароль'),
(371, 3, 'Enter password to save'),
(372, 1, 'Новый пароль'),
(372, 2, 'Новий пароль'),
(372, 3, 'New password'),
(373, 1, 'Подтвердите пароль'),
(373, 2, 'Підтвердіть пароль'),
(373, 3, 'Confirm password'),
(374, 1, 'Новый пароль и его подтверждение должны быть одинаковыми.'),
(374, 2, 'Новий пароль і його підтвердження повинні бути однакові.'),
(374, 3, 'A new password and its confirmation have to be equal.'),
(375, 1, 'Вы ввели неверный пароль'),
(375, 2, 'Ви ввели невірний пароль'),
(375, 3, 'Incorrect password'),
(376, 1, 'Неверный логин или пароль'),
(376, 2, 'Невірний логін або пароль'),
(376, 3, 'Incorrect login or password'),
(377, 1, 'Системное сообщение'),
(377, 2, 'Системне повідомлення'),
(377, 3, 'System message'),
(378, 1, 'Здравствуйте, <br/>\nВ интернет магазин поступил новый заказ\n<ul>\n<li>Номер заявки: %s</li>\n<li>Заказчик: <a href="mailto:%s">%s</a></li>\n<li>Телефон: %s</li>\n<li>Адрес: %s</li>\n<li>Комментарий: %s</li>\n</ul>\nСостав заказа\n%s'),
(378, 2, 'Доброго дня, <br/>\nВ інтернет-магазин надійшло нове повідомлення\n<ul>\n<li>Номер замовлення: %s</li>\n<li>Замовник: <a href="mailto:%s">%s</a></li>\n<li>Телефон: %s</li>\n<li>Адреса: %s</li>\n<li>Коментар: %s</li>\n</ul>\nСклад замовлення\n%s'),
(378, 3, 'Hello, <br/>\nNew order from internet shop\n<ul>\n<li>Number: %s</li>\n<li>Customer: <a href="mailto:%s">%s</a></li>\n<li>Phone: %s</li>\n<li>Address: %s</li>\n<li>Comment: %s</li>\n</ul>\nOrder composition\n%s'),
(379, 1, 'Комментарий'),
(379, 2, 'Коментар'),
(379, 3, 'Comment'),
(381, 1, 'Переводы'),
(381, 2, 'Переклади'),
(381, 3, 'Translations'),
(382, 1, 'Разделы'),
(382, 2, 'Розділи'),
(382, 3, 'Divisions'),
(383, 1, 'Невозможно изменить порядок следования'),
(383, 2, 'Неможливо змінити порядок слідування'),
(383, 3, 'Cannot move'),
(384, 1, 'Альтернативный title страницы'),
(384, 2, 'Альтернативний title сторінки'),
(384, 3, 'Alternative page title'),
(385, 1, 'Режим просмотра'),
(385, 2, 'Режим перегляду'),
(385, 3, 'View mode'),
(386, 1, 'Отображать цены в валюте'),
(386, 2, 'Відображати ціни у валюті'),
(386, 3, 'Show prices in'),
(387, 1, 'Курс'),
(387, 2, 'Курс'),
(387, 3, 'Rate'),
(388, 1, 'Контактный телефон'),
(388, 2, 'Контактний телефон'),
(388, 3, 'Contact phone'),
(389, 1, 'Неправильные данные.'),
(389, 2, 'Неправильні дані.'),
(389, 3, 'Incorrect data.'),
(390, 1, 'Ошибка: ваша корзина пуста.'),
(390, 2, 'Помилка: ваша корзина порожня.'),
(390, 3, 'Error: your cart is empty.'),
(391, 1, 'Заголовок новости'),
(391, 2, 'Заголовок новини'),
(391, 3, 'News title'),
(392, 1, 'Дата новости'),
(392, 2, 'Дата новини'),
(392, 3, 'News date'),
(393, 1, 'Анонс новости '),
(393, 2, 'Анонс новини'),
(393, 3, 'News anounce'),
(394, 1, 'Текст новости'),
(394, 2, 'Текст новини'),
(394, 3, 'News text'),
(395, 1, 'Неправильный формат даты/времени'),
(395, 2, 'Невірний формат дати/часу'),
(395, 3, 'Wrong date/time format '),
(396, 1, 'Файловый репозиторий'),
(396, 2, 'Файловий репозиторій'),
(396, 3, 'File repository'),
(397, 1, 'Имя папки (сегмент URI)'),
(397, 2, 'Назва папки (сегмент URI)'),
(397, 3, 'Folder name (URI segment)'),
(398, 1, 'Роль в системе'),
(398, 2, 'Роль в системі'),
(398, 3, 'Role in the system'),
(399, 1, 'Роль'),
(399, 2, 'Роль'),
(399, 3, 'Role'),
(400, 1, 'Каталог'),
(400, 2, 'Каталог'),
(400, 3, 'Catalogue'),
(401, 1, 'Вход'),
(401, 2, 'Вхід'),
(401, 3, 'Entrance'),
(402, 1, 'Заявка'),
(402, 2, 'Замовлення'),
(402, 3, 'Order'),
(403, 1, 'Информация о клиенте'),
(403, 2, 'Інформація про клієнта'),
(403, 3, 'Info about consumer'),
(404, 1, 'Информация о заказе'),
(404, 2, 'Інформація про замовлення'),
(404, 3, 'Info about order'),
(405, 1, 'Организация'),
(405, 2, 'Організація'),
(405, 3, 'Organization'),
(406, 1, 'Ф. И. О. контактного лица'),
(406, 2, 'П. І. Б. контактної соби'),
(406, 3, 'Contact person'),
(407, 1, 'Должность контактного лица'),
(407, 2, 'Посада контактної особи'),
(407, 3, 'Contact person position'),
(408, 1, 'Тел./Факс'),
(408, 2, 'Тел./Факс'),
(408, 3, 'Phone/Fax'),
(409, 1, 'E-mail'),
(409, 2, 'E-mail'),
(409, 3, 'E-mail'),
(410, 1, 'Адрес'),
(410, 2, 'Адреса'),
(410, 3, 'Address'),
(411, 1, 'Веб-сайт'),
(411, 2, 'Веб-сайт'),
(411, 3, 'Website'),
(412, 1, 'Тип техники, оборудования'),
(412, 2, 'Тип техніки, обладнання'),
(412, 3, 'Product type'),
(413, 1, 'Модель (из каталога)'),
(413, 2, 'Модель (з каталогу)'),
(413, 3, 'Model (from catalogue)'),
(414, 1, 'Объем планируемого заказа (количество единиц)'),
(414, 2, 'Обсяг замовлення (кількість одиниць)'),
(414, 3, 'Order size (number of units)'),
(415, 1, 'Требуемое вспомогательное оборудование (перечислить)'),
(415, 2, 'Потрібне допоміжне обладнання (перерахувати)'),
(415, 3, 'Required additional equipment'),
(416, 1, 'Планируемая сфера применения'),
(416, 2, 'Планована сфера застосування'),
(416, 3, 'Field of use'),
(417, 1, 'Дополнительные пожелания'),
(417, 2, 'Додаткові побажання'),
(417, 3, 'Additional comments'),
(418, 1, 'вся продукция'),
(418, 2, 'вся продукція'),
(418, 3, 'all production'),
(419, 1, 'Контакты'),
(419, 2, 'Контакти'),
(419, 3, 'Contacts'),
(420, 1, 'Открыть поле'),
(420, 2, 'Відкрити поле'),
(420, 3, 'Open field'),
(421, 1, 'Скрыть поле'),
(421, 2, 'Сховати поле'),
(421, 3, 'Hide field'),
(422, 1, '/^(([^()<>@,;:\\\\\\".\\[\\] ]+)|("[^"\\\\\\\\\\r]*"))((\\.[^()<>@,;:\\\\\\".\\[\\] ]+)|(\\."[^"\\\\\\\\\\r]*"))*@(([a-z0-9][a-z0-9\\-]+)*[a-z0-9]+\\.)+[a-z]{2,}$/i'),
(422, 2, '/^(([^()<>@,;:\\\\\\".\\[\\] ]+)|("[^"\\\\\\\\\\r]*"))((\\.[^()<>@,;:\\\\\\".\\[\\] ]+)|(\\."[^"\\\\\\\\\\r]*"))*@(([a-z0-9][a-z0-9\\-]+)*[a-z0-9]+\\.)+[a-z]{2,}$/i'),
(422, 3, '/^(([^()<>@,;:\\\\\\".\\[\\] ]+)|("[^"\\\\\\\\\\r]*"))((\\.[^()<>@,;:\\\\\\".\\[\\] ]+)|(\\."[^"\\\\\\\\\\r]*"))*@(([a-z0-9][a-z0-9\\-]+)*[a-z0-9]+\\.)+[a-z]{2,}$/i'),
(424, 1, 'Спасибо за внимание к нашим продуктам. Ваша заявка отправлена. В ближайшее время мы свяжемся с Вами.'),
(424, 2, 'Дякуємо за інтерес до наших продуктів. Ваше замовлення відправлено. Найближчим часом ми звяжемось з вами. '),
(424, 3, 'Your request has been sent. We''ll contact you, soon.'),
(426, 1, 'Добавить новость'),
(426, 2, 'Додати новину'),
(426, 3, 'Add news'),
(427, 1, 'Редактировать новость'),
(427, 2, 'Редагувати новину'),
(427, 3, 'Edit news '),
(428, 1, 'Удалить новость'),
(428, 2, 'Видалити новину'),
(428, 3, 'Delete news'),
(429, 1, 'Вернуться к списку'),
(429, 2, 'Повернутись до списку'),
(429, 3, 'Back to the list'),
(430, 1, 'Партнеры'),
(430, 2, 'Партнери'),
(430, 3, 'Partners'),
(431, 1, 'Карта сайта'),
(431, 2, 'Карта сайту'),
(431, 3, 'Sitemap'),
(432, 1, 'Контакты'),
(432, 2, 'Контакти'),
(432, 3, 'Contacts'),
(433, 1, 'Добавить фотографию'),
(433, 2, 'Додати фотографію'),
(433, 3, 'Add photo'),
(434, 1, 'Редактировать фотографию'),
(434, 2, 'Редагувати фотографію'),
(434, 3, 'Edit photo'),
(435, 1, 'Удалить фотографию'),
(435, 2, 'Видалити фотографію'),
(435, 3, 'Delete photo'),
(436, 1, 'Текст сообщения'),
(436, 2, 'Текст повідомлення'),
(436, 3, 'Message text'),
(437, 1, 'Автор сообщения'),
(437, 2, 'Автор повідомлення'),
(437, 3, 'Message author'),
(438, 1, 'Контактный e-mail'),
(438, 2, 'Контактний e-mail'),
(438, 3, 'Contact e-mail'),
(439, 1, '<span class="mark">*</span> - поля, обязательные для заполнения'),
(439, 2, '<span class="mark">*</span> - поля, обов''язкові для заповнення'),
(439, 3, '<span class="mark">*</span> - fields must be filled'),
(440, 1, 'Ваше сообщение успешно отправлено.'),
(440, 2, 'Ваше повідомлення успішно відправлено.'),
(440, 3, 'Your message has been successfully sent.'),
(441, 1, 'Поздравляем, Вы удачно зарегистрировались. На указанный Вами адрес электронной почты отправлено письмо с параметрими доступа.'),
(441, 2, 'Вітаємо, Ви вдало зареєструвалися.'),
(441, 3, 'Congratulations, you have successfully registered.'),
(442, 1, 'Дата сообщения'),
(442, 2, 'Дата повідомлення'),
(442, 3, 'Message date'),
(443, 1, 'Список сообщений'),
(443, 2, 'Список повідомлень'),
(443, 3, 'List of messages'),
(444, 1, 'Файл'),
(444, 2, 'Файл'),
(444, 3, 'File');

-- --------------------------------------------------------

--
-- Структура таблицы `share_news`
--

CREATE TABLE IF NOT EXISTS `share_news` (
  `news_id` int(10) unsigned NOT NULL auto_increment,
  `news_date` date NOT NULL default '0000-00-00',
  `smap_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`news_id`),
  KEY `smap_id` (`smap_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Дамп данных таблицы `share_news`
--

INSERT INTO `share_news` (`news_id`, `news_date`, `smap_id`) VALUES
(2, '2007-11-30', NULL),
(3, '2007-11-20', NULL),
(4, '2007-11-28', NULL),
(5, '2007-11-29', NULL),
(6, '2008-02-16', 327),
(7, '2008-02-18', 327),
(8, '2008-03-15', 327),
(9, '2008-03-16', 327),
(10, '2008-03-24', 327),
(11, '2008-04-24', 327);

-- --------------------------------------------------------

--
-- Структура таблицы `share_news_translation`
--

CREATE TABLE IF NOT EXISTS `share_news_translation` (
  `news_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `news_title` varchar(250) default NULL,
  `news_announce_rtf` text,
  `news_text_rtf` text,
  PRIMARY KEY  (`news_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `share_news_translation`
--

INSERT INTO `share_news_translation` (`news_id`, `lang_id`, `news_title`, `news_announce_rtf`, `news_text_rtf`) VALUES
(2, 1, 'В Италии остановился транспорт', '<p>Общенациональную забастовку проводят сегодня итальянские транспортники.</p>\n<p>Она коснулась всех общественных видов транспорта: на линии не вышли ни автобусы, ни трамваи, ни составы метро.</p>', '<p>Общенациональную забастовку проводят сегодня итальянские транспортники.</p>\n<p>Она коснулась всех общественных видов транспорта: на линии не вышли ни автобусы, ни трамваи, ни составы метро.</p>\n<p>С 9 утра до 17 часов будут стоять пассажирские поезда, а с аэродромов не поднимутся в воздух самолеты. Акцию протеста поддержат и портовые рабочие, которые прекратят погрузочно-разгрузочные работы на два часа. Не выйдут на работу и строители-дорожники.</p>\n<p>Лидеры профсоюзов заявляют, что эта акция протеста вызвана не только сокращением инвестиций со стороны правительства в транспортный сектор, но и постоянными отсрочками решения вопросов о продлении трудовых контрактов и повышения заработной платы. Еще вчера вечером в Италии началась забастовка таксистов.</p>'),
(2, 2, 'В Італії зупинився транспорт', '<p>Загальнонаціональний страйк проводять сьогодні італійські транспортники.</p>\n<p>Він торкнувся всіх видів громадського транспорту: на лінії не вийшли ні автобуси, ні трамваї, ні потяги метро.</p>', '<p>Загальнонаціональний страйк проводять сьогодні італійські транспортники.</p>\n<p>Він торкнувся всіх видів громадського транспорту: на лінії не вийшли ні автобуси, ні трамваї, ні потяги метро.</p>\n<p>З 9 ранку до 17 годин будуть стояти пасажирські потяги, а з аеродромів не піднімуться в повітря літаки. Акцію протесту підтримають і портові робітники, які припинять вантажні роботи на дві години. Не вийдуть на роботу і будівельники-шляховики.</p>\n<p>Лідери профсоюзів заявляють, що ця акція протесту викликана не тільки скороченням інвестицій зі сторони уряду в транспортний сектор, а й постійним відкладанням вирішення питань щодо продовження трудових контрактів і підвищення заробітної платні. Ще вчора ввечері в Італії почався страйк таксистів.</p>'),
(2, 3, NULL, NULL, NULL),
(3, 1, 'Рабочие российского "Форда" забастовали', '<p>Рабочие завода "Форд" во Всеволожске, Ленинградская область, с ноля часов 20 ноября начали бессрочную забастовку.</p>', '<p>Они требуют повышения зарплаты на 30% и сокращения продолжительности ночной смены, сообщил профсоюзный лидер предприятия Алексей Этманов.</p>\n\n<p>Забастовка на заводе Ford во Всеволожске приведет к срыву сроков поставок автомобилей "Форд Фокус" российским заказчикам, сообщила пресс-секретарь компании Ford в России Екатерина Кулиненко в интервью РИА "Новости".</p>\n\n<p>На заводе Ford Motor Company, открытом в 2002 году, работает около 2200 человек (из них 1700 бастует) и ежедневно выпускается 300 автомобилей марки "Форд Фокус", считающейся в России самой популярной машиной для среднего класса. Производительность труда в пять раз превышает соответствующий показатель на "АвтоВАЗе".</p>\n\n<p>7 ноября Всеволожские автомобилестроители провели суточную предупредительную забастовку, продлившуюся 19 часов. Тогда профсоюз заявил, что в случае отказа работодателя принять его требования 20 ноября начнется бессрочная стачка.</p>'),
(3, 2, 'Робітники російського "Форда" застрайкували', '<p>Робітники заводу "Форд" у Всеволожську, Ленінградська область, з нуля годин 20 листопада почали безстроковий страйк.</p>', '<p>Вони вимагають підвищення зарплати на 30% і скорочення часу нічної зміни, повідомив профсоюзний лідер підприємства Олексій Етманов.</p>\n\n<p>Забастовка на заводі Ford у Всеволожську призведе до порушення термінів постачання автомобілей "Форд Фокус" російським замовникам, повідомила прес-секретар компанії Ford у Росії Катерина Куліненко в інтерв''ю РІА "Новости". </p>\n\n<p>На заводі Ford Motor Company, відкритому в 2002 році, працює близько 2200 чоловік (з них 1700 страйкує) і щоденно випускається 300 автомобілів марки "Форд Фокус", яка вважається у Росії найпопулярнішою машиною для середнього класу. Продуктивність праці в 5 разів перевищує відповідний показник на "АвтоВАЗі".</p>\n\n<p>7 листопада всеволожські автобудівники провели добовий попереджувальний страйк, що тривав 19 годин. Тоді профспілка заявила, що у випадку відмови роботодавця прийняти її вимоги 20 листопада почнеться безстроковий страйк.</p>'),
(3, 3, NULL, NULL, NULL),
(4, 1, 'Mail.ru оценили в миллиард долларов', '<p>После того, как южноафриканский медиахолдинг Naspers Limited купил 2,6% интернет-портала Mail.ru за 26 млн. долларов, его капитализация оценивается в 1 млрд. долларов.</p>', '<p>Теперь доля Naspers Limited в Mail.ru составляет почти 33%. В январе южноафриканская компания заплатила за 30% акций 165 млн. долларов, то есть тогда весь Mail.ru оценивался в 550 млн. долларов. Меньше, чем за год капитализация компании выросла почти вдвое.</p>\n\n<p>Оценка Mail.ru в 1 млрд. долларов делает компанию самой дорогой в рунете. Впрочем, по мнению директора Центра инвестиций в высокие технологии компании "Финам" Элины Юриной, эта оценка несколько завышена, и капитализация Mail.ru составляет около 800 млн. долларов.</p>\n\n<p>\nТем не менее, сам факт роста капитализации в два раза весьма показателен. В 2006 году компания Rambler Media продемонстрировала нечто подобное - рост почти в три раза.</p>\n\n<p>Повышенный интерес инвесторов к информационным ресурсам рунета объясняется растущими объемами российского рынка интернет-рекламы, который по прогнозам Центра инвестиций, только в 2007 году составит более 350 млн. долларов.</p>\n\n<p>"Возьмем Mail.ru, - говорит Юрина. - На нем можно зарабатывать - и на баннерной рекламе, и на контентной, и в ближайшем будущем - на видео-рекламе".</p>\n\n<p>Инвесторы в последние годы весьма активно скупают интернет-активы, связанные так или иначе с рекламой. Это - стратегические капиталовложения, которые опираются не на прибыли этих интернет-ресурсов в настоящее время, а на их потенциал с точки зрения привлечения новых пользователей.</p>'),
(4, 2, 'Mail.ru оцінили в мільярд доларів', '<p>Після того, як південно-африканський медіахолдинг Naspers Limited купив 2,6% інтернет-порталу Mail.ru за 26 млн. доларів, його капіталізація оцінюється в 1 млрд. доларів.</p>', '<p>Тепер доля Naspers Limited в Mail.ru складає майже 33%. В січні південно-африканська компанія заплатила за 30% акцій 165 млн. доларів, тобто тоді весь Mail.ru оцінювався в 550 млн. доларів. Менш ніж за рік капіталізація компанії зросла майже вдвічі.</p>\n\n<p>\nОцінка Mail.ru в 1 млрд. доларів робить компанію найдорожчою в рунеті. Однак, на думку директора Центру інвестицій у високі технології компанії "Фінам" Еліни Юріної, ця оцінка є дещо завищеною, і капіталізація Mail.ru становить близько 800 млн. доларів.</p>\n\n<p>Тим не менше, сам факт зростання капіталізації в два рази є досить показовим. В 2006 році компанія Rambler Media продемонструвала щось подібне - зростання майже в три рази.</p>\n\n<p>Підвищений інтерес інвесторів до інформаційних ресурсів рунету пояснюється зростаючими обсягами російського ринку інтернет-реклами, який, за прогнозами Центру інвестицій, тільки в 2007 році складе більш як 350 млн. доларів.</p>\n\n<p>"Візьмемо Mail.ru - говорить Юріна. - На ньому можна заробляти - і на банерній рекламі, і на контентній, і в найближчому майбутньому - на відео-рекламі".</p>\n\n<p>Інвестори в останні роки досить активно скуповують інтернет-активи, пов''язані так чи інакше з рекламою. Це - стратегычны капіталовкладення, які спираються не на прибуток цих інтернет-ресурсів в теперішній час, а на їх потенціал з точки зору залучення нових користувачів.</p>'),
(4, 3, NULL, NULL, NULL),
(5, 1, 'Немцы запустили первый в Европе цензурированный поисковик для детей', '<p>Немецкие власти объявили о запуске специальной поисковой системы для детей. Особенность поискового сервиса FragFinn.de заключается в том, что с его помощью юные пользователи Сети смогут искать необходимую информацию без риска наткнуться на порносайт, сообщает France Presse.</p>', '<p>Немецкие власти объявили о запуске специальной поисковой системы для детей. Особенность поискового сервиса FragFinn.de заключается в том, что с его помощью юные пользователи Сети смогут искать необходимую информацию без риска наткнуться на порносайт, сообщает France Presse.\n</p>\n<p>\nВ поисковую базу FragFinn.de внесены только на одобренные сайты. По заявлению создателей, FragFinn.de является уникальным для Европы поисковиком. На разработку безопасного поисковика, в создании которого участвовали такие крупные компании, как AOL, Microsoft, Google, Lycos, Vodafone и Deutsche Telekom, было потрачено 1,5 миллиона евро (2,2 миллиона долларов).\n</p>\n<p>Каждый посетитель сайта может пожаловаться на неподходящую для детей ссылку в результатах поиска. Степень защищенности поисковика от неподходящего для детей содержимого, на первый взгляд, кажется выше, чем у систем фильтрации других поисковиков, например, Google SafeSearch. Так, по запросу ''porn'' FragFinn.de выдал два результата, один из них - новость на CNN, второй - онлайн-музей изобразительного искусства. В ответ на запросы, состоявшие из немецких ругательств, поисковик выдавал ссылки на детские ресурсы по защите окружающей среды и страницу одной из немецких радиостанций.</p>'),
(5, 2, 'Німці запустили першу в Європі цензуровану пошукову систему для дітей', '<p>Німецька влада оголосила про запуск спеціальної пошукової системи для дітей. Особливістю пошукового сервісу FragFinn.de є те, що з його допомогою юні користувачі Мережі зможуть шукати необхідну інформацію без ризику потрапити на порносайт, повідомляє France Presse.</p>', '<p>Німецька влада оголосила про запуск спеціальної пошукової системи для дітей. Особливістю пошукового сервісу FragFinn.de є те, що з його допомогою юні користувачі Мережі зможуть шукати необхідну інформацію без ризику потрапити на порносайт, повідомляє France Presse.</p>\n<p>В пошукову базу FragFinn.de внесені тільки схвалені сайти. Згідно з заявою виробників, FragFinn.de є унікальною для Європи пошуковою системою. На розробку безпечної пошукової системи, у створенні якої брали участь такі великі компанії, як AOL, Microsoft, Google, Lycos, Vodafone і Deutsche Telekom, було витрачено 1,5 млн євро (2,2 млн доларів).</p>\n<p>Кожен відвідувач сайту може поскаржитися на посилання в результатах пошуку, яке не підходить для дітей. Ступінь захищеності пошукової системи від не призначеного для дітей вмісту, на перший погляд, здається вище, ніж у систем фільтрації інших пошукових машин, наприклад,  Google SafeSearch. Так, на запит "porn" FragFinn.de видав два результати, один з них - новина на CNN, інший - онлайн-музей образотворчого мистецтва. У відповідь на запити, що складалися з німецьких лайливих слів, пошукова система видала посилання на дитячі ресурси щодо захисту оточуючого середовища і сторінку однієї з німецьких радіостанцій.</p>'),
(5, 3, NULL, NULL, NULL),
(6, 1, 'Создан корпоративный сайт компании', 'На нашем сайте вы можете ознакомиться с продукцией нашей компании - см. раздел <A href="http://vishnu.colocall.net/~ponomarev/demo-energine/catalogue/">Каталог</A>, а также заказать интересующую вас продукцию.', NULL),
(6, 2, 'Создан корпоративный сайт компании', NULL, NULL),
(6, 3, 'Создан корпоративный сайт компании', NULL, NULL),
(7, 1, 'Новый погрузчик', 'В каталог продукции добавлен новый погрузчик <a href="catalogue/jgm755/">JGM755</a>.', '<img src="uploads/public/frontlifts/product_thumb_img_1202991303.jpg" width="100" height="100" align="" hspace="0" vspace="0" alt="" border="0" class="image_in_text left text_top" />В ассортименте нашей продукции появился новый фронтальный погрузчик JGM755. Он имеет следующие параметры грузоподъемности: стандартная масса загрузки ковша - 5 тонн,  объем ковша - 2.8 м3. Подробное описание погрузчика и его технические характеристики можно увидеть в каталоге: <a href="catalogue/jgm755/">JGM755</a>. '),
(7, 2, NULL, NULL, NULL),
(7, 3, NULL, NULL, NULL),
(8, 1, 'Новость 3', 'Новость, у которой нет конечного текста, а есть только анонс.', NULL),
(8, 2, NULL, NULL, NULL),
(8, 3, NULL, NULL, NULL),
(9, 1, 'Система Energine поддерживает концепцию модулей', '<p>Система Energine поддерживает концепцию модулей. Модуль - независимая часть приложения, которая добавляет специфическую функциональность к основному приложению.</p>', '<p>Система Energine поддерживает концепцию модулей. Модуль - независимая часть приложения, которая добавляет специфическую функциональность к основному приложению. Модуль включает набор компонентов, которые реализуют нужную функциональность, необходимые шаблоны, конфигурационные файлы и прочее.</p>'),
(9, 2, 'Система Energine поддерживает концепцию модулей', NULL, NULL),
(9, 3, 'Система Energine поддерживает концепцию модулей', NULL, NULL),
(10, 1, 'Система управления сайтами Energine', '<p>Система управления сайтами (CMS – content management system) Energine – гибкая модульная система, включающая в себя основные средства управления структурой и содержимым сайта.</p>', '<p>Система управления сайтами (CMS – content management system) Energine – гибкая модульная система, включающая в себя основные средства управления структурой и содержимым сайта. CMS Energine предоставляет возможность автоматизировать часть Вашей работы над сайтом, управлять пользователями, языковыми версиями, структурой разделов и подразделов сайта, редактировать страницы.</p>'),
(10, 2, 'Система управления сайтами Energine', NULL, NULL),
(10, 3, 'Система управления сайтами Energine', NULL, NULL),
(11, 1, 'Это полное и подробное руководство к CMS системе Energine', '<p>Это полное и подробное руководство к CMS системе Energine. Данное руководство предназначено для пользователей, которые занимаются наполнением сайтов, созданных с помощью CMS Energine.</p>', '<p>Сидит, значится, один мужик, рыбачит. Час сидит, два — не клюет. Скучно ему, да и холодно. Ну, он открыл бутылку водочки, налил в походный стаканчик 150, и только собрался эдак с чувством ее, мамочку, выкушать — клюет! Ну, мужик засуетился, неловко так подсек, и крохотный карасик ему прямо в стаканчик — плюх! Мужик брезгливо так выбросил карасика обратно в воду, водочку без кайфа заглотнул, насадил новую наживку, забросил удочку… И тут как поперло: судаки, лещи, щуки! Мужик еле успевает вытаскивать. И вот лежит в его корзине огромный сом и говорит:<br />\n— Ну, карасик, ну с#ка, ну провокатор! наливают, говорит, потом отпускают…</p>\n'),
(11, 2, 'Это полное и подробное руководство к CMS системе Energine', NULL, NULL),
(11, 3, 'Это полное и подробное руководство к CMS системе Energine', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `share_session`
--

CREATE TABLE IF NOT EXISTS `share_session` (
  `session_id` int(10) unsigned NOT NULL auto_increment,
  `session_native_id` varchar(40) NOT NULL default '',
  `session_last_impression` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `session_created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `session_user_agent` varchar(255) NOT NULL default '',
  `session_data` text,
  PRIMARY KEY  (`session_id`),
  UNIQUE KEY `session_native_id` (`session_native_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3028 ;

--
-- Дамп данных таблицы `share_session`
--

INSERT INTO `share_session` (`session_id`, `session_native_id`, `session_last_impression`, `session_created`, `session_user_agent`, `session_data`) VALUES
(3002, '0peqmsrmivsh1hkajjd9hcvbj4', '2008-04-10 11:19:45', '2008-04-10 11:19:45', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', NULL),
(3004, 'ua7b5tl9kqq9d2g0o29unjdap1', '2008-04-10 11:29:44', '2008-04-10 11:29:44', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)', NULL),
(3007, '5qgndnp8kgr5j1dfs0lmhd6ej4', '2008-04-10 13:15:31', '2008-04-10 13:15:31', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', NULL),
(3008, '32sd9lkndstma45bropddre687', '2008-04-10 16:47:39', '2008-04-10 13:15:38', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', 'userID|s:3:"113";'),
(3009, 'c47j6qcd763ll123p63ta2k2i5', '2008-04-10 14:06:05', '2008-04-10 14:06:05', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', NULL),
(3011, 'cs4bft7mfghuup97n4irg7d5m4', '2008-04-10 14:22:07', '2008-04-10 14:22:07', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)', NULL),
(3013, 'qj4ji501kvffo1klqodma0ve37', '2008-04-10 15:10:57', '2008-04-10 15:10:57', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)', NULL),
(3015, '622vn60uosn39bc1bvdd2sv0m0', '2008-04-10 15:32:37', '2008-04-10 15:32:37', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', NULL),
(3017, 'j3f2nhg6u9ollegkdj9nvddft6', '2008-04-10 16:18:41', '2008-04-10 16:18:41', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)', NULL),
(3018, 'mnpf4eed8k0to9mu4i0ec43fp4', '2008-04-10 16:55:03', '2008-04-10 16:55:03', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', NULL),
(3021, 'ts2s1nmgcqvsmiu2c8m3j1fg83', '2008-04-10 17:06:16', '2008-04-10 17:06:16', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', NULL),
(3022, 'rq63m0qma7gg2a7dv90rq2bcb7', '2008-04-10 17:10:47', '2008-04-10 17:07:00', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', 'userID|s:3:"113";'),
(3023, 'q6f8cl4cfgd9p51q18ii4h31u4', '2008-04-10 17:58:02', '2008-04-10 17:58:02', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', NULL),
(3024, '2br9jdshpu9tdm3qbnnecajuu0', '2008-04-10 18:01:12', '2008-04-10 17:58:12', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', 'userID|s:3:"113";'),
(3025, 'tis3d8h4euinesuknv6meeb8n5', '2008-04-10 18:03:00', '2008-04-10 18:03:00', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', NULL),
(3026, 'lrartdqvd03lt5jierq05ci8v2', '2008-04-10 18:04:20', '2008-04-10 18:03:26', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13', 'userID|s:3:"113";'),
(3027, 'h3j9qnc1u6fkej535tl3c4lr14', '2008-04-10 18:17:55', '2008-04-10 18:04:30', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; FDM)', 'userID|s:3:"113";');

-- --------------------------------------------------------

--
-- Структура таблицы `share_sitemap`
--

CREATE TABLE IF NOT EXISTS `share_sitemap` (
  `smap_id` int(10) unsigned NOT NULL auto_increment,
  `tmpl_id` int(10) unsigned NOT NULL default '0',
  `smap_pid` int(10) unsigned default NULL,
  `smap_segment` char(50) NOT NULL default '',
  `smap_is_final` tinyint(1) NOT NULL default '0',
  `smap_order_num` int(10) unsigned default '1',
  `smap_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `smap_default` tinyint(1) NOT NULL default '0',
  `smap_is_system` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`smap_id`),
  KEY `ref_sitemap_template_FK` (`tmpl_id`),
  KEY `ref_sitemap_parent_FK` (`smap_pid`),
  KEY `idx_order` (`smap_order_num`),
  KEY `smap_is_system` (`smap_is_system`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=362 ;

--
-- Дамп данных таблицы `share_sitemap`
--

INSERT INTO `share_sitemap` (`smap_id`, `tmpl_id`, `smap_pid`, `smap_segment`, `smap_is_final`, `smap_order_num`, `smap_modified`, `smap_default`, `smap_is_system`) VALUES
(7, 6, NULL, 'admin', 0, 17, '2008-02-13 17:46:14', 0, 0),
(8, 7, 73, 'user-editor', 0, 1, '2007-12-10 16:22:14', 0, 1),
(9, 8, 73, 'role-editor', 0, 2, '2007-12-10 16:23:10', 0, 1),
(73, 6, 7, 'users', 0, 7, '2007-12-10 16:21:16', 0, 0),
(80, 58, NULL, 'main', 0, 1, '2008-04-09 12:50:37', 1, 0),
(156, 6, 7, 'shop', 0, 2, '2008-04-08 14:53:49', 0, 0),
(158, 40, 156, 'product', 0, 1, '2007-12-10 16:23:51', 0, 0),
(161, 45, NULL, 'shop', 0, 7, '2008-04-08 16:22:04', 0, 0),
(288, 10, 7, 'langs', 0, 3, '2007-12-10 16:20:31', 0, 0),
(292, 42, 156, 'product-type-editor', 0, 2, '2007-12-10 16:25:15', 0, 0),
(324, 71, NULL, 'login', 1, 11, '2008-02-13 17:22:54', 0, 0),
(327, 69, NULL, 'news', 0, 3, '2008-02-14 18:02:39', 0, 0),
(329, 73, NULL, 'sitemap', 0, 9, '2008-04-02 19:24:45', 0, 0),
(330, 74, NULL, 'restore-password', 1, 16, '2008-04-02 19:20:45', 0, 0),
(331, 75, NULL, 'registration', 0, 6, '2008-04-02 19:29:37', 0, 0),
(332, 76, NULL, 'gallery', 0, 4, '2008-04-02 19:32:12', 0, 0),
(333, 76, 332, 'gallery-1', 0, 1, '2008-04-03 17:32:13', 0, 0),
(334, 76, 332, 'gallery2', 0, 2, '2008-04-03 16:27:36', 0, 0),
(335, 5, 349, 'profile', 0, 8, '2008-04-07 16:50:23', 0, 0),
(336, 72, NULL, 'feedback', 0, 5, '2008-04-04 13:45:10', 0, 0),
(337, 77, 7, 'feedback-list', 0, 1, '2008-04-08 14:52:50', 0, 0),
(338, 78, 161, 'basket', 1, 7, '2008-04-04 14:15:32', 0, 0),
(339, 79, 161, 'order', 1, 6, '2008-04-04 17:00:46', 0, 0),
(341, 84, 156, 'discount-editor', 0, 6, '2008-04-07 14:52:57', 0, 0),
(342, 82, 156, 'status-editor', 0, 4, '2008-04-08 14:49:36', 0, 0),
(343, 81, 156, 'producer-editor', 0, 3, '2008-04-08 14:16:44', 0, 0),
(344, 83, 156, 'currency-editor', 0, 5, '2008-04-07 14:29:49', 0, 0),
(345, 85, 156, 'order-status-editor', 0, 7, '2008-04-07 14:58:17', 0, 0),
(346, 86, 7, 'orders', 0, 8, '2008-04-08 14:51:53', 0, 0),
(348, 88, 349, 'user-oder-history', 0, 1, '2008-04-07 17:10:22', 0, 0),
(349, 6, NULL, 'personal-data', 0, 10, '2008-04-07 17:10:06', 0, 0),
(350, 45, 161, 'tv', 0, 5, '2008-04-08 16:24:19', 0, 0),
(351, 45, 161, 'cold', 0, 4, '2008-04-09 13:03:27', 0, 0),
(352, 45, 161, 'cofe', 0, 3, '2008-04-09 13:02:53', 0, 0),
(353, 45, 161, 'pilos', 0, 2, '2008-04-09 13:01:55', 0, 0),
(354, 45, 161, 'furnace', 0, 1, '2008-04-09 13:00:53', 0, 0),
(355, 6, NULL, 'instructions', 0, 2, '2008-04-10 13:36:17', 0, 0),
(357, 58, 355, 'control-panel', 0, 1, '2008-04-10 13:42:20', 0, 0),
(358, 58, 355, 'how-to-create-new-page', 0, 2, '2008-04-10 13:44:56', 0, 0),
(359, 58, 355, 'text-editing', 0, 3, '2008-04-10 13:53:38', 0, 0),
(360, 58, 355, 'dealing-with-files', 0, 4, '2008-04-10 13:55:45', 0, 0),
(361, 58, 355, 'rights-groups-users', 0, 5, '2008-04-10 13:58:00', 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `share_sitemap_translation`
--

CREATE TABLE IF NOT EXISTS `share_sitemap_translation` (
  `smap_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `smap_name` varchar(200) default NULL,
  `smap_description_rtf` text,
  `smap_html_title` varchar(250) default NULL,
  `smap_meta_keywords` text,
  `smap_meta_description` text,
  `smap_is_disabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`lang_id`,`smap_id`),
  KEY `sitemaplv_sitemap_FK` (`smap_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `share_sitemap_translation`
--

INSERT INTO `share_sitemap_translation` (`smap_id`, `lang_id`, `smap_name`, `smap_description_rtf`, `smap_html_title`, `smap_meta_keywords`, `smap_meta_description`, `smap_is_disabled`) VALUES
(7, 1, 'Управление сайтом', NULL, 'Управление сайтом', NULL, NULL, 0),
(8, 1, 'Редактирование пользователей', 'Управление списком пользователей системы.', NULL, NULL, NULL, 0),
(9, 1, 'Редактирование ролей', 'Управление группами пользователей - ролями.', NULL, NULL, NULL, 0),
(73, 1, 'Работа с пользователями', 'Управления пользователями и их ролями.', NULL, NULL, NULL, 0),
(80, 1, 'Демонстрационная версия CMS Energine', NULL, NULL, NULL, NULL, 0),
(156, 1, 'Управление магазином', 'Этот раздел предназначен для управления интернет-каталогом.', NULL, NULL, NULL, 0),
(158, 1, 'Редактор продуктов', 'Управление товарами и их параметрами.', NULL, NULL, NULL, 0),
(161, 1, 'Магазин', NULL, NULL, NULL, NULL, 0),
(288, 1, 'Редактор языков', 'Настройка языковых версий сайта.', NULL, NULL, NULL, 0),
(292, 1, 'Редактор типов продуктов', 'Управление группами продуктов с возможностью задавать общие для группы параметры.', NULL, NULL, NULL, 0),
(324, 1, 'Вход', NULL, NULL, NULL, NULL, 0),
(327, 1, 'Новости', NULL, NULL, NULL, NULL, 0),
(329, 1, 'Карта сайта', NULL, NULL, NULL, NULL, 0),
(330, 1, 'Восстановление пароля', NULL, NULL, NULL, NULL, 0),
(331, 1, 'Форма регистрации', NULL, NULL, NULL, NULL, 0),
(332, 1, 'Фотогалерея', NULL, NULL, NULL, NULL, 0),
(333, 1, 'Первая галерея', NULL, NULL, NULL, NULL, 0),
(334, 1, 'Еще одна галерея', NULL, NULL, NULL, NULL, 0),
(335, 1, 'Профайл', NULL, NULL, NULL, NULL, 0),
(336, 1, 'Форма контакта', NULL, NULL, NULL, NULL, 0),
(337, 1, 'Список сообщений', 'Архив сообщений формы контакта', NULL, NULL, NULL, 0),
(338, 1, 'Содержимое корзины', NULL, NULL, NULL, NULL, 0),
(339, 1, 'Форма заказа', NULL, NULL, NULL, NULL, 0),
(341, 1, 'Редактор скидок', NULL, NULL, NULL, NULL, 0),
(342, 1, 'Редактор статусов продуктов', 'Статусы предназначены для отображения наличия продуктов в магазине', NULL, NULL, NULL, 0),
(343, 1, 'Редактор производителей', 'Управление производителями', NULL, NULL, NULL, 0),
(344, 1, 'Редактор валют', NULL, NULL, NULL, NULL, 0),
(345, 1, 'Редактор статусов заказа', NULL, NULL, NULL, NULL, 0),
(346, 1, 'Список заказов', 'Архив заказов в магазине', NULL, NULL, NULL, 0),
(348, 1, 'История заказов', NULL, NULL, NULL, NULL, 0),
(349, 1, 'Персональные данные', NULL, NULL, NULL, NULL, 0),
(350, 1, 'Телевизоры', NULL, NULL, NULL, NULL, 0),
(351, 1, 'Холодильник', NULL, NULL, NULL, NULL, 0),
(352, 1, 'Кофемолки', NULL, NULL, NULL, NULL, 0),
(353, 1, 'Пылесосы', NULL, NULL, NULL, NULL, 0),
(354, 1, 'Микроволновки', NULL, NULL, NULL, NULL, 0),
(355, 1, 'Как работать с системой?', NULL, NULL, NULL, NULL, 0),
(357, 1, 'Панель управления - что это такое?', 'Краткое описание функциональности главного инструмента - панели управления.', NULL, NULL, NULL, 0),
(358, 1, 'Как создать страницу?', 'Инструкции по созданию новой страницы сайта.', NULL, NULL, NULL, 0),
(359, 1, 'Режим редактирования текста', 'Инструкции по редактированию содержимого страницы.', NULL, NULL, NULL, 0),
(360, 1, 'Работа с файлами и изображениями', 'Описание работы с хранилищем документов (файлов), используемых на сайте - так называемым "файловым репозиторием".', NULL, NULL, NULL, 0),
(361, 1, 'Права, роли, пользователи', 'Описание принципов разграничения прав пользователей системы.', NULL, NULL, NULL, 0),
(7, 2, 'Управління сайтом', NULL, NULL, NULL, NULL, 0),
(8, 2, 'Редагування користувачів', 'Управління списком користувачів системи.', NULL, NULL, NULL, 0),
(9, 2, 'Редагування ролей', 'Управління групами користувачів - ролями.', NULL, NULL, NULL, 0),
(73, 2, 'Робота з користувачами', 'Управління користувачами та їх ролями.', NULL, NULL, NULL, 0),
(80, 2, 'Демонстраційна версія CMS Energine', NULL, NULL, NULL, NULL, 0),
(156, 2, 'Управління магазином', 'Цей розділ призначений для управління інтернет-каталогом.', NULL, NULL, NULL, 0),
(158, 2, 'Редактор продуктів', 'Управління товарами та їх параметрами.', NULL, NULL, NULL, 0),
(161, 2, 'Магазин', NULL, NULL, NULL, NULL, 0),
(288, 2, 'Редактор мов', 'Налаштування мовних версій сайту.', NULL, NULL, NULL, 0),
(292, 2, 'Редактор типів продуктів', 'Управління групами продуктів з можливістю встановлювати загальні для групи параметри.', NULL, NULL, NULL, 0),
(324, 2, 'Вхід', NULL, NULL, NULL, NULL, 0),
(327, 2, 'Новини', NULL, NULL, NULL, NULL, 0),
(329, 2, 'Карта сайту', NULL, NULL, NULL, NULL, 0),
(330, 2, 'Поновлення паролю', NULL, NULL, NULL, NULL, 0),
(331, 2, 'Форма реєстрації', NULL, NULL, NULL, NULL, 0),
(332, 2, 'Галерея світлин', NULL, NULL, NULL, NULL, 0),
(333, 2, 'Подраздел галереи', NULL, NULL, NULL, NULL, 0),
(334, 2, 'Еще одна галерея', NULL, NULL, NULL, NULL, 0),
(335, 2, 'Профайл', NULL, NULL, NULL, NULL, 0),
(336, 2, 'Форма контакта', NULL, NULL, NULL, NULL, 0),
(337, 2, 'Список повідомлень', NULL, NULL, NULL, NULL, 0),
(338, 2, 'Вміст кошика', NULL, NULL, NULL, NULL, 0),
(339, 2, 'Форма замовлення', NULL, NULL, NULL, NULL, 0),
(341, 2, 'Редактор знижок', NULL, NULL, NULL, NULL, 0),
(342, 2, 'Редактор статусів продуктів ', 'Статуси призначені для відображення наявності продуктів в магазині.', NULL, NULL, NULL, 0),
(343, 2, 'Редактор виробників', 'Управління виробниками', NULL, NULL, NULL, 0),
(344, 2, 'Редактор валют', NULL, NULL, NULL, NULL, 0),
(345, 2, 'Редактор статусів замовлення', NULL, NULL, NULL, NULL, 0),
(346, 2, 'Список замовлень', NULL, NULL, NULL, NULL, 0),
(348, 2, 'Історія замовлень', NULL, NULL, NULL, NULL, 0),
(349, 2, 'Персональні дані', NULL, NULL, NULL, NULL, 0),
(350, 2, 'Телевізори', NULL, NULL, NULL, NULL, 0),
(351, 2, 'Холодильник', NULL, NULL, NULL, NULL, 0),
(352, 2, 'Млини', NULL, NULL, NULL, NULL, 0),
(353, 2, 'Пилососи', NULL, NULL, NULL, NULL, 0),
(354, 2, 'Мікрохвильові', NULL, NULL, NULL, NULL, 0),
(355, 2, 'Як працювати з системою?', NULL, NULL, NULL, NULL, 0),
(357, 2, 'Панель управління - що це таке?', NULL, NULL, NULL, NULL, 0),
(358, 2, 'Як створити сторінку?', NULL, NULL, NULL, NULL, 0),
(359, 2, 'Режим редагування тексту', NULL, NULL, NULL, NULL, 0),
(360, 2, 'Робота з файлами та зображеннями', NULL, NULL, NULL, NULL, 0),
(361, 2, 'Права, ролі, користувачі', NULL, NULL, NULL, NULL, 0),
(7, 3, 'Site management', NULL, NULL, NULL, NULL, 0),
(8, 3, 'Users editor', NULL, NULL, NULL, NULL, 0),
(9, 3, 'Role editor', NULL, NULL, NULL, NULL, 0),
(73, 3, 'Edit users and roles', NULL, NULL, NULL, NULL, 0),
(80, 3, 'Energine CMS demo version', NULL, NULL, NULL, NULL, 0),
(156, 3, 'Shop management', NULL, NULL, NULL, NULL, 0),
(158, 3, 'Products editor', NULL, NULL, NULL, NULL, 0),
(161, 3, 'Shop', NULL, NULL, NULL, NULL, 0),
(288, 3, 'Languages editor', NULL, NULL, NULL, NULL, 0),
(292, 3, 'Product type editor', NULL, NULL, NULL, NULL, 0),
(324, 3, 'Entrance', NULL, NULL, NULL, NULL, 0),
(327, 3, 'News', NULL, NULL, NULL, NULL, 0),
(329, 3, 'Sitemap', NULL, NULL, NULL, NULL, 0),
(330, 3, 'Restore password', NULL, NULL, NULL, NULL, 0),
(331, 3, 'Registration form', NULL, NULL, NULL, NULL, 0),
(332, 3, 'Photogallery', NULL, NULL, NULL, NULL, 0),
(333, 3, 'Подраздел галереи', NULL, NULL, NULL, NULL, 0),
(334, 3, 'Еще одна галерея', NULL, NULL, NULL, NULL, 0),
(335, 3, 'Profile', NULL, NULL, NULL, NULL, 0),
(336, 3, 'Форма контакта', NULL, NULL, NULL, NULL, 0),
(337, 3, 'Feedback list', NULL, NULL, NULL, NULL, 0),
(338, 3, 'Basket', NULL, NULL, NULL, NULL, 0),
(339, 3, 'Order form', NULL, NULL, NULL, NULL, 0),
(341, 3, 'Discount editor', NULL, NULL, NULL, NULL, 0),
(342, 3, 'Product status editor', NULL, NULL, NULL, NULL, 0),
(343, 3, 'Producer editor', NULL, NULL, NULL, NULL, 0),
(344, 3, 'Currency editor', NULL, NULL, NULL, NULL, 0),
(345, 3, 'Order status editor', NULL, NULL, NULL, NULL, 0),
(346, 3, 'Order history', NULL, NULL, NULL, NULL, 0),
(348, 3, 'Order history', NULL, NULL, NULL, NULL, 0),
(349, 3, 'Personal data', NULL, NULL, NULL, NULL, 0),
(350, 3, 'TV', NULL, NULL, NULL, NULL, 0),
(351, 3, 'Cold', NULL, NULL, NULL, NULL, 0),
(352, 3, 'Cofe', NULL, NULL, NULL, NULL, 0),
(353, 3, 'Pil', NULL, NULL, NULL, NULL, 0),
(354, 3, 'Furc', NULL, NULL, NULL, NULL, 0),
(355, 3, 'How to work with system?', NULL, NULL, NULL, NULL, 0),
(357, 3, 'Control panel - what''s it?', NULL, NULL, NULL, NULL, 0),
(358, 3, 'How to create new page?', NULL, NULL, NULL, NULL, 0),
(359, 3, 'Text edit mode', NULL, NULL, NULL, NULL, 0),
(360, 3, 'Working with files and images', NULL, NULL, NULL, NULL, 0),
(361, 3, 'Rights, roles, users', NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `share_tags`
--

CREATE TABLE IF NOT EXISTS `share_tags` (
  `tag_id` int(10) unsigned NOT NULL auto_increment,
  `tag_name` char(30) NOT NULL default '',
  `tag_text` char(50) NOT NULL default '',
  PRIMARY KEY  (`tag_id`),
  UNIQUE KEY `tag_const` (`tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `share_tags`
--


-- --------------------------------------------------------

--
-- Структура таблицы `share_tag_map`
--

CREATE TABLE IF NOT EXISTS `share_tag_map` (
  `smap_id` int(10) unsigned NOT NULL default '0',
  `tag_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`smap_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `share_tag_map`
--


-- --------------------------------------------------------

--
-- Структура таблицы `share_templates`
--

CREATE TABLE IF NOT EXISTS `share_templates` (
  `tmpl_id` int(10) unsigned NOT NULL auto_increment,
  `tmpl_name` char(150) NOT NULL default '',
  `tmpl_layout` char(50) NOT NULL default '',
  `tmpl_content` char(50) NOT NULL default '',
  `tmpl_is_system` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`tmpl_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=89 ;

--
-- Дамп данных таблицы `share_templates`
--

INSERT INTO `share_templates` (`tmpl_id`, `tmpl_name`, `tmpl_layout`, `tmpl_content`, `tmpl_is_system`) VALUES
(3, 'Два текстовых блока', 'default.layout.xml', 'textblock2.content.xml', 0),
(5, 'Форма редактирования данных текущего пользователя', 'default.layout.xml', 'user_profile.content.xml', 0),
(6, 'Список подразделов', 'default.layout.xml', 'childs.content.xml', 0),
(7, 'Редактор пользователей', 'admin.layout.xml', 'user_editor.content.xml', 1),
(8, 'Редактор ролей', 'admin.layout.xml', 'role_editor.content.xml', 1),
(10, 'Редактор языков', 'admin.layout.xml', 'lang_editor.content.xml', 0),
(11, 'Редактор шаблонов', 'admin.layout.xml', 'template_editor.content.xml', 1),
(14, 'Редактор разделов', 'admin.layout.xml', 'div_editor.content.xml', 1),
(40, 'Редактор продуктов', 'default.layout.xml', 'product_editor.content.xml', 1),
(42, 'Редактор типов продуктов', 'default.layout.xml', 'product_type_editor.content.xml', 0),
(43, 'Список продуктов', 'catalogue.layout.xml', 'product_list.content.xml', 0),
(45, 'Список товарных разделов', 'catalogue.layout.xml', 'product_division_list.content.xml', 0),
(58, 'Один текстовый блок', 'default.layout.xml', 'textblock.content.xml', 0),
(69, 'Лента новостей', 'default.layout.xml', 'news.content.xml', 0),
(71, 'Форма авторизации', 'default.layout.xml', 'login.content.xml', 0),
(72, 'Форма контакта', 'default.layout.xml', 'feedback_form.content.xml', 0),
(73, 'Карта сайта', 'default.layout.xml', 'map.content.xml', 0),
(74, 'Форма восстановления пароля', 'default.layout.xml', 'restore_password.content.xml', 0),
(75, 'Форма регистрации', 'default.layout.xml', 'register.content.xml', 0),
(76, 'Фотогалерея', 'default.layout.xml', 'gallery.content.xml', 0),
(77, 'Список сообщений', 'admin.layout.xml', 'feedback_list.content.xml', 0),
(78, 'Корзина', 'default.layout.xml', 'basket.content.xml', 0),
(79, 'Форма заказа', 'order.layout.xml', 'order.content.xml', 0),
(81, 'Редактор производителей', 'admin.layout.xml', 'producer_editor.content.xml', 0),
(82, 'Редактор статусов', 'admin.layout.xml', 'product_status_editor.content.xml', 0),
(83, 'Редактор валют', 'default.layout.xml', 'curr_editor.content.xml', 0),
(84, 'Редактор скидок', 'default.layout.xml', 'discounts_editor.content.xml', 0),
(85, 'Редактор статусов заказа', 'default.layout.xml', 'order_status.content.xml', 0),
(86, 'Список заказов', 'default.layout.xml', 'order_history.content.xml', 0),
(88, 'История заказов', 'default.layout.xml', 'user_order_history.content.xml', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `share_textblocks`
--

CREATE TABLE IF NOT EXISTS `share_textblocks` (
  `tb_id` int(10) unsigned NOT NULL auto_increment,
  `smap_id` int(10) unsigned default NULL,
  `tb_num` char(50) NOT NULL default '1',
  PRIMARY KEY  (`tb_id`),
  UNIQUE KEY `smap_id` (`smap_id`,`tb_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=117 ;

--
-- Дамп данных таблицы `share_textblocks`
--

INSERT INTO `share_textblocks` (`tb_id`, `smap_id`, `tb_num`) VALUES
(70, NULL, '1'),
(73, NULL, 'ContactTextBlock'),
(74, NULL, 'FeedbackTextBlock'),
(82, NULL, 'FooterLoginBlock'),
(65, NULL, 'FooterTextBlock'),
(77, NULL, 'InfoTextBlock'),
(108, NULL, 'PartnersTextBlock'),
(72, NULL, 'PaymentTextBlock'),
(105, NULL, 'SidebarTextBlock'),
(89, 7, '1'),
(11, 8, '1'),
(13, 9, '1'),
(16, 80, '1'),
(83, 80, '2'),
(104, 80, '3'),
(94, 158, '1'),
(90, 288, '1'),
(95, 292, '1'),
(116, 355, '1'),
(111, 357, '1'),
(112, 358, '1'),
(113, 359, '1'),
(114, 360, '1'),
(115, 361, '1');

-- --------------------------------------------------------

--
-- Структура таблицы `share_textblocks_translation`
--

CREATE TABLE IF NOT EXISTS `share_textblocks_translation` (
  `tb_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `tb_content` text NOT NULL,
  UNIQUE KEY `tb_id` (`tb_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `share_textblocks_translation`
--

INSERT INTO `share_textblocks_translation` (`tb_id`, `lang_id`, `tb_content`) VALUES
(11, 1, '<p>\n  Редактор пользователей позволяет\n  добавлять, редактировать, удалять\n  пользователей сайта, а также\n  назначать им роль.\n</p>\n<ul>\n  <li>\n    <strong>Добавить<br></strong>Позволяет\n    добавить нового пользователя в\n    систему.<br>\n    <em>Логин (e-mail)</em> - логин\n    пользователя (уникальное в\n    пределах системы имя\n    пользователя) является\n    одновременно его контактным\n    почтовым адресом.<br>\n    <em>Пароль</em> - пароль для нового\n    пользователя.<br>\n    <em>Группы</em> - группа, в которую\n    входит пользователь, его роль с\n    системе; пользователь может\n    состоять в нескольких группах\n    одновременно - используйте ctrl или\n    shift для выбора нескольких групп из\n    списка.\n  </li>\n  <li>\n    <strong>Редактировать<br></strong>Эта\n    функция позволяет редактировать\n    данные выбранного пользователя\n    (см. выше "Добавить").\n  </li>\n  <li>\n    <strong>Удалить<br></strong>Удаляет\n    выбранного пользователя.<br>\n    <span style="color: red;">Внимание! Нельзя\n    удалить текущего пользователя,\n    т.е. самого себя.</span>\n  </li>\n</ul>'),
(13, 1, '<p>\n  Редактор групп позволяет\n  добавлять, редактировать и удалять\n  пользовательские группы.\n</p>\n<ul>\n  <li>\n    <strong>Добавить</strong><br>\n    Позволяет добавить новую\n    пользовательскую группу в\n    систему.<br>\n    <em>Имя группы</em> - уникальное в\n    пределах системы имя группы.<br>\n    <em>По умолчанию для гостей</em> -\n    данный флаг определяет группу,\n    которая будет автоматически\n    присвоена гостям\n    (неаутентифицированным\n    посетителям) сайта.<br>\n    <em>По умолчанию для\n    пользователей</em> - данный флаг\n    определяет группу, которая будет\n    автоматически присвоена новым\n    зарегистрированным пользователям\n    сайта.\n  </li>\n  <li>\n    <strong>Редактировать</strong><br>\n    Открывает окно редактирования\n    выбранной группы (см. выше\n    описание добавления группы).\n  </li>\n  <li>\n    <strong>Удалить</strong><br>\n    Удаляет выбранную группу.\n  </li>\n</ul>'),
(16, 1, '<p>\n  Чтобы перейти в административный\n  режим, вам необходимо\n  авторизоваться. Для этого введите в\n  расположенную слева форму\n  следующие значения:\n</p>\n<ul>\n  <li>Имя пользователя:\n  <strong>demo@energine.org</strong>\n  </li>\n  <li>Пароль: <strong>demo</strong>\n  </li>\n</ul>\n<p>\n  Административная часть работает\n  под управлением браузеров:\n</p>\n<ul>\n  <li>Internet Explorer 5.5 и выше\n  </li>\n  <li>Firefox 1.5 и выше\n  </li>\n</ul>\n<p>\n  <span>Правильная работа\n  административной части под другими\n  браузерами - не\n  гарантируется.</span><br />\n  В некоторых случаях антивирусное\n  ПО и программы блокировщики\n  рекламы могут блокировать работу\n  JavaScript, что делает невозможным\n  работу с административной частью.\n</p>'),
(16, 2, ''),
(65, 1, '<span class="copyright">Powered by <a href= "http://energine.org/">Energine</a></span>'),
(65, 2, '<span class="copyright">Powered by <a href= "http://energine.org/">Energine</a></span> <span class= "copyright">Copyright JGT Ukraine 2008</span>'),
(65, 3, '<span class="copyright">Powered by <a href= "http://energine.org/">Energine</a></span> <span class= "copyright">Copyright JGT Ukraine 2008</span>'),
(70, 1, '<p>\n  Существует два способа создания\n  страницы.\n</p>\n<ol>\n  <li>\n    <div>\n      Создание страницы из панели\n      управления.\n    </div>\n  </li>\n  <li>\n    <div>\n      Создание страницы с помощью\n      редактора разделов.\n    </div>\n  </li>\n</ol>\n<p>\n  Для создания страницы из панели\n  управления необходимо зайти на\n  раздел, в котором будет находиться\n  новая страница, и нажать на кнопку\n  Добавить страницу в панели\n  управления.\n</p><img src="uploads/public/control-panel/1207829716.jpg" alt= "iz-1" align="" border="0" height="142" hspace="0" vspace="0" width="500" />\n<p>\n  После нажатия кнопки открывается\n  окно создания страницы.На форме\n  находятся несколько\n  вкладок(Свойства, Русский,\n  Украинский, Английский, Права на\n  страницу).\n</p>\n<p>\n  <strong>Вкладка Свойства</strong> - тут\n  находятся основные поля,\n  определяющие поведение и положение\n  страницы в иерархии сайта.\n</p>\n<p>\n  <img alt=   "Создание страницы - вкладка Свойства"   src="uploads/how-to-create-page/1174390903.jpg" border="0"   height="301" hspace="0" width="400" />\n</p>\n<p>\n  Родительский раздел. Указывает на\n  раздел в котором будет находиться\n  страница. При нажатии на кнопку "..."\n  открывается окно выбора разделов, в\n  котором необходимо выбрать\n  необходимый раздел.\n</p>\n<p>\n  <img alt="Выбор родительского раздела"   src="uploads/how-to-create-page/1174392215.jpg" border="0"   height="305" hspace="0" width="400" />\n</p>\n<p>\n  По умолчанию - указан текущий\n  раздел.\n</p>\n<p>\n  Сегмент URI. Это часть адреса\n  страницы, которая однозначно\n  идентифицирует эту страницу.\n  Например сегмент URI текущей\n  страницы - "how-to-create-new-page". Сегмент URI\n  может содержать только маленькие\n  латинские бувы, цифры и знак "-".\n</p>\n<p>\n  Шаблон страницы - перечень\n  компонентов на этой странице. Дл%\n</p>'),
(72, 1, '<strong>Принимаем к оплате</strong><br>\nТут у нас текст о том какие карточки\nпринимаются к оплате. А кроме того,\nтут еще и переключаетль валют, а\nвозможно еще и курсы валют.'),
(73, 1, '<strong>Контакты</strong><br>\nСвжитесь с нашим онлайн\nконсультантом по телефону 322-2-322 и\nфаксу 123-45-67 а также по мылу <a href= "mailto:fig@otvechu.com">fig_otvechu@malimar.com.ua</a>, и\nкроме того по аське 12222222222222'),
(74, 1, '<strong>Напишите нам</strong><br>\nА тут кстати неплохо было бы\nнаверное какой то визуал повесить.<br>\n<a href="feedback/">Напишите нам</a>'),
(77, 1, '<strong>высококачественныe картриджи\nдля всех типов принтеров ведущих\nмировых производителей</strong>'),
(82, 1, 'Вы можете <a href="login/">войти</a> в\nсистему, если у вас есть аккаунт.'),
(83, 1, ''),
(83, 2, ''),
(89, 1, '<p>\n  Раздел "Управление сайтом"\n  предоставляет администратору\n  сайта возможность управлять\n  пользователями, языковыми\n  версиями, интернет-магазином и\n  другими функциональностями.\n</p>'),
(89, 2, '<p>\n  Розділ "Управління сайтом" надає\n  адміністратору сайту можливість\n  керувати користувачами, мовними\n  версіями, інтернет-магазином та\n  іншими функціональностями.\n</p>'),
(90, 1, '<p>\n  В стандартную версию системы\n  управления сайтом входит поддержка\n  русского, украинского и\n  английского языка. С помощью\n  редактора языков можно добавлять,\n  удалять и редактировать\n  дополнительные языки. Система\n  поддерживает неограниченное\n  количество языков.\n</p>\n<ul>\n  <li>\n    <strong>Добавить<br></strong>Позволяет\n    добавить новый язык в систему.<br>\n    <em>Аббревиатура языка</em> -\n    используется как часть адреса\n    страницы для определения ее\n    языковой версии; рекомендуется\n    использовать общепринятые\n    двухбуквенные сокращения,\n    например: en, ru, ua.<br>\n    <em>Название языка</em> - используется\n    для обозначения языка, например, в\n    языковых вкладках в\n    административной части.<br>\n    <em>Язык по умолчанию</em> - флаг,\n    который определяет основной язык\n    сайта, т.е. ту языковую версию,\n    которая будет отображена, если в\n    строке адреса набрать адрес сайта.\n  </li>\n  <li>\n    <strong>Редактировать<br></strong>Позволяет\n    редактировать данные выбранного\n    языка (см. выше "Добавить").\n  </li>\n  <li>\n    <strong>Удалить<br></strong>Удаляет\n    выбранный язык.<br>\n    <span style="color: red;">Внимание! Нельзя\n    удалить текущий язык и язык по\n    умолчанию.</span>\n  </li>\n  <li>\n    <strong>Поднять</strong> и\n    <strong>Опустить</strong><br>\n    Позволяет менять порядок\n    расположения языков в списке (в\n    языковых вкладках, в\n    переключателе языков).\n  </li>\n</ul>'),
(90, 2, '<p>\n  В стандартну версію системи\n  управління сайтом входить\n  підтримка російської, української\n  та англійської мови. За допомогою\n  редактора мов можна додавати,\n  видаляти і редагувати додаткові\n  мови. Система підтримує необмежену\n  кількість мов.\n</p>\n<ul>\n  <li>\n    <strong>Додати</strong><br>\n    Дозволяє додати нову мову у\n    систему.<br>\n    <em>Абревіатура мови</em> -\n    використовується як частина\n    адреси сторінки для визначення її\n    мовної версії; рекомендується\n    використовувати загальноприйняті\n    скорочення з двох літер,\n    наприклад, en, ru, ua.<br>\n    <em>Назва мови</em> - використовується\n    для позначення мови, наприклад, в\n    мовних вкладках в\n    адміністративній частині. <em>Мова\n    за замовчанням</em> - помітка, що\n    визначає основну мову сайту, тобто\n    ту мовну версію, яка буде\n    відображеня, якщо у рядку адреси\n    набрати адресу сайту.\n  </li>\n  <li>\n    <strong>Редагувати<br></strong>Дозволяє\n    редагувати дані вибраної мови\n    (див. вище "Додати").\n  </li>\n  <li>\n    <strong>Видалити<br></strong>Видаляє\n    вибрану мову.<br>\n    <span style="color: red;">Увага! Не можна\n    видалити поточну мову і мову за\n    замовчанням.</span>\n  </li>\n  <li>\n    <strong>Підняти</strong> і\n    <strong>Опустити</strong><br>\n    Дозволяє міняти порядок\n    розташування мов у списку (в\n    мовних вкладках, в перемикачі мов).\n  </li>\n</ul>'),
(94, 1, '<p>\n  Редактор продуктов позволяет\n  оперировать списком продуктов -\n  добавлять, редактировать и удалять\n  товары, а также редактировать их\n  параметры.\n</p>\n<ul>\n  <li>\n    <strong>Добавить<br></strong>Открывает\n    диалоговое окно добавления нового\n    товара в каталог.<br>\n    <em>Категория</em> - позволяет отнести\n    продукт к определенной категории\n    (категории являются по сути\n    подразделами каталога).<br>\n    <em>Код товара</em> - внутренний код\n    продукта в магазине, может\n    использоваться для загрузки\n    прайс-листов.<br>\n    <em>Тип товара</em> - позволяет\n    отнести продукт к определенному\n    типу; типовым продуктам\n    назначается одинаковый перечень\n    параметров - см. <a href=     "admin/shop/product-type-editor/">редактор типов\n    продуктов</a>.<br>\n    <em>Сегмент URI</em> - уникальная часть\n    адреса страницы, на которой\n    находится подробное описание\n    продукта.<br>\n    <em>Фотография</em> - позволяет\n    загрузить фотографию продукта в\n    файловый репозиторий; после\n    добавления продукта система\n    автоматически генерирует\n    небольшую копию фотографии для\n    отображения в списке продуктов.<br>\n  </li>\n  <li>\n    <strong>Редактировать<br></strong>Эта\n    функция позволяет редактировать\n    данные выбранного продукта (см.\n    выше "Добавить").<br>\n    <span style="color: red;">Внимание! По\n    технологическим причинам\n    отсутствует возможность изменять\n    параметр "Тип товара".</span>\n  </li>\n  <li>\n    <strong>Редактировать\n    параметры</strong><br>\n    Позволяет задать параметры\n    продукта.<br>\n    <span style="color: red;">Внимание! Чтобы\n    изменять параметры, необходимо\n    вначале создать их список для\n    данного типа продуктов с помощью\n    <a href="admin/shop/product-type-editor/">редактора\n    типов продуктов</a>.</span>\n  </li>\n  <li>\n    <strong>Удалить<br></strong>Удаляет\n    выбранный продукт.<br>\n  </li>\n</ul>'),
(95, 1, '<p>\n  Редактор типов продуктов\n  позволяет оперировать списком\n  типов продуктов - добавлять,\n  редактировать и удалять типовые\n  группы продуктов, а также создавать\n  списки параметров для типовых\n  групп.\n</p>\n<ul>\n  <li>\n    <strong>Добавить</strong><br>\n    Добавляет новый тип продуктов.<br>\n  </li>\n  <li>\n    <strong>Редактировать<br></strong>Позволяет\n    редактировать данные выбранного\n    типа продуктов.\n  </li>\n  <li>\n    <strong>Удалить<br></strong>Удаляет\n    выбранный тип продуктов.<br>\n  </li>\n  <li>\n    <strong>Параметры товара</strong><br>\n    Эта функция позволяет создать\n    список параметров для выбранного\n    типа продуктов. Значения этих\n    параметров для конкретных\n    продуктов устанавливаются с\n    помощью <a href="admin/shop/product/">редактора\n    продуктов</a>.\n  </li>\n</ul>'),
(104, 1, '<h3 class="blue_title">\n  Перспективные проекты\n</h3>\n<p>\n  <span>В нынешнем году\n  <strong>&laquo;</strong></span><strong><span>JGT</span>\n  Украина&raquo;</strong> <span>получает\n  эксклюзивные права на продажу в\n  Украине продукции одного из лучших\n  и старейших (с 1985 г.) китайских\n  производителей оборудования по\n  выпуску изделий их\n  влаговпитывающих материалов\n  (памперсы, гигиенические прокладки,\n  подкладки и проч.).</span>\n</p><span>Ищем партнера для налаживания\nсотрудничества.</span>\n<p class="no_margin">\n  <img class="image_in_text" height="123" alt="Ковш" hspace="0"   src=   "uploads/public/images-in-content/1202745731.jpg"   width="200" vspace="5" border="0">\n</p>'),
(105, 1, '<p>\n  8(050)581-36-57\n</p>\n<p>\n  8(067)579-54-84\n</p>\n<p>\n  ул. Георгиевская, 10\n</p>\n<p>\n  e-mail: <a href=   "mailto:rigel-info@yandex.ru">rigel-info@yandex.ru</a>\n</p>'),
(105, 2, '<p>\n  8(050)581-36-57\n</p>\n<p>\n  8(067)579-54-84\n</p>\n<p>\n  вул. Георгіївська, 10\n</p>\n<p>\n  e-mail: <a href=   "mailto:rigel-info@yandex.ru">rigel-info@yandex.ru</a>\n</p>'),
(105, 3, '<p>\n  8(050)581-36-57\n</p>\n<p>\n  8(067)579-54-84\n</p>\n<p>\n  Georgievskaya str. 10\n</p>\n<p>\n  e-mail: <a href=   "mailto:rigel-info@yandex.ru">rigel-info@yandex.ru</a>\n</p>'),
(108, 1, '<p>\n  Jingong Machinery\n</p>\n<p>\n  <a href="http://www.china-jingong.com/" target="_blank"><img src=   "uploads/public/partners-logo/1203934701.gif" alt="JGM" align=""   border="0" height="34" hspace="0" vspace="0" width="222"></a>\n</p><br>\n<p>\n  Альфа Техно Импорт\n</p>\n<p>\n  <a href="http://www.alphati.com/" target="_blank"><img src=   "uploads/public/partners-logo/1203935198.gif" alt=   "Альфа Техно Импорт " align="" border="0" height=   "52" hspace="0" vspace="0" width="150"></a>\n</p>'),
(108, 2, '<p>\n  Jingong Machinery\n</p>\n<p>\n  <a href="http://www.china-jingong.com/" target=   "_blank"><img height="34" alt="JGM" hspace="0" src=   "uploads/public/partners-logo/1203934701.gif"   width="222" border="0"></a>\n</p><br>\n<p>\n  Альфа Техно Імпорт\n</p>\n<p>\n  <a href="http://www.alphati.com/" target="_blank"><img height=   "52" alt="Альфа Техно Импорт " hspace="0" src=   "uploads/public/partners-logo/1203935198.gif"   width="150" border="0"></a>\n</p>'),
(108, 3, '<p>\n  Jingong Machinery\n</p>\n<p>\n  <a href="http://www.china-jingong.com/" target=   "_blank"><img height="34" alt="JGM" hspace="0" src=   "uploads/public/partners-logo/1203934701.gif"   width="222" border="0"></a>\n</p><br>\n<p>\n  Alfa Tehno Import\n</p>\n<p>\n  <a href="http://www.alphati.com/" target="_blank"><img height=   "52" alt="Alfa Tehno Import" hspace="0" src=   "uploads/public/partners-logo/1203935198.gif"   width="150" border="0"></a>\n</p>'),
(111, 1, '<p>\n  Панель управления предназначена\n  для быстрого редактирования\n  свойств страницы, создания новых\n  страниц, доступа к режиму\n  редактирования, структуре сайта,\n  редактору шаблонов, редактору\n  переводов и хранилищу файлов.\n</p>\n<p>\n  Панель управления появляется\n  вверху страницы после авторизации,\n  если у пользователя на данную\n  страницу выставлен уровень прав\n  "Редактирование" и выше.\n</p><img alt="control" src= "uploads/public/control-panel/1207827603.jpg" border="0" height= "182" hspace="0" width="500" /><br />\n<br />\n<p>\n  На панели управления расположены\n  следующие кнопки:\n</p>\n<ul>\n  <li>Режим редактирования -\n  переключает страницу в режим\n  редактирования текста.\n  </li>\n  <li>Добавить страницу - открывает\n  форму создания новой страницы.\n  </li>\n  <li>Редактировать страницу -\n  открывает форму редактирования\n  свойств текущей страницы.\n  </li>\n  <li>Структура сайта - открывает форму\n  управления структурой сайта, его\n  разделами и подразделами.\n  </li>\n  <li>Шаблоны - открывает форму\n  редактора шаблонов.\n  </li>\n  <li>Переводы - открывает форму\n  редактирования языковых констант\n  сайта.\n  </li>\n  <li>Файловый репозиторий - открывает\n  диалоговое окно хранилища файлов.\n  </li>\n</ul>'),
(112, 1, '<p>\n  Существует два способа создания\n  страницы.\n</p>\n<ol>\n  <li>\n    <div>\n      Создание страницы из панели\n      управления.\n    </div>\n  </li>\n  <li>\n    <div>\n      Создание страницы с помощью\n      редактора разделов.\n    </div>\n  </li>\n</ol>\n<p>\n  Для создания страницы из панели\n  управления необходимо зайти на\n  раздел, в котором будет находиться\n  новая страница, и нажать на кнопку\n  Добавить страницу в панели\n  управления.\n</p><img alt="панель управления" src= "uploads/public/control-panel/1207829716.jpg" border="0" height= "142" hspace="0" width="500" />\n<p>\n  После нажатия кнопки открывается\n  окно создания страницы.На форме\n  находятся несколько\n  вкладок(Свойства, Русский,\n  Украинский, Английский, Права на\n  страницу).\n</p>\n<p>\n  <strong>Вкладка Свойства</strong> - тут\n  находятся основные поля,\n  определяющие поведение и положение\n  страницы в иерархии сайта.\n</p><img alt="вкладка свойства" src= "uploads/public/control-panel/1207829748.jpg" border="0" height= "301" hspace="0" width="400" />\n<p>\n  Родительский раздел. Указывает на\n  раздел в котором будет находиться\n  страница. При нажатии на кнопку "..."\n  открывается окно выбора разделов, в\n  котором необходимо выбрать\n  необходимый раздел.\n</p>\n<p>\n  <img alt="iz-3" src="uploads/public/control-panel/1207829770.jpg"   border="0" height="305" hspace="0" width="400" />\n</p>\n<p>\n  По умолчанию - указан текущий\n  раздел.\n</p>\n<p>\n  Сегмент URI. Это часть адреса\n  страницы, которая однозначно\n  идентифицирует эту страницу.\n  Например сегмент URI текущей\n  страницы - "how-to-create-new-page". Сегмент URI\n  может содержать только маленькие\n  латинские бувы, цифры и знак "-".\n</p>\n<p>\n  Шаблон страницы - перечень\n  компонентов на этой странице. Для\n  создания простой текстовой\n  страницы укажите шаблон "Один\n  текстовый блок".\n</p>\n<p>\n  Конечный раздел - флаг, указывающий\n  на то, что у страницы не может быть\n  дочерних разделов. Конечные\n  разделы не отображаются в меню.\n</p>\n<p>\n  <strong>Вкладки Украинский, Русский,\n  Английский</strong>. На этих вкладках\n  расположены языкозависимые поля.\n  Перечень полей на вкладках -\n  идентичный.\n</p>\n<p>\n  <img alt="iz-4" src="uploads/public/control-panel/1207829792.jpg"   border="0" height="301" hspace="0" width="400" />\n</p>\n<p>\n  Отключен - флаг, указывающий на то,\n  что в данной языковой версии раздел\n  не существует. Должна существовать\n  версия хотя бы для одного языка.\n</p>\n<p>\n  Название раздела - собственно, это\n  название страницы.\n</p>\n<p>\n  Описание раздела - краткая\n  аннотация к разделу. Выводится в\n  списке подразделов. Это поле может\n  содержать картинки и\n  форматированный текст.\n</p>\n<p>\n  Ключевые слова, мета-описание -\n  используются для описания страницы\n  при индексации ее поисковыми\n  машинами.\n</p>\n<p>\n  <strong>Вкладка Права на страницу</strong>.\n  На этой вкладке задаются уровни\n  прав на данную страницу для разных\n  групп пользователей.\n</p>\n<p>\n  <img alt="iz-5" src="uploads/public/control-panel/1207829811.jpg"   border="0" height="300" hspace="0" width="400" />\n</p>\n<p>\n  По умолчанию выставлены\n  стандартные значения. Без\n  понимания системы прав их лучше не\n  менять.\n</p>\n<p>\n  Также страницу можно создать из\n  редактора разделов. Для этого нужно\n  открыть редактор разделов сайта,\n  нажав в панели управления кнопку\n  "Структура сайта".\n</p>\n<p>\n  <img alt="iz-6" src="uploads/public/control-panel/1207829829.jpg"   border="0" height="310" hspace="0" width="400" />\n</p>\n<p>\n  После нажатия кнопки "Сохранить" вы\n  будете перенаправлены на\n  новосозданную страницу (в <a href=   "text-editing/">режиме редактирования\n  текста</a>), таким образом вы сможете\n  сразу внести текст страницы.\n</p>'),
(113, 1, '<p>\n  В режиме редактирования текста\n  пользователь получает возможность\n  редактировать содержимое страницы.\n</p>\n<p>\n  Для перехода в режим\n  редактирования необходимо нажать\n  кнопку "Режим редактирования" на\n  панели управления. После\n  перезагрузки страницы в верхней\n  части появится панель\n  инструментов.\n</p>\n<p>\n  <img src="uploads/public/control-panel/1207832068.jpg" alt=   "режим редактирования" align="" border="0"   height="20" hspace="0" vspace="0" width="500" />\n</p>\n<p>\n  Все текстовые блоки страницы,\n  которые можно редактировать,\n  выделяются красными пунктирными\n  линиями - это означает, что данный\n  блок сейчас в неактивном состоянии.\n  Кликните на любой текстовый блок.\n  Цвет рамки станет зеленым - это\n  означает, что блок переведен в\n  активное состояние.\n</p>\n<p style="color: red;">\n  Для пользователей FireFox. При работе в\n  режиме редактирования текста в FireFox\n  при активизации текстового блока\n  происходит открытие окна\n  редактирования исходного кода\n  блока, где текст необходимо вводить\n  в HTML разметке. WYSIWYG возможности\n  редактирования текста в этом\n  браузере - недоступны.\n</p>\n<p>\n  Кнопки размещенные на панели\n  инструментов знакомы любому\n  пользователю текстового редактора\n  и выполняют те же действия.<br />\n  Специфичные кнопки:\n</p>\n<ul>\n  <li>\n    <div>\n      <img src="uploads/public/control-panel/1207832118.jpg" alt=       "вставка изображения" align="" border="0"       height="19" hspace="0" vspace="0" width="19" /> -\n      вставка изображения. По нажатию\n      этой кнопки открывается окно\n      вставки изображения на страницу.\n    </div>\n  </li>\n  <li>\n    <div>\n      <img src="uploads/public/control-panel/1207832136.jpg" alt=       "вставка ссылки на файл" align="" border=       "0" height="18" hspace="0" vspace="0" width="18" /> -\n      вставка ссылки на файл. Заливает\n      файл на сервер и вставляет на\n      него ссылку. Открывается окно\n      библиотеки файлов.\n    </div>\n  </li>\n  <li>\n    <img src="uploads/public/control-panel/1207832151.jpg" alt=     "HTML-кнопка" align="" border="0" height="15" hspace="0"     vspace="0" width="14" /> - просмотр и\n    редактирование HTML кода текстового\n    блока. Открывается окно\n    редактирования исходного кода.\n  </li>\n</ul>\n<p>\n  После окончания редактирования\n  текста нажмите кнопку <img src=   "uploads/text-editing/1174488363.gif" alt=   "Кнопка сохранения" align="top" border="0"   height="14" hspace="0" vspace="0" width="14" /> для\n  сохранения сделанных вами\n  изменений. Для возвращения в\n  нормальный режим нажмите кнопку\n  "Режим просмотра".\n</p>\n<p>\n  Вы можете копировать текст из\n  текстовых редакторов, других HTML\n  страниц и вставлять его в текстовый\n  блок.\n</p>'),
(114, 1, '<p>\n  Все файлы (в том числе и файлы,\n  содержащие изображения)\n  управляются через\n  специализированное хранилище -\n  файловый репозиторий.\n</p>\n<p>\n  Чтобы открыть диалоговое окно\n  файлового репозитория, нажмите\n  кнопку "Репозиторий файлов" на\n  панели инструментов.\n</p>\n<p>\n  <img src="uploads/public/control-panel/1207834092.jpg" alt=   "менеджер файлов" align="" border="0" height="298"   hspace="0" vspace="0" width="400" />\n</p>\n<p>\n  В репозитории файлов вы можете\n  создавать папки и группировать\n  файлы по папкам. Для создания папки\n  нажмите кнопку "Создать папку".\n  Откроется такое окно\n</p>\n<p>\n  <img src="uploads/public/control-panel/1207834122.jpg" alt=   "форма создания папки" align="" border="0"   height="298" hspace="0" vspace="0" width="400" />\n</p>\n<p>\n  Внутреннее имя - это имя папки на\n  диске, название - название папке в\n  репозитории файлов. Для сохранения\n  нажмите кнопку "Сохранить".\n  Новосозданная папка появиться в\n  окне репозитория файлов. Для того,\n  чтобы зайти в папку, кликните на ней\n  и нажмите кнопку "Открыть", или\n  дважды кликните на папку. Для того\n  чтобы залить файл - нажмите кнопку\n  "Добавить".\n</p>\n<p>\n  <img src="uploads/public/control-panel/1207834137.jpg" alt=   "загрузка файла" align="" border="0" height="306"   hspace="0" vspace="0" width="400" />\n</p>\n<p>\n  Кроме того, вы можете\n  переименовывать и удалять файлы.\n</p>'),
(115, 1, '<p>\n  В CMS Energine существует система прав на\n  страницы, позволяющая гибко\n  управлять отображением страниц для\n  различных пользователей.\n</p>\n<p>\n  Предусмотрены следующие уровни\n  доступа к странице:\n</p>\n<ul>\n  <li>\n    <strong>Отсутствие доступа</strong>.\n    Страница не отображается.\n  </li>\n  <li>\n    <strong>Только чтение</strong>. Страница\n    отображается на чтение.\n  </li>\n  <li>\n    <strong>Редактирование</strong>.\n    Пользователь может редактировать\n    содержимое страницы.\n  </li>\n  <li>\n    <strong>Полный доступ</strong>.\n    Зарезервировано для расширения в\n    последующих версиях.\n  </li>\n</ul>'),
(116, 1, '<p>\n  В этом разделе даны краткие\n  инструкции по управлению системой.\n  Описываемая функциональность\n  доступна только в административном\n  режиме.\n</p>');

-- --------------------------------------------------------

--
-- Структура таблицы `share_uploads`
--

CREATE TABLE IF NOT EXISTS `share_uploads` (
  `upl_id` int(10) unsigned NOT NULL auto_increment,
  `upl_path` varchar(200) NOT NULL default '',
  `upl_name` varchar(250) NOT NULL default '',
  `upl_data` text,
  PRIMARY KEY  (`upl_id`),
  UNIQUE KEY `upl_path` (`upl_path`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=225 ;

--
-- Дамп данных таблицы `share_uploads`
--

INSERT INTO `share_uploads` (`upl_id`, `upl_path`, `upl_name`, `upl_data`) VALUES
(8, 'uploads/tmp/1/1196077246.txt', '4344334', NULL),
(9, 'uploads/tmp/323232', '323232', NULL),
(10, 'uploads/1196077634.jpg', 'Mobile', NULL),
(11, 'uploads/product_thumb_img_1196077634.jpg', 'product_thumb_img: 1196077634.jpg', NULL),
(12, 'uploads/1196079222.jpg', 'BBK-67888', NULL),
(13, 'uploads/product_thumb_img_1196079222.jpg', 'product_thumb_img: 1196079222.jpg', NULL),
(14, 'uploads/photo', 'Фото', NULL),
(15, 'uploads/photo/1196161411.jpg', 'Nikon D40 KIT 18-55 Black', NULL),
(16, 'uploads/photo/product_thumb_img_1196161411.jpg', 'product_thumb_img: 1196161411.jpg', NULL),
(17, 'uploads/photo/1196167450.jpg', 'Canon Digital EOS 400D KIT 18-55 Black', NULL),
(18, 'uploads/photo/product_thumb_img_1196167450.jpg', 'product_thumb_img: 1196167450.jpg', NULL),
(19, 'uploads/photo/1196169112.jpg', 'Canon PowerShot S5 IS', NULL),
(20, 'uploads/photo/product_thumb_img_1196169112.jpg', 'product_thumb_img: 1196169112.jpg', NULL),
(21, 'uploads/photo/1196170764.jpg', 'Olympus SP-560 UZ', NULL),
(22, 'uploads/photo/product_thumb_img_1196170764.jpg', 'product_thumb_img: 1196170764.jpg', NULL),
(23, 'uploads/photo/1196173789.jpg', 'Чехол Hama Case Samsonite Trekking Pro DFV42', NULL),
(24, 'uploads/photo/product_thumb_img_1196173789.jpg', 'product_thumb_img: 1196173789.jpg', NULL),
(25, 'uploads/photo/1196174398.jpg', 'Hama Case Samsonite DV35', NULL),
(26, 'uploads/photo/product_thumb_img_1196174398.jpg', 'product_thumb_img: 1196174398.jpg', NULL),
(27, 'uploads/photo/1196174968.jpg', 'PORTcase Exline 18', NULL),
(28, 'uploads/photo/product_thumb_img_1196174968.jpg', 'product_thumb_img: 1196174968.jpg', NULL),
(29, 'uploads/audio-video', 'Аудио, видео техника', NULL),
(30, 'uploads/audio-video/1196175742.jpg', 'Samsung LE40S81BX/NWT', NULL),
(31, 'uploads/audio-video/product_thumb_img_1196175742.jpg', 'product_thumb_img: 1196175742.jpg', NULL),
(32, 'uploads/audio-video/1196176361.jpg', 'Samsung LE26R81BX/NWT', NULL),
(33, 'uploads/audio-video/product_thumb_img_1196176361.jpg', 'product_thumb_img: 1196176361.jpg', NULL),
(34, 'uploads/audio-video/1196176942.jpg', 'Samsung LE15S51B', NULL),
(35, 'uploads/audio-video/product_thumb_img_1196176942.jpg', 'product_thumb_img: 1196176942.jpg', NULL),
(36, 'uploads/audio-video/1196177788.jpg', 'LG 29 FS 4 RLX', NULL),
(37, 'uploads/audio-video/product_thumb_img_1196177788.jpg', 'product_thumb_img: 1196177788.jpg', NULL),
(38, 'uploads/audio-video/1196178603.jpg', 'LG DP172BP', NULL),
(39, 'uploads/audio-video/product_thumb_img_1196178603.jpg', 'product_thumb_img: 1196178603.jpg', NULL),
(40, 'uploads/audio-video/1196179689.jpg', 'Odeon SDP-2002', NULL),
(41, 'uploads/audio-video/product_thumb_img_1196179689.jpg', 'product_thumb_img: 1196179689.jpg', NULL),
(42, 'uploads/pc', 'ПК', NULL),
(43, 'uploads/pc/1196181519.jpg', 'Samsung TFT 931C 19"', NULL),
(44, 'uploads/pc/product_thumb_img_1196181519.jpg', 'product_thumb_img: 1196181519.jpg', NULL),
(45, 'uploads/pc/1196242507.jpg', 'LG TFT Flatron L1953TR-BF, 19"', NULL),
(46, 'uploads/pc/product_thumb_img_1196242507.jpg', 'product_thumb_img: 1196242507.jpg', NULL),
(47, 'uploads/pc/1196244349.jpg', 'Philips TFT 170C7FS 17"', NULL),
(48, 'uploads/pc/product_thumb_img_1196244349.jpg', 'product_thumb_img: 1196244349.jpg', NULL),
(49, 'uploads/pc/1196245393.jpg', 'Logitech V450 Cordless Notebook Dark', NULL),
(50, 'uploads/pc/product_thumb_img_1196245393.jpg', 'product_thumb_img: 1196245393.jpg', NULL),
(51, 'uploads/pc/1196245757.jpg', 'Клавиатура+Мышь Logitech Deluxe 650 Black Cordless', NULL),
(52, 'uploads/pc/product_thumb_img_1196245757.jpg', 'product_thumb_img: 1196245757.jpg', NULL),
(53, 'uploads/pc/1196246268.jpg', 'Genius Wireless Navigator 900 Pro Bluetooth', NULL),
(54, 'uploads/pc/product_thumb_img_1196246268.jpg', 'product_thumb_img: 1196246268.jpg', NULL),
(55, 'uploads/pc/1196247168.jpg', 'PC 820D', NULL),
(56, 'uploads/pc/product_thumb_img_1196247168.jpg', 'product_thumb_img: 1196247168.jpg', NULL),
(57, 'uploads/pc/1196247870.jpg', 'PC Optimal A64', NULL),
(58, 'uploads/pc/product_thumb_img_1196247870.jpg', 'product_thumb_img: 1196247870.jpg', NULL),
(59, 'uploads/1196337725.gif', 'energine', NULL),
(60, 'uploads/control-panel/1196435311.jpg', 'панель управления', NULL),
(61, 'uploads/how-to-create-page/1196436116.jpg', 'как создать страницу?', NULL),
(62, 'uploads/text-editing/1196436806.jpg', 'режим редактирования', NULL),
(63, 'uploads/control-panel', 'control-panel', NULL),
(64, 'uploads/public/images-in-content', 'Изображения в тексте', NULL),
(65, 'uploads/public/images-in-content/1202745731.jpg', 'Экскаваторы', NULL),
(66, 'uploads/public/images-in-content/1202745751.jpg', 'Ковш', NULL),
(67, 'uploads/public/frontlifts', 'Фронтальные погрузчики', NULL),
(72, 'uploads/public/excavators', 'Экскаваторы', NULL),
(79, 'uploads/public/equipment', 'Оборудование и комплектующие', NULL),
(80, 'uploads/public/equipment/1202825456.jpg', 'Защитные цепи для колес', NULL),
(81, 'uploads/public/equipment/product_thumb_img_1202825456.jpg', 'product_thumb_img: 1202825456.jpg', NULL),
(82, 'uploads/public/frontlifts/1202991303.jpg', 'JGM755', NULL),
(83, 'uploads/public/frontlifts/product_thumb_img_1202991303.jpg', 'product_thumb_img: 1202991303.jpg', NULL),
(84, 'uploads/public/excavators/1202991773.jpg', 'JGM923-LC', NULL),
(85, 'uploads/public/excavators/product_thumb_img_1202991773.jpg', 'product_thumb_img: 1202991773.jpg', NULL),
(86, 'uploads/public/excavators/1202991969.jpg', 'JGM906', NULL),
(87, 'uploads/public/excavators/product_thumb_img_1202991969.jpg', 'product_thumb_img: 1202991969.jpg', NULL),
(88, 'uploads/public/frontlifts/1202992055.jpg', 'JGM757', NULL),
(89, 'uploads/public/frontlifts/product_thumb_img_1202992055.jpg', 'product_thumb_img: 1202992055.jpg', NULL),
(90, 'uploads/public/frontlifts/1202994793.jpg', 'JGM737', NULL),
(91, 'uploads/public/frontlifts/product_thumb_img_1202994793.jpg', 'product_thumb_img: 1202994793.jpg', NULL),
(92, 'uploads/public/frontlifts/1202995387.jpg', 'JGM737 схема', NULL),
(93, 'uploads/public/partners-logo', 'Логотипы партнеров', NULL),
(96, 'uploads/public/partners-logo/1203934701.gif', 'JGM', NULL),
(97, 'uploads/public/partners-logo/1203935198.gif', 'Альфа Техно Импорт ', NULL),
(98, 'uploads/public/equipment/1204104136.gif', 'Структура цепи', NULL),
(99, 'uploads/public/stone-crusher-complex', 'дробильный комплекс для производства щебня', NULL),
(100, 'uploads/public/frontlifts/1204194142.jpg', 'JGM737W', NULL),
(101, 'uploads/public/frontlifts/product_thumb_img_1204194142.jpg', 'product_thumb_img: 1204194142.jpg', NULL),
(102, 'uploads/public/frontlifts/1204195109.jpg', 'JGM737', NULL),
(103, 'uploads/public/frontlifts/1204195224.jpg', 'JGM716', NULL),
(104, 'uploads/public/frontlifts/1204195253.jpg', 'JGM755', NULL),
(105, 'uploads/public/frontlifts/1204195450.jpg', 'JGM757', NULL),
(106, 'uploads/public/frontlifts/1204195482.jpg', 'ZL50C', NULL),
(108, 'uploads/public/frontlifts/1204195550.jpg', 'ZL50D', NULL),
(109, 'uploads/public/frontlifts/1204195590.jpg', 'ZLC50C', NULL),
(110, 'uploads/public/frontlifts/1204195719.jpg', 'ZLJ50C', NULL),
(111, 'uploads/public/frontlifts/1204195749.jpg', 'ZLM50D', NULL),
(112, 'uploads/public/frontlifts/1204195794.jpg', 'ZLMG50C', NULL),
(114, 'uploads/public/frontlifts/1204195869.jpg', 'ZLY50D', NULL),
(115, 'uploads/public/frontlifts/product_thumb_img_1204195109.jpg', 'product_thumb_img: 1204195109.jpg', NULL),
(116, 'uploads/public/frontlifts/product_thumb_img_1204195253.jpg', 'product_thumb_img: 1204195253.jpg', NULL),
(117, 'uploads/public/frontlifts/product_thumb_img_1204195450.jpg', 'product_thumb_img: 1204195450.jpg', NULL),
(118, 'uploads/public/frontlifts/product_thumb_img_1204195719.jpg', 'product_thumb_img: 1204195719.jpg', NULL),
(119, 'uploads/public/frontlifts/product_thumb_img_1204195224.jpg', 'product_thumb_img: 1204195224.jpg', NULL),
(120, 'uploads/public/frontlifts/product_thumb_img_1204195749.jpg', 'product_thumb_img: 1204195749.jpg', NULL),
(121, 'uploads/public/frontlifts/product_thumb_img_1204195482.jpg', 'product_thumb_img: 1204195482.jpg', NULL),
(122, 'uploads/public/frontlifts/product_thumb_img_1204195794.jpg', 'product_thumb_img: 1204195794.jpg', NULL),
(123, 'uploads/public/frontlifts/product_thumb_img_1204195590.jpg', 'product_thumb_img: 1204195590.jpg', NULL),
(125, 'uploads/public/frontlifts/product_thumb_img_1204195869.jpg', 'product_thumb_img: 1204195869.jpg', NULL),
(126, 'uploads/public/frontlifts/1204201349.jpg', 'ZLY40D', NULL),
(127, 'uploads/public/frontlifts/product_thumb_img_1204201349.jpg', 'product_thumb_img: 1204201349.jpg', NULL),
(128, 'uploads/public/frontlifts/product_thumb_img_1204195550.jpg', 'product_thumb_img: 1204195550.jpg', NULL),
(129, 'uploads/public/frontlifts/1204205029.jpg', 'ZL50C-SB45', NULL),
(130, 'uploads/public/frontlifts/product_thumb_img_1204205029.jpg', 'product_thumb_img: 1204205029.jpg', NULL),
(133, 'uploads/public/stone-crusher-complex/1204211575.jpg', 'fff', NULL),
(135, 'uploads/public/stone-crusher-complex/1204217156.jpg', 'grohot', NULL),
(138, 'uploads/public/stone-crusher-complex/1204217317.jpg', 'konveer', NULL),
(139, 'uploads/public/stone-crusher-complex/1204217714.jpg', 'vibropitatel', NULL),
(140, 'uploads/public/stone-crusher-complex/1204274500.jpg', 'k_drobilka', NULL),
(141, 'uploads/public/stone-crusher-complex/1204285383.swf', 'Видео-схема камнедробилки ', NULL),
(142, 'uploads/public/stone-crusher-complex/1204288593.jpg', 'shema', NULL),
(143, 'uploads/public/excavators/1204297527.jpg', 'jgm923_LC', NULL),
(144, 'uploads/public/excavators/product_thumb_img_1204297527.jpg', 'product_thumb_img: 1204297527.jpg', NULL),
(145, 'uploads/public/equipment/1204541162.jpg', 'shema_cheins', NULL),
(146, 'uploads/public/images-in-content/1204543388.jpg', 'iz', NULL),
(147, 'uploads/public/images-in-content/1204544121.jpg', 'partners', NULL),
(148, 'uploads/public/images-in-content/1204544294.jpg', 'o_nas', NULL),
(149, 'uploads/public/images-in-content/1204544952.jpg', 'uslugi', NULL),
(150, 'uploads/public/equipment/1204546456.jpg', 'cepi', NULL),
(151, 'uploads/public/equipment/1204546478.jpg', 'detali', NULL),
(152, 'uploads/public/equipment/product_thumb_img_1204546456.jpg', 'product_thumb_img: 1204546456.jpg', NULL),
(153, 'uploads/public/new', 'Название папки', NULL),
(158, 'uploads/public/gallery-2', 'Фотогалерея 2', NULL),
(159, 'uploads/public/gallery-2/1207230392.jpg', 'фото 1', NULL),
(160, 'uploads/public/gallery-2/1207230416.jpg', 'фото 1 (превью)', NULL),
(161, 'uploads/public/gallery-2/1207230560.jpg', 'фото 2', NULL),
(162, 'uploads/public/gallery-2/1207230584.jpg', 'фото 2 (превью)', NULL),
(163, 'uploads/public/gallery-2/1207230602.jpg', 'фото 3', NULL),
(164, 'uploads/public/gallery-2/1207230622.jpg', 'фото 3 (превью)', NULL),
(165, 'uploads/public/gallery-2/1207230637.jpg', 'фото 4', NULL),
(166, 'uploads/public/gallery-2/1207230656.jpg', 'фото 4 (превью)', NULL),
(167, 'uploads/public/gallery-1', 'Фотогалерея 1', NULL),
(168, 'uploads/public/gallery-1/1207235019.jpg', 'фото 1', NULL),
(169, 'uploads/public/gallery-1/1207235036.jpg', 'фото 1 (превью)', NULL),
(170, 'uploads/public/gallery-1/1207235056.jpg', 'фото 2', NULL),
(171, 'uploads/public/gallery-1/1207235073.jpg', 'фото 2 (превью)', NULL),
(172, 'uploads/public/gallery-1/1207235092.jpg', 'фото 3', NULL),
(173, 'uploads/public/gallery-1/1207235111.jpg', 'фото 3 (превью)', NULL),
(174, 'uploads/public/cold', 'холодильник', NULL),
(175, 'uploads/public/cold/1207664556.jpg', 'kerher_kr', NULL),
(176, 'uploads/public/cold/1207664663.jpg', 'ariston', NULL),
(177, 'uploads/public/tv_set', 'телевизоры', NULL),
(178, 'uploads/public/iron', 'утюги', NULL),
(179, 'uploads/public/furnace', 'микроволновки', NULL),
(180, 'uploads/public/pilesos', 'пылесосы', NULL),
(181, 'uploads/public/furnace/1207665011.jpg', 'bosh', NULL),
(182, 'uploads/public/furnace/1207665061.jpg', 'lg', NULL),
(183, 'uploads/public/pilesos/1207665147.jpg', 'electrolux', NULL),
(184, 'uploads/public/pilesos/1207665184.jpg', 'tomas', NULL),
(185, 'uploads/public/pilesos/1207665229.jpg', 'pilesos', NULL),
(186, 'uploads/public/tv_set/1207665273.jpg', 'daewoo_c', NULL),
(187, 'uploads/public/tv_set/1207665311.jpg', 'daewoo_w', NULL),
(188, 'uploads/public/tv_set/1207665350.jpg', 'lg', NULL),
(189, 'uploads/public/tv_set/1207665376.jpg', 'samsung', NULL),
(190, 'uploads/public/iron/1207665417.jpg', 'philips', NULL),
(191, 'uploads/public/iron/1207665449.jpg', 'pilips', NULL),
(192, 'uploads/public/cofe', 'кофемолки', NULL),
(193, 'uploads/public/cofe/1207665614.jpg', 'ссс', NULL),
(194, 'uploads/public/cofe/1207665637.jpg', 'gaggia', NULL),
(195, 'uploads/public/furnace/product_thumb_img_1207665011.jpg', 'product_thumb_img: 1207665011.jpg', NULL),
(196, 'uploads/public/tv_set/product_thumb_img_1207665376.jpg', 'product_thumb_img: 1207665376.jpg', NULL),
(197, 'uploads/public/furnace/product_thumb_img_1207665061.jpg', 'product_thumb_img: 1207665061.jpg', NULL),
(198, 'uploads/public/pilesos/product_thumb_img_1207665184.jpg', 'product_thumb_img: 1207665184.jpg', NULL),
(199, 'uploads/public/pilesos/product_thumb_img_1207665229.jpg', 'product_thumb_img: 1207665229.jpg', NULL),
(200, 'uploads/public/pilesos/product_thumb_img_1207665147.jpg', 'product_thumb_img: 1207665147.jpg', NULL),
(201, 'uploads/public/tv_set/product_thumb_img_1207665350.jpg', 'product_thumb_img: 1207665350.jpg', NULL),
(202, 'uploads/public/tv_set/product_thumb_img_1207665273.jpg', 'product_thumb_img: 1207665273.jpg', NULL),
(203, 'uploads/public/tv_set/product_thumb_img_1207665311.jpg', 'product_thumb_img: 1207665311.jpg', NULL),
(204, 'uploads/public/cofe/product_thumb_img_1207665637.jpg', 'product_thumb_img: 1207665637.jpg', NULL),
(205, 'uploads/public/cofe/product_thumb_img_1207665614.jpg', 'product_thumb_img: 1207665614.jpg', NULL),
(206, 'uploads/public/cold/product_thumb_img_1207664556.jpg', 'product_thumb_img: 1207664556.jpg', NULL),
(207, 'uploads/public/cold/product_thumb_img_1207664663.jpg', 'product_thumb_img: 1207664663.jpg', NULL),
(208, 'uploads/public/control-panel', 'control-panel', NULL),
(211, 'uploads/public/control-panel/1207827603.jpg', 'control', NULL),
(212, 'uploads/public/control-panel/1207829716.jpg', 'iz-1', NULL),
(213, 'uploads/public/control-panel/1207829748.jpg', 'iz-2', NULL),
(214, 'uploads/public/control-panel/1207829770.jpg', 'iz-3', NULL),
(215, 'uploads/public/control-panel/1207829792.jpg', 'iz-4', NULL),
(216, 'uploads/public/control-panel/1207829811.jpg', 'iz-5', NULL),
(217, 'uploads/public/control-panel/1207829829.jpg', 'iz-6', NULL),
(218, 'uploads/public/control-panel/1207832068.jpg', 'rt-1', NULL),
(219, 'uploads/public/control-panel/1207832118.jpg', 'rt-2', NULL),
(220, 'uploads/public/control-panel/1207832136.jpg', 'rt-3', NULL),
(221, 'uploads/public/control-panel/1207832151.jpg', 're-4', NULL),
(222, 'uploads/public/control-panel/1207834092.jpg', 'fi-1', NULL),
(223, 'uploads/public/control-panel/1207834122.jpg', 'fi-2', NULL),
(224, 'uploads/public/control-panel/1207834137.jpg', 'fi-3', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `shop_basket`
--

CREATE TABLE IF NOT EXISTS `shop_basket` (
  `basket_id` int(10) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL default '0',
  `session_id` int(10) unsigned NOT NULL default '0',
  `basket_count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`basket_id`),
  UNIQUE KEY `product_id` (`product_id`,`session_id`),
  KEY `basket_ibfk_2` (`session_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Дамп данных таблицы `shop_basket`
--


-- --------------------------------------------------------

--
-- Структура таблицы `shop_currency`
--

CREATE TABLE IF NOT EXISTS `shop_currency` (
  `curr_id` int(10) unsigned NOT NULL auto_increment,
  `curr_abbr` char(3) NOT NULL default '',
  `curr_rate` float unsigned NOT NULL default '0',
  PRIMARY KEY  (`curr_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `shop_currency`
--

INSERT INTO `shop_currency` (`curr_id`, `curr_abbr`, `curr_rate`) VALUES
(2, 'UAH', 1),
(3, 'RUR', 20),
(4, 'USD', 0.2);

-- --------------------------------------------------------

--
-- Структура таблицы `shop_currency_translation`
--

CREATE TABLE IF NOT EXISTS `shop_currency_translation` (
  `curr_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `curr_name` char(50) NOT NULL default '',
  `curr_format` char(50) NOT NULL default '',
  PRIMARY KEY  (`curr_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `shop_currency_translation`
--

INSERT INTO `shop_currency_translation` (`curr_id`, `lang_id`, `curr_name`, `curr_format`) VALUES
(2, 1, 'Гривны наличные', '%s грн.'),
(2, 2, 'Гривні (готівка)', '%s грн.'),
(2, 3, 'Hryvnia', '%s hrn.'),
(2, 4, 'Гривна готівка', '%s грн.'),
(3, 1, 'Русский рубль', '%s руб.'),
(3, 2, 'Російський рубль', '%s руб.'),
(3, 3, 'Russian rybl', '%s rub.'),
(4, 1, 'Доллары', '$%s'),
(4, 2, 'Доляри', '$%s'),
(4, 3, 'Dollars', '$%s');

-- --------------------------------------------------------

--
-- Структура таблицы `shop_discounts`
--

CREATE TABLE IF NOT EXISTS `shop_discounts` (
  `dscnt_id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL default '0',
  `dscnt_name` char(100) NOT NULL default '',
  `dscnt_percent` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`dscnt_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `shop_discounts`
--


-- --------------------------------------------------------

--
-- Структура таблицы `shop_orders`
--

CREATE TABLE IF NOT EXISTS `shop_orders` (
  `order_id` int(10) unsigned NOT NULL auto_increment,
  `u_id` int(10) unsigned default NULL,
  `os_id` int(10) unsigned NOT NULL default '0',
  `order_comment` text,
  `order_delivery_comment` text,
  `order_detail` text,
  `user_detail` text NOT NULL,
  `order_created` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`order_id`),
  KEY `u_id` (`u_id`),
  KEY `os_id` (`os_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `shop_orders`
--

INSERT INTO `shop_orders` (`order_id`, `u_id`, `os_id`, `order_comment`, `order_delivery_comment`, `order_detail`, `user_detail`, `order_created`) VALUES
(1, 113, 10, NULL, NULL, 'a:1:{i:0;a:10:{s:9:"basket_id";s:1:"5";s:10:"product_id";s:1:"1";s:10:"session_id";s:4:"2949";s:12:"basket_count";s:1:"2";s:12:"product_name";s:3:"ZZZ";s:13:"product_price";s:11:"111 грн.";s:12:"product_summ";s:11:"222 грн.";s:26:"product_summ_with_discount";N;s:7:"curr_id";s:1:"2";s:15:"product_segment";s:3:"zzz";}}', 'a:6:{s:4:"u_id";s:3:"113";s:6:"u_name";s:17:"demo@energine.org";s:9:"u_address";s:28:"Адрес мой новый";s:16:"u_contact_person";s:4:"demo";s:7:"u_phone";s:9:"555-55-55";s:22:"order_delivery_comment";s:0:"";}', '2008-04-07 17:30:17'),
(2, 113, 11, 'zzz zzz', NULL, 'a:1:{i:0;a:10:{s:9:"basket_id";s:1:"6";s:10:"product_id";s:1:"1";s:10:"session_id";s:4:"2949";s:12:"basket_count";s:1:"1";s:12:"product_name";s:3:"ZZZ";s:13:"product_price";s:11:"111 грн.";s:12:"product_summ";s:11:"111 грн.";s:26:"product_summ_with_discount";N;s:7:"curr_id";s:1:"2";s:15:"product_segment";s:3:"zzz";}}', 'a:6:{s:4:"u_id";s:3:"113";s:6:"u_name";s:17:"demo@energine.org";s:9:"u_address";s:28:"Адрес мой новый";s:16:"u_contact_person";s:4:"demo";s:7:"u_phone";s:9:"555-55-55";s:22:"order_delivery_comment";s:0:"";}', '2008-04-07 17:40:03');

-- --------------------------------------------------------

--
-- Структура таблицы `shop_order_statuses`
--

CREATE TABLE IF NOT EXISTS `shop_order_statuses` (
  `os_id` int(10) unsigned NOT NULL auto_increment,
  `os_priority` smallint(5) unsigned NOT NULL default '1',
  PRIMARY KEY  (`os_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Дамп данных таблицы `shop_order_statuses`
--

INSERT INTO `shop_order_statuses` (`os_id`, `os_priority`) VALUES
(10, 1),
(11, 2),
(12, 3);

-- --------------------------------------------------------

--
-- Структура таблицы `shop_order_statuses_translation`
--

CREATE TABLE IF NOT EXISTS `shop_order_statuses_translation` (
  `os_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `os_name` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`os_id`,`lang_id`),
  KEY `order_status_translation_ibfk_2` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `shop_order_statuses_translation`
--

INSERT INTO `shop_order_statuses_translation` (`os_id`, `lang_id`, `os_name`) VALUES
(10, 1, 'Заказан'),
(10, 2, 'Замовлено'),
(10, 3, 'Ordered'),
(11, 1, 'Принят'),
(11, 2, 'Прийнято'),
(11, 3, 'Accepted'),
(12, 1, 'Исполнен'),
(12, 2, 'Виконано'),
(12, 3, 'Executed');

-- --------------------------------------------------------

--
-- Структура таблицы `shop_producers`
--

CREATE TABLE IF NOT EXISTS `shop_producers` (
  `producer_id` int(10) unsigned NOT NULL auto_increment,
  `producer_name` char(150) NOT NULL default '',
  `producer_segment` char(50) NOT NULL default '',
  PRIMARY KEY  (`producer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Дамп данных таблицы `shop_producers`
--

INSERT INTO `shop_producers` (`producer_id`, `producer_name`, `producer_segment`) VALUES
(1, 'Производитель 1', 'manufacturer-1'),
(2, 'Bosh', 'bosh'),
(3, 'Samsung', 'samsung'),
(4, 'Kerher', 'kerher'),
(5, 'LG', 'lg');

-- --------------------------------------------------------

--
-- Структура таблицы `shop_products`
--

CREATE TABLE IF NOT EXISTS `shop_products` (
  `product_id` int(10) unsigned NOT NULL auto_increment,
  `smap_id` int(10) unsigned NOT NULL default '0',
  `pt_id` int(10) unsigned NOT NULL,
  `producer_id` int(10) unsigned NOT NULL,
  `product_code` char(100) NOT NULL default '',
  `product_segment` char(50) NOT NULL default '',
  `product_photo_img` char(200) default NULL,
  `product_thumb_img` char(200) default NULL,
  `ps_id` int(10) unsigned NOT NULL default '1',
  PRIMARY KEY  (`product_id`),
  UNIQUE KEY `product_code` (`product_code`),
  KEY `smap_id` (`smap_id`),
  KEY `pt_id` (`pt_id`),
  KEY `producer_id` (`producer_id`),
  KEY `product_photo_img` (`product_photo_img`),
  KEY `product_thumb_img` (`product_thumb_img`),
  KEY `ps_id` (`ps_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Дамп данных таблицы `shop_products`
--

INSERT INTO `shop_products` (`product_id`, `smap_id`, `pt_id`, `producer_id`, `product_code`, `product_segment`, `product_photo_img`, `product_thumb_img`, `ps_id`) VALUES
(4, 354, 3, 3, '165461', 'boshm', 'uploads/public/furnace/1207665011.jpg', 'uploads/public/furnace/product_thumb_img_1207665011.jpg', 3),
(5, 350, 2, 3, '123414535', 'tvset', 'uploads/public/tv_set/1207665376.jpg', 'uploads/public/tv_set/product_thumb_img_1207665376.jpg', 1),
(6, 354, 3, 5, '1316', 'lg', 'uploads/public/furnace/1207665061.jpg', 'uploads/public/furnace/product_thumb_img_1207665061.jpg', 1),
(7, 353, 6, 2, '45464', 'tomaspil', 'uploads/public/pilesos/1207665184.jpg', 'uploads/public/pilesos/product_thumb_img_1207665184.jpg', 4),
(8, 353, 6, 4, '46146', 'kerherpl', 'uploads/public/pilesos/1207665229.jpg', 'uploads/public/pilesos/product_thumb_img_1207665229.jpg', 2),
(9, 353, 6, 5, '16156', 'lgp', 'uploads/public/pilesos/1207665147.jpg', 'uploads/public/pilesos/product_thumb_img_1207665147.jpg', 1),
(10, 350, 2, 5, '1061409604', 'lgset', 'uploads/public/tv_set/1207665350.jpg', 'uploads/public/tv_set/product_thumb_img_1207665350.jpg', 4),
(11, 350, 2, 3, '16464662', 'd_w', 'uploads/public/tv_set/1207665273.jpg', 'uploads/public/tv_set/product_thumb_img_1207665273.jpg', 1),
(12, 350, 2, 2, '465453', 'dw_s', 'uploads/public/tv_set/1207665311.jpg', 'uploads/public/tv_set/product_thumb_img_1207665311.jpg', 1),
(13, 352, 3, 4, '46141959', 'gia1', 'uploads/public/cofe/1207665637.jpg', 'uploads/public/cofe/product_thumb_img_1207665637.jpg', 1),
(14, 352, 7, 2, '1321', 'g_2', 'uploads/public/cofe/1207665614.jpg', 'uploads/public/cofe/product_thumb_img_1207665614.jpg', 1),
(15, 351, 4, 2, '165', 'h1', 'uploads/public/cold/1207664556.jpg', 'uploads/public/cold/product_thumb_img_1207664556.jpg', 1),
(16, 351, 4, 4, '136551', 'h2', 'uploads/public/cold/1207664663.jpg', 'uploads/public/cold/product_thumb_img_1207664663.jpg', 3);

-- --------------------------------------------------------

--
-- Структура таблицы `shop_products_translation`
--

CREATE TABLE IF NOT EXISTS `shop_products_translation` (
  `product_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `product_name` varchar(200) NOT NULL default '',
  `product_short_description_rtf` text NOT NULL,
  `product_description_rtf` text NOT NULL,
  PRIMARY KEY  (`product_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `shop_products_translation`
--

INSERT INTO `shop_products_translation` (`product_id`, `lang_id`, `product_name`, `product_short_description_rtf`, `product_description_rtf`) VALUES
(4, 1, 'Samsung GFH245', '<P>Страна производства Корея</P>\r\n<P>&nbsp;Гарантия (мес) 36 Наличие на складе в наличии</P>', '<P><STRONG>ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ</STRONG></P>\r\n<P>&nbsp;Фирма-производитель LG Модель MC 8087 ARRS Цена 233.00 y.e.</P>\r\n<P>&nbsp;Краткое описание Световая печь LG. Цвет серебристый. Объем камеры 30л. Мощность печи 900 Вт, конвекция 2050 Вт. Русский повар- 8 режима, авторазмораживание - 4режима, быстрое размораживание, замок от детей, быстрый старт. Управление тактовый+Dial. Открытие дверцы ручкойСрок поставки На следующий рабочий день после заказа Цвет Серебристый Внутренний объем, л 30 Покрытие рабочей камеры Нержавеющая сталь Мощность, Вт 900 Тип гриля/мощность, Вт Галогеновый/1250 Вт Комбинированый режим/мощность, Вт Есть/2550 Конвекция/мощность, Вт Есть/2050 Автоприготовление Есть/6 режимов Режим размораживания Есть/4 режима Вертел Нет Вращающееся блюдо Есть Панель управления Тактовый+Dial Дисплей Есть Программирование приготовления Есть Таймер Электронный Часы Есть Открытие дверцы Ручкой Принадлежности в комплекте Стеклянное блюдо Вес, кг 19 Габариты (ВxШxГ), мм 530х322х427 Особенности модели Тип управления тактовый+Dial. Функция замок от детей. Быстрый старт. Русский повар 8 режима. Автоприготовление 6 </P>'),
(4, 2, 'пічь', 'Біла піч', 'ssssssss'),
(4, 3, 'rec', 'dfnb fxjh', 'dj myijfm,ikfyuj'),
(5, 1, 'Samsung SDB26', 'Система вещания Поддержка стандартов PAL, SECAMЗвук Мощность колонок 2 x 5 Вт Вт Конфигурация звуковых выходов 2.0 Авторегулировка уровня звука нет Предустановки звука обычный, музыка, кино, спорт, индивидуальный Эквалайзер нет Функции Телетекст 10 страниц Картинка в картинке да Таймер включения по времени, автовыключения, выключения', '<STRONG>Экран</STRONG> \r\n<UL>\r\n<LI>Тип жидкокристаллический </LI>\r\n<LI>Диагональ 20 </LI>\r\n<LI>Формат экрана 4:3</LI>\r\n<LI>Яркость 450 кд/м ²</LI>\r\n<LI>Контрастность 350:1</LI>\r\n<LI>Максимальное разрешение 640 X 480 пикс </LI>\r\n<LI>Угол обзора горизонт. 178, вертикальн. 178 °</LI>\r\n<LI>Время отклика 8 мс </LI>\r\n<LI>Количество цветов 16.7 млн.</LI></UL>Двойной экран нет Система оптимизации изображения нет Предустановки изображения динамичный, стандартный, мягкий, индивидуальный Защита от детей да Система плавного отображения движения нет Встроенные устройства нет Порты, входы, выходы Входы/выходы SCART, S-Video, Audio-Out (mini-jack) Корпус Поддержка Kensington Lock нет Крепление VESA нет Питание Напряжение питания от 100 до 240 В Частота тока 50, 60 Гц Дополнительная информация Комплектация кабель питания, пульт ДУ, руководство пользователя Поддержка Kensington Lock нет Крепление VESA нет Гарантия 36 мес '),
(5, 2, 'Samsung SDB26', 'Samsung', 'Samsung'),
(5, 3, 'Samsung SDB26', 'Samsung', 'Samsung'),
(6, 1, 'LG MO-200 DGE ', 'Микроволновая печь с грилем GORENJE. Объем камеры 20л. Дисплей. СВЧ 1280 Вт. Кварцевый гриль 1000 Вт. Управление электроное. Размораживание. 5 - уровней мощности. Открытие дверцы клавишей. Внутреннее покрытие камеры - нержавеющая сталь', '<P><STRONG>GORENJE MO-200 DGE</STRONG> </P>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD></TD></TR></TBODY></TABLE><BR>\r\n<TABLE cellSpacing=2 cellPadding=0 border=0>\r\n<TBODY>\r\n<TR>\r\n<TD colSpan=3>\r\n<P><A></A><STRONG>ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ</STRONG> </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Фирма-производитель </P></TD>\r\n<TD width="50%">\r\n<P>GORENJE </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Модель </P></TD>\r\n<TD width="50%">\r\n<P>MO-200 DGE </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Цена </P></TD>\r\n<TD width="50%">\r\n<P>134.00 y.e. </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Краткое описание </P></TD>\r\n<TD width="50%">\r\n<P>Микроволновая печь с грилем GORENJE. Объем камеры 20л. Дисплей. СВЧ 1280 Вт. Кварцевый гриль 1000 Вт. Управление электроное. Размораживание. 5 - уровней мощности. Открытие дверцы клавишей. Внутреннее покрытие камеры - нержавеющая сталь. Быстрый старт… </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Страна производства </P></TD>\r\n<TD width="50%">\r\n<P>Словения </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Гарантия (мес) </P></TD>\r\n<TD width="50%">\r\n<P>12 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Наличие на складе </P></TD>\r\n<TD width="50%">\r\n<P>в наличии </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Срок поставки </P></TD>\r\n<TD width="50%">\r\n<P>На следующий рабочий день после заказа </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Цвет </P></TD>\r\n<TD width="50%">\r\n<P>Металлик </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Внутренний объем, л </P></TD>\r\n<TD width="50%">\r\n<P>20 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Покрытие рабочей камеры </P></TD>\r\n<TD width="50%">\r\n<P>Нержавеющая сталь </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Мощность, Вт </P></TD>\r\n<TD width="50%">\r\n<P>1280 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Тип гриля/мощность, Вт </P></TD>\r\n<TD width="50%">\r\n<P>Кварцевый/1000 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Комбинированый режим/мощность, Вт </P></TD>\r\n<TD width="50%">\r\n<P>Есть/2280 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Конвекция/мощность, Вт </P></TD>\r\n<TD width="50%">\r\n<P>Нет </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Автоприготовление </P></TD>\r\n<TD width="50%">\r\n<P>Нет </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Режим размораживания </P></TD>\r\n<TD width="50%">\r\n<P>Есть </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Вертел </P></TD>\r\n<TD width="50%">\r\n<P>Нет </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Вращающееся блюдо </P></TD>\r\n<TD width="50%">\r\n<P>Есть </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Панель управления </P></TD>\r\n<TD width="50%">\r\n<P>Электронная </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Дисплей </P></TD>\r\n<TD width="50%">\r\n<P>Есть </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Программирование приготовления </P></TD>\r\n<TD width="50%">\r\n<P>Нет </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Таймер </P></TD>\r\n<TD width="50%">\r\n<P>Электронный </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Часы </P></TD>\r\n<TD width="50%">\r\n<P>Нет данных </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Открытие дверцы </P></TD>\r\n<TD width="50%">\r\n<P>Клавиша </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Принадлежности в комплекте </P></TD>\r\n<TD width="50%">\r\n<P>Нет данных </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Вес, кг </P></TD>\r\n<TD width="50%">\r\n<P>15,5 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Габариты (ВxШxГ), мм </P></TD>\r\n<TD width="50%">\r\n<P>377х470х282 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Особенности модели </P></TD>\r\n<TD width="50%">\r\n<P>Стильный дизайн. Дисплей. Внутреннее покрытие и корпус из нержавеющей стали. Диаметр стеклянного блюда - 24,5см. 5 уровней мощности </P></TD></TR></TBODY></TABLE>'),
(6, 2, 'LG MO-200 DGE ', 'LG', 'LG'),
(6, 3, 'LG MO-200 DGE ', 'LG', 'LG'),
(7, 1, 'Tomas SDNF', 'Пылесос для сухой уборки BOSCH. Мощность 1900 Вт. Радиус действия 10м. Мешок для мусора - 3,3л. Система фильтрации Air Clean II. Индикатор заполнения пылесборника.', '<STRONG>ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ</STRONG><BR><BR><STRONG>Фирма-производитель</STRONG><BR><BR>BOSCH Модель BSG 61840 Цена 170.00 y.e. <STRONG>Краткое описание</STRONG><BR><BR>&nbsp;Электронная регулировка мощности. Турбощетка… Страна производства Словения <STRONG>Гарантия (мес)</STRONG><BR><BR><BR>24 Наличие на складе в наличии <STRONG>Срок поставки</STRONG><BR><BR><BR>На следующий рабочий день после заказа Тип уборки Сухая Мощность, Вт 1800 Мощность всасывания, Вт Нет данных Уровень шума, дБ Нет данных Система фильтрации Air Clean II Тип трубки всасывания Телескопическая Регулятор мощности Электронный Длина шнура, м 8 Тип пылесборника Сменный пылесборник MEGAfilt SuperTEX Насадки Роликовая щетка ''''ковер-пол'''', щелевая насадка, насадка для мягкой мебели, турбощетка Индикатор заполнения пылесборника Есть Вес, кг 5,1 Габариты (ДxШxВ), мм Нет данных Особенности модели Транспортный механизм из 3 колес, вращающихся на 360° в двух плоскост'),
(7, 2, 'Tomas SDNF', 'Tomas', 'Tomas'),
(7, 3, 'Tomas SDNF', 'Tomas', 'Tomas'),
(8, 1, 'Kerher HGFD254', '<P>Пылесос для сухой уборки ELECTROLUX. Мощность 1800Вт. Радиус действия 13 м. Система фильтрации HEPA-12. Телескопическая трубка. Щетка пол-ковер, щелевая, для мягкой мебели. Уровень шума 78 дБ. Мягкий пуск двигателя. Дискретный регулятор мощности... </P>', '<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD colSpan=3>\r\n<P><STRONG>ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ</STRONG> </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Фирма-производитель </P></TD>\r\n<TD width="50%">\r\n<P>ELECTROLUX </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Модель </P></TD>\r\n<TD width="50%">\r\n<P>XXL TT11 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Цена </P></TD>\r\n<TD width="50%">\r\n<P>190.00 y.e. </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Краткое описание </P></TD>\r\n<TD width="50%">\r\n<P>Пылесос для сухой уборки ELECTROLUX. Мощность 1800Вт. Радиус действия 13 м. Система фильтрации HEPA-12. Телескопическая трубка. Щетка пол-ковер, щелевая, для мягкой мебели. Уровень шума 78 дБ. Мягкий пуск двигателя. Дискретный регулятор мощности... </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Страна производства </P></TD>\r\n<TD width="50%">\r\n<P>Венгрия </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Гарантия (мес) </P></TD>\r\n<TD width="50%">\r\n<P>12 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Наличие на складе </P></TD>\r\n<TD width="50%">\r\n<P>в наличии </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Срок поставки </P></TD>\r\n<TD width="50%">\r\n<P>На следующий рабочий день после заказа </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Тип уборки </P></TD>\r\n<TD width="50%">\r\n<P>Сухая </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Мощность, Вт </P></TD>\r\n<TD width="50%">\r\n<P>1800 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Мощность всасывания, Вт </P></TD>\r\n<TD width="50%">\r\n<P>330 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Уровень шума, дБ </P></TD>\r\n<TD width="50%">\r\n<P>78 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Система фильтрации </P></TD>\r\n<TD width="50%">\r\n<P>W. HEPA 12 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Тип трубки всасывания </P></TD>\r\n<TD width="50%">\r\n<P>Телескопическая </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Регулятор мощности </P></TD>\r\n<TD width="50%">\r\n<P>Дискретный </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Длина шнура, м </P></TD>\r\n<TD width="50%">\r\n<P>13 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Тип пылесборника </P></TD>\r\n<TD width="50%">\r\n<P>Технология - мешок+контейнер </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Насадки </P></TD>\r\n<TD width="50%">\r\n<P>Пол-ковер. Щелевая. Для мягкой мебели </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Индикатор заполнения пылесборника </P></TD>\r\n<TD width="50%">\r\n<P>Нет данных </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Вес, кг </P></TD>\r\n<TD width="50%">\r\n<P>6,2 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Габариты (ДxШxВ), мм </P></TD>\r\n<TD width="50%">\r\n<P>320х375х290 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Особенности модели </P></TD>\r\n<TD width="50%">\r\n<P>Мощность 1800 Вт. Система фильтрации HEPA-12. Автонамотка кабеля, 13 м. Насадка Пол-ковер/Щелевая/для мягкой мебели. Пылесборник пластиковый контейнер (Двойная технология - мешок+контейнер) </P></TD></TR></TBODY></TABLE>'),
(8, 2, 'Kerher HGFD254', 'Kerher', 'Kerher'),
(8, 3, 'Kerher HGFD254', 'Kerher', 'Kerher'),
(9, 1, 'LG MCBN 45', '<P>Пылесос для сухой уборки ELECTROLUX. Цвет графит. Мощность 1800Вт. Многоразовый контейнер 4л. Авто-смотка шнура 9м. Фильтр НЕРА-12. Телескопическая трубка. Турбощетка, щелевая и пол/ковер. Шум 82дБ. Отключение при перегреве. Плавный пуск двигателя... </P>', '<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD colSpan=3>\r\n<P><STRONG>ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ</STRONG> </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Фирма-производитель </P></TD>\r\n<TD width="50%">\r\n<P>ELECTROLUX </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Модель </P></TD>\r\n<TD width="50%">\r\n<P>ZCX 6205 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Цена </P></TD>\r\n<TD width="50%">\r\n<P>159.00 y.e. </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Краткое описание </P></TD>\r\n<TD width="50%">\r\n<P>Пылесос для сухой уборки ELECTROLUX. Цвет графит. Мощность 1800Вт. Многоразовый контейнер 4л. Авто-смотка шнура 9м. Фильтр НЕРА-12. Телескопическая трубка. Турбощетка, щелевая и пол/ковер. Шум 82дБ. Отключение при перегреве. Плавный пуск двигателя... </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Страна производства </P></TD>\r\n<TD width="50%">\r\n<P>Венгрия </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Гарантия (мес) </P></TD>\r\n<TD width="50%">\r\n<P>12 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Наличие на складе </P></TD>\r\n<TD width="50%">\r\n<P>в наличии </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Срок поставки </P></TD>\r\n<TD width="50%">\r\n<P>На следующий рабочий день после заказа </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Тип уборки </P></TD>\r\n<TD width="50%">\r\n<P>Сухая </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Мощность, Вт </P></TD>\r\n<TD width="50%">\r\n<P>1800 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Мощность всасывания, Вт </P></TD>\r\n<TD width="50%">\r\n<P>300 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Уровень шума, дБ </P></TD>\r\n<TD width="50%">\r\n<P>82 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Система фильтрации </P></TD>\r\n<TD width="50%">\r\n<P>НЕРА-12 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Тип трубки всасывания </P></TD>\r\n<TD width="50%">\r\n<P>Телескопическая </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Регулятор мощности </P></TD>\r\n<TD width="50%">\r\n<P>Механический на корпусе </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Длина шнура, м </P></TD>\r\n<TD width="50%">\r\n<P>9 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Тип пылесборника </P></TD>\r\n<TD width="50%">\r\n<P>Многоразовый контейнер 4 л </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Насадки </P></TD>\r\n<TD width="50%">\r\n<P>Щелевая + пылевая щетка, для мебели. Турбо-щетка </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Индикатор заполнения пылесборника </P></TD>\r\n<TD width="50%">\r\n<P>Нет данных </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Вес, кг </P></TD>\r\n<TD width="50%">\r\n<P>5,75 </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Габариты (ДxШxВ), мм </P></TD>\r\n<TD width="50%">\r\n<P>Нет данных </P></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD width="50%">\r\n<P>Особенности модели </P></TD>\r\n<TD width="50%">\r\n<P>Цвет графит. Отключение при перегреве. Плавный пуск двигателя. Регулятор потока воздуха. Мощность 1800Вт. Фильтр НЕРА-12. Многоразовый контейнер 4л. Авто-смотка шнура 9м. Турбощетка. Уровень шума 82д </P></TD></TR></TBODY></TABLE>'),
(9, 2, 'LG MCBN 45', 'LG', 'LG'),
(9, 3, 'LG MCBN 45', 'LG', 'LG'),
(10, 1, 'LG CFG45', 'Тип плазменная панель Диагональ 42; Формат экрана 16:9; Яркость 1500 кд/м²; Контрастность 15000:1; Максимальное разрешение 1024 x 768 пикс; Угол обзора (гориз. / верт.) 178 / 178 °; Время отклика 2 мс Количество цветов 1073 млн.; Дополнительно антибликовое покрытие.', '<TABLE cellSpacing=0 cellPadding=0 width="100%" border=1>\r\n<CAPTION>Экран</CAPTION>\r\n<TBODY>\r\n<TR>\r\n<TD>Тип </TD>\r\n<TD>плазменная панель </TD></TR>\r\n<TR>\r\n<TD>Диагональ </TD>\r\n<TD>42 " </TD></TR>\r\n<TR>\r\n<TD>Формат экрана </TD>\r\n<TD>16:9 </TD></TR>\r\n<TR>\r\n<TD>Яркость </TD>\r\n<TD>1500 кд/м² </TD></TR>\r\n<TR>\r\n<TD>Контрастность </TD>\r\n<TD>15000:1 </TD></TR>\r\n<TR>\r\n<TD>Максимальное разрешение </TD>\r\n<TD>1024 x 768 пикс. </TD></TR>\r\n<TR>\r\n<TD>Угол обзора (гориз. / верт.) </TD>\r\n<TD>178 / 178 ° </TD></TR>\r\n<TR>\r\n<TD>Время отклика </TD>\r\n<TD>2 мс </TD></TR>\r\n<TR>\r\n<TD>Количество цветов </TD>\r\n<TD>1073 млн. </TD></TR>\r\n<TR>\r\n<TD>Дополнительно </TD>\r\n<TD>антибликовое покрытие </TD></TR></TBODY></TABLE>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=1>\r\n<CAPTION>Система вещания</CAPTION>\r\n<TBODY>\r\n<TR>\r\n<TD>Поддержка стандартов </TD>\r\n<TD>PAL, SECAM, NTSC, HDTV </TD></TR>\r\n<TR>\r\n<TD>Поддержка HDTV </TD>\r\n<TD>720p </TD></TR></TBODY></TABLE>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=1>\r\n<CAPTION>Звук</CAPTION>\r\n<TBODY>\r\n<TR>\r\n<TD>Мощность колонок </TD>\r\n<TD>2 x 10 Вт </TD></TR>\r\n<TR>\r\n<TD>Конфигурация звуковых выходов </TD>\r\n<TD>2.0 </TD></TR>\r\n<TR>\r\n<TD>Авторегулировка уровня звука </TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD>Эквалайзер </TD>\r\n<TD>нет </TD></TR></TBODY></TABLE>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=1>\r\n<CAPTION>Функции</CAPTION>\r\n<TBODY>\r\n<TR>\r\n<TD></A>Телетекст </TD>\r\n<TD>1000 страниц </TD></TR>\r\n<TR>\r\n<TD>Картинка в картинке </TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD>Таймер </TD>\r\n<TD>автовыключения, выключения </TD></TR>\r\n<TR>\r\n<TD>Двойной экран </TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD>Система оптимизации изображения </TD>\r\n<TD>да </TD></TR>\r\n<TR>\r\n<TD>Предустановки изображения </TD>\r\n<TD>динамичный, стандартный, мягкий, индивидуальный </TD></TR>\r\n<TR>\r\n<TD>Защита от детей </TD>\r\n<TD>да </TD></TR>\r\n<TR>\r\n<TD>Система плавного отображения движения </TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD>Количество TV каналов </TD>\r\n<TD>100 </TD></TR>\r\n<TR>\r\n<TD>Встроенные устройства </TD>\r\n<TD>нет </TD></TR></TBODY></TABLE>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=1>\r\n<CAPTION>Порты, входы, выходы</CAPTION>\r\n<TBODY>\r\n<TR>\r\n<TD>Входы/выходы </TD>\r\n<TD>SCART 2шт, Composite-In (1xRCA), Component-In (3xRCA), Audio-Out (mini-jack), HDMI 2шт, S-Video 2шт </TD></TR></TBODY></TABLE>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=1>\r\n<CAPTION>Питание</CAPTION>\r\n<TBODY>\r\n<TR>\r\n<TD>Напряжение питания </TD>\r\n<TD>от 110 до 240 В </TD></TR>\r\n<TR>\r\n<TD>Частота тока </TD>\r\n<TD>50, 60 Гц </TD></TR>\r\n<TR>\r\n<TD>Потребляемая мощность при работе/в режиме ожидания </TD>\r\n<TD>250 / 1 Вт </TD></TR></TBODY></TABLE>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=1>\r\n<CAPTION>Дополнительная информация</CAPTION>\r\n<TBODY>\r\n<TR>\r\n<TD>Вес </TD>\r\n<TD>41.5 кг </TD></TR>\r\n<TR>\r\n<TD>Размеры (Ш/В/Г) </TD>\r\n<TD>1047 X 764 X 264 мм </TD></TR>\r\n<TR>\r\n<TD>Возможные цвета </TD>\r\n<TD>чёрный </TD></TR>\r\n<TR>\r\n<TD>Комплектация </TD>\r\n<TD>кабель питания, пульт ДУ, руководство пользователя </TD></TR>\r\n<TR>\r\n<TD>Поддержка Kensington Lock </TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD>Крепление VESA </TD>\r\n<TD>600 x 400 мм </TD></TR>\r\n<TR>\r\n<TD>Гарантия </TD>\r\n<TD>36 мес </TD></TR></TBODY></TABLE>'),
(10, 2, 'LG CFG45', 'LG', 'LG'),
(10, 3, 'LG CFG45', 'LG', 'LG'),
(11, 1, 'Samsung VBH-84', '<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3789");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Тип </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>плазменная панель </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3790");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Диагональ </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>42 " </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3791");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Формат экрана </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>16:9 </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3793");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Контрастность </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>5000:1 </TD></TR></TBODY></TABLE>', '<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD width="100%">Экран </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl832>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3789");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Тип </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>плазменная панель </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3790");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Диагональ </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>42 " </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3791");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Формат экрана </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>16:9 </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3793");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Контрастность </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>5000:1 </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3794");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Максимальное разрешение </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>1920 x 1080 пикс. </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3795");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Угол обзора (гориз. / верт.) </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>178 / 178 ° </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3797");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A><STRONG>Количество цветов </STRONG></TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>68700 млн. </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR></TBODY></TABLE></DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Система вещания </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl833>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3799");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Поддержка стандартов </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>PAL, SECAM, NTSC, HDTV </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3800");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Поддержка HDTV </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>720p, 1080i, 1080p </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR></TBODY></TABLE></DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Звук </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3801");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A><STRONG>Мощность колонок</STRONG> </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>2 x 10 Вт </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3802");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A><EM>Конфигурация звуковых выходов </EM></TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>2.0 </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD>\r\n<UL>\r\n<LI><A onclick=''opis_window("3803");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Авторегулировка уровня звука </LI></UL></TD>\r\n<TD>\r\n<P>&nbsp;</P></TD>\r\n<TD width=1>\r\n<P>&nbsp;</P></TD>\r\n<TD>\r\n<P>нет </P></TD></TR>\r\n<TR>\r\n<TD colSpan=3>\r\n<UL>\r\n<LI></LI></UL></TD></TR>\r\n<TR>\r\n<TD>\r\n<UL>\r\n<LI><A onclick=''opis_window("3804");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Предустановки звука </LI></UL></TD>\r\n<TD>\r\n<P>&nbsp;</P></TD>\r\n<TD width=1>\r\n<P>&nbsp;</P></TD>\r\n<TD>\r\n<P>музыка, спорт </P></TD></TR>\r\n<TR>\r\n<TD colSpan=3>\r\n<UL>\r\n<LI></LI></UL></TD></TR>\r\n<TR>\r\n<TD>\r\n<UL>\r\n<LI><A onclick=''opis_window("3805");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Эквалайзер </LI></UL></TD>\r\n<TD>\r\n<P>&nbsp;</P></TD>\r\n<TD width=1></TD>\r\n<TD>\r\n<P>нет </P></TD></TR>\r\n<TR>\r\n<TD colSpan=3>\r\n<UL>\r\n<LI></LI></UL></TD></TR></TBODY></TABLE></DIV>\r\n<UL>\r\n<LI>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Функции </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE></LI></UL>\r\n<DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<UL>\r\n<LI><A onclick=''opis_window("3807");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Телетекст </LI></UL></TD>\r\n<TD>\r\n<P>&nbsp;</P></TD>\r\n<TD width=1>\r\n<P>&nbsp;</P></TD>\r\n<TD>\r\n<P>1500 страниц </P></TD></TR>\r\n<TR>\r\n<TD colSpan=3>\r\n<UL>\r\n<LI></LI></UL></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3808");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Картинка в картинке </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>да </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3809");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Таймер </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>выключения </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD>Двойной экран </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3811");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Система оптимизации изображения </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>да </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3812");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Предустановки изображения </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>динамичный, стандартный, мягкий, индивидуальный, театр </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3813");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Защита от детей </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>да </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3814");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Система плавного отображения движения </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3815");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Количество TV каналов </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>100 </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3816");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Встроенные устройства </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR></TBODY></TABLE></DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Порты, входы, выходы </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl836>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3819");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Входы/выходы </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD><EM>VGA (15 pin D-Sub), SCART, Composite-In (1xRCA), Component-In (3xRCA), S-Video, Audio-In (mini-jack), Audio-Out (mini-jack), HDMI 2шт, Audio-In (2xRCA), Audio-Out (2xRCA) </EM></TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR></TBODY></TABLE></DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Питание </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl838>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3821");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Напряжение питания </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>от 220 до 240 В </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("4350");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Частота тока </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>50, 60 Гц </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR></TBODY></TABLE></DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Дополнительная информация </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl839>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3825");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Размеры (Ш/В/Г) </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>1134 X 641 X 100 мм </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3826");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Возможные цвета </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>серебристый, чёрный </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3827");return false;'' href="http://www.price.ua/47742/panasonic_th-42py70.html"></A>Комплектация </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>кабель питания, кабель VGA, пульт ДУ, руководство пользователя </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD>Поддержка Kensington Lock </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD>Крепление VESA </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>есть </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD>Гарантия </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>36 мес </TD></TR></TBODY></TABLE></DIV>'),
(11, 2, 'Samsung', 'Samsung', 'Samsung'),
(11, 3, 'Samsung', 'Samsung', 'Samsung');
INSERT INTO `shop_products_translation` (`product_id`, `lang_id`, `product_name`, `product_short_description_rtf`, `product_description_rtf`) VALUES
(12, 1, 'Bosh Cora18', '<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3789");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Тип </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>плазменная панель </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3790");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Диагональ </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>42 " </TD></TR></TBODY></TABLE>', '<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD width="100%">Экран </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl832>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3789");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Тип </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>плазменная панель </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3790");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Диагональ </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>42 " </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3791");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Формат экрана </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>16:9 </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3792");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Яркость </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>1000 кд/м² </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3793");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Контрастность </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>10000:1 </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3794");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Максимальное разрешение </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>1024 x 768 пикс. </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3795");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Угол обзора (гориз. / верт.) </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>160 / 160 ° </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3797");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Количество цветов </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>29000 млн. </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR></TBODY></TABLE></DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Система вещания </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl833>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3799");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Поддержка стандартов </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>PAL, SECAM, NTSC, HDTV </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3800");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Поддержка HDTV </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>720p, 1080i, 1080p </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR></TBODY></TABLE></DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Звук </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl834>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3801");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Мощность колонок </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>2 x 10 Вт </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3802");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Конфигурация звуковых выходов </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>2.0 </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3803");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Авторегулировка уровня звука </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3804");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Предустановки звука </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>музыка, спорт </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3805");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Эквалайзер </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR></TBODY></TABLE></DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Функции </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl835>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3807");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Телетекст </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>1500 страниц </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3808");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Картинка в картинке </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>да </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3809");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Таймер </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>выключения </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD>Двойной экран </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3811");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Система оптимизации изображения </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>да </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3812");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Предустановки изображения </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>динамичный, стандартный, мягкий, индивидуальный </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3813");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Защита от детей </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>да </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3814");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Система плавного отображения движения </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3815");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Количество TV каналов </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>100 </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3816");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Встроенные устройства </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR></TBODY></TABLE></DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Порты, входы, выходы </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl836>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3819");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Входы/выходы </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>VGA (15 pin D-Sub), SCART 2шт, Composite-In (1xRCA), Component-In (3xRCA), HDMI 2шт, Audio-In (2xRCA) 2шт </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR></TBODY></TABLE></DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Питание </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl838>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3821");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Напряжение питания </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>от 220 до 240 В </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("4350");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Частота тока </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>50, 60 Гц </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("4351");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Потребляемая мощность при работе/в режиме ожидания </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>238 / 1 Вт </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR></TBODY></TABLE></DIV>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD align=right width=8></TD>\r\n<TD width="100%">Дополнительная информация </TD>\r\n<TD align=left width=8></TD></TR></TBODY></TABLE>\r\n<DIV id=bl839>\r\n<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>\r\n<TBODY>\r\n<TR>\r\n<TD><A onclick=''opis_window("3824");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Вес </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>27 кг </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3825");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Размеры (Ш/В/Г) </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>1020 X 680 X 97 мм </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3826");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Возможные цвета </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>чёрный </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD><A onclick=''opis_window("3827");return false;'' href="http://www.price.ua/47746/panasonic_th-42pv70.html"></A>Комплектация </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>кабель питания, кабель VGA, пульт ДУ, руководство пользователя </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD>Поддержка Kensington Lock </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>нет </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD>Крепление VESA </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>есть </TD></TR>\r\n<TR>\r\n<TD colSpan=3></TD></TR>\r\n<TR>\r\n<TD>Гарантия </TD>\r\n<TD></TD>\r\n<TD width=1></TD>\r\n<TD>36 мес </TD></TR></TBODY></TABLE></DIV>'),
(12, 2, 'Bosh', 'Bosh', 'Bosh'),
(12, 3, 'Bosh', 'Bosh', 'Bosh'),
(13, 1, 'Gagia CVDF52', '<P><EM>Краткое описание<BR></EM></P>\r\n<P>Электрокофемолка <STRONG>GAGGIA</STRONG>. Мощность 230 Вт. Вместимость контейнера для кофе - 180 г. Система помола - жернова. Точный регулятор степени помола - 9 позиций. Ножи из нержавеющей стали. </P>', '<P><STRONG>ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ</STRONG><BR></P>\r\n<UL>\r\n<LI>Фирма-производитель<BR></LI>\r\n<LI>GAGGIA<BR></LI>\r\n<LI>Модель<BR></LI>\r\n<LI>MM silver<BR></LI>\r\n<LI>Цена<BR></LI>\r\n<LI>93.00 y.e.<BR></LI>\r\n<LI>Съемный контейнер для молотого кофе...<BR></LI></UL>\r\n<P>Страна производства<BR></P>\r\n<P>Италия<BR></P>\r\n<P>Гарантия (мес)<BR></P>\r\n<P>12<BR></P>\r\n<P>Наличие на складе<BR></P>\r\n<P>в наличии<BR></P>\r\n<P>Срок поставки<BR></P>\r\n<P>На следующий рабочий день после заказа<BR></P>\r\n<P>Система помола<BR></P>\r\n<P>Жернова<BR></P>\r\n<P>Мощность, Вт<BR></P>\r\n<P>230<BR></P>\r\n<P>Вместимость, г<BR></P>\r\n<P>180<BR></P>\r\n<P>Регулировка степени помола<BR></P>\r\n<P>Есть<BR></P>\r\n<P>Особенности модели<BR></P>\r\n<P>Вес - 1,4 кг. Материал корпуса - пластик<BR>Значения характеристик могут быть изменены производителем без уведомления. Вы можете уточнить характеристики на сайте производителя оборудования. Магазин не несет ответственности за несоответствие указанных здесь характеристик реальным. Если вы заметили ошибку просьба cообщить. </P>'),
(13, 2, 'Gagia', 'Gagia', 'Gagia'),
(13, 3, 'Gagia', 'Gagia', 'Gagia'),
(14, 1, 'GAGIA CV25', '<P>Страна производства<BR></P>\n<P>Италия<BR></P>\n<P>Гарантия (мес)<BR></P>\n<P>12 </P>', '<P><STRONG>ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ<BR></STRONG></P>\n<P>Фирма-производитель<BR></P>\n<P>GAGGIA<BR></P>\n<P>Модель<BR></P>\n<P>MDF<BR></P>\n<P>Цена<BR></P>\n<P>210.00 y.e.<BR></P>\n<UL>\n<LI>Краткое описание<BR></LI>\n<LI>Электрокофемолка GAGGIA. Мощность 230 Вт. Вместимость контейнера для кофе - 250 г. Система помола - жернова. Точный регулятор степени помола - 35 позиций. Ножи из нержавеющей стали. Дозатор порций. Подставка для рожка. Встроенный таймер…<BR></LI></UL>\n<P>Наличие на складе<BR></P>\n<P>в наличии<BR></P>\n<P>Срок поставки<BR></P>\n<P>На следующий рабочий день после заказа<BR></P>\n<P>Система помола<BR></P>\n<P>Жернова<BR></P>\n<P>Мощность, Вт<BR></P>\n<P>230<BR></P>\n<P>Вместимость, г<BR></P>\n<P>250<BR></P>\n<P>Регулировка степени помола<BR></P>\n<P>Есть<BR></P>\n<P>Особенности модели<BR></P>\n<P>Вес - 5 кг. Материал корпуса - пластик </P>\n<P>Значения характеристик могут быть изменены производителем без уведомления. Вы можете уточнить характеристики на сайте производителя оборудования. Магазин не несет ответственности за несоответствие указанных здесь характеристик реальным. Если вы заметили ошибку просьба </P>'),
(14, 2, 'GAGIA CV25', 'gag', 'gag'),
(14, 3, 'GAGIA CV25', 'gag', 'gag'),
(15, 1, 'Bosh CVS468', '<P>ARDO<BR></P>\r\n<P>Модель<BR></P>\r\n<P>DP 24 SA<BR></P>\r\n<P>Цена<BR></P>\r\n<P>396.00 y.e.<BR></P>', '<P><STRONG>ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ </STRONG></P>\r\n<P><STRONG><EM>Фирма-производитель</EM></STRONG></P>\r\n<UL>\r\n<LI>\r\n<DIV>&nbsp;Краткое описание Холодильник ARDO. 141.7х54х58 см. 2-х камерный холодильник-морозильник. Общий объем 240 л, холодильная камера - 193 л, морозильная – 38 л. 1 компрессор.</DIV></LI>\r\n<LI>\r\n<DIV>&nbsp;Мощность замораживания 3 кг в сутки. Звуковая и световая индикация открытых дверей… Страна производства Италия Гарантия (мес) 12 Наличие на складе в наличии </DIV></LI>\r\n<LI>\r\n<DIV>Срок поставки На следующий рабочий день после заказа Количество камер 2 Количество компрессоров 1 </DIV></LI></UL>\r\n<P>Расположение морозильной камеры Верх Общий внутренний объем, л 231 Объем холодильной камеры, л 193 Объем морозильной камеры, л 38 Класс энергопотребления А Автоматическая разморозка Есть No Frost Нет Возможность перевешивания двери Есть Управление Электромеханическое Габариты (ШхВхГ), см 141,7х54х58 Цвет Белый Особенности модели 3 полки-решетки, регулируемые по высоте. 1 полка из небьющегося стекла. 2 контейнера для овощей, контейнер для мяса. 2 полки для бутылок. Подставка для яиц (Egg-rack). 2 полки для консервов Значения характеристик могут быть изменены производителем без уведомления. Вы можете уточнить характеристики на сайте производителя оборудования. Магазин не несет ответственности за несоответствие указанных здесь характеристик реальным. Если вы заметили ошибку просьба cообщить. </P>'),
(15, 2, 'Bosh CVS468', 'bosh', 'bosh'),
(15, 3, 'Bosh CVS468', 'bosh', 'bosh'),
(16, 1, 'DFG45', '<P>Фирма-производитель </P>\r\n<P>ARDO </P>\r\n<P>Модель </P>\r\n<P>COO 2210 SHC </P>\r\n<P>Цена </P>\r\n<P>743.00 y.e. </P>', '<P><STRONG>ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ</STRONG><BR></P>\r\n<P><EM>Краткое описание<BR></EM></P>\r\n<P><EM>Холодильник ARDO. 590х1850х640. Объем 316(218+83). Класс A+. Антибактериальное покрытие. Мощность заморозки 5кг/сутки. Авто. размораживание. Полки из закаленного стекла. Отделение для овощей и фруктов. Удобные дверные полки. Уровень шума дБ 38. ECO режим…<BR></EM></P>\r\n<P>Страна производства<BR></P>\r\n<P>Италия<BR></P>\r\n<P>Гарантия (мес)<BR></P>\r\n<P>12<BR></P>\r\n<P>Наличие на складе<BR></P>\r\n<P>в наличии<BR></P>\r\n<P>Срок поставки<BR></P>\r\n<P>На следующий рабочий день после заказа<BR></P>\r\n<P>Количество камер<BR></P>\r\n<P>2<BR></P>\r\n<P>Количество компрессоров<BR></P>\r\n<P>1<BR></P>\r\n<P>Расположение морозильной камеры<BR></P>\r\n<P>Низ<BR></P>\r\n<P>Общий внутренний объем, л<BR></P>\r\n<P>316<BR></P>\r\n<P>Объем холодильной камеры, л<BR></P>\r\n<P>218<BR></P>\r\n<P>Объем морозильной камеры, л<BR></P>\r\n<P>83<BR></P>\r\n<P>Класс энергопотребления<BR></P>\r\n<P>A+<BR></P>\r\n<P>Автоматическая разморозка<BR></P>\r\n<P>Есть<BR></P>\r\n<P>No Frost<BR></P>\r\n<P>Нет<BR></P>\r\n<P>Возможность перевешивания двери<BR></P>\r\n<P>Нет<BR></P>\r\n<P>Управление<BR></P>\r\n<P>Электроное<BR></P>\r\n<P>Габариты (ШхВхГ), см<BR></P>\r\n<P>590х1850х640<BR></P>\r\n<P>Цвет<BR></P>\r\n<P>Кремовый<BR></P>\r\n<UL>\r\n<LI>Особенности модели<BR></LI>\r\n<LI>Хромированная полка для бутылок. Формочка для льда (Ice-trays). Скребок для льда (ice-scraper). Подставка для яиц (Egg-rack). Интерактивная панель LED </LI>\r\n<LI>Значения характеристик могут быть изменены производителем без уведомления. Вы можете уточнить характеристики на сайте производителя оборудования. Магазин не несет ответственности за несоответствие указанных здесь характеристик реальным. Если вы заметили ошибку просьба cообщить. </LI></UL>'),
(16, 2, 'DFG45', 'DFG45', 'DFG45'),
(16, 3, 'DFG45', 'DFG45', 'DFG45');

-- --------------------------------------------------------

--
-- Структура таблицы `shop_product_external_properties`
--

CREATE TABLE IF NOT EXISTS `shop_product_external_properties` (
  `product_code` char(200) NOT NULL default '',
  `product_price` decimal(10,2) default '0.00',
  `product_count` int(11) default NULL,
  `curr_id` int(10) unsigned default '1',
  PRIMARY KEY  (`product_code`),
  KEY `curr_id` (`curr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `shop_product_external_properties`
--

INSERT INTO `shop_product_external_properties` (`product_code`, `product_price`, `product_count`, `curr_id`) VALUES
('1061409604', 4577.00, 45, 4),
('123414535', 1233.00, 145, 2),
('1316', 5460.00, 4, 2),
('1321', 3123.00, 6, 2),
('136551', 495456.00, 8, 2),
('16156', 545.00, 54, 2),
('16464662', 8746.00, 5, 2),
('165', 464.56, 8, 4),
('165461', 952.00, 1, 2),
('45464', 6461.00, 12, 2),
('46141959', 6548.00, 5, 2),
('46146', 548.00, NULL, 2),
('465453', 4566.45, 5, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `shop_product_params`
--

CREATE TABLE IF NOT EXISTS `shop_product_params` (
  `pp_id` int(10) unsigned NOT NULL auto_increment,
  `pt_id` int(10) unsigned NOT NULL default '0',
  `pp_type` char(50) NOT NULL default '',
  PRIMARY KEY  (`pp_id`),
  KEY `pt_id` (`pt_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Дамп данных таблицы `shop_product_params`
--

INSERT INTO `shop_product_params` (`pp_id`, `pt_id`, `pp_type`) VALUES
(1, 1, 'integer'),
(2, 1, 'string'),
(3, 2, 'string'),
(4, 2, 'string'),
(5, 3, 'integer'),
(6, 3, 'string'),
(7, 7, 'string'),
(8, 7, 'integer'),
(9, 6, 'string'),
(10, 8, 'string'),
(11, 8, 'string');

-- --------------------------------------------------------

--
-- Структура таблицы `shop_product_params_translation`
--

CREATE TABLE IF NOT EXISTS `shop_product_params_translation` (
  `pp_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `pp_name` char(150) NOT NULL default '',
  PRIMARY KEY  (`pp_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `shop_product_params_translation`
--

INSERT INTO `shop_product_params_translation` (`pp_id`, `lang_id`, `pp_name`) VALUES
(1, 1, 'Вес'),
(1, 2, 'Вага'),
(1, 3, 'Weight'),
(2, 1, 'Размер екрана'),
(2, 2, 'Розмір екрану'),
(2, 3, 'Screen resolution'),
(3, 1, 'Диагональ'),
(3, 2, 'Діагональ'),
(3, 3, 'Line'),
(4, 1, 'Вес'),
(4, 2, 'Вага'),
(4, 3, 'Weight'),
(5, 1, 'вес'),
(5, 2, 'вага'),
(5, 3, 'netto'),
(6, 1, 'объем'),
(6, 2, 'об''ем'),
(6, 3, 'v'),
(7, 1, 'цвет'),
(7, 2, 'колір'),
(7, 3, 'color'),
(8, 1, 'температура'),
(8, 2, 'температура'),
(8, 3, 't'),
(9, 1, 'мощность'),
(9, 2, 'потужність'),
(9, 3, 'power'),
(10, 1, 'мощность'),
(10, 2, 'потужність'),
(10, 3, 'power'),
(11, 1, 'длинна шнура'),
(11, 2, 'довжина дроту'),
(11, 3, 'l');

-- --------------------------------------------------------

--
-- Структура таблицы `shop_product_param_values`
--

CREATE TABLE IF NOT EXISTS `shop_product_param_values` (
  `ppv_id` int(10) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL default '0',
  `pp_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ppv_id`),
  KEY `product_id` (`product_id`),
  KEY `pp_id` (`pp_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Дамп данных таблицы `shop_product_param_values`
--

INSERT INTO `shop_product_param_values` (`ppv_id`, `product_id`, `pp_id`) VALUES
(5, 4, 5),
(6, 4, 6),
(7, 5, 3),
(8, 5, 4),
(9, 6, 5),
(10, 6, 6),
(11, 7, 9),
(12, 8, 9),
(13, 9, 9),
(14, 10, 3),
(15, 10, 4),
(16, 11, 3),
(17, 11, 4),
(18, 12, 3),
(19, 12, 4),
(20, 13, 5),
(21, 13, 6),
(22, 14, 7),
(23, 14, 8);

-- --------------------------------------------------------

--
-- Структура таблицы `shop_product_param_values_translation`
--

CREATE TABLE IF NOT EXISTS `shop_product_param_values_translation` (
  `ppv_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `ppv_value` text NOT NULL,
  PRIMARY KEY  (`ppv_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `shop_product_param_values_translation`
--

INSERT INTO `shop_product_param_values_translation` (`ppv_id`, `lang_id`, `ppv_value`) VALUES
(5, 1, '4'),
(5, 2, '4'),
(5, 3, '4'),
(6, 1, '350 л'),
(6, 2, '350 л'),
(6, 3, '350 l'),
(7, 1, '5841 дм'),
(7, 2, '5841 дм'),
(7, 3, '5841 d'),
(8, 1, '45 кг'),
(8, 2, '45 кг'),
(8, 3, '45 kg'),
(9, 1, '8'),
(9, 2, '8'),
(9, 3, '8'),
(10, 1, '35 л'),
(10, 2, '35 л'),
(10, 3, '35 l'),
(11, 1, '45656887 W'),
(11, 2, '45656887 W'),
(11, 3, '45656887 W'),
(12, 1, '1 W'),
(12, 2, '1 W'),
(12, 3, '1 W'),
(13, 1, '7979465 W'),
(13, 2, '7979465 W'),
(13, 3, '7979465 W'),
(14, 1, '456419 dm'),
(14, 2, '456419 dm'),
(14, 3, '456419 dm'),
(15, 1, '0.5 кг'),
(15, 2, '0.5 кг'),
(15, 3, '0.5 kg'),
(16, 1, ''),
(16, 2, ''),
(16, 3, ''),
(17, 1, ''),
(17, 2, ''),
(17, 3, ''),
(18, 1, '4565'),
(18, 2, '4565'),
(18, 3, '4565'),
(19, 1, '5 т'),
(19, 2, '5 т'),
(19, 3, '5 t'),
(20, 1, '500'),
(20, 2, '500'),
(20, 3, '500'),
(21, 1, '5 л'),
(21, 2, '5 л'),
(21, 3, '5 l'),
(22, 1, ''),
(22, 2, ''),
(22, 3, ''),
(23, 1, ''),
(23, 2, ''),
(23, 3, '');

-- --------------------------------------------------------

--
-- Структура таблицы `shop_product_statuses`
--

CREATE TABLE IF NOT EXISTS `shop_product_statuses` (
  `ps_id` int(10) unsigned NOT NULL auto_increment,
  `ps_is_default` tinyint(1) NOT NULL default '0',
  `right_id` int(10) unsigned default NULL,
  `ps_is_visible` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ps_id`),
  KEY `gr_id` (`right_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `shop_product_statuses`
--

INSERT INTO `shop_product_statuses` (`ps_id`, `ps_is_default`, `right_id`, `ps_is_visible`) VALUES
(1, 1, 1, 0),
(2, 0, 2, 1),
(3, 0, 1, 1),
(4, 0, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `shop_product_statuses_translation`
--

CREATE TABLE IF NOT EXISTS `shop_product_statuses_translation` (
  `ps_id` int(10) unsigned NOT NULL default '0',
  `lang_id` int(10) unsigned NOT NULL default '0',
  `ps_name` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`ps_id`,`lang_id`),
  KEY `lang_id` (`lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `shop_product_statuses_translation`
--

INSERT INTO `shop_product_statuses_translation` (`ps_id`, `lang_id`, `ps_name`) VALUES
(1, 1, 'Доступен'),
(1, 2, 'Доступний'),
(1, 3, 'Enabled'),
(2, 1, 'Отсутствует'),
(2, 2, 'Відсутній'),
(2, 3, 'Absense'),
(3, 1, 'Принимаются заказы'),
(3, 2, 'Приймаються замовлення'),
(3, 3, 'Order'),
(4, 1, 'Ожидается поставка'),
(4, 2, 'Чекаємо на поставку'),
(4, 3, 'Waiting shipping');

-- --------------------------------------------------------

--
-- Структура таблицы `shop_product_types`
--

CREATE TABLE IF NOT EXISTS `shop_product_types` (
  `pt_id` int(10) unsigned NOT NULL auto_increment,
  `pt_name` char(100) NOT NULL default '',
  PRIMARY KEY  (`pt_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Дамп данных таблицы `shop_product_types`
--

INSERT INTO `shop_product_types` (`pt_id`, `pt_name`) VALUES
(1, 'Калькуляторы'),
(2, 'Телевизор'),
(3, 'Микроволновые печи'),
(4, 'Холодильники'),
(6, 'Пылесосы'),
(7, 'Кофемолки'),
(8, 'Утюги');

-- --------------------------------------------------------

--
-- Структура таблицы `user_groups`
--

CREATE TABLE IF NOT EXISTS `user_groups` (
  `group_id` int(10) unsigned NOT NULL auto_increment,
  `group_name` char(50) NOT NULL default '',
  `group_default` tinyint(1) NOT NULL default '0',
  `group_user_default` tinyint(1) NOT NULL default '0',
  `group_default_rights` int(10) unsigned default NULL,
  PRIMARY KEY  (`group_id`),
  KEY `group_default` (`group_default`),
  KEY `default_access_level` (`group_default_rights`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `user_groups`
--

INSERT INTO `user_groups` (`group_id`, `group_name`, `group_default`, `group_user_default`, `group_default_rights`) VALUES
(1, 'Администратор', 0, 0, 3),
(3, 'Гость', 1, 0, 1),
(4, 'Пользователь', 0, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `user_group_rights`
--

CREATE TABLE IF NOT EXISTS `user_group_rights` (
  `right_id` int(10) unsigned NOT NULL auto_increment,
  `right_name` char(20) NOT NULL default '',
  `right_const` char(20) NOT NULL default '',
  PRIMARY KEY  (`right_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `user_group_rights`
--

INSERT INTO `user_group_rights` (`right_id`, `right_name`, `right_const`) VALUES
(1, 'Read only', 'ACCESS_READ'),
(2, 'Edit', 'ACCESS_EDIT'),
(3, 'Full control', 'ACCESS_FULL');

-- --------------------------------------------------------

--
-- Структура таблицы `user_users`
--

CREATE TABLE IF NOT EXISTS `user_users` (
  `u_id` int(10) unsigned NOT NULL auto_increment,
  `u_name` varchar(50) NOT NULL default '',
  `u_password` varchar(40) NOT NULL default '',
  `u_is_active` tinyint(1) NOT NULL default '1',
  `u_address` mediumtext NOT NULL,
  `u_contact_person` varchar(250) NOT NULL default '',
  `u_phone` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`u_id`),
  UNIQUE KEY `u_login` (`u_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=117 ;

--
-- Дамп данных таблицы `user_users`
--

INSERT INTO `user_users` (`u_id`, `u_name`, `u_password`, `u_is_active`, `u_address`, `u_contact_person`, `u_phone`) VALUES
(113, 'demo@energine.org', '89e495e7941cf9e40e6980d14a16bf023ccd4c91', 1, 'Адрес мой новый', 'demo', '555-55-55'),
(116, 'test@test.test', 'af7b62b8fee9d5b614398a6fa0a5b52dc76dcca6', 1, 'test', 'test', '5555555');

-- --------------------------------------------------------

--
-- Структура таблицы `user_user_groups`
--

CREATE TABLE IF NOT EXISTS `user_user_groups` (
  `u_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`u_id`,`group_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `user_user_groups`
--

INSERT INTO `user_user_groups` (`u_id`, `group_id`) VALUES
(113, 1),
(116, 4);

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `image_photo_gallery`
--
ALTER TABLE `image_photo_gallery`
  ADD CONSTRAINT `image_photo_gallery_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `image_photo_gallery_ibfk_2` FOREIGN KEY (`pg_photo_img`) REFERENCES `share_uploads` (`upl_path`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `image_photo_gallery_translation`
--
ALTER TABLE `image_photo_gallery_translation`
  ADD CONSTRAINT `image_photo_gallery_translation_ibfk_1` FOREIGN KEY (`pg_id`) REFERENCES `image_photo_gallery` (`pg_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `image_photo_gallery_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `share_access_level`
--
ALTER TABLE `share_access_level`
  ADD CONSTRAINT `share_access_level_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `share_access_level_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`group_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `share_access_level_ibfk_3` FOREIGN KEY (`right_id`) REFERENCES `user_group_rights` (`right_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `share_lang_tags_translation`
--
ALTER TABLE `share_lang_tags_translation`
  ADD CONSTRAINT `FK_Reference_6` FOREIGN KEY (`ltag_id`) REFERENCES `share_lang_tags` (`ltag_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_tranaslatelv_language` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `share_news`
--
ALTER TABLE `share_news`
  ADD CONSTRAINT `share_news_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `share_news_translation`
--
ALTER TABLE `share_news_translation`
  ADD CONSTRAINT `share_news_translation_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `share_news` (`news_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `share_news_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `share_sitemap`
--
ALTER TABLE `share_sitemap`
  ADD CONSTRAINT `share_sitemap_ibfk_5` FOREIGN KEY (`tmpl_id`) REFERENCES `share_templates` (`tmpl_id`),
  ADD CONSTRAINT `share_sitemap_ibfk_6` FOREIGN KEY (`smap_pid`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `share_sitemap_translation`
--
ALTER TABLE `share_sitemap_translation`
  ADD CONSTRAINT `FK_sitemaplv_language` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_sitemaplv_sitemap` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `share_tag_map`
--
ALTER TABLE `share_tag_map`
  ADD CONSTRAINT `share_tag_map_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `share_tag_map_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `share_tags` (`tag_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `share_textblocks`
--
ALTER TABLE `share_textblocks`
  ADD CONSTRAINT `share_textblocks_ibfk_1` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `share_textblocks_translation`
--
ALTER TABLE `share_textblocks_translation`
  ADD CONSTRAINT `share_textblocks_translation_ibfk_1` FOREIGN KEY (`tb_id`) REFERENCES `share_textblocks` (`tb_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `share_textblocks_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_basket`
--
ALTER TABLE `shop_basket`
  ADD CONSTRAINT `shop_basket_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `shop_products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_basket_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `share_session` (`session_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_currency_translation`
--
ALTER TABLE `shop_currency_translation`
  ADD CONSTRAINT `shop_currency_translation_ibfk_1` FOREIGN KEY (`curr_id`) REFERENCES `shop_currency` (`curr_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_currency_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_discounts`
--
ALTER TABLE `shop_discounts`
  ADD CONSTRAINT `shop_discounts_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`group_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_orders`
--
ALTER TABLE `shop_orders`
  ADD CONSTRAINT `shop_orders_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `user_users` (`u_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shop_orders_ibfk_2` FOREIGN KEY (`os_id`) REFERENCES `shop_order_statuses` (`os_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_order_statuses_translation`
--
ALTER TABLE `shop_order_statuses_translation`
  ADD CONSTRAINT `shop_order_statuses_translation_ibfk_1` FOREIGN KEY (`os_id`) REFERENCES `shop_order_statuses` (`os_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_order_statuses_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_products`
--
ALTER TABLE `shop_products`
  ADD CONSTRAINT `shop_products_ibfk_16` FOREIGN KEY (`product_photo_img`) REFERENCES `share_uploads` (`upl_path`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `shop_products_ibfk_20` FOREIGN KEY (`product_thumb_img`) REFERENCES `share_uploads` (`upl_path`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `shop_products_ibfk_24` FOREIGN KEY (`smap_id`) REFERENCES `share_sitemap` (`smap_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_products_ibfk_25` FOREIGN KEY (`pt_id`) REFERENCES `shop_product_types` (`pt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_products_ibfk_26` FOREIGN KEY (`producer_id`) REFERENCES `shop_producers` (`producer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_products_ibfk_27` FOREIGN KEY (`ps_id`) REFERENCES `shop_product_statuses` (`ps_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_products_translation`
--
ALTER TABLE `shop_products_translation`
  ADD CONSTRAINT `shop_products_translation_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `shop_products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_products_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_product_external_properties`
--
ALTER TABLE `shop_product_external_properties`
  ADD CONSTRAINT `shop_product_external_properties_ibfk_3` FOREIGN KEY (`product_code`) REFERENCES `shop_products` (`product_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shop_product_external_properties_ibfk_4` FOREIGN KEY (`curr_id`) REFERENCES `shop_currency` (`curr_id`);

--
-- Ограничения внешнего ключа таблицы `shop_product_params`
--
ALTER TABLE `shop_product_params`
  ADD CONSTRAINT `shop_product_params_ibfk_1` FOREIGN KEY (`pt_id`) REFERENCES `shop_product_types` (`pt_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_product_params_translation`
--
ALTER TABLE `shop_product_params_translation`
  ADD CONSTRAINT `shop_product_params_translation_ibfk_1` FOREIGN KEY (`pp_id`) REFERENCES `shop_product_params` (`pp_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_product_params_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_product_param_values`
--
ALTER TABLE `shop_product_param_values`
  ADD CONSTRAINT `shop_product_param_values_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `shop_products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_product_param_values_ibfk_2` FOREIGN KEY (`pp_id`) REFERENCES `shop_product_params` (`pp_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_product_param_values_translation`
--
ALTER TABLE `shop_product_param_values_translation`
  ADD CONSTRAINT `shop_product_param_values_translation_ibfk_1` FOREIGN KEY (`ppv_id`) REFERENCES `shop_product_param_values` (`ppv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_product_param_values_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_product_statuses`
--
ALTER TABLE `shop_product_statuses`
  ADD CONSTRAINT `shop_product_statuses_ibfk_1` FOREIGN KEY (`right_id`) REFERENCES `user_group_rights` (`right_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `shop_product_statuses_translation`
--
ALTER TABLE `shop_product_statuses_translation`
  ADD CONSTRAINT `shop_product_statuses_translation_ibfk_1` FOREIGN KEY (`ps_id`) REFERENCES `shop_product_statuses` (`ps_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shop_product_statuses_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_groups`
--
ALTER TABLE `user_groups`
  ADD CONSTRAINT `user_groups_ibfk_1` FOREIGN KEY (`group_default_rights`) REFERENCES `user_group_rights` (`right_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_user_groups`
--
ALTER TABLE `user_user_groups`
  ADD CONSTRAINT `user_user_groups_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `user_users` (`u_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_user_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `user_groups` (`group_id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS=1;

COMMIT;
