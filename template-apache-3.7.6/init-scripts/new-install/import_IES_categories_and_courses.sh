moosh category-create -p 0 -v 1 "ESO"
moosh category-create -p 2 -v 1 "Primero"
moosh category-create -p 2 -v 1 "Segundo"
moosh category-create -p 2 -v 1 "Tercero"
moosh category-create -p 2 -v 1 "Cuarto"
moosh category-create -p 2 -v 1 "PMAR"
moosh category-create -p 7 -v 1 "Primero"
moosh category-create -p 7 -v 1 "Segundo"
moosh category-create -p 0 -v 1 "Bachillerato"
moosh category-create -p 10 -v 1 "Primero"
moosh category-create -p 10 -v 1 "Segundo"
moosh category-create -p 0 -v 1 "Proyectos de trabajo"
moosh category-create -p 0 -v 1 "Sala de Profesorado"
moosh course-restore /init-scripts/mbzs/3-biologia-primero-eso-ies.mbz 3
moosh course-config-set course 2 shortname biologia-primero-eso
moosh course-config-set course 2 fullname "Biología y Geología 1º"

moosh course-restore /init-scripts/mbzs/4-geografia-primero-eso-ies.mbz 3
moosh course-config-set course 3 shortname geografia-primero-eso
moosh course-config-set course 3 fullname "Geografía e Historia 1º"

moosh course-restore /init-scripts/mbzs/5-lengua-primero-eso-ies.mbz 3
moosh course-config-set course 4 shortname lengua-primero-eso
moosh course-config-set course 4 fullname "Lengua Castellana y Literatura 1º"

moosh course-restore /init-scripts/mbzs/6-matematicas-primero-eso-ies.mbz 3
moosh course-config-set course 5 shortname matematicas-primero-eso
moosh course-config-set course 5 fullname "Matemáticas 1º"

moosh course-restore /init-scripts/mbzs/7-english-primero-eso-ies.mbz 3
moosh course-config-set course 6 shortname english-primero-eso
moosh course-config-set course 6 fullname "English 1º"

moosh course-restore /init-scripts/mbzs/8-educacionf-primero-eso-ies.mbz 3
moosh course-config-set course 7 shortname educacionf-primero-eso
moosh course-config-set course 7 fullname "Educación Física 1º"

moosh course-restore /init-scripts/mbzs/9-plastica-primero-eso-ies.mbz 3
moosh course-config-set course 8 shortname plastica-primero-eso
moosh course-config-set course 8 fullname "Educación plástica, visual y audiovisual 1º"

moosh course-restore /init-scripts/mbzs/10-musica-primero-eso-ies.mbz 3
moosh course-config-set course 9 shortname musica-primero-eso
moosh course-config-set course 9 fullname "Música 1º"

moosh course-restore /init-scripts/mbzs/11-francais-primero-eso-ies.mbz 3
moosh course-config-set course 10 shortname francais-primero-eso
moosh course-config-set course 10 fullname "Français 1º"

moosh course-restore /init-scripts/mbzs/12-deusch-primero-eso-ies.mbz 3
moosh course-config-set course 11 shortname deusch-primero-eso
moosh course-config-set course 11 fullname "Deusch 1º"

moosh course-restore /init-scripts/mbzs/13-tallerm-primero-eso-ies.mbz 3
moosh course-config-set course 12 shortname tallerm-primero-eso
moosh course-config-set course 12 fullname "Taller de Matemáticas 1º"

moosh course-restore /init-scripts/mbzs/14-tallerl-primero-eso-ies.mbz 3
moosh course-config-set course 13 shortname tallerl-primero-eso
moosh course-config-set course 13 fullname "Taller de Lengua 1º"

moosh course-restore /init-scripts/mbzs/15-aragones-primero-eso-ies.mbz 3
moosh course-config-set course 14 shortname aragones-primero-eso
moosh course-config-set course 14 fullname "Aragonés 1º"

moosh course-restore /init-scripts/mbzs/16-catalan-primero-eso-ies.mbz 3
moosh course-config-set course 15 shortname catalan-primero-eso
moosh course-config-set course 15 fullname "Catalán 1º"

moosh course-restore /init-scripts/mbzs/17-religion-primero-eso-ies.mbz 3
moosh course-config-set course 16 shortname religion-primero-eso
moosh course-config-set course 16 fullname "Religión 1º"

moosh course-restore /init-scripts/mbzs/18-valores-primero-eso-ies.mbz 3
moosh course-config-set course 17 shortname valores-primero-eso
moosh course-config-set course 17 fullname "Valores Éticos 1º"

moosh course-restore /init-scripts/mbzs/19-tutoria-primero-eso-ies.mbz 3
moosh course-config-set course 18 shortname tutoria-primero-eso
moosh course-config-set course 18 fullname "Tutoría 1º"

moosh course-restore /init-scripts/mbzs/20-linguistico-primero-pmar-ies.mbz 8
moosh course-config-set course 19 shortname linguistico-primero-pmar
moosh course-config-set course 19 fullname "Ámbito Lingüístico y Social PMAR 1º"

moosh course-restore /init-scripts/mbzs/21-cientifico-primero-pmar-ies.mbz 8
moosh course-config-set course 20 shortname cientifico-primero-pmar
moosh course-config-set course 20 fullname "Ámbito Científico y Matemático PMAR  1º"

moosh course-restore /init-scripts/mbzs/22-extranjera-primero-pmar-ies.mbz 8
moosh course-config-set course 21 shortname extranjera-primero-pmar
moosh course-config-set course 21 fullname "Ámbito de Lengua Extranjera PMAR 1º"

moosh course-restore /init-scripts/mbzs/23-educacionf-primero-pmar-ies.mbz 8
moosh course-config-set course 22 shortname educacionf-primero-pmar
moosh course-config-set course 22 fullname "Educación Físca PMAR 1º"

moosh course-restore /init-scripts/mbzs/24-tecnologia-primero-pmar-ies.mbz 8
moosh course-config-set course 23 shortname tecnologia-primero-pmar
moosh course-config-set course 23 fullname "Tecnología PMAR 1º"

moosh course-restore /init-scripts/mbzs/25-plastica-primero-pmar-ies.mbz 8
moosh course-config-set course 24 shortname plastica-primero-pmar
moosh course-config-set course 24 fullname "Educación Plástica, Visual y Audiovisual PMAR 1º"

moosh course-restore /init-scripts/mbzs/26-tutoria-primero-pmar-ies.mbz 8
moosh course-config-set course 25 shortname tutoria-primero-pmar
moosh course-config-set course 25 fullname "Tutoría PMAR 1º"

moosh course-restore /init-scripts/mbzs/27-valores-primero-pmar-ies.mbz 8
moosh course-config-set course 26 shortname valores-primero-pmar
moosh course-config-set course 26 fullname "Valores Éticos PMAR 1º"

moosh course-restore /init-scripts/mbzs/28-religion-primero-pmar-ies.mbz 8
moosh course-config-set course 27 shortname religion-primero-pmar
moosh course-config-set course 27 fullname "Religión PMAR 1º"

moosh course-restore /init-scripts/mbzs/46-biologia-segundo-eso-ies.mbz 4
moosh course-config-set course 28 shortname biologia-segundo-eso
moosh course-config-set course 28 fullname "Física y Química 2º"

moosh course-restore /init-scripts/mbzs/47-geografia-segundo-eso-ies.mbz 4
moosh course-config-set course 29 shortname geografia-segundo-eso
moosh course-config-set course 29 fullname "Geografía e Historia 2º"

moosh course-restore /init-scripts/mbzs/48-lengua-segundo-eso-ies.mbz 4
moosh course-config-set course 30 shortname lengua-segundo-eso
moosh course-config-set course 30 fullname "Lengua Castellana y Literatura 2º"

moosh course-restore /init-scripts/mbzs/49-matematicas-segundo-eso-ies.mbz 4
moosh course-config-set course 31 shortname matematicas-segundo-eso
moosh course-config-set course 31 fullname "Matemáticas 2º"

moosh course-restore /init-scripts/mbzs/50-english-segundo-eso-ies.mbz 4
moosh course-config-set course 32 shortname english-segundo-eso
moosh course-config-set course 32 fullname "English 2º"

moosh course-restore /init-scripts/mbzs/51-educacionf-segundo-eso-ies.mbz 4
moosh course-config-set course 33 shortname educacionf-segundo-eso
moosh course-config-set course 33 fullname "Educación Física 2º"

moosh course-restore /init-scripts/mbzs/52-plastica-segundo-eso-ies.mbz 4
moosh course-config-set course 34 shortname plastica-segundo-eso
moosh course-config-set course 34 fullname "Educación plástica, visual y audiovisual 2º"

moosh course-restore /init-scripts/mbzs/53-tecnologia-segundo-eso-ies.mbz 4
moosh course-config-set course 35 shortname tecnologia-segundo-eso
moosh course-config-set course 35 fullname "Tecnología 2º"

moosh course-restore /init-scripts/mbzs/54-francais-segundo-eso-ies.mbz 4
moosh course-config-set course 36 shortname francais-segundo-eso
moosh course-config-set course 36 fullname "Français 2º"

moosh course-restore /init-scripts/mbzs/55-deusch-segundo-eso-ies.mbz 4
moosh course-config-set course 37 shortname deusch-segundo-eso
moosh course-config-set course 37 fullname "Deusch 2º"

moosh course-restore /init-scripts/mbzs/56-tallerm-segundo-eso-ies.mbz 4
moosh course-config-set course 38 shortname tallerm-segundo-eso
moosh course-config-set course 38 fullname "Taller de Matemáticas 2º"

moosh course-restore /init-scripts/mbzs/57-tallerl-segundo-eso-ies.mbz 4
moosh course-config-set course 39 shortname tallerl-segundo-eso
moosh course-config-set course 39 fullname "Taller de Lengua 2º"

moosh course-restore /init-scripts/mbzs/58-aragones-segundo-eso-ies.mbz 4
moosh course-config-set course 40 shortname aragones-segundo-eso
moosh course-config-set course 40 fullname "Aragonés 2º"

moosh course-restore /init-scripts/mbzs/59-catalan-segundo-eso-ies.mbz 4
moosh course-config-set course 41 shortname catalan-segundo-eso
moosh course-config-set course 41 fullname "Catalán 2º"

moosh course-restore /init-scripts/mbzs/60-religion-segundo-eso-ies.mbz 4
moosh course-config-set course 42 shortname religion-segundo-eso
moosh course-config-set course 42 fullname "Religión 2º"

moosh course-restore /init-scripts/mbzs/61-valores-segundo-eso-ies.mbz 4
moosh course-config-set course 43 shortname valores-segundo-eso
moosh course-config-set course 43 fullname "Valores Éticos 2º"

moosh course-restore /init-scripts/mbzs/62-tutoria-segundo-eso-ies.mbz 4
moosh course-config-set course 44 shortname tutoria-segundo-eso
moosh course-config-set course 44 fullname "Tutoría 2º"

moosh course-restore /init-scripts/mbzs/63-biologia-tercero-eso-ies.mbz 5
moosh course-config-set course 45 shortname biologia-tercero-eso
moosh course-config-set course 45 fullname "Biología y Geología 3º"

moosh course-restore /init-scripts/mbzs/64-geografia-tercero-eso-ies.mbz 5
moosh course-config-set course 46 shortname geografia-tercero-eso
moosh course-config-set course 46 fullname "Geografía e Historia 3º"

moosh course-restore /init-scripts/mbzs/65-lengua-tercero-eso-ies.mbz 5
moosh course-config-set course 47 shortname lengua-tercero-eso
moosh course-config-set course 47 fullname "Lengua Castellana y Literatura 3º"

moosh course-restore /init-scripts/mbzs/66-matematicasea-tercero-eso-ies.mbz 5
moosh course-config-set course 48 shortname matematicasea-tercero-eso
moosh course-config-set course 48 fullname "Matemáticas Orientadas a las Enseñanzas Académicas  3º"

moosh course-restore /init-scripts/mbzs/67-english-tercero-eso-ies.mbz 5
moosh course-config-set course 49 shortname english-tercero-eso
moosh course-config-set course 49 fullname "English 3º"

moosh course-restore /init-scripts/mbzs/68-educacionf-tercero-eso-ies.mbz 5
moosh course-config-set course 50 shortname educacionf-tercero-eso
moosh course-config-set course 50 fullname "Educación Física 3º"

moosh course-restore /init-scripts/mbzs/69-fisica-tercero-eso-ies.mbz 5
moosh course-config-set course 51 shortname fisica-tercero-eso
moosh course-config-set course 51 fullname "Física y Química 3º"

moosh course-restore /init-scripts/mbzs/70-musica-tercero-eso-ies.mbz 5
moosh course-config-set course 52 shortname musica-tercero-eso
moosh course-config-set course 52 fullname "Música 3º"

moosh course-restore /init-scripts/mbzs/71-francais-tercero-eso-ies.mbz 5
moosh course-config-set course 53 shortname francais-tercero-eso
moosh course-config-set course 53 fullname "Français 3º"

moosh course-restore /init-scripts/mbzs/72-deusch-tercero-eso-ies.mbz 5
moosh course-config-set course 54 shortname deusch-tercero-eso
moosh course-config-set course 54 fullname "Deusch 3º"

moosh course-restore /init-scripts/mbzs/73-tallerm-tercero-eso-ies.mbz 5
moosh course-config-set course 55 shortname tallerm-tercero-eso
moosh course-config-set course 55 fullname "Taller de Matemáticas 3º"

moosh course-restore /init-scripts/mbzs/74-tallerl-tercero-eso-ies.mbz 5
moosh course-config-set course 56 shortname tallerl-tercero-eso
moosh course-config-set course 56 fullname "Taller de Lengua 3º"

moosh course-restore /init-scripts/mbzs/75-aragones-tercero-eso-ies.mbz 5
moosh course-config-set course 57 shortname aragones-tercero-eso
moosh course-config-set course 57 fullname "Aragonés 3º"

moosh course-restore /init-scripts/mbzs/76-catalan-tercero-eso-ies.mbz 5
moosh course-config-set course 58 shortname catalan-tercero-eso
moosh course-config-set course 58 fullname "Catalán 3º"

moosh course-restore /init-scripts/mbzs/77-religion-tercero-eso-ies.mbz 5
moosh course-config-set course 59 shortname religion-tercero-eso
moosh course-config-set course 59 fullname "Religión 3º"

moosh course-restore /init-scripts/mbzs/78-valores-tercero-eso-ies.mbz 5
moosh course-config-set course 60 shortname valores-tercero-eso
moosh course-config-set course 60 fullname "Valores Éticos 3º"

moosh course-restore /init-scripts/mbzs/79-tutoria-tercero-eso-ies.mbz 5
moosh course-config-set course 61 shortname tutoria-tercero-eso
moosh course-config-set course 61 fullname "Tutoría 3º"

moosh course-restore /init-scripts/mbzs/108-matematicaseap-tercero-eso-ies.mbz 5
moosh course-config-set course 62 shortname matematicaseap-tercero-eso
moosh course-config-set course 62 fullname "Matemáticas Orientadas a las Enseñanzas Aplicadas"

moosh course-restore /init-scripts/mbzs/109-tecnologia-tercero-eso-ies.mbz 5
moosh course-config-set course 63 shortname tecnologia-tercero-eso
moosh course-config-set course 63 fullname "Tecnología 3º"

moosh course-restore /init-scripts/mbzs/110-emprendedora-tercero-eso-ies.mbz 5
moosh course-config-set course 64 shortname emprendedora-tercero-eso
moosh course-config-set course 64 fullname "Iniciación a la actividad emprendedora y empresarial"

moosh course-restore /init-scripts/mbzs/111-clasica-tercero-eso-ies.mbz 5
moosh course-config-set course 65 shortname clasica-tercero-eso
moosh course-config-set course 65 fullname "Cultura clásica"

moosh course-restore /init-scripts/mbzs/112-ciudadania-tercero-eso-ies.mbz 5
moosh course-config-set course 66 shortname ciudadania-tercero-eso
moosh course-config-set course 66 fullname "Educación para la ciudadanía y los derechos humanos"

moosh course-restore /init-scripts/mbzs/80-biologia-cuarto-eso-ies.mbz 6
moosh course-config-set course 67 shortname biologia-cuarto-eso
moosh course-config-set course 67 fullname "Biología y Geología 4º"

moosh course-restore /init-scripts/mbzs/81-geografia-cuarto-eso-ies.mbz 6
moosh course-config-set course 68 shortname geografia-cuarto-eso
moosh course-config-set course 68 fullname "Geografía e Historia 4º"

moosh course-restore /init-scripts/mbzs/82-lengua-cuarto-eso-ies.mbz 6
moosh course-config-set course 69 shortname lengua-cuarto-eso
moosh course-config-set course 69 fullname "Lengua Castellana y Literatura 4º"

moosh course-restore /init-scripts/mbzs/83-matematicas-cuarto-eso-ies.mbz 6
moosh course-config-set course 70 shortname matematicas-cuarto-eso
moosh course-config-set course 70 fullname "Matemáticas 4º"

moosh course-restore /init-scripts/mbzs/84-english-cuarto-eso-ies.mbz 6
moosh course-config-set course 71 shortname english-cuarto-eso
moosh course-config-set course 71 fullname "English 4º"

moosh course-restore /init-scripts/mbzs/85-educacionf-cuarto-eso-ies.mbz 6
moosh course-config-set course 72 shortname educacionf-cuarto-eso
moosh course-config-set course 72 fullname "Educación Física 4º"

moosh course-restore /init-scripts/mbzs/86-plastica-cuarto-eso-ies.mbz 6
moosh course-config-set course 73 shortname plastica-cuarto-eso
moosh course-config-set course 73 fullname "Educación plástica, visual y audiovisual 4º"

moosh course-restore /init-scripts/mbzs/87-musica-cuarto-eso-ies.mbz 6
moosh course-config-set course 74 shortname musica-cuarto-eso
moosh course-config-set course 74 fullname "Música 4º"

moosh course-restore /init-scripts/mbzs/88-francais-cuarto-eso-ies.mbz 6
moosh course-config-set course 75 shortname francais-cuarto-eso
moosh course-config-set course 75 fullname "Français 4º"

moosh course-restore /init-scripts/mbzs/89-deusch-cuarto-eso-ies.mbz 6
moosh course-config-set course 76 shortname deusch-cuarto-eso
moosh course-config-set course 76 fullname "Deusch 4º"

moosh course-restore /init-scripts/mbzs/90-economia-cuarto-eso-ies.mbz 6
moosh course-config-set course 77 shortname economia-cuarto-eso
moosh course-config-set course 77 fullname "Economía 4º"

moosh course-restore /init-scripts/mbzs/91-fisica-cuarto-eso-ies.mbz 6
moosh course-config-set course 78 shortname fisica-cuarto-eso
moosh course-config-set course 78 fullname "Física y Química 4º"

moosh course-restore /init-scripts/mbzs/92-aragones-cuarto-eso-ies.mbz 6
moosh course-config-set course 79 shortname aragones-cuarto-eso
moosh course-config-set course 79 fullname "Aragonés 4º"

moosh course-restore /init-scripts/mbzs/93-catalan-cuarto-eso-ies.mbz 6
moosh course-config-set course 80 shortname catalan-cuarto-eso
moosh course-config-set course 80 fullname "Catalán 4º"

moosh course-restore /init-scripts/mbzs/94-religion-cuarto-eso-ies.mbz 6
moosh course-config-set course 81 shortname religion-cuarto-eso
moosh course-config-set course 81 fullname "Religión 4º"

moosh course-restore /init-scripts/mbzs/95-valores-cuarto-eso-ies.mbz 6
moosh course-config-set course 82 shortname valores-cuarto-eso
moosh course-config-set course 82 fullname "Valores Éticos 4º"

moosh course-restore /init-scripts/mbzs/96-tutoria-cuarto-eso-ies.mbz 6
moosh course-config-set course 83 shortname tutoria-cuarto-eso
moosh course-config-set course 83 fullname "Tutoría 4º"

moosh course-restore /init-scripts/mbzs/113-latin-cuarto-eso-ies.mbz 6
moosh course-config-set course 84 shortname latin-cuarto-eso
moosh course-config-set course 84 fullname "Latín"

moosh course-restore /init-scripts/mbzs/114-cienciasa-cuarto-eso-ies.mbz 6
moosh course-config-set course 85 shortname cienciasa-cuarto-eso
moosh course-config-set course 85 fullname "Ciencias aplicadas a la actividad profesional"

moosh course-restore /init-scripts/mbzs/115-emprendedora-cuarto-eso-ies.mbz 6
moosh course-config-set course 86 shortname emprendedora-cuarto-eso
moosh course-config-set course 86 fullname "Iniciación a la actividad emprendedora y empresarial"

moosh course-restore /init-scripts/mbzs/116-tecnologia-cuarto-eso-ies.mbz 6
moosh course-config-set course 87 shortname tecnologia-cuarto-eso
moosh course-config-set course 87 fullname "Tecnología"

moosh course-restore /init-scripts/mbzs/117-clasica-cuarto-eso-ies.mbz 6
moosh course-config-set course 88 shortname clasica-cuarto-eso
moosh course-config-set course 88 fullname "Cultura Clásica"

moosh course-restore /init-scripts/mbzs/118-filosofia-cuarto-esp-ies.mbz 6
moosh course-config-set course 89 shortname filosofia-cuarto-esp
moosh course-config-set course 89 fullname "Filosofía"

moosh course-restore /init-scripts/mbzs/119-escenicas-cuarto-eso-ies.mbz 6
moosh course-config-set course 90 shortname escenicas-cuarto-eso
moosh course-config-set course 90 fullname "Artes escénicas y danza"

moosh course-restore /init-scripts/mbzs/120-ccientifica-cuarto-eso-ies.mbz 6
moosh course-config-set course 91 shortname ccientifica-cuarto-eso
moosh course-config-set course 91 fullname "Cultura Científica"

moosh course-restore /init-scripts/mbzs/121-tic-cuarto-eso-ies.mbz 6
moosh course-config-set course 92 shortname tic-cuarto-eso
moosh course-config-set course 92 fullname "Tecnologías de la información y la comunicación"

moosh course-restore /init-scripts/mbzs/97-linguistico-segundo-pmar-ies.mbz 9
moosh course-config-set course 93 shortname linguistico-segundo-pmar
moosh course-config-set course 93 fullname "Ámbito Lingüístico y Social PMAR 2º"

moosh course-restore /init-scripts/mbzs/98-cientifico-segundo-pmar-ies.mbz 9
moosh course-config-set course 94 shortname cientifico-segundo-pmar
moosh course-config-set course 94 fullname "Ámbito Científico y Matemático PMAR  2º"

moosh course-restore /init-scripts/mbzs/99-extranjera-segundo-pmar-ies.mbz 9
moosh course-config-set course 95 shortname extranjera-segundo-pmar
moosh course-config-set course 95 fullname "Ámbito de Lengua Extranjera PMAR 2º"

moosh course-restore /init-scripts/mbzs/100-educacionf-segundo-pmar-ies.mbz 9
moosh course-config-set course 96 shortname educacionf-segundo-pmar
moosh course-config-set course 96 fullname "Educación Físca PMAR 2º"

moosh course-restore /init-scripts/mbzs/101-tecnologia-segundo-pmar-ies.mbz 9
moosh course-config-set course 97 shortname tecnologia-segundo-pmar
moosh course-config-set course 97 fullname "Tecnología PMAR 2º"

moosh course-restore /init-scripts/mbzs/103-tutoria-segundo-pmar-ies.mbz 9
moosh course-config-set course 98 shortname tutoria-segundo-pmar
moosh course-config-set course 98 fullname "Tutoría PMAR 2º"

moosh course-restore /init-scripts/mbzs/104-valores-segundo-pmar-ies.mbz 9
moosh course-config-set course 99 shortname valores-segundo-pmar
moosh course-config-set course 99 fullname "Valores Éticos PMAR 2º"

moosh course-restore /init-scripts/mbzs/105-religion-segundo-pmar-ies.mbz 9
moosh course-config-set course 100 shortname religion-segundo-pmar
moosh course-config-set course 100 fullname "Religión PMAR 2º"

moosh course-restore /init-scripts/mbzs/106-musica-segundo-pmar-ies.mbz 9
moosh course-config-set course 101 shortname musica-segundo-pmar
moosh course-config-set course 101 fullname "Música PMAR 2º"

moosh course-restore /init-scripts/mbzs/107-emprendedora-pmar-segundo-ies.mbz 9
moosh course-config-set course 102 shortname emprendedora-pmar-segundo
moosh course-config-set course 102 fullname "Iniciación a la actividad emprendedora y empresarial 2º PMAR"

