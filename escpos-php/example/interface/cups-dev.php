<?php

require __DIR__ . '/../../autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;


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

    echo("\nItem Cod     |      Desc      | Qtd |  V.Unit | V.Total");

    $det = $nfce->infNFe->det;
    foreach ($det as $key => $value) {
        echo ("\n");
        $cProd = (string)$value->prod->cProd;               //codigo do produto
        $xProd = substr((string)$value->prod->xProd,0,14);   //descricao
        $qCom = (float)$value->prod->qCom;                  //quantidade
        $vUnCom = (float)$value->prod->vUnCom;                //valor unitario
        $vProd = (float)$value->prod->vProd;   
        
        if(strlen($cProd)>=14){
            $aux = substr($cProd,0,10);
            echo $aux."\n";
            $cProd = substr($cProd,10);
        }
       
        $cProd = str_pad($cProd, 14,' ');
       
        $xProd = str_pad($xProd, 16,' ');
        $qCom = str_pad($qCom, 6,' ',STR_PAD_BOTH);
        $vUnCom = str_pad($vUnCom, 10,' ',STR_PAD_BOTH);
        $vProd = str_pad($vProd, 10,' ',STR_PAD_BOTH);
        $linha = $cProd. $xProd . $qCom . $vUnCom . $vProd;
        //$linha = substr($linha,0,42);
        echo $linha;
        
    }

    echo "\n";
}
function parteIV($nfce,$printer){
    $vTotTrib = (float) $nfce->infNFe->total->ICMSTot->vTotTrib;
    echo("\n");
    echo('Informação dos Tributos Totais:' . '' . 'R$ ' .  $vTotTrib);
    echo("\nIncidentes (Lei Federal 12.741 /2012) \n Fonte IBPT");
}
function parteV($nfce,$printer){
    
    $vNF = (float) $nfce->infNFe->total->ICMSTot->vNF;
   echo("\n");
   echo('VALOR TOTAL R$ ' . $vNF);
   echo("\n");
   /*
   echo('FORMA PAGAMENTO          VALOR PAGO');
    $pag = $nfce->infNFe->pag;
    $tot = $pag->count();
    
    foreach($pag as $key){
        echo $key->vPag;
    }
    /*
    for ($x=0; $x<=$tot-1; $x++) {
        $tPag = (int)tipoPag($pag[0]->tPag);
        $vPag = (int) $pag[0]->vPag;
       echo($tPag . '                  R$ '. $vPag);
    }
    */
   
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
    $nfce = loadNFCe('retsai_consagra.xml');
    parteI($nfce,$aURI);
    parteIII($nfce,$printer);
    parteIV($nfce,$printer);
    parteV($nfce,$printer);
    parteVII($nfce,$printer,$aURI);
    //QRCODE
    $qr = (string)$nfce->infNFeSupl->qrCode;
    if(!empty($qr)){
        //$printer->text($qr);
        $tmpfname = tempnam(sys_get_temp_dir(), "temp");
        QRcode::png($qr, $tmpfname);
        $img = EscposImage::load($tmpfname);;
        $printer->bitImage($img);
        unlink($tmpfname);    
    }
    //QRCODE
} catch (Exception $e) {
    echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
}
