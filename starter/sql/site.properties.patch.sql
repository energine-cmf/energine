--
-- Table structure for table `share_sites_properties`
--

CREATE TABLE `share_sites_properties` (
  `prop_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(10) unsigned DEFAULT NULL,
  `prop_name` varchar(255) NOT NULL,
  `prop_value` text NOT NULL,
  PRIMARY KEY (`prop_id`),
  UNIQUE KEY `site_id` (`site_id`,`prop_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `share_sites_properties`
--
ALTER TABLE `share_sites_properties`
  ADD CONSTRAINT `share_sites_properties_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `share_sites` (`site_id`) ON DELETE CASCADE ON UPDATE CASCADE;