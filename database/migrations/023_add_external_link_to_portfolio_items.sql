-- Migration: 023_add_external_link_to_portfolio_items
-- เพิ่มคอลัมน์ external_link สำหรับเก็บลิงก์ผลงานภายนอก (Google Drive / OneDrive / เอกสาร)

ALTER TABLE `portfolio_items` 
ADD COLUMN IF NOT EXISTS `external_link` VARCHAR(500) DEFAULT NULL AFTER `thumbnail`;
