!/bin/bash 

for SITE in *.es; 
do       
	[ "$SITE" = "aeducar.es" ] && continue; [ "$SITE" = "moodle.catedu.es" ] && continue; [ "$SITE" = "csv-aeducar" ] && continue;
	cd $SITE;
	AEDUCAR="$SITE"; AEDUCARMOODLE=${AEDUCAR//.}"_moodle_1";  
	docker exec  "$AEDUCARMOODLE" moosh -n file-list "mimetype='application/vnd.moodle.backup'" > listabackups.txt; 
        cut -f1  listabackups.txt | tail -n +2 > listaids.txt;
	ListaIDS=$(paste -s -d ' ' listaids.txt);
	docker exec "$AEDUCARMOODLE" moosh -n file-delete $ListaIDS;
	rm listabackups.txt listaids.txt
	cd ..;
done
