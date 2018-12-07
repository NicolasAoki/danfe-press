# danfe-press

#IMPORTANTE
Caso surja nova empresa 

```sh
    git checkout master
    git branch empresaX
    git checkout empresaX
```
  ### Somente assim realizar as mudanças necessárias

Impressão térmica utilizando driver 'cups'.

![](../header.png)

## Instalação

 Linux:

```sh
cd danfe-press/escpos-php
composer installsudo apt-get install php-gd
```
## Requisitos

- php7 (compilar codigo fonte)
- inotify (monitorar ações do diretorio)
- sudo apt-get install php-gd (Gerar Imagem do QR Code)

## Exemplo de uso
Executar processo exibe-danfe
```sh
~/danfe-press/escpos-php/example/interface/cups.php
```
O qual ficará esperando uma alteração na pasta (utilizando inotify)
```sh
~/danfe-press/escpos-php/example/interface/teste_xml
```
Caso algum arquivo XML seja copiado para tal diretorio, será enviado uma requisição para impressora térmica com a formatação correta dos dados convertidos. Seguindo o padrão CUPS.


## Meta

Nicolas Aoki – nick_aoki@hotmail.com

Projetos open-source utilizados:

[NFePHP](https://github.com/nfephp-org/nfephp)
[posprint](https://github.com/nfephp-org/posprint)
[escpos-php](https://github.com/mike42/escpos-php)
