moosh category-create -p 0 -v 1 "Infantil"
moosh category-create -p 2 -v 1 "Primero"
moosh category-create -p 2 -v 1 "Segundo"
moosh category-create -p 2 -v 1 "Tercero"
moosh category-create -p 0 -v 1 "Primaria"
moosh category-create -p 6 -v 1 "Primero"
moosh category-create -p 6 -v 1 "Segundo"
moosh category-create -p 6 -v 1 "Tercero"
moosh category-create -p 6 -v 1 "Cuarto"
moosh category-create -p 6 -v 1 "Quinto"
moosh category-create -p 6 -v 1 "Sexto"
moosh category-create -p 0 -v 1 "Sala de profesorado CEIP"
moosh category-create -p 0 -v 1 "Proyectos de trabajo CEIP"

moosh category-create -p 0 -v 1 "ESO"
moosh category-create -p 15 -v 1 "Primero"
moosh category-create -p 15 -v 1 "Segundo"
moosh category-create -p 15 -v 1 "Tercero"
moosh category-create -p 15 -v 1 "Cuarto"
moosh category-create -p 15 -v 1 "PMAR"
moosh category-create -p 20 -v 1 "Primero"
moosh category-create -p 20 -v 1 "Segundo"
moosh category-create -p 0 -v 1 "Bachillerato"
moosh category-create -p 23 -v 1 "Primero"
moosh category-create -p 23 -v 1 "Segundo"
moosh category-create -p 0 -v 1 "Proyectos de trabajo IES"
moosh category-create -p 0 -v 1 "Sala de Profesorado IES"

moosh course-restore /init-scripts/mbzs/10_ceip.mbz 3
moosh course-config-set course 2 shortname proyecto6_inf_primero
moosh course-config-set course 2 fullname "PROYECTO 6 1º"
moosh course-restore /init-scripts/mbzs/11_ceip.mbz 3
moosh course-config-set course 3 shortname proyecto4_inf_primero
moosh course-config-set course 3 fullname "PROYECTO 4 1º"
moosh course-restore /init-scripts/mbzs/12_ceip.mbz 3
moosh course-config-set course 4 shortname proyecto3_inf_primero
moosh course-config-set course 4 fullname "PROYECTO 3 1º"cd 
moosh course-restore /init-scripts/mbzs/13_ceip.mbz 3
moosh course-config-set course 5 shortname proyecto2_inf_primero
moosh course-config-set course 5 fullname "PROYECTO 2 1º"
moosh course-restore /init-scripts/mbzs/14_ceip.mbz 3
moosh course-config-set course 6 shortname proyecto1_inf_primero
moosh course-config-set course 6 fullname "PROYECTO 1 1º"
moosh course-restore /init-scripts/mbzs/2_ceip.mbz 3
moosh course-config-set course 7 shortname english_inf_primero
moosh course-config-set course 7 fullname "ENGLISH 1º"
moosh course-restore /init-scripts/mbzs/3_ceip.mbz 3
moosh course-config-set course 8 shortname atencione_inf_primero
moosh course-config-set course 8 fullname "ATENCIÓN EDUCATIVA 1º"
moosh course-restore /init-scripts/mbzs/4_ceip.mbz 3
moosh course-config-set course 9 shortname religion_inf_primero
moosh course-config-set course 9 fullname "RELIGIÓN 1º"
moosh course-restore /init-scripts/mbzs/5_ceip.mbz 3
moosh course-config-set course 10 shortname aleman_inf_primero
moosh course-config-set course 10 fullname "DEUTSCH 1º"
moosh course-restore /init-scripts/mbzs/6_ceip.mbz 3
moosh course-config-set course 11 shortname frances_inf_primero
moosh course-config-set course 11 fullname "FRANÇAIS 1º"
moosh course-restore /init-scripts/mbzs/7_ceip.mbz 3
moosh course-config-set course 12 shortname proyecto5_inf_primero
moosh course-config-set course 12 fullname "PROYECTO 5 1º"
moosh course-restore /init-scripts/mbzs/8_ceip.mbz 3
moosh course-config-set course 13 shortname aragones_inf_primero
moosh course-config-set course 13 fullname "ARAGONÉS 1º"
moosh course-restore /init-scripts/mbzs/9_ceip.mbz 3
moosh course-config-set course 14 shortname catalan_inf_primero
moosh course-config-set course 14 fullname "CATALÁN 1º"
moosh course-restore /init-scripts/mbzs/10_ceip.mbz 4
moosh course-config-set course 15 shortname proyecto6_inf_segundo
moosh course-config-set course 15 fullname "PROYECTO 6 2º"
moosh course-restore /init-scripts/mbzs/11_ceip.mbz 4
moosh course-config-set course 16 shortname proyecto4_inf_segundo
moosh course-config-set course 16 fullname "PROYECTO 4 2º"
moosh course-restore /init-scripts/mbzs/12_ceip.mbz 4
moosh course-config-set course 17 shortname proyecto3_inf_segundo
moosh course-config-set course 17 fullname "PROYECTO 3 2º"
moosh course-restore /init-scripts/mbzs/13_ceip.mbz 4
moosh course-config-set course 18 shortname proyecto2_inf_segundo
moosh course-config-set course 18 fullname "PROYECTO 2 2º"
moosh course-restore /init-scripts/mbzs/14_ceip.mbz 4
moosh course-config-set course 19 shortname proyecto1_inf_segundo
moosh course-config-set course 19 fullname "PROYECTO 1 2º"
moosh course-restore /init-scripts/mbzs/2_ceip.mbz 4
moosh course-config-set course 20 shortname english_inf_segundo
moosh course-config-set course 20 fullname "ENGLISH 2º"
moosh course-restore /init-scripts/mbzs/3_ceip.mbz 4
moosh course-config-set course 21 shortname atencione_inf_segundo
moosh course-config-set course 21 fullname "ATENCIÓN EDUCATIVA 2º"
moosh course-restore /init-scripts/mbzs/4_ceip.mbz 4
moosh course-config-set course 22 shortname religion_inf_segundo
moosh course-config-set course 22 fullname "RELIGIÓN 2º"
moosh course-restore /init-scripts/mbzs/5_ceip.mbz 4
moosh course-config-set course 23 shortname aleman_inf_segundo
moosh course-config-set course 23 fullname "DEUTSCH 2º"
moosh course-restore /init-scripts/mbzs/6_ceip.mbz 4
moosh course-config-set course 24 shortname frances_inf_segundo
moosh course-config-set course 24 fullname "FRANÇAIS 2º"
moosh course-restore /init-scripts/mbzs/7_ceip.mbz 4
moosh course-config-set course 25 shortname proyecto5_inf_segundo
moosh course-config-set course 25 fullname "PROYECTO 5 2º"
moosh course-restore /init-scripts/mbzs/8_ceip.mbz 4
moosh course-config-set course 26 shortname aragones_inf_segundo
moosh course-config-set course 26 fullname "ARAGONÉS 2º"
moosh course-restore /init-scripts/mbzs/9_ceip.mbz 4
moosh course-config-set course 27 shortname catalan_inf_segundo
moosh course-config-set course 27 fullname "CATALÁN 2º"
moosh course-restore /init-scripts/mbzs/10_ceip.mbz 5
moosh course-config-set course 28 shortname proyecto6_inf_tercero
moosh course-config-set course 28 fullname "PROYECTO 6 3º"
moosh course-restore /init-scripts/mbzs/11_ceip.mbz 5
moosh course-config-set course 29 shortname proyecto4_inf_tercero
moosh course-config-set course 29 fullname "PROYECTO 4 3º"
moosh course-restore /init-scripts/mbzs/12_ceip.mbz 5
moosh course-config-set course 30 shortname proyecto3_inf_tercero
moosh course-config-set course 30 fullname "PROYECTO 3 3º"
moosh course-restore /init-scripts/mbzs/13_ceip.mbz 5
moosh course-config-set course 31 shortname proyecto2_inf_tercero
moosh course-config-set course 31 fullname "PROYECTO 2 3º"
moosh course-restore /init-scripts/mbzs/14_ceip.mbz 5
moosh course-config-set course 32 shortname proyecto1_inf_tercero
moosh course-config-set course 32 fullname "PROYECTO 1 3º"
moosh course-restore /init-scripts/mbzs/2_ceip.mbz 5
moosh course-config-set course 33 shortname english_inf_tercero
moosh course-config-set course 33 fullname "ENGLISH 3º"
moosh course-restore /init-scripts/mbzs/3_ceip.mbz 5
moosh course-config-set course 34 shortname atencione_inf_tercero
moosh course-config-set course 34 fullname "ATENCIÓN EDUCATIVA 3º"
moosh course-restore /init-scripts/mbzs/4_ceip.mbz 5
moosh course-config-set course 35 shortname religion_inf_tercero
moosh course-config-set course 35 fullname "RELIGIÓN 3º"
moosh course-restore /init-scripts/mbzs/5_ceip.mbz 5
moosh course-config-set course 36 shortname aleman_inf_tercero
moosh course-config-set course 36 fullname "DEUTSCH 3º"
moosh course-restore /init-scripts/mbzs/6_ceip.mbz 5
moosh course-config-set course 37 shortname frances_inf_tercero
moosh course-config-set course 37 fullname "FRANÇAIS 3º"
moosh course-restore /init-scripts/mbzs/7_ceip.mbz 5
moosh course-config-set course 38 shortname proyecto5_inf_tercero
moosh course-config-set course 38 fullname "PROYECTO 5 3º"
moosh course-restore /init-scripts/mbzs/8_ceip.mbz 5
moosh course-config-set course 39 shortname aragones_inf_tercero
moosh course-config-set course 39 fullname "ARAGONÉS 3º"
moosh course-restore /init-scripts/mbzs/9_ceip.mbz 5
moosh course-config-set course 40 shortname catalan_inf_tercero
moosh course-config-set course 40 fullname "CATALÁN 3º"
moosh course-restore /init-scripts/mbzs/41_ceip.mbz 7
moosh course-config-set course 41 shortname lengua_primero_prim
moosh course-config-set course 41 fullname "Lengua y Literatura 1º"
moosh course-restore /init-scripts/mbzs/42_ceip.mbz 7
moosh course-config-set course 42 shortname sociales_primero_prim
moosh course-config-set course 42 fullname "Ciencias Sociales 1º"
moosh course-restore /init-scripts/mbzs/43_ceip.mbz 7
moosh course-config-set course 43 shortname matematicas_primero_prim
moosh course-config-set course 43 fullname "Matemáticas 1º"
moosh course-restore /init-scripts/mbzs/44_ceip.mbz 7
moosh course-config-set course 44 shortname ingles_primero_prim
moosh course-config-set course 44 fullname "English 1º"
moosh course-restore /init-scripts/mbzs/45_ceip.mbz 7
moosh course-config-set course 45 shortname edfisica_primero_prim
moosh course-config-set course 45 fullname "Educación Física 1º"
moosh course-restore /init-scripts/mbzs/46_ceip.mbz 7
moosh course-config-set course 46 shortname religion_primero_prim
moosh course-config-set course 46 fullname "Religión 1º"
moosh course-restore /init-scripts/mbzs/47_ceip.mbz 7
moosh course-config-set course 47 shortname valores_primero_prim
moosh course-config-set course 47 fullname "Valores Sociales y Cívicos 1º"
moosh course-restore /init-scripts/mbzs/48_ceip.mbz 7
moosh course-config-set course 48 shortname artistica_primero_prim
moosh course-config-set course 48 fullname "Educación Artística 1º"
moosh course-restore /init-scripts/mbzs/49_ceip.mbz 7
moosh course-config-set course 49 shortname aleman_primero_prim
moosh course-config-set course 49 fullname "Deutsch 1º"
moosh course-restore /init-scripts/mbzs/50_ceip.mbz 7
moosh course-config-set course 50 shortname catalan_primero_prim
moosh course-config-set course 50 fullname "Catalán 1º"
moosh course-restore /init-scripts/mbzs/51_ceip.mbz 7
moosh course-config-set course 51 shortname aragones_primero_prim
moosh course-config-set course 51 fullname "Aragonés 1º"
moosh course-restore /init-scripts/mbzs/52_ceip.mbz 7
moosh course-config-set course 52 shortname francais_primero_prim
moosh course-config-set course 52 fullname "Français 1º"
moosh course-restore /init-scripts/mbzs/53_ceip.mbz 7
moosh course-config-set course 53 shortname ciencias_primero_prim
moosh course-config-set course 53 fullname "Ciencias Naturales 1º"
moosh course-restore /init-scripts/mbzs/41_ceip.mbz 8
moosh course-config-set course 54 shortname lengua_segundo_prim
moosh course-config-set course 54 fullname "Lengua y Literatura 2º"
moosh course-restore /init-scripts/mbzs/42_ceip.mbz 8
moosh course-config-set course 55 shortname sociales_segundo_prim
moosh course-config-set course 55 fullname "Ciencias Sociales 2º"
moosh course-restore /init-scripts/mbzs/43_ceip.mbz 8
moosh course-config-set course 56 shortname matematicas_segundo_prim
moosh course-config-set course 56 fullname "Matemáticas 2º"
moosh course-restore /init-scripts/mbzs/44_ceip.mbz 8
moosh course-config-set course 57 shortname ingles_segundo_prim
moosh course-config-set course 57 fullname "English 2º"
moosh course-restore /init-scripts/mbzs/45_ceip.mbz 8
moosh course-config-set course 58 shortname edfisica_segundo_prim
moosh course-config-set course 58 fullname "Educación Física 2º"
moosh course-restore /init-scripts/mbzs/46_ceip.mbz 8
moosh course-config-set course 59 shortname religion_segundo_prim
moosh course-config-set course 59 fullname "Religión 2º"
moosh course-restore /init-scripts/mbzs/47_ceip.mbz 8
moosh course-config-set course 60 shortname valores_segundo_prim
moosh course-config-set course 60 fullname "Valores Sociales y Cívicos 2º"
moosh course-restore /init-scripts/mbzs/48_ceip.mbz 8
moosh course-config-set course 61 shortname artistica_segundo_prim
moosh course-config-set course 61 fullname "Educación Artística 2º"
moosh course-restore /init-scripts/mbzs/49_ceip.mbz 8
moosh course-config-set course 62 shortname aleman_segundo_prim
moosh course-config-set course 62 fullname "Deutsch 2º"
moosh course-restore /init-scripts/mbzs/50_ceip.mbz 8
moosh course-config-set course 63 shortname catalan_segundo_prim
moosh course-config-set course 63 fullname "Catalán 2º"
moosh course-restore /init-scripts/mbzs/51_ceip.mbz 8
moosh course-config-set course 64 shortname aragones_segundo_prim
moosh course-config-set course 64 fullname "Aragonés 2º"
moosh course-restore /init-scripts/mbzs/52_ceip.mbz 8
moosh course-config-set course 65 shortname francais_segundo_prim
moosh course-config-set course 65 fullname "Français 2º"
moosh course-restore /init-scripts/mbzs/53_ceip.mbz 8
moosh course-config-set course 66 shortname ciencias_segundo_prim
moosh course-config-set course 66 fullname "Ciencias Naturales 2º"
moosh course-restore /init-scripts/mbzs/41_ceip.mbz 9
moosh course-config-set course 67 shortname lengua_tercero_prim
moosh course-config-set course 67 fullname "Lengua y Literatura 3º"
moosh course-restore /init-scripts/mbzs/42_ceip.mbz 9
moosh course-config-set course 68 shortname sociales_tercero_prim
moosh course-config-set course 68 fullname "Ciencias Sociales 3º"
moosh course-restore /init-scripts/mbzs/43_ceip.mbz 9
moosh course-config-set course 69 shortname matematicas_tercero_prim
moosh course-config-set course 69 fullname "Matemáticas 3º"
moosh course-restore /init-scripts/mbzs/44_ceip.mbz 9
moosh course-config-set course 70 shortname ingles_tercero_prim
moosh course-config-set course 70 fullname "English 3º"
moosh course-restore /init-scripts/mbzs/45_ceip.mbz 9
moosh course-config-set course 71 shortname edfisica_tercero_prim
moosh course-config-set course 71 fullname "Educación Física 3º"
moosh course-restore /init-scripts/mbzs/46_ceip.mbz 9
moosh course-config-set course 72 shortname religion_tercero_prim
moosh course-config-set course 72 fullname "Religión 3º"
moosh course-restore /init-scripts/mbzs/47_ceip.mbz 9
moosh course-config-set course 73 shortname valores_tercero_prim
moosh course-config-set course 73 fullname "Valores Sociales y Cívicos 3º"
moosh course-restore /init-scripts/mbzs/48_ceip.mbz 9
moosh course-config-set course 74 shortname artistica_tercero_prim
moosh course-config-set course 74 fullname "Educación Artística 3º"
moosh course-restore /init-scripts/mbzs/49_ceip.mbz 9
moosh course-config-set course 75 shortname aleman_tercero_prim
moosh course-config-set course 75 fullname "Deutsch 3º"
moosh course-restore /init-scripts/mbzs/50_ceip.mbz 9
moosh course-config-set course 76 shortname catalan_tercero_prim
moosh course-config-set course 76 fullname "Catalán 3º"
moosh course-restore /init-scripts/mbzs/51_ceip.mbz 9
moosh course-config-set course 77 shortname aragones_tercero_prim
moosh course-config-set course 77 fullname "Aragonés 3º"
moosh course-restore /init-scripts/mbzs/52_ceip.mbz 9
moosh course-config-set course 78 shortname francais_tercero_prim
moosh course-config-set course 78 fullname "Français 3º"
moosh course-restore /init-scripts/mbzs/53_ceip.mbz 9
moosh course-config-set course 79 shortname ciencias_tercero_prim
moosh course-config-set course 79 fullname "Ciencias Naturales 3º"
moosh course-restore /init-scripts/mbzs/41_ceip.mbz 10
moosh course-config-set course 80 shortname lengua_cuarto_prim
moosh course-config-set course 80 fullname "Lengua y Literatura 4º"
moosh course-restore /init-scripts/mbzs/42_ceip.mbz 10
moosh course-config-set course 81 shortname sociales_cuarto_prim
moosh course-config-set course 81 fullname "Ciencias Sociales 4º"
moosh course-restore /init-scripts/mbzs/43_ceip.mbz 10
moosh course-config-set course 82 shortname matematicas_cuarto_prim
moosh course-config-set course 82 fullname "Matemáticas 4º"
moosh course-restore /init-scripts/mbzs/44_ceip.mbz 10
moosh course-config-set course 83 shortname ingles_cuarto_prim
moosh course-config-set course 83 fullname "English 4º"
moosh course-restore /init-scripts/mbzs/45_ceip.mbz 10
moosh course-config-set course 84 shortname edfisica_cuarto_prim
moosh course-config-set course 84 fullname "Educación Física 4º"
moosh course-restore /init-scripts/mbzs/46_ceip.mbz 10
moosh course-config-set course 85 shortname religion_cuarto_prim
moosh course-config-set course 85 fullname "Religión 4º"
moosh course-restore /init-scripts/mbzs/47_ceip.mbz 10
moosh course-config-set course 86 shortname valores_cuarto_prim
moosh course-config-set course 86 fullname "Valores Sociales y Cívicos 4º"
moosh course-restore /init-scripts/mbzs/48_ceip.mbz 10
moosh course-config-set course 87 shortname artistica_cuarto_prim
moosh course-config-set course 87 fullname "Educación Artística 4º"
moosh course-restore /init-scripts/mbzs/49_ceip.mbz 10
moosh course-config-set course 88 shortname aleman_cuarto_prim
moosh course-config-set course 88 fullname "Deutsch 4º"
moosh course-restore /init-scripts/mbzs/50_ceip.mbz 10
moosh course-config-set course 89 shortname catalan_cuarto_prim
moosh course-config-set course 89 fullname "Catalán 4º"
moosh course-restore /init-scripts/mbzs/51_ceip.mbz 10
moosh course-config-set course 90 shortname aragones_cuarto_prim
moosh course-config-set course 90 fullname "Aragonés 4º"
moosh course-restore /init-scripts/mbzs/52_ceip.mbz 10
moosh course-config-set course 91 shortname francais_cuarto_prim
moosh course-config-set course 91 fullname "Français 4º"
moosh course-restore /init-scripts/mbzs/53_ceip.mbz 10
moosh course-config-set course 92 shortname ciencias_cuarto_prim
moosh course-config-set course 92 fullname "Ciencias Naturales 4º"
moosh course-restore /init-scripts/mbzs/41_ceip.mbz 11
moosh course-config-set course 93 shortname lengua_quinto_prim
moosh course-config-set course 93 fullname "Lengua y Literatura 5º"
moosh course-restore /init-scripts/mbzs/42_ceip.mbz 11
moosh course-config-set course 94 shortname sociales_quinto_prim
moosh course-config-set course 94 fullname "Ciencias Sociales 5º"
moosh course-restore /init-scripts/mbzs/43_ceip.mbz 11
moosh course-config-set course 95 shortname matematicas_quinto_prim
moosh course-config-set course 95 fullname "Matemáticas 5º"
moosh course-restore /init-scripts/mbzs/44_ceip.mbz 11
moosh course-config-set course 96 shortname ingles_quinto_prim
moosh course-config-set course 96 fullname "English 5º"
moosh course-restore /init-scripts/mbzs/45_ceip.mbz 11
moosh course-config-set course 97 shortname edfisica_quinto_prim
moosh course-config-set course 97 fullname "Educación Física 5º"
moosh course-restore /init-scripts/mbzs/46_ceip.mbz 11
moosh course-config-set course 98 shortname religion_quinto_prim
moosh course-config-set course 98 fullname "Religión 5º"
moosh course-restore /init-scripts/mbzs/47_ceip.mbz 11
moosh course-config-set course 99 shortname valores_quinto_prim
moosh course-config-set course 99 fullname "Valores Sociales y Cívicos 5º"
moosh course-restore /init-scripts/mbzs/48_ceip.mbz 11
moosh course-config-set course 100 shortname artistica_quinto_prim
moosh course-config-set course 100 fullname "Educación Artística 5º"
moosh course-restore /init-scripts/mbzs/49_ceip.mbz 11
moosh course-config-set course 101 shortname aleman_quinto_prim
moosh course-config-set course 101 fullname "Deutsch 5º"
moosh course-restore /init-scripts/mbzs/50_ceip.mbz 11
moosh course-config-set course 102 shortname catalan_quinto_prim
moosh course-config-set course 102 fullname "Catalán 5º"
moosh course-restore /init-scripts/mbzs/51_ceip.mbz 11
moosh course-config-set course 103 shortname aragones_quinto_prim
moosh course-config-set course 103 fullname "Aragonés 5º"
moosh course-restore /init-scripts/mbzs/52_ceip.mbz 11
moosh course-config-set course 104 shortname francais_quinto_prim
moosh course-config-set course 104 fullname "Français 5º"
moosh course-restore /init-scripts/mbzs/53_ceip.mbz 11
moosh course-config-set course 105 shortname ciencias_quinto_prim
moosh course-config-set course 105 fullname "Ciencias Naturales 5º"
moosh course-restore /init-scripts/mbzs/41_ceip.mbz 12
moosh course-config-set course 106 shortname lengua_sexto_prim
moosh course-config-set course 106 fullname "Lengua y Literatura 6º"
moosh course-restore /init-scripts/mbzs/42_ceip.mbz 12
moosh course-config-set course 107 shortname sociales_sexto_prim
moosh course-config-set course 107 fullname "Ciencias Sociales 6º"
moosh course-restore /init-scripts/mbzs/43_ceip.mbz 12
moosh course-config-set course 108 shortname matematicas_sexto_prim
moosh course-config-set course 108 fullname "Matemáticas 6º"
moosh course-restore /init-scripts/mbzs/44_ceip.mbz 12
moosh course-config-set course 109 shortname ingles_sexto_prim
moosh course-config-set course 109 fullname "English 6º"
moosh course-restore /init-scripts/mbzs/45_ceip.mbz 12
moosh course-config-set course 110 shortname edfisica_sexto_prim
moosh course-config-set course 110 fullname "Educación Física 6º"
moosh course-restore /init-scripts/mbzs/46_ceip.mbz 12
moosh course-config-set course 111 shortname religion_sexto_prim
moosh course-config-set course 111 fullname "Religión 6º"
moosh course-restore /init-scripts/mbzs/47_ceip.mbz 12
moosh course-config-set course 112 shortname valores_sexto_prim
moosh course-config-set course 112 fullname "Valores Sociales y Cívicos 6º"
moosh course-restore /init-scripts/mbzs/48_ceip.mbz 12
moosh course-config-set course 113 shortname artistica_sexto_prim
moosh course-config-set course 113 fullname "Educación Artística 6º"
moosh course-restore /init-scripts/mbzs/49_ceip.mbz 12
moosh course-config-set course 114 shortname aleman_sexto_prim
moosh course-config-set course 114 fullname "Deutsch 6º"
moosh course-restore /init-scripts/mbzs/50_ceip.mbz 12
moosh course-config-set course 115 shortname catalan_sexto_prim
moosh course-config-set course 115 fullname "Catalán 6º"
moosh course-restore /init-scripts/mbzs/51_ceip.mbz 12
moosh course-config-set course 116 shortname aragones_sexto_prim
moosh course-config-set course 116 fullname "Aragonés 6º"
moosh course-restore /init-scripts/mbzs/52_ceip.mbz 12
moosh course-config-set course 117 shortname francais_sexto_prim
moosh course-config-set course 117 fullname "Français 6º"
moosh course-restore /init-scripts/mbzs/53_ceip.mbz 12
moosh course-config-set course 118 shortname ciencias_sexto_prim
moosh course-config-set course 118 fullname "Ciencias Naturales 6º"
moosh course-restore /init-scripts/mbzs/107_ceip.mbz 13
moosh course-config-set course 119 shortname claustro
moosh course-config-set course 119 fullname "Claustro "
moosh course-restore /init-scripts/mbzs/108_ceip.mbz 13
moosh course-config-set course 120 shortname ccp
moosh course-config-set course 120 fullname "CCP "
moosh course-restore /init-scripts/mbzs/109_ceip.mbz 13
moosh course-config-set course 121 shortname equipo-directivo
moosh course-config-set course 121 fullname "Equipo directivo "
moosh course-restore /init-scripts/mbzs/110_ceip.mbz 13
moosh course-config-set course 122 shortname equipos
moosh course-config-set course 122 fullname "Equipos didácticos "
moosh course-restore /init-scripts/mbzs/14_ceip.mbz 14
moosh course-config-set course 123 shortname proyecto_prim_1
moosh course-config-set course 123 fullname "PROYECTO  1"
moosh course-restore /init-scripts/mbzs/13_ceip.mbz 14
moosh course-config-set course 124 shortname proyecto_prim_2
moosh course-config-set course 124 fullname "PROYECTO  2"
moosh course-restore /init-scripts/mbzs/12_ceip.mbz 14
moosh course-config-set course 125 shortname proyecto_prim_3
moosh course-config-set course 125 fullname "PROYECTO  3"
moosh course-restore /init-scripts/mbzs/11_ceip.mbz 14
moosh course-config-set course 126 shortname proyecto_prim_4
moosh course-config-set course 126 fullname "PROYECTO  4"
moosh course-restore /init-scripts/mbzs/7_ceip.mbz 14
moosh course-config-set course 127 shortname proyecto_prim_5
moosh course-config-set course 127 fullname "PROYECTO  5"
moosh course-restore /init-scripts/mbzs/10_ceip.mbz 14
moosh course-config-set course 128 shortname proyecto_prim_6
moosh course-config-set course 128 fullname "PROYECTO  6"

moosh course-restore /init-scripts/mbzs/3-biologia-primero-eso-ies.mbz 16
moosh course-config-set course 129 shortname biologia-primero-eso
moosh course-config-set course 129 fullname "Biología y Geología 1º"

moosh course-restore /init-scripts/mbzs/4-geografia-primero-eso-ies.mbz 16
moosh course-config-set course 130 shortname geografia-primero-eso
moosh course-config-set course 130 fullname "Geografía e Historia 1º"

moosh course-restore /init-scripts/mbzs/5-lengua-primero-eso-ies.mbz 16
moosh course-config-set course 131 shortname lengua-primero-eso
moosh course-config-set course 131 fullname "Lengua Castellana y Literatura 1º"

moosh course-restore /init-scripts/mbzs/6-matematicas-primero-eso-ies.mbz 16
moosh course-config-set course 132 shortname matematicas-primero-eso
moosh course-config-set course 132 fullname "Matemáticas 1º"

moosh course-restore /init-scripts/mbzs/7-english-primero-eso-ies.mbz 16
moosh course-config-set course 133 shortname english-primero-eso
moosh course-config-set course 133 fullname "English 1º"

moosh course-restore /init-scripts/mbzs/8-educacionf-primero-eso-ies.mbz 16
moosh course-config-set course 134 shortname educacionf-primero-eso
moosh course-config-set course 134 fullname "Educación Física 1º"

moosh course-restore /init-scripts/mbzs/9-plastica-primero-eso-ies.mbz 16
moosh course-config-set course 135 shortname plastica-primero-eso
moosh course-config-set course 135 fullname "Educación plástica, visual y audiovisual 1º"

moosh course-restore /init-scripts/mbzs/10-musica-primero-eso-ies.mbz 16
moosh course-config-set course 136 shortname musica-primero-eso
moosh course-config-set course 136 fullname "Música 1º"

moosh course-restore /init-scripts/mbzs/11-francais-primero-eso-ies.mbz 16
moosh course-config-set course 137 shortname francais-primero-eso
moosh course-config-set course 137 fullname "Français 1º"

moosh course-restore /init-scripts/mbzs/12-deusch-primero-eso-ies.mbz 16
moosh course-config-set course 138 shortname deusch-primero-eso
moosh course-config-set course 138 fullname "Deusch 1º"

moosh course-restore /init-scripts/mbzs/13-tallerm-primero-eso-ies.mbz 16
moosh course-config-set course 139 shortname tallerm-primero-eso
moosh course-config-set course 139 fullname "Taller de Matemáticas 1º"

moosh course-restore /init-scripts/mbzs/14-tallerl-primero-eso-ies.mbz 16
moosh course-config-set course 140 shortname tallerl-primero-eso
moosh course-config-set course 140 fullname "Taller de Lengua 1º"

moosh course-restore /init-scripts/mbzs/15-aragones-primero-eso-ies.mbz 16
moosh course-config-set course 141 shortname aragones-primero-eso
moosh course-config-set course 141 fullname "Aragonés 1º"

moosh course-restore /init-scripts/mbzs/16-catalan-primero-eso-ies.mbz 16
moosh course-config-set course 142 shortname catalan-primero-eso
moosh course-config-set course 142 fullname "Catalán 1º"

moosh course-restore /init-scripts/mbzs/17-religion-primero-eso-ies.mbz 16
moosh course-config-set course 143 shortname religion-primero-eso
moosh course-config-set course 143 fullname "Religión 1º"

moosh course-restore /init-scripts/mbzs/18-valores-primero-eso-ies.mbz 16
moosh course-config-set course 144 shortname valores-primero-eso
moosh course-config-set course 144 fullname "Valores Éticos 1º"

moosh course-restore /init-scripts/mbzs/19-tutoria-primero-eso-ies.mbz 16
moosh course-config-set course 145 shortname tutoria-primero-eso
moosh course-config-set course 145 fullname "Tutoría 1º"

moosh course-restore /init-scripts/mbzs/20-linguistico-primero-pmar-ies.mbz 21
moosh course-config-set course 146 shortname linguistico-primero-pmar
moosh course-config-set course 146 fullname "Ámbito Lingüístico y Social PMAR 1º"

moosh course-restore /init-scripts/mbzs/21-cientifico-primero-pmar-ies.mbz 21
moosh course-config-set course 147 shortname cientifico-primero-pmar
moosh course-config-set course 147 fullname "Ámbito Científico y Matemático PMAR  1º"

moosh course-restore /init-scripts/mbzs/22-extranjera-primero-pmar-ies.mbz 21
moosh course-config-set course 148 shortname extranjera-primero-pmar
moosh course-config-set course 148 fullname "Ámbito de Lengua Extranjera PMAR 1º"

moosh course-restore /init-scripts/mbzs/23-educacionf-primero-pmar-ies.mbz 21
moosh course-config-set course 149 shortname educacionf-primero-pmar
moosh course-config-set course 149 fullname "Educación Físca PMAR 1º"

moosh course-restore /init-scripts/mbzs/24-tecnologia-primero-pmar-ies.mbz 21
moosh course-config-set course 150 shortname tecnologia-primero-pmar
moosh course-config-set course 150 fullname "Tecnología PMAR 1º"

moosh course-restore /init-scripts/mbzs/25-plastica-primero-pmar-ies.mbz 21
moosh course-config-set course 151 shortname plastica-primero-pmar
moosh course-config-set course 151 fullname "Educación Plástica, Visual y Audiovisual PMAR 1º"

moosh course-restore /init-scripts/mbzs/26-tutoria-primero-pmar-ies.mbz 21
moosh course-config-set course 152 shortname tutoria-primero-pmar
moosh course-config-set course 152 fullname "Tutoría PMAR 1º"

moosh course-restore /init-scripts/mbzs/27-valores-primero-pmar-ies.mbz 21
moosh course-config-set course 153 shortname valores-primero-pmar
moosh course-config-set course 153 fullname "Valores Éticos PMAR 1º"

moosh course-restore /init-scripts/mbzs/28-religion-primero-pmar-ies.mbz 21
moosh course-config-set course 154 shortname religion-primero-pmar
moosh course-config-set course 154 fullname "Religión PMAR 1º"

moosh course-restore /init-scripts/mbzs/46-biologia-segundo-eso-ies.mbz 17
moosh course-config-set course 155 shortname biologia-segundo-eso
moosh course-config-set course 155 fullname "Física y Química 2º"

moosh course-restore /init-scripts/mbzs/47-geografia-segundo-eso-ies.mbz 17
moosh course-config-set course 156 shortname geografia-segundo-eso
moosh course-config-set course 156 fullname "Geografía e Historia 2º"

moosh course-restore /init-scripts/mbzs/48-lengua-segundo-eso-ies.mbz 17
moosh course-config-set course 157 shortname lengua-segundo-eso
moosh course-config-set course 157 fullname "Lengua Castellana y Literatura 2º"

moosh course-restore /init-scripts/mbzs/49-matematicas-segundo-eso-ies.mbz 17
moosh course-config-set course 158 shortname matematicas-segundo-eso
moosh course-config-set course 158 fullname "Matemáticas 2º"

moosh course-restore /init-scripts/mbzs/50-english-segundo-eso-ies.mbz 17
moosh course-config-set course 159 shortname english-segundo-eso
moosh course-config-set course 159 fullname "English 2º"

moosh course-restore /init-scripts/mbzs/51-educacionf-segundo-eso-ies.mbz 17
moosh course-config-set course 160 shortname educacionf-segundo-eso
moosh course-config-set course 160 fullname "Educación Física 2º"

moosh course-restore /init-scripts/mbzs/52-plastica-segundo-eso-ies.mbz 17
moosh course-config-set course 161 shortname plastica-segundo-eso
moosh course-config-set course 161 fullname "Educación plástica, visual y audiovisual 2º"

moosh course-restore /init-scripts/mbzs/53-tecnologia-segundo-eso-ies.mbz 17
moosh course-config-set course 162 shortname tecnologia-segundo-eso
moosh course-config-set course 162 fullname "Tecnología 2º"

moosh course-restore /init-scripts/mbzs/54-francais-segundo-eso-ies.mbz 17
moosh course-config-set course 163 shortname francais-segundo-eso
moosh course-config-set course 163 fullname "Français 2º"

moosh course-restore /init-scripts/mbzs/55-deusch-segundo-eso-ies.mbz 17
moosh course-config-set course 164 shortname deusch-segundo-eso
moosh course-config-set course 164 fullname "Deusch 2º"

moosh course-restore /init-scripts/mbzs/56-tallerm-segundo-eso-ies.mbz 17
moosh course-config-set course 165 shortname tallerm-segundo-eso
moosh course-config-set course 165 fullname "Taller de Matemáticas 2º"

moosh course-restore /init-scripts/mbzs/57-tallerl-segundo-eso-ies.mbz 17
moosh course-config-set course 166 shortname tallerl-segundo-eso
moosh course-config-set course 166 fullname "Taller de Lengua 2º"

moosh course-restore /init-scripts/mbzs/58-aragones-segundo-eso-ies.mbz 17
moosh course-config-set course 167 shortname aragones-segundo-eso
moosh course-config-set course 167 fullname "Aragonés 2º"

moosh course-restore /init-scripts/mbzs/59-catalan-segundo-eso-ies.mbz 17
moosh course-config-set course 168 shortname catalan-segundo-eso
moosh course-config-set course 168 fullname "Catalán 2º"

moosh course-restore /init-scripts/mbzs/60-religion-segundo-eso-ies.mbz 17
moosh course-config-set course 169 shortname religion-segundo-eso
moosh course-config-set course 169 fullname "Religión 2º"

moosh course-restore /init-scripts/mbzs/61-valores-segundo-eso-ies.mbz 17
moosh course-config-set course 170 shortname valores-segundo-eso
moosh course-config-set course 170 fullname "Valores Éticos 2º"

moosh course-restore /init-scripts/mbzs/62-tutoria-segundo-eso-ies.mbz 17
moosh course-config-set course 171 shortname tutoria-segundo-eso
moosh course-config-set course 171 fullname "Tutoría 2º"

moosh course-restore /init-scripts/mbzs/63-biologia-tercero-eso-ies.mbz 18
moosh course-config-set course 172 shortname biologia-tercero-eso
moosh course-config-set course 172 fullname "Biología y Geología 3º"

moosh course-restore /init-scripts/mbzs/64-geografia-tercero-eso-ies.mbz 18
moosh course-config-set course 173 shortname geografia-tercero-eso
moosh course-config-set course 173 fullname "Geografía e Historia 3º"

moosh course-restore /init-scripts/mbzs/65-lengua-tercero-eso-ies.mbz 18
moosh course-config-set course 174 shortname lengua-tercero-eso
moosh course-config-set course 174 fullname "Lengua Castellana y Literatura 3º"

moosh course-restore /init-scripts/mbzs/66-matematicasea-tercero-eso-ies.mbz 18
moosh course-config-set course 175 shortname matematicasea-tercero-eso
moosh course-config-set course 175 fullname "Matemáticas Orientadas a las Enseñanzas Académicas  3º"

moosh course-restore /init-scripts/mbzs/67-english-tercero-eso-ies.mbz 18
moosh course-config-set course 176 shortname english-tercero-eso
moosh course-config-set course 176 fullname "English 3º"

moosh course-restore /init-scripts/mbzs/68-educacionf-tercero-eso-ies.mbz 18
moosh course-config-set course 177 shortname educacionf-tercero-eso
moosh course-config-set course 177 fullname "Educación Física 3º"

moosh course-restore /init-scripts/mbzs/69-fisica-tercero-eso-ies.mbz 18
moosh course-config-set course 178 shortname fisica-tercero-eso
moosh course-config-set course 178 fullname "Física y Química 3º"

moosh course-restore /init-scripts/mbzs/70-musica-tercero-eso-ies.mbz 18
moosh course-config-set course 179 shortname musica-tercero-eso
moosh course-config-set course 179 fullname "Música 3º"

moosh course-restore /init-scripts/mbzs/71-francais-tercero-eso-ies.mbz 18
moosh course-config-set course 180 shortname francais-tercero-eso
moosh course-config-set course 180 fullname "Français 3º"

moosh course-restore /init-scripts/mbzs/72-deusch-tercero-eso-ies.mbz 18
moosh course-config-set course 181 shortname deusch-tercero-eso
moosh course-config-set course 181 fullname "Deusch 3º"

moosh course-restore /init-scripts/mbzs/73-tallerm-tercero-eso-ies.mbz 18
moosh course-config-set course 182 shortname tallerm-tercero-eso
moosh course-config-set course 182 fullname "Taller de Matemáticas 3º"

moosh course-restore /init-scripts/mbzs/74-tallerl-tercero-eso-ies.mbz 18
moosh course-config-set course 183 shortname tallerl-tercero-eso
moosh course-config-set course 183 fullname "Taller de Lengua 3º"

moosh course-restore /init-scripts/mbzs/75-aragones-tercero-eso-ies.mbz 18
moosh course-config-set course 184 shortname aragones-tercero-eso
moosh course-config-set course 184 fullname "Aragonés 3º"

moosh course-restore /init-scripts/mbzs/76-catalan-tercero-eso-ies.mbz 18
moosh course-config-set course 185 shortname catalan-tercero-eso
moosh course-config-set course 185 fullname "Catalán 3º"

moosh course-restore /init-scripts/mbzs/77-religion-tercero-eso-ies.mbz 18
moosh course-config-set course 186 shortname religion-tercero-eso
moosh course-config-set course 186 fullname "Religión 3º"

moosh course-restore /init-scripts/mbzs/78-valores-tercero-eso-ies.mbz 18
moosh course-config-set course 187 shortname valores-tercero-eso
moosh course-config-set course 187 fullname "Valores Éticos 3º"

moosh course-restore /init-scripts/mbzs/79-tutoria-tercero-eso-ies.mbz 18
moosh course-config-set course 188 shortname tutoria-tercero-eso
moosh course-config-set course 188 fullname "Tutoría 3º"

moosh course-restore /init-scripts/mbzs/108-matematicaseap-tercero-eso-ies.mbz 18
moosh course-config-set course 189 shortname matematicaseap-tercero-eso
moosh course-config-set course 189 fullname "Matemáticas Orientadas a las Enseñanzas Aplicadas"

moosh course-restore /init-scripts/mbzs/109-tecnologia-tercero-eso-ies.mbz 18
moosh course-config-set course 190 shortname tecnologia-tercero-eso
moosh course-config-set course 190 fullname "Tecnología 3º"

moosh course-restore /init-scripts/mbzs/110-emprendedora-tercero-eso-ies.mbz 18
moosh course-config-set course 191 shortname emprendedora-tercero-eso
moosh course-config-set course 191 fullname "Iniciación a la actividad emprendedora y empresarial"

moosh course-restore /init-scripts/mbzs/111-clasica-tercero-eso-ies.mbz 18
moosh course-config-set course 192 shortname clasica-tercero-eso
moosh course-config-set course 192 fullname "Cultura clásica"

moosh course-restore /init-scripts/mbzs/112-ciudadania-tercero-eso-ies.mbz 18
moosh course-config-set course 193 shortname ciudadania-tercero-eso
moosh course-config-set course 193 fullname "Educación para la ciudadanía y los derechos humanos"

moosh course-restore /init-scripts/mbzs/80-biologia-cuarto-eso-ies.mbz 19
moosh course-config-set course 194 shortname biologia-cuarto-eso
moosh course-config-set course 194 fullname "Biología y Geología 4º"

moosh course-restore /init-scripts/mbzs/81-geografia-cuarto-eso-ies.mbz 19
moosh course-config-set course 195 shortname geografia-cuarto-eso
moosh course-config-set course 195 fullname "Geografía e Historia 4º"

moosh course-restore /init-scripts/mbzs/82-lengua-cuarto-eso-ies.mbz 19
moosh course-config-set course 196 shortname lengua-cuarto-eso
moosh course-config-set course 196 fullname "Lengua Castellana y Literatura 4º"

moosh course-restore /init-scripts/mbzs/83-matematicas-cuarto-eso-ies.mbz 19
moosh course-config-set course 197 shortname matematicas-cuarto-eso
moosh course-config-set course 197 fullname "Matemáticas 4º"

moosh course-restore /init-scripts/mbzs/84-english-cuarto-eso-ies.mbz 19
moosh course-config-set course 198 shortname english-cuarto-eso
moosh course-config-set course 198 fullname "English 4º"

moosh course-restore /init-scripts/mbzs/85-educacionf-cuarto-eso-ies.mbz 19
moosh course-config-set course 199 shortname educacionf-cuarto-eso
moosh course-config-set course 199 fullname "Educación Física 4º"

moosh course-restore /init-scripts/mbzs/86-plastica-cuarto-eso-ies.mbz 19
moosh course-config-set course 200 shortname plastica-cuarto-eso
moosh course-config-set course 200 fullname "Educación plástica, visual y audiovisual 4º"

moosh course-restore /init-scripts/mbzs/87-musica-cuarto-eso-ies.mbz 19
moosh course-config-set course 201 shortname musica-cuarto-eso
moosh course-config-set course 201 fullname "Música 4º"

moosh course-restore /init-scripts/mbzs/88-francais-cuarto-eso-ies.mbz 19
moosh course-config-set course 202 shortname francais-cuarto-eso
moosh course-config-set course 202 fullname "Français 4º"

moosh course-restore /init-scripts/mbzs/89-deusch-cuarto-eso-ies.mbz 19
moosh course-config-set course 203 shortname deusch-cuarto-eso
moosh course-config-set course 203 fullname "Deusch 4º"

moosh course-restore /init-scripts/mbzs/90-economia-cuarto-eso-ies.mbz 19
moosh course-config-set course 204 shortname economia-cuarto-eso
moosh course-config-set course 204 fullname "Economía 4º"

moosh course-restore /init-scripts/mbzs/91-fisica-cuarto-eso-ies.mbz 19
moosh course-config-set course 205 shortname fisica-cuarto-eso
moosh course-config-set course 205 fullname "Física y Química 4º"

moosh course-restore /init-scripts/mbzs/92-aragones-cuarto-eso-ies.mbz 19
moosh course-config-set course 206 shortname aragones-cuarto-eso
moosh course-config-set course 206 fullname "Aragonés 4º"

moosh course-restore /init-scripts/mbzs/93-catalan-cuarto-eso-ies.mbz 19
moosh course-config-set course 207 shortname catalan-cuarto-eso
moosh course-config-set course 207 fullname "Catalán 4º"

moosh course-restore /init-scripts/mbzs/94-religion-cuarto-eso-ies.mbz 19
moosh course-config-set course 208 shortname religion-cuarto-eso
moosh course-config-set course 208 fullname "Religión 4º"

moosh course-restore /init-scripts/mbzs/95-valores-cuarto-eso-ies.mbz 19
moosh course-config-set course 209 shortname valores-cuarto-eso
moosh course-config-set course 209 fullname "Valores Éticos 4º"

moosh course-restore /init-scripts/mbzs/96-tutoria-cuarto-eso-ies.mbz 19
moosh course-config-set course 210 shortname tutoria-cuarto-eso
moosh course-config-set course 210 fullname "Tutoría 4º"

moosh course-restore /init-scripts/mbzs/113-latin-cuarto-eso-ies.mbz 19
moosh course-config-set course 211 shortname latin-cuarto-eso
moosh course-config-set course 211 fullname "Latín"

moosh course-restore /init-scripts/mbzs/114-cienciasa-cuarto-eso-ies.mbz 19
moosh course-config-set course 212 shortname cienciasa-cuarto-eso
moosh course-config-set course 212 fullname "Ciencias aplicadas a la actividad profesional"

moosh course-restore /init-scripts/mbzs/115-emprendedora-cuarto-eso-ies.mbz 19
moosh course-config-set course 213 shortname emprendedora-cuarto-eso
moosh course-config-set course 213 fullname "Iniciación a la actividad emprendedora y empresarial"

moosh course-restore /init-scripts/mbzs/116-tecnologia-cuarto-eso-ies.mbz 19
moosh course-config-set course 214 shortname tecnologia-cuarto-eso
moosh course-config-set course 214 fullname "Tecnología"

moosh course-restore /init-scripts/mbzs/117-clasica-cuarto-eso-ies.mbz 19
moosh course-config-set course 215 shortname clasica-cuarto-eso
moosh course-config-set course 215 fullname "Cultura Clásica"

moosh course-restore /init-scripts/mbzs/118-filosofia-cuarto-esp-ies.mbz 19
moosh course-config-set course 216 shortname filosofia-cuarto-esp
moosh course-config-set course 216 fullname "Filosofía"

moosh course-restore /init-scripts/mbzs/119-escenicas-cuarto-eso-ies.mbz 19
moosh course-config-set course 217 shortname escenicas-cuarto-eso
moosh course-config-set course 217 fullname "Artes escénicas y danza"

moosh course-restore /init-scripts/mbzs/120-ccientifica-cuarto-eso-ies.mbz 19
moosh course-config-set course 218 shortname ccientifica-cuarto-eso
moosh course-config-set course 218 fullname "Cultura Científica"

moosh course-restore /init-scripts/mbzs/121-tic-cuarto-eso-ies.mbz 19
moosh course-config-set course 219 shortname tic-cuarto-eso
moosh course-config-set course 219 fullname "Tecnologías de la información y la comunicación"

moosh course-restore /init-scripts/mbzs/97-linguistico-segundo-pmar-ies.mbz 22
moosh course-config-set course 220 shortname linguistico-segundo-pmar
moosh course-config-set course 220 fullname "Ámbito Lingüístico y Social PMAR 2º"

moosh course-restore /init-scripts/mbzs/98-cientifico-segundo-pmar-ies.mbz 22
moosh course-config-set course 221 shortname cientifico-segundo-pmar
moosh course-config-set course 221 fullname "Ámbito Científico y Matemático PMAR  2º"

moosh course-restore /init-scripts/mbzs/99-extranjera-segundo-pmar-ies.mbz 22
moosh course-config-set course 222 shortname extranjera-segundo-pmar
moosh course-config-set course 222 fullname "Ámbito de Lengua Extranjera PMAR 2º"

moosh course-restore /init-scripts/mbzs/100-educacionf-segundo-pmar-ies.mbz 22
moosh course-config-set course 223 shortname educacionf-segundo-pmar
moosh course-config-set course 223 fullname "Educación Físca PMAR 2º"

moosh course-restore /init-scripts/mbzs/101-tecnologia-segundo-pmar-ies.mbz 22
moosh course-config-set course 224 shortname tecnologia-segundo-pmar
moosh course-config-set course 224 fullname "Tecnología PMAR 2º"

moosh course-restore /init-scripts/mbzs/103-tutoria-segundo-pmar-ies.mbz 22
moosh course-config-set course 225 shortname tutoria-segundo-pmar
moosh course-config-set course 225 fullname "Tutoría PMAR 2º"

moosh course-restore /init-scripts/mbzs/104-valores-segundo-pmar-ies.mbz 22
moosh course-config-set course 226 shortname valores-segundo-pmar
moosh course-config-set course 226 fullname "Valores Éticos PMAR 2º"

moosh course-restore /init-scripts/mbzs/105-religion-segundo-pmar-ies.mbz 22
moosh course-config-set course 227 shortname religion-segundo-pmar
moosh course-config-set course 227 fullname "Religión PMAR 2º"

moosh course-restore /init-scripts/mbzs/106-musica-segundo-pmar-ies.mbz 22
moosh course-config-set course 228 shortname musica-segundo-pmar
moosh course-config-set course 228 fullname "Música PMAR 2º"

moosh course-restore /init-scripts/mbzs/107-emprendedora-pmar-segundo-ies.mbz 22
moosh course-config-set course 229 shortname emprendedora-pmar-segundo
moosh course-config-set course 229 fullname "Iniciación a la actividad emprendedora y empresarial 2º PMAR"

