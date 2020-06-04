moosh category-import /init-scripts/${INSTALL_TYPE}/categories_CPI.xml

# Importing courses in inf
categories="21 22 23"

for category in $categories; do
  moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-9-20200530-0857-nu.mbz $category
  moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-8-20200530-0857-nu.mbz $category
  moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-7-20200530-0857-nu.mbz $category
  moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-6-20200530-0857-nu.mbz $category
  moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-4-20200530-0856-nu.mbz $category
  moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-5-20200530-0856-nu.mbz $category
  moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-35-20200530-0857-nu.mbz $category
  moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-36-20200530-0857-nu.mbz $category
  moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-37-20200530-0857-nu.mbz $category
  moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-44-20200530-0857-nu.mbz $category
  moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-45-20200530-0857-nu.mbz $category
  moosh course-restore /init-scripts/mbzs/lastbackup_56.mbz $category
  moosh course-restore /init-scripts/mbzs/lastbackup_57.mbz $category
done

moosh course-config-set course 3 shortname Inf_Pri_Proyecto1
moosh course-config-set course 4 shortname Inf_Pri_Proyecto2
moosh course-config-set course 5 shortname Inf_Pri_Proyecto3
moosh course-config-set course 6 shortname Inf_Pri_Proyecto4
moosh course-config-set course 7 shortname Inf_Pri_Proyecto5
moosh course-config-set course 8 shortname Inf_Pri_Proyecto6
moosh course-config-set course 9 shortname Inf_Pri_English
moosh course-config-set course 10 shortname Inf_Pri_Atencion
moosh course-config-set course 11 shortname Inf_Pri_Religion
moosh course-config-set course 12 shortname Inf_Pri_Aleman
moosh course-config-set course 13 shortname Inf_Pri_Frances
moosh course-config-set course 14 shortname Inf_Pri_Aragones
moosh course-config-set course 15 shortname Inf_Pri_Catalan
moosh course-config-set course 16 shortname Inf_Seg_Proyecto1
moosh course-config-set course 17 shortname Inf_Seg_Proyecto2
moosh course-config-set course 18 shortname Inf_Seg_Proyecto3
moosh course-config-set course 19 shortname Inf_Seg_Proyecto4
moosh course-config-set course 20 shortname Inf_Seg_Proyecto5
moosh course-config-set course 21 shortname Inf_Seg_Proyecto6
moosh course-config-set course 22 shortname Inf_Seg_English
moosh course-config-set course 23 shortname Inf_Seg_Atencion
moosh course-config-set course 24 shortname Inf_Seg_Religion
moosh course-config-set course 25 shortname Inf_Seg_Aleman
moosh course-config-set course 26 shortname Inf_Seg_Frances
moosh course-config-set course 27 shortname Inf_Seg_Aragones
moosh course-config-set course 28 shortname Inf_Seg_Catalan
moosh course-config-set course 29 shortname Inf_Ter_Proyecto1
moosh course-config-set course 30 shortname Inf_Ter_Proyecto2
moosh course-config-set course 31 shortname Inf_Ter_Proyecto3
moosh course-config-set course 32 shortname Inf_Ter_Proyecto4
moosh course-config-set course 33 shortname Inf_Ter_Proyecto5
moosh course-config-set course 34 shortname Inf_Ter_Proyecto6
moosh course-config-set course 35 shortname Inf_Ter_English
moosh course-config-set course 36 shortname Inf_Ter_Atencion
moosh course-config-set course 37 shortname Inf_Ter_Religion
moosh course-config-set course 38 shortname Inf_Ter_Aleman
moosh course-config-set course 39 shortname Inf_Ter_Frances
moosh course-config-set course 40 shortname Inf_Ter_Aragones
moosh course-config-set course 41 shortname Inf_Ter_Catalan

moosh course-config-set course 3 fullname "Proyecto 1"
moosh course-config-set course 4 fullname "Proyecto 2"
moosh course-config-set course 5 fullname "Proyecto 3"
moosh course-config-set course 6 fullname "Proyecto 4"
moosh course-config-set course 7 fullname "Proyecto 5"
moosh course-config-set course 8 fullname "Proyecto 6"
moosh course-config-set course 9 fullname English
moosh course-config-set course 10 fullname "Atención Educativa"
moosh course-config-set course 11 fullname Religión
moosh course-config-set course 12 fullname Alemán
moosh course-config-set course 13 fullname Francés
moosh course-config-set course 14 fullname Aragonés
moosh course-config-set course 15 fullname Catalán
moosh course-config-set course 16 fullname "Proyecto 1"
moosh course-config-set course 17 fullname "Proyecto 2"
moosh course-config-set course 18 fullname "Proyecto 3"
moosh course-config-set course 19 fullname "Proyecto 4"
moosh course-config-set course 20 fullname "Proyecto 5"
moosh course-config-set course 21 fullname "Proyecto 6"
moosh course-config-set course 22 fullname English
moosh course-config-set course 23 fullname "Atención Educativa"
moosh course-config-set course 24 fullname Religión
moosh course-config-set course 25 fullname Alemán
moosh course-config-set course 26 fullname Francés
moosh course-config-set course 27 fullname Aragonés
moosh course-config-set course 28 fullname Catalán
moosh course-config-set course 29 fullname "Proyecto 1"
moosh course-config-set course 30 fullname "Proyecto 2"
moosh course-config-set course 31 fullname "Proyecto 3"
moosh course-config-set course 32 fullname "Proyecto 4"
moosh course-config-set course 33 fullname "Proyecto 5"
moosh course-config-set course 34 fullname "Proyecto 6"
moosh course-config-set course 35 fullname English
moosh course-config-set course 36 fullname "Atención Educativa"
moosh course-config-set course 37 fullname Religión
moosh course-config-set course 38 fullname Alemán
moosh course-config-set course 39 fullname Francés
moosh course-config-set course 40 fullname Aragonés
moosh course-config-set course 41 fullname Catalán

# Importing courses in pri
categories="31 32 33 34 35 36"

for category in $categories; do
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-10-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-13-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-14-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-15-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-16-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-17-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-18-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-19-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-21-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-22-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-23-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-24-20200530-0857-nu.mbz $category
  moosh course-restore mbzs/copia_de_seguridad-moodle2-course-25-20200530-0857-nu.mbz $category
done

moosh course-config-set course 42 shortname Pri_Pri_Lengua
moosh course-config-set course 43 shortname Pri_Pri_Sociales
moosh course-config-set course 44 shortname Pri_Pri_Matematicas
moosh course-config-set course 45 shortname Pri_Pri_Ingles
moosh course-config-set course 46 shortname Pri_Pri_EF
moosh course-config-set course 47 shortname Pri_Pri_Religion
moosh course-config-set course 48 shortname Pri_Pri_Valores
moosh course-config-set course 49 shortname Pri_Pri_Artistica
moosh course-config-set course 50 shortname Pri_Pri_Aleman
moosh course-config-set course 51 shortname Pri_Pri_Catalan
moosh course-config-set course 52 shortname Pri_Pri_Aragones
moosh course-config-set course 53 shortname Pri_Pri_Frances
moosh course-config-set course 54 shortname Pri_Pri_Naturales
moosh course-config-set course 55 shortname Pri_Seg_Lengua
moosh course-config-set course 56 shortname Pri_Seg_Sociales
moosh course-config-set course 57 shortname Pri_Seg_Matematicas
moosh course-config-set course 58 shortname Pri_Seg_Ingles
moosh course-config-set course 59 shortname Pri_Seg_EF
moosh course-config-set course 60 shortname Pri_Seg_Religion
moosh course-config-set course 61 shortname Pri_Seg_Valores
moosh course-config-set course 62 shortname Pri_Seg_Artistica
moosh course-config-set course 63 shortname Pri_Seg_Aleman
moosh course-config-set course 64 shortname Pri_Seg_Catalan
moosh course-config-set course 65 shortname Pri_Seg_Aragones
moosh course-config-set course 66 shortname Pri_Seg_Frances
moosh course-config-set course 67 shortname Pri_Seg_Naturales
moosh course-config-set course 68 shortname Pri_Ter_Lengua
moosh course-config-set course 69 shortname Pri_Ter_Sociales
moosh course-config-set course 70 shortname Pri_Ter_Matematicas
moosh course-config-set course 71 shortname Pri_Ter_Ingles
moosh course-config-set course 72 shortname Pri_Ter_EF
moosh course-config-set course 73 shortname Pri_Ter_Religion
moosh course-config-set course 74 shortname Pri_Ter_Valores
moosh course-config-set course 75 shortname Pri_Ter_Artistica
moosh course-config-set course 76 shortname Pri_Ter_Aleman
moosh course-config-set course 77 shortname Pri_Ter_Catalan
moosh course-config-set course 78 shortname Pri_Ter_Aragones
moosh course-config-set course 79 shortname Pri_Ter_Frances
moosh course-config-set course 80 shortname Pri_Ter_Naturales
moosh course-config-set course 81 shortname Pri_Cua_Lengua
moosh course-config-set course 82 shortname Pri_Cua_Sociales
moosh course-config-set course 83 shortname Pri_Cua_Matematicas
moosh course-config-set course 84 shortname Pri_Cua_Ingles
moosh course-config-set course 85 shortname Pri_Cua_EF
moosh course-config-set course 86 shortname Pri_Cua_Religion
moosh course-config-set course 87 shortname Pri_Cua_Valores
moosh course-config-set course 88 shortname Pri_Cua_Artistica
moosh course-config-set course 89 shortname Pri_Cua_Aleman
moosh course-config-set course 90 shortname Pri_Cua_Catalan
moosh course-config-set course 91 shortname Pri_Cua_Aragones
moosh course-config-set course 92 shortname Pri_Cua_Frances
moosh course-config-set course 93 shortname Pri_Cua_Naturales
moosh course-config-set course 94 shortname Pri_Qui_Lengua
moosh course-config-set course 95 shortname Pri_Qui_Sociales
moosh course-config-set course 96 shortname Pri_Qui_Matematicas
moosh course-config-set course 97 shortname Pri_Qui_Ingles
moosh course-config-set course 98 shortname Pri_Qui_EF
moosh course-config-set course 99 shortname Pri_Qui_Religion
moosh course-config-set course 100 shortname Pri_Qui_Valores
moosh course-config-set course 101 shortname Pri_Qui_Artistica
moosh course-config-set course 102 shortname Pri_Qui_Aleman
moosh course-config-set course 103 shortname Pri_Qui_Catalan
moosh course-config-set course 104 shortname Pri_Qui_Aragones
moosh course-config-set course 105 shortname Pri_Qui_Frances
moosh course-config-set course 106 shortname Pri_Qui_Naturales
moosh course-config-set course 107 shortname Pri_Sex_Lengua
moosh course-config-set course 108 shortname Pri_Sex_Sociales
moosh course-config-set course 109 shortname Pri_Sex_Matematicas
moosh course-config-set course 110 shortname Pri_Sex_Ingles
moosh course-config-set course 111 shortname Pri_Sex_EF
moosh course-config-set course 112 shortname Pri_Sex_Religion
moosh course-config-set course 113 shortname Pri_Sex_Valores
moosh course-config-set course 114 shortname Pri_Sex_Artistica
moosh course-config-set course 115 shortname Pri_Sex_Aleman
moosh course-config-set course 116 shortname Pri_Sex_Catalan
moosh course-config-set course 117 shortname Pri_Sex_Aragones
moosh course-config-set course 118 shortname Pri_Sex_Frances
moosh course-config-set course 119 shortname Pri_Sex_Naturales

moosh course-config-set course 42 fullname Lengua y Literatura
moosh course-config-set course 43 fullname Ciencias Sociales
moosh course-config-set course 44 fullname Matemáticas
moosh course-config-set course 45 fullname English
moosh course-config-set course 46 fullname Educación Física
moosh course-config-set course 47 fullname Religión
moosh course-config-set course 48 fullname Valores Sociales y Cívicos
moosh course-config-set course 49 fullname Educación Artística
moosh course-config-set course 50 fullname Deutsch
moosh course-config-set course 51 fullname Catalán
moosh course-config-set course 52 fullname Aragonés
moosh course-config-set course 53 fullname Français
moosh course-config-set course 54 fullname Ciencias Naturales
moosh course-config-set course 55 fullname Lengua y Literatura
moosh course-config-set course 56 fullname Ciencias Sociales
moosh course-config-set course 57 fullname Matemáticas
moosh course-config-set course 58 fullname English
moosh course-config-set course 59 fullname Educación Física
moosh course-config-set course 60 fullname Religión
moosh course-config-set course 61 fullname Valores Sociales y Cívicos
moosh course-config-set course 62 fullname Educación Artística
moosh course-config-set course 63 fullname Deutsch
moosh course-config-set course 64 fullname Catalán
moosh course-config-set course 65 fullname Aragonés
moosh course-config-set course 66 fullname Français
moosh course-config-set course 67 fullname Ciencias Naturales
moosh course-config-set course 68 fullname Lengua y Literatura
moosh course-config-set course 69 fullname Ciencias Sociales
moosh course-config-set course 70 fullname Matemáticas
moosh course-config-set course 71 fullname English
moosh course-config-set course 72 fullname Educación Física
moosh course-config-set course 73 fullname Religión
moosh course-config-set course 74 fullname Valores Sociales y Cívicos
moosh course-config-set course 75 fullname Educación Artística
moosh course-config-set course 76 fullname Deutsch
moosh course-config-set course 77 fullname Catalán
moosh course-config-set course 78 fullname Aragonés
moosh course-config-set course 79 fullname Français
moosh course-config-set course 80 fullname Ciencias Naturales
moosh course-config-set course 81 fullname Lengua y Literatura
moosh course-config-set course 82 fullname Ciencias Sociales
moosh course-config-set course 83 fullname Matemáticas
moosh course-config-set course 84 fullname English
moosh course-config-set course 85 fullname Educación Física
moosh course-config-set course 86 fullname Religión
moosh course-config-set course 87 fullname Valores Sociales y Cívicos
moosh course-config-set course 88 fullname Educación Artística
moosh course-config-set course 89 fullname Deutsch
moosh course-config-set course 90 fullname Catalán
moosh course-config-set course 91 fullname Aragonés
moosh course-config-set course 92 fullname Français
moosh course-config-set course 93 fullname Ciencias Naturales
moosh course-config-set course 94 fullname Lengua y Literatura
moosh course-config-set course 95 fullname Ciencias Sociales
moosh course-config-set course 96 fullname Matemáticas
moosh course-config-set course 97 fullname English
moosh course-config-set course 98 fullname Educación Física
moosh course-config-set course 99 fullname Religión
moosh course-config-set course 100 fullname Valores Sociales y Cívicos
moosh course-config-set course 101 fullname Educación Artística
moosh course-config-set course 102 fullname Deutsch
moosh course-config-set course 103 fullname Catalán
moosh course-config-set course 104 fullname Aragonés
moosh course-config-set course 105 fullname Français
moosh course-config-set course 106 fullname Ciencias Naturales
moosh course-config-set course 107 fullname Lengua y Literatura
moosh course-config-set course 108 fullname Ciencias Sociales
moosh course-config-set course 109 fullname Matemáticas
moosh course-config-set course 110 fullname English
moosh course-config-set course 111 fullname Educación Física
moosh course-config-set course 112 fullname Religión
moosh course-config-set course 113 fullname Valores Sociales y Cívicos
moosh course-config-set course 114 fullname Educación Artística
moosh course-config-set course 115 fullname Deutsch
moosh course-config-set course 116 fullname Catalán
moosh course-config-set course 117 fullname Aragonés
moosh course-config-set course 118 fullname Français
moosh course-config-set course 119 fullname Ciencias Naturales

# Populate id 37

moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-55-20200530-0856-nu.mbz 37
moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-39-20200530-0857-nu.mbz 37
moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-40-20200530-0857-nu.mbz 37
moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-41-20200530-0857-nu.mbz 37
moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-42-20200530-0857-nu.mbz 37
moosh course-restore /init-scripts/mbzs/copia_de_seguridad-moodle2-course-43-20200530-0856-nu.mbz 37

moosh course-config-set course 120 shortname p1
moosh course-config-set course 121 shortname p2
moosh course-config-set course 122 shortname p3
moosh course-config-set course 123 shortname p4
moosh course-config-set course 124 shortname p5
moosh course-config-set course 125 shortname p6

moosh course-config-set course 120 fullname "Proyecto 1"
moosh course-config-set course 121 fullname "Proyecto 2"
moosh course-config-set course 122 fullname "Proyecto 3"
moosh course-config-set course 123 fullname "Proyecto 4"
moosh course-config-set course 124 fullname "Proyecto 5"
moosh course-config-set course 125 fullname "Proyecto 6"

