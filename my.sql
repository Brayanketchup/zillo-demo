CALL 2024F_martbray.pStudent_report('');


CALL 2024F_martbray.pStudent_report('1004');
CALL 2024F_martbray.pStudent_report('ALL');


-- CREATE PROCEDURE 2024F_martbray.pStudent_report(IN student_id VARCHAR(10));


drop PROCEDURE 2024F_martbray.pStudent_report;

CALL 2024F_martbray.pStudent_report('ALL');

DELIMITER $$

CREATE PROCEDURE 2024F_martbray.pStudent_report(IN student_id VARCHAR(10))
BEGIN
    DECLARE finished INTEGER DEFAULT 0;
    DECLARE s_sid INT;
    DECLARE cur CURSOR FOR
        SELECT sid FROM dreamhome.Students
        WHERE student_id = 'All' OR sid = student_id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

    DROP TEMPORARY TABLE IF EXISTS temp_student_report;
    CREATE TEMPORARY TABLE temp_student_report (
        year INT,
        semester VARCHAR(10),
        cid INT,
        course_name VARCHAR(100),
        credits INT,
        grade CHAR(2),
        expense_per_course DECIMAL(10,2)
    );

    OPEN cur;

    student_loop: LOOP
        FETCH cur INTO s_sid;
        IF finished = 1 THEN
            LEAVE student_loop;
        END IF;

        INSERT INTO temp_student_report
        SELECT sc.year, sc.semester, sc.cid, c.name, c.credits, sc.grade,
               t.fee_per_credit * c.credits AS expense_per_course
        FROM dreamhome.Students_Courses sc
        INNER JOIN dreamhome.Courses c ON sc.cid = c.cid
        INNER JOIN dreamhome.Tuitions t ON sc.year = t.year
        WHERE sc.sid = s_sid
        ORDER BY sc.year, FIELD(sc.semester, 'Spring', 'Summer', 'Fall', 'Winter');

    END LOOP;

    CLOSE cur;

    -- Output the student reports
    SELECT * FROM temp_student_report;

    -- Display summary information for each student
    SELECT s.sid AS student_sid, CONCAT(s.first_name, ' ', s.last_name) AS student_name,
           SUM(tr.credits) AS total_credits,
           AVG(CASE tr.grade
               WHEN 'A' THEN 4.0
               WHEN 'B' THEN 3.0
               WHEN 'C' THEN 2.0
               WHEN 'D' THEN 1.0
               WHEN 'F' THEN 0.0
               ELSE 0
           END) AS averaged_GPA,
           SUM(tr.expense_per_course) AS total_expense
    FROM temp_student_report tr
    JOIN dreamhome.Students s ON s.sid = tr.sid
    GROUP BY s.sid;

    DROP TEMPORARY TABLE IF EXISTS temp_student_report;

END$$

DELIMITER ;






select * FROM dreamhome.Students;
-- sid first_name last_name Birthday major zipcode
select * from dreamhome.Students_Courses;
-- sid, cid, year semester grade
select * FROM dreamhome.Courses;
-- cid name credits prerequisite_cid
select * from dreamhome.Tuitions;
-- year fee_per_credit




-- CALL pGenerate_report_for_student('1003');

-- Check enrolled courses for student ID 1009

select * from dreamhome.Students_Courses where sid = '1009';

-- Before calling the procedure for generating report
SELECT 'Checking courses for ', sid AS Debug, COUNT(*) AS CourseCount
FROM dreamhome.Students_Courses WHERE sid = sid
group by sid;




drop PROCEDURE 2024F_martbray.pGenerate_report_for_student;
drop PROCEDURE 2024F_martbray.pStudent_report;
drop PROCEDURE 2024F_martbray.pReport_all_students;





CALL 2024F_martbray.pStudent_report('All');

SELECT COUNT(*) FROM dreamhome.Students;

CALL 2024F_martbray.pReport_all_students();





DELIMITER $$

CREATE PROCEDURE 2024F_martbray.pReport_all_students()
BEGIN
    DECLARE finished INTEGER DEFAULT 0;
    DECLARE student_id VARCHAR(255);
    DECLARE course_count INT;
    DECLARE cur CURSOR FOR SELECT sid FROM dreamhome.Students;  -- Ensure 'sid' is the correct column name
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

    OPEN cur;

    student_loop: LOOP
        FETCH cur INTO student_id;  -- Fetch each student ID into the variable
        IF finished THEN
            LEAVE student_loop;
        END IF;

        -- Verify the student ID exists in the Students table
        SELECT COUNT(*) INTO course_count FROM dreamhome.Students WHERE sid = student_id;  -- Correct reference to 'sid'
        IF course_count = 0 THEN
            SELECT CONCAT('No student found with ID ', student_id) AS Error;
            ITERATE student_loop;  -- Skip to the next iteration
        END IF;

        -- Check if the student has any course and tuition entries
        SELECT COUNT(*) INTO course_count FROM dreamhome.Students_Courses sc
        JOIN dreamhome.Courses c ON sc.cid = c.cid
        JOIN dreamhome.Tuitions t ON t.year = sc.year
        WHERE sc.sid = student_id;  -- Ensure this matches your Students table key

        -- Debug output to check course count
        SELECT CONCAT('Student ID: ', student_id, ' has ', course_count, ' courses.') AS DebugInfo;

        -- Call report generation only if there are course records
        IF course_count > 0 THEN
            CALL pGenerate_report_for_student(student_id);
        ELSE
            SELECT CONCAT('No course data available for student ID ', student_id) AS Warning;
        END IF;

    END LOOP;

    CLOSE cur;
END$$

DELIMITER ;




DELIMITER $$

CREATE PROCEDURE 2024F_martbray.pGenerate_report_for_student(IN student_id VARCHAR(255))
BEGIN
    -- Temporary table to hold the course details and financial calculations
    CREATE TEMPORARY TABLE IF NOT EXISTS TempReport (
        year INT,
        semester VARCHAR(255),
        course_id VARCHAR(255),
        course_name VARCHAR(255),
        credits INT,
        grade VARCHAR(3),
        expense_per_course DECIMAL(10,2)
    );

    -- Populate the temporary table with necessary details
    INSERT INTO TempReport (year, semester, course_id, course_name, credits, grade, expense_per_course)
    SELECT sc.year, sc.semester, sc.cid, c.name, c.credits, sc.grade,
           c.credits * t.fee_per_credit AS expense_per_course
    FROM dreamhome.Students_Courses sc
    JOIN dreamhome.Courses c ON sc.cid = c.cid
    JOIN dreamhome.Tuitions t ON t.year = sc.year
    WHERE sc.sid = student_id
    ORDER BY sc.year, (CASE WHEN sc.semester = 'Spring' THEN 1 ELSE 2 END), sc.semester;

    -- Check if there are any entries in the report (i.e., if the student has enrolled in any courses)
    IF (SELECT COUNT(*) FROM TempReport) = 0 THEN
        SELECT 'No course data available for the student.' AS 'Report';
    ELSE
        -- Display the detailed course and financial report
        SELECT year, semester, course_id, course_name, credits, grade, expense_per_course
        FROM TempReport;

        -- Compute and display summary data
        SELECT 'Summary' AS 'Type', 
               SUM(credits) AS 'Total Credits', 
               AVG(fGrade_GPA(grade)) AS 'Average GPA', 
               SUM(expense_per_course) AS 'Total Expense'
        FROM TempReport;
    END IF;

    -- Drop the temporary table to clean up
    DROP TEMPORARY TABLE IF EXISTS TempReport;
END$$

DELIMITER ;



DELIMITER $$

CREATE PROCEDURE 2024F_martbray.pStudent_report(IN input_sid VARCHAR(255))
BEGIN
    IF input_sid IS NULL OR input_sid = '' THEN
        SELECT 'Invalid input provided.' AS Report;
    ELSEIF input_sid = 'All' THEN
        CALL pReport_all_students();
    ELSE
        CALL pGenerate_report_for_student(input_sid);
    END IF;
END$$

DELIMITER ;


-- 2024F_martbray.fGrade_GPA(
          
select * from Students;
use 2024F_martbray;


show tables;

show databases;

USE dreamhome;

select * FROM dreamhome.Students;
-- sid first_name last_name Birthday major zipcode
select * from dreamhome.Students_Courses;
-- sid, cid, year semester grade
select * FROM dreamhome.Courses;
-- cid name credits prerequisite_cid
select * from dreamhome.Tuitions;
-- year fee_per_credit


show tables;
select * from Tuitions;


use 2024F_martbray;
use dreamhome;
use dreamhome;
use CPS5740;

 -- q2
 
Select * from CPS5740.PRODUCT2;
-- product_id, name, description, vendor_id, cost, sell_price, quantity, employee_id
Select * from CPS5740.VENDOR2;
-- vendor_id, name, address, city, state, zipcode, latitude, Longitude
Select * from CPS5740.ORDERS2 ;
-- order_id, customer_id, date
Select * from CPS5740.PRODUCT_ORDER2;
-- order_id, product_id quantity





-- SET SQL_SAFE_UPDATES = 0;



-- part 2 question 8

-- Create the HW_Concurrency table
CREATE TABLE 2024F_martbray.HW_Concurrency (
    Time_id INT,
    T1 VARCHAR(255),
    T2 VARCHAR(255)
);

-- Insert SQL statements and results
INSERT INTO 2024F_martbray.HW_Concurrency (Time_id, T1, T2) VALUES (1, 'START TRANSACTION;', NULL);
INSERT INTO 2024F_martbray.HW_Concurrency (Time_id, T1, T2) VALUES (2, 'SELECT * FROM HW_test2 WHERE name = ''Alice'' FOR UPDATE;', NULL);
INSERT INTO 2024F_martbray.HW_Concurrency (Time_id, T1, T2) VALUES (3, NULL, 'START TRANSACTION;');
INSERT INTO 2024F_martbray.HW_Concurrency (Time_id, T1, T2) VALUES (4, 'UPDATE HW_test2 SET salary = salary + 5000 WHERE name = ''Alice'';', NULL);
INSERT INTO 2024F_martbray.HW_Concurrency (Time_id, T1, T2) VALUES (5, NULL, 'SELECT * FROM HW_test2 WHERE name = ''Bob'' FOR UPDATE;');
INSERT INTO 2024F_martbray.HW_Concurrency (Time_id, T1, T2) VALUES (6, NULL, 'UPDATE HW_test2 SET salary = salary + 3000 WHERE name = ''Bob'';');
INSERT INTO 2024F_martbray.HW_Concurrency (Time_id, T1, T2) VALUES (7, 'ERROR 1213 (40001): Deadlock found when trying to get lock; try restarting transaction;', NULL);

select * from 2024F_martbray.HW_Concurrency;



-- Create the HW_test2 table
CREATE TABLE 2024F_martbray.HW_test2 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10),
    salary FLOAT
);


    -- cambiar
-- Insert two records
INSERT INTO 2024F_martbray.HW_test2 (name, salary) VALUES ('Leticia', 62000);
INSERT INTO 2024F_martbray.HW_test2 (name, salary) VALUES ('Jennifer', 8000);



-- part 2 question 7

-- testing 
-- end 
UPDATE 2024F_martbray.HW_test1 SET name = 'p2' WHERE name = 'p1';

SELECT * FROM 2024F_martbray.HW_test_audit;

UPDATE 2024F_martbray.HW_test1 SET price = 13.7 WHERE name = 'p1';


DELETE FROM 2024F_martbray.HW_test1 WHERE name = 'p1';


SELECT * FROM 2024F_martbray.HW_test_audit;


INSERT INTO 2024F_martbray.HW_test1 (name, price, qty) VALUES ('p1', 10.5, 3);
-- start

-- part E

DELIMITER $$

CREATE TRIGGER 2024F_martbray.before_update_HW_test1
BEFORE UPDATE ON 2024F_martbray.HW_test1
FOR EACH ROW
BEGIN
    -- Check if the user is trying to change the name
    IF NEW.name <> OLD.name THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot update the name';
    ELSE
        -- Log the price change in the audit table if the price is being updated
        INSERT INTO 2024F_martbray.HW_test_audit (tid, user, access_time, old_price, new_price, note)
        VALUES (OLD.id, USER(), NOW(), OLD.price, NEW.price, CONCAT('update item ', OLD.name));
    END IF;
END $$

DELIMITER ;


-- Step D
DELIMITER $$

CREATE TRIGGER 2024F_martbray.before_delete_HW_test1
BEFORE DELETE ON 2024F_martbray.HW_test1
FOR EACH ROW
BEGIN
    DECLARE msg VARCHAR(255);
    SET msg = CONCAT('Cannot delete the item ', OLD.name);
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = msg;
END $$

DELIMITER ;




-- Step C

DELIMITER $$

CREATE TRIGGER 2024F_martbray.after_insert_HW_test1
AFTER INSERT ON HW_test1
FOR EACH ROW
BEGIN
    INSERT INTO HW_test_audit (tid, user, old_price, new_price, note)
    VALUES (NEW.id, CURRENT_USER(), NULL, NEW.price, 'after insert operation');
END $$

DELIMITER ;



-- step B

CREATE TABLE 2024F_martbray.HW_test_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tid INT,
    user VARCHAR(50),
    access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    old_price FLOAT,
    new_price FLOAT,
    note VARCHAR(255)
) ENGINE=MyISAM;


-- step A
CREATE TABLE 2024F_martbray.HW_test1 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10),
    price FLOAT,
    qty INT
);



-- part 2 question 6


-- point inside the polygons
SELECT 2024F_martbray.fHW_Inside_Polygon(5, 5);  -- Expected output: Test1, Test2

-- point outside the polygons
SELECT 2024F_martbray.fHW_Inside_Polygon(500, 50);  -- Expected output: No polygon found.


DELIMITER $$

CREATE FUNCTION 2024F_martbray.fHW_Inside_Polygon(x DOUBLE, y DOUBLE)
RETURNS VARCHAR(255)
BEGIN
    DECLARE polygon_names VARCHAR(255);
    
    -- Initialize the result as an empty string
    SET polygon_names = '';
    
    -- Query to find polygons containing the point
    SELECT GROUP_CONCAT(name SEPARATOR ', ')
    INTO polygon_names
    FROM Shape
    WHERE ST_Contains(g, POINT(x, y));
    
    -- Check if any polygons were found
    IF polygon_names IS NULL THEN
        RETURN 'No polygon found.';
    ELSE
        RETURN polygon_names;
    END IF;
END $$

DELIMITER ;



-- part 2 question 5


-- Step 1: Create the Shape table
CREATE TABLE 2024F_martbray.Shape (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    g GEOMETRY NOT NULL
);


-- //// NO CAMBIAR
-- Step 2: Insert the polygon data
INSERT INTO 2024F_martbray.Shape (name, g) 
VALUES 
    ('Test1', ST_PolygonFromText('POLYGON((0 0, 10 0, 10 10, 0 10, 0 0))')),
    ('Test2', ST_PolygonFromText('POLYGON((0 0, 100 0, 100 100, 0 100, 0 0))'));




select * from 2024F_martbray.Shape ;

-- part 2 question 4 ////////////////////////////////////////////////////
-- part 2 question 4 ////////////////////////////////////////////////////
-- part 2 question 4 ////////////////////////////////////////////////////
-- part 2 question 4 ////////////////////////////////////////////////////



-- part 2 question 3f

CALL 2024F_martbray.pProduct_report();


-- DROP PROCEDURE 2024F_martbray.pProduct_report;


DELIMITER $$

CREATE PROCEDURE 2024F_martbray.pProduct_report()
BEGIN
    -- Step 1: Create a temporary table to store intermediate results
    CREATE TEMPORARY TABLE IF NOT EXISTS TempProductReport (
        product_name VARCHAR(255),
        vendor_name VARCHAR(255),
        unit_cost DECIMAL(10, 2),
        available_quantity INT,
        sold_quantity INT DEFAULT 0,
        sell_price DECIMAL(10, 2),
        total_sales DECIMAL(10, 2) DEFAULT 0,
        profit DECIMAL(10, 2) DEFAULT 0
    );

    -- Step 2: Insert data into the temporary table
    INSERT INTO TempProductReport (product_name, vendor_name, unit_cost, available_quantity, sell_price)
    SELECT 
        p.name AS product_name,
        v.name AS vendor_name,
        p.cost AS unit_cost,
        p.quantity AS available_quantity,
        p.sell_price AS sell_price
    FROM 
        CPS5740.PRODUCT2 p
    LEFT JOIN 
        CPS5740.VENDOR2 v ON p.vendor_id = v.vendor_id;

    -- Step 3: Update the temporary table with sold quantity and total sales
    UPDATE TempProductReport tp
    JOIN (
        SELECT 
            po.product_id,
            SUM(po.quantity) AS sold_quantity,
            SUM(po.quantity * p.sell_price) AS total_sales
        FROM 
            CPS5740.PRODUCT_ORDER2 po
        JOIN 
            CPS5740.PRODUCT2 p ON po.product_id = p.product_id
        GROUP BY 
            po.product_id
    ) AS sales_data ON tp.product_name = (SELECT name FROM CPS5740.PRODUCT2 WHERE product_id = sales_data.product_id)
    SET 
        tp.sold_quantity = sales_data.sold_quantity,
        tp.total_sales = sales_data.total_sales;

    -- Step 4: Calculate profit for each product
    UPDATE TempProductReport
    SET profit = total_sales - (sold_quantity * unit_cost);

    -- Step 5: Select final report with total summary
    SELECT 
        product_name,
        vendor_name,
        unit_cost,
        available_quantity,
        sold_quantity,
        sell_price,
        total_sales,
        profit
    FROM 
        TempProductReport;

    -- Step 7: Drop the temporary table
    DROP TEMPORARY TABLE IF EXISTS TempProductReport;
END $$

DELIMITER ;





-- part 2 question 2

SET SQL_SAFE_UPDATES = 0;


CALL 2024F_martbray.pVendor_report();



DELIMITER $$

CREATE PROCEDURE 2024F_martbray.pVendor_report()
BEGIN
    -- Step 1: Create a temporary table to hold vendor summary data
    CREATE TEMPORARY TABLE IF NOT EXISTS TempVendorSummary (
        vendor_id INT,
        vendor_name VARCHAR(255),
        total_quantity_in_stock INT DEFAULT 0,
        total_cost DECIMAL(10, 2) DEFAULT 0,
        total_sold_quantity INT DEFAULT 0,
        total_sales DECIMAL(10, 2) DEFAULT 0,
        total_profit DECIMAL(10, 2) DEFAULT 0
    );

    -- Step 2: Insert vendor data into the temporary table
    INSERT INTO TempVendorSummary (vendor_id, vendor_name)
    SELECT v.vendor_id, v.name
    FROM CPS5740.VENDOR2 v;

    -- Step 3: Update the temporary table with product stock information
    UPDATE TempVendorSummary tvs
    JOIN (
        SELECT p.vendor_id,
               SUM(p.quantity) AS total_quantity_in_stock
        FROM CPS5740.PRODUCT2 p
        GROUP BY p.vendor_id
    ) AS stock_info ON tvs.vendor_id = stock_info.vendor_id
    SET tvs.total_quantity_in_stock = stock_info.total_quantity_in_stock;

    -- Step 4: Update the temporary table with sales information
    UPDATE TempVendorSummary tvs
    JOIN (
        SELECT v.vendor_id,
               SUM(po.quantity) AS total_sold_quantity,
               SUM(po.quantity * p.cost) AS total_cost,
               SUM(po.quantity * p.sell_price) AS total_sales,
               SUM((po.quantity * p.sell_price) - (po.quantity * p.cost)) AS total_profit
        FROM CPS5740.PRODUCT_ORDER2 po
        JOIN CPS5740.PRODUCT2 p ON po.product_id = p.product_id
        JOIN CPS5740.VENDOR2 v ON p.vendor_id = v.vendor_id
        GROUP BY v.vendor_id
    ) AS sales_info ON tvs.vendor_id = sales_info.vendor_id
    SET tvs.total_sold_quantity = sales_info.total_sold_quantity,
        tvs.total_cost = sales_info.total_cost,
        tvs.total_sales = sales_info.total_sales,
        tvs.total_profit = sales_info.total_profit;

    -- Step 5: Select the final report including totals
    SELECT 
        vendor_name,
        total_quantity_in_stock,
        IFNULL(total_cost, 0) AS total_cost,
        IFNULL(total_sold_quantity, 0) AS total_sold_quantity,
        IFNULL(total_sales, 0) AS total_sales,
        IFNULL(total_profit, 0) AS total_profit
    FROM TempVendorSummary
    group by vendor_name;


    -- Step 6: Drop the temporary table
    DROP TEMPORARY TABLE IF EXISTS TempVendorSummary;

END$$

DELIMITER ;




-- question 1 part 2


DELIMITER $$

CREATE FUNCTION 2024F_martbray.fGrade_GPA(grade VARCHAR(3))
RETURNS FLOAT
DETERMINISTIC
BEGIN
    DECLARE gpa FLOAT;
    
    -- Set default value to NULL to handle invalid grades
    SET gpa = NULL;
    
    -- Check if the input grade is NULL or empty
    IF grade IS NULL OR TRIM(grade) = '' THEN
        RETURN NULL;
    END IF;
    
    -- Mapping grades to GPA points
    CASE grade
        WHEN 'A' THEN SET gpa = 4.0;
        WHEN 'A-' THEN SET gpa = 3.7;
        WHEN 'B+' THEN SET gpa = 3.3;
        WHEN 'B' THEN SET gpa = 3.0;
        WHEN 'B-' THEN SET gpa = 2.7;
        WHEN 'C+' THEN SET gpa = 2.3;
        WHEN 'C' THEN SET gpa = 2.0;
        WHEN 'D' THEN SET gpa = 1.0;
        WHEN 'F' THEN SET gpa = 0.0;
    END CASE;
    
    RETURN gpa;
END$$

DELIMITER ;


-- question 6

select * from 2024F_martbray.vHW6;

CREATE VIEW 2024F_martbray.vHW6 AS
SELECT
	s.sid,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    s.major,
    COALESCE(AVG(2024F_martbray.fGrade_GPA(sc.grade)), NULL) AS average_GPA,
    COALESCE(SUM(c.credits * t.fee_per_credit), NULL) AS total_payment,
    MIN(CONCAT(sc.year, ' ', sc.semester)) AS first_year_semester,
    MAX(CONCAT(sc.year, ' ', sc.semester)) AS last_year_semester
FROM
    dreamhome.Students s
LEFT JOIN dreamhome.Students_Courses sc ON s.sid = sc.sid
LEFT JOIN dreamhome.Courses c ON sc.cid = c.cid
LEFT JOIN dreamhome.Tuitions t ON sc.year = t.year
GROUP BY s.sid, s.first_name, s.last_name, s.major;



-- question 5
select * from 2024F_martbray.vHW5;

-- CREATE VIEW 2024F_martbray.vHW5 AS 
SELECT
    c1.cid AS cid,
    c1.prerequisite_cid AS direct_prerequisite,
    c2.prerequisite_cid AS level2_prerequisite,
    c3.prerequisite_cid AS level3_prerequisite
FROM
    Courses c1
LEFT JOIN Courses c2 ON c1.prerequisite_cid = c2.cid
LEFT JOIN Courses c3 ON c2.prerequisite_cid = c3.cid;


-- question 4 

select * from 2024F_martbray.vHW4;


CREATE VIEW 2024F_martbray.vHW4 AS 
SELECT
    s.major,
    COUNT(DISTINCT s.sid) AS number_of_students,
    AVG(TIMESTAMPDIFF(YEAR, s.birthday, CURDATE())) AS avg_student_age,
    AVG(2024F_martbray.fGrade_GPA(sc.grade)) AS avg_student_gpa
FROM
    dreamhome.Students s
LEFT JOIN
    dreamhome.Students_Courses sc ON s.sid = sc.sid
GROUP BY
    s.major;


-- question 3

select * from 2024F_martbray.vHW3;


-- CREATE VIEW 2024F_martbray.vHW3 AS 
SELECT 
    c.name AS course_name,
    COALESCE(SUM(t.fee_per_credit * c.credits), 0) AS total_payment,
    COALESCE(COUNT(sc.sid), 0) AS total_enrollment
FROM 
    Courses c
LEFT JOIN 
    Students_Courses sc ON c.cid = sc.cid
LEFT JOIN 
    Tuitions t ON sc.year = t.year
GROUP BY 
    c.name
ORDER BY 
    total_payment DESC;




-- question 2
select * from 2024F_martbray.vHW2;
-- CREATE VIEW 2024F_martbray.vHW2 AS 
SELECT 
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    COUNT(sc.cid) AS number_of_courses,
    SUM(t.fee_per_credit) AS total_payment
FROM 
    Students s
JOIN 
    Students_Courses sc ON s.sid = sc.sid
JOIN 
    Tuitions t ON sc.year = t.year
GROUP BY 
    s.first_name, s.last_name
ORDER BY 
    total_payment DESC;




-- question 1

use 2024F_martbray;
-- CREATE VIEW 2024F_martbray.vHW1 AS 
select * from 2024F_martbray.vHW1;

CREATE VIEW 2024F_martbray.vHW1 AS 
SELECT CONCAT(s.first_name, ' ', s.last_name) AS student_name, 
COUNT(Students_Courses.Cid) AS number_of_courses
FROM Students s
JOIN Students_Courses
ON s.sid = Students_Courses.sid
GROUP BY s.first_name, s.last_name  
ORDER BY number_of_courses DESC 
LIMIT 1;