-- Admin user SQL script for EduTrip
-- Password is: Admin123!
-- The password hash below is the hashed version of 'Admin123!'

-- Check if admin already exists
SET @admin_email = 'admin@edutrip.com';
SET @admin_count = (SELECT COUNT(*) FROM `user` WHERE `mail` = @admin_email);

-- Only insert if admin doesn't exist
INSERT INTO `user` (`mail`, `role`, `password`, `prenom`, `nom`, `tel`, `status`)
SELECT 
    'admin@edutrip.com', 
    'ROLE_ADMIN', 
    '$2y$13$bwA0uO.8h1pPx1.S0yO4V.v.7Vsi72GYHaU2z6CZcL8zBZM8WIXVa', 
    'Admin', 
    'EduTrip', 
    '12345678', 
    'active'
WHERE NOT EXISTS (SELECT 1 FROM `user` WHERE `mail` = @admin_email); 