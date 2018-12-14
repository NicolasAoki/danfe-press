while sleep 2; do ls; done
ou----

while true
do 
    ls
    sleep 1
done
---------------------

#!/bin/sh
MONITORDIR="/root"
inotifywait -e create | echo "identificou ao criar"

---------------------
inotifywait -m --exclude "[^.][^x][^m][^l]$" /sircplus/dados/csag/nfce/f0100/ret/ -e close_write |
    while read path action file; do
	    echo $file |
            /usr/bin/php -q /sircplus/bin/danfe-press/escpos-php/example/interface/notify_press.php $file 
    done
---------------------

inotifywait -m -e close_write --format '%f' /sircplus/dados/csag/nfce/f0100/ret/ | while read FILE
do
  echo "$FILE"
  /usr/bin/php -q /sircplus/bin/danfe-press/escpos-php/example/interface/notify_press.php echo $FILE
done


---------------------
# exclude retira arquivos terminados em xml, aswp e diferentes de rets
# verifica a criacao de um arquivo aplicados com regex (sobrando o aspw),
# retira-se o nome manda por parametro para o programa php notify_press 

inotifywait -m  -e create --exclude '\.xml|\.aswp' /sircplus/dados/csag/nfce/f0100/ret/ | 
while read path action file; do
	echo "The file '$file' appeared in directory '$path' via '$action'" |
	/usr/bin/php -q /sircplus/bin/danfe-press/escpos-php/example/interface/notify_press.php $file
done
---------------------
#PARTE 2
# exclude retira arquivos terminados em xml, aswp e diferentes de rets
# verifica a criacao de um arquivo aplicados com regex (sobrando o aspw),
# retira-se o nome manda por parametro para o programa php notify_press 

inotifywait -m -e create /sircplus/dados/csag/nfce/f0100/ret/ | 
while read path action file; do sleep 3s 
	echo "The file '$file' appeared in directory '$path' via '$action'" |
	/usr/bin/php -q /sircplus/bin/danfe-press/escpos-php/example/interface/notify_press.php $file
done
---------------------
inotifywait -m /sircplus/dados/csag/nfce/f0100/ret/ -e close_write |
    while read path action file; do
        if [ "$file" =~ retsai* ] && ["$file" -ne *".swp" ]; then # Does the file end with .xml?
	    echo $file |
            /usr/bin/php -q /sircplus/bin/danfe-press/escpos-php/example/interface/notify_press.php $file 
        fi
    done
---------------------


#!/bin/sh
MONITORDIR="/path/to/the/dir/to/monitor/"
inotifywait -m -r -e create --format '%w%f' "${MONITORDIR}" | while read NEWFILE
do
        echo "This is the body of your mail" | mailx -s "File ${NEWFILE} has been created" "yourmail@addresshere.tld"
done