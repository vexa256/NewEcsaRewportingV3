DROP VIEW IF EXISTS vw_semi_annual_cluster_summary;
CREATE ALGORITHM=UNDEFINED
DEFINER=`root`@`localhost`
SQL SECURITY DEFINER
VIEW vw_semi_annual_cluster_summary AS
SELECT
sp.cluster_pk,
sp.cluster_code,
sp.cluster_name,
sp.timeline_year,
sp.semi_annual_label,
COUNT(\*) AS total_indicators,
ROUND(AVG(sp.achievement_percent),2) AS average_achievement,
SUM(CASE WHEN sp.achievement_percent < 10 THEN 1 ELSE 0 END) AS needs_attention,
SUM(CASE WHEN sp.achievement_percent >= 10 AND sp.achievement_percent < 50 THEN 1 ELSE 0 END) AS progressing,
SUM(CASE WHEN sp.achievement_percent >= 50 AND sp.achievement_percent < 90 THEN 1 ELSE 0 END) AS on_track,
SUM(CASE WHEN sp.achievement_percent >= 90 THEN 1 ELSE 0 END) AS met
FROM vw_semi_annual_performance sp
GROUP BY
sp.cluster_pk,
sp.cluster_code,
sp.cluster_name,
sp.timeline_year,
sp.semi_annual_label
ORDER BY sp.timeline_year, sp.semi_annual_label, sp.cluster_name;
