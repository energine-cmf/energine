SET names utf8;
ALTER TABLE share_uploads add column upl_is_mp4 tinyint(1) not null default 0;
ALTER TABLE share_uploads add column upl_is_webm tinyint(1) not null default 0;
ALTER TABLE share_uploads add column upl_is_flv tinyint(1) not null default 0;

update share_uploads set upl_is_mp4=1 where upl_mime_type='video/mp4';
update share_uploads set upl_is_webm=1 where upl_mime_type='video/mp4';
