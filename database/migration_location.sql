-- Add department and dual-market salary columns to positions
ALTER TABLE positions
  ADD COLUMN department VARCHAR(100) NULL AFTER company_id,
  ADD COLUMN monthly_salary_doha DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER monthly_salary,
  ADD COLUMN monthly_salary_lebanon DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER monthly_salary_doha;

-- Seed doha rate from existing monthly_salary so rate card keeps working
UPDATE positions SET monthly_salary_doha = monthly_salary WHERE monthly_salary_doha = 0 AND monthly_salary > 0;

-- Add per-line location to proposal items
ALTER TABLE proposal_items
  ADD COLUMN location ENUM('doha','lebanon') NOT NULL DEFAULT 'doha' AFTER position_id;
