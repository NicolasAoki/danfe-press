# danfe-press

Impressão térmica utilizando driver 'cups'.

![](../header.png)

## Instalação

 Linux:

```sh
cd danfe-press/escpos-php
composer install
```


## Exemplo de uso
Executar processo exibe-danfe
```sh
~/danfe-press/escpos-php/example/interface/exibe-danfe.php
```
O qual ficará esperando uma alteração na pasta (utilizando inotify)
```sh
~/danfe-press/escpos-php/example/interface/teste-xml
```
Caso algum arquivo XML seja copiado para tal diretorio, será enviado uma requisição para impressora térmica com a formatação correta dos dados convertidos. Seguindo o padrão CUPS.


## Meta

Nicolas Aoki – nick_aoki@hotmail.com

Projetos open-source utilizados:

[NFePHP](https://github.com/nfephp-org/nfephp)
[posprint](https://github.com/nfephp-org/posprint)
[escpos-php](https://github.com/mike42/escpos-php)
