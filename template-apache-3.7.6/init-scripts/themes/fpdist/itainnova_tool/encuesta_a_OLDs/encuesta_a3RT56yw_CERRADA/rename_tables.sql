RENAME TABLE encuesta_2016 TO encuesta;
RENAME TABLE encuesta_2016_datos TO encuesta_datos;
UPDATE encuesta SET encuesta = '20160301a' WHERE encuesta = '2016v3';
UPDATE encuesta SET encuesta = '20160201a' WHERE encuesta = '2016v2';
UPDATE encuesta SET encuesta = '20160101a' WHERE encuesta = '2016';
UPDATE encuesta SET encuesta = '20160301p' WHERE encuesta = '2016pv3';
UPDATE encuesta SET encuesta = '20160201p' WHERE encuesta = '2016pv2';
UPDATE encuesta SET encuesta = '20160101p' WHERE encuesta = '2016p';
