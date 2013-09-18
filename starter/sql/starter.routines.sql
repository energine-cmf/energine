SET NAMES utf8;

SET UNIQUE_CHECKS         = 0;
SET FOREIGN_KEY_CHECKS    = 0;
SET character_set_client  = utf8;
SET character_set_results = utf8;
SET collation_connection  = utf8_general_ci;
SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

/*
DROP FUNCTION IF EXISTS `get_share_uploads_repo_id`;

DELIMITER ;;

CREATE FUNCTION `get_share_uploads_repo_id`(media_id INT UNSIGNED) RETURNS int(10) UNSIGNED
DETERMINISTIC
  BEGIN
    DECLARE upl_pid INT;
    SET upl_pid = 0;
    CALL proc_get_share_uploads_repo_id(media_id, upl_pid);
    RETURN upl_pid;
  END ;;

DELIMITER ;

DROP PROCEDURE IF EXISTS `proc_get_share_uploads_repo_id`;

DELIMITER ;;

CREATE PROCEDURE `proc_get_share_uploads_repo_id`(IN media_id INT UNSIGNED, OUT repo_id INT UNSIGNED)
  BEGIN
    SET max_sp_recursion_depth=100;
    SELECT upl_pid INTO repo_id FROM share_uploads WHERE upl_id = media_id;
    IF repo_id > 0 THEN
      CALL proc_get_share_uploads_repo_id(repo_id, repo_id);
    ELSE
      SELECT media_id INTO repo_id;
    END IF;
  END ;;

DELIMITER ;*/

DROP FUNCTION IF EXISTS `get_upl_parent`;

DELIMITER ;;

CREATE FUNCTION `get_upl_parent`(`in_id` INT(10) UNSIGNED) RETURNS int(10) unsigned
READS SQL DATA
  RETURN (select ifnull(`upl_pid`, 0) from `share_uploads` where `upl_id` = in_id);;

DELIMITER ;

DROP PROCEDURE IF EXISTS `proc_get_upl_pid_list`;

DELIMITER ;;

CREATE PROCEDURE `proc_get_upl_pid_list` (IN `in_id` INT(10) UNSIGNED)
MODIFIES SQL DATA
  BEGIN
    create temporary table if not exists temp (id int(10) UNSIGNED, title varchar(255)) DEFAULT CHARSET=utf8;
    set @_pid = in_id;

    while @_pid > 0 do
    insert into `temp` select upl_id, upl_title from `share_uploads` where upl_id = @_pid;
    set @_pid = get_upl_parent(@_pid);
    end while;
    select * from `temp`;
    drop table if exists `temp`;
  END;;

DELIMITER ;

DROP PROCEDURE IF EXISTS `proc_update_dir_date`;

DELIMITER ;;

CREATE PROCEDURE `proc_update_dir_date`(IN `in_id` INT UNSIGNED, IN `in_date` DATETIME)
    MODIFIES SQL DATA
BEGIN
	set @_pid = get_upl_parent(in_id);

    while @_pid > 0 do
	update share_uploads set upl_publication_date=in_date where upl_id=@_pid;
	set @_pid = get_upl_parent(@_pid);
    end while;
END;;

DELIMITER ;