<?php

require __DIR__ . '/../../autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\EscposImage;
require_once("phpqrcode/qrlib.php");

function parteI($nfce,$aURI){
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
    echo ('CNPJ: '.$cnpj.' ');
    echo($razao." \n");
    echo ($log . ', ' . $nro . ' ' . $bairro . ' ' . $mun . ' ' . $uf);
    echo "\nDocumento Auxiliar da Nota Fiscal de Consumidor Eletônica \n";

}
function parteIII($nfce,$printer){

    echo("\nItem Cod    | Descrição        |  Qtd|  V.Unit | V.Total");

    $det = $nfce->infNFe->det;
    $qtdItens = 0;
    foreach ($det as $key => $value) {
        echo ("\n");
        $cProd = (string)$value->prod->cProd;               //codigo do produto
        $xProd = substr((string)$value->prod->xProd,0,14);   //descricao
        $qCom = (float)$value->prod->qCom;                  //quantidade
        $vUnCom = number_format((float)$value->prod->vUnCom, 2);                //valor unitario
        $vProd = $value->prod->vProd;   
        
        if(strlen($cProd)>=14){
            $aux = substr($cProd,0,10);
            echo $aux."\n";
            $cProd = substr($cProd,10);
        }
       
        $cProd = str_pad($cProd, 14,' ');
       
        $xProd = str_pad($xProd, 17,' ');
        $qCom = str_pad($qCom, 5,' ',STR_PAD_LEFT);
        $vUnCom = str_pad($vUnCom, 11,' ',STR_PAD_LEFT);
        $vProd = str_pad($vProd, 9,' ',STR_PAD_LEFT);
        $linha = $cProd. $xProd . $qCom . $vUnCom . $vProd;
        //$linha = substr($linha,0,42);
        echo $linha;
        $qtdItens++;
        
    }

    echo "\n";
    return $qtdItens;
}
function parteIV($nfce,$printer){
    $vTotTrib = (float) $nfce->infNFe->total->ICMSTot->vTotTrib;
    echo("\n");
    echo('Informação dos Tributos Totais:' . '' . 'R$ ' .  $vTotTrib);
    echo("\nIncidentes (Lei Federal 12.741 /2012) \n Fonte IBPT");
}
function parteV($nfce,$printer,$qtdItens){
    $vNF = number_format((float)$nfce->infNFe->total->ICMSTot->vNF, 2);
    
    $qtdItens = str_pad($qtdItens, 26,' ',STR_PAD_LEFT);
    $vDesc = number_format((float)$nfce->infNFe->total->ICMSTot->vDesc,2);
    $vOutro =number_format((float)$nfce->infNFe->total->ICMSTot->vOutro,2);
    $vSeg =  number_format((float)$nfce->infNFe->total->ICMSTot->vSeg,2);
    $vProd =  number_format((float)$nfce->infNFe->total->ICMSTot->vProd,2);
    $valor_total = $vProd;
    $vProd = str_pad($vProd,45,' ', STR_PAD_LEFT);
    echo("\n");
    $soma =  $vOutro + $vSeg;
    $soma = number_format($soma,2);
    echo("\nQTD. TOTAL DE ITENS".$qtdItens."\n");
    echo("VALOR TOTAL" . $vProd."\n");
    if($soma != 0){
        $soma = str_pad($soma, 29,' ',STR_PAD_LEFT);
        echo("ACRÉSCIMOS(Seguro e outros)" . $soma . "\n");
    }
    if($vDesc != 0){
        $vDesc = str_pad($vDesc, 48,' ',STR_PAD_LEFT);
        echo("DESCONTO" . $vDesc . "\n");
    }
    if($vNF != $valor_total){
        $vNF_formatado = str_pad($vNF, 40,' ',STR_PAD_LEFT);
        echo("VALOR A PAGAR R$" . $vNF_formatado . "\n");
    }
    echo("\n");
    echo(divisoria("FORMA DE PAGAMENTO"));
    
    $pag = $nfce->infNFe->pag->detPag;
    foreach ($pag as $key) {
        //echo tipoPag((string)$key->tPag);
        $forma_pagamento = tipoPag((string)$key->tPag);
        $forma_pagamento= str_pad($forma_pagamento,17,' ',STR_PAD_RIGHT);
        $tipoPagamento = str_pad($key->vPag,24,' ',STR_PAD_LEFT);
        echo("\n" .$forma_pagamento . " -  " . $tipoPagamento);
    }
}
function parteVII($nfce,$printer,$aURI){
    echo("\n");
    $tpAmb = (int) $nfce->infNFe->ide->tpAmb;
    if ($tpAmb == 2) {
        echo('EMITIDA EM AMBIENTE DE HOMOLOGAÇÃO - SEM  VALOR FISCAL ');
    }
    $tpEmis = (int) $nfce->infNFe->ide->tpEmis;
    if ($tpEmis != 1) {
        echo('EMITIDA EM AMBIENTE DE CONTINGẼNCIA');
    }
    $nNF = (float) $nfce->infNFe->ide->nNF;
    $serie = (int) $nfce->infNFe->ide->serie;
    $dhEmi = (string) $nfce->infNFe->ide->dhEmi;
    $Id = (string) $nfce->infNFe->attributes()->{'Id'};
    $chave = substr($Id, 3, strlen($Id)-3);
    echo('Nr. ' . $nNF. ' Serie ' .$serie . ' Emissão ' .$dhEmi . ' via  Consumidor');
    echo("\n");

    echo('Consulte pela chave de acesso em: ');
    $uf = (string)$nfce->infNFe->emit->enderEmit->UF;
    if (array_key_exists($uf,$aURI)) {
        $uri =$aURI[$uf];
    }
    echo($uri);
    echo("\n");
    echo('CHAVE DE ACESSO');
    echo("\n");
    echo($chave);

}
function parteVIII($nfce,$printer){
    
    $dest = $nfce->infNFe->dest;
    if (empty($dest)) {
        echo('CONSUMIDOR NÃO IDENTIFICADO');
    }
    $xNome = (string) $nfce->infNFe->dest->xNome;
    echo($xNome);
    $cnpj = (string) $nfce->infNFe->dest->CNPJ;
    $cpf = (string) $nfce->infNFe->dest->CPF;
    $idEstrangeiro = (string) $nfce->infNFe->dest->idEstrangeiro;
    
    if (!empty($cnpj)) {
        echo('CNPJ ' . $cnpj);
    }
    if (!empty($cpf)) {
        echo('CPF ' . $cpf);
    }
    if (!empty($idEstrangeiro)) {
        echo('Extrangeiro ' . $idEstrangeiro);
    }
    $xLgr = (string) $nfce->infNFe->dest->enderDest->xLgr;
    $nro = (string) $nfce->infNFe->dest->enderDest->nro;
    $xCpl = (string) $nfce->infNFe->dest->enderDest->xCpl;
    $xBairro = (string) $nfce->infNFe->dest->enderDest->xBairro;
    $xMun = (string) $nfce->infNFe->dest->enderDest->xMun;
    $uf = (string) $nfce->infNFe->dest->enderDest->UF;
    $cep = (string) $nfce->infNFe->dest->enderDest->CEP;
    echo($xLgr . '' . $nro . '' . $xCpl . '' . $xBairro . '' . $xMun . '' . $uf);
    //linha divisória ??
}
function parteIX($nfce,$printer){
    $infAdic = $nfce->infNFe->infAdic;
    echo str_replace("#","\n",$nfce->infNFe->infAdic->infCpl);
    
}
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
function loadNFCe($nfcexml){
    $xml = $nfcexml;
    if (is_file($nfcexml)) {
        $xml = @file_get_contents($nfcexml);
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
    print_r($nfce->infNFeSupl->qrCode);
    return $nfce;
}
function divisoria($titulo){
    $titulo = str_pad($titulo, 42, '-', STR_PAD_BOTH);
    return $titulo;
}
try {
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
    $nfce = loadNFCe('retsai_798.xml');
    parteI($nfce,$aURI);
    $qtdItens = parteIII($nfce,$printer);
    parteV($nfce,$printer,$qtdItens);
    parteVII($nfce,$printer,$aURI);
    parteVIII($nfce,$printer);
    parteIX($nfce,$printer);
    //QRCODE
    $qr = (string)$nfce->infNFeSupl->qrCode;
    if(!empty($qr)){
        //$printer->text($qr);
        $tmpfname = tempnam(sys_get_temp_dir(), "temp");
        QRcode::png($qr, $tmpfname);
        $img = EscposImage::load($tmpfname);;
        //$printer->bitImage($img);
        unlink($tmpfname);    
    }
    //QRCODE
} catch (Exception $e) {
    echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
}
