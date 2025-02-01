-- Add date_of_birth column to students table
ALTER TABLE students ADD COLUMN date_of_birth DATE NOT NULL AFTER father_name;
