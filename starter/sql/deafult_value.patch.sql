ALTER TABLE `share_lang_tags_translation` CHANGE `ltag_id` `ltag_id` INT(10) UNSIGNED NOT NULL, CHANGE `lang_id` `lang_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `share_sitemap_translation` CHANGE `smap_id` `smap_id` INT(10) UNSIGNED NOT NULL, CHANGE `lang_id` `lang_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `share_textblocks_translation` CHANGE `tb_id` `tb_id` INT(10) UNSIGNED NOT NULL, CHANGE `lang_id` `lang_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `user_user_groups` CHANGE `u_id` `u_id` INT(10) UNSIGNED NOT NULL, CHANGE `group_id` `group_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `share_access_level` CHANGE `smap_id` `smap_id` INT(10) UNSIGNED NOT NULL, CHANGE `group_id` `group_id` INT(10) UNSIGNED NOT NULL, CHANGE `right_id` `right_id` INT(10) UNSIGNED NOT NULL;