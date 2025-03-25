DROP VIEW IF EXISTS vw_semi_annual_trend_analysis;
CREATE ALGORITHM=UNDEFINED
DEFINER=`root`@`localhost`
SQL SECURITY DEFINER
VIEW vw_semi_annual_trend_analysis AS
SELECT
a.cluster_pk,
a.cluster_code,
a.cluster_name,
a.timeline_year,
a.average_achievement AS first_half_score,
b.average_achievement AS second_half_score,
ROUND(b.average_achievement - a.average_achievement,2) AS score_change,
ROUND(100.0 \* (b.average_achievement - a.average_achievement) / NULLIF(a.average_achievement,0),2) AS percent_change
FROM vw_semi_annual_cluster_summary a
JOIN vw_semi_annual_cluster_summary b
ON a.cluster_pk = b.cluster_pk
AND a.timeline_year = b.timeline_year
AND a.semi_annual_label = 'First Semi Annual'
AND b.semi_annual_label = 'Second Semi Annual'
ORDER BY a.cluster_name;
