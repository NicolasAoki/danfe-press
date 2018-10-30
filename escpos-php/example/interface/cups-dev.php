<?php
/* Change to the correct path if you copy this example! */
require __DIR__ . '/../../autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;


    /**
 * Parte I - Emitente
 * Dados do emitente
 * Campo Obrigatório
 */

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
    /*
    $totItens = $det->count();
    for ($x=0; $x<=$totItens-1; $x++) { 
        $nItem = (int) $det[$x]->attributes()->{'nItem'};
        $cProd = (string) $det[$x]->prod->cProd;
        $xProd = (string) $det[$x]->prod->xProd;
        $qCom = (float) $det[$x]->prod->qCom;
        $uCom = (string) $det[$x]->prod->uCom;
        $vUnCom = (float) $det[$x]->prod->vUnCom;
        $vProd = (float) $det[$x]->prod->vProd;
        //falta formatar os campos e o espaçamento entre eles
       // echo("\n".$nItem .  $cProd. $xProd . $qCom . $uCom . $vUnCom . $vProd);
        echo("\n".$nItem .  $cProd. $xProd . $qCom . $uCom);
    }
*/
    echo "\n";
}
 /*
 * Parte IX - QRCode
 * Consulte via Leitor de QRCode
 * Protocolo de autorização 1234567891234567 22/06/2016 14:43:51
 * Campo Obrigatório
 
function parteIX($nfce,$printer,$align){
    $printer->text('Consulte via Leitor de QRCode');
    $qr = (string)$nfce->infNFeSupl->qrCode;
    $printer -> qrCode($qr);
    /*
    if (!empty(protNFe)) {
        $nProt = (string)$protNFe->infProt->nProt;
        $dhRecbto = (string)$protNFe->infProt->dhRecbto;
        $printer->text('Protocolo de autorização ' . $nProt . $dhRecbto);
    } else {
        $printer->text('NOTA FISCAL INVÁLIDA - SEM PROTOCOLO DE AUTORIZAÇÃO');
    }
    
}
    */
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
    print_r($nfce->infNFeSupl);
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

    //$connector = new CupsPrintConnector("bema2");
    //$printer = new Printer($connector);
    $nfce = loadNFCe('../resources/teste_nota.xml');
    /*$align = array(
        'left' => $printer->setJustification(Printer::JUSTIFY_LEFT),
        'mid' => $printer->setJustification(Printer::JUSTIFY_CENTER),
        'right' => $printer->setJustification(Printer::JUSTIFY_RIGHT),
        'reset' => $printer->setJustification()
    );
    */
    parteI($nfce,$aURI);
    parteIII($nfce,$printer);
    ///parteIX($nfce,$printer,$align);
    //$printer -> text("Testa QR code");
    /* Start the printer 
    $logo = EscposImage::load("../resources/escpos-php.png", false);
    /* Print top logo 
    $printer -> setJustification(Printer::JUSTIFY_CENTER);
    $printer -> graphics($logo);
    $printer -> setJustification(); // Reset
    */    
    $qr = (string)$nfce->infNFeSupl->qrCode;
    echo ($qr);
    $printer->qrCode();
    echo "asda"."\t" . "asd";
    //$printer -> cut();

   // $printer -> close();
} catch (Exception $e) {
    echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
}
