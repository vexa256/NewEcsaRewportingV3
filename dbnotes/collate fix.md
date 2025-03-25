-- First, set the database default collation
USE NewEcsaRewportingV3;
ALTER DATABASE NewEcsaRewportingV3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create a temporary procedure to handle the conversion
DELIMITER //
CREATE PROCEDURE convert_all_tables()
BEGIN
DECLARE done INT DEFAULT FALSE;
DECLARE current_table VARCHAR(255);
DECLARE tables_cursor CURSOR FOR
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'NewEcsaRewportingV3'
AND table_type = 'BASE TABLE';
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- Create a log table to track progress
    CREATE TABLE IF NOT EXISTS collation_conversion_log (
        table_name VARCHAR(255),
        conversion_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(50)
    );

    OPEN tables_cursor;
    table_loop: LOOP
        FETCH tables_cursor INTO current_table;
        IF done THEN
            LEAVE table_loop;
        END IF;

        -- Log that we're starting this table
        INSERT INTO collation_conversion_log (table_name, status)
        VALUES (current_table, 'STARTING');

        -- Convert the table
        SET @sql = CONCAT('ALTER TABLE `', current_table, '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;

        -- Log completion
        INSERT INTO collation_conversion_log (table_name, status)
        VALUES (current_table, 'COMPLETED');

        -- Output progress (visible in console clients)
        SELECT CONCAT('Converted table: ', current_table) AS progress;
    END LOOP;

    CLOSE tables_cursor;

    SELECT 'All tables converted successfully' AS result;

END //
DELIMITER ;

-- Execute the procedure
CALL convert_all_tables();

-- Check the log to verify all conversions
SELECT \* FROM collation_conversion_log ORDER BY conversion_time;

-- Clean up
DROP PROCEDURE convert_all_tables;
-- Keep the log table for reference, or drop it if not needed
-- DROP TABLE collation_conversion_log;
