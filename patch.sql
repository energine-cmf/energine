ALTER TABLE share_sites DROP site_is_indexed;
ALTER TABLE share_sites DROP site_meta_robots;
ALTER TABLE share_sites ADD site_meta_robots SET('NOINDEX', 'NOFOLLOW', 'NOARCHIVE', 'NOSNIPPET', 'NOODP') NULL;
ALTER TABLE share_sitemap DROP smap_meta_robots;
ALTER TABLE share_sitemap ADD smap_meta_robots SET('NOINDEX', 'NOFOLLOW', 'NOARCHIVE', 'NOSNIPPET', 'NOODP') NULL;
ALTER TABLE share_sitemap DROP smap_is_indexed;