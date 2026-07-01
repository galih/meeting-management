-- Folder sharing support for /dokumen
CREATE TABLE IF NOT EXISTS dokumen_folder_shares (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folder_id INT NOT NULL,
  shared_to INT NOT NULL,
  permission ENUM('view','download') NOT NULL DEFAULT 'view',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_folder_share (folder_id, shared_to),
  KEY idx_folder_share_folder (folder_id),
  KEY idx_folder_share_user (shared_to)
);
