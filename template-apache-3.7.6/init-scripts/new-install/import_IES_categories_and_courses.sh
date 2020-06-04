moosh category-import /init-scripts/${INSTALL_TYPE}/categories_IES.xml

categories="11 12"

for category in $categories; do
  moosh course-restore /var/www/moodledata/repository/backups/$name\_start_backup.mbz $category
done

echo All done


# To implement in the future
# categories="5 6"
# courses="mybackup1.mbz mybackup2.mbz"

# for name in $categories; do
#   for course in $courses; do
#     moosh -n course-restore --ignore-warnings $course $name
#   done
# done

# list=$(moosh -n course-list -c 5)
# echo "$list"
# emtpy=""
# to_replace="\""

# while IFS=$'\n' read -r line; do
#     IFS=$',' read -r -a fields <<< "$line"
#     category=$(echo "${fields[1]}" | cut -d'/' -f 2)
#     cohort=$(echo "${fields[1]}" | cut -d'/' -f 3)
#     shortname=$(echo "${fields[2]}" | cut -d'_' -f 1)
#     shortname="${category:0:3}_${cohort:0:3}_${shortname:1}"
#     shortname=$(echo "${shortname/\ /_}")
#     fullname=$(echo "${fields[3]}" | sed "s/\(.*\) \([cC]opia [0-9]\).*/\1/g")
#     fullname=$(echo "${fullname#"$to_replace"}")
#     id="${fields[0]}"
#     echo "${id:1:-1}    ${shortname%"$to_replace"}     ${fullname%"$to_replace"}"
#     moosh -n course-config-set course $id shortname $shortname
#     moosh -n course-config-set course $id fullname "$fullname"
# done <<< "$list"

# echo All done