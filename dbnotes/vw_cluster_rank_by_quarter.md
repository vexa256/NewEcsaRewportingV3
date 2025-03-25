DROP VIEW IF EXISTS `vw_cluster_rank_by_quarter`;

CREATE ALGORITHM=UNDEFINED
DEFINER=`root`@`localhost`
SQL SECURITY DEFINER
VIEW `vw_cluster_rank_by_quarter` AS
/\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***
Outer Query:

-   Groups results by cluster, year, and quarter.
-   Computes a quarter label (e.g., 'First Quarter' for Q1).
-   Ranks clusters within each (year, quarter) group using a window function.
-   Assigns a status label and an over-achieved comment. \***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***/
    SELECT
    sums.cluster_pk,
    sums.cluster_code,
    sums.cluster_name,
    sums.year_val AS timeline_year,
    CASE
    WHEN sums.quarter_val = 1 THEN 'First Quarter'
    WHEN sums.quarter_val = 2 THEN 'Second Quarter'
    WHEN sums.quarter_val = 3 THEN 'Third Quarter'
    WHEN sums.quarter_val = 4 THEN 'Fourth Quarter'
    ELSE CONCAT('Q', sums.quarter_val)
    END AS quarter_label,
    sums.final_score_percent,
    DENSE_RANK() OVER (
    PARTITION BY sums.year_val, sums.quarter_val
    ORDER BY sums.final_score_percent DESC
    ) AS cluster_rank,
    CASE
    WHEN sums.final_score_percent < 10 THEN 'Needs Attention'
    WHEN sums.final_score_percent < 50 THEN 'Progressing'
    WHEN sums.final_score_percent < 90 THEN 'On Track'
    ELSE 'Met'
    END AS status_label,
    CASE
    WHEN sums.sum_target_val > 0
    AND sums.sum_actual_val > sums.sum_target_val
    THEN 'Over Achieved'
    ELSE ''
    END AS comment
    FROM (
    /\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***
    Subquery "sums":
    -   Aggregates cluster performance for each (year, quarter).
    -   Sums actual and target values (for overachieved detection).
    -   Averages the indicator fraction (0..1) and multiplies by 100 to get the final score. \***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***\*\*\*\*\***/
        SELECT
        frac.cluster\*pk,
        frac.cluster_code,
        frac.cluster_name,
        frac.year_val,
        frac.quarter_val,
        SUM(frac.sum_actual_val) AS sum_actual_val,
        SUM(frac.sum_target_val) AS sum_target_val,
        CAST(AVG(frac.indicator_fraction) \* 100 AS DECIMAL(6,2)) AS final_score_percent
        FROM (
        /**\*\***\*\***\*\***\*\***\*\***\*\***\*\***\*\*\***\*\***\*\***\*\***\*\***\*\***\*\***\*\***
        Deep Subquery "frac": - For each cluster, year, quarter, and indicator, compute:
        -   sum\*actual_val: The sum of numeric responses (or 1/0 for yes-no).
        -   sum*target_val: The target value (numeric or 1 for yes-no). \* indicator_fraction: The ratio of actual to target, capped between 0 and 1. - Uses our established logic joining clusters, indicators, targets, timelines,
            and performance mappings.
            **\*\***\*\***\*\***\*\***\*\***\*\***\*\***\*\*\***\*\***\*\***\*\***\*\***\*\***\*\***\*\***/
            SELECT
            c.id AS cluster_pk,
            c.ClusterID AS cluster_code,
            c.Cluster_Name AS cluster_name,
            t.Year AS year_val,
            t.Quarter AS quarter_val,
            /* Sum actual value _/
            CASE
            WHEN pi.ResponseType = 'Number' THEN
            CAST(
            SUM(
            CASE
            WHEN cpm.ResponseType = 'Number'
            AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                THEN CAST(cpm.Response AS DECIMAL(20,4))
                ELSE 0
              END
            ) AS DECIMAL(20,4)
          )
        WHEN pi.ResponseType IN ('Yes/No','Boolean') THEN
          CASE
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
      /* Get the target value */
      CASE
        WHEN pi.ResponseType = 'Number'
             AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
            THEN CAST(cit.Target_Value AS DECIMAL(20,4))
            WHEN pi.ResponseType IN ('Yes/No','Boolean')
            AND cit.Target_Value IN ('Yes','True','No','False')
            THEN 1
            ELSE 0
            END AS sum_target_val,
            /_ Compute the fraction (actual/target) capped to [0,1]. \_/
            CASE
            WHEN pi.ResponseType = 'Number' THEN
            CASE
            WHEN cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                 AND CAST(cit.Target_Value AS DECIMAL(20,4)) > 0
            THEN LEAST(
                   1,
                   GREATEST(
                     0,
                     (
                       CAST(
                         SUM(
                           CASE
                             WHEN cpm.ResponseType = 'Number'
                                  AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
            THEN CAST(cpm.Response AS DECIMAL(20,4))
            ELSE 0
            END
            ) AS DECIMAL(20,4)
            )
            /
            CAST(cit.Target_Value AS DECIMAL(20,4))
            )
            )
            )
            ELSE 0
            END
            WHEN pi.ResponseType IN ('Yes/No','Boolean') THEN
            CASE
            WHEN cit.Target_Value IN ('Yes','True','No','False')
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
            END
            ELSE
            NULL
            END AS indicator_fraction
            FROM
            cluster_indicator_targets AS cit
            JOIN performance_indicators AS pi
            ON pi.id = CAST(cit.IndicatorID AS UNSIGNED)
            AND pi.id <> 0
            AND JSON_CONTAINS(pi.Responsible_Cluster, JSON_QUOTE(cit.ClusterID)) = 1
            JOIN clusters AS c
            ON c.ClusterID = cit.ClusterID
            JOIN ecsahc_timelines AS t
            ON t.status IN ('In Progress','Completed')
            AND (
            (
            cit.Target_Year LIKE '%-%'
            AND t.Year BETWEEN
            CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED)
            AND
            CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED)
            )
            OR (
            NOT(cit.Target_Year LIKE '%-%')
            AND t.Year = CAST(cit.Target_Year AS UNSIGNED)
            )
            )
            LEFT JOIN cluster_performance_mappings AS cpm
            ON cpm.ClusterID = cit.ClusterID
            AND cpm.IndicatorID = cit.IndicatorID
            AND cpm.ReportingID = t.ReportingID
            GROUP BY
            c.id, c.ClusterID, c.Cluster_Name,
            t.Year, t.Quarter,
            pi.id, pi.ResponseType,
            cit.Target_Value
            ) AS frac
            GROUP BY
            frac.cluster_pk,
            frac.cluster_code,
            frac.cluster_name,
            frac.year_val,
            frac.quarter_val
            ) AS sums
            ORDER BY
            sums.year_val,
            sums.quarter_val,
            sums.final_score_percent DESC;
