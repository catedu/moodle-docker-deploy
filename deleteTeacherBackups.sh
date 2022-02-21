#!/bin/bash -xv

for SITE in cmb7.aeducar.es; 
do       
	[ "$SITE" = "www.aeducar.es" ] && continue; [ "$SITE" = "moodle.catedu.es" ] && continue; [ "$SITE" = "csv-aeducar" ] && continue;
	cd $SITE;
	echo -e "** SITE: $SITE **\n";
	AEDUCAR="$SITE"; AEDUCARMOODLE=${AEDUCAR//.}"_moodle_1"; 
	#Es necesario recoger el listado en fichero y no variable porque moosh devuelve el listado en codificación que no es interpretada correctamente por sed para la sustitución de '\n' por ' ' 
	docker exec -it "$AEDUCARMOODLE" moosh -n file-list "mimetype='application/vnd.moodle.backup'" > listabackups.txt; 
        cut -f1  listabackups.txt | tail -n +2 > listaids.txt;
	ListaIDS=$(paste -s -d ' ' listaids.txt);
	echo -e $ListaIDS;
	#Es necesario poner la variable sin comillas dobles para que no se añadan los ids encerrados en comillas simples porque en ese caso moosh sólo borraría el fichero correspondiente al primer id
	docker exec -it "$AEDUCARMOODLE" moosh -n file-delete $ListaIDS;
	rm listabackups.txt listaids.txt;
	cd ..;
done

