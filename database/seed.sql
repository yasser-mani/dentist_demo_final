USE dentaflow;

SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE attachments;
TRUNCATE TABLE patient_notes;
TRUNCATE TABLE teeth_records;
TRUNCATE TABLE appointments;
TRUNCATE TABLE patients;
SET FOREIGN_KEY_CHECKS=1;

INSERT INTO patients (id,full_name,phone,insurance,status_id,created_at) VALUES
(1,'Marie Dubois','01 42 86 82 00','CPAM Île-de-France',1,DATE_SUB(NOW(),INTERVAL 45 DAY)),
(2,'Jean Martin','01 48 05 26 26','Harmonie Mutuelle',2,DATE_SUB(NOW(),INTERVAL 30 DAY)),
(3,'Sophie Laurent','06 12 34 56 78','MGEN',2,DATE_SUB(NOW(),INTERVAL 20 DAY)),
(4,'Pierre Bernard','01 55 12 00 00',NULL,1,DATE_SUB(NOW(),INTERVAL 15 DAY)),
(5,'Isabelle Moreau','06 98 76 54 32','Mutuelle Générale',2,DATE_SUB(NOW(),INTERVAL 10 DAY)),
(6,'Luc Petit','01 40 50 60 70','AG2R La Mondiale',1,DATE_SUB(NOW(),INTERVAL 5 DAY)),
(7,'Camille Roux','06 11 22 33 44','Malakoff Humanis',2,DATE_SUB(NOW(),INTERVAL 2 DAY)),
(8,'Thomas Lefevre','01 70 80 90 00','MAIF',1,NOW());

INSERT INTO appointments (patient_id,appointment_date,reason,status) VALUES
(1,DATE_ADD(NOW(),INTERVAL 1 HOUR),'Contrôle annuel','scheduled'),
(2,DATE_ADD(NOW(),INTERVAL 3 HOUR),'Détartrage','scheduled'),
(8,DATE_ADD(NOW(),INTERVAL 5 HOUR),'Consultation initiale','scheduled'),
(3,DATE_ADD(NOW(),INTERVAL 1 DAY),'Soin carie molaire','scheduled'),
(4,DATE_ADD(NOW(),INTERVAL 2 DAY),'Radiographie panoramique','scheduled'),
(5,DATE_ADD(NOW(),INTERVAL 3 DAY),'Couronne sur 16','scheduled'),
(6,DATE_ADD(NOW(),INTERVAL 5 DAY),'Détartrage','scheduled'),
(7,DATE_ADD(NOW(),INTERVAL 7 DAY),'Blanchiment','scheduled'),
(2,DATE_SUB(NOW(),INTERVAL 2 DAY),'Soin sur 36','completed'),
(3,DATE_SUB(NOW(),INTERVAL 5 DAY),'Contrôle','completed');

INSERT INTO teeth_records(patient_id,tooth_number,state,treatment,notes,record_date) VALUES
(2,16,'needs_intervention','Couronne céramique','Lésion importante à contrôler.','2026-09-10'),
(2,36,'in_progress','Composite','Traitement en cours.','2026-09-18'),
(3,21,'treated','Restauration composite','Aspect stable.','2026-09-12'),
(5,46,'needs_intervention','Carie occlusale','Prévoir soin.','2026-09-20');

INSERT INTO patient_notes(patient_id,content) VALUES
(2,'Patient sensible au froid sur le secteur inférieur droit.'),
(2,'Conseiller une hygiène interdentaire quotidienne.'),
(3,'Contrôle satisfaisant. Continuer le suivi préventif.'),
(5,'Planifier la restauration de la dent 46.');
