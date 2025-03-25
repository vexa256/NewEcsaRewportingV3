-- Drop the existing view first
DROP VIEW IF EXISTS `vw_performance_over_time_by_quarter`;

-- Create the refactored view with fixed target handling
CREATE VIEW `vw_performance_over_time_by_quarter` AS
SELECT
-- Cluster information
c.id AS cluster_pk,
c.ClusterID AS cluster_code,
c.Cluster_Name AS cluster_name,

    -- Indicator information
    pi.id AS indicator_pk,
    pi.Indicator_Number AS indicator_number,
    pi.Indicator_Name AS indicator_name,
    pi.ResponseType AS indicator_response_type,

    -- Timeline information
    t.id AS timeline_pk,
    t.ReportName AS timeline_name,
    t.Year AS timeline_year,
    t.Quarter AS timeline_quarter,
    t.ClosingDate AS timeline_closing_date,
    t.status AS timeline_status,

    -- Target information
    cit.id AS cluster_target_pk,
    cit.Target_Year AS target_year_string,
    cit.Target_Value AS target_value_raw,

    -- Actual value calculation with proper type handling
    CASE
        WHEN pi.ResponseType = 'Number' THEN
            CAST(SUM(CASE
                WHEN cpm.ResponseType = 'Number' AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                THEN CAST(cpm.Response AS DECIMAL(20,4))
                ELSE 0
            END) AS DECIMAL(20,4))

        WHEN pi.ResponseType IN ('Yes/No', 'Boolean') THEN
            CASE
                WHEN SUM(CASE
                    WHEN (cit.Target_Value IN ('Yes', 'True') AND cpm.Response IN ('Yes', 'True'))
                    OR (cit.Target_Value IN ('No', 'False') AND cpm.Response IN ('No', 'False'))
                    THEN 1
                    ELSE 0
                END) > 0
                THEN 1
                ELSE 0
            END

        ELSE 0
    END AS total_actual_value,

    -- Target value normalization - adjusted to handle multi-year targets properly
    CASE
        -- For numeric responses, use the target value directly
        WHEN pi.ResponseType = 'Number' AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$' THEN
            -- If it's a multi-year target, divide by the number of quarters in the range
            CASE
                WHEN cit.Target_Year LIKE '%-%' THEN
                    CAST(cit.Target_Value AS DECIMAL(20,4)) /
                    (
                        (CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED) -
                         CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED) + 1) * 4
                    )
                ELSE
                    -- For single year targets, divide by 4 quarters
                    CAST(cit.Target_Value AS DECIMAL(20,4)) / 4
            END

        -- For Boolean/Yes/No responses, keep as 1
        WHEN pi.ResponseType IN ('Yes/No', 'Boolean') AND cit.Target_Value IN ('Yes', 'True', 'No', 'False')
        THEN 1

        ELSE 0
    END AS total_target_value,

    -- Original target value (undivided) for reference
    CASE
        WHEN pi.ResponseType = 'Number' AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
        THEN CAST(cit.Target_Value AS DECIMAL(20,4))

        WHEN pi.ResponseType IN ('Yes/No', 'Boolean') AND cit.Target_Value IN ('Yes', 'True', 'No', 'False')
        THEN 1

        ELSE 0
    END AS original_target_value,

    -- Achievement percentage calculation with proper guard against division by zero
    CASE
        WHEN (CASE
            WHEN pi.ResponseType = 'Number' AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
            THEN
                CASE
                    WHEN cit.Target_Year LIKE '%-%' THEN
                        CAST(cit.Target_Value AS DECIMAL(20,4)) /
                        (
                            (CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED) -
                             CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED) + 1) * 4
                        )
                    ELSE
                        CAST(cit.Target_Value AS DECIMAL(20,4)) / 4
                END
            WHEN pi.ResponseType IN ('Yes/No', 'Boolean') AND cit.Target_Value IN ('Yes', 'True', 'No', 'False')
            THEN 1
            ELSE 0
        END) <= 0
        THEN 0
        ELSE
            LEAST(100, GREATEST(0, (
                CASE
                    WHEN pi.ResponseType = 'Number' THEN
                        CAST(SUM(CASE
                            WHEN cpm.ResponseType = 'Number' AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                            THEN cpm.Response
                            ELSE '0'
                        END) AS DECIMAL(20,4))

                    WHEN pi.ResponseType IN ('Yes/No', 'Boolean') THEN
                        CASE
                            WHEN SUM(CASE
                                WHEN (cit.Target_Value IN ('Yes', 'True') AND cpm.Response IN ('Yes', 'True'))
                                OR (cit.Target_Value IN ('No', 'False') AND cpm.Response IN ('No', 'False'))
                                THEN 1
                                ELSE 0
                            END) > 0
                            THEN 1
                            ELSE 0
                        END

                    ELSE 0
                END
                /
                CASE
                    WHEN pi.ResponseType = 'Number' AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                    THEN
                        CASE
                            WHEN cit.Target_Year LIKE '%-%' THEN
                                CAST(cit.Target_Value AS DECIMAL(20,4)) /
                                (
                                    (CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED) -
                                     CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED) + 1) * 4
                                )
                            ELSE
                                CAST(cit.Target_Value AS DECIMAL(20,4)) / 4
                        END
                    WHEN pi.ResponseType IN ('Yes/No', 'Boolean') AND cit.Target_Value IN ('Yes', 'True', 'No', 'False')
                    THEN 1
                    ELSE 0
                END
            ) * 100))
    END AS achievement_percent,

    -- Status label based on achievement percentage
    CASE
        WHEN (CASE
            WHEN pi.ResponseType = 'Number' AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
            THEN LEAST(100, GREATEST(0, (
                CAST(SUM(CASE
                    WHEN cpm.ResponseType = 'Number' AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                    THEN cpm.Response
                    ELSE '0'
                END) AS DECIMAL(20,4))
                /
                CASE
                    WHEN cit.Target_Year LIKE '%-%' THEN
                        CAST(cit.Target_Value AS DECIMAL(20,4)) /
                        (
                            (CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED) -
                             CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED) + 1) * 4
                        )
                    ELSE
                        CAST(cit.Target_Value AS DECIMAL(20,4)) / 4
                END
            ) * 100))

            WHEN pi.ResponseType IN ('Yes/No', 'Boolean') AND cit.Target_Value IN ('Yes', 'True', 'No', 'False')
            THEN
                CASE
                    WHEN SUM(CASE
                        WHEN (cit.Target_Value IN ('Yes', 'True') AND cpm.Response IN ('Yes', 'True'))
                        OR (cit.Target_Value IN ('No', 'False') AND cpm.Response IN ('No', 'False'))
                        THEN 1
                        ELSE 0
                    END) > 0
                    THEN 100
                    ELSE 0
                END

            ELSE 0
        END) < 10 THEN 'Needs Attention'

        WHEN (CASE
            WHEN pi.ResponseType = 'Number' AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
            THEN LEAST(100, GREATEST(0, (
                CAST(SUM(CASE
                    WHEN cpm.ResponseType = 'Number' AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                    THEN cpm.Response
                    ELSE '0'
                END) AS DECIMAL(20,4))
                /
                CASE
                    WHEN cit.Target_Year LIKE '%-%' THEN
                        CAST(cit.Target_Value AS DECIMAL(20,4)) /
                        (
                            (CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED) -
                             CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED) + 1) * 4
                        )
                    ELSE
                        CAST(cit.Target_Value AS DECIMAL(20,4)) / 4
                END
            ) * 100))

            WHEN pi.ResponseType IN ('Yes/No', 'Boolean') AND cit.Target_Value IN ('Yes', 'True', 'No', 'False')
            THEN
                CASE
                    WHEN SUM(CASE
                        WHEN (cit.Target_Value IN ('Yes', 'True') AND cpm.Response IN ('Yes', 'True'))
                        OR (cit.Target_Value IN ('No', 'False') AND cpm.Response IN ('No', 'False'))
                        THEN 1
                        ELSE 0
                    END) > 0
                    THEN 100
                    ELSE 0
                END

            ELSE 0
        END) < 50 THEN 'In Progress'

        WHEN (CASE
            WHEN pi.ResponseType = 'Number' AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
            THEN LEAST(100, GREATEST(0, (
                CAST(SUM(CASE
                    WHEN cpm.ResponseType = 'Number' AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                    THEN cpm.Response
                    ELSE '0'
                END) AS DECIMAL(20,4))
                /
                CASE
                    WHEN cit.Target_Year LIKE '%-%' THEN
                        CAST(cit.Target_Value AS DECIMAL(20,4)) /
                        (
                            (CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED) -
                             CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED) + 1) * 4
                        )
                    ELSE
                        CAST(cit.Target_Value AS DECIMAL(20,4)) / 4
                END
            ) * 100))

            WHEN pi.ResponseType IN ('Yes/No', 'Boolean') AND cit.Target_Value IN ('Yes', 'True', 'No', 'False')
            THEN
                CASE
                    WHEN SUM(CASE
                        WHEN (cit.Target_Value IN ('Yes', 'True') AND cpm.Response IN ('Yes', 'True'))
                        OR (cit.Target_Value IN ('No', 'False') AND cpm.Response IN ('No', 'False'))
                        THEN 1
                        ELSE 0
                    END) > 0
                    THEN 100
                    ELSE 0
                END

            ELSE 0
        END) < 90 THEN 'On Track'

        ELSE 'Met'
    END AS status_label,

    -- Over-achievement comment
    CASE
        WHEN (
            CASE
                WHEN pi.ResponseType = 'Number'
                THEN CAST(SUM(CASE
                    WHEN cpm.ResponseType = 'Number' AND cpm.Response REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                    THEN cpm.Response
                    ELSE '0'
                END) AS DECIMAL(20,4))

                WHEN pi.ResponseType IN ('Yes/No', 'Boolean')
                THEN
                    CASE
                        WHEN SUM(CASE
                            WHEN (cit.Target_Value IN ('Yes', 'True') AND cpm.Response IN ('Yes', 'True'))
                            OR (cit.Target_Value IN ('No', 'False') AND cpm.Response IN ('No', 'False'))
                            THEN 1
                            ELSE 0
                        END) > 0
                        THEN 1
                        ELSE 0
                    END

                ELSE 0
            END
        ) > (
            CASE
                WHEN pi.ResponseType = 'Number' AND cit.Target_Value REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                THEN
                    CASE
                        WHEN cit.Target_Year LIKE '%-%' THEN
                            CAST(cit.Target_Value AS DECIMAL(20,4)) /
                            (
                                (CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED) -
                                 CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED) + 1) * 4
                            )
                        ELSE
                            CAST(cit.Target_Value AS DECIMAL(20,4)) / 4
                    END

                WHEN pi.ResponseType IN ('Yes/No', 'Boolean') AND cit.Target_Value IN ('Yes', 'True', 'No', 'False')
                THEN 1

                ELSE 0
            END
        )
        THEN 'Over Achieved'
        ELSE ''
    END AS comment

FROM cluster_indicator_targets cit
JOIN performance_indicators pi ON
pi.id = CAST(cit.IndicatorID AS UNSIGNED) AND
pi.id <> 0 AND
JSON_CONTAINS(pi.Responsible_Cluster, JSON_QUOTE(cit.ClusterID)) = 1
JOIN clusters c ON c.ClusterID = cit.ClusterID
JOIN ecsahc_timelines t ON
t.status IN ('In Progress', 'Completed') AND
(
(cit.Target_Year LIKE '%-%' AND t.Year BETWEEN
CAST(SUBSTRING_INDEX(cit.Target_Year, '-', 1) AS UNSIGNED) AND
CAST(SUBSTRING_INDEX(cit.Target_Year, '-', -1) AS UNSIGNED)
) OR
(NOT(cit.Target_Year LIKE '%-%') AND t.Year = CAST(cit.Target_Year AS UNSIGNED))
)
LEFT JOIN cluster_performance_mappings cpm ON
cpm.ClusterID = cit.ClusterID AND
cpm.IndicatorID = cit.IndicatorID AND
cpm.ReportingID = t.ReportingID
GROUP BY
c.id, c.ClusterID, c.Cluster_Name,
pi.id, pi.Indicator_Number, pi.Indicator_Name, pi.ResponseType,
t.id, t.ReportName, t.Year, t.Quarter, t.ClosingDate, t.status,
cit.id, cit.Target_Year, cit.Target_Value;
