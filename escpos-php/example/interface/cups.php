<?php
/* Change to the correct path if you copy this example! */
require __DIR__ . '/../autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;


    /**
 * Parte I - Emitente
 * Dados do emitente
 * Campo Obrigatório
 */

function parteI($nfce,$aURI,$printer,$align){
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
    $align['left'];
    $printer->text('CNPJ: '.$cnpj.' ');
    $printer -> setEmphasis(true);
    $printer->text($razao."\n");
    $printer -> setEmphasis(false);
    //$printer->text('IE:' . $ie); inscricao estadual
    //$printer->text('IM: '.$im); 
    $printer->text($log . ', ' . $nro . ' ' . $bairro . ' ' . $mun . ' ' . $uf);
    $align['right'];
    $printer->text("\nDocumento Auxiliar da Nota Fiscal de Consumidor Eletônica \n");
    $align['reset'];
}

 
function parteIII($nfce,$printer,$align){
    $printer -> setEmphasis(true);
    $align['mid'];
    $printer->text("\nItem Cod  | Desc | Qtd |  V.U nit | V.Total");
    $printer -> setEmphasis(false);
    //obter dados dos itens da NFCe
    $align['reset'];
     $det = $nfce->infNFe->det;
    foreach ($det as $key => $value) {
        # code...
        $cProd = "\n".(string)$value->prod->cProd;               //codigo do produto
        $xProd = " ".substr((string)$value->prod->xProd,0,14);   //descricao
        $qCom = "      ".(float)$value->prod->qCom;                  //quantidade
        $vUnCom = "      ".(float)$value->prod->vUnCom;                //valor unitario
        $vProd = "      ".(float)$value->prod->vProd;                 //
        $printer->text("\n". $cProd. $xProd . $qCom . $vUnCom . $vProd);
    

    }
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
    return $nfce;
}
function title(Printer $printer, $text)
{
    $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
    $printer -> text("\n" . $text);
    $printer -> selectPrintMode(); // Reset
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
    $connector = new CupsPrintConnector("bema2");
    $printer = new Printer($connector);
    $nfce = loadNFCe('teste_nota.xml');
    $align = array(
        'left' => $printer->setJustification(Printer::JUSTIFY_LEFT),
        'mid' => $printer->setJustification(Printer::JUSTIFY_CENTER),
        'right' => $printer->setJustification(Printer::JUSTIFY_RIGHT),
        'reset' => $printer->setJustification()
    );
    parteI($nfce,$aURI,$printer,$align);
    parteIII($nfce,$printer,$align);
    ///parteIX($nfce,$printer,$align);
    //$printer -> text("Testa QR code");
    /* Start the printer 
    $logo = EscposImage::load("../resources/escpos-php.png", false);
    /* Print top logo 
    $printer -> setJustification(Printer::JUSTIFY_CENTER);
    $printer -> graphics($logo);
    $printer -> setJustification(); // Reset
    */    
     /* Text of various (in-proportion) sizes */
     title($printer, "\nChange height & width\n");
     for ($i = 1; $i <= 8; $i++) {
         $printer -> setTextSize($i, $i);
         $printer -> text($i);
     }
     $printer -> text("\n");
     

    $printer -> cut();

    $printer -> close();
} catch (Exception $e) {
    echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
}
