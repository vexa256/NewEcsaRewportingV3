CREATE VIEW vw*semi_annual_performance AS
SELECT
base.cluster_pk,
base.cluster_code,
base.cluster_name,
base.so_pk,
base.so_number,
base.so_name,
base.indicator_pk,
base.indicator_number,
base.indicator_name,
base.indicator_response_type,
base.year_val AS timeline_year,
CASE
WHEN base.semi_annual_half = 1 THEN 'First Semi Annual'
ELSE 'Second Semi Annual'
END AS semi_annual_label,
base.total_actual_value AS raw_actual_value,
base.total_target_value AS raw_target_value,
CASE
WHEN base.total_target_value <= 0 THEN 0
ELSE LEAST(100, GREATEST(0, (base.total_actual_value / base.total_target_value) * 100))
END AS achievement*percent,
CASE
WHEN base.total_target_value <= 0 THEN 'No Valid Target'
ELSE
CASE
WHEN ((base.total_actual_value / base.total_target_value) * 100) < 10 THEN 'Needs Attention'
WHEN ((base.total*actual_value / base.total_target_value) * 100) < 50 THEN 'In Progress'
WHEN ((base.total*actual_value / base.total_target_value) * 100) < 90 THEN 'On Track'
ELSE 'Met'
END
END AS status_label,
CASE
WHEN base.total_actual_value > base.total_target_value THEN 'Over Achieved'
ELSE ''
END AS comment
FROM (
SELECT
c.id AS cluster_pk,
c.ClusterID AS cluster_code,
c.Cluster_Name AS cluster_name,
pi.id AS indicator_pk,
pi.Indicator_Number AS indicator_number,
pi.Indicator_Name AS indicator_name,
pi.ResponseType AS indicator_response_type,
so.id AS so_pk,
so.SO_Number AS so_number,
so.SO_Name AS so_name,
t.Year AS year_val,
CASE WHEN t.Quarter IN (1, 2) THEN 1 ELSE 2 END AS semi_annual_half,
CASE
WHEN pi.ResponseType = 'Number' THEN
CAST(SUM(
CASE
WHEN cpm.ResponseType = 'Number' AND REGEXP_LIKE(cpm.Response, '^-?[0-9]+(\\.[0-9]+)?$') 
                        THEN CAST(cpm.Response AS DECIMAL(20,4)) 
                        ELSE 0 
                    END
                ) AS DECIMAL(20,4))
            WHEN pi.ResponseType IN ('Yes/No', 'Boolean') THEN 
                CASE 
                    WHEN SUM(
                        CASE 
                            WHEN ((cit.Target_Value IN ('Yes', 'True') AND cpm.Response IN ('Yes', 'True')) 
                                OR (cit.Target_Value IN ('No', 'False') AND cpm.Response IN ('No', 'False'))) 
                            THEN 1 
                            ELSE 0 
                        END
                    ) > 0 THEN 1 
                    ELSE 0 
                END
            ELSE 0 
        END AS total_actual_value,
        CASE 
            WHEN pi.ResponseType = 'Number' AND REGEXP_LIKE(cit.Target_Value, '^-?[0-9]+(\\.[0-9]+)?$')
THEN CAST(MAX(CAST(cit.Target_Value AS DECIMAL(20,4))) AS DECIMAL(20,4))
WHEN pi.ResponseType IN ('Yes/No', 'Boolean') AND cit.Target_Value IN ('Yes', 'True', 'No', 'False')
THEN 1
ELSE 0
END AS total_target_value
FROM
cluster_indicator_targets cit
JOIN performance_indicators pi ON (
pi.id = CAST(cit.IndicatorID AS UNSIGNED)
AND pi.id <> 0
AND JSON_CONTAINS(pi.Responsible_Cluster, JSON_QUOTE(cit.ClusterID)) = 1
)
LEFT JOIN strategic_objectives so ON so.SO_Number = pi.SO_ID
JOIN clusters c ON c.ClusterID = cit.ClusterID
JOIN ecsahc_timelines t ON (
t.status IN ('In Progress', 'Completed')
AND (
(cit.Target_Year LIKE '%-%'
AND t.Year BETWEEN CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED)
AND CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED)
)
OR (
NOT(cit.Target_Year LIKE '%-%')
AND t.Year = CAST(cit.Target_Year AS UNSIGNED)
)
)
)
LEFT JOIN cluster_performance_mappings cpm ON (
cpm.ClusterID = cit.ClusterID
AND cpm.IndicatorID = cit.IndicatorID
AND cpm.ReportingID = t.ReportingID
)
GROUP BY
c.id, c.ClusterID, c.Cluster_Name,
pi.id, pi.Indicator_Number, pi.Indicator_Name, pi.ResponseType,
so.id, so.SO_Number, so.SO_Name,
t.Year,
CASE WHEN t.Quarter IN (1, 2) THEN 1 ELSE 2 END
) AS base;
