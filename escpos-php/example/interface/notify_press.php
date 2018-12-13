<?php
/* Change to the correct path if you copy this example! */
require __DIR__ . '/../../autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\EscposImage;
require_once("phpqrcode/qrlib.php");

//PARAMETROS
$date = new DateTime();
$nfce = '';
$protNFe = '';
$printer='';
$da = [];
$totItens = 0;
$uri = '';
$aURI = [
    'AC' => 'http://sefaznet.ac.gov.br/nfce/consulta.xhtml',
    'AM' => 'http://sistemas.sefaz.am.gov.br/nfceweb/formConsulta.do',
    'BA' => 'http://nfe.sefaz.ba.gov.br/servicos/nfce/Modulos/Geral/NFCEC_consulta_chave_acesso.aspx',
    'MT' => 'https://www.sefaz.mt.gov.br/nfce/consultanfce',
    'MA' => 'http://www.nfce.sefaz.ma.gov.br/portal/consultaNFe.do?method=preFilterCupom&',
    'PA' => 'https://appnfc.sefa.pa.gov.br/portal/view/consultas/nfce/consultanfce.seam',
    'PB' => 'https://www.receita.pb.gov.br/ser/servirtual/documentos-fiscais/nfc-e/consultar-nfc-e',
    'PR' => 'http://www.sped.fazenda.pr.gov.br/modules/conteudo/conteudo.php?conteudo=100',
    'RJ' => 'http://www4.fazenda.rj.gov.br/consultaDFe/paginas/consultaChaveAcesso.faces',
    'RS' => 'https://www.sefaz.rs.gov.br/NFE/NFE-COM.aspx',
    'RO' => 'http://www.nfce.sefin.ro.gov.br/home.jsp',
    'RR' => 'https://www.sefaz.rr.gov.br/nfce/servlet/wp_consulta_nfce',
    'SE' => 'http://www.nfce.se.gov.br/portal/portalNoticias.jsp?jsp=barra-menu/servicos/consultaDANFENFCe.htm',
    'SP' => 'https://www.nfce.fazenda.sp.gov.br/NFCeConsultaPublica/Paginas/ConsultaPublica.aspx'
];

//tipo de pagamento utilizado na nota
function tipoPag($tPag){
    $aPag = [
        '01' => 'Dinheiro',
        '02' => 'Cheque',
        '03' => 'Cartao de Credito',
        '04' => 'Cartao de Debito',
        '05' => 'Credito Loja',
        '10' => 'Vale Alimentacao',
        '11' => 'Vale Refeicao',
        '12' => 'Vale Presente',
        '13' => 'Vale Combustivel',
        '99' => 'Outros'
    ];
    if (array_key_exists($tPag, $aPag)) {
        return $aPag[$tPag];
    }
    return '';
}
//cabecalho da nota fiscal, informacoes sobre a empresa emissora
function parteI($nfce,$printer,$aURI){
    $razao = (string)$nfce->infNFe->emit->xNome;
    $cnpj = (string)$nfce->infNFe->emit->CNPJ;
    $ie = (string)$nfce->infNFe->emit->IE;
    $im = (string)$nfce->infNFe->emit->IM;
    $log = (string)$nfce->infNFe->emit->enderEmit->xLgr;
    $nro = (string)$nfce->infNFe->emit->enderEmit->nro;
    $bairro = (string)$nfce->infNFe->emit->enderEmit->xBairro;
    $mun = (string)$nfce->infNFe->emit->enderEmit->xMun;
    $uf = (string)$nfce->infNFe->emit->enderEmit->UF;
    if (array_key_exists($uf,$aURI)) {
        $uri =$aURI[$uf];
    }
    $printer ->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text('CNPJ: '.$cnpj.' ');
    $printer -> setEmphasis(true);
    $printer->text($razao."\n");
    $printer -> setEmphasis(false);
    //$printer->text('IE:' . $ie); inscricao estadual
    //$printer->text('IM: '.$im); 
    $printer->text($log . ', ' . $nro . ' ' . $bairro . ' ' . $mun . ' ' . $uf);
    $printer -> setEmphasis(true);
    $printer->text("\nDocumento Auxiliar da Nota Fiscal de Consumidor Eletônica \n");
    $printer->text(divisoria("DANFE NFC-e"));
    $printer -> setEmphasis(false);
}
//especificacoes dos itens da nota fiscal
function parteIII($nfce,$printer){
    $qtdItens = 0;
    $printer -> setEmphasis(true);
    $printer->text("\n");
    $printer -> setFont(Printer::FONT_B);
    $printer->text("\nItem Cod    | Descrição       |  Qtd|    V.Unit| V.Total");
    $printer -> setEmphasis(false);
    //obter dados dos itens da NFCe
     $det = $nfce->infNFe->det;
    foreach ($det as $key => $value) {
        $printer->text("\n");

        $cProd = (string)$value->prod->cProd;               //codigo do produto
        $xProd = substr((string)$value->prod->xProd,0,14);   //descricao
        $qCom = (float)$value->prod->qCom;                  //quantidade
        $vUnCom = number_format((float)$value->prod->vUnCom, 2);                //valor unitario
        $vProd = $value->prod->vProd;   
        
        if(strlen($cProd)>=10){
            $aux = substr($cProd,0,7);
            $printer->text( $aux."\n");
            $cProd = substr($cProd,7);
        }
       
        $cProd = str_pad($cProd, 14,' ');
       
        $xProd = str_pad($xProd, 17,' ');
        $qCom = str_pad($qCom, 5,' ',STR_PAD_LEFT);
        $vUnCom = str_pad($vUnCom, 11,' ',STR_PAD_LEFT);
        $vProd = str_pad($vProd, 9,' ',STR_PAD_LEFT);
        $linha = $cProd. $xProd . $qCom . $vUnCom . $vProd;
        $printer->text($cProd. $xProd . $qCom . $vUnCom . $vProd);
        $qtdItens++;
    }
    $printer -> setFont(); // Reset
    return $qtdItens;
}
//forma utilizada para acerto da nota fiscal
function parteV($nfce,$printer,$qtdItens){
    $printer ->setJustification(Printer::JUSTIFY_LEFT);
    $vNF = number_format((float)$nfce->infNFe->total->ICMSTot->vNF, 2);
    
    $qtdItens = str_pad($qtdItens, 37,' ',STR_PAD_LEFT);
    $vDesc = number_format((float)$nfce->infNFe->total->ICMSTot->vDesc,2);
    $vOutro =number_format((float)$nfce->infNFe->total->ICMSTot->vOutro,2);
    $vSeg =  number_format((float)$nfce->infNFe->total->ICMSTot->vSeg,2);
    $vProd =  number_format((float)$nfce->infNFe->total->ICMSTot->vProd,2);
    $valor_total = $vProd;
    $vProd = str_pad($vProd,45,' ', STR_PAD_LEFT);
    $printer->text("\n");
    $soma =  $vOutro + $vSeg;
    $soma = number_format($soma,2);
    $printer -> setFont(Printer::FONT_B);
    $printer ->setJustification();
    $printer->text("\nQTD. TOTAL DE ITENS".$qtdItens."\n");
    $printer->text("VALOR TOTAL" . $vProd."\n");
    if($soma != 0){
        $soma = str_pad($soma, 29,' ',STR_PAD_LEFT);
        $printer->text("ACRÉSCIMOS(Seguro e outros)" . $soma . "\n");
    }
    if($vDesc != 0){
        $vDesc = str_pad($vDesc, 48,' ',STR_PAD_LEFT);
        $printer->text("DESCONTO" . $vDesc . "\n");
        //$printer->text("VALOR A PAGAR" . $vNF - $vDesc);
        
    }
    if($vNF != $valor_total){
        $vNF_formatado = str_pad($vNF, 40,' ',STR_PAD_LEFT);
        $printer->text("VALOR A PAGAR R$" . $vNF_formatado . "\n");
    }
    

    $printer->text("\n");
    $printer ->setJustification(Printer::JUSTIFY_CENTER);
    $printer -> setEmphasis(true);
    $printer->text(divisoria("FORMA DE PAGAMENTO"));
    $printer -> setEmphasis(false);
    
    $pag = $nfce->infNFe->pag->detPag;
    foreach ($pag as $key) {
        //echo tipoPag((string)$key->tPag);
        $forma_pagamento = tipoPag((string)$key->tPag);
        $forma_pagamento= str_pad($forma_pagamento,17,' ',STR_PAD_RIGHT);
        $tipoPagamento = str_pad($key->vPag,24,' ',STR_PAD_LEFT);
        $printer->text("\n" .$forma_pagamento . " -  " . $tipoPagamento);
    }
    $printer->setFont();
    $printer->setJustification();
}
//informações para consulta da nota fiscal no site da receita
function parteVII($nfce,$printer,$aURI){
    $printer->text("\n");
    $printer -> setEmphasis(true);
    $printer->text(divisoria("INFO RECEITA"));
    $printer -> setEmphasis(false);
    $printer->text("\n");
    $tpAmb = (int) $nfce->infNFe->ide->tpAmb;
    if ($tpAmb == 2) {
        $printer ->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->text("EMITIDA EM AMBIENTE DE HOMOLOGAÇÃO-\nSEM VALOR FISCAL");
        $printer ->setJustification();
    }
    $tpEmis = (int) $nfce->infNFe->ide->tpEmis;
    if ($tpEmis != 1) {
        $printer->text('EMITIDA EM AMBIENTE DE CONTINGẼNCIA');
    }
    $nNF = (float) $nfce->infNFe->ide->nNF;
    $serie = (int) $nfce->infNFe->ide->serie;
    $dhEmi = (string) $nfce->infNFe->ide->dhEmi;
    $Id = (string) $nfce->infNFe->attributes()->{'Id'};
    $chave = substr($Id, 3, strlen($Id)-3);

    $printer->text('Nr. ' . $nNF. ' Serie ' .$serie . ' Emissão ' .$dhEmi);
    $printer -> setEmphasis(true);
    $printer->text("\n");
    $printer->text(divisoria("VIA CONSUMIDOR"));

    $printer->text("\n");
    $printer -> setFont(Printer::FONT_B);
    $printer ->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("Consulte pela chave de acesso em: \n");
    $uf = (string)$nfce->infNFe->emit->enderEmit->UF;
    if (array_key_exists($uf,$aURI)) {
        $uri =$aURI[$uf];
    }
    $printer->text($uri);
    $printer->text("\n");
    $printer ->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text('CHAVE DE ACESSO');
    $printer->text("\n");
    $printer->text($chave);
    $printer ->setJustification();

    $printer -> setFont();
    $printer -> setEmphasis(false);
    $printer->text("\n");
}
function parteVIII($nfce,$printer){
    
    $printer -> text("\n");
    $printer -> setFont(Printer::FONT_B);
    $dest = $nfce->infNFe->dest;
    if (empty($dest)) {
        $printer->text('CONSUMIDOR NÃO IDENTIFICADO');
    }
    $xNome = (string) $nfce->infNFe->dest->xNome;
    if(strlen($xNome)>=44){
        $xNome = substr($xNome,0,28) . "\n" . substr($xNome,29);
    }
    $printer->text($xNome);
    $printer -> text("\n");
    $cnpj = (string) $nfce->infNFe->dest->CNPJ;
    $cpf = (string) $nfce->infNFe->dest->CPF;
    $idEstrangeiro = (string) $nfce->infNFe->dest->idEstrangeiro;
    if (!empty($cnpj)) {
        $printer->text('CNPJ ' . $cnpj);
    }
    if (!empty($cpf)) {
        $printer->text('CPF ' . $cpf);
    }
    if (!empty($idEstrangeiro)) {
        $printer->text('Extrangeiro ' . $idEstrangeiro);
    }
    $xLgr = (string) $nfce->infNFe->dest->enderDest->xLgr;
    $nro = (string) $nfce->infNFe->dest->enderDest->nro;
    $xCpl = (string) $nfce->infNFe->dest->enderDest->xCpl;
    $xBairro = (string) $nfce->infNFe->dest->enderDest->xBairro;
    $xMun = (string) $nfce->infNFe->dest->enderDest->xMun;
    $uf = (string) $nfce->infNFe->dest->enderDest->UF;
    $cep = (string) $nfce->infNFe->dest->enderDest->CEP;
    $printer->text($xLgr . '' . $nro . '' . $xCpl . '' . $xBairro . '' . $xMun . '' . $uf);
    //linha divisória ??
}
function parteIX($nfce,$printer){
    //Informações adicionais
    $infAdic = $nfce->infNFe->infAdic->infCpl;
    
    //retirar # e substitui por quebra de linha \n como instruido na nota
    $infAdic = str_replace("#","\n",$nfce->infNFe->infAdic->infCpl);
    $printer ->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("\n".$infAdic);
    
}
function divisoria($titulo){
    $titulo = str_pad($titulo, 42, '-', STR_PAD_BOTH);
    return $titulo;
}
//Carrega o arquivo XML e o retorna
function loadNFCe($nfcexml){
    $xml = $nfcexml;
    if (is_file($nfcexml)) {
        $xml = file_get_contents($nfcexml);
    }
    if (empty($xml)) {
        throw new InvalidArgumentException('Não foi possivel ler o documento.');
    }
    $nfe = simplexml_load_string($xml, null, LIBXML_NOCDATA);
    $protNFe = $nfe->protNFe;
    $nfce = $nfe->NFe;
    if (empty($protNFe)) {
        //NFe sem protocolo
        $nfce = $nfe;
    }
    return $nfce;
}
//Seta string com enfase, deixando-o centralizado e em negrito
function title(Printer $printer, $text){
    $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
    $printer -> text("\n" . $text);
    $printer -> selectPrintMode(); // Reset
}

try {
    $connector = new CupsPrintConnector("bema2");
    $printer = new Printer($connector);
    //FIM PARAMETROS
    $nome_arq = $argv[1]; 
    
    if($nome_arq){
        echo $nome_arq . " Sendo processado ! \n";
        $nome_arq = substr($nome_arq,0,6);
        if($nome_arq == 'retsai'){
            //banner consagra
            $printer ->setJustification(Printer::JUSTIFY_CENTER);
            $img = EscposImage::load("/sircplus/bin/danfe-press/escpos-php/example/interface/banner.png");
            $printer->bitImage($img);
            $printer ->setJustification();
            //banner
            $printer = new Printer($connector);
            $printer -> initialize();

            $path = "/sircplus/dados/csag/nfce/f0100/ret";
            $nfce = loadNFCe($path."/".$argv[1]);

            parteI($nfce,$printer,$aURI);
            echo "\n PART 1 ! \n";
            $qtdItens = parteIII($nfce,$printer);
            echo "\n PART 3 ! \n";
            parteV($nfce,$printer,$qtdItens);
            echo "\n PART 5 ! \n";
            parteVII($nfce,$printer,$aURI);
            echo "\n PART 7 ! \n";
            parteVIII($nfce,$printer);
            echo "\n PART 8 ! \n";
            parteIX($nfce,$printer);
            echo "\n PART 9 ! \n";
            
            //QRCODE
            $qr = (string)$nfce->infNFeSupl->qrCode;
            echo("\nQRCODE: \n".$qr);
            if(!empty($qr)){
                $tmpfname = tempnam(sys_get_temp_dir(), "temp");
                QRcode::png($qr, $tmpfname);
                $img = EscposImage::load($tmpfname);;
                $printer->bitImage($img,Printer::IMG_DOUBLE_WIDTH | Printer::IMG_DOUBLE_HEIGHT);
                unlink($tmpfname);    
            }
            unset($img);
            //QRCODE
            echo "INFO ADICIONAL";
            parteIX($nfce,$printer);
            $printer ->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("\n Emissão : " . date("d-m-Y H:i:s") );
            $printer ->setJustification(Printer::JUSTIFY_CENTER);
 
            $printer->cut();
            $printer->close();
        
        }else{
            echo "\nParametro nao recebido ! (nome do arquivo)\n";
            $printer->close();
            
        }
    }
    $printer->close();
} catch (Throawble $e) {
    echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
    $printer->close();
}
