SET names utf8;

CREATE TABLE `share_tags_translation` (
  `tag_id` int(11) unsigned NOT NULL,
  `lang_id` int(11) unsigned NOT NULL,
  `tag_name` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`tag_id`,`lang_id`),
  KEY `lang_id` (`lang_id`),
  CONSTRAINT `share_tags_translation_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `share_languages` (`lang_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `share_tags_translation_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `share_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO share_tags_translation (tag_id, lang_id, tag_name)
  SELECT tag_id, 1 as lang_id, tag_name from share_tags;

INSERT INTO share_tags_translation (tag_id, lang_id, tag_name)
  SELECT tag_id, 2 as lang_id, tag_name from share_tags;

ALTER TABLE share_tags CHANGE tag_name tag_code char(100);
