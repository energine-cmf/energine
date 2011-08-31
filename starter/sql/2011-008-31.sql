DROP TABLE IF EXISTS `apps_news_tags`;
CREATE TABLE IF NOT EXISTS `apps_news_tags` (
  `news_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`news_id`,`tag_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `stb_news_tags`
--
ALTER TABLE `apps_news_tags`
  ADD CONSTRAINT `apps_news_tags_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `stb_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `apps_news_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `share_tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;