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
inotifywait -m -e create -e moved_to -d -o /sircplus/bin/danfe-press/escpos-php/example/interface/log.log --format '%f' /sircplus/dados/csag/nfce/f0100/ret/ | while read FILE
do
  echo "$FILE"
  /usr/bin/php -q /sircplus/bin/danfe-press/escpos-php/example/interface/notify_press.php $FILE
done


