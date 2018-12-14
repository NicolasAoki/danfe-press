#!/bin/bash
############################################################
# SCRIPT DE INICIALIZACAO DO SOFTWARE DE IMPRESSAO DE NFCe #
#----------------------------------------------------------#
# CRIADO POR: Nicolas Aoki                                 #
# Auxilio: Demetrius                                       #
#----------------------------------------------------------#
# Data: 13/12/2018                                         #
############################################################

echo " * Finalizando o processo ativo - inotifywait "
killall -9 inotifywait
sleep 4;


echo " * Iniciando o processo - inotifywait "

#PARTE 3 Com Daemon
# exclude retira arquivos terminados em xml, aswp e diferentes de rets
# verifica a criacao de um arquivo aplicados com regex (sobrando o aspw),
# retira-se o nome manda por parametro para o programa php notify_press 

inotifywait -m -e create -d -o log.log /sircplus/dados/csag/nfce/f0100/ret/ | 
while read path action file; do sleep 3s 
	echo "The file '$file' appeared in directory '$path' via '$action'" |
	/usr/bin/php -q /sircplus/bin/danfe-press/escpos-php/example/interface/notify_press.php $file
done