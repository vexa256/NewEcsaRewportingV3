DROP VIEW IF EXISTS `vw_cluster_rank_annual`;

CREATE ALGORITHM=UNDEFINED
DEFINER=`root`@`localhost`
SQL SECURITY DEFINER
VIEW `vw_cluster_rank_annual` AS
SELECT
agg.cluster_pk,
agg.cluster_code,
agg.cluster_name,
agg.year_val AS timeline_year,
agg.final_score_percent,
DENSE_RANK() OVER (
PARTITION BY agg.year_val
ORDER BY agg.final_score_percent DESC
) AS cluster_rank,
CASE
WHEN agg.final_score_percent < 10 THEN 'Needs Attention'
WHEN agg.final_score_percent < 50 THEN 'Progressing'
WHEN agg.final_score_percent < 90 THEN 'On Track'
ELSE 'Met'
END AS status_label,
CASE
WHEN agg.sum_target_val > 0
AND agg.sum_actual_val > agg.sum_target_val THEN 'Over Achieved'
ELSE ''
END AS comment,
agg.reporting_periods
FROM (
SELECT
frac.cluster_pk,
frac.cluster_code,
frac.cluster_name,
frac.year_val,
GROUP_CONCAT(DISTINCT frac.timeline_name ORDER BY frac.timeline_quarter SEPARATOR ', ') AS reporting_periods,
SUM(frac.sum_actual_val) AS sum_actual_val,
SUM(frac.sum_target_val) AS sum_target_val,
CAST(AVG(frac.indicator_fraction) \* 100 AS DECIMAL(6,2)) AS final_score_percent
FROM (
SELECT
c.id AS cluster_pk,
c.ClusterID AS cluster_code,
c.Cluster_Name AS cluster_name,
t.Year AS year_val,
t.Quarter AS timeline_quarter,
t.ReportName AS timeline_name,
-- Actual reported value for the indicator in this period:
CASE
WHEN pi.ResponseType = 'Number'
THEN CAST(SUM(
CASE
WHEN cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$' 
                THEN CAST(cpm.Response AS DECIMAL(20,4)) 
              ELSE 0 
            END
          ) AS DECIMAL(20,4))
        WHEN pi.ResponseType IN ('Yes/No','Boolean')
          THEN CASE 
                 WHEN SUM(
                   CASE 
                     WHEN ((cit.Target_Value IN ('Yes','True')) AND (cpm.Response IN ('Yes','True')))
                       OR ((cit.Target_Value IN ('No','False')) AND (cpm.Response IN ('No','False')))
                     THEN 1 
                     ELSE 0 
                   END
                 ) > 0 THEN 1 
                 ELSE 0 
               END
        ELSE 0
      END AS sum_actual_val,
      -- Target value for the indicator:
      CASE 
        WHEN pi.ResponseType = 'Number'
             AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
THEN CAST(cit.Target_Value AS DECIMAL(20,4))
WHEN pi.ResponseType IN ('Yes/No','Boolean')
AND cit.Target_Value IN ('Yes','True','No','False')
THEN 1
ELSE 0
END AS sum_target_val,
-- The “indicator fraction” (ranging 0 to 1) for the reporting period:
CASE
WHEN pi.ResponseType = 'Number'
THEN CASE
WHEN cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                      AND CAST(cit.Target_Value AS DECIMAL(20,4)) > 0
                   THEN LEAST(1, GREATEST(0, 
                        CAST(SUM(
                          CASE 
                            WHEN cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
THEN CAST(cpm.Response AS DECIMAL(20,4))
ELSE 0
END
) AS DECIMAL(20,4)) / CAST(cit.Target_Value AS DECIMAL(20,4))
))
ELSE 0
END
WHEN pi.ResponseType IN ('Yes/No','Boolean')
THEN CASE
WHEN cit.Target_Value IN ('Yes','True','No','False')
THEN CASE
WHEN SUM(
CASE
WHEN ((cit.Target_Value IN ('Yes','True')) AND (cpm.Response IN ('Yes','True')))
OR ((cit.Target_Value IN ('No','False')) AND (cpm.Response IN ('No','False')))
THEN 1 ELSE 0
END
) > 0 THEN 1
ELSE 0
END
ELSE 0
END
ELSE NULL
END AS indicator_fraction
FROM cluster_indicator_targets cit
JOIN performance_indicators pi
ON pi.id = CAST(cit.IndicatorID AS UNSIGNED)
AND pi.id <> 0
AND JSON_CONTAINS(pi.Responsible_Cluster, JSON_QUOTE(cit.ClusterID)) = 1
JOIN clusters c
ON c.ClusterID = cit.ClusterID
JOIN ecsahc_timelines t
ON t.status IN ('In Progress','Completed')
AND (
((cit.Target_Year LIKE '%-%')
AND t.Year BETWEEN CAST(SUBSTRING_INDEX(cit.Target_Year,'-',1) AS UNSIGNED)
AND CAST(SUBSTRING_INDEX(cit.Target_Year,'-',-1) AS UNSIGNED))
OR ((NOT(cit.Target_Year LIKE '%-%'))
AND t.Year = CAST(cit.Target_Year AS UNSIGNED))
)
LEFT JOIN cluster_performance_mappings cpm
ON cpm.ClusterID = cit.ClusterID
AND cpm.IndicatorID = cit.IndicatorID
AND cpm.ReportingID = t.ReportingID
GROUP BY c.id, c.ClusterID, c.Cluster_Name, t.Year, t.Quarter, pi.id, pi.ResponseType, cit.Target_Value
) AS frac
GROUP BY frac.cluster_pk, frac.cluster_code, frac.cluster_name, frac.year_val
) AS agg
GROUP BY agg.cluster_pk, agg.year_val;
