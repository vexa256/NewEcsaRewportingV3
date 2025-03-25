USE NewEcsaRewportingV3;

CREATE OR REPLACE VIEW vw_cluster_completeness_summary AS
SELECT
    /* ============ CLUSTER INFO ============ */
    c.id               AS cluster_pk,
    c.ClusterID        AS cluster_text_identifier,
    c.Cluster_Name     AS cluster_name,
    c.Description      AS cluster_description,

    /* ============ TIMELINE INFO ============ */
    t.id               AS timeline_pk,
    t.ReportName       AS timeline_name,
    t.Type             AS timeline_type,
    t.Description      AS timeline_description,
    t.ReportingID      AS timeline_reporting_id,
    t.Year             AS timeline_year,
    t.Quarter          AS timeline_quarter,
    t.ClosingDate      AS timeline_closing_date,
    t.status           AS timeline_status,

    /* ============ AGGREGATES ============ */
    COUNT(*) AS total_indicators,
    SUM(
      CASE 
        WHEN cpm.id IS NOT NULL THEN 1 
        ELSE 0 
      END
    ) AS reported_indicators,
    SUM(
      CASE 
        WHEN cpm.id IS NULL THEN 1 
        ELSE 0 
      END
    ) AS not_reported_indicators,

    /*
       completeness_percentage = ( (#reported / #total) * 100 ),
       default to 0 if no required indicators.
    */
    CASE
      WHEN COUNT(*) = 0 THEN 0
      ELSE CAST(
            100.0 *
            SUM(
              CASE
                WHEN cpm.id IS NOT NULL THEN 1
                ELSE 0
              END
            )
            / COUNT(*) 
            AS DECIMAL(5,2)
           )
    END AS completeness_percentage

FROM clusters c
JOIN cluster_indicator_targets cit
    ON c.ClusterID = cit.ClusterID

/* 
   Only include valid indicators by joining to performance_indicators pi:
   1) pi.id = cit.IndicatorID AND pi.id <> 0
   2) 'c.ClusterID' must exist in pi.Responsible_Cluster JSON array
*/
JOIN performance_indicators pi
    ON pi.id = CAST(cit.IndicatorID AS UNSIGNED)
   AND pi.id <> 0
   AND JSON_CONTAINS(
         pi.Responsible_Cluster, 
         JSON_QUOTE(c.ClusterID)
       ) = 1

/*
   Next, join to ecsahc_timelines but ONLY those 
   - with status in ('In Progress','Completed') 
   - whose numeric 'Year' matches the target year or is inside the target range.
*/
JOIN ecsahc_timelines t
    ON t.status IN ('In Progress','Completed')
   AND (
        (
          cit.Target_Year LIKE '%-%'
          /* example: '2024-2025' => parse '2024' and '2025' */
          AND t.Year BETWEEN 
               CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED)
               AND CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED)
        )
        OR
        (
          cit.Target_Year NOT LIKE '%-%'
          /* single year, e.g. '2024' => t.Year must match exactly */
          AND t.Year = CAST(cit.Target_Year AS UNSIGNED)
        )
    )

/*
   LEFT JOIN cluster_performance_mappings: check if a performance row 
   exists for each (cluster, indicator, timeline).
*/
LEFT JOIN cluster_performance_mappings cpm
    ON cpm.ClusterID   = c.ClusterID
   AND cpm.IndicatorID = cit.IndicatorID
   AND cpm.ReportingID = t.ReportingID

GROUP BY
    c.id,
    c.ClusterID,
    c.Cluster_Name,
    c.Description,
    t.id,
    t.ReportName,
    t.Type,
    t.Description,
    t.ReportingID,
    t.Year,
    t.Quarter,
    t.ClosingDate,
    t.status;
